<?php

use PHPUnit\Framework\TestCase;
use CyberdelicDigital\AuthorizeNet\Transaction;
use CyberdelicDigital\AuthorizeNet\TransactionDetails;
use CyberdelicDigital\AuthorizeNet\Exceptions\InvalidPaymentDetailsException;

final class ValidationTest extends TestCase
{
    public $deets;
    public $invalidDeets;

    public function setUp(): void
    {
        $this->deets = [
            'cardNumber' => '4111111111111111',
            'expirationDate' => '2038-12',
            'cardCode' => '123',
            'amount' => 151.25
        ];

        $this->invalidDeets = [
            'expirationDate' => '2038-12',
            'cardCode' => '123',
            'amount' => 151.25
        ];

        parent::setUp();
    }

    /** @test */
    public function it_creates_a_detail_object()
    {
        $transaction = new Transaction(json_encode($this->deets));

        $this->assertInstanceOf(TransactionDetails::class, $transaction->details);
    }

    /** @test */
    public function it_validates_the_keys()
    {
        $transaction = new Transaction(json_encode($this->deets));
        $this->assertTrue($transaction->details->validate());

        $this->expectException(InvalidPaymentDetailsException::class);
        $transaction = new Transaction(json_encode($this->invalidDeets));
    }
}
