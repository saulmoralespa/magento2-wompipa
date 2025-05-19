# Wompi Panamá for Magento 2

## Description
WompiPa is a payment module for Magento 2 that allows you to accept payments through Wompi, a payment gateway in Panamá

## Installation

use composer to install the module

```
composer require saulmoralespa/magento2-wompipa
```

## Execute the commands
```
php bin/magento module:enable Saulmoralespa_WompiPa
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy en_US #on i18n
```

## Configuration
1. Go to Stores > Configuration > Sales > Payment Methods
2. Find the Wompi Panamá section
3. Configure the settings:
   - Enable: Set to "Yes" to enable the payment method.
   - Environment: Choose between "Development" and "Production".
   - Public Key: Your Wompi public key.
   - Private Key: Your Wompi private key.
   - Events Key: Your Wompi events key.
   - Integrity key: Your Wompi integrity key.
