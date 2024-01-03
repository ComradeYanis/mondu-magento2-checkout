<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);


namespace Mondu\Mondu\Controller\Payment\Checkout;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Mondu\Mondu\Helper\ABTesting\ABTesting;
use Mondu\Mondu\Helper\Logger\Logger as MonduFileLogger;
use Mondu\Mondu\Model\Request\Factory as RequestFactory;

abstract class AbstractPaymentController implements ActionInterface
{
    /**
     * @var RedirectInterface
     */
    protected $redirect;
    /**
     * @var ResponseInterface
     */
    protected $response;
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var MonduFileLogger
     */
    protected $monduFileLogger;

    /**
     * @var RequestFactory
     */
    protected $requestFactory;

    /**
     * @var JsonFactory
     */
    protected $jsonResultFactory;

    /**
     * @var ABTesting
     */
    protected $aBTesting;
    /**
     * Execute
     *
     * @return ResponseInterface|ResultInterface
     */
    abstract public function execute();

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param RedirectInterface $redirect
     * @param Session $checkoutSession
     * @param MessageManagerInterface $messageManager
     * @param MonduFileLogger $monduFileLogger
     * @param RequestFactory $requestFactory
     * @param JsonFactory $jsonResultFactory
     */
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        RedirectInterface $redirect,
        Session $checkoutSession,
        MessageManagerInterface $messageManager,
        MonduFileLogger $monduFileLogger,
        RequestFactory $requestFactory,
        JsonFactory $jsonResultFactory,
        ABTesting $aBTesting
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->redirect = $redirect;
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
        $this->monduFileLogger = $monduFileLogger;
        $this->requestFactory = $requestFactory;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->aBTesting = $aBTesting;
    }


    /**
     * Redirect to url
     *
     * @param string $path
     * @return ResponseInterface
     */
    protected function redirect($path)
    {
        $this->redirect->redirect($this->response, $path);
        return $this->response;
    }

    /**
     * Process exceptions
     *
     * @param Exception $e
     * @param string $message
     * @return ResponseInterface
     */
    protected function processException(Exception $e, $message)
    {
        $this->messageManager->addExceptionMessage($e, __($message));
        return $this->redirect('checkout/cart');
    }

    /**
     * Redirect with error message
     *
     * @param string $message
     * @return ResponseInterface
     */
    protected function redirectWithErrorMessage($message)
    {
        $this->messageManager->addErrorMessage(__($message));
        return $this->redirect('checkout/cart');
    }
}
