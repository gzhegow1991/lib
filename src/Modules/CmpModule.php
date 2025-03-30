<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;


if (! defined('_CMP_MODE_ERROR_THROW')) define('_CMP_MODE_ERROR_THROW', 1 << 0);
if (! defined('_CMP_MODE_ERROR_NAN')) define('_CMP_MODE_ERROR_NAN', 1 << 1);
if (! defined('_CMP_MODE_TYPE_STRICT')) define('_CMP_MODE_TYPE_STRICT', 1 << 2);
if (! defined('_CMP_MODE_TYPE_SPACESHIP')) define('_CMP_MODE_TYPE_SPACESHIP', 1 << 3);
if (! defined('_CMP_MODE_TYPE_GT')) define('_CMP_MODE_TYPE_GT', 1 << 4);
if (! defined('_CMP_MODE_TYPE_LT')) define('_CMP_MODE_TYPE_LT', 1 << 5);
if (! defined('_CMP_MODE_TYPE_CONTINUE')) define('_CMP_MODE_TYPE_CONTINUE', 1 << 6);
if (! defined('_CMP_MODE_TYPE_NAN')) define('_CMP_MODE_TYPE_NAN', 1 << 7);
if (! defined('_CMP_MODE_STRING_VS_STRCMP')) define('_CMP_MODE_STRING_VS_STRCMP', 1 << 8);
if (! defined('_CMP_MODE_STRING_VS_STRCASECMP')) define('_CMP_MODE_STRING_VS_STRCASECMP', 1 << 9);
if (! defined('_CMP_MODE_STRING_VS_STRNATCMP')) define('_CMP_MODE_STRING_VS_STRNATCMP', 1 << 10);
if (! defined('_CMP_MODE_STRING_VS_STRNATCASECMP')) define('_CMP_MODE_STRING_VS_STRNATCASECMP', 1 << 11);
if (! defined('_CMP_MODE_STRING_VS_SPACESHIP')) define('_CMP_MODE_STRING_VS_SPACESHIP', 1 << 12);
if (! defined('_CMP_MODE_STRING_VS_IGNORE')) define('_CMP_MODE_STRING_VS_IGNORE', 1 << 13);
if (! defined('_CMP_MODE_STRING_SIZE_LENGTH')) define('_CMP_MODE_STRING_SIZE_LENGTH', 1 << 14);
if (! defined('_CMP_MODE_STRING_SIZE_STRLEN')) define('_CMP_MODE_STRING_SIZE_STRLEN', 1 << 15);
if (! defined('_CMP_MODE_STRING_SIZE_IGNORE')) define('_CMP_MODE_STRING_SIZE_IGNORE', 1 << 16);
if (! defined('_CMP_MODE_ARRAY_SIZE_COUNT')) define('_CMP_MODE_ARRAY_SIZE_COUNT', 1 << 17);
if (! defined('_CMP_MODE_ARRAY_SIZE_IGNORE')) define('_CMP_MODE_ARRAY_SIZE_IGNORE', 1 << 18);
if (! defined('_CMP_MODE_ARRAY_VS_SPACESHIP')) define('_CMP_MODE_ARRAY_VS_SPACESHIP', 1 << 19);
if (! defined('_CMP_MODE_ARRAY_VS_IGNORE')) define('_CMP_MODE_ARRAY_VS_IGNORE', 1 << 20);
if (! defined('_CMP_MODE_NULL_SPACESHIP')) define('_CMP_MODE_NULL_SPACESHIP', 1 << 21);
if (! defined('_CMP_MODE_NULL_0')) define('_CMP_MODE_NULL_0', 1 << 22);
if (! defined('_CMP_MODE_NULL_GT')) define('_CMP_MODE_NULL_GT', 1 << 23);
if (! defined('_CMP_MODE_NULL_LT')) define('_CMP_MODE_NULL_LT', 1 << 24);
if (! defined('_CMP_MODE_NULL_NAN')) define('_CMP_MODE_NULL_NAN', 1 << 25);

