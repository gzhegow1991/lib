<?php

namespace Gzhegow\Lib;

use Gzhegow\Lib\Traits\FsTrait;
use Gzhegow\Lib\Traits\BcTrait;
use Gzhegow\Lib\Traits\MbTrait;
use Gzhegow\Lib\Traits\PhpTrait;
use Gzhegow\Lib\Traits\StrTrait;
use Gzhegow\Lib\Traits\CliTrait;
use Gzhegow\Lib\Traits\UrlTrait;
use Gzhegow\Lib\Traits\NetTrait;
use Gzhegow\Lib\Traits\BoolTrait;
use Gzhegow\Lib\Traits\HttpTrait;
use Gzhegow\Lib\Traits\ArrayTrait;
use Gzhegow\Lib\Traits\ParseTrait;
use Gzhegow\Lib\Traits\DebugTrait;
use Gzhegow\Lib\Traits\AssertTrait;
use Gzhegow\Lib\Traits\FormatTrait;
use Gzhegow\Lib\Traits\EscapeTrait;
use Gzhegow\Lib\Traits\RandomTrait;
use Gzhegow\Lib\Traits\ItertoolsTrait;


class Lib
{
    use ArrayTrait;
    use AssertTrait;
    use BcTrait;
    use BoolTrait;
    use CliTrait;
    use DebugTrait;
    use EscapeTrait;
    use FormatTrait;
    use FsTrait;
    use HttpTrait;
    use ItertoolsTrait;
    use MbTrait;
    use NetTrait;
    use ParseTrait;
    use PhpTrait;
    use RandomTrait;
    use StrTrait;
    use UrlTrait;
}
