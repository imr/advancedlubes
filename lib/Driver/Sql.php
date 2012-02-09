<?php

class Superbatch_Driver_Sql extends Superbatch_Driver
{
    protected $_params = array();

    protected $_db;

    public function __construct($name, $params = array())
    {
	$this->_db = $params['db'];
    }

    public function listTanks($type, $tanks = array())
    {
        $query = 'SELECT _kp_tankid, tanknum, description, compatibility, capacity, currentvolume AS volume, Conversion, measured_inches, tap_inches, tap_volume FROM tanks WHERE tanknum IS NOT NULL ORDER BY TankOrderInventory';
        if (!empty($type)) {
            $query .= " AND tanktype = '" . $type . "'";
        }

        if (!empty($tanks)) {
            $query .= " AND _kp_tankid IN (";
            for($i = 0; $i < count($tanks); $i++) {
                $query .= "$tanks[$i],";
            }
            $query = substr($query, 0, strlen($query) -1) . ')';
        }
        /* Execute the query. */
        try {
            $rows = $this->_db->selectAll($query);
        } catch (Horde_Db_Exception $e) {
            throw new Superbatch_Exception($e);
        }

        return $rows;
    }

    public function listMaterialWeeks()
    {
        $query = 'SELECT DISTINCT YEARWEEK(date) AS week FROM materialusage';

        /* Execute the query. */
        try {
            $rows = $this->_db->selectAll($query);
        } catch (Horde_Db_Exception $e) {
            throw new Superbatch_Exception($e);
        }

        return $rows;
    }

