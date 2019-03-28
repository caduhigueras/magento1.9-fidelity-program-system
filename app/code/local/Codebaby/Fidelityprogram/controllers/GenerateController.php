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
    }

    //get customer
    public function getCurrentCustomer(){
    	return $customer = Mage::getSingleton('customer/session')->getCustomer();
    }

    protected function generateNewShoppingCartRule($discountAmount)
    {
    	$customer = $this->getCurrentCustomer();
    	//getting values on new table
    	$customerCouponInfo = $this->getCustomerCouponInfo($customer);
        $currentPoints = $customerCouponInfo['customer_fidelity_points'];
    	
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
		//if(!($x >= $y)) echo "True";
		//define params of the shopping cart rule
    	$name = "Fidelity Program: ".$customer->getName(); // name of Shopping Cart Price Rule
		$websiteId = 1;
		$customerGroupId = $customer->getGroupId(); 
		$actionType = 'cart_fixed'; // discount by percentage
		//generating coupon
		//TODO: Add prefix to module config
		$couponInit = 'CBFP';
		$couponEnd = $this->generateRandomString();
		$generatedCoupon = $couponInit.$couponEnd;
		//TODO:fazer função pra verificar se o cupom já existe
		//Mage::getModel('salesrule/coupon')->load($couponCode, 'code');

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

		try {    
			//cria o cupom no BD
			$couponDataFormat = array(
				'customer_id' => $customer->getId(),
				'codigo_coupon' => $generatedCoupon,
				'is_cupom_used' => 0
			 );
			$couponModel = Mage::getModel('fidelityprogram/fidelitycouponcodebaby')->setData($couponDataFormat); 
			$couponModel->save();

			//remove os pontos usados do cliente
			//check coupon collection with customer ID
	        $couponCollection = Mage::getModel('fidelityprogram/fidelitycouponcodebabynotified')->getCollection()
	        ->addFieldToSelect('*')->addFieldToFilter('customer_id', $customer->getId())->getFirstItem();
	        //check if array brings results
	        $couponCollectionArray = $couponCollection->getData();
	        $dbId = $couponCollectionArray['notified_id'];
	        $data = array('customer_fidelity_points'=>$pointsAfterSubstract);
			$model = Mage::getModel('fidelityprogram/fidelitycouponcodebabynotified')->load($dbId)->addData($data);
			$model->save();
			//save coupon
		    $shoppingCartPriceRule->save();                
		    //$shoppingCartPriceRule->applyAll();
		} catch (Exception $e) {
		    Mage::getSingleton('core/session')->addError(Mage::helper('catalog')->__($e->getMessage()));
		    return;
		}
	}

	public function generateRandomString($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

    public function getCustomerCouponInfo($customer){
        //get all coupons not used ans with the current customer id
        $customerId = $customer->getId();
        $customerCouponCollection = Mage::getModel('fidelityprogram/fidelitycouponcodebabynotified')->getCollection()
        ->addFieldToSelect('*')->addFieldToFilter('customer_id', $customerId)->getFirstItem();
        //check if array brings results
        if(sizeof($customerCouponCollection) > 0):
            return $customerCouponCollection->getData();
        else:
            return false;
        endif;
    }
}