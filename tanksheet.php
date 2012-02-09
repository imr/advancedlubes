<?php

require_once dirname(__FILE__) . '/lib/Application.php';
require_once dirname(__FILE__) . '/dompdf/dompdf_config.inc.php';
Horde_Registry::appInit('superbatch');

Horde::addScriptFile('tables.js', 'horde');

$super_driver = $GLOBALS['injector']->getInstance('Superbatch_Factory_Driver')->create();
$tanks = $super_driver->listTanks();
$rowOdd = 'rowEven';

$vars = Horde_Variables::getDefaultVariables();
$view = $vars->get('view');
/*
if ($view == 'pdf') {
    try {
        $wkhtmltopdf = new Wkhtmltopdf(array('path' => './uploads'));
        $wkhtmltopdf->setTitle("Tank Inventory Sheet");
        $wkhtmltopdf->setHTML(file_get_contents("http://example.com"));
        $wkhtmltopdf->output(Wkhtmltopdf::MODE_DOWNLOAD, "tanks.pdf");
        exit;
    } catch (Exception $e) {
        $notification->push($e->getMessage());
    }
} */
$html = '<table width="100%" cellspacing=0><thead><tr><th>Tank</th><th>Description</th>' .
        '<th align=right>Max Capacity</th><th align=right>Previous Volume</th>' .
        '<th align=right>Previous Measurement</th><th align=right>New Value</th></tr></thead><tbody>';
foreach ($tanks as $tank) {
    $html .= "<tr class='$rowOdd'>" .
             "<td rowspan=2 align=center>$tank[tanknum]</td>" .
             "<td>$tank[description]</td>" .
             "<td rowspan=2 class='rightAlign'>$tank[capacity]</td>" .
             "<td rowspan=2 class='rightAlign'>" . $tank['Conversion'] * $tank['measured_inches'] . "</td>" .
             "<td rowspan=2 class='rightAlign'>$tank[measured_inches]</td>" .
             "<td rowspan=2 style='border-bottom:solid'>&nbsp;</td></tr>" .
             "<tr class='$rowOdd'>" .
             "<td>$tank[compatibility]</td></tr>";
    if ($rowOdd == 'rowOdd') {
        $rowOdd = 'rowEven';
    } else {
        $rowOdd = 'rowOdd';
    }
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
        echo Horde::link(Horde::url('tanksheet.php?view=pdf'), _("View PDF")) . 'View PDF</a><BR><BR>';
        echo Horde::link(Horde::url('tankmeasure.php'), _("Update Inventory")) . 'Update Inventory</a><BR>';
        echo $html;
        require $registry->get('templates', 'horde') . '/common-footer.inc';
}
