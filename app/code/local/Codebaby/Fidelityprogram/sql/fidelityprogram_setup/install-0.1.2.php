<?php
 
$installer = $this;
 
$installer->startSetup();
 
$table = $installer->getConnection()
    ->newTable($installer->getTable('fidelityprogram/fidelitycouponcodebaby'))
    ->addColumn('coupon_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ), 'Customer Id')
    ->addColumn('codigo_coupon', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
        ), 'Coupon Code')
    ->addColumn('is_cupom_used', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        ), 'Is Coupon Used?')
    ->addColumn('date_used_cupom', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        'nullable'  => false,
        ), 'Date Coupon was used')
    ->addColumn('order_used_cupom', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
        ), 'Order in whic coupon was used');
    
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('fidelityprogram/fidelitycouponcodebabynotified'))
    ->addColumn('notified_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Id')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ), 'Customer Id')
    ->addColumn('is_customer_notified', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        ), 'is Notified?')
    ->addColumn('customer_fidelity_points', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ), 'Fidelity Points Accumulated')
    ->addColumn('customer_points_to_sum_next', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ), 'To add on next Purchase');
    
$installer->getConnection()->createTable($table);

 
$installer->endSetup();