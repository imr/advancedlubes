<?php

class Superbatch_Form_Fluctuation extends Horde_Form
{
    public function __construct($vars)
    {
        parent::__construct($vars, _("Select Fluctuation Data"));
        $display_enum = array('Changing Now', 'All (for a single day)', 'Resource', 'Finish', 'Selection');

        $tanks = $GLOBALS['injector']->getInstance('Superbatch_Factory_Driver')->create()->listTanks();
        foreach ($tanks as $tank) {
            if ($tank['tanknum']) {
                $tanks_enum[$tank['_kp_tankid']] = $tank['tanknum'];
            }
        }
        $x = $this->addVariable(_("Tank Display"), 'display_type', 'radio', false, false, false, array($display_enum));
        $x->setDefault(0);
        $x->setAction(Horde_Form_Action::factory('reload'));
        if ($vars->get('display_type') == 4) {
            $this->addVariable(_("Tanks"), 'tanks', 'multienum', true, false, false, array($tanks_enum));
        }
        if ($vars->get('display_type') > 0) {
            $this->addVariable(_("Start Date"), 'time_start', 'monthdayyear', true);
            $this->addVariable(_("End Date"), 'time_end', 'monthdayyear', false);
        }
        $this->addVariable(_("Gallons per hour"), 'volume', 'int', false);

        $this->setButtons(array(_("Display Table")));
    }
}
