<?php

namespace Custommodule8\Activation8\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerLogin implements ObserverInterface
{

    protected $_customerRepositoryInterface;

    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $customer = $observer->getEvent()->getCustomer();

        if($customer->getApproveAccount()==0){

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customerSession = $objectManager->create('Magento\Customer\Model\Session');
            $customerSession->logout();

            $urlBuilder = $objectManager->get('\Magento\Framework\UrlInterface');           
            $_responseFactory = $objectManager->get('\Magento\Framework\App\ResponseFactory');           
            $messageManager = $objectManager->get('\Magento\Framework\Message\ManagerInterface');          
            $messageManager->addError(__("Your account will be enabled by the site owner soon"));            
            $CustomRedirectionUrl = $urlBuilder->getUrl('customer/account/login');            
            $_responseFactory->create()->setRedirect($CustomRedirectionUrl)->sendResponse();

            die;
        }

    }
            
}