class CmpModule
{
    public function fnSame(int $flags = null, array $refs = []) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        $fnCmpName = null;
        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
            $fnCmpName = null;
        }

        $_flags = $this->flagsDefault($flags);

        $fn = function ($a, $b) use ($_flags, &$fnCmpName) {
            $result = null
                ?? $this->cmpNan($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNull($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNil($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpBoolean($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNumeric($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpString($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpArray($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpObject($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpResource($a, $b, $_flags, $fnCmpName);

            $result = $this->cmpUnknown($result, $a, $b, $_flags, $fnCmpName);

            if (is_float($result) && is_nan($result)) {
                return ($_flags & _CMP_MODE_TYPE_STRICT)
                    ? ($a === $b)
                    : ($a == $b);
            }

            return 0 === $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnDifferent(int $flags = null, array $refs = []) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        $fnCmpName = null;
        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
            $fnCmpName = null;
        }

        $_flags = $this->flagsDefault($flags);

        $fn = function ($a, $b) use ($_flags, &$fnCmpName) {
            $result = null
                ?? $this->cmpNan($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNull($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNil($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpBoolean($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNumeric($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpString($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpArray($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpObject($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpResource($a, $b, $_flags, $fnCmpName);

            $result = $this->cmpUnknown($result, $a, $b, $_flags, $fnCmpName);

            if (is_float($result) && is_nan($result)) {
                return ($_flags & _CMP_MODE_TYPE_STRICT)
                    ? ($a !== $b)
                    : ($a != $b);
            }

            return 0 !== $result;
        };

        unset($fnCmpName);

        return $fn;
    }


    public function fnCompare(int $flags = null, array $refs = []) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        $fnCmpName = null;
        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
            $fnCmpName = null;
        }

        $_flags = $this->flagsDefault($flags);

        $fn = function ($a, $b) use ($_flags, &$fnCmpName) {
            $result = null
                ?? $this->cmpTypeStrict($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNan($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNil($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNull($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpBoolean($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNumeric($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpString($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpArray($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpDate($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpObject($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpResource($a, $b, $_flags, $fnCmpName);

            $result = $this->cmpUnknown($result, $a, $b, $_flags, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareNil(int $flags = null, array $refs = []) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        $fnCmpName = null;
        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
            $fnCmpName = null;
        }

        $_flags = $this->flagsDefault($flags);

        $fn = function ($a, $b) use ($_flags, &$fnCmpName) {
            $result = null
                ?? $this->cmpTypeStrict($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNan($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNil($a, $b, $_flags, $fnCmpName);

            $result = $this->cmpUnknown($result, $a, $b, $_flags, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareNull(int $flags = null, array $refs = []) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        $fnCmpName = null;
        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
            $fnCmpName = null;
        }

        $_flags = $this->flagsDefault($flags);

        $fn = function ($a, $b) use ($_flags, &$fnCmpName) {
            $result = null
                ?? $this->cmpTypeStrict($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNan($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNil($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNull($a, $b, $_flags, $fnCmpName);

            $result = $this->cmpUnknown($result, $a, $b, $_flags, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareBoolean(int $flags = null, array $refs = []) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        $fnCmpName = null;
        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
            $fnCmpName = null;
        }

        $_flags = $this->flagsDefault($flags);

        $fn = function ($a, $b) use ($_flags, &$fnCmpName) {
            $result = null
                ?? $this->cmpTypeStrict($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNan($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNil($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNull($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpBoolean($a, $b, $_flags, $fnCmpName);

            $result = $this->cmpUnknown($result, $a, $b, $_flags, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareInteger(int $flags = null, array $refs = []) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        $fnCmpName = null;
        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
            $fnCmpName = null;
        }

        $_flags = $this->flagsDefault($flags);

        $fn = function ($a, $b) use ($_flags, &$fnCmpName) {
            $result = null
                ?? $this->cmpTypeStrict($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNan($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNil($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNull($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpInteger($a, $b, $_flags, $fnCmpName);

            $result = $this->cmpUnknown($result, $a, $b, $_flags, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareFloat(int $flags = null, array $refs = []) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        $fnCmpName = null;
        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
            $fnCmpName = null;
        }

        $_flags = $this->flagsDefault($flags);

        $fn = function ($a, $b) use ($_flags, &$fnCmpName) {
            $result = null
                ?? $this->cmpTypeStrict($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNan($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNil($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNull($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpFloat($a, $b, $_flags, $fnCmpName);

            $result = $this->cmpUnknown($result, $a, $b, $_flags, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareNumeric(int $flags = null, array $refs = []) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        $fnCmpName = null;
        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
            $fnCmpName = null;
        }

        $_flags = $this->flagsDefault($flags);

        $fn = function ($a, $b) use ($_flags, &$fnCmpName) {
            $result = null
                ?? $this->cmpTypeStrict($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNan($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNil($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNull($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNumeric($a, $b, $_flags, $fnCmpName);

            $result = $this->cmpUnknown($result, $a, $b, $_flags, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareString(int $flags = null, array $refs = []) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        $fnCmpName = null;
        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
            $fnCmpName = null;
        }

        $_flags = $this->flagsDefault($flags);

        $fn = function ($a, $b) use ($_flags, &$fnCmpName) {
            $result = null
                ?? $this->cmpTypeStrict($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNan($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNil($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNull($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpString($a, $b, $_flags, $fnCmpName);

            $result = $this->cmpUnknown($result, $a, $b, $_flags, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareArray(int $flags = null, array $refs = []) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        $fnCmpName = null;
        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
            $fnCmpName = null;
        }

        $_flags = $this->flagsDefault($flags);

        $fn = function ($a, $b) use ($_flags, &$fnCmpName) {
            $result = null
                ?? $this->cmpTypeStrict($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNan($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNil($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNull($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpArray($a, $b, $_flags, $fnCmpName);

            $result = $this->cmpUnknown($result, $a, $b, $_flags, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareDate(int $flags = null, array $refs = []) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        $fnCmpName = null;
        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
            $fnCmpName = null;
        }

        $_flags = $this->flagsDefault($flags);

        $fn = function ($a, $b) use ($_flags, &$fnCmpName) {
            $result = null
                ?? $this->cmpTypeStrict($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNan($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNil($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNull($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpDate($a, $b, $_flags, $fnCmpName);

            $result = $this->cmpUnknown($result, $a, $b, $_flags, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareObject(int $flags = null, array $refs = []) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        $fnCmpName = null;
        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
            $fnCmpName = null;
        }

        $_flags = $this->flagsDefault($flags);

        $fn = function ($a, $b) use ($_flags, &$fnCmpName) {
            $result = null
                ?? $this->cmpTypeStrict($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNan($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNil($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNull($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpDate($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpObject($a, $b, $_flags, $fnCmpName);

            $result = $this->cmpUnknown($result, $a, $b, $_flags, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }

    public function fnCompareResource(int $flags = null, array $refs = []) : \Closure
    {
        $withFnCmpName = array_key_exists(0, $refs);

        $fnCmpName = null;
        if ($withFnCmpName) {
            $fnCmpName =& $refs[ 0 ];
            $fnCmpName = null;
        }

        $_flags = $this->flagsDefault($flags);

        $fn = function ($a, $b) use ($_flags, &$fnCmpName) {
            $result = null
                ?? $this->cmpTypeStrict($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNan($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNil($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpNull($a, $b, $_flags, $fnCmpName)
                ?? $this->cmpResource($a, $b, $_flags, $fnCmpName);

            $result = $this->cmpUnknown($result, $a, $b, $_flags, $fnCmpName);

            return $result;
        };

        unset($fnCmpName);

        return $fn;
    }


    /**
     * @return null|float
     */
    protected function cmpTypeStrict($a, $b, int $flags, string &$fnCmpName = null) // : null|NAN
    {
        if ($flags & _CMP_MODE_TYPE_STRICT) {
            if (gettype($a) !== gettype($b)) {
                $fnCmpName = __FUNCTION__;

                return NAN;
            }
        }

        return null;
    }

    /**
     * @return null|int|float
     */
    protected function cmpTypeCastFailed(
        $a, $b,
        bool $aStatus, bool $bStatus,
        int $flags,
        string &$fnCmpName = null
    ) // : null|int|NAN
    {
        if ($flags & _CMP_MODE_TYPE_SPACESHIP) {
            $fnCmpName = __FUNCTION__;

            return $aStatus <=> $bStatus;

        } elseif ($flags & _CMP_MODE_TYPE_GT) {
            $fnCmpName = __FUNCTION__;

            return 1;

        } elseif ($flags & _CMP_MODE_TYPE_LT) {
            $fnCmpName = __FUNCTION__;

            return -1;

        } elseif ($flags & _CMP_MODE_TYPE_NAN) {
            $fnCmpName = __FUNCTION__;

            return NAN;
        }

        // elseif ($flags & _CMP_MODE_TYPE_CONTINUE) {
        return null;
        // }
    }


    /**
     * @return null|int|float
     */
    protected function cmpNan($a, $b, int $flags, string &$fnCmpName = null) // : null|int|NAN
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
     * @return null|int|float
     */
    protected function cmpNil($a, $b, int $flags, string &$fnCmpName = null) // : null|int|NAN
    {
        $theType = Lib::type();

        $isNilA = $theType->is_nil($a);
        $isNilB = $theType->is_nil($b);

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
     * @return null|int|float
     */
    protected function cmpNull($a, $b, int $flags, string &$fnCmpName = null) // : null|int|NAN
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
            if (! $aStatus) $aStatus = $theType->is_blank($a);
            if (! $bStatus) $bStatus = $theType->is_blank($b);

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
     * @return null|int|float
     */
    protected function cmpBoolean($a, $b, int $flags, string &$fnCmpName = null) // : null|int|NAN
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
            if (! $isBoolA) $aStatus = $theType->bool($aBool, $a);
            if (! $isBoolB) $bStatus = $theType->bool($bBool, $b);

            if ($aStatus && $bStatus) {
                $aBool = $aBool ?? $a;
                $bBool = $bBool ?? $b;

                $fnCmpName = __FUNCTION__;

                return $aBool <=> $bBool;
            }

            return $this->cmpTypeCastFailed(
                $a, $b,
                $aStatus, $bStatus,
                $flags
            );
        }

        return null;
    }

    /**
     * @return null|int|float
     */
    protected function cmpInteger($a, $b, int $flags, string &$fnCmpName = null) // : null|int|NAN
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
            if (! $aStatus) $aStatus = $theType->int($aInt, $a);
            if (! $bStatus) $bStatus = $theType->int($bInt, $b);

            if ($aStatus && $bStatus) {
                $aInt = $aInt ?? $a;
                $bInt = $bInt ?? $b;

                $fnCmpName = __FUNCTION__;

                return $aInt <=> $bInt;
            }

            return $this->cmpTypeCastFailed(
                $a, $b,
                $aStatus, $bStatus,
                $flags
            );
        }

        return null;
    }

    /**
     * @return null|int|float
     */
    protected function cmpFloat($a, $b, int $flags, string &$fnCmpName = null) // : null|int|NAN
    {
        $isFloatA = is_float($a);
        $isFloatB = is_float($b);

        if ($isFloatA && $isFloatB) {
            $fnCmpName = __FUNCTION__;

            return $a <=> $b;

        } elseif ($isFloatA || $isFloatB) {
            $theType = Lib::type();

            $aStatus = $isFloatA;
            $bStatus = $isFloatB;
            if (! $aStatus) $aStatus = $theType->num($aNum, $a);
            if (! $bStatus) $bStatus = $theType->num($bNum, $b);

            if ($aStatus && $bStatus) {
                $aFloat = floatval($aNum ?? $a);
                $bFloat = floatval($bNum ?? $b);

                $fnCmpName = __FUNCTION__;

                return $aFloat <=> $bFloat;
            }

            return $this->cmpTypeCastFailed(
                $a, $b,
                $aStatus, $bStatus,
                $flags
            );
        }

        return null;
    }

    /**
     * @return null|int|float
     */
    protected function cmpNumeric($a, $b, int $flags, string &$fnCmpName = null) // : null|int|NAN
    {
        $isNumericA = is_numeric($a);
        $isNumericB = is_numeric($b);

        if ($isNumericA || $isNumericB) {
            $theType = Lib::type();

            $aStatus = $isNumericA;
            $bStatus = $isNumericB;
            if (! $aStatus) $aStatus = $theType->int($aInt, $a);
            if (! $bStatus) $bStatus = $theType->int($bInt, $b);

            if ($aStatus && $bStatus) {
                $aInt = $aInt ?? $a;
                $bInt = $bInt ?? $b;

                $fnCmpName = __FUNCTION__;

                return $aInt <=> $bInt;
            }

            $aStatus = $isNumericA;
            $bStatus = $isNumericB;
            if (! $aStatus) $aStatus = $theType->num($aNum, $a);
            if (! $bStatus) $bStatus = $theType->num($bNum, $b);

            if ($aStatus && $bStatus) {
                $aNum = $aNum ?? $a;
                $bNum = $bNum ?? $b;

                $aFloat = floatval($aNum);
                $bFloat = floatval($bNum);

                $fnCmpName = __FUNCTION__;

                return $aFloat <=> $bFloat;
            }

            return $this->cmpTypeCastFailed(
                $a, $b,
                $aStatus, $bStatus,
                $flags
            );
        }

        return null;
    }

    /**
     * @return null|int|float
     */
    protected function cmpString($a, $b, int $flags, string &$fnCmpName = null) // : null|int|NAN
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

                $result = null;

                if ($flags & _CMP_MODE_STRING_SIZE_LENGTH) {
                    $theStr = Lib::str();

                    $aStringLen = $theStr->strlen($aString);
                    $bStringLen = $theStr->strlen($bString);

                    $result = ($aStringLen <=> $bStringLen);

                    if (0 !== $result) {
                        $fnCmpName = __FUNCTION__;

                        return $result;
                    }

                } elseif ($flags & _CMP_MODE_STRING_SIZE_STRLEN) {
                    $result = (strlen($a) <=> strlen($b));

                    if (0 !== $result) {
                        $fnCmpName = __FUNCTION__;

                        return $result;
                    }
                }

                $result = null
                    ?? (($flags & _CMP_MODE_STRING_VS_STRNATCASECMP) ? strnatcasecmp($aString, $bString) : null)
                    ?? (($flags & _CMP_MODE_STRING_VS_STRCASECMP) ? strcasecmp($aString, $bString) : null)
                    ?? (($flags & _CMP_MODE_STRING_VS_STRNATCMP) ? strnatcmp($aString, $bString) : null)
                    ?? (($flags & _CMP_MODE_STRING_VS_STRCMP) ? strcmp($aString, $bString) : null)
                    ?? (($flags & _CMP_MODE_STRING_VS_SPACESHIP) ? ($aString <=> $bString) : null);

                if (null !== $result) {
                    $fnCmpName = __FUNCTION__;

                    return $result;
                }

                $fnCmpName = __FUNCTION__;

                return NAN;
            }

            return $this->cmpTypeCastFailed(
                $a, $b,
                $aStatus, $bStatus,
                $flags
            );
        }

        return null;
    }

    /**
     * @return null|int|float
     */
    protected function cmpArray($a, $b, int $flags, string &$fnCmpName = null) // : null|int|NAN
    {
        $isArrayA = is_array($a);
        $isArrayB = is_array($b);

        if ($isArrayA && $isArrayB) {
            if ($a === $b) {
                $fnCmpName = __FUNCTION__;

                return 0;
            }

            $result = null;

            if ($flags & _CMP_MODE_ARRAY_SIZE_COUNT) {
                $result = (count($a) <=> count($b));

                if (0 !== $result) {
                    $fnCmpName = __FUNCTION__;

                    return $result;
                }
            }

            if ($flags & _CMP_MODE_ARRAY_VS_SPACESHIP) {
                $fnCmpName = __FUNCTION__;

                return $a <=> $b;
            }

            $fnCmpName = __FUNCTION__;

            return NAN;

        } elseif ($isArrayA || $isArrayB) {
            return $this->cmpTypeCastFailed(
                $a, $b,
                $isArrayA, $isArrayB,
                $flags
            );
        }

        return null;
    }

    /**
     * @return null|int|float
     */
    protected function cmpDate($a, $b, int $flags, string &$fnCmpName = null) // : null|int|NAN
    {
        $isDateA = $a instanceof \DateTimeInterface;
        $isDateB = $b instanceof \DateTimeInterface;

        if ($isDateA && $isDateB) {
            $fnCmpName = __FUNCTION__;

            return $a <=> $b;

        } elseif ($isDateA || $isDateB) {
            $theType = Lib::type();

            $aStatus = $isDateA;
            $bStatus = $isDateB;
            if (! $aStatus) $aStatus = $theType->date_interface($aDate, $a);
            if (! $bStatus) $bStatus = $theType->date_interface($bDate, $b);

            if ($aStatus && $bStatus) {
                $aDate = $aDate ?? $a;
                $bDate = $bDate ?? $b;

                $fnCmpName = __FUNCTION__;

                return $aDate <=> $bDate;
            }

            return $this->cmpTypeCastFailed(
                $a, $b,
                $aStatus, $bStatus,
                $flags
            );
        }

        return null;
    }

    /**
     * @return null|int|float
     */
    protected function cmpObject($a, $b, int $flags, string &$fnCmpName = null) // : null|int|NAN
    {
        $isObjectA = is_object($a);
        $isObjectB = is_object($b);

        if ($isObjectA && $isObjectB) {
            if ($a === $b) {
                $fnCmpName = __FUNCTION__;

                return 0;
            }

            $theType = Lib::type();

            $isCountableA = $theType->countable($aCountable, $a);
            $isCountableB = $theType->countable($bCountable, $b);

            if ($isCountableA && $isCountableB) {
                $fnCmpName = __FUNCTION__;

                return count($a) <=> count($b);
            }

            $fnCmpName = __FUNCTION__;

            return NAN;

        } elseif ($isObjectA || $isObjectB) {
            return $this->cmpTypeCastFailed(
                $a, $b,
                $isObjectA, $isObjectB,
                $flags
            );
        }

        return null;
    }

    /**
     * @return null|int|float
     */
    protected function cmpResource($a, $b, int $flags, string &$fnCmpName = null) // : null|int|NAN
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
            return $this->cmpTypeCastFailed(
                $a, $b,
                $isResourceA, $isResourceB,
                $flags
            );
        }

        return null;
    }


    /**
     * @return int|float
     *
     * @throws RuntimeException
     */
    protected function cmpUnknown($result, $a, $b, int $flags, string &$fnCmpName = null) // : int|NAN
    {
        $_result = $result;

        if (null === $result) {
            if ($flags & _CMP_MODE_NULL_SPACESHIP) {
                $fnCmpName = __FUNCTION__;

                $_result = $a <=> $b;

            } elseif ($flags & _CMP_MODE_NULL_0) {
                $fnCmpName = __FUNCTION__;

                $_result = 0;

            } elseif ($flags & _CMP_MODE_NULL_GT) {
                $fnCmpName = __FUNCTION__;

                $_result = 1;

            } elseif ($flags & _CMP_MODE_NULL_LT) {
                $fnCmpName = __FUNCTION__;

                $_result = -1;

            } else {
                // } elseif ($flags & _CMP_MODE_NULL_NAN) {
                $fnCmpName = __FUNCTION__;

                $_result = NAN;
            }
        }

        if (is_float($_result) && is_nan($_result)) {
            if ($flags & _CMP_MODE_ERROR_THROW) {
                throw new RuntimeException(
                    [ 'Values are incomparable', $a, $b ]
                );
            }
        }

        return $_result;
    }


    protected function flagsDefault(int $flags = null) : int
    {
        $flags = $flags ?? 0;

        $_flags = $flags;

        $sum = (int) (
            ((bool) ($_flags & _CMP_MODE_ERROR_THROW))
            + ((bool) ($_flags & _CMP_MODE_ERROR_NAN))
        );
        if (1 !== $sum) {
            $_flags &= ~(
                _CMP_MODE_ERROR_THROW
                | _CMP_MODE_ERROR_NAN
            );

            $_flags |= _CMP_MODE_ERROR_THROW;
        }

        $sum = (int) (
            ((bool) ($_flags & _CMP_MODE_TYPE_STRICT))
            + ((bool) ($_flags & _CMP_MODE_TYPE_SPACESHIP))
            + ((bool) ($_flags & _CMP_MODE_TYPE_GT))
            + ((bool) ($_flags & _CMP_MODE_TYPE_LT))
            + ((bool) ($_flags & _CMP_MODE_TYPE_CONTINUE))
            + ((bool) ($_flags & _CMP_MODE_TYPE_NAN))
        );
        if (1 !== $sum) {
            $_flags &= ~(
                _CMP_MODE_TYPE_STRICT
                | _CMP_MODE_TYPE_SPACESHIP
                | _CMP_MODE_TYPE_GT
                | _CMP_MODE_TYPE_LT
                | _CMP_MODE_TYPE_CONTINUE
                | _CMP_MODE_TYPE_NAN
            );

            $_flags |= _CMP_MODE_TYPE_NAN;
        }

        $sum = (int) (
            ((bool) ($_flags & _CMP_MODE_STRING_SIZE_LENGTH))
            + ((bool) ($_flags & _CMP_MODE_STRING_SIZE_STRLEN))
            + ((bool) ($_flags & _CMP_MODE_STRING_SIZE_IGNORE))
        );
        if (1 !== $sum) {
            $_flags &= ~(
                _CMP_MODE_STRING_SIZE_LENGTH
                | _CMP_MODE_STRING_SIZE_STRLEN
                | _CMP_MODE_STRING_SIZE_IGNORE
            );

            $_flags |= _CMP_MODE_STRING_SIZE_LENGTH;
        }

        $sum = (int) (
            ((bool) ($_flags & _CMP_MODE_STRING_VS_STRNATCASECMP))
            + ((bool) ($_flags & _CMP_MODE_STRING_VS_STRCASECMP))
            + ((bool) ($_flags & _CMP_MODE_STRING_VS_STRNATCMP))
            + ((bool) ($_flags & _CMP_MODE_STRING_VS_STRCMP))
            + ((bool) ($_flags & _CMP_MODE_STRING_VS_SPACESHIP))
            + ((bool) ($_flags & _CMP_MODE_STRING_VS_IGNORE))
        );
        if (1 !== $sum) {
            $_flags &= ~(
                _CMP_MODE_STRING_VS_STRNATCASECMP
                | _CMP_MODE_STRING_VS_STRCASECMP
                | _CMP_MODE_STRING_VS_STRNATCMP
                | _CMP_MODE_STRING_VS_STRCMP
                | _CMP_MODE_STRING_VS_SPACESHIP
                | _CMP_MODE_STRING_VS_IGNORE
            );

            $_flags |= _CMP_MODE_STRING_VS_STRCMP;
        }

        $sum = (int) (
            ((bool) ($_flags & _CMP_MODE_ARRAY_SIZE_COUNT))
            + ((bool) ($_flags & _CMP_MODE_ARRAY_SIZE_IGNORE))
        );
        if (1 !== $sum) {
            $_flags &= ~(
                _CMP_MODE_ARRAY_SIZE_COUNT
                | _CMP_MODE_ARRAY_SIZE_IGNORE
            );

            $_flags |= _CMP_MODE_ARRAY_SIZE_COUNT;
        }

        $sum = (int) (
            ((bool) ($_flags & _CMP_MODE_ARRAY_VS_SPACESHIP))
            + ((bool) ($_flags & _CMP_MODE_ARRAY_VS_IGNORE))
        );
        if (1 !== $sum) {
            $_flags &= ~(
                _CMP_MODE_ARRAY_VS_SPACESHIP
                | _CMP_MODE_ARRAY_VS_IGNORE
            );

            $_flags |= _CMP_MODE_ARRAY_VS_IGNORE;
        }

        $sum = (int) (
            ((bool) ($_flags & _CMP_MODE_NULL_SPACESHIP))
            + ((bool) ($_flags & _CMP_MODE_NULL_0))
            + ((bool) ($_flags & _CMP_MODE_NULL_GT))
            + ((bool) ($_flags & _CMP_MODE_NULL_LT))
            + ((bool) ($_flags & _CMP_MODE_NULL_NAN))
        );
        if (1 !== $sum) {
            $_flags &= ~(
                _CMP_MODE_NULL_SPACESHIP
                | _CMP_MODE_NULL_0
                | _CMP_MODE_NULL_GT
                | _CMP_MODE_NULL_LT
                | _CMP_MODE_NULL_NAN
            );

            $_flags |= _CMP_MODE_NULL_NAN;
        }

        // $keys = [
        //     '_CMP_MODE_ERROR_THROW',
        //     '_CMP_MODE_ERROR_NAN',
        //     '_CMP_MODE_TYPE_STRICT',
        //     '_CMP_MODE_TYPE_SPACESHIP',
        //     '_CMP_MODE_TYPE_GT',
        //     '_CMP_MODE_TYPE_LT',
        //     '_CMP_MODE_TYPE_CONTINUE',
        //     '_CMP_MODE_TYPE_NAN',
        //     '_CMP_MODE_STRING_VS_STRCMP',
        //     '_CMP_MODE_STRING_VS_STRCASECMP',
        //     '_CMP_MODE_STRING_VS_STRNATCMP',
        //     '_CMP_MODE_STRING_VS_STRNATCASECMP',
        //     '_CMP_MODE_STRING_VS_SPACESHIP',
        //     '_CMP_MODE_STRING_VS_IGNORE',
        //     '_CMP_MODE_STRING_SIZE_LENGTH',
        //     '_CMP_MODE_STRING_SIZE_STRLEN',
        //     '_CMP_MODE_STRING_SIZE_IGNORE',
        //     '_CMP_MODE_ARRAY_SIZE_COUNT',
        //     '_CMP_MODE_ARRAY_SIZE_IGNORE',
        //     '_CMP_MODE_ARRAY_VS_SPACESHIP',
        //     '_CMP_MODE_ARRAY_VS_IGNORE',
        //     '_CMP_MODE_NULL_SPACESHIP',
        //     '_CMP_MODE_NULL_0',
        //     '_CMP_MODE_NULL_GT',
        //     '_CMP_MODE_NULL_LT',
        //     '_CMP_MODE_NULL_NAN',
        // ];
        //
        // $tFlags = array_reverse(str_split(str_pad(decbin($flags), count($keys), '0', STR_PAD_LEFT)));
        // $tFlagsNew = array_reverse(str_split(decbin($_flags), 1));
        // $tFlags = array_combine($keys, $tFlags);
        // $tFlagsNew = array_combine($keys, $tFlagsNew);
        // dd($tFlags, $tFlagsNew);

        return $_flags;
    }
}
