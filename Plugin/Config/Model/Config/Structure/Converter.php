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
namespace PostFinanceCheckout\Payment\Plugin\Config\Model\Config\Structure;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Module\Dir\Reader as ModuleDirReader;
use Magento\Store\Model\StoreManagerInterface;
use PostFinanceCheckout\Payment\Api\PaymentMethodConfigurationRepositoryInterface;
use PostFinanceCheckout\Payment\Api\Data\PaymentMethodConfigurationInterface;
use PostFinanceCheckout\Payment\Model\PaymentMethodConfiguration;
use PostFinanceCheckout\Payment\Model\Config\Structure\Reader;

/**
 * Interceptor to dynamically extend config structure with the PostFinance Checkout payment method data.
 */
class Converter
{

    /**
     *
     * @var PaymentMethodConfigurationRepositoryInterface
     */
    private $paymentMethodConfigurationRepository;

    /**
     *
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     *
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     *
     * @var Reader
     */
    private $reader;

    /**
     *
     * @var ModuleDirReader
     */
    private $moduleReader;

    /**
     *
     * @var DriverPool
     */
    private $driverPool;

    /**
     *
     * @var string
     */
    private $template;

    /**
     *
     * @param PaymentMethodConfigurationRepositoryInterface $paymentMethodConfigurationRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resourceConnection
     * @param Reader $reader
     * @param ModuleDirReader $moduleReader
     * @param DriverPool $driverPool
     */
    public function __construct(PaymentMethodConfigurationRepositoryInterface $paymentMethodConfigurationRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder, StoreManagerInterface $storeManager,
        ResourceConnection $resourceConnection, Reader $reader, ModuleDirReader $moduleReader, DriverPool $driverPool)
    {
        $this->paymentMethodConfigurationRepository = $paymentMethodConfigurationRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->resourceConnection = $resourceConnection;
        $this->reader = $reader;
        $this->moduleReader = $moduleReader;
        $this->driverPool = $driverPool;
    }

    public function beforeConvert(\Magento\Config\Model\Config\Structure\Converter $subject, $source)
    {
        if (! $this->isTableExists()) {
            return [
                $source
            ];
        }

        $configMerger = $this->reader->createConfigMerger();
        $configMerger->setDom($source);

        $searchCriteria = $this->searchCriteriaBuilder->addFilter(PaymentMethodConfigurationInterface::STATE,
            [
                PaymentMethodConfiguration::STATE_ACTIVE,
                PaymentMethodConfiguration::STATE_INACTIVE
            ], 'in')->create();

        $configurations = $this->paymentMethodConfigurationRepository->getList($searchCriteria)->getItems();
        foreach ($configurations as $configuration) {
            $configMerger->merge($this->reader->processDocument($this->generateXml($configuration)));
        }

        return [
            $configMerger->getDom()
        ];
    }

    private function generateXml(PaymentMethodConfigurationInterface $configuration)
    {
        return str_replace([
            '{id}',
            '{name}'
        ], [
            $configuration->getEntityId(),
            $configuration->getConfigurationName()
        ], $this->getTemplate());
    }

    /**
     * Gets whether the payment method configuration database table exists.
     *
     * @return boolean
     */
    private function isTableExists()
    {
        return $this->resourceConnection->getConnection()->isTableExists(
            $this->resourceConnection->getTableName('postfinancecheckout_payment_method_configuration'));
    }

    private function getTemplate()
    {
        if ($this->template == null) {
            $templatePath = $this->moduleReader->getModuleDir(\Magento\Framework\Module\Dir::MODULE_ETC_DIR,
                'PostFinanceCheckout_Payment') . '/adminhtml/system-method-template.xml';
            $this->template = $this->driverPool->getDriver(DriverPool::FILE)->fileGetContents($templatePath);
        }
        return $this->template;
    }
}