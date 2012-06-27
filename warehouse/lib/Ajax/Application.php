<?php

class Warehouse_Ajax_Application extends Horde_Core_Ajax_Application
{
    /**
     * Determines if notification information is sent in response.
     *
     * @var boolean
     */
    public $notify = true;

    /**
     * Constructor.
     *
     * @param string $app     The application name.
     * @param string $action  The AJAX action to perform.
     */
    public function __construct($app, $vars, $action = null)
    {
        parent::__construct($app, $vars, $action);
        $this->_defaultDomain = empty($GLOBALS['conf']['storage']['default_domain']) ? null : $GLOBALS['conf']['storage']['default_domain'];
    }

    /**
     * Just polls for alarm messages and keeps session fresh for now.
     */
    public function poll()
    {
        return false;
    }
}
