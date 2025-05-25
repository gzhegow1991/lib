<?php

if (! defined('_NUM_PHP_INT_MIN_FLOAT')) define('_NUM_PHP_INT_MIN_FLOAT', (float) PHP_INT_MIN);
if (! defined('_NUM_PHP_INT_MAX_FLOAT')) define('_NUM_PHP_INT_MAX_FLOAT', (float) PHP_INT_MAX);

$var = (function () {
    $var = (string) PHP_FLOAT_MIN;
    [ $mant, $exp ] = explode('E', $var);
    [ $int, $frac ] = explode('.', $mant);
    $frac = substr($frac, 0, PHP_FLOAT_DIG - 1);
    $frac = str_pad($frac, PHP_FLOAT_DIG, '9');
    $var = "{$int}.{$frac}E{$exp}";

    return $var;
})();
if (! defined('_NUM_PHP_FLOAT_MIN_STRING_DIG')) define('_NUM_PHP_FLOAT_MIN_STRING_DIG', $var);
if (! defined('_NUM_PHP_FLOAT_MIN_FLOAT_DIG')) define('_NUM_PHP_FLOAT_MIN_FLOAT_DIG', (float) $var);

$var = (function () {
    $var = (string) PHP_FLOAT_MAX;
    [ $mant, $exp ] = explode('E', $var);
    [ $int, $frac ] = explode('.', $mant);
    $frac = substr($frac, 0, PHP_FLOAT_DIG - 1);
    $frac = str_pad($frac, PHP_FLOAT_DIG, '0');
    $var = "{$int}.{$frac}E{$exp}";

    return $var;
})();
if (! defined('_NUM_PHP_FLOAT_MAX_STRING_DIG')) define('_NUM_PHP_FLOAT_MAX_STRING_DIG', $var);
if (! defined('_NUM_PHP_FLOAT_MAX_FLOAT_DIG')) define('_NUM_PHP_FLOAT_MAX_FLOAT_DIG', (float) $var);
