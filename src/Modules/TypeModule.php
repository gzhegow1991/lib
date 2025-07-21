<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Modules\Type\Traits\IsTrait;
use Gzhegow\Lib\Modules\Type\Traits\TypeTrait;
use Gzhegow\Lib\Modules\Type\Traits\AssertTrait;
use Gzhegow\Lib\Modules\Type\Traits\FilterTrait;


class TypeModule
{
    use AssertTrait;
    use FilterTrait;
    use IsTrait;
    use TypeTrait;
}
