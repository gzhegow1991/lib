<?php

if (! defined('_PHP_PATHINFO_DIRNAME')) define('_PHP_PATHINFO_DIRNAME', PATHINFO_DIRNAME);       // 1 << 0
if (! defined('_PHP_PATHINFO_BASENAME')) define('_PHP_PATHINFO_BASENAME', PATHINFO_BASENAME);    // 1 << 1
if (! defined('_PHP_PATHINFO_EXTENSION')) define('_PHP_PATHINFO_EXTENSION', PATHINFO_EXTENSION); // 1 << 2
if (! defined('_PHP_PATHINFO_FILENAME')) define('_PHP_PATHINFO_FILENAME', PATHINFO_FILENAME);    // 1 << 3
if (! defined('_PHP_PATHINFO_FILE')) define('_PHP_PATHINFO_FNAME', 1 << 4);
if (! defined('_PHP_PATHINFO_EXTENSIONS')) define('_PHP_PATHINFO_EXTENSIONS', 1 << 5);
if (! defined('_PHP_PATHINFO_ALL')) define('_PHP_PATHINFO_ALL', ((1 << 6) - 1));

if (! defined('_PHP_STRUCT_TYPE_CLASS')) define('_PHP_STRUCT_TYPE_CLASS', 1 << 1);
if (! defined('_PHP_STRUCT_TYPE_INTERFACE')) define('_PHP_STRUCT_TYPE_INTERFACE', 1 << 2);
if (! defined('_PHP_STRUCT_TYPE_TRAIT')) define('_PHP_STRUCT_TYPE_TRAIT', 1 << 3);
if (! defined('_PHP_STRUCT_TYPE_ENUM')) define('_PHP_STRUCT_TYPE_ENUM', 1 << 4);
if (! defined('_PHP_STRUCT_TYPE_ALL')) define('_PHP_STRUCT_TYPE_ALL', (1 << 5) - 1);

if (! defined('_PHP_STRUCT_EXISTS_TRUE')) define('_PHP_STRUCT_EXISTS_TRUE', 1 << 5);
if (! defined('_PHP_STRUCT_EXISTS_FALSE')) define('_PHP_STRUCT_EXISTS_FALSE', 1 << 6);
if (! defined('_PHP_STRUCT_EXISTS_IGNORE')) define('_PHP_STRUCT_EXISTS_IGNORE', 1 << 7);
