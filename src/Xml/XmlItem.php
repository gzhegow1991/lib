<?php

namespace Gzhegow\Lib\Xml;

class XmlItem implements
    \ArrayAccess
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string|null
     */
    protected $text;

    /**
     * @var array<string, string>
     */
    protected $attributes = [];
    /**
     * @var array<int|string, self|self[]>
     */
    protected $children = [];


    public function __construct(string $name, $value = null)
    {
        $this->name = $name;
        if (is_scalar($value)) {
            $this->value = (string) $value;
        }
    }

    public function setAttributes(array $attributes) : void
    {
        $this->attributes = $attributes;
    }

    public function setChildren(array $children) : void
    {
        foreach ( $children as $childName => $childValue ) {
            if (is_array($childValue)) {
                $this->children[] = new XMLItem($childName, null);
                end($this->children)->setChildren($childValue);
            } elseif ($childValue instanceof XMLItem) {
                $this->children[] = $childValue;
            } else {
                $this->children[] = new XMLItem($childName, $childValue);
            }
        }
    }

    public function toSimpleXML(?SimpleXMLElement $xml = null) : SimpleXMLElement
    {
        if ($xml === null) {
            $xml = new SimpleXMLElement("<{$this->name}></{$this->name}>");
        } else {
            $xml = $xml->addChild($this->name, $this->value ?? '');
        }

        foreach ( $this->attributes as $key => $val ) {
            $xml->addAttribute($key, $val);
        }

        foreach ( $this->children as $child ) {
            if ($child instanceof self) {
                $child->toSimpleXML($xml);
            }
        }

        return $xml;
    }

    public function __toString() : string
    {
        return $this->toSimpleXML()->asXML();
    }

    // ArrayAccess

    public function offsetExists($offset) : bool
    {
        foreach ( $this->children as $child ) {
            if ($child instanceof self && $child->name === $offset) {
                return true;
            }
        }

        return false;
    }

    public function offsetGet($offset) : mixed
    {
        foreach ( $this->children as $child ) {
            if ($child instanceof self && $child->name === $offset) {
                return $child;
            }
        }

        return null;
    }

    public function offsetSet($offset, $value) : void
    {
        if ($value instanceof self) {
            $value->name = $offset ?? $value->name;
            $this->children[] = $value;
        } elseif (is_array($value)) {
            $child = new XMLItem($offset ?? 'item');
            $child->setChildren($value);
            $this->children[] = $child;
        } else {
            $this->children[] = new XMLItem($offset ?? 'item', $value);
        }
    }

    public function offsetUnset($offset) : void
    {
        foreach ( $this->children as $k => $child ) {
            if ($child instanceof self && $child->name === $offset) {
                unset($this->children[ $k ]);
                break;
            }
        }
    }
}
