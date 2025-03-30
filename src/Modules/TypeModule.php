<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Crypt\Alphabet;
use Gzhegow\Lib\Modules\Bcmath\Bcnumber;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Modules\Type\Base\TypeModuleBase;


class TypeModule extends TypeModuleBase
{
    /**
     * @param bool|null $result
     */
    public function bool(&$result, $value) : bool
    {
        $result = null;

        if (is_bool($value)) {
            $result = $value;

            return true;
        }

        if (null === $value) {
            // > NULL is not bool

            return false;
        }

        if ('0' === $value) {
            // > '0' is true
            $result = true;

            return true;
        }

        if (is_float($value) && is_nan($value)) {
            // > NAN is not bool
            return false;
        }

        if (Lib::type()->is_nil($value)) {
            // > NIL is not bool
            return false;
        }

        if (0 === ($cnt = Lib::php()->count($value))) {
            // > EMPTY COUNTABLE is false

            $result = false;

            return true;
        }

        if ('resource (closed)' === gettype($value)) {
            // > CLOSED RESOURCE is false

            $result = false;

            return true;
        }

        $result = (bool) $value;

        return true;
    }

    /**
     * @param bool|null $result
     */
    public function userbool(&$result, $value) : bool
    {
        $result = null;

        if (is_bool($value)) {
            $result = $value;

            return true;
        }

        if (null === $value) {
            // > NULL is not bool

            return false;
        }

        if ('0' === $value) {
            // > '0' is true

            $result = true;

            return true;
        }

        if (is_float($value) && is_nan($value)) {
            // > NAN is not bool
            return false;
        }

        if (is_string($value)) {
            $_value = strtolower($value);

            if (
                ($_value === 'true')
                || ($_value === 'y')
                || ($_value === 'yes')
                || ($_value === 'on')
            ) {
                $result = true;

                return true;

            } elseif (
                ($_value === 'false')
                || ($_value === 'n')
                || ($_value === 'no')
                || ($_value === 'off')
            ) {
                $result = false;

                return true;
            }
        }

        if (Lib::type()->is_nil($value)) {
            // > NIL is not bool
            return false;
        }

        if (0 === ($cnt = Lib::php()->count($value))) {
            // > EMPTY COUNTABLE is false

            $result = false;

            return true;
        }

        if ('resource (closed)' === gettype($value)) {
            // > CLOSED RESOURCE is false

            $result = false;

            return true;
        }

        $result = (bool) $value;

        return true;
    }


