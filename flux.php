<?php

require_once dirname(__FILE__) . '/lib/Application.php';
Horde_Registry::appInit('superbatch');

$vars = Horde_Variables::getDefaultVariables();
$form = new Superbatch_Form_Fluctuation($vars);

Horde::addScriptFile('tables.js', 'horde');
require $registry->get('templates', 'horde') . '/common-header.inc';
echo Horde::menu();
if ($form->validate($vars)) {
       
    $id = $vars->get('tank');
    $volume = $vars->get('volume') / 12; //change gallon per hour to gallon per 5 minute increment
    $start_array = $vars->get('time_start');
    $end_array = $vars->get('time_end');
    $start_year = empty($start_array['year']) ? null : $start_array['year'];
    $start_month = empty($start_array['month']) ? null : $start_array['month'];
    $start_day = empty($start_array['day']) ? null : $start_array['day'];
    $start_time = (int)strtotime("$start_month/$start_day/$start_year");
    $end_year = empty($end_array['year']) ? null : $end_array['year'];
    $end_month = empty($end_array['month']) ? null : $end_array['month'];
    $end_day = empty($end_array['day']) ? null: $end_array['day'];
    $end_time = (int)strtotime("$end_month/$end_day/$end_year");
    $start_date = $start_year . str_pad($start_month, 2, "0", STR_PAD_LEFT) . str_pad($start_day, 2, "0", STR_PAD_LEFT);
    $super_driver = $GLOBALS['injector']->getInstance('Superbatch_Factory_Driver')->create();

    if ($id) { //changes for one tank
        $results = $super_driver->getTankFluxbyId($id,$volume,$start_time,$end_time);
        $tank = $super_driver->getTankNamefromId($id);
?>
<h3>Volume Changes for tank <?php echo $tank ?></h3>
<table width="100%" class="sortable" cellspacing=0>
  <thead>
    <tr class="control leftAlign">
      <th class="sortdown">Start Time</th><th>End Time</th><th>Start Volume</th><th>End Volume</th><th>Volume Change</th><th>Product</th>
    </tr>
  </thead>
  <tbody>
<?php
        foreach ($results as $result) {
            if ($result['startid'] == $oldend) { // same set
                $endts = $result['endtime'];
                $endvolume = $result['endvolume'];
            } else { // new slope set
                $totalvolume = $endvolume - $startvolume;
                if ($endts && (abs($totalvolume) > $volume)) { //output latest row
                    echo '<tr><td>' . $startts . '</td><td>' . $endts . '</td><td>' . $startvolume . '</td><td>' . $endvolume .
                        '</td><td>' . $totalvolume . '</td><td>' . $product . '</td></tr>';
                }
                $startts = $result['starttime'];
                $endts = $result['endtime'];
                $startvolume = $result['startvolume'];
                $endvolume = $result['endvolume'];
                $product = $result['productcode'];
            }
            $oldend = $result['endid'];
        }
        $totalvolume = $endvolume - $startvolume;
        if ($startts && (abs($totalvolume) > $volume)) { //output last row, if there is one
            echo '<tr><td>' . $startts . '</td><td>' . $endts . '</td><td>' . $startvolume . '</td><td>' . $endvolume .
                '</td><td>' . $totalvolume . '</td><td>' . $product . '</td></tr>';
        }
    } else { // volume changes for one day
        $results = $super_driver->getFluxbyDay($start_date,$volume);
?>
<h3>Volume Changes for <?php echo $start_date ?></h3>
<table width="100%" class="sortable" cellspacing=0">
  <thead>
    <tr class="control leftAlign"> 
      <th class="sortdown">Tank</th><th>Start Time</th><th>End Time</th><th>Start Volume</th><th>End Volume</th><th>Volume Change</th><th>Product</th>
    </tr>
  </thead>
  <tbody>
<?php
        foreach ($results as $result) {
            if (($result['startid'] == $oldend) && ($result['tanknum'] == $tankid)) { // same set
                $endts = $result['endtime'];
                $endvolume = $result['endvolume'];
            } else { // new slope set
                $totalvolume = $endvolume - $startvolume;
                if ($endts && (abs($totalvolume) > $volume)) { //output latest row
                    echo '      <tr><td>' . $tankid . '</td><td>' . $startts . '</td><td>' . $endts . '</td><td>' . $startvolume . '</td><td>' . $endvolume .
                        '</td><td>' . $totalvolume . '</td><td>' . $product . '</td></tr>';
                }
                $tankid = $result['tanknum'];
                $startts = $result['starttime'];
                $endts = $result['endtime'];
                $startvolume = $result['startvolume'];
                $endvolume = $result['endvolume'];
                $product = $result['productcode'];
            }
            $oldend = $result['endid'];
        }
        $totalvolume = $endvolume - $startvolume;
        if ($startts && (abs($totalvolume) > $volume)) { //output last row, if there is one
            echo '      <tr><td>' . $tankid . '</td><td>' . $startts . '</td><td>' . $endts . '</td><td>' . $startvolume . '</td><td>' . $endvolume .
                '</td><td>' . $totalvolume . '</td><td>' . $product . '</td></tr>';
        }
    }
}
?>
  </tbody>
</table>
</br>
<?php
$form->renderActive();
require $registry->get('templates', 'horde') . '/common-footer.inc';
