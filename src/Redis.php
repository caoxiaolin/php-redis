<?php
namespace PhpRedis;

/**
 * class Redis 
 */
class Redis
{
    private $_socket;
    private $_result;

    public function __destruct()
    {
        
    }

    /**
     * get count
     * @return int
     */
    private function _conn()
    {
        if ($this->$_socket)
        {
            return $this->$_socket;
        }
        $this->$_socket = fsockopen($address, $port, $errno, $errstr, 30);
        if (!$this->$_socket) {
            echo "$errstr ($errno)\n";
        }
        else
        {
            return $this->$_socket;
            $out = "GET a\r\n";
            fwrite($fp, $out);
            echo fgets($fp);
            fclose($fp);
        }
    }

    private function _write(string $msg)
    {
        $msg.= "\r\n";
        $byte = fwrite($this->$_socket, $msg);
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

    private function _read()
    {
        return fgets($this->$_socket);
    }

    public function get($key)
    {
        $cmd = 'GET ' . $key;
        $this->_conn();
        $this->_write($cmd);
        return $this->_read();
    }
}
