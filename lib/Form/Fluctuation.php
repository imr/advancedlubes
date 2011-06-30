<?php

class Superbatch_Form_Fluctuation extends Horde_Form
{
    public function __construct($vars)
    {
        parent::__construct($vars, _("Select Fluctuation Data"));

        $driver = $GLOBALS['injector']->getInstance('Superbatch_Driver');
        $tanks = $driver->listTanks();
        $tanks_enum[0] = "All (start date required)";
        foreach ($tanks as $tank) {
            if ($tank['tanknum']) {
                $tanks_enum[$tank['_kp_tankid']] = $tank['tanknum'];
            }
        }
        $this->addVariable(_("Tank Selection"), 'tank', 'enum', false, false, '', array($tanks_enum));
        $this->addVariable(_("Start Date"), 'time_start', 'monthdayyear', false);
        $this->addVariable(_("End Date"), 'time_end', 'monthdayyear', false);
        $this->addVariable(_("Gallons per hour"), 'volume', 'int', false);

        $this->setButtons(array(_("Display Table")));
    }
}
