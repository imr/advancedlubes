<?php

class Warehouse
{
    static public function showAjaxView()
    {
        global $prefs, $session;

        $mode = $session->get('horde', 'mode');
        return ($mode == 'dynamic' || ($prefs->getValue('dynamic_view') && $mode == 'auto')) && Horde::ajaxAvailable();
    }
}
