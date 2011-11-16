<?php

class Superbatch_Form_TankUsage extends Horde_Form
{
    public function __construct($vars)
    {
        parent::__construct($vars, _("Select Tank Ussage Data"));

        $weeks = $GLOBALS['injector']->getInstance('Superbatch_Factory_Driver')->create()->listWeeks();
        foreach ($weeks as $week) {
            $weeks_enum[$counter] = $week['week'];
            $counter++;
        }
        $this->addVariable(_("Start Week"), 'week_start', 'enum', false, false, '', array($weeks_enum));
        $this->addVariable(_("End Week"), 'week_end', 'enum', false, false, '', array($weeks_enum));

        $this->setButtons(array(_("Display Table")));
    }
}
