<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;


class CmpModule
{
    public function fnCompareValues(
        ?int $flagsMode = null, ?int $flagsResult = null,
        array $refs = []
    ) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
        }

        $fnCmpName = null;

        $_flagsMode = $this->flagsModeDefault($flagsMode);
        $_flagsResult = $this->flagsResultDefault($flagsResult);

        $fn = function ($a, $b) use (
            $_flagsMode, $_flagsResult,
            &$fnCmpName
        ) {
            $result = null
                ?? $this->cmpTypesStrict($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNan($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNil($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNull($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesBoolean($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesInteger($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesFloat($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNumeric($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesString($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesArray($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesDate($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesObject($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesResource($a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            $result = $this->cmpResultUnknown($result, $a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareSizes(
        ?int $flagsMode = null, ?int $flagsResult = null,
        array $refs = []
    ) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
        }

        $fnCmpName = null;

        $_flagsMode = $this->flagsModeDefault($flagsMode);
        $_flagsResult = $this->flagsResultDefault($flagsResult);

        $fn = function ($a, $b) use (
            $_flagsMode, $_flagsResult,
            &$fnCmpName
        ) {
            $result = null
                ?? $this->cmpTypesStrict($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNan($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNil($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNull($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpSizesString($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpSizesArray($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpSizesObject($a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            $result = $this->cmpResultUnknown($result, $a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareDates(
        ?int $flagsMode = null, ?int $flagsResult = null,
        array $refs = []
    ) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
        }

        $fnCmpName = null;

        $_flagsMode = $this->flagsModeDefault($flagsMode);
        $_flagsResult = $this->flagsResultDefault($flagsResult);

        $fn = function ($a, $b) use (
            $_flagsMode, $_flagsResult,
            &$fnCmpName
        ) {
            $result = null
                ?? $this->cmpTypesStrict($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNan($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNil($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNull($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesDate($a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            $result = $this->cmpResultUnknown($result, $a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnComparePeriods(
        ?int $flagsMode = null, ?int $flagsResult = null,
        array $refs = []
    ) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
        }

        $fnCmpName = null;

        $_flagsMode = $this->flagsModeDefault($flagsMode);
        $_flagsResult = $this->flagsResultDefault($flagsResult);

        $fn = function ($aStart, $bStart, $aEnd = null, $bEnd = null) use (
            $_flagsMode, $_flagsResult,
            &$fnCmpName
        ) {
            $result = null
                ?? $this->cmpTypesStrict($aStart, $bStart, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNan($aStart, $bStart, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNil($aStart, $bStart, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNull($aStart, $bStart, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpPeriodsDate(
                    $aStart, $bStart,
                    $aEnd, $bEnd,
                    $_flagsMode, $_flagsResult,
                    $fnCmpName
                );

            $result = $this->cmpResultUnknown($result,
                $aStart, $bStart,
                $_flagsMode, $_flagsResult,
                $fnCmpName
            );

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }


    public function fnCompareValuesNil(
        ?int $flagsMode = null, ?int $flagsResult = null,
        array $refs = []
    ) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
        }

        $fnCmpName = null;

        $_flagsMode = $this->flagsModeDefault($flagsMode);
        $_flagsResult = $this->flagsResultDefault($flagsResult);

        $fn = function ($a, $b) use (
            $_flagsMode, $_flagsResult,
            &$fnCmpName
        ) {
            $result = null
                ?? $this->cmpTypesStrict($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNan($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNil($a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            $result = $this->cmpResultUnknown($result, $a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareValuesNull(
        ?int $flagsMode = null, ?int $flagsResult = null,
        array $refs = []
    ) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
        }

        $fnCmpName = null;

        $_flagsMode = $this->flagsModeDefault($flagsMode);
        $_flagsResult = $this->flagsResultDefault($flagsResult);

        $fn = function ($a, $b) use (
            $_flagsMode, $_flagsResult,
            &$fnCmpName
        ) {
            $result = null
                ?? $this->cmpTypesStrict($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNan($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNil($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNull($a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            $result = $this->cmpResultUnknown($result, $a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareValuesBoolean(
        ?int $flagsMode = null, ?int $flagsResult = null,
        array $refs = []
    ) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
        }

        $fnCmpName = null;

        $_flagsMode = $this->flagsModeDefault($flagsMode);
        $_flagsResult = $this->flagsResultDefault($flagsResult);

        $fn = function ($a, $b) use (
            $_flagsMode, $_flagsResult,
            &$fnCmpName
        ) {
            $result = null
                ?? $this->cmpTypesStrict($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNan($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNil($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNull($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesBoolean($a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            $result = $this->cmpResultUnknown($result, $a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareValuesInteger(
        ?int $flagsMode = null, ?int $flagsResult = null,
        array $refs = []
    ) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
        }

        $fnCmpName = null;

        $_flagsMode = $this->flagsModeDefault($flagsMode);
        $_flagsResult = $this->flagsResultDefault($flagsResult);

        $fn = function ($a, $b) use (
            $_flagsMode, $_flagsResult,
            &$fnCmpName
        ) {
            $result = null
                ?? $this->cmpTypesStrict($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNan($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNil($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNull($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesInteger($a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            $result = $this->cmpResultUnknown($result, $a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareValuesFloat(
        ?int $flagsMode = null, ?int $flagsResult = null,
        array $refs = []
    ) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
        }

        $fnCmpName = null;

        $_flagsMode = $this->flagsModeDefault($flagsMode);
        $_flagsResult = $this->flagsResultDefault($flagsResult);

        $fn = function ($a, $b) use (
            $_flagsMode, $_flagsResult,
            &$fnCmpName
        ) {
            $result = null
                ?? $this->cmpTypesStrict($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNan($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNil($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNull($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesFloat($a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            $result = $this->cmpResultUnknown($result, $a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareValuesNumeric(
        ?int $flagsMode = null, ?int $flagsResult = null,
        array $refs = []
    ) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
        }

        $fnCmpName = null;

        $_flagsMode = $this->flagsModeDefault($flagsMode);
        $_flagsResult = $this->flagsResultDefault($flagsResult);

        $fn = function ($a, $b) use (
            $_flagsMode, $_flagsResult,
            &$fnCmpName
        ) {
            $result = null
                ?? $this->cmpTypesStrict($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNan($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNil($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNull($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesInteger($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesFloat($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNumeric($a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            $result = $this->cmpResultUnknown($result, $a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareValuesString(
        ?int $flagsMode = null, ?int $flagsResult = null,
        array $refs = []
    ) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
        }

        $fnCmpName = null;

        $_flagsMode = $this->flagsModeDefault($flagsMode);
        $_flagsResult = $this->flagsResultDefault($flagsResult);

        $fn = function ($a, $b) use (
            $_flagsMode, $_flagsResult,
            &$fnCmpName
        ) {
            $result = null
                ?? $this->cmpTypesStrict($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNan($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNil($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNull($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesString($a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            $result = $this->cmpResultUnknown($result, $a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareValuesArray(
        ?int $flagsMode = null, ?int $flagsResult = null,
        array $refs = []
    ) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
        }

        $fnCmpName = null;

        $_flagsMode = $this->flagsModeDefault($flagsMode);
        $_flagsResult = $this->flagsResultDefault($flagsResult);

        $fn = function ($a, $b) use (
            $_flagsMode, $_flagsResult,
            &$fnCmpName
        ) {
            $result = null
                ?? $this->cmpTypesStrict($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNan($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNil($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNull($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesArray($a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            $result = $this->cmpResultUnknown($result, $a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareValuesDate(
        ?int $flagsMode = null, ?int $flagsResult = null,
        array $refs = []
    ) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
        }

        $fnCmpName = null;

        $_flagsMode = $this->flagsModeDefault($flagsMode);
        $_flagsResult = $this->flagsResultDefault($flagsResult);

        $fn = function ($a, $b) use (
            $_flagsMode, $_flagsResult,
            &$fnCmpName
        ) {
            $result = null
                ?? $this->cmpTypesStrict($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNan($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNil($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNull($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesDate($a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            $result = $this->cmpResultUnknown($result, $a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareValuesObject(
        ?int $flagsMode = null, ?int $flagsResult = null,
        array $refs = []
    ) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
        }

        $fnCmpName = null;

        $_flagsMode = $this->flagsModeDefault($flagsMode);
        $_flagsResult = $this->flagsResultDefault($flagsResult);

        $fn = function ($a, $b) use (
            $_flagsMode, $_flagsResult,
            &$fnCmpName
        ) {
            $result = null
                ?? $this->cmpTypesStrict($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNan($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNil($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNull($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesDate($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesObject($a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            $result = $this->cmpResultUnknown($result, $a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareValuesResource(
        ?int $flagsMode = null, ?int $flagsResult = null,
        array $refs = []
    ) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
        }

        $fnCmpName = null;

        $_flagsMode = $this->flagsModeDefault($flagsMode);
        $_flagsResult = $this->flagsResultDefault($flagsResult);

        $fn = function ($a, $b) use (
            $_flagsMode, $_flagsResult,
            &$fnCmpName
        ) {
            $result = null
                ?? $this->cmpTypesStrict($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNan($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNil($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNull($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesResource($a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            $result = $this->cmpResultUnknown($result, $a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }


    public function fnCompareSizesString(
        ?int $flagsMode = null, ?int $flagsResult = null,
        array $refs = []
    ) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
        }

        $fnCmpName = null;

        $_flagsMode = $this->flagsModeDefault($flagsMode);
        $_flagsResult = $this->flagsResultDefault($flagsResult);

        $fn = function ($a, $b) use (
            $_flagsMode, $_flagsResult,
            &$fnCmpName
        ) {
            $result = null
                ?? $this->cmpTypesStrict($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNan($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNil($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNull($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpSizesString($a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            $result = $this->cmpResultUnknown($result, $a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareSizesArray(
        ?int $flagsMode = null, ?int $flagsResult = null,
        array $refs = []
    ) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
        }

        $fnCmpName = null;

        $_flagsMode = $this->flagsModeDefault($flagsMode);
        $_flagsResult = $this->flagsResultDefault($flagsResult);

        $fn = function ($a, $b) use (
            $_flagsMode, $_flagsResult,
            &$fnCmpName
        ) {
            $result = null
                ?? $this->cmpTypesStrict($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNan($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNil($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNull($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpSizesArray($a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            $result = $this->cmpResultUnknown($result, $a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareSizesObject(
        ?int $flagsMode = null, ?int $flagsResult = null,
        array $refs = []
    ) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
        }

        $fnCmpName = null;

        $_flagsMode = $this->flagsModeDefault($flagsMode);
        $_flagsResult = $this->flagsResultDefault($flagsResult);

        $fn = function ($a, $b) use (
            $_flagsMode, $_flagsResult,
            &$fnCmpName
        ) {
            $result = null
                ?? $this->cmpTypesStrict($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNan($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNil($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpValuesNull($a, $b, $_flagsMode, $_flagsResult, $fnCmpName)
                ?? $this->cmpSizesObject($a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            $result = $this->cmpResultUnknown($result, $a, $b, $_flagsMode, $_flagsResult, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }


    /**
     * @param callable|callable-string|null $fnCmpName
     *
     * @return null|float
     */
    protected function cmpTypesStrict(
        $a, $b,
        int $flagsMode, int $flagsResult,
        ?string &$fnCmpName = null
    ) // : null|NAN
    {
        if ($flagsMode & _CMP_MODE_TYPE_STRICT) {
            if (gettype($a) !== gettype($b)) {
                $fnCmpName = __FUNCTION__;

                return NAN;
            }
        }

        return null;
    }


    /**
     * @param callable|callable-string|null $fnCmpName
     *
     * @return null|float
     */
    protected function cmpValuesNan(
        $a, $b,
        int $flagsMode, int $flagsResult,
        ?string &$fnCmpName = null
    ) // : null|NAN
    {
        $isNanA = is_float($a) && is_nan($a);
        $isNanB = is_float($b) && is_nan($b);

        if ($isNanA || $isNanB) {
            $fnCmpName = __FUNCTION__;

            return NAN;
        }

        return null;
    }

    /**
     * @param callable|callable-string|null $fnCmpName
     *
     * @return null|int|float
     */
    protected function cmpValuesNil(
        $a, $b,
        int $flagsMode, int $flagsResult,
        ?string &$fnCmpName = null
    ) // : null|int|NAN
    {
        $theType = Lib::type();

        $isNilA = $theType->nil($var, $a);
        $isNilB = $theType->nil($var, $b);

        if ($isNilA && $isNilB) {
            $fnCmpName = __FUNCTION__;

            return 0;

        } elseif ($isNilA || $isNilB) {
            $fnCmpName = __FUNCTION__;

            return NAN;
        }

        return null;
    }

    /**
     * @param callable|callable-string|null $fnCmpName
     *
     * @return null|int|float
     */
    protected function cmpValuesNull(
        $a, $b,
        int $flagsMode, int $flagsResult,
        ?string &$fnCmpName = null
    ) // : null|int|NAN
    {
        $isNullA = ($a === null);
        $isNullB = ($b === null);

        if ($isNullA && $isNullB) {
            $fnCmpName = __FUNCTION__;

            return 0;

        } elseif ($isNullA || $isNullB) {
            $theType = Lib::type();

            $aStatus = $isNullA;
            $bStatus = $isNullB;
            if (! $isNullA && ($flagsMode & _CMP_MODE_TYPECAST_A)) $aStatus = $theType->blank($var, $a);
            if (! $isNullB && ($flagsMode & _CMP_MODE_TYPECAST_B)) $bStatus = $theType->blank($var, $b);

            if ($aStatus && $bStatus) {
                $fnCmpName = __FUNCTION__;

                return 0;

            } elseif ($aStatus || $bStatus) {
                $fnCmpName = __FUNCTION__;

                return $bStatus <=> $aStatus;
            }
        }

        return null;
    }

    /**
     * @param callable|callable-string|null $fnCmpName
     *
     * @return null|int|float
     */
    protected function cmpValuesBoolean(
        $a, $b,
        int $flagsMode, int $flagsResult,
        ?string &$fnCmpName = null
    ) // : null|int|NAN
    {
        $isBoolA = is_bool($a);
        $isBoolB = is_bool($b);

        if ($isBoolA && $isBoolB) {
            $fnCmpName = __FUNCTION__;

            return $a <=> $b;

        } elseif ($isBoolA || $isBoolB) {
            $theType = Lib::type();

            $aStatus = $isBoolA;
            $bStatus = $isBoolB;
            if (! $isBoolA && ($flagsMode & _CMP_MODE_TYPECAST_A)) $aStatus = $theType->bool($aBool, $a);
            if (! $isBoolB && ($flagsMode & _CMP_MODE_TYPECAST_B)) $bStatus = $theType->bool($bBool, $b);

            if ($aStatus && $bStatus) {
                $aBool = $aBool ?? $a;
                $bBool = $bBool ?? $b;

                $fnCmpName = __FUNCTION__;

                return $aBool <=> $bBool;
            }

            return $this->cmpTypeCastFail(
                $a, $b,
                $aStatus, $bStatus,
                $flagsMode, $flagsResult,
                $fnCmpName
            );
        }

        return null;
    }

    /**
     * @param callable|callable-string|null $fnCmpName
     *
     * @return null|int|float
     */
    protected function cmpValuesInteger(
        $a, $b,
        int $flagsMode, int $flagsResult,
        ?string &$fnCmpName = null
    ) // : null|int|NAN
    {
        $isIntA = is_int($a);
        $isIntB = is_int($b);

        if ($isIntA && $isIntB) {
            $fnCmpName = __FUNCTION__;

            return $a <=> $b;

        } elseif ($isIntA || $isIntB) {
            $theType = Lib::type();

            $aStatus = $isIntA;
            $bStatus = $isIntB;
            if (! $isIntA && ($flagsMode & _CMP_MODE_TYPECAST_A)) $aStatus = $theType->int($aInt, $a);
            if (! $isIntB && ($flagsMode & _CMP_MODE_TYPECAST_B)) $bStatus = $theType->int($bInt, $b);

            $aInt = $aInt ?? $a;
            $bInt = $bInt ?? $b;

            if ($aStatus && $bStatus) {
                $aInt = $aInt ?? $a;
                $bInt = $bInt ?? $b;

                $fnCmpName = __FUNCTION__;

                return $aInt <=> $bInt;
            }

            return $this->cmpTypeCastFail(
                $a, $b,
                $aStatus, $bStatus,
                $flagsMode, $flagsResult,
                $fnCmpName
            );
        }

        return null;
    }

    /**
     * @param callable|callable-string|null $fnCmpName
     *
     * @return null|int|float
     */
    protected function cmpValuesFloat(
        $a, $b,
        int $flagsMode, int $flagsResult,
        ?string &$fnCmpName = null
    ) // : null|int|NAN
    {
        $isFloatA = is_float($a);
        $isFloatB = is_float($b);

        if ($isFloatA || $isFloatB) {
            $isNanA = $isFloatA && is_nan($a);
            $isNanB = $isFloatB && is_nan($b);

            if ($isNanA || $isNanB) {
                $fnCmpName = __FUNCTION__;

                return NAN;
            }

            if ($isFloatA && $isFloatB) {
                $fnCmpName = __FUNCTION__;

                return $a <=> $b;
            }

            $theType = Lib::type();

            $aStatus = $isFloatA;
            $bStatus = $isFloatB;
            if (! $aStatus && ($flagsMode & _CMP_MODE_TYPECAST_A)) $aStatus = $theType->num($aNum, $a);
            if (! $bStatus && ($flagsMode & _CMP_MODE_TYPECAST_B)) $bStatus = $theType->num($bNum, $b);

            $isInfiniteA = $isFloatA && is_infinite($a);
            $isInfiniteB = $isFloatB && is_infinite($b);
            if ($isInfiniteA || $isInfiniteB) {
                $aNum = $aNum ?? $a;
                $bNum = $bNum ?? $b;

                if ($isInfiniteA && $bStatus) {
                    $fnCmpName = __FUNCTION__;

                    return $a <=> $bNum;
                }

                if ($isInfiniteB && $aStatus) {
                    $fnCmpName = __FUNCTION__;

                    return $aNum <=> $b;
                }

                $fnCmpName = __FUNCTION__;

                return NAN;
            }

            if ($aStatus && $bStatus) {
                $aNum = $aNum ?? $a;
                $bNum = $bNum ?? $b;

                $fnCmpName = __FUNCTION__;

                return $aNum <=> $bNum;
            }

            return $this->cmpTypeCastFail(
                $a, $b,
                $aStatus, $bStatus,
                $flagsMode, $flagsResult,
                $fnCmpName
            );
        }

        return null;
    }

    /**
     * @param callable|callable-string|null $fnCmpName
     *
     * @return null|int|float
     */
    protected function cmpValuesNumeric(
        $a, $b,
        int $flagsMode, int $flagsResult,
        ?string &$fnCmpName = null
    ) // : null|int|NAN
    {
        $theType = Lib::type();

        $isNumericA = $theType->numeric($aNumeric, $a);
        $isNumericB = $theType->numeric($bNumeric, $b);

        if ($isNumericA || $isNumericB) {
            $isIntA = is_int($a);
            $isIntB = is_int($b);

            if ($isIntA && $isIntB) {
                $fnCmpName = __FUNCTION__;

                return $a <=> $b;
            }

            $isFloatA = is_float($a);
            $isFloatB = is_float($b);

            $isNanA = $isFloatA && is_nan($a);
            $isNanB = $isFloatB && is_nan($b);

            if ($isNanA || $isNanB) {
                $fnCmpName = __FUNCTION__;

                return NAN;
            }

            if ($isFloatA && $isFloatB) {
                $fnCmpName = __FUNCTION__;

                return $a <=> $b;
            }

            $theType = Lib::type();

            $aStatus = $isIntA || $isFloatA;
            $bStatus = $isIntB || $isFloatB;
            if (! $aStatus && ($flagsMode & _CMP_MODE_TYPECAST_A)) $aStatus = $theType->num($aNum, $a);
            if (! $bStatus && ($flagsMode & _CMP_MODE_TYPECAST_B)) $bStatus = $theType->num($bNum, $b);

            $isInfiniteA = $isFloatA && is_infinite($a);
            $isInfiniteB = $isFloatB && is_infinite($b);
            if ($isInfiniteA || $isInfiniteB) {
                $aNum = $aNum ?? $a;
                $bNum = $bNum ?? $b;


                if ($isInfiniteA && $bStatus) {
                    $fnCmpName = __FUNCTION__;

                    return $a <=> $bNum;
                }

                if ($isInfiniteB && $aStatus) {
                    $fnCmpName = __FUNCTION__;

                    return $aNum <=> $b;
                }

                $fnCmpName = __FUNCTION__;

                return NAN;
            }

            if ($aStatus && $bStatus) {
                $aNum = $aNum ?? $a;
                $bNum = $bNum ?? $b;

                $fnCmpName = __FUNCTION__;

                return $aNum <=> $bNum;
            }

            return $this->cmpTypeCastFail(
                $a, $b,
                $aStatus, $bStatus,
                $flagsMode, $flagsResult,
                $fnCmpName
            );
        }

        return null;
    }

    /**
     * @param callable|callable-string|null $fnCmpName
     *
     * @return null|int|float
     */
    protected function cmpValuesString(
        $a, $b,
        int $flagsMode, int $flagsResult,
        ?string &$fnCmpName = null
    ) // : null|int|NAN
    {
        $isStringA = is_string($a);
        $isStringB = is_string($b);

        if ($isStringA || $isStringB) {
            $theType = Lib::type();

            $aStatus = $isStringA;
            $bStatus = $isStringB;
            if (! $aStatus && ($flagsMode & _CMP_MODE_TYPECAST_A)) $aStatus = $theType->string($aString, $a);
            if (! $bStatus && ($flagsMode & _CMP_MODE_TYPECAST_B)) $bStatus = $theType->string($bString, $b);

            if ($aStatus && $bStatus) {
                $aString = $aString ?? $a;
                $bString = $bString ?? $b;

                if ($aString === $bString) {
                    $fnCmpName = __FUNCTION__;

                    return 0;
                }

                $resultLen = null;

                if ($flagsMode & _CMP_MODE_STRING_SIZE_STRLEN) {
                    $theStr = Lib::str();

                    $aStringLen = $theStr->strlen($aString);
                    $bStringLen = $theStr->strlen($bString);

                    $resultLen = ($aStringLen <=> $bStringLen);

                    if (0 !== $resultLen) {
                        $fnCmpName = __FUNCTION__;

                        return $resultLen;
                    }

                } elseif ($flagsMode & _CMP_MODE_STRING_SIZE_STRSIZE) {
                    $resultLen = (strlen($a) <=> strlen($b));

                    if (0 !== $resultLen) {
                        $fnCmpName = __FUNCTION__;

                        return $resultLen;
                    }
                }

                $result = null
                    ?? (($flagsMode & _CMP_MODE_STRING_VS_STRNATCASECMP) ? strnatcasecmp($aString, $bString) : null)
                    ?? (($flagsMode & _CMP_MODE_STRING_VS_STRCASECMP) ? strcasecmp($aString, $bString) : null)
                    ?? (($flagsMode & _CMP_MODE_STRING_VS_STRNATCMP) ? strnatcmp($aString, $bString) : null)
                    ?? (($flagsMode & _CMP_MODE_STRING_VS_STRCMP) ? strcmp($aString, $bString) : null)
                    ?? $resultLen;

                if (null !== $result) {
                    $fnCmpName = __FUNCTION__;

                    return $result;
                }

                $fnCmpName = __FUNCTION__;

                return NAN;
            }

            return $this->cmpTypeCastFail(
                $a, $b,
                $aStatus, $bStatus,
                $flagsMode, $flagsResult,
                $fnCmpName
            );
        }

        return null;
    }

    /**
     * @param callable|callable-string|null $fnCmpName
     *
     * @return null|int|float
     */
    protected function cmpValuesArray(
        $a, $b,
        int $flagsMode, int $flagsResult,
        ?string &$fnCmpName = null
    ) // : null|int|NAN
    {
        $isArrayA = is_array($a);
        $isArrayB = is_array($b);

        if ($isArrayA && $isArrayB) {
            if ($a === $b) {
                $fnCmpName = __FUNCTION__;

                return 0;
            }

            $resultCnt = null;

            if ($flagsMode & _CMP_MODE_ARRAY_SIZE_COUNT) {
                $resultCnt = (count($a) <=> count($b));

                if (0 !== $resultCnt) {
                    $fnCmpName = __FUNCTION__;

                    return $resultCnt;
                }
            }

            $result = null
                ?? (($flagsMode & _CMP_MODE_ARRAY_VS_SPACESHIP) ? ($a <=> $b) : null)
                ?? $resultCnt;

            if (null !== $result) {
                $fnCmpName = __FUNCTION__;

                return $result;
            }

            $fnCmpName = __FUNCTION__;

            return NAN;

        } elseif ($isArrayA || $isArrayB) {
            $fnCmpName = __FUNCTION__;

            return NAN;
        }

        return null;
    }

    /**
     * * @param callable|callable-string|null $fnCmpName
     *
     * @return null|int|float
     */
    protected function cmpValuesDate(
        $a, $b,
        int $flagsMode, int $flagsResult,
        ?string &$fnCmpName = null
    ) // : null|int|NAN
    {
        $isDateA = $a instanceof \DateTimeInterface;
        $isDateB = $b instanceof \DateTimeInterface;

        if ($isDateA && $isDateB) {
            if ($a === $b) {
                $fnCmpName = __FUNCTION__;

                return 0;
            }

            $aCut = $this->prepareDateCut($a, $flagsMode);
            $bCut = $this->prepareDateCut($b, $flagsMode);

            $fnCmpName = __FUNCTION__;

            return $aCut <=> $bCut;

        } elseif ($isDateA || $isDateB) {
            $fnCmpName = __FUNCTION__;

            return NAN;
        }

        return null;
    }

    /**
     * @param callable|callable-string|null $fnCmpName
     *
     * @return null|int|float
     */
    protected function cmpValuesObject(
        $a, $b,
        int $flagsMode, int $flagsResult,
        ?string &$fnCmpName = null
    ) // : null|int|NAN
    {
        $isObjectA = is_object($a);
        $isObjectB = is_object($b);

        if ($isObjectA && $isObjectB) {
            if ($a === $b) {
                $fnCmpName = __FUNCTION__;

                return 0;
            }

            if (get_class($a) !== get_class($b)) {
                $fnCmpName = __FUNCTION__;

                return NAN;
            }

            $resultCnt = null;

            if ($flagsMode & _CMP_MODE_OBJECT_SIZE_COUNT) {
                $theType = Lib::type();

                $isCountableA = $theType->countable($aCountable, $a);
                $isCountableB = $theType->countable($bCountable, $b);

                if ($isCountableA && $isCountableB) {
                    $resultCnt = count($a) <=> count($b);

                    if (0 !== $resultCnt) {
                        $fnCmpName = __FUNCTION__;

                        return $resultCnt;
                    }

                } elseif ($isCountableA || $isCountableB) {
                    $fnCmpName = __FUNCTION__;

                    return NAN;
                }
            }

            if (null !== $resultCnt) {
                $fnCmpName = __FUNCTION__;

                return $resultCnt;
            }

            $fnCmpName = __FUNCTION__;

            return NAN;

        } elseif ($isObjectA || $isObjectB) {
            $fnCmpName = __FUNCTION__;

            return NAN;
        }

        return null;
    }

    /**
     * @param callable|callable-string|null $fnCmpName
     *
     * @return null|int|float
     */
    protected function cmpValuesResource(
        $a, $b,
        int $flagsMode, int $flagsResult,
        ?string &$fnCmpName = null
    ) // : null|int|NAN
    {
        $theType = Lib::type();

        $isResourceA = $theType->resource($aResource, $a);
        $isResourceB = $theType->resource($bResource, $b);

        if ($isResourceA && $isResourceB) {
            if ($a === $b) {
                $fnCmpName = __FUNCTION__;

                return 0;
            }

            $fnCmpName = __FUNCTION__;

            return NAN;

        } elseif ($isResourceA || $isResourceB) {
            $fnCmpName = __FUNCTION__;

            return NAN;
        }

        return null;
    }


    /**
     * @param callable|callable-string|null $fnCmpName
     *
     * @return null|int|float
     */
    protected function cmpSizesString(
        $a, $b,
        int $flagsMode, int $flagsResult,
        ?string &$fnCmpName = null
    ) // : null|int|NAN
    {
        $isStringA = is_string($a);
        $isStringB = is_string($b);

        if ($isStringA || $isStringB) {
            $theType = Lib::type();

            $aStatus = $isStringA;
            $bStatus = $isStringB;
            if (! $aStatus) $aStatus = $theType->string($aString, $a);
            if (! $bStatus) $bStatus = $theType->string($bString, $b);

            if ($aStatus && $bStatus) {
                $aString = $aString ?? $a;
                $bString = $bString ?? $b;

                if ($aString === $bString) {
                    $fnCmpName = __FUNCTION__;

                    return 0;
                }

                $resultLen = null;

                if ($flagsMode & _CMP_MODE_STRING_SIZE_STRLEN) {
                    $theStr = Lib::str();

                    $aStringLen = $theStr->strlen($aString);
                    $bStringLen = $theStr->strlen($bString);

                    $resultLen = ($aStringLen <=> $bStringLen);

                    if (0 !== $resultLen) {
                        $fnCmpName = __FUNCTION__;

                        return $resultLen;
                    }

                } elseif ($flagsMode & _CMP_MODE_STRING_SIZE_STRSIZE) {
                    $resultLen = (strlen($a) <=> strlen($b));

                    if (0 !== $resultLen) {
                        $fnCmpName = __FUNCTION__;

                        return $resultLen;
                    }
                }

                if (null !== $resultLen) {
                    $fnCmpName = __FUNCTION__;

                    return $resultLen;
                }

                $fnCmpName = __FUNCTION__;

                return NAN;
            }

            return $this->cmpTypeCastFail(
                $a, $b,
                $aStatus, $bStatus,
                $flagsMode, $flagsResult,
                $fnCmpName
            );
        }

        return null;
    }

    /**
     * @param callable|callable-string|null $fnCmpName
     *
     * @return null|int|float
     */
    protected function cmpSizesArray(
        $a, $b,
        int $flagsMode, int $flagsResult,
        ?string &$fnCmpName = null
    ) // : null|int|NAN
    {
        $isArrayA = is_array($a);
        $isArrayB = is_array($b);

        if ($isArrayA && $isArrayB) {
            if ($a === $b) {
                $fnCmpName = __FUNCTION__;

                return 0;
            }

            $resultCnt = null;

            if ($flagsMode & _CMP_MODE_ARRAY_SIZE_COUNT) {
                $resultCnt = (count($a) <=> count($b));

                if (0 !== $resultCnt) {
                    $fnCmpName = __FUNCTION__;

                    return $resultCnt;
                }
            }

            if (null !== $resultCnt) {
                $fnCmpName = __FUNCTION__;

                return $resultCnt;
            }

            $fnCmpName = __FUNCTION__;

            return NAN;

        } elseif ($isArrayA || $isArrayB) {
            $fnCmpName = __FUNCTION__;

            return NAN;
        }

        return null;
    }

    /**
     * @param callable|callable-string|null $fnCmpName
     *
     * @return null|int|float
     */
    protected function cmpSizesObject(
        $a, $b,
        int $flagsMode, int $flagsResult,
        ?string &$fnCmpName = null
    ) // : null|int|NAN
    {
        $isObjectA = is_object($a);
        $isObjectB = is_object($b);

        if ($isObjectA && $isObjectB) {
            $resultCnt = null;

            if (get_class($a) !== get_class($b)) {
                $fnCmpName = __FUNCTION__;

                return NAN;
            }

            if ($flagsMode & _CMP_MODE_OBJECT_SIZE_COUNT) {
                $theType = Lib::type();

                $isCountableA = $theType->countable($aCountable, $a);
                $isCountableB = $theType->countable($bCountable, $b);

                if ($isCountableA && $isCountableB) {
                    $resultCnt = count($a) <=> count($b);

                    if (0 !== $resultCnt) {
                        $fnCmpName = __FUNCTION__;

                        return $resultCnt;
                    }

                } elseif ($isCountableA || $isCountableB) {
                    $fnCmpName = __FUNCTION__;

                    return NAN;
                }
            }

            if (null !== $resultCnt) {
                $fnCmpName = __FUNCTION__;

                return $resultCnt;
            }

            $fnCmpName = __FUNCTION__;

            return NAN;

        } elseif ($isObjectA || $isObjectB) {
            $fnCmpName = __FUNCTION__;

            return NAN;
        }

        return null;
    }


    /**
     * @param callable|callable-string|null $fnCmpName
     *
     * @return int|float
     */
    protected function cmpPeriodsDate(
        \DateTimeInterface $aStart,
        \DateTimeInterface $bStart,
        ?\DateTimeInterface $aEnd,
        ?\DateTimeInterface $bEnd,
        //
        int $flagsMode,
        int $flagsResult,
        //
        ?string &$fnCmpName = null
    ) // : int|NAN
    {
        $_aStart = $this->prepareDateCut($aStart, $flagsMode);
        $_bStart = $this->prepareDateCut($bStart, $flagsMode);

        $_aEnd = ((null === $aEnd)
            ? $_aStart
            : $this->prepareDateCut($aEnd, $flagsMode)
        );
        $_bEnd = ((null === $bEnd)
            ? $_bStart
            : $this->prepareDateCut($bEnd, $flagsMode)
        );

        $result = null;

        if (($_aStart > $_aEnd) || ($_bStart > $_bEnd)) {
            // > invalid
            $result = NAN;

        } elseif (($_aStart == $_bStart) && ($_aEnd == $_bEnd)) {
            // | AB--AB |
            $result = 0;

        } elseif (($_aStart >= $_bStart) && ($_aEnd <= $_bEnd)) {
            // | B-A-A-B |
            $result = -1;

        } elseif (($_aStart <= $_bStart) && ($_aEnd >= $_bEnd)) {
            // | A-B-B-A |
            $result = 1;

        } elseif ($_aEnd == $_bStart) {
            // | A--AB--B |
            $result = -3;

        } elseif ($_aStart == $_bEnd) {
            // | B--BA--A |
            $result = 3;

        } elseif (($_aStart < $_bStart) && ($_aEnd > $_bStart) && ($_aEnd < $_bEnd)) {
            // | A-B-A-B |
            $result = -2;

        } elseif (($_aStart > $_bStart) && ($_aStart < $_bEnd) && ($_aEnd > $_bEnd)) {
            // | B-A-B-A |
            $result = 2;

        } elseif ($_aEnd < $_bStart) {
            // | A--A B--B |
            $result = -4;

        } elseif ($_aStart > $_bEnd) {
            // | B--B A--A |
            $result = 4;
        }

        if (null !== $result) {
            $fnCmpName = __FUNCTION__;

            return $result;
        }

        $fnCmpName = __FUNCTION__;

        return NAN;
    }


    protected function prepareDateCut(\DateTimeInterface $date, int $flagsMode) : \DateTimeImmutable
    {
        $dt = null
            ?? (($date instanceof \DateTimeImmutable) ? \DateTime::createFromImmutable($date) : null)
            ?? (($date instanceof \DateTime) ? (clone $date) : null);

        if ($flagsMode & _CMP_MODE_DATE_VS_YEAR) {
            $dt
                ->setDate($date->format('Y'), 1, 1)
                ->setTime(0, 0, 0, 0)
            ;

        } elseif ($flagsMode & _CMP_MODE_DATE_VS_MONTH) {
            $dt
                ->setDate(
                    (int) $date->format('Y'),
                    (int) $date->format('m'),
                    1
                )
                ->setTime(0, 0, 0, 0)
            ;

        } elseif ($flagsMode & _CMP_MODE_DATE_VS_DAY) {
            $dt
                ->setDate(
                    (int) $date->format('Y'),
                    (int) $date->format('m'),
                    (int) $date->format('d')
                )
                ->setTime(0, 0, 0, 0)
            ;

        } elseif ($flagsMode & _CMP_MODE_DATE_VS_HOUR) {
            $dt
                ->setDate(
                    (int) $date->format('Y'),
                    (int) $date->format('m'),
                    (int) $date->format('d')
                )
                ->setTime((int) $date->format('H'), 0, 0, 0)
            ;

        } elseif ($flagsMode & _CMP_MODE_DATE_VS_MIN) {
            $dt
                ->setDate(
                    (int) $date->format('Y'),
                    (int) $date->format('m'),
                    (int) $date->format('d')
                )
                ->setTime(
                    (int) $date->format('H'),
                    (int) $date->format('i'),
                    0,
                    0
                )
            ;

        } elseif ($flagsMode & _CMP_MODE_DATE_VS_SEC) {
            $dt
                ->setDate(
                    (int) $date->format('Y'),
                    (int) $date->format('m'),
                    (int) $date->format('d')
                )
                ->setTime(
                    (int) $date->format('H'),
                    (int) $date->format('i'),
                    (int) $date->format('s'),
                    0
                )
            ;

        } elseif ($flagsMode & _CMP_MODE_DATE_VS_MSEC) {
            $dt
                ->setDate(
                    (int) $date->format('Y'),
                    (int) $date->format('m'),
                    (int) $date->format('d')
                )
                ->setTime(
                    (int) $date->format('H'),
                    (int) $date->format('i'),
                    (int) $date->format('s'),
                    (int) $date->format('v')
                )
            ;

        } elseif ($flagsMode & _CMP_MODE_DATE_VS_USEC) {
            $dt
                ->setDate(
                    (int) $date->format('Y'),
                    (int) $date->format('m'),
                    (int) $date->format('d')
                )
                ->setTime(
                    (int) $date->format('H'),
                    (int) $date->format('i'),
                    (int) $date->format('s'),
                    (int) $date->format('u')
                )
            ;
        }

        $dateImmutable = \DateTimeImmutable::createFromMutable($dt);

        return $dateImmutable;
    }


    /**
     * @return null|float
     */
    protected function cmpTypeCastFail(
        $a, $b,
        bool $aStatus, bool $bStatus,
        int $flagsMode, int $flagsResult,
        ?string &$fnCmpName = null
    ) // : null|int|NAN
    {
        if ($flagsMode & _CMP_MODE_TYPE_CAST_OR_CONTINUE) {
            return null;

        } elseif ($flagsMode & _CMP_MODE_TYPE_CAST_OR_NAN) {
            $fnCmpName = __FUNCTION__;

            return NAN;

        } elseif ($flagsMode & _CMP_MODE_TYPE_STRICT) {
            /** @see static::cmpTypesStrict() */

            return NAN;
        }

        $fnCmpName = __FUNCTION__;

        return NAN;
    }


    /**
     * @param callable|callable-string|null $fnCmpName
     *
     * @return int|float
     *
     * @throws RuntimeException
     */
    protected function cmpResultUnknown(
        $result,
        $a, $b,
        int $flagsMode, int $flagsResult,
        ?string &$fnCmpName = null
    ) // : int|NAN
    {
        $_result = $result;

        if (null === $result) {
            if ($flagsResult & _CMP_MODE_RESULT_SPACESHIP) {
                $fnCmpName = __FUNCTION__;

                $_result = $a <=> $b;

            } elseif ($flagsResult & _CMP_RESULT_NULL_0) {
                $fnCmpName = __FUNCTION__;

                $_result = 0;

            } elseif ($flagsResult & _CMP_RESULT_NULL_A_LT) {
                $fnCmpName = __FUNCTION__;

                $_result = 1;

            } elseif ($flagsResult & _CMP_RESULT_NULL_A_GT) {
                $fnCmpName = __FUNCTION__;

                $_result = -1;

            } else {
                // } elseif ($flags & _CMP_MODE_NULL_NAN) {
                $fnCmpName = __FUNCTION__;

                $_result = NAN;
            }
        }

        if (is_float($_result) && is_nan($_result)) {
            if ($flagsResult & _CMP_RESULT_NAN_THROW) {
                throw new RuntimeException(
                    [ 'Values are incomparable', $a, $b ]
                );
            }
        }

        return $_result;
    }


    protected function flagsModeDefault(?int $flagsMode = null) : int
    {
        $flagsMode = $flagsMode ?? 0;

        $flags = $flagsMode;

        $sum = 0;
        $sum += (($flags & _CMP_MODE_TYPECAST_A) ? 1 : 0);
        $sum += (($flags & _CMP_MODE_TYPECAST_B) ? 1 : 0);
        if (0 === $sum) {
            $flags &= ~(
                _CMP_MODE_TYPECAST_A
                | _CMP_MODE_TYPECAST_B
            );

            $flags |= (_CMP_MODE_TYPECAST_A | _CMP_MODE_TYPECAST_B);
        }
        unset($sum);

        $sum = 0;
        $sum += (($flags & _CMP_MODE_TYPE_STRICT) ? 1 : 0);
        $sum += (($flags & _CMP_MODE_TYPE_CAST_OR_NAN) ? 1 : 0);
        $sum += (($flags & _CMP_MODE_TYPE_CAST_OR_CONTINUE) ? 1 : 0);
        if (1 !== $sum) {
            $flags &= ~(
                _CMP_MODE_TYPE_STRICT
                | _CMP_MODE_TYPE_CAST_OR_NAN
                | _CMP_MODE_TYPE_CAST_OR_CONTINUE
            );

            $flags |= _CMP_MODE_TYPE_CAST_OR_CONTINUE;
        }
        unset($sum);

        $sum = 0;
        $sum += (($flags & _CMP_MODE_STRING_SIZE_STRLEN) ? 1 : 0);
        $sum += (($flags & _CMP_MODE_STRING_SIZE_STRSIZE) ? 1 : 0);
        $sum += (($flags & _CMP_MODE_STRING_SIZE_IGNORE) ? 1 : 0);
        if (1 !== $sum) {
            $flags &= ~(
                _CMP_MODE_STRING_SIZE_STRLEN
                | _CMP_MODE_STRING_SIZE_STRSIZE
                | _CMP_MODE_STRING_SIZE_IGNORE
            );

            $flags |= _CMP_MODE_STRING_SIZE_STRLEN;
        }
        unset($sum);

        $sum = 0;
        $sum += (($flags & _CMP_MODE_STRING_VS_STRNATCASECMP) ? 1 : 0);
        $sum += (($flags & _CMP_MODE_STRING_VS_STRCASECMP) ? 1 : 0);
        $sum += (($flags & _CMP_MODE_STRING_VS_STRNATCMP) ? 1 : 0);
        $sum += (($flags & _CMP_MODE_STRING_VS_STRCMP) ? 1 : 0);
        $sum += (($flags & _CMP_MODE_STRING_VS_IGNORE) ? 1 : 0);
        if (1 !== $sum) {
            $flags &= ~(
                _CMP_MODE_STRING_VS_STRNATCASECMP
                | _CMP_MODE_STRING_VS_STRCASECMP
                | _CMP_MODE_STRING_VS_STRNATCMP
                | _CMP_MODE_STRING_VS_STRCMP
                | _CMP_MODE_STRING_VS_IGNORE
            );

            $flags |= _CMP_MODE_STRING_VS_STRCMP;
        }
        unset($sum);

        $sum = 0;
        $sum += (($flags & _CMP_MODE_ARRAY_SIZE_COUNT) ? 1 : 0);
        $sum += (($flags & _CMP_MODE_ARRAY_SIZE_IGNORE) ? 1 : 0);
        if (1 !== $sum) {
            $flags &= ~(
                _CMP_MODE_ARRAY_SIZE_COUNT
                | _CMP_MODE_ARRAY_SIZE_IGNORE
            );

            $flags |= _CMP_MODE_ARRAY_SIZE_COUNT;
        }
        unset($sum);

        $sum = 0;
        $sum += (($flags & _CMP_MODE_ARRAY_VS_SPACESHIP) ? 1 : 0);
        $sum += (($flags & _CMP_MODE_ARRAY_VS_IGNORE) ? 1 : 0);
        if (1 !== $sum) {
            $flags &= ~(
                _CMP_MODE_ARRAY_VS_SPACESHIP
                | _CMP_MODE_ARRAY_VS_IGNORE
            );

            $flags |= _CMP_MODE_ARRAY_VS_IGNORE;
        }
        unset($sum);

        $sum = 0;
        $sum += (($flags & _CMP_MODE_DATE_VS_YEAR) ? 1 : 0);
        $sum += (($flags & _CMP_MODE_DATE_VS_MONTH) ? 1 : 0);
        $sum += (($flags & _CMP_MODE_DATE_VS_DAY) ? 1 : 0);
        $sum += (($flags & _CMP_MODE_DATE_VS_HOUR) ? 1 : 0);
        $sum += (($flags & _CMP_MODE_DATE_VS_MIN) ? 1 : 0);
        $sum += (($flags & _CMP_MODE_DATE_VS_SEC) ? 1 : 0);
        $sum += (($flags & _CMP_MODE_DATE_VS_MSEC) ? 1 : 0);
        $sum += (($flags & _CMP_MODE_DATE_VS_USEC) ? 1 : 0);
        if (1 !== $sum) {
            $flags &= ~(
                _CMP_MODE_DATE_VS_YEAR
                | _CMP_MODE_DATE_VS_MONTH
                | _CMP_MODE_DATE_VS_DAY
                | _CMP_MODE_DATE_VS_HOUR
                | _CMP_MODE_DATE_VS_MIN
                | _CMP_MODE_DATE_VS_SEC
                | _CMP_MODE_DATE_VS_MSEC
                | _CMP_MODE_DATE_VS_USEC
            );

            $flags |= _CMP_MODE_DATE_VS_USEC;
        }
        unset($sum);

        $sum = 0;
        $sum += (($flags & _CMP_MODE_OBJECT_SIZE_COUNT) ? 1 : 0);
        $sum += (($flags & _CMP_MODE_OBJECT_SIZE_IGNORE) ? 1 : 0);
        if (1 !== $sum) {
            $flags &= ~(
                _CMP_MODE_OBJECT_SIZE_COUNT
                | _CMP_MODE_OBJECT_SIZE_IGNORE
            );

            $flags |= _CMP_MODE_OBJECT_SIZE_COUNT;
        }
        unset($sum);

        return $flags;
    }

    protected function flagsResultDefault(?int $flagsResult = null) : int
    {
        $flagsResult = $flagsResult ?? 0;

        $flags = $flagsResult;

        $sum = 0;
        $sum += (($flags & _CMP_MODE_RESULT_SPACESHIP) ? 1 : 0);
        $sum += (($flags & _CMP_RESULT_NULL_0) ? 1 : 0);
        $sum += (($flags & _CMP_RESULT_NULL_A_LT) ? 1 : 0);
        $sum += (($flags & _CMP_RESULT_NULL_A_GT) ? 1 : 0);
        $sum += (($flags & _CMP_RESULT_NULL_NAN) ? 1 : 0);
        if (1 !== $sum) {
            $flags &= ~(
                _CMP_MODE_RESULT_SPACESHIP
                | _CMP_RESULT_NULL_0
                | _CMP_RESULT_NULL_A_LT
                | _CMP_RESULT_NULL_A_GT
                | _CMP_RESULT_NULL_NAN
            );

            $flags |= _CMP_RESULT_NULL_NAN;
        }
        unset($sum);

        $sum = 0;
        $sum += (($flags & _CMP_RESULT_NAN_THROW) ? 1 : 0);
        $sum += (($flags & _CMP_RESULT_NAN_RETURN) ? 1 : 0);
        if (1 !== $sum) {
            $flags &= ~(
                _CMP_RESULT_NAN_THROW
                | _CMP_RESULT_NAN_RETURN
            );

            $flags |= _CMP_RESULT_NAN_THROW;
        }
        unset($sum);

        return $flags;
    }
}
