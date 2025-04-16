<?php

/**
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */

namespace Gzhegow\Lib\Modules\Str\Inflector;

use Gzhegow\Lib\Exception\RuntimeException;


class DefaultInflector implements InflectorInterface
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
     * @var bool
     */
    protected $useDoctrineInflector = false;
    /**
     * @var bool
     */
    protected $useSymfonyInflector = false;


    public function __construct()
    {
        $this->useDoctrineInflector(null);
        $this->useSymfonyInflector(null);
    }


    /**
     * @param null|object|\Symfony\Component\String\Inflector\InflectorInterface $doctrineInflector
     *
     * @return object
     */
    public function withDoctrineInflector(?object $doctrineInflector) : object
    {
        if (null !== $doctrineInflector) {
            if (! is_a($doctrineInflector, $interface = static::DOCTRINE_INFLECTOR)) {
                throw new RuntimeException(
                    [
                        'The `doctrineInflector` should be instance of: ' . $interface,
                        $doctrineInflector,
                    ]
                );
            }
        }

        $this->doctrineInflector = $doctrineInflector;

        return $this;
    }

    /**
     * @return \Symfony\Component\String\Inflector\InflectorInterface
     */
    protected function newDoctrineInflector() : object
    {
        $commands = [
            'composer require doctrine/inflector',
        ];

        if (! class_exists($doctrineInflector = static::DOCTRINE_INFLECTOR)) {
            throw new RuntimeException([
                ''
                . 'Please, run following commands: '
                . '[ ' . implode(' ][ ', $commands) . ' ]',
                //
                $commands,
            ]);
        }

        $cachedWordInflector = '\Doctrine\Inflector\CachedWordInflector';
        $rulesetInflector = '\Doctrine\Inflector\RulesetInflector';
        $englishRules = '\Doctrine\Inflector\Rules\English\Rules';

        return new $doctrineInflector(
            new $cachedWordInflector(
                new $rulesetInflector(
                    $englishRules::{'getSingularRuleset'}()
                )
            ),
            new $cachedWordInflector(
                new $rulesetInflector(
                    $englishRules::{'getPluralRuleset'}()
                )
            )
        );
    }

    /**
     * @return \Doctrine\Inflector\Inflector
     */
    protected function getDoctrineInflector() : object
    {
        return $this->doctrineInflector = null
            ?? $this->doctrineInflector
            ?? $this->newDoctrineInflector();
    }


    /**
     * @param null|object|\Symfony\Component\String\Inflector\InflectorInterface $symfonyInflector
     *
     * @return object
     */
    public function withSymfonyInflector(?object $symfonyInflector) : object
    {
        if (null !== $symfonyInflector) {
            if (! is_a($symfonyInflector, $interface = static::SYMFONY_INFLECTOR_INTERFACE)) {
                throw new RuntimeException(
                    [
                        'The `symfonyInflector` should be instance of: ' . $interface,
                        $symfonyInflector,
                    ]
                );
            }
        }

        $this->symfonyInflector = $symfonyInflector;

        return $this;
    }

    /**
     * @return \Symfony\Component\String\Inflector\InflectorInterface
     */
    protected function newSymfonyInflector() : object
    {
        $commands = [
            'composer require symfony/string',
            'composer require symfony/translation-contracts',
        ];

        if (! class_exists($class = static::SYMFONY_INFLECTOR_ENGLISH_INFLECTOR)) {
            throw new RuntimeException([
                ''
                . 'Please, run following commands: '
                . '[ ' . implode(' ][ ', $commands) . ' ]',
                //
                $commands,
            ]);
        }

        return new $class();
    }

    /**
     * @return \Symfony\Component\String\Inflector\InflectorInterface
     */
    protected function getSymfonyInflector() : object
    {
        return $this->symfonyInflector = null
            ?? $this->symfonyInflector
            ?? $this->newSymfonyInflector();
    }


    /**
     * @return static
     */
    public function useDoctrineInflector(?bool $useDoctrineInflector = null)
    {
        $classExists = class_exists(static::DOCTRINE_INFLECTOR);

        $useDoctrineInflector = $useDoctrineInflector ?? $classExists;

        if ($useDoctrineInflector) {
            $this->getDoctrineInflector();
        }

        $this->useDoctrineInflector = $useDoctrineInflector;

        return $this;
    }

    /**
     * @return static
     */
    public function useSymfonyInflector(?bool $useSymfonyInflector = null)
    {
        $classExists = class_exists(static::SYMFONY_INFLECTOR_INTERFACE);

        $useSymfonyInflector = $useSymfonyInflector ?? $classExists;

        if ($useSymfonyInflector) {
            $this->getSymfonyInflector();
        }

        $this->useSymfonyInflector = $useSymfonyInflector;

        return $this;
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

        $list = [];

        if ($this->useDoctrineInflector) {
            try {
                $list[] = $this->pluralizeUsingDoctrineInflector($singular);
            }
            catch ( \Throwable $e ) {
                throw new RuntimeException('Unable to ' . __FUNCTION__, null, $e);
            }
        }

        if ($this->useSymfonyInflector) {
            try {
                $list[] = $this->pluralizeUsingSymfonyInflector($singular);
            }
            catch ( \Throwable $e ) {
                throw new RuntimeException('Unable to ' . __FUNCTION__, null, $e);
            }
        }

        $list[] = $this->pluralizeUsingSimpleInflector($singular);

        $list = array_merge(...$list);

        $list = array_unique($list);
        $list = array_values($list);

        usort($list, function ($a, $b) use ($singular) {
            return similar_text($singular, $b) <=> similar_text($singular, $a);
        });

        $result = [];

        foreach ( $list as $i => $string ) {
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

        $list = [];

        if ($this->useDoctrineInflector) {
            try {
                $list[] = $this->singularizeUsingDoctrineInflector($plural);
            }
            catch ( \Throwable $e ) {
                throw new RuntimeException('Unable to ' . __FUNCTION__, null, $e);
            }
        }

        if ($this->useSymfonyInflector) {
            try {
                $list[] = $this->singularizeUsingSymfonyInflector($plural);
            }
            catch ( \Throwable $e ) {
                throw new RuntimeException('Unable to ' . __FUNCTION__, null, $e);
            }
        }

        $list[] = $this->singularizeUsingSimpleInflector($plural);

        $list = array_merge(...$list);

        $list = array_unique($list);
        $list = array_values($list);

        usort($list, function ($a, $b) use ($plural) {
            return similar_text($plural, $b) <=> similar_text($plural, $a);
        });

        $result = [];

        foreach ( $list as $i => $string ) {
            if ($offset--) continue;

            $result[ $i ] = $string;

            if (! --$limit) break;
        }

        return $result;
    }


    protected function pluralizeUsingDoctrineInflector(string $singular) : array
    {
        $string = $this->getDoctrineInflector()->pluralize($singular);

        $result = [ $string ];

        return $result;
    }

    protected function singularizeUsingDoctrineInflector(string $plural) : array
    {
        $string = $this->getDoctrineInflector()->singularize($plural);

        $result = [ $string ];

        return $result;
    }


    protected function pluralizeUsingSymfonyInflector(string $singular) : array
    {
        $result = $this->getSymfonyInflector()->pluralize($singular);

        return $result;
    }

    protected function singularizeUsingSymfonyInflector(string $plural) : array
    {
        $result = $this->getSymfonyInflector()->singularize($plural);

        return $result;
    }


    protected function pluralizeUsingSimpleInflector(string $singular) : array
    {
        if ('s' === substr($singular, -1)) {
            $string = $singular . 'es';

        } else {
            $string = $singular . 's';
        }

        $result = [ $string ];

        return $result;
    }

    protected function singularizeUsingSimpleInflector(string $plural) : array
    {
        $string = rtrim($plural, 'sS');

        if ($plural !== $string) {
            $result = [ $string ];

        } else {
            $result = [];
        }

        return $result;
    }
}
