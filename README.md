[![Latest Stable Version](https://poser.pugx.org/dmamontov/restnormalizer/v/stable.svg)](https://packagist.org/packages/dmamontov/restnormalizer)
[![License](https://poser.pugx.org/dmamontov/restnormalizer/license.svg)](https://packagist.org/packages/dmamontov/restnormalizer)
[![Total Downloads](https://poser.pugx.org/dmamontov/restnormalizer/downloads)](https://packagist.org/packages/dmamontov/restnormalizer)

REST Normalizer
===============

This class can validate and filter parameters based on JSON rules.

It can take a list of request parameters passed as an array or as a JSON string and validates it according to rules defined in an external files in the JSON format.

The class can traverse the parameter data and check if the entries match the types and validation rules defined in the rules file for the specific type of request.

Invalid values may be discard and logged to a given log file.

The class may also call callback functions before and after the normalization process.

[More information](https://dmamontov.github.io/restnormalizer).

## Requirements
* PHP version >5.0

## Installation

1) Install [composer](https://getcomposer.org/download/)

2) Follow in the project folder:
```bash
composer require dmamontov/restnormalizer ~1.0.0
```

In config `composer.json` your project will be added to the library `dmamontov/restnormalizer`, who settled in the folder `vendor/`. In the absence of a config file or folder with vendors they will be created.

If before your project is not used `composer`, connect the startup file vendors. To do this, enter the code in the project:
```php
require 'path/to/vendor/autoload.php';
```

## Data types and values to be formatted

#### The data type "string"
* required - Checking the mandatory values, accepts parameters (true, false)
* default - Default
* max - The maximum allowable length of the string
* min - The minimum allowable length of the string
* pad - Supplemented with the symbol (default: " ")

```json
"example": {
        "type": "string",
        "required": false,
        "default": "example",
        "max": 15,
        "min": 4,
        "pad": "+"
}
```

#### The data type "int"
* required - Checking the mandatory values, accepts parameters (true, false)
* default - Default
* max - The maximum allowable value
* min - The minimum allowable value

```json
"example": {
        "type": "int",
        "required": true,
        "default": 55,
        "max": 203,
        "min": 10
}
```

#### The data type "double"
* required - Checking the mandatory values, accepts parameters (true, false)
* default - Default
* max - The maximum allowable value
* min - The minimum allowable value
* decimals - The number of digits after the decimal point

```json
"example": {
        "type": "double",
        "required": true,
        "default": 5,
        "max": 20.5,
        "min": 1.1,
        "decimals": 5
}
```
#### The data type "bool"
* required - Checking the mandatory values, accepts parameters (true, false)
* default - Default

```json
"example": {
        "type": "bool",
        "required": true,
        "default": true
}
```
#### The data type "datetime"
* required - Checking the mandatory values, accepts parameters (true, false)
* default - Default (default: "now")
* format - Date and time format

```json
"example": {
        "type": "datetime",
        "required": true,
        "default": "now",
        "format": "Y-m-d H:i:s"
}
```
#### The data type "enum"
* required - Checking the mandatory values, accepts parameters (true, false)
* default - Default
* values - An array of enumerated values

```json
"example": {
        "type": "enum",
        "required": true,
        "default": 999,
        "values": [1, 5, 999]
    }
```
#### The data type "skip"

```json
"example": {
        "type": "skip"
}
```

## Example of work
```php
require_once 'RestNormalizer.php';
$n = new RestNormalizer();
$n->logFile = 'valid.log';
$n->setValidation('you-valid.json');

$data = array(
	'key1' => 'value1',
	'key2' => 'value2',
	'key3' => 'value3'
);
$data = $n->normalize($data)
```
