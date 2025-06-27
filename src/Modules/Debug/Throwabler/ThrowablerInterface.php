<?php

namespace Gzhegow\Lib\Modules\Debug\Throwabler;

interface ThrowablerInterface
{
    /**
     * @return static
     */
    public function setDirRoot(?string $dirRoot);


    /**
     * @return array<string, \Throwable>
     */
    public function getPreviousArray(\Throwable $throwable) : array;

    /**
     * @return \Generator<string, \Throwable[]>
     */
    public function getPreviousIterator(\Throwable $throwable) : \Generator;

    /**
     * @return \Traversable<string, \Throwable[]>
     */
    public function getPreviousTrackIterator(\Throwable $throwable) : \Traversable;


    /**
     * @return string[]
     */
    public function getPreviousMessageList(\Throwable $throwable, ?int $flags = null) : array;

    /**
     * @return string[]
     */
    public function getPreviousMessageLines(\Throwable $throwable, ?int $flags = null) : array;


    /**
     * @return string[]
     */
    public function getPreviousMessagesList(\Throwable $throwable, ?int $flags = null) : array;

    /**
     * @return string[]
     */
    public function getPreviousMessagesLines(\Throwable $throwable, ?int $flags = null) : array;


    /**
     * @template-covariant T of \Throwable
     *
     * @param \Throwable      $throwable
     * @param class-string<T> $throwableClass
     *
     * @return T|null
     */
    public function catchPrevious(\Throwable $throwable, string $throwableClass = '') : ?\Throwable;


    public function getThrowableMessage(\Throwable $throwable, ?int $flags = null) : string;

    /**
     * @return string[]
     */
    public function getThrowableMessageLines(\Throwable $throwable, ?int $flags = null) : array;


    /**
     * @return string[]
     */
    public function getThrowableMessages(\Throwable $throwable, ?int $flags = null) : array;

    /**
     * @return string[]
     */
    public function getThrowableMessagesLines(\Throwable $throwable, ?int $flags = null) : array;


    public function getThrowableInfo(\Throwable $throwable, ?int $flags = null) : array;

    /**
     * @return string[]
     */
    public function getThrowableInfoLines(\Throwable $throwable, ?int $flags = null) : array;


    public function getThrowableTrace(\Throwable $e, ?int $flags = null) : array;

    /**
     * @return string[]
     */
    public function getThrowableTraceLines(\Throwable $throwable, ?int $flags = null) : array;
}
