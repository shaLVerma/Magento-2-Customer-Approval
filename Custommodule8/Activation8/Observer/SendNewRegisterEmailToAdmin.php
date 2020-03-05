<?php

namespace Custommodule8\Activation8\Observer;

use Magento\Framework\Event\ObserverInterface;

class SendNewRegisterEmailToAdmin implements ObserverInterface
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

        //if($customer->getApproveAccount()==0){

            // mail code start

            $this->inlineTranslation->suspend();

            $error = false;
    
            $sender = [
                'name' => $this->_escaper->escapeHtml($customer->getFirstName()),
                'email' => $this->_escaper->escapeHtml($customer->getEmail()),
            ];
            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData($sender);

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE; 
            $transport = 
                $this->_transportBuilder
                ->setTemplateIdentifier('new_user_email_send_to_admin') // Send the ID of Email template which is created in Admin panel
                ->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
                )
                ->setTemplateVars(['data' => $postObject])
                ->setFrom($sender)
                ->addTo($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope))
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
          
            // mail code end

            // redirect code start
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $customerSession = $objectManager->create('Magento\Customer\Model\Session');
                $customerSession->logout();

                $urlBuilder = $objectManager->get('\Magento\Framework\UrlInterface');           
                $_responseFactory = $objectManager->get('\Magento\Framework\App\ResponseFactory');           
                $messageManager = $objectManager->get('\Magento\Framework\Message\ManagerInterface');          
                $messageManager->addSuccess(__("Your account will be enabled by the site owner soon"));            
                $CustomRedirectionUrl = $urlBuilder->getUrl('customer/account/login');            
                $_responseFactory->create()->setRedirect($CustomRedirectionUrl)->sendResponse();
            // redirect code end

            die;
        //}

    }
            
}