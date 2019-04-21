<?php
namespace CyberdelicDigital\AuthorizeNet;

class TransactionDetails
{
    const REQUIRED_KEYS = [
        'cardNumber',
        'expirationDate',
        'cardCode',
        'amount'
    ];

    public function __construct(string $details)
    {
        foreach (json_decode($details, true) as $key => $value) {
            $this->$key = $value;
        }
    }

    public function validate()
    {

    }
}
