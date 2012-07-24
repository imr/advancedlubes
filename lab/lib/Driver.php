<?php
/**
 * Lab_Driver defines an API for implementing storage backends for
 * Lab.
 *
 * Copyright 2007-2012 Ian Roth
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
     * Array holding the current foo list. Each array entry is a hash
     * describing a foo. The array is indexed by the IDs.
     *
     * @var array
     */
    protected $_foos = array();

    /**
     * Constructor.
     *
     * @param array $params  A hash containing connection parameters.
     */
    public function __construct($params = array())
    {
        $this->_params = $params;
    }

    abstract function getMaterial($id);

    abstract function listProducts();
}  
