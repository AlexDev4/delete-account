<?php

namespace Histomagento\DeletingAccount\Controller\Account;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\TransportInterfaceFactory;

class delete implements ActionInterface
{
    protected $context;
    protected $customerRepositoryInterface;
    protected $resultRedirectFactory;
    protected $messageManager;
    protected $_customerSession;
    protected $registry;
    protected $messageInterface;
    protected $transportInterface;

    const EMAIL_FROM = 'admin@yourwebsite.com';
    const EMAIL_CC = 'cc@yourwebsite.com';

    static $customer_mail;
    static bool $account_is_deleted = false;

    public function __construct(
        Context                     $context,
        CustomerRepositoryInterface $customerRepositoryInterface,
        RedirectFactory             $resultRedirectFactory,
        ManagerInterface            $messageManager,
        Session                     $customerSession,
        Registry                    $registry,
        MessageInterfaceFactory     $messageInterface,
        TransportInterfaceFactory   $transportInterface
    )
    {
        $this->_context = $context;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_redirectFactory = $resultRedirectFactory;
        $this->_messageManager = $messageManager;
        $this->_customerSession = $customerSession;
        $this->_registry = $registry;
        $this->_messageInterface = $messageInterface;
        $this->_transportInterface = $transportInterface;
    }

    public function execute(): \Magento\Framework\Controller\Result\Redirect
    {
        if ($this->_customerSession->isLoggedIn()) {
            $customerId = $this->_customerSession->getCustomerId();
            self::$customer_mail = !self::$customer_mail ? $this->_customerRepositoryInterface->getById($customerId)->getEmail() : self::$customer_mail;
            try {
                $this->deleteAccountById($customerId);
                self::$account_is_deleted = true;
            } catch (\Exception $e) {
                $this->_messageManager->addErrorMessage(__('An error occurred while deleting your account.'));
            }
            if (self::$account_is_deleted) {
                $this->sendSuccessDeletingMail(self::$customer_mail);
            }

        }

        $resultRedirect = $this->_redirectFactory->create();
        $resultRedirect->setPath('/');
        return $resultRedirect;
    }

    //TODO : remove _registry because it's deprecated
    private function deleteAccountById($customerId): void
    {
        $this->_registry->unregister('isSecureArea');
        $this->_registry->register('isSecureArea', 'true');
        $this->_customerRepositoryInterface->deleteById($customerId);
        $this->_registry->unregister('isSecureArea');
        $this->_registry->register('isSecureArea', 'false');
        $this->_customerSession->logout();
        $this->_messageManager->addSuccessMessage(__('Your account has been deleted.'));
    }

    //TODO : create mail template and link it
    private function sendSuccessDeletingMail($customerMail): void
    {
        $subject = "email subject";
        $emailHtml = "<h1>Test Email</h1>";

        $message = $this->_messageInterface->create();
        $message->setFromAddress(self::EMAIL_FROM);
        $message->addCc(self::EMAIL_CC);
        $message->addTo($customerMail);
        $message->setSubject($subject);
        $message->setBodyHtml($emailHtml);
        $transport = $this->_transportInterface->create(['message' => $message]);
        $transport->sendMessage();
    }
}
