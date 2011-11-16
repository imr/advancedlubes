<?php

class Superbatch_Form_TankUsage extends Horde_Form
{
    public function __construct($vars)
    {
        parent::__construct($vars, _("Select Tank Ussage Data"));

        $weeks = $GLOBALS['injector']->getInstance('Superbatch_Factory_Driver')->create()->listWeeks();
        foreach ($weeks as $week) {
            $weeks_enum[$week['week']] = $week['week'];
            $counter++;
        }

        $start = $vars->get('week_start');
        if (empty($start)) {
            $weeks2_enum = $weeks_enum;
        } else {
            $counter = $start;
            while (!empty($weeks_enum[$counter])) {
                $weeks2_enum[$counter] = $counter;
                $counter++;
            }
        }


        $v = $this->addVariable(_("Start Week"), 'week_start', 'enum', false, false, '', array($weeks_enum));
        $v->setAction(Horde_Form_Action::factory('reload'));
        $this->addVariable(_("End Week"), 'week_end', 'enum', false, false, '', array($weeks2_enum));

        $this->setButtons(array(_("Display Table")));
    }
}
