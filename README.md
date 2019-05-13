# Authorize.Net Transactions for PHP
A simple wrapper for the Authorize.Net PHP SDK

## Installation
    composer require CyberdelicDigital/authorizenet

## Environment/Global Variables
#### Required
Login ID from your Authorize.Net Merchant Dashboard

    define('AUTHORIZENET_LOGIN_ID', 'YOUR_LOGIN_ID');

Transaction Key from your Authorize.Net Merchant Dashboard

    define('AUTHORIZENET_TRANSACTION_KEY', 'YOUR_TRANSACTION_KEY');


#### Optional
Relative path to the file where you would like to keep transaction logs (for debugging purposes)

    define('AUTHORIZENET_LOG_FILE', 'authorizenet_log');

## Usage
This package accepts a payload of structured data (JSON) and returns the response from Authorize.Net

### Parameters
| Key | Required | Type | Notes |
|-----|----------|------|-------|
| cardNumber | yes | String |
| expirationDate | yes | String | Format: `YYYY-mm`
| cardCode | yes | String | Also known as CVV
| amount | yes | Number | Example: `151.25`

## Examples
For the following example, we'll use a simple JSON object consisting of only the minimum required fields to complete the transaction.
```json
{
    "cardNumber": "4111111111111111",
    "expirationDate": "2038-12",
    "cardCode": "123",
    "amount": 151.25
}
```

```php
use CyberdelicDigital\AuthorizeNet\Transaction;

public function chargeCard($details)
{
    $transaction = new Transaction($details);

    $response = $transaction->execute();

    if ($response->isSuccess()) {
        return $response;
    }

    return $response->getErrors();
}
```

## Custom Validation Rules
In addition to the JSON data passed into the `Transaction` class, you can also pass a second parameter consisting of an array of any additional custom fields you need to be required.

>**Note**: You do not need to specify the required fields listed in the chart above. They will always be required for a valid transaction.

```php
$requiredFields = ['field_1', 'field_2'];

$transaction = new Transaction($details, $requiredFields);
```

## Transaction Response
Calling the `execute` method on the transaction object will return an instance of `CyberdelicDigital\AuthorizeNet\TransactionResponse`.


