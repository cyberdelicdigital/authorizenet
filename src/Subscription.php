<?php
namespace CyberdelicDigital\AuthorizeNet;

use net\authorize\api\contract\v1\PaymentType;
use net\authorize\api\constants\ANetEnvironment;
use net\authorize\api\contract\v1\CreditCardType;
use net\authorize\api\contract\v1\TransactionRequestType;
use net\authorize\api\contract\v1\CreateTransactionRequest;
use net\authorize\api\contract\v1\MerchantAuthenticationType;
use net\authorize\api\controller\CreateTransactionController;
use CyberdelicDigital\AuthorizeNet\Exceptions\NullResponseException;
use CyberdelicDigital\AuthorizeNet\Exceptions\MissingCredentialsException;
use CyberdelicDigital\AuthorizeNet\Exceptions\InvalidPaymentDetailsException;
use net\authorize\api\contract\v1\CustomerAddressType;
use net\authorize\api\contract\v1\CustomerDataType;
use CyberdelicDigital\AuthorizeNet\Exceptions\InvalidTransactionException;
use net\authorize\api\contract\v1\ARBCreateSubscriptionRequest;
use net\authorize\api\contract\v1\ARBSubscriptionType;
use net\authorize\api\contract\v1\UserFieldType;
use net\authorize\api\contract\v1\OrderType;
use net\authorize\api\contract\v1\PaymentScheduleType;
use net\authorize\api\contract\v1\PaymentScheduleType\IntervalAType;
use net\authorize\api\controller\ARBCreateSubscriptionController;

class Subscription
{
    const TRANSACTION_TYPE = 'authCaptureTransaction';

    public $details;
    public $isValid = false;
    private $merchantAuthentication;
    private $refId;
    private $creditCard;
    private $payment;
    private $transactionRequestType;

    /**
     * Class Constructor
     *
     * @param string $details
     * @param array $rules
     */
    public function __construct(string $details, array $rules = [])
    {
        $this->details = new TransactionDetails($details);
        $this->validator = new Validator($rules);

        if ($this->isValid = $this->validator->validate($this->details)) {
            $this->setMerchantDetails();
            $this->setRefId();
            $this->handleSubscription();
            $this->setCreditCard();
            // $this->setTransactionRequestType();
        } else {
            $requiredKeys = array_map(function ($key) {
                return $key . ', ';
            }, $this->validator->requiredKeys);
            $message = sprintf(
                "Invalid payment details supplied. Required keys are: %s", substr(implode($requiredKeys), 0, -2)
            );
            throw new InvalidPaymentDetailsException($message);
        }
    }

    /**
     * Add custom fields to the request type
     *
     * @param array $fields
     *
     * @return Transaction
     */
    public function addCustomFields(array $fields)
    {
        if (! $this->isValid) {
            throw new InvalidTransactionException('The transaction is not valid and cannot add your custom field');
        }
        foreach ($fields as $key => $value) {
            if (is_array($value)) {
                $this->addCustomField($value);
            }

            $customField = new UserFieldType();
            $customField->setName($key);
            $customField->setValue($value);

            $this->transactionRequestType->addToUserFields($customField);
        }

        return $this;
    }

    /**
     * Add invoice number and description to order
     *
     * @param string $invoiceNumber
     * @param string $description
     *
     * @return Transaction
     */
    public function addOrderDetails(string $invoiceNumber = null, string $description = null)
    {
        if (! $this->isValid) {
            throw new InvalidTransactionException('The transaction is not valid and cannot add your order details');
        }

        $order = new OrderType();
        if ($invoiceNumber) {
            $order->setInvoiceNumber($invoiceNumber);
        }

        if ($description) {
            $order->setDescription($description);
        }

        $this->subscription->setOrder($order);

        return $this;
    }

    /**
     * Execute the transaction with the supplied data
     *
     * @return void
     */
    public function execute()
    {
        $this->setBillingAddress();
        $request = new ARBCreateSubscriptionRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setRefId($this->refId);
        // die(var_dump($this->subscription));
        $request->setSubscription($this->subscription);
        $controller = new ARBCreateSubscriptionController($request);
        $response = $controller->executeWithApiResponse(ANET_ENVIRONMENT);

        if ($response != null) {
            $transactionResponse = $response->getMessages();
                return new TransactionResponse(json_encode($transactionResponse));
        } else {
            $message = sprintf("Null Response returned from Authorize.Net API");
            throw new NullResponseException($message);
        }
    }

    private function handleSubscription()
    {
        $subscription = new ARBSubscriptionType();
        $subscription->setName($this->details->need);

        $interval = new IntervalAType();
        $interval->setLength(1);
        $interval->setUnit("months");

        $paymentSchedule = new PaymentScheduleType();
        $paymentSchedule->setInterval($interval);
        $paymentSchedule->setStartDate(new \DateTime(date('Y-m-d')));
        $paymentSchedule->setTotalOccurrences(9999);

        $subscription->setPaymentSchedule($paymentSchedule);
        $subscription->setAmount($this->details->amount);

        $this->subscription = $subscription;
    }

    /**
     * Common setup for API credentials
     *
     * @return void
     */
    private function setMerchantDetails(): void
    {
        $validCredentials = (defined('AUTHORIZENET_LOGIN_ID') && defined('AUTHORIZENET_TRANSACTION_KEY'));

        if (! $validCredentials) {
            throw new MissingCredentialsException('Proper Authorize.Net credentials were not found in your project.');
        }
        $this->merchantAuthentication = new MerchantAuthenticationType();
        $this->merchantAuthentication->setName(AUTHORIZENET_LOGIN_ID);
        $this->merchantAuthentication->setTransactionKey(AUTHORIZENET_TRANSACTION_KEY);
    }

    /**
     * Set Unique Reference Id for the transaction
     *
     * @return void
     */
    private function setRefId(): void
    {
        $this->refId = 'ref#'.time();
    }

    /**
     * Create the payment data for the credit card
     *
     * @return void
     */
    private function setCreditCard(): void
    {
        $this->creditCard = new CreditCardType();
        $this->creditCard->setCardNumber($this->details->cardNumber);
        $this->creditCard->setExpirationDate($this->details->expirationDate);
        $this->payment = new PaymentType();
        $this->payment->setCreditCard($this->creditCard);
        $this->subscription->setPayment($this->payment);
    }

    // /**
    //  * Create a Transaction type
    //  *
    //  * @return void
    //  */
    // private function setTransactionRequestType(): void
    // {
    //     $this->transactionRequestType = new TransactionRequestType();
    //     $this->transactionRequestType->setTransactionType(self::TRANSACTION_TYPE);
    //     $this->transactionRequestType->setAmount($this->details->amount);
    //     $this->transactionRequestType->setPayment($this->payment);
    //     $this->transactionRequestType->setBillTo($this->setBillingAddress());
    //     $this->transactionRequestType->setCustomer($this->setEmail());
    // }

    private function setBillingAddress()
    {
        $customerAddress = new CustomerAddressType();
        $customerAddress->setFirstName($this->details->customer->firstName);
        $customerAddress->setLastName($this->details->customer->lastName);
        $customerAddress->setAddress($this->details->customer->street);
        $customerAddress->setCity($this->details->customer->city);
        $customerAddress->setState($this->details->customer->state);
        $customerAddress->setZip($this->details->customer->zip);
        $customerAddress->setCountry($this->details->customer->country);

        $this->subscription->setBillTo($customerAddress);
    }

    private function setEmail()
    {
        $customerData = new CustomerDataType();
        $customerData->setEmail($this->details->customer->customerEmail);

        return $customerData;
    }
}
