<?php

namespace Custommodule8\Activation8\Observer;

use Magento\Framework\Event\ObserverInterface;

class SendApprovalEmail implements ObserverInterface
{

    const XML_PATH_EMAIL_RECIPIENT = 'contact/email/recipient_email';
    protected $_customerRepositoryInterface;
    protected $_transportBuilder;
    protected $inlineTranslation;
    protected $scopeConfig;
    protected $storeManager;
    protected $_escaper;

    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_escaper = $escaper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $customer = $observer->getEvent()->getCustomer();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $websiteId = $storeManager->getWebsite()->getWebsiteId();
        $customerFactory = $objectManager->get('\Magento\Customer\Model\CustomerFactory'); 
        $customer12=$customerFactory->create();
        $customer12->setWebsiteId($websiteId);
        $customer12->loadByEmail($customer->getEmail());

        if($_POST['customer']['approve_account']!=$customer12->getApproveAccount()) {

            if($_POST['customer']['approve_account']==0){

                $this->inlineTranslation->suspend();

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE; 

                $sender1 = [
                    'name' => 'Admin',
                    'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
                ];
        
                $sender = [
                    'name' => $this->_escaper->escapeHtml($customer->getFirstName()),
                    'email' => $this->_escaper->escapeHtml($customer->getEmail()),
                    'status' => 'Unapproved',
                ];
                $postObject = new \Magento\Framework\DataObject();
                $postObject->setData($sender);

                
                $transport = 
                    $this->_transportBuilder
                    ->setTemplateIdentifier('customer_account_approved') // Send the ID of Email template which is created in Admin panel
                    ->setTemplateOptions(
                        ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
                    )
                    ->setTemplateVars(['data' => $postObject])
                    ->setFrom($sender1)
                    ->addTo($customer->getEmail())
                    ->getTransport();
                $transport->sendMessage();
                $this->inlineTranslation->resume();

            }else{

                $this->inlineTranslation->suspend();

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE; 

                $sender1 = [
                    'name' => 'Admin',
                    'email' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope),
                ];
        
                $sender = [
                    'name' => $this->_escaper->escapeHtml($customer->getFirstName()),
                    'email' => $this->_escaper->escapeHtml($customer->getEmail()),
                    'status' => 'Approved',
                ];
                $postObject = new \Magento\Framework\DataObject();
                $postObject->setData($sender);

                $transport = 
                    $this->_transportBuilder
                    ->setTemplateIdentifier('customer_account_approved') // Send the ID of Email template which is created in Admin panel
                    ->setTemplateOptions(
                        ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
                    )
                    ->setTemplateVars(['data' => $postObject])
                    ->setFrom($sender1)
                    ->addTo($customer->getEmail())
                    ->getTransport();
                $transport->sendMessage();
                $this->inlineTranslation->resume();

            }

        }
        

    }
            
}