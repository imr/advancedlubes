<?php

class Superbatch_Form_TankMeasure extends Horde_Form
{
    public function __construct($vars)
    {
        parent::__construct($vars, _("Measured Inventory"));
        $display_enum = array('All', 'Resource', 'Finish', 'Selection');

        $tanks = $GLOBALS['injector']->getInstance('Superbatch_Factory_Driver')->create()->listTanks();

        foreach ($tanks as $tank) {
            $z = $this->addVariable(_($tank['tanknum']), $tank['_kp_tankid'], 'Superbatch:TankInventory', false);
            $z->setDefault(array($tank['description'], $tank['compatibility'], $tank['measured_inches']));
        }

        $this->setButtons(array(_("Complete Inventory")));
    }

    public function execute()
    {
        $super_driver = $GLOBALS['injector']->getInstance('Superbatch_Factory_Driver')->create();
        $id = 2;
        $descdata = $this->_vars->get("desc$id");
        $compdata = $this->_vars->get("comp$id");
        $measdata = $this->_vars->get("meas$id");
        while($measdata <> NULL) {
            $data[$id] = array($descdata,$compdata,$measdata);
            $id++;
            $descdata = $this->_vars->get("desc$id");
            $compdata = $this->_vars->get("comp$id");
            $measdata = $this->_vars->get("meas$id");
        }
        $super_driver->updateTankMeasure($data);
        $super_driver->insertTankHistoryMeasure($GLOBALS['registry']->getAuth());
    }

    public function renderActive()
    {
        return parent::renderActive(new Horde_Form_Renderer(array('varrenderer_driver' => array('superbatch', 'superbatch'))), $this->_vars, Horde::url('tankmeasure.php'), 'post');
    }
}