    public function listTankWeeks($week_start = 201101, $week_end = 999952)
    {
        $query = 'SELECT DISTINCT YEARWEEK(date) AS week FROM tankusage WHERE YEARWEEK(date) BETWEEN ? AND ?';
        $values = array($week_start, $week_end);

        /* Execute the query. */
        try {
            $rows = $this->_db->selectAll($query, $values);
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
            'AND FROM_UNIXTIME(?) ORDER BY th.curtimestamp';
        $values = array($id, $start_time, $end_time);
        try {
            $rows = $this->_db->selectAll($query, $values);
        } catch (Horde_Db_Exception $e) {
            throw new Superbatch_Exception($e);
        }
        return $rows;
    }

    public function getTankHistorybyIds($id = array(), $start_time = 1, $end_time) {
        if (!$end_time) {
            $end_time = time();
        } 

        $query = 'SELECT t.tanknum, th.curtimestamp as timeunix, p.productCode as productcode, ' .
            'th.volume as volume, th.temperature as temperature from tankhistory th ' .
            'INNER JOIN tanks t ON th.tankid = t._kp_tankid ' .
            'LEFT JOIN products p ON th.productid = p._kp_Products ' .
            'WHERE th.tankid IN (?';
        for ($i = 0;count($id) - 1 > $i; $i++) {
            $query .= ',?';
        }
        $query .= ') AND th.curtimestamp BETWEEN FROM_UNIXTIME(?) ' .
            'AND FROM_UNIXTIME(?) ORDER BY th.tankid, th.curtimestamp';
        $values = $id;
        $values[] = $start_time;
        $values[] = $end_time;
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
                     ' AS t2 ON TIMESTAMPDIFF(MINUTE, t1.curtimestamp, t2.curtimestamp) = 5' .
                     ' LEFT JOIN products on t1.productid = products._kp_Products' .
                     ' WHERE ABS(t1.volume - t2.volume) > ? ORDER BY t1._kp_tankhistoryid';
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
                     ' ON t1.tankid = t2.tankid AND TIMESTAMPDIFF(MINUTE, t1.curtimestamp, t2.curtimestamp) = 5' .
                     ' LEFT JOIN products on t1.productid = products._kp_Products' .
                     ' INNER JOIN tanks on t1.tankid = tanks._kp_tankid' .
                     ' WHERE ABS(t1.volume - t2.volume) > ? ORDER BY t1.tankid, t1._kp_tankhistoryid';
        $values = array($start_time, $start_time, $volume);

        try {
            $rows = $this->_db->selectAll($query, $values);
        } catch (Horde_Db_Exception $e) {
            throw new Superbatch_Exception($e);
        }
        return $rows;
    }

    public function getTankUsagebyWeek($id = 2,$week_start = 201112,$week_end = null) {

        if (empty($week_end)) {
            $query = 'SELECT YEARWEEK(date) as week, SUM(increase) AS increase, SUM(decrease) AS decrease ' .
                         'FROM tankusage WHERE tankid = ? ' .
                         'AND YEARWEEK(date) BETWEEN ? AND YEARWEEK(NOW()) ' .
                         'GROUP BY YEARWEEK(date) ORDER BY YEARWEEK(date) DESC';
            $values = array($id, $week_start);
        } else {
            $query = 'SELECT YEARWEEK(date) as week, SUM(increase) AS increase, SUM(decrease) AS decrease ' .
                         'FROM tankusage WHERE tankid = ? ' .
                         'AND YEARWEEK(date) BETWEEN ? AND ? ' .
                         'GROUP BY YEARWEEK(date) ORDER BY YEARWEEK(date) DESC';
            $values = array($id, $week_start, $week_end);
        }

        try {
            $rows = $this->_db->selectAll($query, $values);
        } catch (Horde_Db_Exception $e) {
            throw new Superbatch_Exception($e);
        }
        return $rows;
    }

    public function getTankUsageforWeek($id = 2,$week = 201112) {

        $query = 'SELECT * FROM tankusage WHERE tankid = ? ' .
                     'AND YEARWEEK(date) = ? ORDER BY date';
        $values = array($id, $week);

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

    public function getCurrentWeekYear() {
        $query = 'SELECT WEEKYEAR(NOW())';
        try {
            $row = $this->_db->selectValue($query);
        } catch (Horde_Db_Exception $e) {
            throw new Superbatch_Exception($e);
        }
        return $row;
    }

    public function insertTankUsage($date) {
        $query = 'INSERT INTO tankusage(date,tankid,increase,decrease) (SELECT ?, temp.tankid, '.
                 'SUM(IF(temp.diff > 0, temp.diff, 0)) AS increase, SUM(IF(temp.diff < 0, temp.diff, 0)) '.
                 'AS decrease FROM (SELECT t1.tankid, t2.volume - t1.volume AS diff FROM '.
                 '(SELECT * FROM tankhistory WHERE DATE(curtimestamp) = ?) AS t1 '.
                 'INNER JOIN '.
                 '(SELECT * FROM tankhistory WHERE DATE(curtimestamp) = ?) AS t2 '.
                 'ON t1.tankid = t2.tankid AND TIMESTAMPDIFF(MINUTE, t1.curtimestamp, t2.curtimestamp) = 5 '.
                 'WHERE ABS(t1.volume - t2.volume) > 83.3) AS temp GROUP BY temp.tankid)';
        $values = array($date,$date,$date);

        try {
            $this->_db->execute($query, $values);
        } catch (Horde_Db_Exception $e) {
            throw new Superbatch_Exception($e);
        }
    }

    public function updateTankMeasure($id, $measurement) {
        $query = 'UPDATE tanks SET measured_inches = ? WHERE _kp_tankid = ?';
        $values = array($measurement, $id);

        try {
            $this->_db->update($query, $values);
        } catch (Horde_Db_Exception $e) {
            throw new Superbatch_Exception($e);
        }
    }

    public function insertTankHistoryMeasure($id) {
        $query = 'INSERT INTO tankhistorymeasure(time,user_id,tank_id,measured_inches,volume) ' .
                 'SELECT ?,?,_kp_tankid,measured_inches,measured_inches * Conversion FROM tanks ' .
                 'WHERE measured_inches > 0';
        $date = round(time() / (15 * 60)) * (15 * 60);
        $values = array(date('Y-m-d H:i', $date),$id);

        try {
            $this->_db->execute($query, $values);
        } catch (Horde_Db_Exception $e) {
            throw new Superbatch_Exception($e);
        }
    }
}
