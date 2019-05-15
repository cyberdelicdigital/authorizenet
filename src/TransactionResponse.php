<?php
namespace CyberdelicDigital\AuthorizeNet;

class TransactionResponse
{
    public function __construct(string $response)
    {
        foreach (json_decode($response, true) as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function hasErrors()
    {
        return property_exists($this, 'errors');
    }
}
