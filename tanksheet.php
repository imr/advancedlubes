<?php

require_once dirname(__FILE__) . '/lib/Application.php';
require_once dirname(__FILE__) . '/dompdf/dompdf_config.inc.php';
Horde_Registry::appInit('superbatch');

Horde::addScriptFile('tables.js', 'horde');

$super_driver = $GLOBALS['injector']->getInstance('Superbatch_Factory_Driver')->create();
$tanks = $super_driver->listTanks();

$vars = Horde_Variables::getDefaultVariables();
$view = $vars->get('view');

$html = '<table border="1px solid" width="100%" cellspacing=0 class="striped sortable"><thead><tr><th>Tank</th>' .
        '<th>Product</th><th colspan=3>Description</th>' .
        '<th align=right>Max Vol</th><th align=right>Conv</th>' .
        '<th align=right>Tap Min</th><th align=right>Prev Vol</th>' .
        '<th align=right>Prev Inches</th><th align=right>New Value</th></tr></thead><tbody>';
foreach ($tanks as $tank) {
    $prev_volume = round($tank['Conversion'] * $tank['measured_inches']);
    $html .= "<tr>" .
             "<td width='3%'class='leftAlign'>$tank[tanknum]</td>" .
             "<td width='4%'>$tank[userproduct]</td>" .
             "<td width='25%'>$tank[description]</td>" .
             "<td width='18%'>$tank[compatibility]</td>" .
             "<td width='18%'>$tank[note]</td>" .
             "<td width='4%' class='rightAlign'>$tank[capacity]</td>" .
             "<td width='4%' class='rightAlign'>$tank[Conversion]</td>" .
             "<td width='4%' class='rightAlign'>" . (int) $tank['tap_volume'] .
             "<td width='5%' class='rightAlign'>" . $prev_volume . "</td>" .
             "<td width='5%' class='rightAlign'>$tank[measured_inches]</td>" .
             "<td width='11%' style='border-bottom:solid'>&nbsp;</td></tr>";
}
$html .= '</tbody></table>';
if ($view == 'pdf') {
    $dompdf = new DOMPDF();
    $dompdf->load_html($html);
    $dompdf->render();
    $dompdf->stream("tanks.pdf");
        echo '<html><body>' . $html;
} elseif ($view == 'simple') {
        require $registry->get('templates', 'horde') . '/common-header.inc';
        echo $html . '</body></html>';
} else {
        require $registry->get('templates', 'horde') . '/common-header.inc';
        echo Horde::menu();
        $notification->notify(array('listeners' => 'status'));     
        echo Horde::link(Horde::url('tanksheet.php?view=simple'), _("View Print Sheet")) . 'View Print Sheet</a> ';
        echo Horde::link(Horde::url('tanksheet.php?view=pdf'), _("View PDF")) . 'View PDF</a> ';
        echo Horde::link(Horde::url('tankmeasure.php'), _("Update Inventory")) . 'Update Inventory</a><BR>';
        echo $html;
        require $registry->get('templates', 'horde') . '/common-footer.inc';
}
