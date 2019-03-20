<?php
class Codebaby_Fidelityprogram_Block_DashboardBox extends Mage_Core_Block_Template{
	
	public function getCustomer()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer->getId()):
            return $customer;
        endif;

        return false;
    }
}	
