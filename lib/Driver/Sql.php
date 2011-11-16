<?php

class Superbatch_Driver_Sql extends Superbatch_Driver
{
    protected $_params = array();

    protected $_db;

    public function __construct($name, $params = array())
    {
	$this->_db = $params['db'];
    }

    public function listTanks($type)
    {
        if (empty($type)) {
            $query = 'SELECT _kp_tankid, tanknum FROM tanks';
        } else {
            $query = "SELECT _kp_tankid, tanknum FROM tanks WHERE tanktype = '" . $type . "'";
        }

        /* Execute the query. */
        try {
            $rows = $this->_db->selectAll($query);
        } catch (Horde_Db_Exception $e) {
            throw new Superbatch_Exception($e);
        }

        return $rows;
    }

    public function listWeeks()
    {
        $query = 'SELECT DISTINCT weekofyear AS week FROM tankusage';

        /* Execute the query. */
        try {
            $rows = $this->_db->selectAll($query);
        } catch (Horde_Db_Exception $e) {
            throw new Superbatch_Exception($e);
        }

        return $rows;
    }

    public function getTankHistorybyId($id = 2, $start_time = 1, $end_time) {
        if (!$end_time) {
            $end_time = time();
        } 

        $query = 'SELECT th.curtimestamp as timeunix, p.productCode as productcode, ' .
            'th.volume as volume, th.temperature as temperature from tankhistory th ' .
            'LEFT JOIN products p ON th.productid = p._kp_Products ' .
            'WHERE th.tankid = ? AND th.curtimestamp BETWEEN FROM_UNIXTIME(?) ' .
            'AND FROM_UNIXTIME(?)';
        $values = array($id, $start_time, $end_time);
        try {
            $rows = $this->_db->selectAll($query, $values);
        } catch (Horde_Db_Exception $e) {
            throw new Superbatch_Exception($e);
        }
        return $rows;
    }

    public function getTanksHistory($start_time = 1, $end_time) {
        if (!$end_time) {
            $end_time = time();
        }
        $query = 'CALL GetTanksHistory(?,?)';
        $query = 'SELECT t.tanknum as tanknum, th.curtimestamp as timeunix, p.productCode as productcode, ' .
            'th.volume as volume, th.temperature as temperature FROM tankhistory th INNER JOIN tanks t ' .
            'ON th.tankid = t._kp_tankid LEFT JOIN products p ON th.productid = p._kp_Products ' .
            'WHERE th.curtimestamp BETWEEN FROM_UNIXTIME(?) AND FROM_UNIXTIME(?) ' .
            'ORDER BY th.tankid';
        $values = array($start_time, $end_time);

        try {
            $rows = $this->_db->selectAll($query, $values);
        } catch (Horde_Db_Exception $e) {
            throw new Superbatch_Exception($e);
        }
        return $rows;
    }

    public function getTankFluxbyId($id = 2, $volume = 16, $start_time = 1, $end_time) { //volume is gallons per 5 or 6 minute interval
        if (!$end_time)
            $end_time = time();
        
        $query = 'SELECT t1._kp_tankhistoryid AS startid, t2._kp_tankhistoryid AS endid, t1.volume AS startvolume, t2.volume AS endvolume,' .
                     ' t1.curtimestamp AS starttime, t2.curtimestamp as endtime, productcode FROM (SELECT * from tankhistory' .
                     ' WHERE tankid = ? AND curtimestamp BETWEEN FROM_UNIXTIME(?) and FROM_UNIXTIME(?)) AS t1 INNER JOIN' .
                     ' (SELECT * from tankhistory WHERE tankid = ? AND curtimestamp BETWEEN FROM_UNIXTIME(?) and FROM_UNIXTIME(?))' .
                     ' AS t2 ON t1.tankid = t2.tankid AND t1.curtimestamp < t2.curtimestamp' .
                     ' LEFT JOIN products on t1.productid = products._kp_Products' .
                     ' WHERE TIMESTAMPDIFF(MINUTE, t1.curtimestamp, t2.curtimestamp) < 6 AND' .
                     ' ABS(t1.volume - t2.volume) > ? ORDER BY t1._kp_tankhistoryid';
        $values = array($id, $start_time, $end_time, $id, $start_time, $end_time, $volume);

        try {
            $rows = $this->_db->selectAll($query, $values);
        } catch (Horde_Db_Exception $e) {
            throw new Superbatch_Exception($e);
        }
        return $rows;
    }

