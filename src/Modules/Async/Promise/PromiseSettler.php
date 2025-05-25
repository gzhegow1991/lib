<?php

namespace Gzhegow\Lib\Modules\Async\Promise;

class PromiseSettler
{
    const TYPE_THEN    = 'then';
    const TYPE_CATCH   = 'catch';
    const TYPE_FINALLY = 'finally';

    const LIST_TYPE = [
        self::TYPE_THEN    => true,
        self::TYPE_CATCH   => true,
        self::TYPE_FINALLY => true,
    ];


    /**
     * @var string
     */
    public $type;

    /**
     * @var callable|null
     */
    public $fnOnResolved;
    /**
     * @var callable|null
     */
    public $fnOnRejected;
    /**
     * @var callable|null
     */
    public $fnOnFinally;

    /**
     * @var Promise
     */
    public $promiseParent;
    /**
     * @var Promise
     */
    public $promise;
}
