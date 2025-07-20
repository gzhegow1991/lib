<?php

namespace Gzhegow\Lib\Modules\Bcmath;

use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Php\Interfaces\ToFloatInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToStringInterface;
use Gzhegow\Lib\Modules\Php\Interfaces\ToIntegerInterface;


class Bcnumber implements
    ToIntegerInterface,
    ToFloatInterface,
    ToStringInterface
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @var mixed
     */
    protected $original;

    /**
     * @var string
     */
    protected $sign;
    /**
     * @var string
     */
    protected $int;
    /**
     * @var string
     */
    protected $frac;

    /**
     * @var int
     */
    protected $scale;


    private function __construct()
    {
    }


    public function __toString()
    {
        return $this->getValue();
    }


    /**
     * @return static|Ret<static>
     */
    public static function fromValid($from, ?array $fallback = null)
    {
        $ret = Ret::new();

        $instance = null
            ?? static::fromStatic($from)->orNull($ret)
            ?? static::fromValidArray($from)->orNull($ret);

        if ($ret->isFail()) {
            return Ret::throw($fallback, $ret);
        }

        return Ret::val($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromStatic($from, ?array $fallback = null)
    {
        if ($from instanceof static) {
            return Ret::val($fallback, $from);
        }

        return Ret::throw(
            $fallback,
            [ 'The `from` should be an instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromValidArray($from, ?array $fallback = null)
    {
        if (! is_array($from)) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be array', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new static();

        [
            'original' => $instance->original,
            'sign'     => $instance->sign,
            'int'      => $instance->int,
            'frac'     => $instance->frac,
            'scale'    => $instance->scale,
        ] = $from;

        $instance->value = "{$instance->sign}{$instance->int}{$instance->frac}";

        return Ret::val($fallback, $instance);
    }


    public function isInteger() : bool
    {
        return $this->value === "{$this->sign}{$this->int}";
    }

    public function isDecimal() : bool
    {
        return true
            && ($this->value === "{$this->sign}{$this->int}{$this->frac}")
            && ($this->value !== "{$this->sign}{$this->int}");
    }


    public function isZero() : bool
    {
        return true
            && ('' === $this->frac)
            && ('0' === $this->int);
    }

    public function isPositive() : bool
    {
        return true
            && ('' === $this->sign)
            && ! (('' === $this->frac) && ('0' === $this->int));
    }

    public function isNegative() : bool
    {
        return true
            && ('-' === $this->sign)
            && ! (('' === $this->frac) && ('0' === $this->int));
    }

    public function isNonPositive() : bool
    {
        return false
            || ('-' === $this->sign)
            || (('' === $this->frac) && ('0' === $this->int));
    }

    public function isNonNegative() : bool
    {
        return false
            || ('' === $this->sign)
            || (('' === $this->frac) && ('0' === $this->int));
    }


    public function toInteger(array $options = []) : int
    {
        if (! $this->isInteger()) {
            throw new RuntimeException(
                [ 'This number cannot be converted to an integer', $this ]
            );
        }

        return (int) $this->value;
    }

    public function toFloat(array $options = []) : float
    {
        return (float) $this->value;
    }

    public function toString(array $options = []) : string
    {
        return $this->value;
    }


    public function getOriginal()
    {
        return $this->original;
    }


    public function getSign() : string
    {
        return $this->sign;
    }

    public function getInt() : string
    {
        return $this->int;
    }


    public function hasFrac(?string &$result = null) : bool
    {
        $result = null;

        if ('' !== $this->frac) {
            $result = $this->frac;

            return true;
        }

        return false;
    }

    public function getFrac() : string
    {
        return $this->frac;
    }


    public function getScale() : int
    {
        return $this->scale;
    }


    public function getValue() : string
    {
        return $this->value;
    }


    public function getValueInt() : string
    {
        return "{$this->sign}{$this->int}";
    }

    public function getValueFrac() : string
    {
        return "{$this->sign}0{$this->frac}";
    }


    public function getValueAbsolute() : string
    {
        return "{$this->int}{$this->frac}";
    }

    public function getValueAbsoluteInt() : string
    {
        return $this->int;
    }

    public function getValueAbsoluteFrac() : string
    {
        return "0{$this->frac}";
    }
}
