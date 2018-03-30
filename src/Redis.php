<?php
namespace PhpRedis;

/**
 * class Redis 
 */
class Redis
{
    private $_socket;
    private $_database;
    private $_command;
    private $_result;

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
        @fclose($this->_socket);
    }

    private $_cmds = [
        'DEL',
        //'DUMP',
        'EXISTS',
        'EXPIRE',
        'EXPIREAT',
        'KEYS',
        //'MIGRATE',
        'MOVE',
        //'OBJECT',
        'PERSIST',
        'PEXPIRE',
        'PEXPIREAT',
        'PTTL',
        'RANDOMKEY',
        'RENAME',
        'RENAMENX',
        //'RESTORE',
        'SORT',
        'TTL',
        'TYPE',
        'SCAN',
    ];

    public function __call(string $command, array $args)
    {
        $command = strtoupper($command);
        if ($this->_exec($command, $args))
        {
            return $this->_result;
        }
        else
        {
            return false;
        }
    }

    /**
     *
     */
    private function _conn():bool
    {
        $this->_socket = fsockopen(Config::$redisConfig['host'], Config::$redisConfig['port'], $errno, $errstr, 30);
        if (!$this->_socket) {
            throw new \Exception("[" . __METHOD__ . "] ($errno) $errstr");
            return false;
        }
        if (Config::$redisConfig['password'])
        {
            $this->_exec('AUTH', [Config::$redisConfig['password']]);
        }
        if ($this->_database)
        {
            $this->_exec('SELECT', [$this->_database]);
        }
        return true;
    }

    private function _write(string $command):bool
    {
        $len = fwrite($this->_socket, $command);
        if ($len === false)
        {
            throw new \Exception("[" . __METHOD__ . "] write redis error");
            return false;
        }
        elseif ($len !== mb_strlen($command, '8bit'))
        {
            throw new \Exception("[" . __METHOD__ . "] writed data length error");
            return false;
        }
        else
        {
            return true;
        }
    }

    private function _exec(string $command, array $args = []):bool
    {
        if (!$this->_socket)
        {
            return false;
        }

        $this->_command = "*" . (count($args) + 1) . "\r\n";
        $this->_command.= "$" . mb_strlen($command, '8bit') . "\r\n";
        $this->_command.= $command . "\r\n";
        foreach ($args as $arg) {
            //var_dump($arg);
            $this->_command .= '$' . mb_strlen($arg, '8bit') . "\r\n" . $arg . "\r\n";
        }
        //var_dump($this->_command);
        $this->_write($this->_command);

        $result = $this->_read();
        if ($result)
        {
            $this->_result = $this->_parseResult($result);
        }
        return true;
    }

    private function _read()
    {
        $result = fgets($this->_socket);
        if ($result === false)
        {
            throw new \Exception("[" . __METHOD__ . "] read redis error");
            return false;
        }
        return $result;
    }

    private function _parseResult(string $result)
    {
        //var_dump($result);
        $type = mb_substr($result, 0, 1, '8bit');
        switch($type)
        {
            case '+':
                $msg = mb_substr($result, 1, -2, '8bit');
                return ($msg === 'OK') ? true : $msg;
                break;
            case '-':
                throw new \Exception("[" . __METHOD__ . "] redis response: " . $result);
                return false;
                break;
            case ':':
                return (int)mb_substr($result, 1, -2, '8bit');
                break;
            case '$':
                return $this->_readData(0, (int)mb_substr($result, 1, -2, '8bit'));
                break;
            case '*':
                $num = (int)mb_substr($result, 1, -2, '8bit');  //result number
                $res = [];
                while ($num > 0)
                {
                    $res[] = $this->_readData(1);
                    $num--;
                }
                return $res; 
                break;
            default:
                throw new \Exception("[" . __METHOD__ . "] redis response: " . $result);
                return false;
        }
    }

    private function _readData(int $flag, int $len = 0)
    {
        if (!$flag && $len <= 0)
        {
            return $len;
        }

        $ret = '';
        if ($flag && $len === 0)
        {
            $result= fgets($this->_socket);
            $len = (int)mb_substr($result, 1, -2, '8bit');
        }
        while($len > 0)
        {
            $data= fgets($this->_socket);
            $datalen = strlen($data);
            if ($datalen > $len)
            {
                $data = substr($data, 0, $len - $datalen);
            }
            $len-= strlen($data);
            $ret.= $data;
        }
        return $ret;
    }
}
