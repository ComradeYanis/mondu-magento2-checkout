<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Mondu\Mondu\Helper\BulkActions;
use Mondu\Mondu\Model\Config\MonduConfigProvider;

class Cron
{

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var MonduConfigProvider
     */
    private $configProvider;

    /**
     * @var BulkActions
     */
    private $bulkActions;

    /**
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param BulkActions $bulkActions
     * @param MonduConfigProvider $configProvider
     */
    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        BulkActions $bulkActions,
        MonduConfigProvider $configProvider
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->bulkActions = $bulkActions;
        $this->configProvider = $configProvider;
    }

    /**
     * Execute
     *
     * @return $this
     */
    public function execute(): Cron
    {
        if ($this->configProvider->isCronEnabled()) {
            $this->executeBulkShipAction();
        }
        return $this;
    }

    /**
     * @return void
     */
    private function executeBulkShipAction(): void
    {
        $date = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $orders = $this->orderCollectionFactory->create()
            ->addAttributeToFilter('updated_at', [ 'from' => $date ])
            ->addAttributeToFilter('mondu_reference_id', ['neq' => null]);

        $orderIds = $orders->getAllIds();

        $this->bulkActions->execute($orderIds, BulkActions::BULK_SHIP_ACTION);
    }
}
