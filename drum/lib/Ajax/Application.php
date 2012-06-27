<?php

class Drum_Ajax_Application extends Horde_Core_Ajax_Application
{

    // notification information sent in response
    public $notify = true;

    //read only actions
    protected $_readOnly = array('','');

    public function __construct($app, $vars, $action = null)
    {
        parent::__construct($app, $vars, $action);
    }

    
}
