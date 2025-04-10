<?php

/**
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */

namespace Gzhegow\Lib\Modules\Str\Inflector;

use Gzhegow\Lib\Exception\RuntimeException;


class Inflector implements InflectorInterface
{
    const DOCTRINE_INFLECTOR                  = '\Doctrine\Inflector\Inflector';
    const SYMFONY_INFLECTOR_ENGLISH_INFLECTOR = '\Symfony\Component\String\Inflector\EnglishInflector';
    const SYMFONY_INFLECTOR_INTERFACE         = '\Symfony\Component\String\Inflector\InflectorInterface';


    /**
     * @var \Doctrine\Inflector\Inflector
     */
    protected $doctrineInflector;
    /**
     * @var \Symfony\Component\String\Inflector\InflectorInterface
     */
    protected $symfonyInflector;


    /**
     * @param null|object|\Symfony\Component\String\Inflector\InflectorInterface $doctrineInflector
     *
     * @return object
     */
    public function withDoctrineInflector(?object $doctrineInflector) : object
    {
        if ($doctrineInflector) {
            if (! is_a($doctrineInflector, $interface = static::DOCTRINE_INFLECTOR)) {
                throw new RuntimeException([
                    'Doctrine Inflector should implements %s: %s',
                    $interface,
                    $doctrineInflector,
                ]);
            }
        }

        $this->doctrineInflector = $doctrineInflector;

        return $this;
    }

    /**
     * @param null|object|\Symfony\Component\String\Inflector\InflectorInterface $symfonyInflector
     *
     * @return object
     */
    public function withSymfonyInflector(?object $symfonyInflector) : object
    {
        if ($symfonyInflector) {
            if (! is_a($symfonyInflector, $interface = static::SYMFONY_INFLECTOR_INTERFACE)) {
                throw new RuntimeException([
                    'Symfony Inflector should implements %s: %s',
                    $interface,
                    $symfonyInflector,
                ]);
            }
        }

        $this->symfonyInflector = $symfonyInflector;

        return $this;
    }


    /**
     * @return \Symfony\Component\String\Inflector\InflectorInterface
     */
    public function newDoctrineInflector() : object
    {
        $commands = [
            'composer require symfony/string',
            'composer require symfony/translation-contracts',
        ];

        if (! class_exists($class = static::DOCTRINE_INFLECTOR)) {
            throw new RuntimeException([
                'Please, run following: %s',
                $commands,
            ]);
        }

        $cachedWordInflector = '\Doctrine\Inflector\CachedWordInflector';
        $rulesetInflector = '\Doctrine\Inflector\RulesetInflector';
        $englishRules = '\Doctrine\Inflector\Rules\English\Rules';

        return new $class(
            new $cachedWordInflector(new $rulesetInflector(
                $englishRules::{'getSingularRuleset'}()
            )),
            new $cachedWordInflector(new $rulesetInflector(
                $englishRules::{'getPluralRuleset'}()
            ))
        );
    }

    /**
     * @return \Symfony\Component\String\Inflector\InflectorInterface
     */
    public function newSymfonyInflector() : object
    {
        $commands = [
            'composer require symfony/string',
            'composer require symfony/translation-contracts',
        ];

        if (! class_exists($class = static::SYMFONY_INFLECTOR_ENGLISH_INFLECTOR)) {
            throw new RuntimeException(
                [ 'Please, run following: %s', $commands ]
            );
        }

        return new $class();
    }


    /**
     * @return \Doctrine\Inflector\Inflector
     */
    public function getDoctrineInflector() : object
    {
        return $this->doctrineInflector = $this->doctrineInflector
            ?? $this->newDoctrineInflector();
    }

    /**
     * @return \Symfony\Component\String\Inflector\InflectorInterface
     */
    public function getSymfonyInflector() : object
    {
        return $this->symfonyInflector = $this->symfonyInflector
            ?? $this->newSymfonyInflector();
    }


