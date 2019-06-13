<?php
namespace CyberdelicDigital\AuthorizeNet;

class TransactionDetails
{
    public $customer;

    public function __construct(string $details)
    {
        foreach (json_decode($details, true) as $key => $value) {
            $this->$key = $value;
        }
        $customer = new Customer(json_encode($this->customer));

        $this->customer = $customer;
    }
}
