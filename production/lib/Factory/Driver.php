<?php

class Production_Factory_Driver
{
    private $_instances = array();

    private $_injector;

    public function __construct(Horde_Injector $injector)
    {
        $this->_injector = $injector;
    }

    public function create($name = '')
    {
        if (!isset($this->_instances[$name])) {
            $driver = $GLOBALS['conf']['storage']['driver'];
            $params = Horde::getDriverConfig('storage', $driver);
            $class = 'Production_Driver_' . ucfirst(basename($driver));
            if (!class_exists($class)) {
                throw new Production_Exception(sprintf('Unable to load the definition of %s.', $class));
            }

            switch ($class) {
            case 'Production_Driver_Sql':
                $params['db'] = $this->_injector->getInstance('Horde_Core_Factory_Db')->create('production', $params);
                break;
            }
            $driver = new $class($name, $params);
            $this->_instances[$name] = $driver;
        }

        return $this->_instances[$name];
    }
}
