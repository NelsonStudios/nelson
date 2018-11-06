<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Fecon\CustomMultishipping\Block\DataProviders;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Fecon\CustomMultishipping\Block\Checkout\Results;

/**
 * Provides additional data for multishipping checkout success step.
 */
class Success extends Results implements ArgumentInterface
{

}