    /**
     * @param int|null $result
     */
    public function int(&$result, $value) : bool
    {
        $result = null;

        if (! $this->num($_value, $value)) {
            return false;
        }

        if (! is_int($_value)) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param int|null $result
     */
    public function int_non_zero(&$result, $value) : bool
    {
        $result = null;

        if (! $this->int($_value, $value)) {
            return false;
        }

        if ($_value === 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param int|null $result
     */
    public function int_non_negative(&$result, $value) : bool
    {
        $result = null;

        if (! $this->int($_value, $value)) {
            return false;
        }

        if ($_value < 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param int|null $result
     */
    public function int_non_positive(&$result, $value) : bool
    {
        $result = null;

        if (! $this->int($_value, $value)) {
            return false;
        }

        if ($_value > 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param int|null $result
     */
    public function int_negative(&$result, $value) : bool
    {
        $result = null;

        if (! $this->int($_value, $value)) {
            return false;
        }

        if ($_value >= 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param int|null $result
     */
    public function int_positive(&$result, $value) : bool
    {
        $result = false;

        if (! $this->int($_value, $value)) {
            return false;
        }

        if ($_value <= 0) {
            return false;
        }

        $result = $_value;

        return true;
    }


    /**
     * @param int|float|null $result
     */
    public function num(&$result, $value) : bool
    {
        $result = null;

        if (is_int($value)) {
            $result = $value;

            return true;
        }

        if (is_float($value)) {
            if (! is_finite($value)) {
                return false;

            } else {
                $result = $value;

                return true;
            }
        }

        if (! is_numeric($value)) {
            return false;
        }

        $valueString = (string) $value;

        // > IEEE 754 double-precision floating point (64-bit float)
        $valueFloat = floatval(sprintf('%.17g', $valueString));

        if (! is_finite($valueFloat)) {
            return false;
        }

        if (($valueFloat < -PHP_INT_MAX) || (PHP_INT_MAX < $valueFloat)) {
            $result = $valueFloat;

            return true;
        }

        $valueInt = (int) $valueFloat;

        if ($valueFloat === (float) $valueInt) {
            $result = $valueInt;

            return true;
        }

        $result = $valueFloat;

        return true;
    }

    /**
     * @param int|float|null $result
     */
    public function num_non_zero(&$result, $value) : bool
    {
        $result = null;

        if (! $this->num($_value, $value)) {
            return false;
        }

        if ($_value == 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param int|float|null $result
     */
    public function num_non_negative(&$result, $value) : bool
    {
        $result = null;

        if (! $this->num($_value, $value)) {
            return false;
        }

        if ($_value < 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param int|float|null $result
     */
    public function num_non_positive(&$result, $value) : bool
    {
        $result = null;

        if (! $this->num($_value, $value)) {
            return false;
        }

        if ($_value > 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param int|float|null $result
     */
    public function num_negative(&$result, $value) : bool
    {
        $result = null;

        if (! $this->num($_value, $value)) {
            return false;
        }

        if ($_value >= 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param int|float|null $result
     */
    public function num_positive(&$result, $value) : bool
    {
        $result = null;

        if (! $this->num($_value, $value)) {
            return false;
        }

        if ($_value <= 0) {
            return false;
        }

        $result = $_value;

        return true;
    }


    /**
     * @param string|null $result
     */
    public function numeric_int(&$result, $value) : bool
    {
        $result = null;

        if (! $this->numeric($_value, $value)) {
            return false;
        }

        $valueNoMinus = ltrim($_value, '-');

        if (! $this->ctype_digit($var, $valueNoMinus)) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function numeric_int_non_zero(&$result, $value) : bool
    {
        $result = null;

        if (! $this->numeric_int($_value, $value)) {
            return false;
        }

        if ($_value == 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function numeric_int_non_negative(&$result, $value) : bool
    {
        $result = null;

        if (! $this->numeric_int($_value, $value)) {
            return false;
        }

        if ($_value == 0) {
            $result = '0';

            return true;
        }

        if ('-' === $_value[ 0 ]) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function numeric_int_non_positive(&$result, $value) : bool
    {
        $result = null;

        if (! $this->numeric_int($_value, $value)) {
            return false;
        }

        if ($_value == 0) {
            $result = '0';

            return true;
        }

        if ('-' === $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function numeric_int_negative(&$result, $value) : bool
    {
        $result = null;

        if (! $this->numeric_int($_value, $value)) {
            return false;
        }

        if ($_value == 0) {
            return false;
        }

        if ('-' === $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function numeric_int_positive(&$result, $value) : bool
    {
        $result = null;

        if (! $this->numeric_int($_value, $value)) {
            return false;
        }

        if ($_value == 0) {
            return false;
        }

        if ('-' === $_value[ 0 ]) {
            return false;
        }

        $result = $_value;

        return true;
    }


    /**
     * @param string|null $result
     */
    public function numeric(&$result, $value) : bool
    {
        $result = null;

        if (! is_numeric($value)) {
            return false;
        }

        $valueString = (string) $value;

        // > IEEE 754 double-precision floating point (64-bit float)
        $valueFloat = floatval(sprintf('%.17g', $valueString));

        if (! is_finite($valueFloat)) {
            return false;
        }

        $result = $valueString;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function numeric_non_zero(&$result, $value) : bool
    {
        $result = null;

        if (! $this->numeric($_value, $value)) {
            return false;
        }

        if ($_value == 0) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function numeric_non_negative(&$result, $value) : bool
    {
        $result = null;

        if (! $this->numeric($_value, $value)) {
            return false;
        }

        if ($_value == 0) {
            $result = '0';

            return true;
        }

        if ('-' === $_value[ 0 ]) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function numeric_non_positive(&$result, $value) : bool
    {
        $result = null;

        if (! $this->numeric($_value, $value)) {
            return false;
        }

        if ($_value == 0) {
            $result = '0';

            return true;
        }

        if ('-' === $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function numeric_negative(&$result, $value) : bool
    {
        $result = null;

        if (! $this->numeric($_value, $value)) {
            return false;
        }

        if ($_value == 0) {
            return false;
        }

        if ('-' === $_value[ 0 ]) {
            $result = $_value;

            return true;
        }

        return false;
    }

    /**
     * @param string|null $result
     */
    public function numeric_positive(&$result, $value) : bool
    {
        $result = null;

        if (! $this->numeric($_value, $value)) {
            return false;
        }

        if ($_value == 0) {
            return false;
        }

        if ('-' === $_value[ 0 ]) {
            return false;
        }

        $result = $_value;

        return true;
    }


    /**
     * @param Bcnumber|null $result
     */
    public function bcnum(&$result, $value) : bool
    {
        return Lib::bcmath()->type_bcnum($result, $value);
    }


    /**
     * @param string|null $result
     */
    public function string(&$result, $value, bool $removeNanInf = null) : bool
    {
        $result = null;

        $removeNanInf = $removeNanInf ?? false;

        $isString = is_string($value);

        if (! $isString
            && (
                (null === $value)
                || (is_bool($value))
                || (is_array($value))
                || (is_float($value) && ! is_finite($value))
                || (is_resource($value))
                || ('resource (closed)' === gettype($value))
            )
        ) {
            return false;
        }

        $_value = null;

        if (is_string($value)) {
            $_value = $value;

        } elseif (is_object($value)) {
            if (method_exists($value, '__toString')) {
                $_value = (string) $value;
            }

        } else {
            $settype = $value;
            $status = settype($settype, 'string');
            if ($status) {
                $_value = $settype;
            }
        }

        if (null === $_value) {
            return false;
        }

        if ($removeNanInf) {
            if (is_numeric($_value)) {
                // > IEEE 754 double-precision floating point (64-bit float)
                $valueFloat = floatval(sprintf('%.17g', $_value));

                if (! is_finite($valueFloat)) {
                    return false;
                }
            }
        }

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function string_not_empty(&$result, $value, bool $removeNanInf = null) : bool
    {
        $result = null;

        if (! $this->string($_value, $value, $removeNanInf)) {
            return false;
        }

        if ('' === $_value) {
            return false;
        }

        $result = $_value;

        return true;
    }


    /**
     * @param string|null $result
     */
    public function trim(&$result, $value, string $characters = null, bool $removeNanInf = null) : bool
    {
        return Lib::str()->type_trim($result, $value, $characters, $removeNanInf);
    }


    /**
     * @param string|null $result
     */
    public function letter(&$result, $value) : bool
    {
        return Lib::str()->type_letter($result, $value);
    }

    /**
     * @param Alphabet|null $result
     */
    public function alphabet(&$result, $value) : bool
    {
        return Lib::crypt()->type_alphabet($result, $value);
    }


    /**
     * @param string|null $result
     */
    public function ctype_digit(&$result, $value) : bool
    {
        $result = null;

        if (! $this->string_not_empty($_value, $value)) {
            return false;
        }

        if (extension_loaded('ctype')) {
            if (ctype_digit($_value)) {
                $result = $_value;

                return true;
            }

            return false;
        }

        if (! preg_match('~[^0-9]~', $_value)) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function ctype_alpha(&$result, $value, bool $ignoreCase = null) : bool
    {
        $result = null;

        $ignoreCase = $ignoreCase ?? true;

        if (! $this->string_not_empty($_value, $value)) {
            return false;
        }

        if (extension_loaded('ctype')) {
            if (! $ignoreCase) {
                if (strtolower($_value) !== $_value) {
                    return false;
                }
            }

            if (ctype_alpha($_value)) {
                $result = $_value;

                return true;
            }

            return false;
        }

        $regexFlags = $ignoreCase
            ? 'i'
            : '';

        if (preg_match('~[^a-z]~' . $regexFlags, $_value)) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function ctype_alnum(&$result, $value, bool $ignoreCase = null) : bool
    {
        $result = null;

        $ignoreCase = $ignoreCase ?? true;

        if (! $this->string_not_empty($_value, $value)) {
            return false;
        }

        if (extension_loaded('ctype')) {
            if (! $ignoreCase) {
                if (strtolower($_value) !== $_value) {
                    return false;
                }
            }

            if (ctype_alnum($_value)) {
                $result = $_value;

                return true;
            }

            return false;
        }

        $regexFlags = $ignoreCase
            ? 'i'
            : '';

        if (preg_match('~[^0-9a-z]~' . $regexFlags, $_value)) {
            return false;
        }

        $result = $_value;

        return true;
    }


    /**
     * @param string|null $result
     */
    public function base(&$result, $value, $alphabet) : bool
    {
        return Lib::crypt()->type_base($result, $value, $alphabet);
    }

    /**
     * @param string|null $result
     */
    public function base_bin(&$result, $value) : bool
    {
        return Lib::crypt()->type_base_bin($result, $value);
    }

    /**
     * @param string|null $result
     */
    public function base_oct(&$result, $value) : bool
    {
        return Lib::crypt()->type_base_oct($result, $value);
    }

    /**
     * @param string|null $result
     */
    public function base_dec(&$result, $value) : bool
    {
        return Lib::crypt()->type_base_dec($result, $value);
    }

    /**
     * @param string|null $result
     */
    public function base_hex(&$result, $value) : bool
    {
        return Lib::crypt()->type_base_hex($result, $value);
    }


    /**
     * @param array|null $result
     */
    public function list(&$result, $value) : bool
    {
        return Lib::arr()->type_list($result, $value);
    }

    /**
     * @param array|null $result
     */
    public function list_sorted(&$result, $value) : bool
    {
        return Lib::arr()->type_list_sorted($result, $value);
    }


    /**
     * @param array|null $result
     */
    public function dict(&$result, $value) : bool
    {
        return Lib::arr()->type_dict($result, $value);
    }

    /**
     * @param array|null $result
     */
    public function dict_sorted(&$result, $value) : bool
    {
        return Lib::arr()->type_dict_sorted($result, $value);
    }


    /**
     * @param array|null $result
     */
    public function index_list(&$result, $value) : bool
    {
        return Lib::arr()->type_index_list($result, $value);
    }

    /**
     * @param array|null $result
     */
    public function index_dict(&$result, $value) : bool
    {
        return Lib::arr()->type_index_dict($result, $value);
    }


    /**
     * @param array|null $result
     */
    public function table(&$result, $value) : bool
    {
        return Lib::arr()->type_table($result, $value);
    }

    /**
     * @param array|null $result
     */
    public function matrix(&$result, $value) : bool
    {
        return Lib::arr()->type_matrix($result, $value);
    }

    /**
     * @param array|null $result
     */
    public function matrix_strict(&$result, $value) : bool
    {
        return Lib::arr()->type_matrix_strict($result, $value);
    }


    /**
     * @param string|null $result
     */
    public function regex(&$result, $value) : bool
    {
        $result = null;

        if (! $this->string_not_empty($_value, $value)) {
            return false;
        }

        error_clear_last();

        try {
            $status = preg_match($_value, '');
        }
        catch ( \Throwable $e ) {
            return false;
        }

        if (error_get_last()) {
            return false;
        }

        if (false === $status) {
            return false;
        }

        $result = $_value;

        return true;
    }


    /**
     * @param string|null $result
     */
    public function ip(&$result, $value) : bool
    {
        return Lib::net()->type_ip($result, $value);
    }


    /**
     * @param string|null       $result
     * @param string            $value
     * @param string|array|null $query
     * @param string|null       $fragment
     */
    public function url(
        &$result,
        $value, $query = null, $fragment = null,
        array $refs = []
    ) : bool
    {
        return Lib::url()->type_url($result, $value, $query, $fragment, $refs);
    }

    /**
     * @param string|null $result
     * @param string      $value
     */
    public function host(
        &$result,
        $value,
        array $refs = []
    ) : bool
    {
        return Lib::url()->type_host($result, $value, $refs);
    }

    /**
     * @param string|null       $result
     * @param string            $value
     * @param string|array|null $query
     * @param string|null       $fragment
     */
    public function link(
        &$result,
        $value, $query = null, $fragment = null,
        array $refs = []
    ) : bool
    {
        return Lib::url()->type_link($result, $value, $query, $fragment, $refs);
    }


    /**
     * @param string|null $result
     */
    public function uuid(&$result, $value) : bool
    {
        return Lib::random()->type_uuid($result, $value);
    }


    /**
     * @param \DateTimeInterface|null   $result
     *
     * @param string|\DateTimeZone|null $timezoneIfParsed
     * @param string|string[]|null      $formats
     */
    public function date_interface(&$result, $value, $timezoneIfParsed = null, $formats = null) : bool
    {
        $result = null;

        if ($value instanceof \DateTimeInterface) {
            $result = $value;

            return true;
        }

        if ($this->date($date, $value, $timezoneIfParsed, $formats)) {
            $result = $date;

            return true;
        }

        return false;
    }

    /**
     * @param \DateTime|null            $result
     *
     * @param string|\DateTimeZone|null $timezoneIfParsed
     * @param string|string[]|null      $formats
     */
    public function date(&$result, $value, $timezoneIfParsed = null, $formats = null) : bool
    {
        $result = null;

        $hasTimezoneIfParsed = (null !== $timezoneIfParsed);
        $hasFormats = (null !== $formats);

        $_timezoneIfParsed = null;
        if ($hasTimezoneIfParsed) {
            if (! $this->timezone($_timezoneIfParsed, $timezoneIfParsed)) {
                throw new LogicException(
                    [ 'The `timezoneIfParsed` should be null or valid \DateTimeZone', $timezoneIfParsed ]
                );
            }
        }

        if ($value instanceof \DateTime) {
            $result = $value;

            return true;

        } elseif ($value instanceof \DateTimeImmutable) {
            $date = \DateTime::createFromImmutable($value);

            $result = $date;

            return true;
        }

        if ($hasFormats) {
            $_formats = Lib::php()->to_list($formats);

            foreach ( $_formats as $i => $format ) {
                if (! (is_string($format) && ('' !== $format))) {
                    throw new LogicException(
                        [
                            'Each of `formats` should be non-empty string',
                            $format,
                            $i,
                        ]
                    );
                }
            }

        } else {
            if ('' === $value) {
                // > strtotime(''); // OK, what?

                return false;
            }

            if (is_numeric($value)) {
                // > strtotime(0); // FALSE
                // > strtotime(1); // FALSE
                // > strtotime(1.0); // FALSE
                // > strtotime('0'); // FALSE
                // > strtotime('1'); // FALSE

                // > strtotime(1.1); // OK, what?
                // > strtotime('0.0'); // OK, what?
                // > strtotime('1.0'); // OK, what?
                // > strtotime('1.1'); // OK, what?

                return false;
            }

            if ($this->letter($letter, $value)) {
                // $bAscii = 'b';
                // $bbAscii = 'bb';
                // $bRussian = 'б';
                // $bbRussian = 'бб';
                // > strtotime($bbAscii); // FALSE
                // > strtotime($bRussian); // FALSE
                // > strtotime($bbRussian); // FALSE

                // > strtotime($bAscii); // OK, what?

                return false;
            }
        }

        $date = null;

        if ($hasFormats) {
            foreach ( $_formats as $format ) {
                try {
                    $date = \DateTime::createFromFormat(
                        $format,
                        $value,
                        $_timezoneIfParsed
                    );
                }
                catch ( \Throwable $e ) {
                }

                if ($date) {
                    $result = $date;

                    break;
                }
            }

        } else {
            try {
                $date = new \DateTime($value, $_timezoneIfParsed);

                $result = $date;

                return true;
            }
            catch ( \Throwable $e ) {
            }
        }

        return false;
    }

    /**
     * @param \DateTimeImmutable|null   $result
     *
     * @param string|\DateTimeZone|null $timezoneIfParsed
     * @param string|string[]|null      $formats
     */
    public function date_immutable(&$result, $value, $timezoneIfParsed = null, $formats = null) : bool
    {
        $result = null;

        $hasTimezoneIfParsed = (null !== $timezoneIfParsed);
        $hasFormats = (null !== $formats);

        $_timezoneIfParsed = null;
        if ($hasTimezoneIfParsed) {
            if (! $this->timezone($_timezoneIfParsed, $timezoneIfParsed)) {
                throw new LogicException(
                    [ 'The `timezoneIfParsed` should be null or valid \DateTimeZone', $timezoneIfParsed ]
                );
            }
        }

        if ($value instanceof \DateTimeImmutable) {
            $result = $value;

            return true;

        } elseif ($value instanceof \DateTime) {
            $dateImmutable = \DateTimeImmutable::createFromMutable($value);

            $result = $dateImmutable;

            return true;
        }

        if ($hasFormats) {
            $_formats = Lib::php()->to_list($formats);

            foreach ( $_formats as $i => $format ) {
                if (! (is_string($format) && ('' !== $format))) {
                    throw new LogicException(
                        [
                            'Each of `formats` should be non-empty string',
                            $format,
                            $i,
                        ]
                    );
                }
            }

        } else {
            if ('' === $value) {
                // > strtotime(''); // OK, what?

                return false;
            }

            if (is_numeric($value)) {
                // > strtotime(0); // FALSE
                // > strtotime(1); // FALSE
                // > strtotime(1.0); // FALSE
                // > strtotime('0'); // FALSE
                // > strtotime('1'); // FALSE

                // > strtotime(1.1); // OK, what?
                // > strtotime('0.0'); // OK, what?
                // > strtotime('1.0'); // OK, what?
                // > strtotime('1.1'); // OK, what?

                return false;
            }

            if ($this->letter($letter, $value)) {
                // $bAscii = 'b';
                // $bbAscii = 'bb';
                // $bRussian = 'б';
                // $bbRussian = 'бб';
                // > strtotime($bbAscii); // FALSE
                // > strtotime($bRussian); // FALSE
                // > strtotime($bbRussian); // FALSE

                // > strtotime($bAscii); // OK, what?

                return false;
            }
        }

        $dateImmutable = null;

        if ($hasFormats) {
            foreach ( $_formats as $format ) {
                try {
                    $dateImmutable = \DateTimeImmutable::createFromFormat(
                        $format,
                        $value,
                        $_timezoneIfParsed
                    );
                }
                catch ( \Throwable $e ) {
                }

                if ($dateImmutable) {
                    $result = $dateImmutable;

                    return true;
                }
            }

        } else {
            try {
                $dateImmutable = new \DateTimeImmutable($value, $_timezoneIfParsed);

                $result = $dateImmutable;

                return true;
            }
            catch ( \Throwable $e ) {
            }
        }

        return false;
    }

    /**
     * @param \DateTimeZone|null $result
     */
    public function timezone(&$result, $value) : bool
    {
        $result = null;

        if ($value instanceof \DateTimeZone) {
            $result = $value;

            return true;
        }

        try {
            $timezone = new \DateTimeZone($value);

            $result = $timezone;

            return true;
        }
        catch ( \Throwable $e ) {
        }

        return false;
    }

    /**
     * @param \DateInterval|null $result
     */
    public function interval(&$result, $value) : bool
    {
        $result = null;

        if ($value instanceof \DateInterval) {
            $result = $value;

            return true;
        }

        try {
            $interval = new \DateInterval($value);

            $result = $interval;

            return true;
        }
        catch ( \Throwable $e ) {
        }

        return false;
    }


    /**
     * @param array|\Countable|null $result
     */
    public function countable(&$result, $value) : bool
    {
        return Lib::php()->type_countable($result, $value);
    }


    /**
     * @param resource|null $result
     */
    public function resource(&$result, $value) : bool
    {
        return Lib::php()->type_resource($result, $value);
    }

    /**
     * @param resource|null $result
     */
    public function resource_opened(&$result, $value) : bool
    {
        return Lib::php()->type_resource_opened($result, $value);
    }

    /**
     * @param resource|null $result
     */
    public function resource_closed(&$result, $value) : bool
    {
        return Lib::php()->type_resource_closed($result, $value);
    }


    /**
     * @param class-string|null $result
     *
     * @param callable          ...$fnExistsList
     */
    public function struct(&$result, $value, bool $useRegex = null, ...$fnExistsList) : bool
    {
        return Lib::php()->type_struct($result, $value, $useRegex, ...$fnExistsList);
    }

    /**
     * @param class-string|null $result
     */
    public function struct_class(&$result, $value, bool $useRegex = null) : bool
    {
        return Lib::php()->type_struct_class($result, $value, $useRegex);
    }

    /**
     * @param class-string|null $result
     */
    public function struct_interface(&$result, $value, bool $useRegex = null) : bool
    {
        return Lib::php()->type_struct_interface($result, $value, $useRegex);
    }

    /**
     * @param class-string|null $result
     */
    public function struct_trait(&$result, $value, bool $useRegex = null) : bool
    {
        return Lib::php()->type_struct_trait($result, $value, $useRegex);
    }


    /**
     * @param class-string|null $result
     *
     * @param callable          ...$fnExistsList
     */
    public function struct_fqcn(&$result, $value, bool $useRegex = null, ...$fnExistsList) : bool
    {
        return Lib::php()->type_struct_fqcn($result, $value, $useRegex, ...$fnExistsList);
    }

    /**
     * @param string|null $result
     *
     * @param callable    ...$fnExistsList
     */
    public function struct_namespace(&$result, $value, bool $useRegex = null, ...$fnExistsList) : bool
    {
        return Lib::php()->type_struct_namespace($result, $value, $useRegex, ...$fnExistsList);
    }

    /**
     * @param string|null $result
     *
     * @param callable    ...$fnExistsList
     */
    public function struct_basename(&$result, $value, bool $useRegex = null, ...$fnExistsList) : bool
    {
        return Lib::php()->type_struct_basename($result, $value, $useRegex, ...$fnExistsList);
    }


    /**
     * @param array{ 0: class-string, 1: string }|null $result
     */
    public function method_array(&$result, $value) : bool
    {
        return Lib::php()->type_method_array($result, $value);
    }

    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function method_string(&$result, $value, array $refs = []) : bool
    {
        return Lib::php()->type_method_string($result, $value, $refs);
    }


    /**
     * @param callable|null $result
     * @param string|object $newScope
     */
    public function callable(&$result, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_object($result, $value, $newScope);
    }


    /**
     * @param callable|\Closure|object|null $result
     */
    public function callable_object(&$result, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_object($result, $value, $newScope);
    }

    /**
     * @param callable|object|null $result
     */
    public function callable_object_closure(&$result, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_object_closure($result, $value, $newScope);
    }

    /**
     * @param callable|object|null $result
     */
    public function callable_object_invokable(&$result, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_object_invokable($result, $value, $newScope);
    }


    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $result
     * @param string|object                                            $newScope
     */
    public function callable_array(&$result, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_array($result, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: object|class-string, 1: string }|null $result
     * @param string|object                                            $newScope
     */
    public function callable_array_method(&$result, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_array_method($result, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: class-string, 1: string }|null $result
     * @param string|object                                     $newScope
     */
    public function callable_array_method_static(&$result, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_array_method_static($result, $value, $newScope);
    }

    /**
     * @param callable|array{ 0: object, 1: string }|null $result
     * @param string|object                               $newScope
     */
    public function callable_array_method_non_static(&$result, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_array_method_non_static($result, $value, $newScope);
    }


    /**
     * @param callable-string|null $result
     */
    public function callable_string(&$result, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_string($result, $value, $newScope);
    }

    /**
     * @param callable-string|null $result
     */
    public function callable_string_function(&$result, $value) : bool
    {
        return Lib::php()->type_callable_string_function($result, $value);
    }

    /**
     * @param callable-string|null $result
     */
    public function callable_string_function_internal(&$result, $value) : bool
    {
        return Lib::php()->type_callable_string_function_internal($result, $value);
    }

    /**
     * @param callable-string|null $result
     */
    public function callable_string_function_non_internal(&$result, $value) : bool
    {
        return Lib::php()->type_callable_string_function_non_internal($result, $value);
    }

    /**
     * @param callable-string|null $result
     */
    public function callable_string_method_static(&$result, $value, $newScope = 'static') : bool
    {
        return Lib::php()->type_callable_string_method_static($result, $value, $newScope);
    }


    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function path(
        &$result,
        $value, array $refs = []
    ) : bool
    {
        return Lib::fs()->type_path($result, $value, $refs);
    }

    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function dirpath(
        &$result,
        $value, array $refs = []
    ) : bool
    {
        return Lib::fs()->type_dirpath($result, $value, $refs);
    }

    /**
     * @param string|null $result
     */
    public function filepath(
        &$result,
        $value, array $refs = []
    ) : bool
    {
        return Lib::fs()->type_filepath($result, $value, $refs);
    }


    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function path_realpath(
        &$result,
        $value, array $refs = []
    ) : bool
    {
        return Lib::fs()->type_path_realpath($result, $value, $refs);
    }

    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function dirpath_realpath(
        &$result,
        $value, array $refs = []
    ) : bool
    {
        return Lib::fs()->type_dirpath_realpath($result, $value, $refs);
    }

    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function filepath_realpath(
        &$result,
        $value, array $refs = []
    ) : bool
    {
        return Lib::fs()->type_filepath_realpath($result, $value, $refs);
    }


    /**
     * @param string|null $result
     */
    public function filename(&$result, $value) : bool
    {
        return Lib::fs()->type_filename($result, $value);
    }
}
