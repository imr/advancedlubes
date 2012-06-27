<?php

class Superbatch_Form_Chart extends Horde_Form
{
    public function __construct($vars)
    {
        parent::__construct($vars, _("Select Chart Options"));

        $tanks = $GLOBALS['injector']->getInstance('Superbatch_Factory_Driver')->create()->listTanks();
        foreach ($tanks as $tank) {
            if ($tank['tanknum']) {
                $tanks_enum[$tank['_kp_tankid']] = $tank['tanknum'];
            }
        }
        $data_enum = array(
            'both' => 'Temp and Volume',
            'temp' => 'Temperature',
            'vol' => 'Volume',
            'meas' => 'Measured Volume',
            'twovol' => 'Measured and Sensor Volume'
        );
        $this->addVariable(_("Tank Selection"), 'tank', 'multienum', false, false, '', array($tanks_enum));
        $this->addVariable(_("Select All"), 'tankall', 'boolean', false, false, '');
        $this->addVariable(_("Data to graph"), 'data', 'enum', false, false, '',array($data_enum));
        $this->addVariable(_("Start Date"), 'time_start', 'monthdayyear', false);
        $this->addVariable(_("End Date"), 'time_end', 'monthdayyear', false);

        $this->setButtons(array(_("Display Chart")));
    }
}
