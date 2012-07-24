<?php
/**
 * Lab_Driver factory.
 *
 * Copyright 2012 Ian Roth
 *
 * @author   Ian Roth <iron_hat@hotmail.com>
 * @package  Lab
 */
class Lab_Factory_Driver extends Horde_Core_Factory_Injector
{
    /**
     * @var array
     */
    private $_instances = array();

    /**
     * Return an Lab_Driver instance.
     *
     * @return Lab_Driver
     */
    public function create(Horde_Injector $injector)
    {
        $driver = Horde_String::ucfirst($GLOBALS['conf']['storage']['driver']);
        $signature = serialize(array($driver, $GLOBALS['conf']['storage']['params']['driverconfig']));
        if (empty($this->_instances[$signature])) {
            switch ($driver) {
            case 'Sql':
                try {
                    if ($GLOBALS['conf']['storage']['params']['driverconfig'] == 'horde') {
                        $db = $GLOBALS['injector']->getInstance('Horde_Db_Adapter');
                    } else {
                        $db = $GLOBALS['injector']->getInstance('Horde_Core_Factory_Db')
                            ->create('lab', 'storage');
                    }
                } catch (Horde_Exception $e) {
                    throw new Lab_Exception($e);
                }
                $params = array('db' => $db);
                break;
            case 'Ldap':
                try {
                    $params = array('ldap' => $injector->getIntance('Horde_Core_Factory_Ldap')->create('lab', 'storage'));
                } catch (Horde_Exception $e) {
                    throw new Lab_Exception($e);
                }
                break;
            }
            $class = 'Lab_Driver_' . $driver;
            $this->_instances[$signature] = new $class($params);
        }

        return $this->_instances[$signature];
    }
}
