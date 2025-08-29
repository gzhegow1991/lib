<?php

if ( ! defined('_NUM_PHP_INT_MIN_FLOAT') ) define('_NUM_PHP_INT_MIN_FLOAT', (float) PHP_INT_MIN);
if ( ! defined('_NUM_PHP_INT_MAX_FLOAT') ) define('_NUM_PHP_INT_MAX_FLOAT', (float) PHP_INT_MAX);

$var = (function () {
    $var = (string) PHP_FLOAT_MIN;
    [ $mant, $exp ] = explode('E', $var);
    [ $int, $frac ] = explode('.', $mant);
    $frac = substr($frac, 0, PHP_FLOAT_DIG - 1);
    $frac = str_pad($frac, PHP_FLOAT_DIG, '9');
    $var = "{$int}.{$frac}E{$exp}";

    return $var;
})();
if ( ! defined('_NUM_PHP_FLOAT_MIN_STRING_DIG') ) define('_NUM_PHP_FLOAT_MIN_STRING_DIG', $var);
if ( ! defined('_NUM_PHP_FLOAT_MIN_FLOAT_DIG') ) define('_NUM_PHP_FLOAT_MIN_FLOAT_DIG', (float) $var);

$var = (function () {
    $var = (string) PHP_FLOAT_MAX;
    [ $mant, $exp ] = explode('E', $var);
    [ $int, $frac ] = explode('.', $mant);
    $frac = substr($frac, 0, PHP_FLOAT_DIG - 1);
    $frac = str_pad($frac, PHP_FLOAT_DIG, '0');
    $var = "{$int}.{$frac}E{$exp}";

    return $var;
})();
if ( ! defined('_NUM_PHP_FLOAT_MAX_STRING_DIG') ) define('_NUM_PHP_FLOAT_MAX_STRING_DIG', $var);
if ( ! defined('_NUM_PHP_FLOAT_MAX_FLOAT_DIG') ) define('_NUM_PHP_FLOAT_MAX_FLOAT_DIG', (float) $var);

if ( ! defined('_NUM_ROUND_AWAY_FROM_ZERO') ) define('_NUM_ROUND_AWAY_FROM_ZERO', 1 << 0);
if ( ! defined('_NUM_ROUND_TOWARD_ZERO') ) define('_NUM_ROUND_TOWARD_ZERO', 1 << 1);
if ( ! defined('_NUM_ROUND_TO_POSITIVE_INF') ) define('_NUM_ROUND_TO_POSITIVE_INF', 1 << 2);
if ( ! defined('_NUM_ROUND_TO_NEGATIVE_INF') ) define('_NUM_ROUND_TO_NEGATIVE_INF', 1 << 3);
if ( ! defined('_NUM_ROUND_EVEN') ) define('_NUM_ROUND_EVEN', 1 << 4);
if ( ! defined('_NUM_ROUND_ODD') ) define('_NUM_ROUND_ODD', 1 << 5);
