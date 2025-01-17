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
namespace PostFinanceCheckout\Payment\Model\Service;

use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use PostFinanceCheckout\Payment\Api\PaymentMethodConfigurationManagementInterface;
use PostFinanceCheckout\Payment\Helper\Data as Helper;
use PostFinanceCheckout\Payment\Model\ApiClient;
use PostFinanceCheckout\Sdk\Model\Gender;
use PostFinanceCheckout\Sdk\Model\Transaction;
use PostFinanceCheckout\Sdk\Service\TransactionService;

/**
 * Abstract service to handle transactions.
 */
abstract class AbstractTransactionService
{

    /**
     *
     * @var ResourceConnection
     */
    private $resource;

    /**
     *
     * @var Helper
     */
    private $helper;

    /**
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     *
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     *
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     *
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     *
     * @var PaymentMethodConfigurationManagementInterface
     */
    private $paymentMethodConfigurationManagement;

    /**
     *
     * @var ApiClient
     */
    private $apiClient;

    /**
     *
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     *
     * @param ResourceConnection $resource
     * @param Helper $helper
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerRegistry $customerRegistry
     * @param CartRepositoryInterface $quoteRepository
     * @param TimezoneInterface $timezone
     * @param PaymentMethodConfigurationManagementInterface $paymentMethodConfigurationManagement
     * @param ApiClient $apiClient
     * @param CookieManagerInterface $cookieManager
     */
    public function __construct(ResourceConnection $resource, Helper $helper, ScopeConfigInterface $scopeConfig,
        CustomerRegistry $customerRegistry, CartRepositoryInterface $quoteRepository, TimezoneInterface $timezone,
        PaymentMethodConfigurationManagementInterface $paymentMethodConfigurationManagement, ApiClient $apiClient,
        CookieManagerInterface $cookieManager)
    {
        $this->resource = $resource;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->customerRegistry = $customerRegistry;
        $this->quoteRepository = $quoteRepository;
        $this->timezone = $timezone;
        $this->paymentMethodConfigurationManagement = $paymentMethodConfigurationManagement;
        $this->apiClient = $apiClient;
        $this->cookieManager = $cookieManager;
    }

    /**
     * Updates the payment method configurations with the given data.
     *
     * @param \PostFinanceCheckout\Sdk\Model\PaymentMethodConfiguration[] $paymentMethods
     */
    protected function updatePaymentMethodConfigurations($paymentMethods)
    {
        foreach ($paymentMethods as $paymentMethod) {
            $this->paymentMethodConfigurationManagement->update($paymentMethod);
        }
    }

    /**
     * Gets the transaction by its ID.
     *
     * @param int $spaceId
     * @param int $transactionId
     * @return Transaction
     */
    public function getTransaction($spaceId, $transactionId)
    {
        return $this->apiClient->getService(TransactionService::class)->read($spaceId, $transactionId);
    }

    /**
     * Updates the transaction information on the quote.
     *
     * @param Quote $quote
     * @param Transaction $transaction
     */
    protected function updateQuote(Quote $quote, Transaction $transaction)
    {
        $this->resource->getConnection()->update($this->resource->getTableName('quote'),
            [
                'postfinancecheckout_space_id' => $transaction->getLinkedSpaceId(),
                'postfinancecheckout_transaction_id' => $transaction->getId()
            ], [
                'entity_id = ?' => $quote->getId()
            ]);
    }

    /**
     * Gets the device session identifier from the cookie.
     *
     * @return string|NULL
     */
    protected function getDeviceSessionIdentifier()
    {
        return $this->cookieManager->getCookie('postfinancecheckout_device_id');
    }

    /**
     * Gets the customer's tax number.
     *
     * @param string $taxNumber
     * @param int $customerId
     * @return string
     */
    protected function getTaxNumber($taxNumber, $customerId)
    {
        if ($taxNumber !== null) {
            return $taxNumber;
        } elseif (! empty($customerId)) {
            return $this->customerRegistry->retrieve($customerId)->getTaxvat();
        } else {
            return null;
        }
    }

    /**
     * Gets the customer's gender.
     *
     * @param string $gender
     * @param int $customerId
     * @return string
     */
    protected function getGender($gender, $customerId)
    {
        if ($gender == null && ! empty($customerId)) {
            $gender = $this->customerRegistry->retrieve($customerId)->getGender();
        }

        if ($gender == 2) {
            return Gender::FEMALE;
        } elseif ($gender == 1) {
            return Gender::MALE;
        } else {
            return null;
        }
    }

    /**
     * Gets the customer's email address.
     *
     * @param string $customerEmailAddress
     * @param int $customerId
     * @return string
     */
    protected function getCustomerEmailAddress($customerEmailAddress, $customerId)
    {
        if ($customerEmailAddress != null) {
            return $customerEmailAddress;
        } elseif (! empty($customerId)) {
            $customer = $this->customerRegistry->retrieve($customerId);
            $customerMail = $customer->getEmail();
            if (! empty($customerMail)) {
                return $customerMail;
            } else {
                return null;
            }
        }
    }

    /**
     * Gets the customer's date of birth.
     *
     * @param string $dateOfBirth
     * @param int $customerId
     * @return string
     */
    protected function getDateOfBirth($dateOfBirth, $customerId)
    {
        if ($dateOfBirth === null && ! empty($customerId)) {
            $customer = $this->customerRegistry->retrieve($customerId);
            $dateOfBirth = $customer->getDob();
        }

        if ($dateOfBirth !== null) {
            return \substr($dateOfBirth, 0, 10);
        }
    }
}