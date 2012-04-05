<?php

abstract class Superbatch_Driver
{
    protected $_tanks = array();
    protected $_params;

    public function __construct($params = array())
    {
        $this->_params = $params;
    }

    abstract public function listTanks($type, $tanks = array());

    abstract public function listNotes();

    abstract public function getNote();

    abstract public function insertNote($user, $note);

    abstract public function listMaterialWeeks();

    abstract public function listTankWeeks($week_start = 201101, $week_end = 999952);

    abstract public function listInventories();

    abstract public function getTanksHistorybyIds($id = array(), $start_time = 1, $end_time);

    abstract public function getTanksHistoryMeasurebyIds($id = array(), $start_time = 1, $end_time);

    abstract public function getTanksHistoriesbyIds($id = array(), $start_time = 1, $end_time);

    abstract public function getTankFluxbyIds($id = array(), $volume, $start_time, $end_time);

    abstract public function getTankFluxRecent($volume);

    abstract public function getFluxbyDay($start_time = 1, $volume = 16);

    abstract public function getTankUsagebyWeek($id = 2, $week_start = 201112, $week_end = null);

    abstract public function getTankUsageforWeek($id = 2, $week = 201112);

    abstract public function getTankNamefromId($id);

    abstract public function getCurrentWeekYear();

    abstract public function insertTankUsage($date);

    abstract public function updateTankMeasure($data);

    abstract public function insertTankHistoryMeasure($id);

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
