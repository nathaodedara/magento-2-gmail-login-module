<?php
namespace Invigorate\GmailLogin\Block;

class Login extends \Magento\Framework\View\Element\Template
{
    protected $hlp;
    protected $customerSession;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Invigorate\GmailLogin\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->hlp = $helper;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }
    public function isUserLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }
    public function isGoogleEnabled()
    {
        return $this->hlp->getConfigValue('invigorate_gmaillogin_section/gmaillogin_group/gmaillogin_status');
    }
    public function getGoogleAppId()
    {
        return $this->hlp->getConfigValue('invigorate_gmaillogin_section/gmaillogin_group/gmaillogin_clientid');
    }
    public function getGoogleApiKey()
    {
        return $this->hlp->getConfigValue('invigorate_gmaillogin_section/gmaillogin_group/gmaillogin_clientsecret');
    }
    public function getButtonText()
    {
        return $this->hlp->getConfigValue('invigorate_gmaillogin_section/gmaillogin_group/gmaillogin_btntext');
    }
}
