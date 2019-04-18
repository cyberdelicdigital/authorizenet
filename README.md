# Authorize.Net Transactions for PHP
A simple wrapper for the Authorize.Net PHP SDK

## **Installation**
`composer require CyberdelicDigital/authorizenet`

### **Environment/Global Variables**
#### Required
`define('AUTHORIZENET_LOGIN_ID', 'YOUR_LOGIN_ID');`

  Login ID from your Authorize.Net Merchant Dashboard

`define('AUTHORIZENET_TRANSACTION_KEY', 'YOUR_TRANSACTION_KEY');`

  Transaction Key from your Authorize.Net Merchant Dashboard

#### Optional
`define('AUTHORIZENET_LOG_FILE', 'authorizenet_log');`
  Relative path to the file where you would like to keep transaction logs (for debugging purposes)

## **Usage**
