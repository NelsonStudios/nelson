<?php

namespace Fecon\Sso\Plugin\Customer\Model\Metadata;

use Magento\Customer\Model\Metadata\Form as CustomerForm;

/**
 * Plugin to fix customer group data
 */
class Form
{

    /**
     * Clean sso_customer_group if not set in form
     *
     * @param CustomerForm $subject
     * @param array $result
     * @return array
     */
    public function afterCompactData(CustomerForm $subject, $result)
    {
        if (!isset($result['sso_customer_group']) ||
        (isset($result['sso_customer_group']) && $result['sso_customer_group'] === false)) {
            $result['sso_customer_group'] = [];
        }

        return $result;
    }
}