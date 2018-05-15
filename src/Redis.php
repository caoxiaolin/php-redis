<?php
namespace PhpRedis;

/**
 * class Redis 
 */
class Redis
{
    /**
     * Redis socket connection
     */
    private $_socket;

    /**
     * The database index, default 0, specified with the "SELECT" command
     */
    private $_database;

    /**
     * The redis command
     */
    private $_command;

    /**
     * The last return result, may be boolean, string, numeric, or list, etc.
     */
    private $_result;


    /**
     * Callback function
     */
    private $_callback;

    /**
     * @param $database 
     */
    public function __construct(int $database = 0)
    {
        $this->_database = $database;
        $this->_conn();
    }

    public function __destruct()
    {
        $this->_exec('QUIT');
        fclose($this->_socket);
    }

    public function __call(string $command, array $args)
    {
        $command = strtoupper($command);

        if ($this->_exec($command, $args)) {
            return $this->_result;
        } else {
            return false;
        }
    }

    /**
     * connect redis server
     */
    private function _conn():void
    {
        if ($this->_socket)
        {
            return;
        }

        $retries = Config::$redisConfig['retries'];
        while ($retries > 0) {
            $retries--;
            $this->_socket = @fsockopen(
                Config::$redisConfig['host'],
                Config::$redisConfig['port'],
                $errno,
                $errstr,
                Config::$redisConfig['ctimeout'] ? Config::$redisConfig['ctimeout'] : floatval(ini_get('default_socket_timeout'))
            );
            if ($this->_socket) {
                break;
            } elseif (!$this->_socket && $retries == 0) {
                throw new \Exception("[" . __METHOD__ . "] " . $errstr . ", errno : " . $errno);
            }
        }
        if (Config::$redisConfig['rwtimeout'])
        {
            stream_set_timeout($this->_socket, Config::$redisConfig['rwtimeout']);
        }
        if (Config::$redisConfig['password']) {
            $this->_exec('AUTH', [Config::$redisConfig['password']]);
        }
        if ($this->_database) {
            $this->_exec('SELECT', [$this->_database]);
        }
    }

    /**
     * send command to redis
     */
    private function _exec(string $command, array $args = []):bool
    {
        if (!$this->_socket) {
            return false;
        }

        $this->_command = $command;

        if ($command == 'PSUBSCRIBE') {
            $this->_callback = array_pop($args);
        }

        $command = "*" . (count($args) + 1) . "\r\n";
        $command .= "$" . mb_strlen($this->_command, '8bit') . "\r\n";
        $command .= $this->_command . "\r\n";
        foreach ($args as $arg) {
            $command .= '$' . mb_strlen($arg, '8bit') . "\r\n" . $arg . "\r\n";
        }

        $this->_write($command);

        $this->_result = $this->_read();
        return true;
    }

    /**
     * write data to socket
     */
    private function _write(string $command):bool
    {
        $len = fwrite($this->_socket, $command);
        if ($len === false) {
            throw new \Exception("[" . __METHOD__ . "] command : " . $this->_command . ", write redis error");
        } elseif ($len !== mb_strlen($command, '8bit')) {
            throw new \Exception("[" . __METHOD__ . "] command : " . $this->_command . ", writed data length error");
        } else {
            return true;
        }
    }

    /**
     * read data from socket
     */
    private function _read()
    {
        //listen & callback
        if ($this->_command == 'PSUBSCRIBE')
        {
            while (!feof($this->_socket))
            {
                call_user_func($this->_callback, $this->_parseResult());
            }
        }
        return $this->_parseResult();
    }

    /**
     * Analyze the result according to the redis protocol
     */
    private function _parseResult()
    {
        $result = fgets($this->_socket);
        if ($result === false) {
            throw new \Exception("[" . __METHOD__ . "] command : " . $this->_command . ", read redis error");
        }

        $type = mb_substr($result, 0, 1, '8bit');
        $data = mb_substr($result, 1, -2, '8bit');
        switch ($type)
        {
            case '+':
                return in_array($data, ['OK', 'PONG']) ? true : $data;
                break;
            case '-':
                throw new \Exception("[" . __METHOD__ . "] command : " . $this->_command . ", redis response: " . $result);
                break;
            case ':':
                return $data;
                break;
            case '$':
                if ($data == "-1") {
                    return $data;
                }
                $res = '';
                $len = (int)$data + 2;
                while ($len > 0) {
                    $content = fgets($this->_socket);
                    if ($content) {
                        $len -= (int)mb_strlen($content, '8bit');
                        $res .= $content;
                    } else {
                        throw new \Exception("[" . __METHOD__ . "] command : " . $this->_command . ", read redis error");
                    }
                }
                return mb_substr($res, 0, -2, '8bit'); //remove \r\n
                break;
            case '*':
                $res = [];
                $num = (int)$data;
                while ($num > 0) {
                    $res[] = $this->_parseResult();
                    $num--;
                }
                $res = $this->_formatResult($res);
                return $res; 
                break;
            default:
                throw new \Exception("[" . __METHOD__ . "] command : " . $this->_command . ", redis response: " . $result);
        }
    }

    /**
     * Formatting the result for some specific commands
     *
     * @param   $res
     */
    private function _formatResult($res)
    {
        if ($this->_command === "HGETALL" && is_array($res)) {
            $return = [];
            $count = count($res);
            for ($i = 0; $i < $count; $i++) {
                $return[$res[$i]] = $res[++$i];
            }
            return $return;
        }
        return $res;
    }
}
