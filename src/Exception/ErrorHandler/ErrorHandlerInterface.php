<?php

namespace Gzhegow\Lib\Exception\ErrorHandler;

interface ErrorHandlerInterface
{
    /**
     * @return static
     */
    public function setDirRoot(?string $dirRoot);


    /**
     * @return int|null
     */
    public function getPhpErrorReporting();

    /**
     * @return static
     */
    public function setErrorReporting(?int $errorReporting = -1);

    /**
     * @return static
     */
    public function useErrorReporting(&$last = null);


    /**
     * @return callable|null
     */
    public function getPhpErrorHandler();

    /**
     * @return callable|null
     */
    public function getErrorHandler();

    /**
     * @return static
     * @var callable $fnErrorHandler
     *
     */
    public function setErrorHandler($fnErrorHandler = '');

    /**
     * @param callable|null $last
     *
     * @return static
     */
    public function useErrorHandler(&$last = null);


    /**
     * @return callable|null
     */
    public function getPhpExceptionHandler();

    /**
     * @return callable|null
     */
    public function getExceptionHandler();

    /**
     * @return static
     * @var callable $fnExceptionHandler
     *
     */
    public function setExceptionHandler($fnExceptionHandler = '');

    /**
     * @param callable|null $last
     *
     * @return static
     */
    public function useExceptionHandler(&$last = null);


    /**
     * @throws \ErrorException
     */
    public function fnErrorHandler($errno, $errstr, $errfile, $errline) : void;

    public function fnExceptionHandler(\Throwable $throwable) : void;
}
