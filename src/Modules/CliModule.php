<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;


class CliModule
{
    // public function __construct()
    // {
    // }

    public function __initialize()
    {
        return $this;
    }


    public function pause($var = null, ...$vars)
    {
        $theDebug = Lib::debug();
        $thePhp = Lib::php();
        $theType = Lib::type();

        $theType->is_sapi_terminal()->orThrow();

        if ( null !== $var ) {
            $theDebugDumper = $theDebug->dumper();

            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
            $theDebugDumper->d($trace, $var, ...$vars);
        }

        echo '> Press ENTER to continue...' . "\n";
        $h = $thePhp->stdin();
        fgets($h);

        return $var;
    }

    public function stop(...$vars) : void
    {
        $theType = Lib::type();

        $theType->is_sapi_terminal()->orThrow();

        $this->pause(...$vars);

        exit(1);
    }


    public function readln() : string
    {
        $thePhp = Lib::php();
        $theType = Lib::type();

        $theType->is_sapi_terminal()->orThrow();

        $h = $thePhp->stdin();

        $line = trim(fgets($h));

        return $line;
    }

    public function cin(?string $delimiter = null) : string
    {
        $thePhp = Lib::php();
        $theStr = Lib::str();
        $theType = Lib::type();

        $theType->is_sapi_terminal()->orThrow();

        $delimiter = $delimiter ?? '```';

        echo '> Enter text separating lines by pressing ENTER' . "\n";
        echo '> Write when you\'re done: ' . $delimiter . "\n";

        $fnStrlen = $theStr->mb_func('strlen');
        $fnStrrpos = $theStr->mb_func('strrpos');
        $fnSubstr = $theStr->mb_func('substr');

        $lines = [];
        $h = $thePhp->stdin();
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
        $theType = Lib::type();

        $theType->is_sapi_terminal()->orThrow();

        $refAnswer = $refAnswer ?? 'n';

        $isYes = ('y' === $refAnswer) || ('yy' === $refAnswer);
        $isAll = ('nn' === $refAnswer) || ('yy' === $refAnswer);

        if ( ! $isAll ) {
            if ( ! $isYes ) {
                $accepted = [ 'yy', 'y', 'n', 'nn' ];

                echo $message . ' [' . implode('/', $accepted) . ']' . "\n";

                do {
                    echo 'Please enter one of: [' . implode('/', $accepted) . ']';

                    $passed = $this->readln();

                    if ( in_array($passed, $accepted) ) {
                        break;
                    }
                } while ( true );

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
