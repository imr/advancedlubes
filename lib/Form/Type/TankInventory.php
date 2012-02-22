<?php

class Superbatch_Form_Type_TankInventory extends Horde_Form_Type
{
    public function getInfo(&$vars, &$var, &$info)
    {
    }

    public function isValid(&$var, &$vars, $value, &$message)
    {
        return true;
    }

    public function getTypeName()
    {
        return 'TankInventory';
    }
}
