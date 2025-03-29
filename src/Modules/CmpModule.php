<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;


if (! defined('_CMP_MODE_ERROR_THROW')) define('_CMP_MODE_ERROR_THROW', 1 << 0);
if (! defined('_CMP_MODE_ERROR_NAN')) define('_CMP_MODE_ERROR_NAN', 1 << 1);
if (! defined('_CMP_MODE_STRICT')) define('_CMP_MODE_STRICT', 1 << 2);
if (! defined('_CMP_MODE_STRINGS_VS_STRCMP')) define('_CMP_MODE_STRINGS_VS_STRCMP', 1 << 3);
if (! defined('_CMP_MODE_STRINGS_VS_STRCASECMP')) define('_CMP_MODE_STRINGS_VS_STRCASECMP', 1 << 4);
if (! defined('_CMP_MODE_STRINGS_VS_STRNATCMP')) define('_CMP_MODE_STRINGS_VS_STRNATCMP', 1 << 5);
if (! defined('_CMP_MODE_STRINGS_VS_STRNATCASECMP')) define('_CMP_MODE_STRINGS_VS_STRNATCASECMP', 1 << 6);
if (! defined('_CMP_MODE_STRINGS_VS_SPACESHIP')) define('_CMP_MODE_STRINGS_VS_SPACESHIP', 1 << 7);
if (! defined('_CMP_MODE_STRINGS_VS_NOTHING')) define('_CMP_MODE_STRINGS_VS_NOTHING', 1 << 8);
if (! defined('_CMP_MODE_STRINGS_SIZE_LENGTH')) define('_CMP_MODE_STRINGS_SIZE_LENGTH', 1 << 9);
if (! defined('_CMP_MODE_STRINGS_SIZE_STRLEN')) define('_CMP_MODE_STRINGS_SIZE_STRLEN', 1 << 10);
if (! defined('_CMP_MODE_STRINGS_SIZE_NOTHING')) define('_CMP_MODE_STRINGS_SIZE_NOTHING', 1 << 11);
if (! defined('_CMP_MODE_ARRAYS_SIZE_COUNT')) define('_CMP_MODE_ARRAYS_SIZE_COUNT', 1 << 12);
if (! defined('_CMP_MODE_ARRAYS_SIZE_NOTHING')) define('_CMP_MODE_ARRAYS_SIZE_NOTHING', 1 << 13);
if (! defined('_CMP_MODE_ARRAYS_VS_SPACESHIP')) define('_CMP_MODE_ARRAYS_VS_SPACESHIP', 1 << 14);
if (! defined('_CMP_MODE_ARRAYS_VS_NOTHING')) define('_CMP_MODE_ARRAYS_VS_NOTHING', 1 << 15);

class CmpModule
{
    public function same($a, $b, int $flags = null) : bool
    {
        $isStrict = ($flags & _CMP_MODE_STRICT);

        $result = $this->compare($a, $b, $flags);

        if (is_float($result) && is_nan($result)) {
            return $isStrict
                ? ($a === $b)
                : ($a == $b);
        }

        return $result === 0;
    }

    public function different($a, $b, int $flags = null) : bool
    {
        $isStrict = ($flags & _CMP_MODE_STRICT);

        $result = $this->compare($a, $b, $flags);

        if (is_float($result) && is_nan($result)) {
            return $isStrict
                ? ($a !== $b)
                : ($a != $b);
        }

        return $result !== 0;
    }


    /**
     * @return int|float
     */
    public function compare($a, $b, int $flags = null) // : int|float
    {
        $result = null
            ?? $this->cmpNan($a, $b, $flags)
            ?? $this->cmpNull($a, $b, $flags)
            ?? $this->cmpNil($a, $b, $flags)
            ?? $this->cmpBoolean($a, $b, $flags)
            ?? $this->cmpNumeric($a, $b, $flags)
            ?? $this->cmpString($a, $b, $flags)
            ?? $this->cmpArray($a, $b, $flags)
            ?? $this->cmpObject($a, $b, $flags)
            ?? $this->cmpResource($a, $b, $flags);

        if (null === $result) {
            return ($a <=> $b);
        }

        return $result;
    }


    /**
     * @param int|null $flags
     *
     * @return null|int|float
     */
    public function cmpNan($a, $b, int $flags = null) // : null|int|NAN
    {
        if ($isStrict = ($flags & _CMP_MODE_STRICT)) {
            if (gettype($a) !== gettype($b)) {
                return $this->cmpError($a, $b, $flags);
            }
        }

        $isNanA = is_float($a) && is_nan($a);
        $isNanB = is_float($b) && is_nan($b);

        if ($isNanA || $isNanB) {
            return $this->cmpError($a, $b, $flags);
        }

        return null;
    }

