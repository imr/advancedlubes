<?php
/**
 * Lab AJAX application API.
 *
 * This file defines the AJAX actions provided by this module. The primary
 * AJAX endpoint is represented by horde/services/ajax.php but that handler
 * will call the module specific actions via the class defined here.
 *
 * Copyright 2012 Ian Roth
 *
 * @package Lab
 */
class Lab_Ajax_Application extends Horde_Core_Ajax_Application
{
    /**
     * Application specific initialization tasks should be done in here.
     */
    protected function _init()
    {
    }

}
