<?php

class Production_Form_TankMeasure extends Horde_Form
{
    public function __construct($vars)
    {
        parent::__construct($vars, _("Measured Inventory"));
        $display_enum = array('All', 'Resource', 'Finish', 'Selection');

        $super_driver = $GLOBALS['injector']->getInstance('Production_Factory_Driver')->create();
        $tanks = $super_driver->listTanks();
        $tanknotes = $super_driver->getNote();

        $this->addVariable(_('Record History'), 'history', 'boolean', true, false, false);
        foreach ($tanks as $tank) {
            $z = $this->addVariable(_($tank['tanknum']), $tank['_kp_tankid'], 'Production:TankInventory', false);
            $z->setDefault(array($tank['description'], $tank['compatibility'], $tank['note'], $tank['measured_inches'], $tank['userproduct']));
        }
        $x = $this->addVariable(_('Tank Sheet Notes'), 'tanknote', 'longtext', false, false, false, array(4, 60));
        $x->setDefault($tanknotes['note']);

        $this->setButtons(array(_("Complete Inventory")));
    }

    public function execute()
    {
        $super_driver = $GLOBALS['injector']->getInstance('Production_Factory_Driver')->create();
        $id = 2;
        $descdata = $this->_vars->get("desc$id");
        $compdata = $this->_vars->get("comp$id");
        $notedata = $this->_vars->get("note$id");
        $proddata = $this->_vars->get("prod$id");
        $measdata = $this->_vars->get("meas$id");
        while($measdata <> NULL) {
            $data[$id] = array($descdata,$compdata,$notedata,$proddata,$measdata);
            $id++;
            $descdata = $this->_vars->get("desc$id");
            $compdata = $this->_vars->get("comp$id");
            $notedata = $this->_vars->get("note$id");
            $proddata = $this->_vars->get("prod$id");
            $measdata = $this->_vars->get("meas$id");
        }
        $super_driver->updateTankMeasure($data);
        $super_driver->insertNote($GLOBALS['registry']->getAuth(), $this->_vars->get('tanknote'));
        if ($this->_vars->get('history') == true) {
            $super_driver->insertTankHistoryMeasure($GLOBALS['registry']->getAuth());
        }
    }

    public function renderActive()
    {
        return parent::renderActive(new Horde_Form_Renderer(array('varrenderer_driver' => array('production', 'production'))), $this->_vars, Horde::url('tankmeasure.php'), 'post');
    }
}
