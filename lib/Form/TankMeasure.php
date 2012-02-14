<?php

class Superbatch_Form_TankMeasure extends Horde_Form
{
    public function __construct($vars)
    {
        parent::__construct($vars, _("Measured Inventory"));
        $_default = array(); // Storage for defaults
        $display_enum = array('All', 'Resource', 'Finish', 'Selection');

        $tanks = $GLOBALS['injector']->getInstance('Superbatch_Factory_Driver')->create()->listTanks();

        foreach ($tanks as $tank) {
            $measure = $tank['measured_inches'];
            $z = $this->addVariable(_($tank['tanknum']), $tank['_kp_tankid'], 'number', false);
            $z->setDefault($measure);
            $_default[$tank['_kp_tankid']] = $measure;
        }

        $this->setButtons(array(_("Complete Inventory")));
    }

    public function execute()
    {
        $super_driver = $GLOBALS['injector']->getInstance('Superbatch_Factory_Driver')->create();
        $id = 2;
        $data = $this->_vars->get($id);
        while($data <> NULL) {
            if ($data <> $_default[$id]) {
                $super_driver->updateTankMeasure($id,$data);
            }
            $id++;
            $data = $this->_vars->get($id);
        }
        $super_driver->insertTankHistoryMeasure($GLOBALS['registry']->getAuth());
    }
}
