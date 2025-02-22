<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;


class CliModule
{
    public function __construct()
    {
        if (in_array(\PHP_SAPI, [ 'cli', 'phpdbg' ])) {
            throw new RuntimeException('Module must be created in CLI mode');
        }
    }


    public function isWindows() : bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }


    public function isJunction(string $junction) : bool
    {
        // https://github.com/composer/composer/blob/main/src/Composer/Util/Filesystem.php#L807

        if (! $this->isWindows()) {
            return false;
        }

        clearstatcache(true, $junction);

        if (! is_dir($junction)) {
            return false;
        }

        if (is_link($junction)) {
            return false;
        }

        $stat = lstat($junction);

        // S_ISDIR test (S_IFDIR is 0x4000, S_IFMT is 0xF000 bitmask)
        $result = is_array($stat)
            && 0x4000 !== ($stat[ 'mode' ] & 0xF000);

        return $result;
    }

    public function isSymlink(string $symlink) : bool
    {
        return false
            || is_link($symlink)
            || $this->isJunction($symlink);
    }


    public function pause($var = null, ...$vars) // : mixed
    {
        if (null !== $var) {
            var_dump($var, ...$vars);
        }

        echo '> Press ENTER to continue...' . PHP_EOL;
        $h = fopen('php://stdin', 'r');
        fgets($h);
        fclose($h);

        return $var;
    }

    public function stop(...$vars) : void
    {
        $this->pause(...$vars);

        exit(1);
    }


    public function readln() : string
    {
        $h = fopen('php://stdin', 'r');
        $line = trim(fgets($h));
        fclose($h);

        return $line;
    }

    public function cin(string $delimiter = '```') : string
    {
        $theStr = Lib::str();

        echo '> Enter text separating lines by pressing ENTER' . PHP_EOL;
        echo '> Write when you\'re done: ' . $delimiter . PHP_EOL;

        $fnStrlen = $theStr->mb_func('strlen');
        $fnStrrpos = $theStr->mb_func('strrpos');
        $fnSubstr = $theStr->mb_func('substr');

        $lines = [];
        $h = fopen('php://stdin', 'r');
        while ( false !== ($line = fgets($h)) ) {
            $line = trim($line);

            if (! $line) {
                echo '> Write `' . $delimiter . '` when done...' . PHP_EOL;

                continue;
            }

            $expected_pos = $fnStrlen($line) - $fnStrlen($delimiter);
            $pos = $fnStrrpos($line, $delimiter);

            // end found
            if ($expected_pos === $pos) {
                $line = $fnSubstr($line, 0, $pos);

                if ($line) {
                    $lines[] = $line;
                }

                break;

            } else {
                // end is not found
                $lines[] = $line;
            }
        }
        fclose($h);

        // results
        return implode(PHP_EOL, $lines);
    }


    public function yes(string $message, string &$yesQuestion = null) : bool
    {
        $yesQuestion = $yesQuestion ?? 'n';

        $yes = ('y' === $yesQuestion) || ('yy' === $yesQuestion);
        $all = ('nn' === $yesQuestion) || ('yy' === $yesQuestion);

        if (! $all) {
            if (! $yes) {
                $accepted = [ 'yy', 'y', 'n', 'nn' ];

                echo $message . ' [' . implode('/', $accepted) . ']' . PHP_EOL;

                while ( ! in_array($passed = $this->readln(), $accepted) ) {
                    echo 'Please enter one of: [' . implode('/', $accepted) . ']';
                }

                $yesQuestion = $passed;

                $yes = ('y' === $yesQuestion) || ('yy' === $yesQuestion);
                $all = ('nn' === $yesQuestion) || ('yy' === $yesQuestion);
            }

            if (! $all) {
                $yesQuestion = null;
            }
        }

        return $yes;
    }
}
