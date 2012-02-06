<?php

class Superbatch_Form_TankMeasure extends Horde_Form
{
    public function __construct($vars)
    {
        parent::__construct($vars, _("Measured Inventory"));
        $display_enum = array('All', 'Resource', 'Finish', 'Selection');

        $tanks = $GLOBALS['injector']->getInstance('Superbatch_Factory_Driver')->create()->listTanks();

        foreach ($tanks as $tank) {
            $z = $this->addVariable(_($tank['tanknum']), $tank['_kp_tankid'], 'number', false);
            $z->setDefault($tank['measured_inches']);
        }

        $this->setButtons(array(_("Complete Inventory")));
    }
}
