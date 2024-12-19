<?php

namespace Gzhegow\Lib\Traits;

trait ParseTrait
{
    public static function parse_userbool($value) : ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (null !== ($_value = static::parse_int($value))) {
            return (bool) $_value;
        }

        if (null === ($_value = static::parse_int($value))) {
            return null;
        }

        $_value = strtolower($_value);

        switch ( $_value ):
            case 'true':
            case 'y':
            case 'yes':
            case 'on':
                return true;

            case 'false':
            case 'n':
            case 'no':
            case 'off':
                return false;

        endswitch;

        return null;
    }


    public static function parse_int($value) : ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value)) {
            if (! is_numeric($value)) {
                return null;
            }
        }

        $valueOriginal = $value;

        if (! is_scalar($valueOriginal)) {
            if (null === ($_valueOriginal = static::parse_string($valueOriginal))) {
                return null;
            }

            if (! is_numeric($_valueOriginal)) {
                return null;
            }

            $valueOriginal = $_valueOriginal;
        }

        $_value = $valueOriginal;
        $status = @settype($_value, 'integer');

        if ($status) {
            if ((float) $valueOriginal !== (float) $_value) {
                return null;
            }

            return $_value;
        }

        return null;
    }

    public static function parse_int_non_zero($value) : ?int
    {
        if (null === ($_value = static::parse_int($value))) {
            return null;
        }

        if ($_value == 0) {
            return null;
        }

        return $_value;
    }

    public static function parse_int_non_negative($value) : ?int
    {
        if (null === ($_value = static::parse_int($value))) {
            return null;
        }

        if ($_value < 0) {
            return null;
        }

        return $_value;
    }

    public static function parse_int_non_positive($value) : ?int
    {
        if (null === ($_value = static::parse_int($value))) {
            return null;
        }

        if ($_value > 0) {
            return null;
        }

        return $_value;
    }

    public static function parse_int_negative($value) : ?int
    {
        if (null === ($_value = static::parse_int($value))) {
            return null;
        }

        if ($_value >= 0) {
            return null;
        }

        return $_value;
    }

    public static function parse_int_positive($value) : ?int
    {
        if (null === ($_value = static::parse_int($value))) {
            return null;
        }

        if ($_value <= 0) {
            return null;
        }

        return $_value;
    }


    public static function parse_num($value) // : ?int|float
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            if (! is_finite($value)) {
                return null;

            } else {
                return $value;
            }
        }

        if (is_string($value)) {
            if (! is_numeric($value)) {
                return null;
            }
        }

        $valueOriginal = $value;

        if (! is_scalar($valueOriginal)) {
            if (null === ($_valueOriginal = static::parse_string($valueOriginal))) {
                return null;
            }

            if (! is_numeric($_valueOriginal)) {
                return null;
            }

            $valueOriginal = $_valueOriginal;
        }

        $_value = $valueOriginal;

        $_valueInt = $_value;
        $statusInt = @settype($_valueInt, 'integer');

        $_valueFloat = $_value;
        $statusFloat = @settype($_valueFloat, 'float');

        if ($statusInt) {
            if ($_valueFloat === (float) $_valueInt) {
                return $_valueInt;
            }
        }

        if ($statusFloat) {
            return $_valueFloat;
        }

        return null;
    }

    public static function parse_num_non_zero($value) // : ?int|float
    {
        if (null === ($_value = static::parse_num($value))) {
            return null;
        }

        if ($_value == 0) {
            return null;
        }

        return $_value;
    }

    public static function parse_num_non_negative($value) // : ?int|float
    {
        if (null === ($_value = static::parse_num($value))) {
            return null;
        }

        if ($_value < 0) {
            return null;
        }

        return $_value;
    }

    public static function parse_num_non_positive($value) // : ?int|float
    {
        if (null === ($_value = static::parse_num($value))) {
            return null;
        }

        if ($_value > 0) {
            return null;
        }

        return $_value;
    }

    public static function parse_num_negative($value) // : ?int|float
    {
        if (null === ($_value = static::parse_num($value))) {
            return null;
        }

        if ($_value >= 0) {
            return null;
        }

        return $_value;
    }

    public static function parse_num_positive($value) // : ?int|float
    {
        if (null === ($_value = static::parse_num($value))) {
            return null;
        }

        if ($_value <= 0) {
            return null;
        }

        return $_value;
    }


    public static function parse_numeric($value) : ?string
    {
        if (null === ($_value = static::parse_string($value))) {
            return null;
        }

        if (! is_numeric($_value)) {
            return null;
        }

        return $_value;
    }

    public static function parse_floor($value) : ?string
    {
        if (null === ($_value = static::parse_string($value))) {
            return null;
        }

        $minus = ('-' === $_value[ 0 ]) ? '-' : '';
        if ($minus) {
            $_value = substr($_value, 1);
        }

        if ('' === $_value) {
            return null;
        }

        if (extension_loaded('ctype')) {
            if (! ctype_digit($_value)) {
                return null;
            }

        } else {
            if (preg_match('/[^0-9]/', $_value)) {
                return null;
            }
        }

        return "{$minus}{$_value}";
    }

    public static function parse_frac($value) : ?string
    {
        if (null === ($_value = static::parse_string($value))) {
            return null;
        }

        $dot = ('.' === $_value[ 0 ]) ? '.' : '';
        if ($dot) {
            $_value = substr($_value, 1);
        }

        if ('' === $_value) {
            return null;
        }

        if (extension_loaded('ctype')) {
            if (! ctype_digit($_value)) {
                return null;
            }

        } else {
            if (preg_match('/[^0-9]/', $_value)) {
                return null;
            }
        }

        return "{$dot}{$_value}";
    }


    public static function parse_bcnum($value, int &$scaleParsed = null) : ?string
    {
        $scaleParsed = null;

        if (null === ($_value = static::parse_numeric($value))) {
            return null;
        }

        // > gzhegow, _filter_numeric() converts to string
        if (in_array($_value, [ 'NAN', 'INF', '-INF' ])) {
            return null;
        }

        // > gzhegow, 0.000022 becomes 2.2E-5, so you need to pass formatted string instead of float
        if (false !== strpos(strtolower($_value), 'e')) {
            return null;
        }

        $valueMinus = '-' === $_value[ 0 ];
        $valueAbs = $valueMinus ? substr($_value, 1) : $_value;
        [ $valueAbsFloor, $valueAbsFrac ] = explode('.', $valueAbs) + [ 1 => '' ];

        $valueAbsFloor = ltrim($valueAbsFloor, '0'); // 0000.1
        $valueAbsFrac = rtrim($valueAbsFrac, '0');   // 1.0000

        $scaleParsed = strlen($valueAbsFrac);

        $_value = ""
            . ($valueMinus ? '-' : '')
            . (('' !== $valueAbsFloor) ? $valueAbsFloor : "0")
            . (('' !== $valueAbsFrac) ? ".{$valueAbsFrac}" : "");

        return $_value;
    }

    public static function parce_bcnum_scale($value, int $scaleLimit, int &$scaleValue = null) : ?string
    {
        $scaleValue = null;

        if (null === ($_scaleLimit = static::parse_int_non_negative($scaleLimit))) {
            return null;
        }

        if (null === ($_value = static::parse_bcnum($value, $scaleValue))) {
            return null;
        }

        if ($_scaleLimit < $scaleValue) {
            return null;
        }

        return $_value;
    }


    public static function parse_string($value) : ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if (
            (null === $value)
            || is_array($value)
            || is_resource($value)
        ) {
            return null;
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                $_value = (string) $value;

                return $_value;
            }

            return null;
        }

        $_value = $value;
        $status = @settype($_value, 'string');

        if ($status) {
            return $_value;
        }

        return null;
    }

    public static function parse_string_not_empty($value) : ?string
    {
        if (null === ($_value = static::parse_string($value))) {
            return null;
        }

        if ('' === $_value) {
            return null;
        }

        return $_value;
    }


    public static function parse_trim($value) : ?string
    {
        if (null === ($_value = static::parse_string($value))) {
            return null;
        }

        $_value = trim($_value);

        if ('' === $_value) {
            return null;
        }

        return $_value;
    }

    public static function parse_letter($value) : ?string
    {
        if (null === ($_value = static::parse_string($value))) {
            return null;
        }

        preg_replace('/\s+/', '', $_value, 1, $count);
        if ($count > 0) {
            return null;
        }

        $fnStrlen = extension_loaded('mbstring')
            ? 'mb_strlen'
            : 'strlen';

        if ($fnStrlen($_value) > 1) {
            return null;
        }

        return $_value;
    }

    public static function parse_alphabet($value) : ?string
    {
        if (null === ($_value = static::parse_string($value))) {
            return null;
        }

        preg_replace('/\s+/', '', $_value, 1, $count);
        if ($count > 0) {
            return null;
        }

        $fnStrlen = extension_loaded('mbstring')
            ? 'mb_strlen'
            : 'strlen';

        if ($fnStrlen($_value) <= 1) {
            return null;
        }

        $fnStrSplit = extension_loaded('mbstring')
            ? 'mb_str_split'
            : 'str_split';

        $array = $fnStrSplit($value);

        if (count($array) !== count(array_unique($array))) {
            return null;
        }

        return $_value;
    }


    public static function parse_list($value) : ?array
    {
        if (! is_array($value)) return null;

        $keys = array_keys($value);

        foreach ( $keys as $key ) {
            if (is_string($key)) {
                return null;
            }
        }

        if ($keys !== range(0, count($value))) {
            return null;
        }

        return $value;
    }

    public static function parse_index($value) : ?array
    {
        if (! is_array($value)) return null;

        foreach ( array_keys($value) as $key ) {
            if (is_string($key)) {
                return null;
            }
        }

        return $value;
    }

    public static function parse_dict($value) : ?array
    {
        if (! is_array($value)) return null;

        foreach ( array_keys($value) as $key ) {
            if (is_int($key)) {
                return null;
            }

            if ('' === $key) {
                return null;
            }
        }

        return $value;
    }


    public static function parse_countable($value) : ?iterable
    {
        if (PHP_VERSION_ID < 70300) {
            return null;
        }

        if (! is_countable($value)) {
            return null;
        }

        return $value;
    }


    /**
     * @return resource|null
     */
    public static function parse_resource($value) // : ?resource
    {
        if (false
            || is_resource($value)
            || (gettype($value) === 'resource (closed)')
        ) {
            return $value;
        }

        return null;
    }

    /**
     * @return resource|null
     */
    function parse_resource_opened($value) // : ?resource
    {
        return is_resource($value) ? $value : null;
    }

    /**
     * @return resource|null
     */
    function parse_resource_closed($value) // : ?resource
    {
        if ('resource (closed)' === gettype($value)) {
            return $value;
        }

        return null;
    }


    public static function parse_path(
        $value, array $optional = [],
        array &$pathinfo = null
    ) : ?string
    {
        $pathinfo = null;

        $optional[ 0 ] = $optional[ 'with_pathinfo' ] ?? $optional[ 0 ] ?? false;

        if (null === ($_value = static::parse_string_not_empty($value))) {
            return null;
        }

        if (false !== strpos($_value, "\0")) {
            return null;
        }

        $withPathInfoResult = (bool) $optional[ 0 ];

        if ($withPathInfoResult) {
            try {
                $pathinfo = pathinfo($_value);
            }
            catch ( \Throwable $e ) {
                return null;
            }
        }

        return $_value;
    }

    public static function parse_dirpath(
        $value, array $optional = [],
        array &$pathinfo = null
    ) : ?string
    {
        $_value = static::parse_path(
            $value, $optional,
            $pathinfo
        );

        if (null === $_value) {
            return null;
        }

        $status = file_exists($_value);

        if (false === $status) {
            return $_value;
        }

        if (! is_dir($_value)) {
            return null;
        }

        $_value = realpath($_value);

        return $_value;
    }

    public static function parse_filepath(
        $value, array $optional = [],
        array &$pathinfo = null
    ) : ?string
    {
        $_value = static::parse_path(
            $value, $optional,
            $pathinfo
        );

        if (null === $_value) {
            return null;
        }

        $status = file_exists($_value);

        if (false === $status) {
            return $_value;
        }

        if (! is_file($_value)) {
            return null;
        }

        $_value = realpath($_value);

        return $_value;
    }


    public static function parse_path_realpath(
        $value, array $optional = [],
        array &$pathinfo = null
    ) : ?string
    {
        $_value = static::parse_path(
            $value, $optional,
            $pathinfo
        );

        if (null === $_value) {
            return null;
        }

        if (false === ($_value = realpath($_value))) {
            return null;
        }

        return $_value;
    }

    public static function parse_dirpath_realpath(
        $value, array $optional = [],
        array &$pathinfo = null
    ) : ?string
    {
        $_value = static::parse_path(
            $value, $optional,
            $pathinfo
        );

        if (null === $_value) {
            return null;
        }

        $status = file_exists($_value);

        if (false === $status) {
            return null;
        }

        if (! is_dir($_value)) {
            return null;
        }

        $_value = realpath($_value);

        return $_value;
    }

    public static function parse_filepath_realpath(
        $value, array $optional = [],
        array &$pathinfo = null
    ) : ?string
    {
        $_value = static::parse_path(
            $value, $optional,
            $pathinfo
        );

        if (null === $_value) {
            return null;
        }

        $status = file_exists($_value);

        if (false === $status) {
            return null;
        }

        if (! is_file($_value)) {
            return null;
        }

        $_value = realpath($_value);

        return $_value;
    }


    public static function parse_filename($value) : ?string
    {
        if (null === ($_value = static::parse_string_not_empty($value))) {
            return null;
        }

        $forbidden = [ "\0", "/", "\\", DIRECTORY_SEPARATOR ];

        foreach ( $forbidden as $f ) {
            if (false !== strpos($_value, $f)) {
                return null;
            }
        }

        return $_value;
    }


    public static function parse_regex($regex) : ?string
    {
        if (null === ($_value = static::parse_string_not_empty($regex))) {
            return null;
        }

        error_clear_last();

        try {
            $status = preg_match($regex, '');
        }
        catch ( \Throwable $e ) {
            return null;
        }

        if (error_get_last()) {
            return null;
        }

        if (false === $status) {
            return null;
        }

        return $_value;
    }


    /**
     * @param callable ...$fnExistsList
     *
     * @return class-string|null
     */
    public static function parse_struct($value, bool $useRegex = null, ...$fnExistsList) : ?string
    {
        $useRegex = $useRegex ?? false;
        $fnExistsList = $fnExistsList ?: [
            'class_exists',
            'interface_exists',
            'trait_exists',
        ];

        if (is_object($value)) {
            return ltrim(get_class($value), '\\');
        }

        if (null === ($_value = static::parse_string_not_empty($value))) {
            return null;
        }

        $_value = ltrim($_value, '\\');

        foreach ( $fnExistsList as $fn ) {
            if ($fn($_value)) {
                return $_value;
            }
        }

        if ($useRegex) {
            if (! preg_match(
                '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*$/',
                $_value
            )) {
                return null;
            }
        }

        return $_value;
    }

    /**
     * @return class-string|null
     */
    public static function parse_struct_class($value, bool $useRegex = null) : ?string
    {
        $_value = static::parse_struct($value, $useRegex, 'class_exists');

        if (null === $_value) {
            return null;
        }

        return $_value;
    }

    /**
     * @return class-string|null
     */
    public static function parse_struct_interface($value, bool $useRegex = null) : ?string
    {
        $_value = static::parse_struct($value, $useRegex, 'interface_exists');

        if (null === $_value) {
            return null;
        }

        return $_value;
    }

    /**
     * @return class-string|null
     */
    public static function parse_struct_trait($value, bool $useRegex = null) : ?string
    {
        $_value = static::parse_struct($value, $useRegex, 'trait_exists');

        if (null === $_value) {
            return null;
        }

        return $_value;
    }


    /**
     * @param callable ...$fnExistsList
     */
    public static function parse_struct_fqcn($value, bool $useRegex = null, ...$fnExistsList) : ?string
    {
        $_value = static::parse_struct($value, $useRegex, ...$fnExistsList);

        if (null === $_value) {
            return null;
        }

        $_value = '\\' . $_value;

        return $_value;
    }

    /**
     * @param callable ...$fnExistsList
     */
    public static function parse_struct_namespace($value, bool $useRegex = null, ...$fnExistsList) : ?string
    {
        $_value = static::parse_struct($value, $useRegex, ...$fnExistsList);

        if (null === $_value) {
            return null;
        }

        if (false !== strpos($_value, '\\')) {
            $_value = str_replace('\\', '/', $_value);
        }

        if (false === strpos($_value, '/')) {
            $_value = null;

        } else {
            $_value = preg_replace('~[/]+~', '/', $_value);

            $namespace = dirname($_value);
            $namespace = str_replace('/', '\\', $namespace);

            $_value = $namespace;
        }

        return $_value;
    }

    /**
     * @param callable ...$fnExistsList
     */
    public static function parse_struct_basename($value, bool $useRegex = null, ...$fnExistsList) : ?string
    {
        $_value = static::parse_struct($value, $useRegex, ...$fnExistsList);

        if (null === $_value) {
            return null;
        }

        if (false !== strpos($_value, '\\')) {
            $_value = str_replace('\\', '/', $_value);
        }

        if (false !== strpos($_value, '/')) {
            $_value = preg_replace('~[/]+~', '/', $_value);

            $_value = basename($_value);
        }

        return $_value;
    }


    public static function parse_ip(string $ip) : ?string
    {
        if (null === ($_ip = static::parse_string_not_empty($ip))) {
            return null;
        }

        if (false === ($_ip = filter_var($_ip, FILTER_VALIDATE_IP))) {
            return null;
        }

        return $_ip;
    }
}
