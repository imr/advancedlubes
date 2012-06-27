<?php

class WarehouseBaseTables extends Horde_Db_Migration_Base
{

    public function up()
    {
        $tableList = $this->tables();

        if (!in_array('warehouse_', $tableList)) {
            $t = $this->createTable('warehouse_'));

            $t->end();
        }

    }

    public function down()
    {
        $this->dropTable('warehouse_');
    }
}
