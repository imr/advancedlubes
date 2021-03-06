<?php

class Production_Driver_Sql extends Production_Driver
{
    protected $_params = array();

    protected $_db;

    public function __construct($name, $params = array())
    {
	$this->_db = $params['db'];
    }

    public function listTanks($type = NULL, $tanks = array())
    {
        $query = 'SELECT _kp_tankid, tanknum, description, compatibility, note, userproduct, ' .
                 'capacity, currentvolume AS volume, Conversion, measured_inches, tap_inches, tap_volume ' .
                 'FROM tanks WHERE tanknum IS NOT NULL';
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
        $query .= ' ORDER BY TankOrderInventory';
        /* Execute the query. */
        try {
            $rows = $this->_db->selectAll($query);
        } catch (Horde_Db_Exception $e) {
            throw new Production_Exception($e);
        }

        return $rows;
    }

    public function listNotes()
    {
        $query = 'SELECT date, user_id, note FROM tanknote ORDER BY date DESC';

        try {
            $rows = $this->_db->selectAll($query);
        } catch (Horde_Db_Exception $e) {
            throw new Production_Exception($e);
        }
        return $rows;
    }
    public function getNote()
    {
        $query = 'SELECT date, user_id, note FROM tanknote ORDER BY date DESC';

        try {
            $row = $this->_db->selectOne($query);
        } catch (Horde_Db_Exception $e) {
            throw new Production_Exception($e);
        }
        return $row;
    }

    public function insertNote($user, $note)
    {
        $query = 'INSERT INTO tanknote(user_id, note) VALUES (?,?)';
        $values = array($user, $note);

        try {
            $rows = $this->_db->execute($query, $values);
        } catch (Horde_Db_Exception $e) {
            throw new Production_Exception($e);
        }
    }

    public function listMaterialWeeks()
    {
        $query = 'SELECT DISTINCT YEARWEEK(date) AS week FROM materialusage';

        /* Execute the query. */
        try {
            $rows = $this->_db->selectAll($query);
        } catch (Horde_Db_Exception $e) {
            throw new Production_Exception($e);
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
            throw new Production_Exception($e);
        }

        return $rows;
    }

    public function listInventories()
    {
        $query = 'SELECT DISTINCT time,user_id FROM tankhistorymeasure';
                 'GROUP BY time, user_id';

        /* Execute the query. */
        try {
            $rows = $this->_db->selectAll($query);
        } catch (Horde_Db_Exception $e) {
            throw new Production_Exception($e);
        }
        return $rows;
    }

    public function getTanksHistorybyIds($id = array(), $start_time = 1, $end_time) {
        if (!$end_time) {
            $end_time = time();
        } 
        $values = array();

        $query = 'SELECT t.tanknum, th.curtimestamp as timeunix, p.productCode as productcode, ' .
            'th.volume as volume, th.temperature as temperature from tankhistory th ' .
            'INNER JOIN tanks t ON th.tankid = t._kp_tankid ' .
            'LEFT JOIN products p ON th.productid = p._kp_Products ';
        if (!empty($id)) {
            $query .= 'WHERE th.tankid IN (?';
            for ($i = 0;count($id) - 1 > $i; $i++) {
                $query .= ',?';
            }
            $values = $id;
            $query .= ') AND ';
        } else {
            $query .= 'WHERE ';
        }
        $query .= 'th.curtimestamp BETWEEN FROM_UNIXTIME(?) ' .
            'AND FROM_UNIXTIME(?) ORDER BY th.tankid, th.curtimestamp';
        $values[] = $start_time;
        $values[] = $end_time;
        try {
            $rows = $this->_db->selectAll($query, $values);
        } catch (Horde_Db_Exception $e) {
            throw new Production_Exception($e);
        }
        return $rows;
    }

