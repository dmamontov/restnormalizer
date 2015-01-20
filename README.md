RestNormalizer 1.0.0
====================

Normalization data for the transmission via "Representational State Transfer".

## Requirements
* PHP version **5.1** or **higher**.

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
	"key1" => "value1",
	"key2" => value2,
	"key3" => "value3"
);
$data = $n->normalize($data)
```