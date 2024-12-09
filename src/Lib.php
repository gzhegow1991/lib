<?php

namespace Gzhegow\Lib;

use Gzhegow\Lib\Traits\PhpTrait;
use Gzhegow\Lib\Traits\StrTrait;
use Gzhegow\Lib\Traits\BoolTrait;
use Gzhegow\Lib\Traits\ArrayTrait;
use Gzhegow\Lib\Traits\ParseTrait;
use Gzhegow\Lib\Traits\DebugTrait;
use Gzhegow\Lib\Traits\AssertTrait;


class Lib
{
    use ArrayTrait;
    use AssertTrait;
    use BoolTrait;
    use DebugTrait;
    use ParseTrait;
    use PhpTrait;
    use StrTrait;
}
