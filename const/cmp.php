<?php

if (! defined('_CMP_MODE_TYPE_STRICT')) define('_CMP_MODE_TYPE_STRICT', 1 << 1);
if (! defined('_CMP_MODE_TYPE_TYPECAST_OR_NAN')) define('_CMP_MODE_TYPE_TYPECAST_OR_NAN', 1 << 2);
if (! defined('_CMP_MODE_TYPE_TYPECAST_OR_CONTINUE')) define('_CMP_MODE_TYPE_TYPECAST_OR_CONTINUE', 1 << 3);
if (! defined('_CMP_MODE_TYPECAST_A')) define('_CMP_MODE_TYPECAST_A', 1 << 4);
if (! defined('_CMP_MODE_TYPECAST_B')) define('_CMP_MODE_TYPECAST_B', 1 << 5);
if (! defined('_CMP_MODE_STRING_VS_STRCMP')) define('_CMP_MODE_STRING_VS_STRCMP', 1 << 6);
if (! defined('_CMP_MODE_STRING_VS_STRCASECMP')) define('_CMP_MODE_STRING_VS_STRCASECMP', 1 << 7);
if (! defined('_CMP_MODE_STRING_VS_STRNATCMP')) define('_CMP_MODE_STRING_VS_STRNATCMP', 1 << 8);
if (! defined('_CMP_MODE_STRING_VS_STRNATCASECMP')) define('_CMP_MODE_STRING_VS_STRNATCASECMP', 1 << 9);
if (! defined('_CMP_MODE_STRING_VS_IGNORE')) define('_CMP_MODE_STRING_VS_IGNORE', 1 << 10);
if (! defined('_CMP_MODE_STRING_SIZE_STRLEN')) define('_CMP_MODE_STRING_SIZE_STRLEN', 1 << 11);
if (! defined('_CMP_MODE_STRING_SIZE_STRSIZE')) define('_CMP_MODE_STRING_SIZE_STRSIZE', 1 << 12);
if (! defined('_CMP_MODE_STRING_SIZE_IGNORE')) define('_CMP_MODE_STRING_SIZE_IGNORE', 1 << 13);
if (! defined('_CMP_MODE_ARRAY_SIZE_COUNT')) define('_CMP_MODE_ARRAY_SIZE_COUNT', 1 << 14);
if (! defined('_CMP_MODE_ARRAY_SIZE_IGNORE')) define('_CMP_MODE_ARRAY_SIZE_IGNORE', 1 << 15);
if (! defined('_CMP_MODE_ARRAY_VS_SPACESHIP')) define('_CMP_MODE_ARRAY_VS_SPACESHIP', 1 << 16);
if (! defined('_CMP_MODE_ARRAY_VS_IGNORE')) define('_CMP_MODE_ARRAY_VS_IGNORE', 1 << 17);
if (! defined('_CMP_MODE_DATE_VS_YEAR')) define('_CMP_MODE_DATE_VS_YEAR', 1 << 18);
if (! defined('_CMP_MODE_DATE_VS_MONTH')) define('_CMP_MODE_DATE_VS_MONTH', 1 << 19);
if (! defined('_CMP_MODE_DATE_VS_DAY')) define('_CMP_MODE_DATE_VS_DAY', 1 << 20);
if (! defined('_CMP_MODE_DATE_VS_HOUR')) define('_CMP_MODE_DATE_VS_HOUR', 1 << 21);
if (! defined('_CMP_MODE_DATE_VS_MIN')) define('_CMP_MODE_DATE_VS_MIN', 1 << 22);
if (! defined('_CMP_MODE_DATE_VS_SEC')) define('_CMP_MODE_DATE_VS_SEC', 1 << 23);
if (! defined('_CMP_MODE_DATE_VS_MSEC')) define('_CMP_MODE_DATE_VS_MSEC', 1 << 24);
if (! defined('_CMP_MODE_DATE_VS_USEC')) define('_CMP_MODE_DATE_VS_USEC', 1 << 25);
if (! defined('_CMP_MODE_OBJECT_SIZE_COUNT')) define('_CMP_MODE_OBJECT_SIZE_COUNT', 1 << 26);
if (! defined('_CMP_MODE_OBJECT_SIZE_IGNORE')) define('_CMP_MODE_OBJECT_SIZE_IGNORE', 1 << 27);

if (! defined('_CMP_RESULT_NULL_SPACESHIP')) define('_CMP_MODE_RESULT_SPACESHIP', 1 << 1);
if (! defined('_CMP_RESULT_NULL_0')) define('_CMP_RESULT_NULL_0', 1 << 2);
if (! defined('_CMP_RESULT_NULL_A_LT')) define('_CMP_RESULT_NULL_A_LT', 1 << 3);
if (! defined('_CMP_RESULT_NULL_A_GT')) define('_CMP_RESULT_NULL_A_GT', 1 << 4);
if (! defined('_CMP_RESULT_NULL_NAN')) define('_CMP_RESULT_NULL_NAN', 1 << 5);
if (! defined('_CMP_RESULT_NAN_THROW')) define('_CMP_RESULT_NAN_THROW', 1 << 6);
if (! defined('_CMP_RESULT_NAN_RETURN')) define('_CMP_RESULT_NAN_RETURN', 1 << 7);
