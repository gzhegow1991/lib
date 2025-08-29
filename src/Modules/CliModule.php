<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Cli\Process\DefaultProcessManager;
use Gzhegow\Lib\Modules\Cli\Process\ProcessManagerInterface;


class CliModule
{
    /**
     * @var ProcessManagerInterface
     */
    protected $processManager;


    public function newProcessManager() : ProcessManagerInterface
    {
        return new DefaultProcessManager();
    }

    public function cloneProcessManager() : ProcessManagerInterface
    {
        return clone $this->processManager();
    }

    public function processManager(?ProcessManagerInterface $processManager = null) : ProcessManagerInterface
    {
        return $this->processManager = null
            ?? $processManager
            ?? $this->processManager
            ?? $this->newProcessManager();
    }


    public function is_symlink(string $symlink) : bool
    {
        return false
            || is_link($symlink)
            || $this->is_junction($symlink);
    }

    public function is_junction(string $junction) : bool
    {
        // https://github.com/composer/composer/blob/main/src/Composer/Util/Filesystem.php#L807

        $thePhp = Lib::php();

        if ( ! $thePhp->is_windows() ) {
            return false;
        }

        clearstatcache(true, $junction);

        if ( ! is_dir($junction) ) {
            return false;
        }

        if ( is_link($junction) ) {
            return false;
        }

        $stat = lstat($junction);

        // S_ISDIR test (S_IFDIR is 0x4000, S_IFMT is 0xF000 bitmask)
        $result = is_array($stat)
            && 0x4000 !== ($stat['mode'] & 0xF000);

        return $result;
    }


    /**
     * @return resource
     */
    public function stdin()
    {
        if ( ! defined('STDIN') ) define('STDIN', fopen('php://stdin', 'rb'));

        return STDIN;
    }

    /**
     * @return resource
     */
    public function stdout()
    {
        if ( ! defined('STDOUT') ) define('STDOUT', fopen('php://stdout', 'wb'));

        return STDOUT;
    }

    /**
     * @return resource
     */
    public function stderr()
    {
        if ( ! defined('STDERR') ) define('STDERR', fopen('php://stderr', 'wb'));

        return STDERR;
    }


    public function pause($var = null, ...$vars)
    {
        $theDebug = Lib::debug();
        $thePhp = Lib::php();

        if ( ! $thePhp->is_terminal() ) {
            throw new RuntimeException('Function must be called only in CLI mode');
        }

        if ( null !== $var ) {
            $theDebugDumper = $theDebug->dumper();

            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
            $theDebugDumper->d($trace, $var, ...$vars);
        }

        echo '> Press ENTER to continue...' . "\n";
        $h = $this->stdin();
        fgets($h);

        return $var;
    }

    public function stop(...$vars) : void
    {
        $thePhp = Lib::php();

        if ( ! $thePhp->is_terminal() ) {
            throw new RuntimeException('Function must be called only in CLI mode');
        }

        $this->pause(...$vars);

        exit(1);
    }


    public function readln() : string
    {
        $thePhp = Lib::php();

        if ( ! $thePhp->is_terminal() ) {
            throw new RuntimeException('Function must be called only in CLI mode');
        }

        $h = $this->stdin();

        $line = trim(fgets($h));

        return $line;
    }

    public function cin(?string $delimiter = null) : string
    {
        $theStr = Lib::str();
        $thePhp = Lib::php();

        if ( ! $thePhp->is_terminal() ) {
            throw new RuntimeException('Function must be called only in CLI mode');
        }

        $delimiter = $delimiter ?? '```';

        echo '> Enter text separating lines by pressing ENTER' . "\n";
        echo '> Write when you\'re done: ' . $delimiter . "\n";

        $fnStrlen = $theStr->mb_func('strlen');
        $fnStrrpos = $theStr->mb_func('strrpos');
        $fnSubstr = $theStr->mb_func('substr');

        $lines = [];
        $h = $this->stdin();
        while ( false !== ($line = fgets($h)) ) {
            $line = trim($line);

            if ( '' === $line ) {
                echo '> Write `' . $delimiter . '` when done...' . "\n";

                continue;
            }

            $expected_pos = $fnStrlen($line) - $fnStrlen($delimiter);
            $pos = $fnStrrpos($line, $delimiter);

            // end found
            if ( $expected_pos === $pos ) {
                $line = $fnSubstr($line, 0, $pos);

                if ( $line ) {
                    $lines[] = $line;
                }

                break;

            } else {
                // end is not found
                $lines[] = $line;
            }
        }

        // results
        return implode("\n", $lines);
    }


    public function yes(string $message, ?string &$refAnswer = null) : bool
    {
        $thePhp = Lib::php();

        if ( ! $thePhp->is_terminal() ) {
            throw new RuntimeException('Function must be called only in CLI mode');
        }

        $refAnswer = $refAnswer ?? 'n';

        $isYes = ('y' === $refAnswer) || ('yy' === $refAnswer);
        $isAll = ('nn' === $refAnswer) || ('yy' === $refAnswer);

        if ( ! $isAll ) {
            if ( ! $isYes ) {
                $accepted = [ 'yy', 'y', 'n', 'nn' ];

                echo $message . ' [' . implode('/', $accepted) . ']' . "\n";

                while ( ! in_array($passed = $this->readln(), $accepted) ) {
                    echo 'Please enter one of: [' . implode('/', $accepted) . ']';
                }

                $refAnswer = $passed;

                $isYes = ('y' === $refAnswer) || ('yy' === $refAnswer);
                $isAll = ('nn' === $refAnswer) || ('yy' === $refAnswer);
            }

            if ( ! $isAll ) {
                $refAnswer = null;
            }
        }

        return $isYes;
    }
}
