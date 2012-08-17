<?php
/**
 * Lab_Driver defines an API for implementing storage backends for
 * Lab.
 *
 * Copyright 2012 Ian Roth
 *
 * @author  Ian Roth <iron_hat@hotmail.com>
 * @package Lab
 */
class Lab_Driver
{
    /**
     * Hash containing connection parameters.
     *
     * @var array
     */
    protected $_params = array();

    /**
     * Constructor.
     *
     * @param array $params  A hash containing connection parameters.
     */
    public function __construct($params = array())
    {
        $this->_params = $params;
    }

}
