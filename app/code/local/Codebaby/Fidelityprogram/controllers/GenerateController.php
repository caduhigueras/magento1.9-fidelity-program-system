<?php
class Codebaby_Fidelityprogram_GenerateController extends Mage_Core_Controller_Front_Action
{   
    public function indexAction()
    {
    	$discount = $this->getRequest()->getParam('discount');
    	$success = $this->getRequest()->getParam('success');
    	if (!Mage::getSingleton('customer/session')->isLoggedIn()):
            $this->_redirect('customer/account/login');
            return;
        endif;
        $this->generateNewShoppingCartRule($discount);
	    $this->_redirect('fidelity/program?success=true');
    	

    	//$this->loadLayout();
    	// $blockHtml = $this->getLayout()->createBlock('codebaby_fidelityprogram/dashboardbox')
    	// 						  ->setTemplate('codebaby_fidelityprogram/lightbox.phtml');
			  //   $this->getLayout()->getBlock('content')->append($bannerHtml)->append($blockHtml);
		//$blockHtml = $this->getLayout()->createBlock('core/text')
    	//						  ->setText('Cupom Criado!');
		//$this->getLayout()->getBlock('content')->append($blockHtml);
        //$this->renderLayout();
	    
	    //return;
		// echo $this->getCurrentCustomer()->getName();

    }

    //get customer
    public function getCurrentCustomer(){
    	return $customer = Mage::getSingleton('customer/session')->getCustomer();
    }

    protected function generateNewShoppingCartRule($discountAmount)
    {
    	$customer = $this->getCurrentCustomer();
    	$currentPoints = $this->getCurrentCustomer()->getCustomer_fidelity_points();
    	//check all options of points
    	$couponSystemFirstLevelPointsToGet = Mage::getStoreConfig('couponpointssystem_tab/couponfirstlevel_group/points_toget',Mage::app()->getStore());
		$couponSystemSecondLevelPointsToGet = Mage::getStoreConfig('couponpointssystem_tab/couponsecondlevel_group/points_toget',Mage::app()->getStore());
		$couponSystemThirdLevelPointsToGet = Mage::getStoreConfig('couponpointssystem_tab/couponthirdlevel_group/points_toget',Mage::app()->getStore());

		//check current points to see how many points need to be subtracted
		if(($currentPoints >= $couponSystemFirstLevelPointsToGet) && ($currentPoints < $couponSystemSecondLevelPointsToGet)):
			$pointsAfterSubstract = $currentPoints - $couponSystemFirstLevelPointsToGet;
		elseif(($currentPoints >= $couponSystemSecondLevelPointsToGet) && ($currentPoints < $couponSystemThirdLevelPointsToGet)):
			$pointsAfterSubstract = $currentPoints - $couponSystemSecondLevelPointsToGet;
		elseif($currentPoints >= $couponSystemThirdLevelPointsToGet):
			$pointsAfterSubstract = $currentPoints - $couponSystemThirdLevelPointsToGet;
		endif;
		if(!($x >= $y)) echo "True";
		//define params of the shopping cart rule
    	$name = "Fidelity Program: ".$customer->getName(); // name of Shopping Cart Price Rule
		$websiteId = 1;
		$customerGroupId = $customer->getGroupId();; 
		$actionType = 'cart_fixed';
		//generating coupon
		$couponInit = 'FP';
		$couponEnd = $this->generateRandomString();
		$generatedCoupon = $couponInit.$couponEnd;
		//TODO:fazer função pra verificar se o cupom já existe
		//Mage::getModel('salesrule/coupon')->load($couponCode, 'code');

		//$generatedCoupon = '1236154564564'; 
		//(other options are: by_fixed, cart_fixed, buy_x_get_y,by_percent)
		//$discount = 10; // percentage discount
		//$sku = 'chair'; // product sku
		 
		$shoppingCartPriceRule = Mage::getModel('salesrule/rule');
		$shoppingCartPriceRule
		    ->setName($name)
		    ->setDescription('')
		    ->setIsActive(1)
		    ->setWebsiteIds(array($websiteId))
		    ->setCustomerGroupIds(array($customerGroupId))
		    ->setCouponType(2)
		    ->setUsesPerCoupon(1)
		    ->setCouponCode($generatedCoupon)
		    ->setFromDate('')
		    ->setToDate('')
		    ->setSortOrder('')
		    ->setSimpleAction($actionType)
		    ->setDiscountAmount($discountAmount)
		    ->setStopRulesProcessing(0);
		 
		// $skuCondition = Mage::getModel('salesrule/rule_condition_product')
		//                     ->setType('salesrule/rule_condition_product')
		//                     ->setAttribute('sku')
		//                     ->setOperator('==')
		//                     ->setValue($sku);
		                    
		try {    
		    //$shoppingCartPriceRule->getConditions()->addCondition($skuCondition);
		    $customer->setCustomercurrentfidelitycoupon($generatedCoupon);
		    $customer->setCustomer_fidelity_points($pointsAfterSubstract);
		    $customer->save();
		    $shoppingCartPriceRule->save();                
		    //$shoppingCartPriceRule->applyAll();
		} catch (Exception $e) {
		    Mage::getSingleton('core/session')->addError(Mage::helper('catalog')->__($e->getMessage()));
		    return;
		}
	}

	function generateRandomString($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}
}