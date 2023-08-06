<?php

namespace Histomagento\DeletingAccount\Block\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class DeletingAccount extends Template
{

    protected $customerSession;

    public function __construct(
        Session $customerSession,
        Context $context,
        array   $data = []
    )
    {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
    }

    /**
     * Get URL for delete account action
     *
     * @return string
     */
    public function getDeleteUrl():string
    {
        return $this->getUrl('deletingaccount/account/deletingaccount');
    }
}

