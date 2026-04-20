<?php

namespace Gzhegow\Lib\Modules\Debug\Throwabler;

use Throwable as T;
use Gzhegow\Lib\Exception\ExceptInterface;


interface ThrowablerInterface
{
    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return \Traversable<string, (\Throwable|ExceptInterface)[]>
     */
    public function getPreviousIterator($throwable) : \Traversable;

    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return \Traversable<int, array<string, (\Throwable|ExceptInterface)[]>>
     */
    public function getPreviousTrackIterator($throwable) : \Traversable;


    /**
     * @template-covariant T of (\Throwable|ExceptInterface)
     *
     * @param \Throwable|ExceptInterface $throwable
     * @param class-string<T>|null       $throwableClass
     *
     * @return T|null
     */
    public function catchPrevious($throwable, ?string $throwableClass = null) : ?\Throwable;


    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getArray($throwable, ?int $flags = null) : array;

    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getLines($throwable, ?int $flags = null) : array;


    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getMessagesArray($throwable, ?int $flags = null) : array;

    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getMessagesLines($throwable, ?int $flags = null) : array;


    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getThrowableArray($throwable, ?int $flags = null) : array;

    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getThrowableLines($throwable, ?int $flags = null) : array;


    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getThrowableMessagesArray($throwable, ?int $flags = null) : array;

    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getThrowableMessagesLines($throwable, ?int $flags = null) : array;


    /**
     * @param \Throwable|ExceptInterface $throwable
     */
    public function getThrowableInfoArray($throwable, ?int $flags = null) : array;

    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getThrowableInfoLines($throwable, ?int $flags = null) : array;


    /**
     * @param \Throwable|ExceptInterface $throwable
     */
    public function getThrowableTraceArray($throwable, ?int $flags = null) : array;

    /**
     * @param \Throwable|ExceptInterface $throwable
     *
     * @return string[]
     */
    public function getThrowableTraceLines($throwable, ?int $flags = null) : array;
}
