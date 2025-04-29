<?php

if (! defined('_ARR_FN_USE_VALUE')) define('_ARR_FN_USE_VALUE', 1 << 0);
if (! defined('_ARR_FN_USE_KEY')) define('_ARR_FN_USE_KEY', 1 << 1);
if (! defined('_ARR_FN_USE_KEY_VALUE')) define('_ARR_FN_USE_KEY_VALUE', (1 << 2) - 1);
if (! defined('_ARR_FN_USE_SRC')) define('_ARR_FN_USE_SRC', 1 << 2);
if (! defined('_ARR_FN_USE_ALL')) define('_ARR_FN_USE_ALL', (1 << 3) - 1);

if (! defined('_ARR_WALK_MODE_BREADTH_FIRST')) define('_ARR_WALK_MODE_BREADTH_FIRST', 1 << 0);
if (! defined('_ARR_WALK_MODE_DEPTH_FIRST')) define('_ARR_WALK_MODE_DEPTH_FIRST', 1 << 1);

if (! defined('_ARR_WALK_SORT_CHILD_FIRST')) define('_ARR_WALK_SORT_CHILD_FIRST', 1 << 2);
if (! defined('_ARR_WALK_SORT_PARENT_FIRST')) define('_ARR_WALK_SORT_PARENT_FIRST', 1 << 3);
if (! defined('_ARR_WALK_SORT_SELF_FIRST')) define('_ARR_WALK_SORT_SELF_FIRST', 1 << 4);

if (! defined('_ARR_WALK_WITH_LEAVES')) define('_ARR_WALK_WITH_LEAVES', 1 << 5);
if (! defined('_ARR_WALK_WITHOUT_LEAVES')) define('_ARR_WALK_WITHOUT_LEAVES', 1 << 6);

if (! defined('_ARR_WALK_WITH_DICTS')) define('_ARR_WALK_WITH_DICTS', 1 << 7);
if (! defined('_ARR_WALK_WITHOUT_DICTS')) define('_ARR_WALK_WITHOUT_DICTS', 1 << 8);

if (! defined('_ARR_WALK_WITH_EMPTY_ARRAYS')) define('_ARR_WALK_WITH_EMPTY_ARRAYS', 1 << 9);
if (! defined('_ARR_WALK_WITHOUT_EMPTY_ARRAYS')) define('_ARR_WALK_WITHOUT_EMPTY_ARRAYS', 1 << 10);

if (! defined('_ARR_WALK_WITH_LISTS')) define('_ARR_WALK_WITH_LISTS', 1 << 11);
if (! defined('_ARR_WALK_WITHOUT_LISTS')) define('_ARR_WALK_WITHOUT_LISTS', 1 << 12);

if (! defined('_ARR_WALK_WITH_PARENTS')) define('_ARR_WALK_WITH_PARENTS', 1 << 13);
if (! defined('_ARR_WALK_WITHOUT_PARENTS')) define('_ARR_WALK_WITHOUT_PARENTS', 1 << 14);
