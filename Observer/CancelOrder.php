<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Mondu\Mondu\Helper\ContextHelper;
use Mondu\Mondu\Helper\PaymentMethod;
use Mondu\Mondu\Model\Request\Factory as RequestFactory;
use Psr\Log\LoggerInterface;

class CancelOrder extends AbstractMonduObserver
{
    /**
     * @var string
     */
    protected $name = 'CancelOrder';

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @param PaymentMethod $paymentMethodHelper
     * @param LoggerInterface $logger
     * @param ContextHelper $contextHelper
     * @param RequestFactory $requestFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        PaymentMethod    $paymentMethodHelper,
        LoggerInterface  $logger,
        ContextHelper    $contextHelper,
        RequestFactory   $requestFactory,
        ManagerInterface $messageManager
    ) {
        parent::__construct(
            $paymentMethodHelper,
            $logger,
            $contextHelper
        );

        $this->requestFactory = $requestFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function executeMondu(Observer $observer): void
    {
        $order = $observer->getEvent()->getOrder();
        $monduId = $order->getData('mondu_reference_id');

        try {
            if (!$order->getRelationChildId()) {
                $this->logger->info('Trying to cancel Order '.$order->getIncrementId());

                $cancelData = $this->requestFactory->create(RequestFactory::CANCEL)
                    ->process(['orderUid' => $monduId]);

                if (!$cancelData) {
                    $message = 'Mondu: Unexpected error: Order could not be found,' .
                        ' please contact Mondu Support to resolve this issue.';
                    $this->messageManager
                        ->addErrorMessage($message);
                    return;
                }

                $order->addStatusHistoryComment(
                    __('Mondu: The order with the id %1 was successfully canceled.', $monduId)
                );
                $order->save();
                $this->logger->info('Cancelled order ', ['orderNumber' => $order->getIncrementId()]);
            }
        } catch (Exception $error) {
            $this->logger
                ->info('Failed to cancel Order '.$order->getIncrementId(), ['e' => $error->getMessage()]);
            throw new LocalizedException(__($error->getMessage()));
        }
    }
}
