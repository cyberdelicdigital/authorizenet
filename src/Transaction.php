<?php
namespace CyberdelicDigital\AuthorizeNet;

use net\authorize\api\contract\v1\PaymentType;
use net\authorize\api\constants\ANetEnvironment;
use net\authorize\api\contract\v1\CreditCardType;
use net\authorize\api\contract\v1\TransactionRequestType;
use net\authorize\api\contract\v1\CreateTransactionRequest;
use net\authorize\api\contract\v1\MerchantAuthenticationType;
use net\authorize\api\controller\CreateTransactionController;
use CyberdelicDigital\AuthorizeNet\Exceptions\MissingCredentialsException;
use CyberdelicDigital\AuthorizeNet\Exceptions\InvalidPaymentDetailsException;

class Transaction
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
            $this->setCreditCard();
            $this->setTransactionRequestType();
        } else {
            $requiredKeys = array_map(function ($key) {
                return $key . ', ';
            }, $this->validator->requiredKeys);
            $message = sprintf("Invalid payment details supplied. Required keys are: %s", substr(implode($requiredKeys), 0, -2));
            throw new InvalidPaymentDetailsException($message);
        }
    }

    /**
     * Execute the transaction with the supplied data
     *
     * @return void
     */
    public function execute()
    {
        $request = new CreateTransactionRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setRefId($this->refId);
        $request->setTransactionRequest($this->transactionRequestType);
        $controller = new CreateTransactionController($request);
        $response = $controller->executeWithApiResponse(ANetEnvironment::SANDBOX);

        if ($response != null) {
            $tresponse = $response->getTransactionResponse();
            if (($tresponse != null) && ($tresponse->getResponseCode() == '1')) {
                echo 'Charge Credit Card AUTH CODE : '.$tresponse->getAuthCode()."\n";
                echo 'Charge Credit Card TRANS ID  : '.$tresponse->getTransId()."\n";
            } else {
                echo "Charge Credit Card ERROR :  Invalid response\n";
            }
        } else {
            echo  'Charge Credit Card Null response returned';
        }
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
    }

    /**
     * Create a Transaction type
     *
     * @return void
     */
    private function setTransactionRequestType(): void
    {
        $this->transactionRequestType = new TransactionRequestType();
        $this->transactionRequestType->setTransactionType(self::TRANSACTION_TYPE);
        $this->transactionRequestType->setAmount($this->details->amount);
        $this->transactionRequestType->setPayment($this->payment);
    }
}