    public function getTanksHistoryMeasurebyIds($id = array(), $start_time = 1, $end_time) {
        if (!$end_time) {
            $end_time = time();
        } 

        $query = 'SELECT t.tanknum, thm.time as timeunix, p.productCode as productcode, ' .
                 'thm.volume as volume FROM tankhistorymeasure thm ' .
                 'INNER JOIN tanks t ON thm.tank_id = t._kp_tankid ' .
                 'LEFT JOIN products p ON thm.product_id = p._kp_Products ';
        if (!empty($id)) {
            $query .= 'WHERE thm.tank_id IN (?';
            for ($i = 0;count($id) - 1 > $i; $i++) {
                $query .= ',?';
            }
            $values = $id;
            $query .= ') AND ';
        } else {
            $query .= 'WHERE ';
            $values = array();
        }
        $query .= 'thm.time BETWEEN FROM_UNIXTIME(?) ' .
            'AND FROM_UNIXTIME(?) ORDER BY thm.tank_id, thm.time';
        $values[] = $start_time;
        $values[] = $end_time;
        try {
            $rows = $this->_db->selectAll($query, $values);
        } catch (Horde_Db_Exception $e) {
            throw new Production_Exception($e);
        }
        return $rows;
    }

    public function getTanksHistoriesbyIds($id = array(), $start_time = 1, $end_time) {
        if (!$end_time) {
            $end_time = time();
        }
        $values = array();

        $query = 'SELECT t.tanknum, th.curtimestamp as timeunix, p.productCode as productcode, ' .
                 'thm.volume as measured, th.volume as sensor, th.temperature as temperature ' .
                 'FROM tankhistory th INNER JOIN tanks t ON th.tankid = t._kp_tankid ' .
                 'LEFT OUTER JOIN tankhistorymeasure thm ON thm.tank_id = th.tankid ' .
                 'AND thm.time = th.curtimestamp LEFT JOIN products p ON th.productid = p._kp_Products ';
        if (!empty($id)) {
            $query .= 'WHERE th.tankid IN (?';
            for ($i = 0;count($id) - 1 > $i; $i++) {
                $query .= ',?';
            }
            $values = $id;
            $query .= ') AND ';
        } else {
            $query .= 'WHERE ';
        }
        $query .= 'th.curtimestamp BETWEEN FROM_UNIXTIME(?) ' .
                  'AND FROM_UNIXTIME(?) ORDER BY th.tankid, th.curtimestamp';
        $values[] = $start_time;
        $values[] = $end_time;
        try {
            $rows = $this->_db->selectAll($query, $values);
        } catch (Horde_Db_Exception $e) {
            throw new Production_Exception($e);
        }
        return $rows;

    }

    public function getTankFluxbyIds($id = array(), $volume = 16, $start_time = 1, $end_time) { //volume is gallons per 5 or 6 minute interval
        if (!$end_time)
            $end_time = time();
        
        if (!empty($id)) {
            $where = 'WHERE tankid IN (?';
            for ($i = 0;count($id) - 1 > $i; $i++) {
                $where .= ',?';
            }
            $values = array_merge($id, array($start_time, $end_time), $id, array($start_time, $end_time, $volume));
            $where .= ') AND ';
        } else {
            $values = array($start_time, $end_time, $start_time, $end_time, $volume);
            $where = 'WHERE ';
        }
        $where .= 'curtimestamp BETWEEN FROM_UNIXTIME(?) AND FROM_UNIXTIME(?)';
        $query = 'SELECT tanknum, t1._kp_tankhistoryid AS startid, t2._kp_tankhistoryid AS endid, t1.volume AS startvolume, t2.volume AS endvolume,' .
                     ' t1.curtimestamp AS starttime, t2.curtimestamp as endtime, productcode FROM (SELECT * from tankhistory' .
                     " $where) AS t1 INNER JOIN" .
                     " (SELECT * from tankhistory $where)" .
                     ' AS t2 ON t1.tankid = t2.tankid AND TIMESTAMPDIFF(MINUTE, t1.curtimestamp, t2.curtimestamp) = 5' .
                     ' LEFT JOIN products on t1.productid = products._kp_Products' .
                     ' INNER JOIN tanks ON t1.tankid = tanks._kp_tankid' .
                     ' WHERE ABS(t1.volume - t2.volume) > ? ORDER BY t1._kp_tankhistoryid';
        try {
            $rows = $this->_db->selectAll($query, $values);
        } catch (Horde_Db_Exception $e) {
            throw new Production_Exception($e);
        }
        return $rows;
    }

