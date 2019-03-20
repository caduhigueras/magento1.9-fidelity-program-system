<?php 
class Codebaby_Fidelityprogram_Model_Observer{
    //applying the store credit when calculating totals
    public function removeUsedFidelityProgramCoupon($observer) {
    	$order = $observer->getEvent()->getOrder();
    	//$usedCoupon = $order['coupon_code'];
    	//get the coupon used on the sale
    	$usedCoupon = $order->getCouponCode();
    	//try to load customer with coupon code
		//we also need to add the customer points to the purchases
		$currentCustomer = $this->getCurrentCustomer();
		if($this->isCustomerGroupOnProgram($currentCustomer)):
			$customerCurrentPoints = $currentCustomer->getCustomer_fidelity_points();
			$totalSpent = $order->getGrandTotal();
			$customerNewPoints = $customerCurrentPoints + $totalSpent;
			$currentCustomer->setCustomer_fidelity_points($customerNewPoints);
			$currentCustomer->save();
		endif;
		if($usedCoupon):
			$customerCouponOwner = mage::getModel('customer/customer')->getCollection()
			   ->addAttributeToSelect('entity_id')->addAttributeToFilter('customercurrentfidelitycoupon', $usedCoupon)->getFirstItem();
			//if locate customer holding the coupon code, remove the coupon code
			if($customerCouponOwner):
				$couponOwner = $customerCouponOwner->getData();
				$couponOwnerId = $couponOwner['entity_id'];
				$customerToUnset = Mage::getModel('customer/customer')->load($couponOwnerId);
				$customerToUnset->setCustomercurrentfidelitycoupon('');
				$customerToUnset->save();
			endif;
		endif;

    }

    protected function getCurrentCustomer(){
      return $currentCustomer = Mage::getSingleton('customer/session')->getCustomer();
    }

    //check if customer is entitled to the fidelity program
    protected function isCustomerGroupOnProgram($currentCustomer){
        //get current customer group
        $customerGroup = $currentCustomer->getGroupId();
        //get current groups allowed for program
        $customerGroupsAllowedProgram = Mage::getStoreConfig('generaln_tab/fidelitysettingsgroup/customergroup_select',Mage::app()->getStore());
        //split groups into array
        $customerGroupsAllowedProgramArray = explode(',', $customerGroupsAllowedProgram);
        //check if current customer group is in array
        if(in_array($customerGroup,$customerGroupsAllowedProgramArray)):
            return true;
        endif;
        return false;
    }
}