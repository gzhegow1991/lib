<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\BcMath\BcNumber;


class ParseModule
{
    public function userbool($value) : ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (null !== ($_value = $this->int($value))) {
            return (bool) $_value;
        }

        if (null === ($_value = $this->int($value))) {
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


    public function int($value) : ?int
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
            if (null === ($_valueOriginal = $this->string($valueOriginal))) {
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

    public function int_non_zero($value) : ?int
    {
        if (null === ($_value = $this->int($value))) {
            return null;
        }

        if ($_value == 0) {
            return null;
        }

        return $_value;
    }

    public function int_non_negative($value) : ?int
    {
        if (null === ($_value = $this->int($value))) {
            return null;
        }

        if ($_value < 0) {
            return null;
        }

        return $_value;
    }

    public function int_non_positive($value) : ?int
    {
        if (null === ($_value = $this->int($value))) {
            return null;
        }

        if ($_value > 0) {
            return null;
        }

        return $_value;
    }

    public function int_negative($value) : ?int
    {
        if (null === ($_value = $this->int($value))) {
            return null;
        }

        if ($_value >= 0) {
            return null;
        }

        return $_value;
    }

    public function int_positive($value) : ?int
    {
        if (null === ($_value = $this->int($value))) {
            return null;
        }

        if ($_value <= 0) {
            return null;
        }

        return $_value;
    }


    public function num($value) // : ?int|float
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
            if (null === ($_valueOriginal = $this->string($valueOriginal))) {
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

    public function num_non_zero($value) // : ?int|float
    {
        if (null === ($_value = $this->num($value))) {
            return null;
        }

        if ($_value == 0) {
            return null;
        }

        return $_value;
    }

    public function num_non_negative($value) // : ?int|float
    {
        if (null === ($_value = $this->num($value))) {
            return null;
        }

        if ($_value < 0) {
            return null;
        }

        return $_value;
    }

    public function num_non_positive($value) // : ?int|float
    {
        if (null === ($_value = $this->num($value))) {
            return null;
        }

        if ($_value > 0) {
            return null;
        }

        return $_value;
    }

    public function num_negative($value) // : ?int|float
    {
        if (null === ($_value = $this->num($value))) {
            return null;
        }

        if ($_value >= 0) {
            return null;
        }

        return $_value;
    }

    public function num_positive($value) // : ?int|float
    {
        if (null === ($_value = $this->num($value))) {
            return null;
        }

        if ($_value <= 0) {
            return null;
        }

        return $_value;
    }


    public function numeric($value) : ?string
    {
        if (null === ($_value = $this->string($value))) {
            return null;
        }

        if (! is_numeric($_value)) {
            return null;
        }

        return $_value;
    }

    public function floor($value) : ?string
    {
        if (null === ($_value = $this->string($value))) {
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

    public function frac($value) : ?string
    {
        if (null === ($_value = $this->string($value))) {
            return null;
        }

        if ('.' !== $_value[ 0 ]) {
            return null;
        }

        $test = substr($_value, 1);

        if ('' === $test) {
            return null;
        }

        if (extension_loaded('ctype')) {
            if (! ctype_digit($test)) {
                return null;
            }

        } else {
            if (preg_match('/[^0-9]/', $test)) {
                return null;
            }
        }

        return $_value;
    }


    public function bcnum($value, int &$scaleParsed = null) : ?BcNumber
    {
        return Lib::bcmath()->parse_bcnum($value, $scaleParsed);
    }


    public function string($value) : ?string
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

    public function string_not_empty($value) : ?string
    {
        if (null === ($_value = $this->string($value))) {
            return null;
        }

        if ('' === $_value) {
            return null;
        }

        return $_value;
    }


    public function trim($value) : ?string
    {
        if (null === ($_value = $this->string($value))) {
            return null;
        }

        $_value = trim($_value);

        if ('' === $_value) {
            return null;
        }

        return $_value;
    }

    public function letter($value) : ?string
    {
        if (null === ($_value = $this->string($value))) {
            return null;
        }

        preg_replace('/\s+/', '', $_value, 1, $count);
        if ($count > 0) {
            return null;
        }

        $fnStrlen = Lib::str()->mb_func('strlen');

        if ($fnStrlen($_value) > 1) {
            return null;
        }

        return $_value;
    }

    public function alphabet($value) : ?string
    {
        if (null === ($_value = $this->string($value))) {
            return null;
        }

        preg_replace('/\s+/', '', $_value, 1, $count);
        if ($count > 0) {
            return null;
        }

        $fnStrlen = Lib::str()->mb_func('strlen');

        if ($fnStrlen($_value) <= 1) {
            return null;
        }

        $fnStrSplit = Lib::str()->mb_func('str_split');

        $array = $fnStrSplit($value);

        if (count($array) !== count(array_unique($array))) {
            return null;
        }

        return $_value;
    }


    public function list($value) : ?array
    {
        if (! is_array($value)) return null;

        foreach ( array_keys($value) as $key ) {
            if (is_string($key)) {
                return null;
            }
        }

        return $value;
    }

    public function list_strict($value) : ?array
    {
        if (! is_array($value)) {
            return null;
        }

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

    public function dict($value) : ?array
    {
        if (! is_array($value)) {
            return null;
        }

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


    public function countable($value) : ?iterable
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
    public function resource($value) // : ?resource
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
    public function resource_opened($value) // : ?resource
    {
        return is_resource($value) ? $value : null;
    }

    /**
     * @return resource|null
     */
    public function resource_closed($value) // : ?resource
    {
        if ('resource (closed)' === gettype($value)) {
            return $value;
        }

        return null;
    }


    public function path(
        $value, array $optional = [],
        array &$pathinfo = null
    ) : ?string
    {
        $pathinfo = null;

        $optional[ 0 ] = $optional[ 'with_pathinfo' ] ?? $optional[ 0 ] ?? false;

        if (null === ($_value = $this->string_not_empty($value))) {
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

    public function dirpath(
        $value, array $optional = [],
        array &$pathinfo = null
    ) : ?string
    {
        $_value = $this->path(
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

    public function filepath(
        $value, array $optional = [],
        array &$pathinfo = null
    ) : ?string
    {
        $_value = $this->path(
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


    public function path_realpath(
        $value, array $optional = [],
        array &$pathinfo = null
    ) : ?string
    {
        $_value = $this->path(
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

    public function dirpath_realpath(
        $value, array $optional = [],
        array &$pathinfo = null
    ) : ?string
    {
        $_value = $this->path(
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

    public function filepath_realpath(
        $value, array $optional = [],
        array &$pathinfo = null
    ) : ?string
    {
        $_value = $this->path(
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


    public function filename($value) : ?string
    {
        if (null === ($_value = $this->string_not_empty($value))) {
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


    public function regex($regex) : ?string
    {
        if (null === ($_value = $this->string_not_empty($regex))) {
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
    public function struct($value, bool $useRegex = null, ...$fnExistsList) : ?string
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

        if (null === ($_value = $this->string_not_empty($value))) {
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
    public function struct_class($value, bool $useRegex = null) : ?string
    {
        $_value = $this->struct($value, $useRegex, 'class_exists');

        if (null === $_value) {
            return null;
        }

        return $_value;
    }

    /**
     * @return class-string|null
     */
    public function struct_interface($value, bool $useRegex = null) : ?string
    {
        $_value = $this->struct($value, $useRegex, 'interface_exists');

        if (null === $_value) {
            return null;
        }

        return $_value;
    }

    /**
     * @return class-string|null
     */
    public function struct_trait($value, bool $useRegex = null) : ?string
    {
        $_value = $this->struct($value, $useRegex, 'trait_exists');

        if (null === $_value) {
            return null;
        }

        return $_value;
    }


    /**
     * @param callable ...$fnExistsList
     */
    public function struct_fqcn($value, bool $useRegex = null, ...$fnExistsList) : ?string
    {
        $_value = $this->struct($value, $useRegex, ...$fnExistsList);

        if (null === $_value) {
            return null;
        }

        $_value = '\\' . $_value;

        return $_value;
    }

    /**
     * @param callable ...$fnExistsList
     */
    public function struct_namespace($value, bool $useRegex = null, ...$fnExistsList) : ?string
    {
        $_value = $this->struct($value, $useRegex, ...$fnExistsList);

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
    public function struct_basename($value, bool $useRegex = null, ...$fnExistsList) : ?string
    {
        $_value = $this->struct($value, $useRegex, ...$fnExistsList);

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


    public function ip(string $ip) : ?string
    {
        return Lib::net()->parse_ip($ip);
    }
}
