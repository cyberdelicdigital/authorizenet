<?php

use PHPUnit\Framework\TestCase;
use CyberdelicDigital\AuthorizeNet\Transaction;
use CyberdelicDigital\AuthorizeNet\TransactionDetails;
use CyberdelicDigital\AuthorizeNet\Exceptions\InvalidPaymentDetailsException;
use CyberdelicDigital\AuthorizeNet\Validator;

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
    public function it_validates_the_required_keys()
    {
        $transaction = new Transaction(json_encode($this->deets));
        $validator = new Validator();
        $this->assertTrue($validator->validate($transaction->details));


        $this->expectException(InvalidPaymentDetailsException::class);
        $transaction = new Transaction(json_encode($this->invalidDeets));
        $validator = new Validator();
        $validator->validate($transaction->details);
    }

    /** @test */
    public function it_handles_custom_rules()
    {
        $additionalFields = ['test' => 'value', 'test2' => 'value'];
        $validTransaction = array_merge($this->deets, $additionalFields);
        $transaction = new Transaction(json_encode($validTransaction), ['test', 'test2']);
        $this->assertTrue($transaction->isValid);
    }

    /** @test */
    public function it_errors_when_custom_rules_are_not_met()
    {
        $this->expectException(InvalidPaymentDetailsException::class);
        $incompleteDetails = ['test' => 'value'];
        $invalidTransaction = array_merge($this->deets, $incompleteDetails);
        $transaction = new Transaction(json_encode($invalidTransaction), ['test', 'missing']);
        $this->assertFalse($transaction->isValid);
    }
}
