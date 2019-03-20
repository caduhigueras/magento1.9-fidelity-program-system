<?php
class Codebaby_Fidelityprogram_Block_DashboardPage extends Mage_Core_Block_Template{
	
	public function getCustomer()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer->getId()):
            return $customer;
        endif;

        return false;
    }

    public function couponSystemEnabledField(){
        $couponSystemEnabledField = Mage::getStoreConfig('couponpointssystem_tab/pointscouponconfig_group/enable_couponsystem',Mage::app()->getStore());
        if($couponSystemEnabledField == 1 && $this->isCustomerGroupOnProgram($this->getCustomer())):
            return true;
        endif;
        return false;
    }

    public function couponSystemMoneyAmount(){
        return $couponSystemMoneyAmount = Mage::getStoreConfig('couponpointssystem_tab/pointscouponconfig_group/money_amount',Mage::app()->getStore());    
    }

    public function couponSystemPointsPerMoney(){
        return $couponSystemPointsPerMoney = Mage::getStoreConfig('couponpointssystem_tab/pointscouponconfig_group/points_permoney',Mage::app()->getStore());    
    }

    public function couponSystemEnableFirstLevel(){
        $couponSystemEnableFirstLevel = Mage::getStoreConfig('couponpointssystem_tab/couponfirstlevel_group/enable_level',Mage::app()->getStore());    
        if($couponSystemEnableFirstLevel == 1):
            return true;
        endif;
        return false;
    }

    public function couponSystemFirstLevelPointsToGet(){
        return $couponSystemFirstLevelPointsToGet = Mage::getStoreConfig('couponpointssystem_tab/couponfirstlevel_group/points_toget',Mage::app()->getStore());
    }

    public function couponSystemFirstLevelCouponDiscount(){
        return $couponSystemFirstLevelCouponDiscount = Mage::getStoreConfig('couponpointssystem_tab/couponfirstlevel_group/points_permoney',Mage::app()->getStore());    
    }

    public function couponSystemEnableSecondLevel(){
        $couponSystemEnableSecondLevel = Mage::getStoreConfig('couponpointssystem_tab/couponsecondlevel_group/enable_level',Mage::app()->getStore());    
        if($couponSystemEnableSecondLevel == 1):
            return true;
        endif;
        return false;
    }

    public function couponSystemSecondLevelPointsToGet(){
        return $couponSystemSecondLevelPointsToGet = Mage::getStoreConfig('couponpointssystem_tab/couponsecondlevel_group/points_toget',Mage::app()->getStore());    
    }

    public function couponSystemSecondLevelCouponDiscount(){
        return $couponSystemSecondLevelCouponDiscount = Mage::getStoreConfig('couponpointssystem_tab/couponsecondlevel_group/points_permoney',Mage::app()->getStore());
    }

    public function couponSystemEnableThirdLevel(){
        $couponSystemEnableThirdLevel = Mage::getStoreConfig('couponpointssystem_tab/couponthirdlevel_group/enable_level',Mage::app()->getStore());    
        if($couponSystemEnableThirdLevel == 1):
            return true;
        endif;
        return false;
    }

    public function couponSystemThirdLevelPointsToGet(){
        return $couponSystemThirdLevelPointsToGet = Mage::getStoreConfig('couponpointssystem_tab/couponthirdlevel_group/points_toget',Mage::app()->getStore());
    }

    public function couponSystemThirdLevelCouponDiscount(){
        return $couponSystemThirdLevelCouponDiscount = Mage::getStoreConfig('couponpointssystem_tab/couponthirdlevel_group/points_permoney',Mage::app()->getStore());
    }

    public function getProgramConfigs(){
        return $configsArray;
    }

    public function getPromoConditions(){
        return $promoConditions = Mage::getStoreConfig('couponpointssystem_tab/pointscouponconfig_group/other_infos_field',Mage::app()->getStore());
    }

    public function getCurrentCoupon(){
        $customer = $this->getCustomer();
        if($customer->getCustomercurrentfidelitycoupon()):
            return $customer->getCustomercurrentfidelitycoupon();
        endif;
        return false;
    }

    //check if customer is entitled to the fidelity program
    public function isCustomerGroupOnProgram($customer){
        //get current customer group
        $customerGroup = $customer->getGroupId();
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

    public function isCouponRedeamable(){
        $currentPoints = $this->getCustomer()->getCustomer_fidelity_points();
        
        $toGetCoupon1 = $this->couponSystemFirstLevelPointsToGet();
        $discountCoupon1 = $this->couponSystemFirstLevelCouponDiscount();
        
        $isEnabledCoupon2 = $this->couponSystemEnableSecondLevel();
        $toGetCoupon2 = $this->couponSystemSecondLevelPointsToGet();
        $discountCoupon2 = $this->couponSystemSecondLevelCouponDiscount();
        
        $isEnabledCoupon3 = $this->couponSystemEnableThirdLevel();
        $toGetCoupon3 = $this->couponSystemThirdLevelPointsToGet();
        $discountCoupon3 = $this->couponSystemThirdLevelCouponDiscount();
        
        if($isEnabledCoupon3 && $currentPoints >= $toGetCoupon3):
            return $discount = $discountCoupon3;
        endif;
        
        if($isEnabledCoupon2 && $currentPoints >= $toGetCoupon2):
            return $discount = $discountCoupon2;
        endif;

        if($currentPoints >= $toGetCoupon1):
            return $discount = $discountCoupon1;
        endif;

        return false;
    }

    public function generateCouponButton(){
        if($this->isCouponRedeamable()):
            return $this->isCouponRedeamable();
        else:
            return false;
        endif;
    }

    public function getCurrentCouponAmount(){
        $couponCode = $this->getCustomer()->getCustomercurrentfidelitycoupon();
        $currentCoupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
        $currentRule = Mage::getModel('salesrule/rule')->load($currentCoupon->getRuleId());
        $discountAmount = $currentRule->getDiscountAmount();
        return $formattedDiscount = Mage::helper('core')->currency($discountAmount, true, false);
        //print_r($currentRule->getData());exit();
    }
}	
