<?php

abstract class Superbatch_Driver
{
    protected $_tanks = array();
    protected $_params;

    public function __construct($params = array())
    {
        $this->_params = $params;
    }

    abstract public function listTanks($type);

    abstract public function listWeeks();

    abstract public function getTankHistorybyId($id = 2, $start_time = 1, $end_time);

    abstract public function getTanksHistory($start_time = 1, $end_time);

    abstract public function getTankFluxbyId($id = 2, $volume, $start_time, $end_time);

    abstract public function getFluxbyDay($start_time = 1, $volume = 16);

    abstract public function getTankUsagebyWeek($id = 2, $week_start = 201112, $week_end = null);

    abstract public function getTankNamefromId($id);

    abstract public function getCurrentWeekYear();

    abstract public function insertTankUsage($date);

    function &factory($driver = null, $params = null)
    {
        if(is_null($driver))
            $driver = $GLOBALS['conf']['storage']['driver'];

        $driver = basename($driver);

        if (is_null($params))
            $params = Horde::getDriverConfig('storage', $driver);

        $class = 'Superbatch_Driver_' . $driver;
        if (class_exists($class)) {
            $superbatch = new $class($params);
        } else {
            $superbatch = false;
        }

    }
}
