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

        $this->validate();
    }

    public function validate()
    {
        return (array_intersect(self::REQUIRED_KEYS, array_keys(get_object_vars($this))) === self::REQUIRED_KEYS);
    }
}
