<?php

class DrumBaseTables extends Horde_Db_Migration_Base
{

    public function up()
    {
        $tableList = $this->tables();

        if (!in_array('drum_palet', $tableList)) {
            $t = $this->createTable('drum_palet'));
            $t->column('order', 'integer', array('null' => false, 'default' => 0));
            $t->column('weightstart', 'float');
            $t->column('weightafter1', 'float');
            $t->column('weightafter2', 'float');
            $t->column('weightafter3', 'float');
            $t->column('weightend', 'float');
            $t->column('timestart', 'integer', array('null' => false, 'default' => 0));
            $t->column('time1', 'integer', array('null' => false, 'default' => 0));
            $t->column('time2', 'integer', array('null' => false, 'default' => 0));
            $t->column('time3', 'integer', array('null' => false, 'default' => 0));
            $t->column('timeend', 'integer', array('null' => false, 'default' => 0));

            $t->end();

            $this->addIndex('drum_palet', array('order'));
        }

        if (!in_array('drum_', $tableList)) {
        }
    }

    public function down()
    {
        $this->dropTable('drum_palet');
    }
}
