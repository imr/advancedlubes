<?php

require_once dirname(__FILE__) . '/lib/Application.php';
Horde_Registry::appInit('production');

$vars = Horde_Variables::getDefaultVariables();
$form = new Production_Form_Fluctuation($vars);

$page_output->header();
echo Horde::menu();
if ($form->validate($vars)) {
       
    $id = $vars->get('tanks');
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
    $super_driver = $GLOBALS['injector']->getInstance('Production_Factory_Driver')->create();
    switch ($vars->get('display_type')) {
        case 0: // Recently Changed
            $recent = true;
            break;
        case 1: // All tanks
            $all = true;
            break;
        case 2: // Resource
            $typetoget = 'Resource';
            break;
        case 3: // Finish
            $typetoget = 'Finish';
            break;
        case 4: // Selection
            $tankstoget = $vars->get('tanks');
            break;
    }

    if ($recent) {
        $results = $super_driver->getTankFluxRecent($volume);
    } elseif ($all) {
        $results = $super_driver->getFluxbyDay($start_date, $volume);
    } else {
        $results = $super_driver->getTankFluxbyIds($id,$volume,$start_time,$end_time);
    }
    if (count($id) == 1) { //changes for one tank
        $tank = $super_driver->getTankNamefromId($id);
?>
<h3>Volume Changes for tank <?php echo $tank ?></h3>
<table width="100%" class="sortable" cellspacing=0>
  <thead>
    <tr class="control leftAlign">
      <th class="sortdown">Start Time</th><th>End Time</th><th>Product</th>
      <th class="rightAlign">Start Volume</th>
      <th class="rightAlign">End Volume</th>
      <th class="rightAlign">Volume Change</th>
      <th class="rightAlign">Rate of Change (gal/hour)</th>
    </tr>
  </thead>
  <tbody>
<?php
        foreach ($results as $result) {
            if ($result['startid'] == $oldend) { // same set
                $endts = $result['endtime'];
                $endvolume = $result['endvolume'];
                $counter++;
            } else { // new slope set
                $totalvolume = (int) ($endvolume - $startvolume);
                $rateofchange = (int) ($totalvolume * (12 / $counter));
                if ($endts && (abs($totalvolume) > $volume)) { //output latest row
                    echo "<tr><td>$startts</td><td>$endts</td><td>$product</td>" .
                         "<td class='rightAlign'>$startvolume</td><td class='rightAlign'>$endvolume</td>" .
                         "<td class='rightAlign'>$totalvolume</td><td class='rightAlign'>$rateofchange</td></tr>";
                }
                $startts = $result['starttime'];
                $endts = $result['endtime'];
                $startvolume = $result['startvolume'];
                $endvolume = $result['endvolume'];
                $product = $result['productcode'];
                $counter = 1;
            }
            $oldend = $result['endid'];
        }
        $totalvolume = (int) ($endvolume - $startvolume);
        $rateofchange = (int) ($totalvolume * (12 / $counter));
        if ($startts && (abs($totalvolume) > $volume)) { //output last row, if there is one
            echo "<tr><td>$startts</td><td>$endts</td><td>$product</td><td class='rightAlign'>$startvolume</td>" .
                 "<td class='rightAlign'>$endvolume<td class='rightAlign'>$totalvolume</td><td class='rightAlign'>$rateofchange</td></tr>";
        }
    } else { // volume changes for one day
?>
<h3>Volume Changes for <?php echo ($start_date == '0000') ? date('d/m/Y H:i:s') : $start_date?></h3>
<table width="100%" class="sortable" cellspacing=0">
  <thead>
    <tr class="control leftAlign"> 
      <th class="sortdown">Tank</th><th>Start Time</th><th>End Time</th><th>Product</th>
      <th class="rightAlign">Start Volume</th>
      <th class="rightAlign">End Volume</th>
      <th class="rightAlign">Volume Change</th>
      <th class="rightAlign">Rate of Change (gal/hour)</th>
    </tr>
  </thead>
  <tbody>
<?php
        foreach ($results as $result) {
            if (($result['startid'] == $oldend) && ($result['tanknum'] == $tankid)) { // same set
                $endts = $result['endtime'];
                $endvolume = $result['endvolume'];
                $counter++;
            } else { // new slope set
                $totalvolume = (int) ($endvolume - $startvolume);
                $rateofchange = (int) ($totalvolume * (12 / $counter));
                if ($endts && (abs($totalvolume) > $volume)) { //output latest row
                    echo "      <tr><td>$tankid</td><td>$startts</td><td>$endts</td><td>$product</td><td class='rightAlign'>$startvolume</td>".
                        "<td class='rightAlign'>$endvolume</td><td class='rightAlign'>$totalvolume</td><td class='rightAlign'>$rateofchange</td></tr>";
                }
                $tankid = $result['tanknum'];
                $startts = $result['starttime'];
                $endts = $result['endtime'];
                $startvolume = $result['startvolume'];
                $endvolume = $result['endvolume'];
                $product = $result['productcode'];
                $counter = 1;
            }
            $oldend = $result['endid'];
        }
        $totalvolume = (int) ($endvolume - $startvolume);
        $rateofchange = (int) ($totalvolume * (12 / $counter));
        if ($startts && (abs($totalvolume) > $volume)) { //output last row, if there is one
            echo "      <tr><td>$tankid</td><td>$startts</td><td>$endts</td><td>$product</td>" .
                 "<td class='rightAlign'>$startvolume</td><td class='rightAlign'>$endvolume</td>".
                 "<td class='rightAlign'>$totalvolume</td><td class='rightAlign'>$rateofchange</td></tr>";
        }
    }
}
?>
  </tbody>
</table>
</br>
<?php
$form->renderActive();
$page_output->footer();
