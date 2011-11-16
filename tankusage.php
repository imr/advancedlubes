<?php

require_once dirname(__FILE__) . '/lib/Application.php';
Horde_Registry::appInit('superbatch');

$vars = Horde_Variables::getDefaultVariables();
$form = new Superbatch_Form_TankUsage($vars);

Horde::addScriptFile('tables.js', 'horde');
require $registry->get('templates', 'horde') . '/common-header.inc';
echo Horde::menu();
if ($form->validate($vars)) {
       
    $week_start = $vars->get('week_start');
    $week_end = $vars->get('week_end');
    $week_total = $week_end - $week_start + 1;
    $super_driver = $GLOBALS['injector']->getInstance('Superbatch_Factory_Driver')->create();
    if ($vars->get('display_all') == false) { // Only want resource tanks
        $tankstoget = 'Resource';
    }
    $results = $super_driver->listTanks($tankstoget);
?>
<h3>Tank Usage for <?php echo $week_start ?> to <?php echo $week_end ?></h3>
<table width="100%" class="sortable" cellspacing=0">
  <thead>
    <tr class="control leftAlign"> 
      <th class="sortdown">Tank</th>
      <th>Average</th>
<?php
    for ($i = $week_end; $i>= $week_start; $i--) {
?>
      <th><?php echo $i ?></th>
<?php
    }
?>
    </tr>
  </thead>
  <tbody>
<?php

        foreach ($results as $result) {
            $row_results = $super_driver->getTankUsage($result['_kp_tankid'],$week_start,$week_end);
            $count_week = 0;
	    $top_row = '';
            $bottom_row = '';
            $increase_total = 0;
            $decrease_total = 0;
?>
    <tr class="">
      <td rowspan=2><?php echo $result['tanknum'] ?></td>
<?php
            foreach ($row_results as $row_data) {
                $top_row .= '<td>' . $row_data['increase'] . '</td>';
                $bottom_row .= '<td>' . $row_data['decrease'] . '</td>';
                $increase_total += $row_data['increase'];
                $decrease_total += $row_data['decrease'];
                $count_week++;
            }
            for ($j = $count_week; $j < $week_total; $j++) {
                $top_row .= '<td></td>';
                $bottom_row .= '<td></td>';
            }
            echo '<td>' . (int) ($increase_total / $count_week) . '</td>' . $top_row;
?>
    </tr>
    <tr>
<?php
            echo '<td>' . (int) ($decrease_total / $count_week) . '</td>' . $bottom_row;
?>
    </tr>
<?php
        }
}
?>
  </tbody>
</table>
</br>
<?php
$form->renderActive();
require $registry->get('templates', 'horde') . '/common-footer.inc';
