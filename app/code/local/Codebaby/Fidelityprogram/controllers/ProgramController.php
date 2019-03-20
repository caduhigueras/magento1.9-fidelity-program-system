<?php
class Codebaby_Fidelityprogram_ProgramController extends Mage_Core_Controller_Front_Action
{   
    public function indexAction()
    {
    	
        if (!Mage::getSingleton('customer/session')->isLoggedIn()):
            $this->_redirect('customer/account/login');
            return;
        endif;

        $this->loadLayout();
        $this->renderLayout();
    }
}