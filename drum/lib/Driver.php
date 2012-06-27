<?php

abstract class Drum_Driver
{
    protected $_tanks = array();
    protected $_params;

    public function __construct($params = array())
    {
        $this->_params = $params;
    }

    abstract public function listTanks();

    abstract public function insertDiesel($datapoints = array());

    function &factory($driver = null, $params = null)
    {
        if(is_null($driver))
            $driver = $GLOBALS['conf']['storage']['driver'];

        $driver = basename($driver);

        if (is_null($params))
            $params = Horde::getDriverConfig('storage', $driver);

        $class = 'Drum_Driver_' . $driver;
        if (class_exists($class)) {
            $drum = new $class($params);
        } else {
            $drum = false;
        }

    }
}
