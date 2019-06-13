<?php

namespace CyberdelicDigital\AuthorizeNet;

use net\authorize\api\contract\v1\CustomerAddressType;
use net\authorize\api\contract\v1\CustomerDataType;

class Customer
{
    public $billingAddress = '';
    public $customerEmail = '';
    public $firstName = '';
    public $lastName = '';
    public $street = '';
    public $city = '';
    public $state = '';
    public $zip = '';
    public $country = 'USA';

    public function __construct(string $details)
    {
        foreach (json_decode($details, true) as $key => $value) {
            $this->$key = $value;
        }
    }

    private function setBillingAddress()
    {
        $customerAddress = new CustomerAddressType();
        $customerAddress->setFirstName($this->firstName);
        $customerAddress->setLastName($this->lastName);
        $customerAddress->setAddress($this->street);
        $customerAddress->setCity($this->city);
        $customerAddress->setState($this->state);
        $customerAddress->setZip($this->zip);
        $customerAddress->setCountry($this->country);

        $this->billingAddress = $customerAddress;
    }

    private function setEmail()
    {
        $customerData = new CustomerDataType();
        $customerData->setEmail($this->email);

        $this->customerEmail = $customerData;
    }
}
