<?php
/**
 * Lab storage implementation for the Horde_Db database abstraction layer.
 *
 * Copyright 2012 Ian Roth
 *
 * @author   Ian Roth <iron_hat@hotmail.com>
 * @package  Lab
 */
class Lab_Driver_Sql extends Lab_Driver
{
    /**
     * Handle for the current database connection.
     *
     * @var Horde_Db_Adapter
     */
    protected $_db;

    /**
     * Storage variable.
     *
     * @var array
     */
    protected $_foo = array();

    /**
     * Constructs a new SQL storage object.
     *
     * @param array $params  Class parameters:
     *                       - db:    (Horde_Db_Adapater) A database handle.
     *                       - table: (string, optional) The name of the
     *                                database table.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $params = array())
    {
        if (!isset($params['db'])) {
            throw new InvalidArgumentException('Missing db parameter.');
        }
        $this->_db = $params['db'];
        unset($params['db']);

        parent::__construct($params);
    }

    public function getMaterial($id)
    {
        $query = 'SELECT * FROM lab_material' .
                 ' WHERE material_id = ?';

        try {
            $this->_db->selectOne($query, $id);
        } catch (Horde_Db_Exception $e) {
            throw new Lab_Exception($e->getMessage());
        }
    }

    public function listProducts()
    {

        $query = 'SELECT * FROM lab_material AS m INNER JOIN lab_product AS p ' .
            'ON m.id = p.material_id';

        try {
            $result = $this->_db->selectAll($query);
        } catch (Horde_Db_Exception $e) {
            throw new Lab_Exception($e->getMessage());
        }

        return $result;
    }
}