    /**
     * @param int|null $flags
     *
     * @return null|int|float
     */
    public function cmpNull($a, $b, int $flags = null) // : null|int|NAN
    {
        if ($isStrict = ($flags & _CMP_MODE_STRICT)) {
            if (gettype($a) !== gettype($b)) {
                return $this->cmpError($a, $b, $flags);
            }
        }

        $isNullA = ($a === null);
        $isNullB = ($b === null);

        if ($isNullA && $isNullB) {
            return 0;

        } elseif ($isNullA || $isNullB) {
            $theType = Lib::type();

            $aStatus = $isNullA;
            $bStatus = $isNullB;
            if (! $aStatus) $aStatus = $theType->is_blank($a);
            if (! $bStatus) $bStatus = $theType->is_blank($b);

            if ($aStatus && $bStatus) {
                return 0;
            }

            return $this->cmpError($a, $b, $flags);
        }

        return null;
    }

    /**
     * @param int|null $flags
     *
     * @return null|int|float
     */
    public function cmpNil($a, $b, int $flags = null) // : null|int|NAN
    {
        if ($isStrict = ($flags & _CMP_MODE_STRICT)) {
            if (gettype($a) !== gettype($b)) {
                return $this->cmpError($a, $b, $flags);
            }
        }

        $theType = Lib::type();

        $isNilA = $theType->is_nil($a);
        $isNilB = $theType->is_nil($b);

        if ($isNilA && $isNilB) {
            return 0;

        } elseif ($isNilA || $isNilB) {
            return $this->cmpError($a, $b, $flags);
        }

        return null;
    }

    /**
     * @param int|null $flags
     *
     * @return null|int|float
     */
    public function cmpBoolean($a, $b, int $flags = null) // : null|int|NAN
    {
        if ($isStrict = ($flags & _CMP_MODE_STRICT)) {
            if (gettype($a) !== gettype($b)) {
                return $this->cmpError($a, $b, $flags);
            }
        }

        $isBoolA = is_bool($a);
        $isBoolB = is_bool($b);

        if ($isBoolA && $isBoolB) {
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

                return $aBool <=> $bBool;
            }

            return $this->cmpError($a, $b, $flags);
        }

