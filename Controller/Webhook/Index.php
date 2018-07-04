<?php
/**
 * PostFinance Checkout Magento 2
 *
 * This Magento 2 extension enables to process payments with PostFinance Checkout (https://www.postfinance.ch/).
 *
 * @package PostFinanceCheckout_Payment
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)
 */
namespace PostFinanceCheckout\Payment\Controller\Webhook;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use PostFinanceCheckout\Payment\Model\Service\WebhookService;
use PostFinanceCheckout\Payment\Model\Webhook\Request;

/**
 * Frontend controller action to proces webhook requests.
 */
class Index extends \PostFinanceCheckout\Payment\Controller\Webhook
{

    /**
     *
     * @var WebhookService
     */
    protected $_webhookService;

    /**
     *
     * @param Context $context
     * @param WebhookService $webhookService
     */
    public function __construct(Context $context, WebhookService $webhookService)
    {
        parent::__construct($context);
        $this->_webhookService = $webhookService;
    }

    public function execute()
    {
        http_response_code(500);
        $this->getResponse()->setHttpResponseCode(500);
        try {
            $this->_webhookService->execute($this->parseRequest());
        } catch (NotFoundException $e) {
            throw new \Exception($e);
        }
        $this->getResponse()->setHttpResponseCode(200);
    }

    /**
     * Parses the HTTP request.
     *
     * @throws \InvalidArgumentException
     * @return \PostFinanceCheckout\Payment\Model\Webhook\Request
     */
    protected function parseRequest()
    {
        $jsonRequest = $this->getRequest()->getContent();
        if (empty($jsonRequest)) {
            throw new \InvalidArgumentException('Empty request.');
        }
        $parsedRequest = \json_decode($jsonRequest, true);
        if (\json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Unable to unserialize value.');
        }
        return new Request($parsedRequest);
    }
}