    /**
     * @param string   $singular
     * @param null|int $offset
     * @param null|int $limit
     *
     * @return array
     */
    public function pluralize(string $singular, ?int $limit = null, ?int $offset = null) : array
    {
        $array = [];

        $limit = $limit ?? 0;
        $offset = $offset ?? 0;

        $array = array_merge($array, $this->pluralizeViaSimpleInflector($singular));

        try {
            $array = array_merge($array, null
                ?? $this->pluralizeViaDoctrineInflector($singular)
                ?? $this->pluralizeViaSymfonyInflector($singular)
            );
        }
        catch ( \Throwable $e ) {
            if (0 === count($array)) {
                throw new RuntimeException('Unable to ' . __FUNCTION__, null, $e);
            }
        }

        $array = array_values(array_unique($array));

        $result = [];
        foreach ( $array as $i => $string ) {
            if ($i < $offset) continue;

            $result[ $i ] = $string;

            if (! --$limit) break;
        }

        return $result;
    }

    /**
     * @param string   $plural
     * @param null|int $offset
     * @param null|int $limit
     *
     * @return null|array
     */
    public function singularize(string $plural, ?int $limit = null, ?int $offset = null) : array
    {
        $array = [];

        $limit = $limit ?? 0;
        $offset = $offset ?? 0;

        $array = array_merge($array, $this->singularizeViaSimpleInflector($plural));

        try {
            $array = array_merge($array, null
                ?? $this->singularizeViaDoctrineInflector($plural)
                ?? $this->singularizeViaSymfonyInflector($plural)
            );
        }
        catch ( \Throwable $e ) {
            if (0 === count($array)) {
                throw new RuntimeException('Unable to ' . __FUNCTION__, null, $e);
            }
        }

        $array = array_values(array_unique($array));

        $result = [];
        foreach ( $array as $i => $string ) {
            if ($offset--) continue;

            $result[ $i ] = $string;

            if (! --$limit) break;
        }

        return $result;
    }


    /**
     * @param string $singular
     *
     * @return null|array
     */
    protected function pluralizeViaSimpleInflector(string $singular) : ?array
    {
        $result = [];

        if ('s' === substr($singular, -1)) {
            $string = $singular . 'es';
        } else {
            $string = $singular . 's';
        }

        $result[] = $string;

        return $result;
    }

    /**
     * @param string $plural
     *
     * @return null|array
     */
    protected function singularizeViaSimpleInflector(string $plural) : ?array
    {
        $result = [];

        if ($plural !== ($string = rtrim($plural, 'sS'))) {
            $result[] = $string;
        }

        return $result;
    }


    /**
     * @param string $singular
     *
     * @return null|array
     */
    protected function pluralizeViaDoctrineInflector(string $singular) : ?array
    {
        if (! class_exists(static::DOCTRINE_INFLECTOR)) {
            return null;
        }

        $string = $this->getDoctrineInflector()->pluralize($singular);

        $result = [ $string ];

        return $result;
    }

    /**
     * @param string $plural
     *
     * @return null|array
     */
    protected function singularizeViaDoctrineInflector(string $plural) : ?array
    {
        if (! class_exists(static::DOCTRINE_INFLECTOR)) {
            return null;
        }

        $string = $this->getDoctrineInflector()->singularize($plural);

        $result = [ $string ];

        return $result;
    }


    /**
     * @param string $singular
     *
     * @return null|array
     */
    protected function pluralizeViaSymfonyInflector(string $singular) : ?array
    {
        if (! interface_exists(static::SYMFONY_INFLECTOR_INTERFACE)) {
            return null;
        }

        $result = $this->getSymfonyInflector()->pluralize($singular);

        usort($result, function ($a, $b) use ($singular) {
            return similar_text($singular, $b) - similar_text($singular, $a);
        });

        return $result;
    }

    /**
     * @param string $plural
     *
     * @return null|array
     */
    protected function singularizeViaSymfonyInflector(string $plural) : ?array
    {
        if (! interface_exists(static::SYMFONY_INFLECTOR_INTERFACE)) {
            return null;
        }

        $result = $this->getSymfonyInflector()->singularize($plural);

        usort($result, function ($a, $b) use ($plural) {
            return similar_text($plural, $b) - similar_text($plural, $a);
        });

        return $result;
    }
}
