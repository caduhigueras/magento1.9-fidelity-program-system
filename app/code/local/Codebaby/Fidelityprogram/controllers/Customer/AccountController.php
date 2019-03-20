<?php
require_once 'Mage'.DS.'Customer'.DS.'controllers'.DS.'AccountController.php';

class Codebaby_Fidelityprogram_Customer_AccountController extends Mage_Customer_AccountController {
    //TODO: check if its necessary
	public function indexAction()
    {
        //parent::indexAction();
        $customer = $this->getCurrentCustomer();
		//if customer is entitled to the program
		if($this->isCustomerGroupOnProgram($customer)):
        	//if customer already saw the lightbox 
    		if($this->isCustomerNotified($customer)):
    			//append the banner block
    			$this->loadLayout();
    			$bannerHtml = $this->getLayout()->createBlock('codebaby_fidelityprogram/dashboardbox')
    							   ->setTemplate('codebaby_fidelityprogram/fidelitybanner.phtml');
			    $this->getLayout()->getBlock('content')->append($bannerHtml);
			    $this->renderLayout();
			else:
				//if the customer didn't see the lightbox
				//append the banner block, the lightbox block and set as notified
    			$this->loadLayout();
    			$bannerHtml = $this->getLayout()->createBlock('codebaby_fidelityprogram/dashboardbox')
    							   ->setTemplate('codebaby_fidelityprogram/fidelitybanner.phtml');
    			$blockHtml = $this->getLayout()->createBlock('codebaby_fidelityprogram/dashboardbox')
    							  ->setTemplate('codebaby_fidelityprogram/lightbox.phtml');
			    $this->getLayout()->getBlock('content')->append($bannerHtml)->append($blockHtml);
			    $this->renderLayout();
			    $customer->setIscustomernotified(1);
			    $customer->save();
			endif;
    	endif;
    }

    //get customer
    public function getCurrentCustomer(){
    	return $customer = Mage::getSingleton('customer/session')->getCustomer();
    }

    //check if costumer already saw the lightbox
    public function isCustomerNotified($customer){
    	$customerStatus = $customer->getIscustomernotified();
    	if($customerStatus == 1):
    		return true;
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
}
