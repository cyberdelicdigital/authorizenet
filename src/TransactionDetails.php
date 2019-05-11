<?php
namespace CyberdelicDigital\AuthorizeNet;

class TransactionDetails
{
    public function __construct(string $details)
    {
        foreach (json_decode($details, true) as $key => $value) {
            $this->$key = $value;
        }
    }
}
