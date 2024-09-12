📦 This package is a sub-repo split of a PHP Validator which is currently under development.

✨ This can also be installed as a dependency for [Payment Card](https://github.com/thewebsolver/payment-card) library to validate Debit/Credit Cards.

# Introduction

Luhn Algorithm to validate Payment Cards, IMEI numbers and other digits that needs Luhn validation.

## Usage

### Install

```sh
composer require thewebsolver/luhn-algorithm
```

### Validate

Validation can be performed using any of the three OPTIONS presented below:

```php
use TheWebSolver\Codegarage\LuhnAlgorithm;

// OPTION 1: Value passing via constructor.
$luhn     = new LuhnAlgorithm(79927398713);
$isValid  = $luhn->isValid();  // true
$checksum = $luhn->checksum(); // 70

// OPTION 2: Value passing via invocable class.
$luhn     = new LuhnAlgorithm();
$isValid  = $luhn(79927398713); // true
$checksum = $luhn->checksum();  // 70

// OPTION 3: Value passing via static method. In this case, There is no
// instance stored in memory & can only be used for one-off validation.
$isValid = LuhnAlgorithm::validate(79927398713); // true
```

### Debug

From above [Validation](#validate) example, we can debug more details about the validation status and various state of each number when checkum was calculated.

```php
var_dump( $luhn ); // Input value was: 79927398713

// The debug output.
array(4) {
  'isValid' =>
  bool(true)
  'digits' =>
  int(79947697723)
  'checksum' =>
  int(70)
  'state' =>
  array(11) {
    [0] =>
    array(2) {
      'doubled' =>
      bool(false)
      'result' =>
      int(7)
    }
    [1] =>
    array(2) {
      'doubled' =>
      bool(true)
      'result' =>
      int(9)
    }
    [2] =>
    array(2) {
      'doubled' =>
      bool(false)
      'result' =>
      int(9)
    }
    [3] =>
    array(2) {
      'doubled' =>
      bool(true)
      'result' =>
      int(4)
    }
    [4] =>
    array(2) {
      'doubled' =>
      bool(false)
      'result' =>
      int(7)
    }
    [5] =>
    array(2) {
      'doubled' =>
      bool(true)
      'result' =>
      int(6)
    }
    [6] =>
    array(2) {
      'doubled' =>
      bool(false)
      'result' =>
      int(9)
    }
    [7] =>
    array(2) {
      'doubled' =>
      bool(true)
      'result' =>
      int(7)
    }
    [8] =>
    array(2) {
      'doubled' =>
      bool(false)
      'result' =>
      int(7)
    }
    [9] =>
    array(2) {
      'doubled' =>
      bool(true)
      'result' =>
      int(2)
    }
    [10] =>
    array(2) {
      'doubled' =>
      bool(false)
      'result' =>
      int(3)
    }
  }
}
```

# Composition

The [Luhn Algorithm](Src/LuhnAlgorithm.php) class is actually composed by using [Luhn](Src/Luhn.php) trait. This trait can be used elsewhere in separate class defined in your project or modify validation methods as per the project's requirement (latter of which seem unnecessary though).
