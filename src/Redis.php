<?php
namespace PhpRedis;

/**
 * class Redis 
 */
class Redis
{
    private $_socket;
    private $_command;
    private $_result;

    public function __construct()
    {
        $this->_conn();
    }

    public function __destruct()
    {
        
    }

    /**
     * get count
     * @return int
     */
    private function _conn()
    {
        $this->_socket = fsockopen(Config::$redisConfig['host'], Config::$redisConfig['port'], $errno, $errstr, 30);
        if (!$this->_socket) {
            echo "$errstr ($errno)\n";
        }
    }

    private function _write(string $msg)
    {
        $msg.= "\r\n";
        $byte = fwrite($this->_socket, $msg);
        if ($byte === false)
        {
            throw new Exception("write error");
        }
        elseif ($byte !== strlen($msg))
        {
            throw new Exception("length error");
        }
        else
        {
            return true;
        }
    }

    private function _exec()
    {
        $this->_write($this->_command);
        $this->_result = $this->_read();
    }

    private function _read()
    {
        $result = fgets($this->_socket);
        $type = substr($result, 0, 1);
        switch($type)
        {
            case '+':
                return true;
                break;
            case '-':
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
                    $datalen = strlen($data);
                    if ($datalen > $len)
                    {
                        $data = substr($data, 0, $len - $datalen);
                    }
                    $len-= strlen($data);
                    $ret.= $data;
                }
                return $ret;
                break;
            case '*':
                break;
            default:
                throw new Exception("read error");
                return false;
        }
    }

    public function get($key)
    {
        $this->_command = 'GET ' . $key;
        $this->_exec();
        return $this->_result;
    }

    public function set($key, $value)
    {
        $this->_command = 'SET ' . $key . ' "' . addslashes($value) . '"';
        $this->_exec();
        return $this->_result;

    }
}
