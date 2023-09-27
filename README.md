# CM.com Payments Magento 2 module

1. [Get started](#get-started)
2. [Installation & Update the CM.com Payments Magento 2 plugin](#installation--update-the-cmcom-payments-magento-2-plugin)
3. [About CM.com Payments](#about-cmcom-payments)
4. [Supported CM.com Payments Methods](#supported-cmcom-payments-methods)
5. [Requirements](#requirements)
6. [Setup local development environment](#setup-local-development-environment)
7. [Payment methods](#payment-methods)
8. [Issues and support](#issues-and-support)
## Get started

Before you begin to integrate Magento with the CM.com payments platform, make sure that you have performed the following steps: 

1. Sign up for a test account with CM.com Payments at https://www.cm.com/register/?app=81e52ab7-4cfc-4b89-8ae8-f5be73bab15d&product=PAYMENTMETHODS
2. Create a payment method profile in the CM Portal
3. Install and configure the magento module

## Installation & Update the CM.com Payments Magento 2 plugin

1. Installation by Composer

   ```
   composer require cmdotcom-plugins/magento2-payments
   ```

   ```
   php bin/magento module:enable CM_Payments
   php bin/magento setup:upgrade
   php bin/magento cache:clean
   ```

   If Magento® is running in production mode, deploy the static content:

   ```
   php bin/magento setup:static-content:deploy
   ```

2. Update by Composer

   ```
   composer update cmdotcom-plugins/magento2-payments
   ```

   ```
   php bin/magento setup:upgrade
   php bin/magento cache:clean
   ```

   If Magento® is running in production mode, deploy the static content:

   ```
   php bin/magento setup:static-content:deploy
   ```

3. Configuration
   
   To configure the CM.com Payments extension you can go to your Magento® 2 admin portal, to **Stores** > **Configuration** > **CM.com Payments**
   1. **General settings:** Set ‘Enabled’ to ‘Yes’
   2. **General settings:**  Enter the Test and/or API key of your webshop. You received the API credentials by email from CM.com Payments ([register link](https://www.cm.com/register/?app=81e52ab7-4cfc-4b89-8ae8-f5be73bab15d&product=PAYMENTMETHODS))
   3. **General settings:** Set payment method profile that is configured in the CM Portal 
   4. **Payment methods:** Configure each payment method you would like to offer in your webshop
   5. **Magento:** Refresh the caches after saving the configuration

## About CM.com Payments

https://www.cm.com/payments

## Supported CM.com Payments Methods

- iDEAL, iDEAL QR
- Banktransfer
- Credit Cards (American Express, Mastercard, Maestro, Visa, V-Pay)
- Bancontact, Bancontact Mobile
- Sofortüberweisung, EBanking
- Paysafecard
- ELV
- Giropay
- KBC, CBC
- Belfius Pay Button
- ING Home Pay
- Giftcards
- Point of Sale
- Apple Pay, Apple Business Chat
- Google Pay
- PayPal
- Sepa Direct Debit
- Afterpay
- Klarna
- Przelewy24, BLIK

For more details on the configuring see the payment methonds section below.

## Requirements

- Magento Open Source / Enterprise version 2.3.x & 2.4.x
- PHP 7.3+

## Setup local development environment

Setup local development environment with installed extension

```
mkdir extensions
git clone git@github.com:cmdotcom-plugins/pay-ext-magento2.git
composer config repositories.dev-extensions path extensions/* 
composer require cmdotcom-plugins/magento2-payments:@dev
bin/magento module:enable CM_Payments
bin/magento setup:upgrade
```

**Docker setup**

https://github.com/markshust/docker-magento

## Payment methods
### Fetch payment methods by order

The CM.com API requires an order to retrieve all the payment methods, to accomplish this in the Magento checkout this module creates a temporary order based on the Magento quote. These temporary orders will always have a 'Q_' prefix. 

## Payment method configuration
### General

Each payment method is configurable in Magento. There are a few default settings: 
- Enabled
- Title
- Applicable countries
- Applicable currencies
- Minimum order total
- Maximum order total
- Sort order

**Note** The payment methods will only visible if they enabled in both Magento and the CM.com Portal. 

### CM.com redirect to Menu

This payment method redirects to the CM.com payment menu. In the payment menu you will see all available payment methods as configured in the CM.com Portal.

### iDEAL

This method shows the bank issuers in the Magento checkout and redirects directly to the selected issuer. 

### Paypal

This method directly redirects to the Paypal payment page.

### ELV

ELV (Elektronisches Lastschriftverfahren) is a payment method used mainly in Germany.
This method directly redirects to the ELV payment page.

### Klarna

This method directly redirects to the Klarna payment page. Klarna requires a birthdate of the shopper which is requested in the Magento checkout.

### Creditcard

All the 'Creditcard' payment methods are mapped under one Magento payment method called `cm_payments_creditcard`
This includes the following CM.com payment methods:
`VISA`
`MASTERCARD`
`MAESTRO`

**Configuration**

The creditcard payment redirects to the CM.com payment menu. It's recommended to create a separate 'Creditcard' payment profile in the CM.com portal to show only the credit card methods in the CM.com payment menu.

### BanContact

The BanContact payment redirects to the CM.com payment menu. It's recommended to create a separate 'BanContact' payment profile in the CM.com portal to show only the BanContact method in the CM.com payment menu.

### Afterpay

The BanContact payment redirects to the CM.com payment menu. It's recommended to create a separate 'Afterpay' payment profile in the CM.com portal to show only the Afterpay method in the CM.com payment menu.

### KBC

The KBC payment redirects to the CM.com payment menu. It's recommended to create a separate 'KBC' payment profile in the CM.com portal to show only the KBC method in the CM.com payment menu.

### Belfius

The Belfius payment redirects to the CM.com payment menu. It's recommended to create a separate 'Belfius' payment profile in the CM.com portal to show only the Belfius method in the CM.com payment menu.

## Webhook
In order to get status updates from CM.com it's required to configure a webhook in the CM.com portal.
The url for this webhook is: `{{shop_url}}/cmpayments/payment/notification?id={{increment_id}}`

## Issues and support

You can create issues on our Github repository.
If you have other questions, or need specific payment methods in your test account, contact us at support.payments@cm.com
