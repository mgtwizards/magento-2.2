<?php
/**
 * PostFinance Checkout Magento 2
 *
 * This Magento 2 extension enables to process payments with PostFinance Checkout (https://www.postfinance.ch/checkout/).
 *
 * @package PostFinanceCheckout_Payment
 * @author wallee AG (http://www.wallee.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)
 */
namespace PostFinanceCheckout\Payment\Model\Webhook;

/**
 * Webhook listener pool interface.
 */
interface ListenerPoolInterface
{

    /**
     * Retrieves listener.
     *
     * @param string $listenerCode
     * @return ListenerInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function get($listenerCode);
}