<?php

namespace Gzhegow\Lib\Modules\Str;


class Alphabet
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
    protected $length;
    /**
     * @var string
     */
    protected $regex;
    /**
     * @var string
     */
    protected $regexNot;


    public function __construct(
        $original,
        //
        int $length,
        string $regex,
        string $regexNot
    )
    {
        $this->original = $original;

        $this->length = $length;
        $this->regex = $regex;
        $this->regexNot = $regexNot;

        $this->value = $original;
    }


    public function __toString()
    {
        return $this->getValue();
    }


    public function getValue() : string
    {
        return $this->value;
    }


    public function getOriginal() // : mixed
    {
        return $this->original;
    }


    public function getLength() : int
    {
        return $this->length;
    }

    public function getRegex() : string
    {
        return $this->regex;
    }

    public function getRegexNot() : string
    {
        return $this->regexNot;
    }
}
