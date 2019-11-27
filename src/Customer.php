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
}
