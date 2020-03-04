<?php
namespace Invigorate\GmailLogin\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class Process extends Action implements CsrfAwareActionInterface
{
    protected $_customer;
    protected $_customerSession;
    protected $_messageManager;
    private $pageFactory;
    protected $mathRandom;
    protected $customerFactory;
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storemanager,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        parent::__construct($context);
        $this->mathRandom = $mathRandom;
        $this->pageFactory = $pageFactory;
        $this->_customer = $customer;
        $this->_customerSession = $customerSession;
        $this->_storemanager=$storemanager;
        $this->_messageManager = $messageManager;
        $this->customerFactory  = $customerFactory;
    }
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $websiteId =$this->_storemanager->getStore()->getWebsiteId();
        $redirectUrl= $this->_storemanager->getStore()->getBaseUrl();
        $post = (array) $this->getRequest()->getPost();
        $email = $post['email'];
        $firstname = $post['firstname'];
        $lastname = $post['lastname'];
        $customer = $this->_customer->setWebsiteId($websiteId)->loadByEmail($email);
        if (!$this->_customerSession->isLoggedIn()) {
            try {
                if ($customer['email'] == $email) {
                    $this->_customerSession->setCustomerAsLoggedIn($customer);
                    $resultRedirect->setPath($redirectUrl);
                    return $resultRedirect;
                } else {
                    $newcustomer = $this->customerFactory->create();
                    $newcustomer->setEmail($email);
                    $newcustomer->setFirstname($firstname);
                    $newcustomer->setLastname($lastname);
                    $password = $this->mathRandom->getRandomString(8);
                    $newcustomer->setPassword($password);
                    $newcustomer->save();
                    $this->_customerSession->setCustomerAsLoggedIn($newcustomer);
                    $greeting = "Thank you for signing in using Google account.";
                    $password_msg = "Password for your account is: 
                                    <strong><span style='color:red;'>".$password."</span></strong>";
                    $lastmsg = "Please note it down or you can change your password below.";
                    $this->_messageManager->addSuccess(__($greeting." ".$password_msg." , ".$lastmsg));
                    $resultRedirect->setPath('customer/account/edit/changepass/1/');
                    return $resultRedirect;
                }
            } catch (Exception $e) {
                $this->_messageManager->addError($e->getMessage());
            }
        }
    }
}
