<?php

/**
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */

namespace Gzhegow\Lib\Modules\Http\Session;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Exception\Runtime\ComposerException;
use Gzhegow\Lib\Modules\Http\Session\SessionDisabler\SessionDisabler;


class DefaultSession implements SessionInterface
{
    const SYMFONY_SESSION_INTERFACE = '\Symfony\Component\HttpFoundation\Session\SessionInterface';
    const SYMFONY_SESSION_CLASS     = '\Symfony\Component\HttpFoundation\Session\Session';


    /**
     * @var bool
     */
    protected $disableNativeSession = false;
    /**
     * @var bool
     */
    protected $useNativeSession = false;

    /**
     * @var bool
     */
    protected $useSymfonySession = false;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $symfonySession;
    /**
     * @see $_SESSION
     *
     * @var array
     */
    protected $sessionNative;


    private function __construct()
    {
        $isSessionDisabled = (PHP_SESSION_DISABLED === session_status());

        $this->disableNativeSession($isSessionDisabled);
    }


    /**
     * @param null|object|\Symfony\Component\HttpFoundation\Session\SessionInterface $symfonySession
     *
     * @return static
     */
    public function withSymfonySession(?object $symfonySession)
    {
        if (null !== $symfonySession) {
            if (! is_a($symfonySession, $interface = static::SYMFONY_SESSION_INTERFACE)) {
                throw new RuntimeException(
                    [
                        'The `symfonySession` should be an instance of: ' . $interface,
                        $symfonySession,
                    ]
                );
            }
        }

        $this->symfonySession = $symfonySession;

        $this->useSymfonySession = true;

        return $this;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    public function newSymfonySession(...$args) : object
    {
        $commands = [
            'composer require symfony/http-foundation',
        ];

        if (! class_exists($symfonySessionClass = static::SYMFONY_SESSION_CLASS)) {
            throw new ComposerException([
                ''
                . 'Please, run following commands: '
                . '[ ' . implode(' ][ ', $commands) . ' ]',
            ]);
        }

        return new $symfonySessionClass(...$args);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    public function getSymfonySession() : object
    {
        return $this->symfonySession;
    }


    /**
     * @return static
     */
    public function disableNativeSession(?bool $disableNativeSession = null)
    {
        if (isset($_SESSION) && is_object($_SESSION)) {
            return $this;
        }

        if (null !== $this->symfonySession) {
            throw new RuntimeException(
                [ 'Unable to disable native PHP session due to Symfony uses $_SESSION for its own needs' ]
            );
        }

        if (null !== $this->sessionNative) {
            throw new RuntimeException(
                [ 'Unable to disable native PHP session due to it is already selected to use' ]
            );
        }

        $disableNativeSession = $disableNativeSession ?? true;

        $this->disableNativeSession = $disableNativeSession;

        if ($disableNativeSession) {
            $sessionDisablerObject = SessionDisabler::new();

            $_SESSION =& $sessionDisablerObject;
        }

        return $this;
    }

    /**
     * @return static
     */
    public function useNativeSession(?bool $useNativeSession = null)
    {
        if (null !== $this->sessionNative) {
            return $this;
        }

        if ($this->disableNativeSession) {
            throw new RuntimeException(
                [ 'Unable to use native PHP session due to it is disabled before' ]
            );
        }

        if (null !== $this->symfonySession) {
            throw new RuntimeException(
                [ 'Unable to use native PHP session due to Symfony Session object is bound' ]
            );
        }

        $this->useNativeSession = $useNativeSession ?? true;

        $sessionStatus = session_status();

        if (PHP_SESSION_DISABLED === $sessionStatus) {
            throw new RuntimeException(
                [ 'The native PHP session is disabled' ]
            );
        }

        $headersSent = headers_sent();

        if (! $headersSent) {
            if (PHP_SESSION_NONE === $sessionStatus) {
                $theHttp = Lib::http();

                session_start($theHttp->staticSessionOptions());
            }
        }

        $_SESSION = $_SESSION ?? [];

        $this->sessionNative =& $_SESSION;

        return $this;
    }


    /**
     * @param string $refValue
     */
    public function has(string $key, &$refValue = null) : bool
    {
        $refValue = null;

        if ($this->useSymfonySession) {
            if ($this->symfonySession->has($key)) {
                $refValue = $this->symfonySession->get($key);

                return true;
            }

        } else {
            if (isset($this->sessionNative[ $key ])) {
                $refValue = $this->sessionNative[ $key ];

                return true;
            }
        }

        return false;
    }

    public function get(string $key) : string
    {
        $this->has($key, $result);

        return $result;
    }

    /**
     * @return static
     */
    public function set(string $key, string $value)
    {
        if ($this->useSymfonySession) {
            $this->symfonySession->set($key, $value);

        } else {
            $this->useNativeSession();

            $this->sessionNative[ $key ] = $value;
        }

        return $this;
    }

    /**
     * @return static
     */
    public function unset(string $key)
    {
        if ($this->useSymfonySession) {
            $this->symfonySession->remove($key);

        } else {
            $this->useNativeSession();

            if (isset($this->sessionNative[ $key ])) {
                unset($this->sessionNative[ $key ]);
            }
        }

        return $this;
    }


    /**
     * @return static
     */
    public static function getInstance()
    {
        return static::$instance = static::$instance ?? new static();
    }

    protected static $instance;
}
