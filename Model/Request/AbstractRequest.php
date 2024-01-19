<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);

namespace Mondu\Mondu\Model\Request;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Mondu\Mondu\Model\Config\MonduConfigProvider;

abstract class AbstractRequest implements RequestInterface
{

    /**
     * @var mixed
     */
    protected $envInformation;

    /**
     * @var mixed
     */
    protected $requestParams;

    /**
     * @var bool
     */
    protected $sendEvents = true;

    /**
     * @var string
     */
    protected $requestOrigin;

    /**
     * @var RequestInterface
     */
    protected $errorEventsHandler;
    /**
     * @var ClientInterface
     */
    protected $curl;
    /**
     * @var SerializerInterface
     */
    protected $serializer;
    /**
     * @var MonduConfigProvider
     */
    protected $configProvider;

    /**
     * @param ClientInterface $curl
     * @param SerializerInterface $serializer
     * @param MonduConfigProvider $configProvider
     */
    public function __construct(
        ClientInterface $curl,
        SerializerInterface $serializer,
        MonduConfigProvider $configProvider
    ) {
        $this->curl = $curl;
        $this->serializer = $serializer;
        $this->configProvider = $configProvider;
    }

    /**
     * Method that sends the request to api
     *
     * @param array|null $params
     * @return mixed
     * @throws LocalizedException
     */
    abstract protected function request($params);

    /**
     * Sends Request
     *
     * @param mixed $params
     * @return mixed
     * @throws Exception
     */
    public function process($params = null)
    {
        $exception = null;
        $data = null;
        try {
            $data = $this->request($params);
        } catch (Exception $e) {
            $exception = $e;
        }

        if ($this->sendEvents) {
            $this->sendEvents($exception);
        }

        if ($exception) {
            throw $exception;
        }

        return $data;
    }

    /**
     * Sets Curl headers
     *
     * @param array $headers
     * @return $this
     */
    public function setCommonHeaders($headers): AbstractRequest
    {
        $this->curl->setHeaders($headers);
        return $this;
    }

    /**
     * Sets env information
     *
     * @param array $environment
     * @return $this
     */
    public function setEnvironmentInformation($environment): AbstractRequest
    {
        if (!isset($this->envInformation)) {
            $this->envInformation = $environment;
        }
        return $this;
    }

    /**
     * Sets request origin
     *
     * @param string $origin
     * @return $this
     */
    public function setRequestOrigin($origin)
    {
        if (!isset($this->requestOrigin)) {
            $this->requestOrigin = $origin;
        }
        return $this;
    }

    /**
     * Send error events to Mondu Api
     *
     * @param Exception|null $exception
     * @return void
     */
    public function sendEvents($exception = null)
    {
        $statusFirstDigit = ((string) $this->curl->getStatus())[0];
        if ($statusFirstDigit !== '1' && $statusFirstDigit !== '2') {
            $curlData = [
                'response_status' => (string) $this->curl->getStatus(),
                'response_body' => $this->serializer->unserialize($this->curl->getBody()) ?? [],
                'request_body' => $this->serializer->unserialize($this->requestParams ?? '') ?? [],
                'origin_event' => $this->requestOrigin
            ];

            $data = array_merge($this->envInformation, $curlData);

            if ($exception) {
                $data = array_merge($data, [
                    'error_trace' => $exception->getTraceAsString(),
                    'error_message' => $exception->getMessage()
                ]);
            } else {
                $data = array_merge($data, [
                    'error_trace' => $this->serializer->unserialize(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))
                ]);
            }

            $this->errorEventsHandler->process($data);
        }
    }

    /**
     * Sends request
     *
     * @param string $method
     * @param string $url
     * @param string $params
     * @return string
     */
    public function sendRequestWithParams($method, $url, $params = null)
    {
        $this->requestParams = $params;

        if ($method === 'post') {
            // Ensure we never send the "Expect: 100-continue" header
            $this->curl->addHeader('Expect', '');
        }

        if ($params) {
            $this->curl->{$method}($url, $params);
        } else {
            $this->curl->{$method}($url);
        }
        return $this->curl->getBody();
    }

    /**
     * Sets error events handler
     *
     * @param mixed $handler
     * @return $this
     */
    public function setErrorEventsHandler($handler)
    {
        $this->errorEventsHandler = $handler;
        return $this;
    }
}
