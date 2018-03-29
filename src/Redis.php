<?php
namespace PhpRedis;

use Monolog\Logger;

/**
 * class Redis 
 */
class Redis
{
    private $_socket;
    private $_database;
    private $_command;
    private $_result;
    private $_log;

    public function __construct(int $database = 0)
    {
        $this->_database = $database;
        $this->_log = new Logger('redis');
        $this->_conn();
    }

    public function __destruct()
    {
        $this->_exec('QUIT');
        @fclose($this->_socket);
    }

    public function __call(string $command, array $args)
    {
        $command = strtoupper($command);
        return $this->_exec($command, $args);
    }

    /**
     *
     */
    private function _conn():bool
    {
        $this->_socket = fsockopen(Config::$redisConfig['host'], Config::$redisConfig['port'], $errno, $errstr, 30);
        if (!$this->_socket) {
            $this->_log->error($errstr . '(' . $errno . ')');
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
            $this->_log->error("write redis error");
            return false;
        }
        elseif ($len !== mb_strlen($command, '8bit'))
        {
            $this->_log->error("writed data length error");
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
            $this->_log->error(__METHOD__ . " read redis error");
            return false;
        }
        return $result;
    }

    private function _parseResult(string $result)
    {
        //var_dump($result);
        $type = substr($result, 0, 1);
        switch($type)
        {
            case '+':
                return true;
                break;
            case '-':
                $this->_log->error(__METHOD__ . $result);
                throw new Exception("error");
                return false;
                break;
            case ':':
                break;
            case '$':
                $len = (int)substr($result, 1, -2);
                $ret = '';
                while($len > 0)
                {
                    $data= fgets($this->_socket);
                    //var_dump($data);
                    $datalen = strlen($data);
                    if ($datalen > $len)
                    {
                        $data = substr($data, 0, $len - $datalen);
                    }
                    $len-= strlen($data);
                    $ret.= $data;
                }
                echo "########### $ret #########\n";
                return $ret;
                break;
            case '*':
                break;
            default:
                $this->_log->error("read error");
                return false;
        }
    }
}
