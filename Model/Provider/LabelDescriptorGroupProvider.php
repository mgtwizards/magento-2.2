<?php
/**
 * PostFinance Checkout Magento 2
 *
 * This Magento 2 extension enables to process payments with PostFinance Checkout (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html/).
 *
 * @package PostFinanceCheckout_Payment
 * @author wallee AG (http://www.wallee.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)
 */
namespace PostFinanceCheckout\Payment\Model\Provider;

use Magento\Framework\Cache\FrontendInterface;
use PostFinanceCheckout\Payment\Model\ApiClient;
use PostFinanceCheckout\Sdk\Service\LabelDescriptionGroupService;

/**
 * Provider of label descriptor group information from the gateway.
 */
class LabelDescriptorGroupProvider extends AbstractProvider
{

    /**
     *
     * @var ApiClient
     */
    private $apiClient;

    /**
     *
     * @param FrontendInterface $cache
     * @param ApiClient $apiClient
     */
    public function __construct(FrontendInterface $cache, ApiClient $apiClient)
    {
        parent::__construct($cache, 'postfinancecheckout_payment_label_descriptor_groups',
            \PostFinanceCheckout\Sdk\Model\LabelDescriptorGroup::class);
        $this->apiClient = $apiClient;
    }

    /**
     * Gets the label descriptor group by the given id.
     *
     * @param int $id
     * @return \PostFinanceCheckout\Sdk\Model\LabelDescriptorGroup
     */
    public function find($id)
    {
        return parent::find($id);
    }

    /**
     * Gets a list of label descriptor groups.
     *
     * @return \PostFinanceCheckout\Sdk\Model\LabelDescriptorGroup[]
     */
    public function getAll()
    {
        return parent::getAll();
    }

    protected function fetchData()
    {
        return $this->apiClient->getService(LabelDescriptionGroupService::class)->all();
    }

    protected function getId($entry)
    {
        /** @var \PostFinanceCheckout\Sdk\Model\LabelDescriptorGroup $entry */
        return $entry->getId();
    }
}