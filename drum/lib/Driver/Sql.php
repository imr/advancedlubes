<?php

class Drum_Driver_Sql extends Drum_Driver
{
    protected $_params = array();

    protected $_db;

    public function __construct($params = array())
    {
        parent::__construct($params);
        $this->_db = $params['db_adapter'];
    }

    public function listTanks()
    {
        $query = 'SELECT _kp_tankid, tanknum FROM tanks';

        /* Execute the query. */
        try {
            $rows = $this->_db->selectAll($query);
        } catch (Horde_Db_Exception $e) {
            throw new Drum_Exception($e);
        }

        return $rows;
    }

    public function insertDiesel($datapoints = array())
    {
        $query = 'INSERT INTO diesel_price VALUES(?,?,?,?,?,?,?,?,?,?,?)';

        if (count($datapoints == 11)) {
            try {
                $this->_db->insert($query, $datapoints);
            } catch (Horde_Db_Exception $e) {
                throw new Drum_Exception($e);
            }
        }
    }
}
