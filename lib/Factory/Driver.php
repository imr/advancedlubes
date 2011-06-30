<?php

class Superbatch_Factory_Driver
{
    private $_instances = array();

    private $_injector;

    public function __construct(Horde_Injector $injector)
    {
        $this->_injector = $injector;
    }

    public function create()
    {
        $driver = $GLOBALS['conf']['storage']['driver'];
        $signature = serialize(array($driver, $GLOBALS['conf']['storage']['params']['driverconfig']));
        if (empty($this->_instances[$signature])) {
            if ($driver == 'sql' && $GLOBALS['conf']['storage']['params']['driverconfig'] == 'horde') {
                $params = array('db_adapter' => $this->_injector->getInstance('Horde_Db_Adapter'));
            } else {
                throw new Horde_Exception('Using non-global db connection not yet supported.');
            }
            $class = 'Superbatch_Driver_' . Horde_String::ucfirst($driver);
            $this->_instances[$signature] = new $class($params);
        }

        return $this->_instances[$signature];
    }

}
