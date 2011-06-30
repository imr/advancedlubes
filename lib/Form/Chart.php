<?php

class Superbatch_Form_Chart extends Horde_Form
{
    public function __construct($vars)
    {
        parent::__construct($vars, _("Select Chart Options"));

        $driver = $GLOBALS['injector']->getInstance('Superbatch_Driver');
        $tanks = $driver->listTanks();
        $tanks_enum[0] = "All";
        foreach ($tanks as $tank) {
            if ($tank['tanknum']) {
                $tanks_enum[$tank['_kp_tankid']] = $tank['tanknum'];
            }
        }
        if ($vars->get('tank') > 0) {
            $data_enum = array(
                'both' => 'Temp and Volume',
                'temp' => 'Temperature',
                'vol' => 'Volume'
            );
        } else {
            $data_enum = array(
                'temp' => 'Temperature',
                'vol' => 'Volume'
            );
        }
        $v = $this->addVariable(_("Tank Selection"), 'tank', 'enum', false, false, '', array($tanks_enum));
        $v->setAction(Horde_Form_Action::factory('reload'));
        $v = $this->addVariable(_("Data to graph"), 'data', 'enum', false, false, '',array($data_enum));
        $this->addVariable(_("Start Date"), 'time_start', 'monthdayyear', false);
        $this->addVariable(_("End Date"), 'time_end', 'monthdayyear', false);

        $this->setButtons(array(_("Display Chart")));
    }
}