    public function getFluxbyDay($start_time = 1, $volume = 16) {
        
        $query = 'SELECT tanknum, t1._kp_tankhistoryid AS startid, t2._kp_tankhistoryid AS endid,' .
                     ' t1.volume AS startvolume, t2.volume AS endvolume,' .
                     ' t1.curtimestamp AS starttime, t2.curtimestamp as endtime, productcode FROM (SELECT * from tankhistory' .
                     ' WHERE DATE(curtimestamp) = ?) AS t1 INNER JOIN' .
                     ' (SELECT * from tankhistory WHERE DATE(curtimestamp) = ?) AS t2' .
                     ' ON t1.tankid = t2.tankid AND t1.curtimestamp < t2.curtimestamp' .
                     ' LEFT JOIN products on t1.productid = products._kp_Products' .
                     ' INNER JOIN tanks on t1.tankid = tanks._kp_tankid' .
                     ' WHERE TIMESTAMPDIFF(MINUTE, t1.curtimestamp, t2.curtimestamp) < 6 AND' .
                     ' ABS(t1.volume - t2.volume) > ? ORDER BY t1.tankid, t1._kp_tankhistoryid';
        $values = array($start_time, $start_time, $volume);

        try {
            $rows = $this->_db->selectAll($query, $values);
        } catch (Horde_Db_Exception $e) {
            throw new Superbatch_Exception($e);
        }
        return $rows;
    }

    public function getTankUsage($id = 2,$week_start = 201112,$week_end = null) {

        $query = 'SELECT * FROM tankusage WHERE tankid = ? AND weekofyear BETWEEN ? AND YEARWEEK(NOW())';
        $values = array($id, $week_start);//, $week_end);

        try {
            $rows = $this->_db->selectAll($query, $values);
        } catch (Horde_Db_Exception $e) {
            throw new Superbatch_Exception($e);
        }
        return $rows;
    }

    public function getTankNamefromId($id) {
        $query = 'SELECT tanknum FROM tanks WHERE _kp_tankid = ?';
	$values = array($id);
        try {
            $name = $this->_db->selectValue($query, $values);
        } catch (Horde_Db_Exception $e) {
            throw new Superbatch_Exception($e);
        }
        return $name;
    }

    public function insertTankUsage($id, $yearweek) {
        $query = 'INSERT INTO tankusage(weekofyear,tankid,increase,decrease) (SELECT ?, ?, '.
                 'SUM(IF(temp.diff > 0, temp.diff, 0)) AS increase, SUM(IF(temp.diff < 0, temp.diff, 0)) '.
                 'AS decrease FROM (SELECT t1.tankid, t2.volume - t1.volume AS diff FROM '.
                 '(SELECT * FROM tankhistory WHERE tankid = ? AND YEARWEEK(curtimestamp) '.
                 '= ?) AS t1 INNER JOIN '.
                 '(SELECT * FROM tankhistory WHERE tankid = ? AND YEARWEEK(curtimestamp) '.
                 '= ?) '.
                 'AS t2 ON TIMESTAMPDIFF(MINUTE, t1.curtimestamp, t2.curtimestamp) = 5 '.
                 'WHERE ABS(t1.volume - t2.volume) > 83.3) AS temp)';
        $values = array($yearweek,$id,$id,$yearweek,$id,$yearweek);

        try {
            $this->_db->execute($query, $values);
        } catch (Horde_Db_Exception $e) {
            throw new Superbatch_Exception($e);
        }
    }
}
