<?php
 
class Codebaby_Fidelityprogram_Model_Resource_Fidelitycouponcodebaby extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('fidelityprogram/fidelitycouponcodebaby', 'coupon_id');
    }
}