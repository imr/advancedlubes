<?php

class Horde_Core_Ui_VarRenderer_Superbatch extends Horde_Core_Ui_VarRenderer_Html
{
    protected function _renderVarInput_TankInventory($form, $var, $vars)
    {
        $varname = @htmlspecialchars($var->getVarName(), ENT_QUOTES, $this->_charset);
        $varvalue = $var->getValue($vars);

        printf('<input type="text" name="desc%s" id="%s" size="50" value="%s" %s />',
               $varname,
               $this->_genID($var->getVarName(), false),
               $varvalue[0],
               $this->_getActionScripts($form, $var)
        );
        printf('<input type="text" name="comp%s" id="%s" size="50" value="%s" %s />',
               $varname,
               $this->_genID($var->getVarName(), false),
               $varvalue[1],
               $this->_getActionScripts($form, $var)
        );
        printf('<input type="text" name="meas%s" id="%s" size="7" tabindex="1" value="%s" %s />',
               $varname,
               $this->_genID($var->getVarName(), false),
               $varvalue[2],
               $this->_getActionScripts($form, $var)
        );

    }
}
