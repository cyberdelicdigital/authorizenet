<?php

use PHPUnit\Framework\TestCase;
use CyberdelicDigital\AuthorizeNet\Transaction;
use CyberdelicDigital\AuthorizeNet\TransactionDetails;
use CyberdelicDigital\AuthorizeNet\Customer;

final class TransactionTest extends TestCase
{
    private $deets;
    private $invalidDeets;
    private $transaction;

    public function setUp(): void
    {
        $this->deets = [
            'cardNumber' => '4111111111111111',
            'expirationDate' => '2038-12',
            'cardCode' => '123',
            'amount' => 151.25,
            'customer' => [
                'firstName' => 'Joe',
                'lastName' => 'Testerson',
                'street' => '123 Example Street',
                'city' => 'Hollywood',
                'state' => 'CA',
                'zip' => '90210',
                'country' => 'USA',
                'email' => 'joe@testerson.com'
            ]
        ];

        $this->invalidDeets = [
            'expirationDate' => '2038-12',
            'cardCode' => '123',
            'amount' => 151.25,
            'customer' => [
                'firstName' => 'Joe',
                'lastName' => 'Testerson',
                'street' => '123 Example Street',
                'city' => 'Hollywood',
                'state' => 'CA',
                'zip' => '90210',
                'country' => 'USA',
                'email' => 'joe@testerson.com'
            ]
        ];

        $this->transaction = new Transaction(json_encode($this->deets));

        parent::setUp();
    }
    /** @test */
    public function it_creates_a_detail_object()
    {
        $this->assertInstanceOf(TransactionDetails::class, $this->transaction->details);
    }

    /** @test */
    public function it_creates_a_customer()
    {
        $this->assertInstanceOf(Customer::class, $this->transaction->details->customer);
    }
}