    public function getTankFluxRecent($volume) {
        $query = 'SELECT tanknum, t1._kp_tankhistoryid AS startid, t2._kp_tankhistoryid AS endid, ' .
                 't1.volume AS startvolume, t2.volume AS endvolume, ' .
                 't1.curtimestamp AS starttime, t2.curtimestamp as endtime, productcode FROM (SELECT * from tankhistory ' .
                 'WHERE TIMESTAMPDIFF(MINUTE, curtimestamp, NOW()) < 15) AS t1 INNER JOIN ' .
                 '(SELECT * from tankhistory WHERE TIMESTAMPDIFF(MINUTE, curtimestamp, NOW()) < 15) AS t2 ' .
                 'ON t1.tankid = t2.tankid AND TIMESTAMPDIFF(MINUTE, t1.curtimestamp, t2.curtimestamp) = 5 ' .
                 'LEFT JOIN products on t1.productid = products._kp_Products ' .
                 'INNER JOIN tanks on t1.tankid = tanks._kp_tankid ' .
                 'WHERE ABS(t1.volume - t2.volume) > ? ORDER BY t1.tankid, t1._kp_tankhistoryid ';

        try {
            $rows = $this->_db->selectAll($query, array($volume));
        } catch (Horde_Db_Exception $e) {
            throw new Production_Exception($e);
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
            throw new Production_Exception($e);
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
            throw new Production_Exception($e);
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
            throw new Production_Exception($e);
        }
        return $rows;
    }

    public function getTankNamefromId($id) {
        $query = 'SELECT tanknum FROM tanks WHERE _kp_tankid = ?';
	$values = array($id);
        try {
            $name = $this->_db->selectValue($query, $values);
        } catch (Horde_Db_Exception $e) {
            throw new Production_Exception($e);
        }
        return $name;
    }

    public function getCurrentWeekYear() {
        $query = 'SELECT WEEKYEAR(NOW())';
        try {
            $row = $this->_db->selectValue($query);
        } catch (Horde_Db_Exception $e) {
            throw new Production_Exception($e);
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
            throw new Production_Exception($e);
        }
    }

    public function updateTankMeasure($data) {
        $total = count($data);
        $when = NULL;
        $in = NULL;
        
        for($i = 0; $i < $total; $i++) {
            $when .= 'WHEN ? THEN ? ';
            $in .= '?,';
        }
        $in = substr($in, 0, -1);
        foreach ($data as $id => $value) {
            $description[] = $id;
            $description[] = $value[0];
            $compatibility[] = $id;
            $compatibility[] = $value[1];
            $note[] = $id;
            $note[] = $value[2];
            $userproduct[] = $id;
            $userproduct[] = $value[3];
            $measurement[] = $id;
            $measurement[] = $value[4];
        }
        $query = "UPDATE tanks SET description = CASE _kp_tankid $when" . 
                 "END, compatibility = CASE _kp_tankid $when" .
                 "END, note = CASE _kp_tankid $when" .
                 "END, userproduct = CASE _kp_tankid $when" .
                 "END, measured_inches = CASE _kp_tankid $when " .
                 "END WHERE _kp_tankid IN ($in)";
        
        $values = array_merge($description, $compatibility, $note, $userproduct, $measurement, array_keys($data));
        try {
            $this->_db->update($query, $values);
        } catch (Horde_Db_Exception $e) {
            throw new Production_Exception($e);
        }
    }

    public function insertTankHistoryMeasure($id) {
        $query = 'INSERT INTO tankhistorymeasure(time,user_id,tank_id,product_id, ' .
                 'description, measured_inches,volume) ' .
                 'SELECT ?,?,_kp_tankid,_kp_Products,description,measured_inches,measured_inches * Conversion + Cone ' .
                 'FROM tanks LEFT OUTER JOIN products on currentcontents = productcode ' .
                 'WHERE _kp_tankid BETWEEN 2 AND 117';
        $date = round(time() / (15 * 60)) * (15 * 60);
        $values = array(date('Y-m-d H:i', $date),$id);

        try {
            $this->_db->execute($query, $values);
        } catch (Horde_Db_Exception $e) {
            throw new Production_Exception($e);
        }
    }
}
