<?php
namespace CyberdelicDigital\AuthorizeNet;

class Validator
{
    const REQUIRED_KEYS = [
        'cardNumber',
        'expirationDate',
        'cardCode',
        'amount',
        'customer'
    ];

    protected $rules;
    public $requiredKeys;

    public function __construct(array $rules = [])
    {
        $this->rules = $rules;

        $this->requiredKeys = array_merge(self::REQUIRED_KEYS, $rules);
    }

    public function validate(TransactionDetails $details)
    {
        return (
            array_intersect($this->requiredKeys, array_keys(get_object_vars($details))) === $this->requiredKeys
        );
    }
}
