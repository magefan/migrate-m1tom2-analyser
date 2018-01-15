<?php

class Magefan_M1ToM2_Adminhtml_Mfm1tom2_IndexController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/mfm1tom2');
    }

    public function indexAction()
    {
       $this->loadLayout();
       $this->_title($this->__("Migrate Magento 1 to Magento 2 (Analize)"));
       $this->renderLayout();
    }
}