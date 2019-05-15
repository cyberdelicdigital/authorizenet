<?php
namespace CyberdelicDigital\AuthorizeNet;

class TransactionResponse
{
    /**
     * Class Constructor
     *
     * @param string $response
     */
    public function __construct(string $response)
    {
        foreach (json_decode($response, true) as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Confirms whether the AuthorizeNet response has errors
     *
     * @return boolean
     */
    public function hasErrors(): bool
    {
        return property_exists($this, 'errors');
    }
}
