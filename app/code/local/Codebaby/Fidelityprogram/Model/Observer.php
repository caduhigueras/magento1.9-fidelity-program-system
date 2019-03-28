<?php 
class Codebaby_Fidelityprogram_Model_Observer{
    //applying the store credit when calculating totals
    public function removeUsedFidelityProgramCoupon($observer) {
        $order = $observer->getEvent()->getOrder();
        //get the coupon used on the sale
        $usedCoupon = $order->getCouponCode();
        $orderId = $order->getIncrementId();
        $orderDate = $order->getCreatedAt();
        //try to load customer with coupon code
        //we also need to add the customer points to the purchases
        $currentCustomer = $this->getCurrentCustomer();
        if($this->isCustomerGroupOnProgram($currentCustomer)):
            $currentCustomerId = $currentCustomer->getId();

            //load model collection of coupon customer by the id
            $couponCollection = Mage::getModel('fidelityprogram/fidelitycouponcodebabynotified')->getCollection()
            ->addFieldToSelect('*')->addFieldToFilter('customer_id', $currentCustomerId)->getFirstItem();
            
            //separando aqui, vai mostrar a somatória total para o cliente, mas a conta vai ser feita na tabela "fidelitycouponcodebabybeforegenerate"
            $couponCollectionArray = $couponCollection->getData();
            $customerCurrentPoints = $couponCollectionArray['customer_fidelity_points_to_be_confirmed'];
            
            //get money acumulated from previous purchases
            $customerMoneyAcumulated = 0;
            $customerMoneyAcumulatedField = $couponCollectionArray['customer_points_to_sum_next'];
            if($customerMoneyAcumulatedField != 0){
                $customerMoneyAcumulated = $customerMoneyAcumulatedField;
            }
            //get total spent with order
            $totalSpentOnOrder = $order->getGrandTotal();
            //sum total spent with money that was acumulated from previous purchase in order to calculate the points generated
            $totalSpent = $totalSpentOnOrder+$customerMoneyAcumulated;

            //fazer as contas dos pontos a serem adicionados
            $moneyNecessaireToMakePoints = Mage::getStoreConfig('couponpointssystem_tab/pointscouponconfig_group/money_amount',Mage::app()->getStore());
            $pointsMadePerMoneyNecessaire = Mage::getStoreConfig('couponpointssystem_tab/pointscouponconfig_group/points_permoney',Mage::app()->getStore());
            if($totalSpent%$moneyNecessaireToMakePoints == 0):
                $pointsGenerated = ($totalSpent/$moneyNecessaireToMakePoints)*$pointsMadePerMoneyNecessaire;
                $moneyToBeAppliedLater = 0;
            else:
                $moneyToBeAppliedLater = $totalSpent%$moneyNecessaireToMakePoints;
                $pontosAcumuladosPraMultiplicar = $totalSpent-$dinheiroPraDepois;
                $pointsGenerated = ($pontosAcumuladosPraMultiplicar/$moneyNecessaireToMakePoints)*$pointsMadePerMoneyNecessaire;
            endif;

            $customerNewPoints = $customerCurrentPoints + $pointsGenerated;

            //get the customer coupon id, in case it differs
            $dbId = $couponCollectionArray['notified_id'];
            //define new points value to be confirmed
            $data = array('customer_fidelity_points_to_be_confirmed'=>$customerNewPoints,'customer_points_to_sum_next'=>$moneyToBeAppliedLater);
            //adding data to item
            $model = Mage::getModel('fidelityprogram/fidelitycouponcodebabynotified')->load($dbId)->addData($data);
            $model->save();
            
            //now that the values are defined we create a new record on the table fidelitycouponcodebabybeforegenerate
            $beforeApplyData = array('customer_id'=>$currentCustomerId, 'order_coupon_generate'=>$orderId, 'order_coupon_points_generated'=>$pointsGenerated, 'order_coupon_points_generated_applied'=>0);
            $couponsBeforeApply = Mage::getModel('fidelityprogram/fidelitycouponcodebabybeforegenerate')->setData($beforeApplyData);
            $couponsBeforeApply->save();


            // $currentCustomer->setCustomer_fidelity_points($customerNewPoints);
            // $currentCustomer->save();
        endif;
        if($usedCoupon):
            //check coupon collection with customer ID
            $couponCollection = Mage::getModel('fidelityprogram/fidelitycouponcodebaby')->getCollection()
            ->addFieldToSelect('*')->addFieldToFilter('codigo_coupon', $usedCoupon)->getFirstItem();
            //check if array brings results
            $usedCouponCollectionArray = $couponCollection->getData();
            if(sizeof($usedCouponCollectionArray) > 0):
                $dbId = $usedCouponCollectionArray['coupon_id'];
                // $data = array('is_cupom_used'=>1);
                $data = array('order_used_cupom'=>$orderId,'date_used_cupom'=>$orderDate,'is_cupom_used'=>1);
                $model = Mage::getModel('fidelityprogram/fidelitycouponcodebaby')->load($dbId)->addData($data);
                $model->save();
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

    public function applyCouponAfterConfirmPayment($observer){
        /* @var Mage_Sales_Model_Order $order */
        $order = $observer->getOrder();
        $stateProcessing = $order::STATE_PROCESSING;
        // Only trigger when an order enters processing state.
        if ($order->getState() == $stateProcessing && $order->getOrigData('state') != $stateProcessing) {
            //if order checked as processed, check for it on points to be applied table
            //checking only cupons that were not applied
            $couponsBeforeApply = Mage::getModel('fidelityprogram/fidelitycouponcodebabybeforegenerate')->getCollection()
            ->addFieldToSelect('*')->addFieldToFilter('order_coupon_generate', $order->getIncrementId())->addFieldToFilter('order_coupon_points_generated_applied', 0)->getFirstItem();
            $couponsBeforeApplyArray = $couponsBeforeApply->getData();
            if(sizeof($couponsBeforeApplyArray)>0):
                //Mage::log(print_r($couponsBeforeApplyArray, 1),null,'codebaby.log',true);
                $couponsBeforeApplyArrayId = $couponsBeforeApplyArray['beforegenerate_id'];
                //this is the points amount that will be actually applied and removed from the points to be applied column
                $pointsToBeApplied = $couponsBeforeApplyArray['order_coupon_points_generated'];
                $customerOwnerId = $couponsBeforeApplyArray['customer_id'];

                //change status to points applied
                $updatedCouponData = array('order_coupon_points_generated_applied'=>1);
                $currentCouponModel = Mage::getModel('fidelityprogram/fidelitycouponcodebabybeforegenerate')->load($couponsBeforeApplyArrayId)->addData($updatedCouponData);
                $currentCouponModel->save();

                //now lets transfer the points and remove it from the points to be applied
                //get the record by its customer id
                $couponsAfterApply = Mage::getModel('fidelityprogram/fidelitycouponcodebabynotified')->getCollection()
                ->addFieldToSelect('*')->addFieldToFilter('customer_id', $customerOwnerId)->getFirstItem();
                $couponsAfterApplyArray = $couponsAfterApply->getData();

                //Mage::log(print_r($couponsAfterApplyArray, 1),null,'codebaby.log',true);
                //get id of the record to update
                $couponsAfterApplyArrayId = $couponsAfterApplyArray['notified_id'];
                $pointsToBeRemoved = $couponsAfterApplyArray['customer_fidelity_points_to_be_confirmed'];
                $pointsToBeAdded = $couponsAfterApplyArray['customer_fidelity_points'];
                //remove from points to be confirmed and sum to confirmed points
                $toBeConfirmedNew = $pointsToBeRemoved - $pointsToBeApplied;
                $newPointsToBeAdded = $pointsToBeAdded + $pointsToBeApplied;
                //define the new values
                $newCouponData = array('customer_fidelity_points_to_be_confirmed'=>$toBeConfirmedNew,'customer_fidelity_points'=>$newPointsToBeAdded);
                 $newCouponsAfterApply = Mage::getModel('fidelityprogram/fidelitycouponcodebabynotified')->load($couponsAfterApplyArrayId)->addData($newCouponData);
                $newCouponsAfterApply->save();
            endif;//sizeof($couponsBeforeApplyArray)>0
        }
    }
}