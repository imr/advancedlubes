<?php

class Superbatch_Form_TankUsage extends Horde_Form
{
    public function __construct($vars)
    {
        parent::__construct($vars, _("Select Tank Usage Data"));
        $display_enum = array('All', 'Resource', 'Finish', 'Selection');

        $weeks = $GLOBALS['injector']->getInstance('Superbatch_Factory_Driver')->create()->listTankWeeks();
        foreach ($weeks as $week) {
            $weeks_enum[$week['week']] = $week['week'];
        }

        $start = $vars->get('week_start');
        if (empty($start)) {
            $weeks2_enum = $weeks_enum;
        } else {
            foreach ($weeks as $week) {
                if ($week['week'] >= $start) {
                    $weeks2_enum[$week['week']] = $week['week'];
                }
            }
        }

        $tanks = $GLOBALS['injector']->getInstance('Superbatch_Factory_Driver')->create()->listTanks();
        foreach ($tanks as $tank) {
            if ($tank['tanknum']) {
                $tanks_enum[$tank['_kp_tankid']] = $tank['tanknum'];
            }
        }
        $x = $this->addVariable(_("Tank Display"), 'display_type', 'radio', false, false, false, array($display_enum));
        $x->setDefault(0);
        $x->setAction(Horde_Form_Action::factory('reload'));
        if ($vars->get('display_type') == 3) {
            $this->addVariable(_("Tanks"), 'tanks', 'multienum', false, false, false, array($tanks_enum));
        }
        $v = $this->addVariable(_("Start Week"), 'week_start', 'enum', false, false, '', array($weeks_enum));
        $v->setAction(Horde_Form_Action::factory('reload'));
        $this->addVariable(_("End Week"), 'week_end', 'enum', false, false, '', array($weeks2_enum));

        $this->setButtons(array(_("Display Table")));
    }
}
