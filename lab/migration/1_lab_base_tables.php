<?php
/**
 * Create Lab base tables.
 *
 * Copyright 2012 Ian Roth
 *
 * @author  Ian Roth <iron_hat@hotmail.com>
 * @package  Lab
 */
class LabBaseTables extends Horde_Db_Migration_Base
{
    /**
     * Upgrade
     */
    public function up()
    {
        $t = $this->createTable('lab_material');
        $t->column('name', 'string', array('limit' => 255, 'null' => false));
        $t->column('description', 'string', array('limit' => 255, 'null' => false));
        $t->end();

        $this->addIndex('lab_material', array('name'));

        $t = $this->createTable('lab_product');
        $t->column('material_id', 'integer', array('null' => false));
        $t->column('name', 'string', array('limit' => 256, 'null' => false));
        $t->column('description', 'string', array('limit' => 256, 'null' => false));
        $t->column('brand', 'string', array('limit' => 100, 'null' => false));
        $t->column('drupal_node', 'integer', array('null' => false));
        $t->column('status', 'integer', array('null' => false));
        $t->end();

        $this->addIndex('lab_product', array('name'));
        $this->addIndex('lab_product', array('brand'));

        $t = $this->createTable('lab_product_code', array('autoincrementKey' => false));
        $t->column('product_id', 'integer', array('null' => false));
        $t->column('code', 'string', array('limit' => 50, 'null' => false));
        $t->primaryKey(array('product_id', 'code'));
        $t->end();

        $this->addIndex('lab_product_code', array('code'));

        $t = $this->createTable('lab_pib');
        $t->column('drupal_node', 'integer');
        $t->column('title', 'string', array('limit' => 256, 'null' => false));
        $t->column('short_title', 'string', array('limit' => 256));
        $t->column('description', 'text');
        $t->column('type', 'string', array('limit' => 50, 'null' => false));
        $t->column('feature', 'boolean', array('null' => false));
        $t->column('approval_separate', 'boolean', array('null' => false));
        $t->end();

        $this->addIndex('lab_pib', array('drupal_node'));

        $t = $this->createTable('lab_pib_product', array('autoincrementKey' => false));
        $t->column('pib_id', 'integer', array('null' => false));
        $t->column('product_id', 'integer', array('null' => false));
        $t->primaryKey(array('pib_id', 'product_id'));
        $t->end();

        $t = $this->createTable('lab_raw_material');
        $t->column('material_id', 'integer', array('null' => false));
        $t->end();

        $t = $this->createTable('lab_formula');
        $t->column('material_id', 'integer', array('null' => false));
        $t->column('name', 'string', array('limit' => 256, 'null' => false));
        $t->column('notes', 'text');
        $t->end();

        $t = $this->createTable('lab_component', array('autoincrementKey' => false));
        $t->column('formula_id', 'integer', array('null' => false));
        $t->column('material_id', 'integer', array('null' => false));
        $t->column('component_percentage', 'decimal', array('percision' => 8, 'scale' => 4));
        $t->primaryKey(array('formula_id', 'material_id'));
        $t->end();

        $t = $this->createTable('lab_property');
        $t->column('name', 'string', array('limit' => 255, 'null' => false));
        $t->column('drupal_name', 'string', array('limit' => 255, 'null' => false));
        $t->end();

        $t = $this->createTable('lab_typical', array('autoincrementKey' => false));
        $t->column('material_id', 'integer', array('null' => false));
        $t->column('property_id', 'integer', array('null' => false));
        $t->column('pib_include', 'boolean', array('null' => false, 'default' => true));
        $t->column('value', 'string', array('limit' => 50, 'null' => false));
        $t->primaryKey(array('material_id', 'property_id'));
        $t->end();

        $t = $this->createTable('lab_test_param', array('autoincrementKey' => false));
        $t->column('material_id', 'integer', array('null' => false));
        $t->column('property_id', 'integer', array('null' => false));
        $t->column('cofa_include', 'boolean', array('null' => false, 'default' => true));
        $t->column('minimum', 'string', array('limit' => 50));
        $t->column('maximum', 'string', array('limit' => 50));
        $t->primaryKey(array('material_id', 'property_id'));
        $t->end();

        $t = $this->createTable('lab_test_result');
        $t->column('formula_id', 'integer');
        $t->column('raw_material_id', 'integer');
        $t->column('user_id', 'string', array('limit' => 255, 'null' => false));
        $t->column('time', 'datetime');
        $t->end();
    }

    /**
     * Downgrade
     */
    public function down()
    {
        $this->dropTable('lab_material');
        $this->dropTable('lab_product');
        $this->dropTable('lab_product_code');
        $this->dropTable('lab_pib');
        $this->dropTable('lab_pib_product');
        $this->dropTable('lab_raw_material');
        $this->dropTable('lab_formula');
        $this->dropTable('lab_component');
        $this->dropTable('lab_property');
        $this->dropTable('lab_typical');
        $this->dropTable('lab_test_param');
        $this->dropTable('lab_test_result');
    }
}
