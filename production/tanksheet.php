<?php

require_once dirname(__FILE__) . '/lib/Application.php';
Horde_Registry::appInit('production');

$super_driver = $GLOBALS['injector']->getInstance('Production_Factory_Driver')->create();
$tanks = $super_driver->listTanks();
$tanknotes = $super_driver->getNote();

$vars = Horde_Variables::getDefaultVariables();
$view = $vars->get('view');

if ($GLOBALS['production_perms']->hasPermission('production:tank sheet', $GLOBALS['registry']->getAuth(), Horde_Perms::READ)) {
    $html = '<table border="1px solid" width="100%" cellspacing=0 class="striped sortable"><thead><tr><th>Tank</th>' .
            '<th>Product</th><th colspan=2>Description</th>' .
            '<th align=right>Max Vol</th><th align=right>Conv</th>' .
            '<th align=right>Tap Min</th><th align=right>Inches</th>' .
            '<th align=right>Volume</th><th>Note</th></tr></thead><tbody>';
    foreach ($tanks as $tank) {
        $prev_volume = round($tank['Conversion'] * $tank['measured_inches']);
        $html .= "<tr>" .
                 "<td width='3%'class='leftAlign'>$tank[tanknum]</td>" .
                 "<td width='6%'>$tank[userproduct]</td>" .
                 "<td width='23%'>$tank[description]</td>" .
                 "<td width='18%'>$tank[compatibility]</td>" .
                 "<td width='4%' class='rightAlign'>$tank[capacity]</td>" .
                 "<td width='4%' class='rightAlign'>$tank[Conversion]</td>" .
                 "<td width='4%' class='rightAlign'>" . (int) $tank['tap_volume'] .
                 "<td width='5%' class='rightAlign'>$tank[measured_inches]</td>" .
                 "<td width='5%' class='rightAlign'>" . $prev_volume . "</td>" .
                 "<td width='29%'>$tank[note]</td></tr>";
    }
    $html .= "<tr><td>Notes:</td><td colspan=8>$tanknotes[note]</td><td>Revised $tanknotes[date] by $tanknotes[user_id]</td></tr></tbody></table>";
} else {
    $html .= "Reading tank sheet not authorized.";
}

if ($view == 'pdf') {
    $dompdf = new DOMPDF();
    $dompdf->load_html($html);
    $dompdf->render();
    $dompdf->stream("tanks.pdf");
        echo "<html><body>$html</body></html>";
} elseif ($view == 'simple') {
        require $registry->get('templates', 'production') . '/print-header.inc';
        $page_output->header();
        echo "$html</body></html>";
} else {
        $page_output->header();
        echo Horde::menu();
        $notification->notify(array('listeners' => 'status'));     
        echo Horde::link(Horde::url('tanksheet.php?view=simple'), _("Printer Friendly Sheet")) . 'Printer Friendly Sheet</a> | ';
        echo Horde::link(Horde::url('tanksheet.php?view=pdf'), _("View PDF")) . 'View PDF</a> | ';
        if ($GLOBALS['production_perms']->hasPermission('production:tank sheet', $GLOBALS['registry']->getAuth(), Horde_Perms::EDIT)) {
            echo Horde::link(Horde::url('tankmeasure.php'), _("Update Inventory")) . 'Update Inventory</a> | ';
        }
        echo Horde::link(Horde::url('inventorylist.php'), _("Inventory List")) . 'Inventory List</a><BR>';
        echo $html;
        $page_output->footer();
}
