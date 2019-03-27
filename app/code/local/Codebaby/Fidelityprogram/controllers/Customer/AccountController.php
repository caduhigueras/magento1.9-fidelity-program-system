<?php
require_once 'Mage'.DS.'Customer'.DS.'controllers'.DS.'AccountController.php';

class Codebaby_Fidelityprogram_Customer_AccountController extends Mage_Customer_AccountController {
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
            endif;
        endif;
    }

    //get customer
    public function getCurrentCustomer(){
        return $customer = Mage::getSingleton('customer/session')->getCustomer();
    }

    //check if costumer already saw the lightbox
    public function isCustomerNotified($customer){
        //get customer ID
        $customerId = $customer->getId();
        //check if customer exists on notified table and if its notified
        $customerNotifiedCollection = Mage::getModel('fidelityprogram/fidelitycouponcodebabynotified')->getCollection()
        ->addFieldToSelect('*')->addFieldToFilter('customer_id', $customerId)->getFirstItem();
        //check if array brings results
        $customerNotifiedItem = $customerNotifiedCollection->getData();
        $tamanhoArray = sizeof($customerNotifiedItem);
        if($tamanhoArray > 0):
            return true;
        else:
            $customerData = array(
                'customer_id'=>$customerId,
                'is_customer_notified' => 1
            );
            $couponModel = Mage::getModel('fidelityprogram/fidelitycouponcodebabynotified')->setData($customerData); 
            $couponModel->save();
            return false;
        endif;
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
