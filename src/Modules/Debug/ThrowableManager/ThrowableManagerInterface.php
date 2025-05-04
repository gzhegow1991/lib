<?php

namespace Gzhegow\Lib\Modules\Debug\ThrowableManager;

interface ThrowableManagerInterface
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
    public function getPreviousMessageList(\Throwable $throwable, array $options = []) : array;

    /**
     * @return string[]
     */
    public function getPreviousMessageLines(\Throwable $throwable, array $options = []) : array;


    /**
     * @return string[]
     */
    public function getPreviousMessagesList(\Throwable $throwable, array $options = []) : array;

    /**
     * @return string[]
     */
    public function getPreviousMessagesLines(\Throwable $throwable, array $options = []) : array;


    /**
     * @template-covariant T of \Throwable
     *
     * @param \Throwable      $throwable
     * @param class-string<T> $throwableClass
     *
     * @return T|null
     */
    public function catchPrevious(\Throwable $throwable, string $throwableClass = '') : ?\Throwable;


    public function getThrowableMessage(\Throwable $throwable, array $options = []) : string;

    /**
     * @return string[]
     */
    public function getThrowableMessageLines(\Throwable $throwable, array $options = []) : array;


    /**
     * @return string[]
     */
    public function getThrowableMessages(\Throwable $throwable, array $options = []) : array;

    /**
     * @return string[]
     */
    public function getThrowableMessagesLines(\Throwable $throwable, array $options = []) : array;


    public function getThrowableInfo(\Throwable $throwable, array $options = []) : array;

    /**
     * @return string[]
     */
    public function getThrowableInfoLines(\Throwable $throwable, array $options = []) : array;


    public function getThrowableTrace(\Throwable $e, array $options = []) : array;

    /**
     * @return string[]
     */
    public function getThrowableTraceLines(\Throwable $throwable, array $options = []) : array;
}
