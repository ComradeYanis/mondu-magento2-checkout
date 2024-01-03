<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Observer\Adminhtml;

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Mondu\Mondu\Helper\BulkActions;
use Mondu\Mondu\Helper\ContextHelper;
use Mondu\Mondu\Helper\Log;
use Mondu\Mondu\Helper\PaymentMethod;
use Mondu\Mondu\Model\Request\Factory as RequestFactory;
use Mondu\Mondu\Observer\AbstractMonduObserver;
use Psr\Log\LoggerInterface;

class UpdateOrder extends AbstractMonduObserver
{
    /**
     * @var string
     */
    protected $name = 'UpdateOrder';

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Log
     */
    private $monduLogger;

    /**
     * @var BulkActions
     */
    private $bulkActions;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @param PaymentMethod $paymentMethodHelper
     * @param LoggerInterface $logger
     * @param ContextHelper $contextHelper
     * @param RequestFactory $requestFactory
     * @param RequestInterface $request
     * @param Log $logger
     * @param BulkActions $bulkActions
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        PaymentMethod    $paymentMethodHelper,
        LoggerInterface  $logger,
        ContextHelper    $contextHelper,
        RequestFactory   $requestFactory,
        RequestInterface $request,
        Log              $monduLogger,
        BulkActions      $bulkActions,
        ManagerInterface $messageManager
    ) {
        parent::__construct(
            $paymentMethodHelper,
            $logger,
            $contextHelper
        );

        $this->requestFactory = $requestFactory;
        $this->request = $request;
        $this->monduLogger = $monduLogger;
        $this->bulkActions = $bulkActions;
        $this->messageManager = $messageManager;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     * @return void
     */
    public function executeMondu(Observer $observer): void
    {
        $creditMemo = $observer->getEvent()->getCreditmemo();
        $order = $creditMemo->getOrder();
        $monduId = $order->getData('mondu_reference_id');

        try {
            if ($order->canCreditmemo() || $order->canInvoice() || $this->monduLogger->canCreditMemo($monduId)) {
                $this->logger
                    ->info('Trying to create a credit memo', ['orderNumber' => $order->getIncrementId()]);
                $requestParams = $this->request->getParams();
                if (isset($requestParams['creditmemo']['creditmemo_mondu_id'])) {
                    $grossAmountCents = round($creditMemo->getBaseGrandTotal(), 2) * 100;
                    $data = [
                        'invoice_uid' => $requestParams['creditmemo']['creditmemo_mondu_id'],
                        'gross_amount_cents' => $grossAmountCents,
                        'external_reference_id' => $creditMemo->getIncrementId()
                    ];

                    $memoData = $this->requestFactory->create(RequestFactory::MEMO)
                        ->process($data);

                    if (isset($memoData['errors'])) {
                        $this->logger
                            ->info(
                                'Error in UpdateOrder observer ',
                                ['orderNumber' => $order->getIncrementId(), 'e' => $memoData['errors'][0]['details']]
                            );
                        $message = 'Mondu: Unexpected error: Could not send the credit note to Mondu,' .
                            ' please contact Mondu Support to resolve this issue.';
                        $this->messageManager
                            ->addErrorMessage($message);
                        return;
                    }

                    $this->logger->info('Created credit memo', ['orderNumber' => $order->getIncrementId()]);

                    $this->bulkActions->execute([$order->getId()], BulkActions::BULK_SYNC_ACTION);
                } else {
                    $this->logger
                        ->info(
                            'Cant create a credit memo: no Mondu invoice id provided',
                            ['orderNumber' => $order->getIncrementId()]
                        );
                    $logData = $this->monduLogger->getTransactionByOrderUid($monduId);
                    if ($logData['mondu_state']  !== 'shipped' &&
                        $logData['mondu_state'] !== 'partially_shipped' &&
                        $logData['mondu_state'] !== 'partially_complete' &&
                        $logData['mondu_state'] !== 'complete'
                    ) {
                        throw new LocalizedException(__('Mondu: You cant partially refund order before shipment'));
                    }
                    $message = 'Mondu: Unexpected error: Could not send the credit note to Mondu,' .
                        ' please contact Mondu Support to resolve this issue.';
                    $this->messageManager->addErrorMessage($message);
                }
                return;
            } else {
                $this->logger
                    ->info(
                        'Whole order amount is being refunded, canceling the order',
                        ['orderNumber' => $order->getIncrementId()]
                    );
                $cancelData = $this->requestFactory->create(RequestFactory::CANCEL)
                    ->process(['orderUid' => $monduId]);

                if (isset($cancelData['errors']) && !isset($cancelData['order'])) {
                    $message = 'Mondu: Unexpected error: Could not cancel the order,' .
                        ' please contact Mondu Support to resolve this issue.';
                    $this->messageManager
                        ->addErrorMessage($message);
                    return;
                }

                $this->monduLogger->updateLogMonduData($monduId, $cancelData['order']['state']);

                $order->addStatusHistoryComment(
                    __('Mondu: The order with the id %1 was successfully canceled.', $monduId)
                );
                $order->save();
            }
        } catch (Exception $error) {
            $this->logger
                ->info(
                    'Error in UpdateOrder observer ',
                    ['orderNumber' => $order->getIncrementId(), 'e' => $error->getMessage()]
                );
            $this->messageManager->addErrorMessage('Mondu: ' . $error->getMessage());
        }
    }
}