        return null;
    }

    /**
     * @param int|null $flags
     *
     * @return null|int|float
     */
    public function cmpInteger($a, $b, int $flags = null) // : null|int|NAN
    {
        if ($isStrict = ($flags & _CMP_MODE_STRICT)) {
            if (gettype($a) !== gettype($b)) {
                return $this->cmpError($a, $b, $flags);
            }
        }

        $isIntA = is_int($a);
        $isIntB = is_int($b);

        if ($isIntA && $isIntB) {
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

                return $aInt <=> $bInt;
            }

            // > removed, one of values may be string
            // return $this->cmpError($a, $b, $flags);
        }

        return null;
    }

    /**
     * @param int|null $flags
     *
     * @return null|int|float
     */
    public function cmpFloat($a, $b, int $flags = null) // : null|int|NAN
    {
        if ($isStrict = ($flags & _CMP_MODE_STRICT)) {
            if (gettype($a) !== gettype($b)) {
                return $this->cmpError($a, $b, $flags);
            }
        }

        $isFloatA = is_float($a);
        $isFloatB = is_float($b);

        $isNanA = $isFloatA && is_nan($a);
        $isNanB = $isFloatB && is_nan($b);

        if ($isNanA || $isNanB) {
            return $this->cmpError($a, $b, $flags);
        }

        if ($isFloatA && $isFloatB) {
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

                return $aFloat <=> $bFloat;
            }

            // > removed, one of values may be string
            // return $this->cmpError($a, $b, $flags);
        }

        return null;
    }

    /**
     * @param int|null $flags
     *
     * @return null|int|float
     */
    public function cmpNumeric($a, $b, int $flags = null) // : null|int|NAN
    {
        if ($isStrict = ($flags & _CMP_MODE_STRICT)) {
            if (gettype($a) !== gettype($b)) {
                return $this->cmpError($a, $b, $flags);
            }
        }

        $isNumericA = is_numeric($a);
        $isNumericB = is_numeric($b);

        $isNanA = $isNumericA && is_nan($a);
        $isNanB = $isNumericB && is_nan($b);

        if ($isNanA || $isNanB) {
            return $this->cmpError($a, $b, $flags);
        }

        if ($isNumericA || $isNumericB) {
            $theType = Lib::type();

            $aStatus = $isNumericA;
            $bStatus = $isNumericB;
            if (! $aStatus) $aStatus = $theType->int($aInt, $a);
            if (! $bStatus) $bStatus = $theType->int($bInt, $b);

            if ($aStatus && $bStatus) {
                $aInt = $aInt ?? $a;
                $bInt = $bInt ?? $b;

                return $aInt <=> $bInt;
            }

            $aStatus = $isNumericA;
            $bStatus = $isNumericB;
            if (! $aStatus) $aStatus = $theType->num($aInt, $a);
            if (! $bStatus) $bStatus = $theType->num($bInt, $b);

            if ($aStatus && $bStatus) {
                $aFloat = floatval($aNum ?? $a);
                $bFloat = floatval($bNum ?? $b);

                return $aFloat <=> $bFloat;
            }

            // > removed, one of values may be string
            // return $this->cmpError($a, $b, $flags);
        }

        return null;
    }

    /**
     * @param int|null $flags
     *
     * @return null|int|float
     */
    public function cmpString($a, $b, int $flags = null) // : null|int|NAN
    {
        if ($isStrict = ($flags & _CMP_MODE_STRICT)) {
            if (gettype($a) !== gettype($b)) {
                return $this->cmpError($a, $b, $flags);
            }
        }

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
                    return 0;
                }

                $result = null;

                $isSizeLength = ($flags & _CMP_MODE_STRINGS_SIZE_LENGTH);
                $isSizeStrlen = ($flags & _CMP_MODE_STRINGS_SIZE_STRLEN);
                $isSizeNothing = ($flags & _CMP_MODE_STRINGS_SIZE_NOTHING);
                if (! (
                    $isSizeLength
                    || $isSizeStrlen
                    || $isSizeNothing
                )) {
                    $isSizeLength = true;
                }

                if ($isSizeLength) {
                    $theStr = Lib::str();

                    $aStringLen = $theStr->strlen($aString);
                    $bStringLen = $theStr->strlen($bString);

                    $result = ($aStringLen <=> $bStringLen);

                } elseif ($isSizeStrlen) {
                    $result = (strlen($a) <=> strlen($b));

                } elseif ($isSizeNothing) {
                    $result = 0;
                }

                if (0 === $result) {
                    // > if same or doesnt matter, then continue
                    $result = null;
                }

                if (null !== $result) {
                    return $result;
                }

                $isVsStrnatcasecmp = ($flags & _CMP_MODE_STRINGS_VS_STRNATCASECMP);
                $isVsStrcasecmp = ($flags & _CMP_MODE_STRINGS_VS_STRCASECMP);
                $isVsStrnatcmp = ($flags & _CMP_MODE_STRINGS_VS_STRNATCMP);
                $isVsStrcmp = ($flags & _CMP_MODE_STRINGS_VS_STRCMP);
                $isVsSpaceship = ($flags & _CMP_MODE_STRINGS_VS_SPACESHIP);
                $isVsNothing = ($flags & _CMP_MODE_STRINGS_VS_NOTHING);
                if (! (
                    $isVsStrnatcasecmp
                    || $isVsStrcasecmp
                    || $isVsStrnatcmp
                    || $isVsStrcmp
                    || $isVsSpaceship
                    || $isVsNothing
                )) {
                    $isVsSpaceship = true;
                }

                $result = null
                    ?? (! empty($isVsStrnatcasecmp) ? strnatcasecmp($aString, $bString) : null)
                    ?? (! empty($isVsStrcasecmp) ? strcasecmp($aString, $bString) : null)
                    ?? (! empty($isVsStrnatcmp) ? strnatcmp($aString, $bString) : null)
                    ?? (! empty($isVsStrcmp) ? strcmp($aString, $bString) : null)
                    ?? (! empty($isVsSpaceship) ? ($aString <=> $bString) : null)
                    ?? (! empty($isVsNothing) ? NAN : null);

                if (null !== $result) {
                    return $result;
                }
            }

            return $this->cmpError($a, $b, $flags);
        }

        return null;
    }

    /**
     * @param int|null $flags
     *
     * @return null|int|float
     */
    public function cmpArray($a, $b, int $flags = null) // int|NAN
    {
        if ($isStrict = ($flags & _CMP_MODE_STRICT)) {
            if (gettype($a) !== gettype($b)) {
                return $this->cmpError($a, $b, $flags);
            }
        }

        $isArrayA = is_array($a);
        $isArrayB = is_array($b);

        if ($isArrayA && $isArrayB) {
            $result = null;

            $isSizeLength = ($flags & _CMP_MODE_ARRAYS_SIZE_COUNT);
            $isSizeNothing = ($flags & _CMP_MODE_STRINGS_SIZE_NOTHING);
            if (! ($isSizeLength || $isSizeNothing)) {
                $isSizeLength = true;
            }

            if ($isSizeLength) {
                $theStr = Lib::str();

                $aArrayCnt = count($a);
                $bArrayCnt = count($b);

                $result = ($aArrayCnt <=> $bArrayCnt);

            } elseif ($isSizeNothing) {
                $result = 0;
            }

            if (0 === $result) {
                $result = null;
            }

            if (null !== $result) {
                return $result;
            }

            $isVsSpaceship = ($flags & _CMP_MODE_ARRAYS_VS_SPACESHIP);
            $isVsNothing = ($flags & _CMP_MODE_ARRAYS_VS_NOTHING);
            if (! ($isVsSpaceship || $isVsNothing)) {
                $isVsNothing = true;
            }

            $result = null
                ?? (! empty($isVsSpaceship) ? ($a <=> $b) : null)
                ?? (! empty($isVsNothing) ? 0 : null);

            if (0 === $result) {
                $result = null;
            }

            if (null !== $result) {
                return $result;
            }

            if ($a === $b) {
                return 0;
            }

            return $this->cmpError($a, $b, $flags);

        } elseif ($isArrayA || $isArrayB) {
            return $this->cmpError($a, $b, $flags);
        }

        return null;
    }

    /**
     * @param int|null $flags
     *
     * @return null|int|float
     */
    public function cmpObject($a, $b, int $flags = null) // int|NAN
    {
        if ($isStrict = ($flags & _CMP_MODE_STRICT)) {
            if (gettype($a) !== gettype($b)) {
                return $this->cmpError($a, $b, $flags);
            }
        }

        $isObjectA = is_object($a);
        $isObjectB = is_object($b);

        if ($isObjectA && $isObjectB) {
            if ($a === $b) {
                return 0;
            }

            $isDateA = $a instanceof \DateTimeInterface;
            $isDateB = $b instanceof \DateTimeInterface;

            if ($isDateA && $isDateB) {
                return $a <=> $b;

            } else {
                $theType = Lib::type();

                $aStatus = $isDateA;
                $bStatus = $isDateB;
                if (! $aStatus) $aStatus = $theType->date_interface($aDate, $a);
                if (! $bStatus) $bStatus = $theType->date_interface($bDate, $b);

                if ($aStatus && $bStatus) {
                    $aDate = $aDate ?? $a;
                    $bDate = $bDate ?? $b;

                    return $aDate <=> $bDate;
                }

                return $this->cmpError($a, $b, $flags);
            }

        } elseif ($isObjectA || $isObjectB) {
            return $this->cmpError($a, $b, $flags);
        }

        return null;
    }

    /**
     * @param int|null $flags
     *
     * @return null|int|float
     */
    public function cmpResource($a, $b, int $flags = null) // int|NAN
    {
        if ($isStrict = ($flags & _CMP_MODE_STRICT)) {
            if (gettype($a) !== gettype($b)) {
                return $this->cmpError($a, $b, $flags);
            }
        }

        $theType = Lib::type();

        $isResourceA = $theType->resource($aResource, $a);
        $isResourceB = $theType->resource($bResource, $b);

        if ($isResourceA && $isResourceB) {
            $isResourceClosedA = ! is_resource($aResource);
            $isResourceClosedB = ! is_resource($bResource);

            if (
                ($isResourceClosedA && $isResourceClosedB)
                || ! ($isResourceClosedA || $isResourceClosedB)
            ) {
                if ($a === $b) {
                    return 0;
                }
            }

            return $this->cmpError($a, $b, $flags);

        } elseif ($isResourceA || $isResourceB) {
            return $this->cmpError($a, $b, $flags);
        }

        return null;
    }


    protected function cmpError($a, $b, int $flags = null) : float
    {
        $isErrorThrow = ($flags & _CMP_MODE_ERROR_THROW);
        $isErrorNan = ($flags & _CMP_MODE_ERROR_NAN);
        if (! ($isErrorThrow || $isErrorNan)) {
            $isErrorThrow = true;
        }

        if ($isErrorThrow) {
            throw new RuntimeException(
                [ 'Values are incomparable', $a, $b ]
            );
        }

        return NAN;
    }
}
