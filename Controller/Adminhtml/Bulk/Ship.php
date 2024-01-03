<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);


namespace Mondu\Mondu\Controller\Adminhtml\Bulk;

use Magento\Framework\Exception\LocalizedException;
use Mondu\Mondu\Helper\BulkActions;

class Ship extends BulkAction
{
    /**
     * Ships selected orders
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->executeAction(BulkActions::BULK_SHIP_ACTION);
    }
}
