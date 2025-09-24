<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Exception\LogicException;


class DateModule
{
    const INTERVAL_MINUTE = 60;
    const INTERVAL_HOUR   = 3600;
    const INTERVAL_DAY    = 86400;
    const INTERVAL_WEEK   = 604800;
    const INTERVAL_MONTH  = 2592000;
    const INTERVAL_YEAR   = 31536000;

    const FORMAT_SQL_DATE       = self::FORMAT_SQL_DATE_DAY;
    const FORMAT_SQL_DATE_YEAR  = 'Y-00-00';
    const FORMAT_SQL_DATE_MONTH = 'Y-m-00';
    const FORMAT_SQL_DATE_DAY   = 'Y-m-d';

    const FORMAT_SQL       = self::FORMAT_SQL_SEC;
    const FORMAT_SQL_YEAR  = 'Y-00-00 00:00:00';
    const FORMAT_SQL_MONTH = 'Y-m-00 00:00:00';
    const FORMAT_SQL_DAY   = 'Y-m-d 00:00:00';
    const FORMAT_SQL_HOUR  = 'Y-m-d H:00:00';
    const FORMAT_SQL_MIN   = 'Y-m-d H:i:00';
    const FORMAT_SQL_SEC   = 'Y-m-d H:i:s';
    const FORMAT_SQL_MSEC  = 'Y-m-d H:i:s.v';
    const FORMAT_SQL_USEC  = 'Y-m-d H:i:s.u';

    const FORMAT_SQL_TIME      = self::FORMAT_SQL_TIME_SEC;
    const FORMAT_SQL_TIME_HOUR = 'H:00:00';
    const FORMAT_SQL_TIME_MIN  = 'H:i:00';
    const FORMAT_SQL_TIME_SEC  = 'H:i:s';
    const FORMAT_SQL_TIME_MSEC = 'H:i:s.v';
    const FORMAT_SQL_TIME_USEC = 'H:i:s.u';

    const FORMAT_JAVASCRIPT                = self::FORMAT_JAVASCRIPT_SEC;
    const FORMAT_JAVASCRIPT_SEC            = "Y-m-d\TH:i:sP";
    const FORMAT_JAVASCRIPT_MSEC           = "Y-m-d\TH:i:s.vP";
    const FORMAT_JAVASCRIPT_USEC           = "Y-m-d\TH:i:s.uP";
    const FORMAT_JAVASCRIPT_NO_OFFSET      = self::FORMAT_JAVASCRIPT_NO_OFFSET_SEC;
    const FORMAT_JAVASCRIPT_NO_OFFSET_SEC  = "Y-m-d\TH:i:s";
    const FORMAT_JAVASCRIPT_NO_OFFSET_MSEC = "Y-m-d\TH:i:s.v";
    const FORMAT_JAVASCRIPT_NO_OFFSET_USEC = "Y-m-d\TH:i:s.u";
    const FORMAT_JAVASCRIPT_Z              = self::FORMAT_JAVASCRIPT_Z_SEC;
    const FORMAT_JAVASCRIPT_Z_SEC          = "Y-m-d\TH:i:s\Z";
    const FORMAT_JAVASCRIPT_Z_MSEC         = "Y-m-d\TH:i:s.v\Z";
    const FORMAT_JAVASCRIPT_Z_USEC         = "Y-m-d\TH:i:s.u\Z";

    const FORMAT_FILENAME_DATE       = self::FORMAT_FILENAME_DATE_DAY;
    const FORMAT_FILENAME_DATE_YEAR  = 'y0000';
    const FORMAT_FILENAME_DATE_MONTH = 'ym00';
    const FORMAT_FILENAME_DATE_DAY   = 'ymd';

    const FORMAT_FILENAME       = self::FORMAT_FILENAME_SEC;
    const FORMAT_FILENAME_YEAR  = 'y0000_000000';
    const FORMAT_FILENAME_MONTH = 'ym00_000000';
    const FORMAT_FILENAME_DAY   = 'ymd_000000';
    const FORMAT_FILENAME_HOUR  = 'ymd_H0000';
    const FORMAT_FILENAME_MIN   = 'ymd_Hi00';
    const FORMAT_FILENAME_SEC   = 'ymd_His';
    const FORMAT_FILENAME_MSEC  = 'ymd_His_v';
    const FORMAT_FILENAME_USEC  = 'ymd_His_u';

    const FORMAT_HUMAN_DATE = "D, d M Y P e";
    const FORMAT_HUMAN      = "D, d M Y H:i:s P e";


    const LIST_INTERVAL = [
        self::INTERVAL_MINUTE => true,
        self::INTERVAL_HOUR   => true,
        self::INTERVAL_DAY    => true,
        self::INTERVAL_WEEK   => true,
        self::INTERVAL_MONTH  => true,
        self::INTERVAL_YEAR   => true,
    ];

    const LIST_FORMAT = [
        self::FORMAT_SQL_DATE_YEAR  => true,
        self::FORMAT_SQL_DATE_MONTH => true,
        self::FORMAT_SQL_DATE_DAY   => true,

        self::FORMAT_SQL_YEAR  => true,
        self::FORMAT_SQL_MONTH => true,
        self::FORMAT_SQL_DAY   => true,
        self::FORMAT_SQL_HOUR  => true,
        self::FORMAT_SQL_MIN   => true,
        self::FORMAT_SQL_SEC   => true,
        self::FORMAT_SQL_MSEC  => true,
        self::FORMAT_SQL_USEC  => true,

        self::FORMAT_SQL_TIME_HOUR => true,
        self::FORMAT_SQL_TIME_MIN  => true,
        self::FORMAT_SQL_TIME_SEC  => true,
        self::FORMAT_SQL_TIME_MSEC => true,
        self::FORMAT_SQL_TIME_USEC => true,

        self::FORMAT_JAVASCRIPT_SEC  => true,
        self::FORMAT_JAVASCRIPT_MSEC => true,
        self::FORMAT_JAVASCRIPT_USEC => true,

        self::FORMAT_JAVASCRIPT_NO_OFFSET_SEC  => true,
        self::FORMAT_JAVASCRIPT_NO_OFFSET_MSEC => true,
        self::FORMAT_JAVASCRIPT_NO_OFFSET_USEC => true,

        self::FORMAT_FILENAME_DATE_YEAR  => true,
        self::FORMAT_FILENAME_DATE_MONTH => true,
        self::FORMAT_FILENAME_DATE_DAY   => true,

        self::FORMAT_FILENAME_YEAR  => true,
        self::FORMAT_FILENAME_MONTH => true,
        self::FORMAT_FILENAME_DAY   => true,
        self::FORMAT_FILENAME_HOUR  => true,
        self::FORMAT_FILENAME_MIN   => true,
        self::FORMAT_FILENAME_SEC   => true,
        self::FORMAT_FILENAME_MSEC  => true,
        self::FORMAT_FILENAME_USEC  => true,

        self::FORMAT_HUMAN_DATE => true,
        self::FORMAT_HUMAN      => true,
    ];


    public function the_timezone_nil() : \DateTimeZone
    {
        return new \DateTimeZone('+1234');
    }

    public function the_timezone_utc() : \DateTimeZone
    {
        return new \DateTimeZone('UTC');
    }


    /**
     * @return Ret<\DateTimeZone>
     */
    public function type_timezone($timezone, ?array $allowedTimezoneTypes = null)
    {
        $dateTimeZone = null;

        if ( $timezone instanceof \DateTimeZone ) {
            $dateTimeZone = $timezone;

        } elseif ( $timezone instanceof \DateTimeInterface ) {
            $dateTimeZone = $timezone->getTimezone();

        } else {
            try {
                $dateTimeZone = new \DateTimeZone($timezone);
            }
            catch ( \Throwable $e ) {
            }
        }

        if ( null === $dateTimeZone ) {
            return Ret::err(
                [ 'The `timezone` should be valid timezone', $timezone ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( null !== $allowedTimezoneTypes ) {
            $timezoneType = $this->timezone_type($dateTimeZone);

            if ( ! in_array($timezoneType, $allowedTimezoneTypes, true) ) {
                return Ret::err(
                    [ 'The `timezone` should be timezone of one of the allowed types', $timezone, $allowedTimezoneTypes ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        return Ret::val($dateTimeZone);
    }

    /**
     * @return Ret<\DateTimeZone>
     */
    public function type_timezone_offset($timezoneOrOffset)
    {
        $dateTimeZone = null;

        if ( $timezoneOrOffset instanceof \DateTimeZone ) {
            $dateTimeZone = $timezoneOrOffset;

        } elseif ( $timezoneOrOffset instanceof \DateTimeInterface ) {
            $dateTimeZone = $timezoneOrOffset->getTimezone();

        } else {
            try {
                $dateTimeZone = new \DateTimeZone($timezoneOrOffset);
            }
            catch ( \Throwable $e ) {
            }
        }

        if ( null === $dateTimeZone ) {
            return Ret::err(
                [ 'The `timezoneOrOffset` should be valid timezone', $timezoneOrOffset ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezoneType = $this->timezone_type($dateTimeZone);

        if ( $timezoneType !== 1 ) {
            return Ret::err(
                [ 'The `timezoneOrOffset` should be offset timezone', $timezoneOrOffset ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($dateTimeZone);
    }

    /**
     * @return Ret<\DateTimeZone>
     */
    public function type_timezone_abbr($timezoneOrAbbr)
    {
        $dateTimeZone = null;

        if ( $timezoneOrAbbr instanceof \DateTimeZone ) {
            $dateTimeZone = $timezoneOrAbbr;

        } elseif ( $timezoneOrAbbr instanceof \DateTimeInterface ) {
            $dateTimeZone = $timezoneOrAbbr->getTimezone();

        } else {
            try {
                $dateTimeZone = new \DateTimeZone($timezoneOrAbbr);
            }
            catch ( \Throwable $e ) {
            }
        }

        if ( null === $dateTimeZone ) {
            return Ret::err(
                [ 'The `timezoneOrAbbr` should be valid timezone', $timezoneOrAbbr ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezoneType = $this->timezone_type($dateTimeZone);

        if ( $timezoneType !== 2 ) {
            return Ret::err(
                [ 'The `timezoneOrAbbr` should be abbr timezone', $timezoneOrAbbr ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($dateTimeZone);
    }

    /**
     * @return Ret<\DateTimeZone>
     */
    public function type_timezone_name($timezoneOrName)
    {
        $dateTimeZone = null;

        if ( $timezoneOrName instanceof \DateTimeZone ) {
            $dateTimeZone = $timezoneOrName;

        } elseif ( $timezoneOrName instanceof \DateTimeInterface ) {
            $dateTimeZone = $timezoneOrName->getTimezone();

        } else {
            try {
                $dateTimeZone = new \DateTimeZone($timezoneOrName);
            }
            catch ( \Throwable $e ) {
            }
        }

        if ( null === $dateTimeZone ) {
            return Ret::err(
                [ 'The `timezoneOrName` should be valid timezone', $timezoneOrName ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezoneType = $this->timezone_type($dateTimeZone);

        if ( $timezoneType !== 3 ) {
            return Ret::err(
                [ 'The `timezoneOrName` should be name timezone', $timezoneOrName ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($dateTimeZone);
    }

    /**
     * @return Ret<\DateTimeZone>
     */
    public function type_timezone_nameabbr($timezoneOrNameOrAbbr)
    {
        $dateTimeZone = null;

        if ( $timezoneOrNameOrAbbr instanceof \DateTimeZone ) {
            $dateTimeZone = $timezoneOrNameOrAbbr;

        } elseif ( $timezoneOrNameOrAbbr instanceof \DateTimeInterface ) {
            $dateTimeZone = $timezoneOrNameOrAbbr->getTimezone();

        } else {
            try {
                $dateTimeZone = new \DateTimeZone($timezoneOrNameOrAbbr);
            }
            catch ( \Throwable $e ) {
            }
        }

        if ( null === $dateTimeZone ) {
            return Ret::err(
                [ 'The `timezoneOrNameOrAbbr` should be valid timezone', $timezoneOrNameOrAbbr ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezoneType = $this->timezone_type($dateTimeZone);

        if ( ! (
            ($timezoneType === 2)
            || ($timezoneType === 3)
        ) ) {
            return Ret::err(
                [ 'The `timezoneOrName` should be name or abbr timezone', $timezoneOrNameOrAbbr ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($dateTimeZone);
    }


    /**
     * @return Ret<\DateInterval>
     */
    public function type_interval($interval)
    {
        if ( $interval instanceof \DateInterval ) {
            return Ret::val($interval);
        }

        $ret = Ret::new();

        $dateInterval = null
            ?? $this->type_interval_duration($interval)->orNull($ret)
            ?? $this->type_interval_datestring($interval)->orNull($ret)
            //
            // > commented, autoparsing integers is bad practice
            // ?? $this->type_interval_microtime($interval)->orNull($ret)
        ;

        if ( $ret->isFail() ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($dateInterval);
    }

    /**
     * @return Ret<\DateInterval>
     */
    public function type_interval_duration($duration)
    {
        if ( $duration instanceof \DateInterval ) {
            return Ret::val($duration);
        }

        if ( ! (is_string($duration) && ('' !== $duration)) ) {
            return Ret::err(
                [ 'The `duration` should be string, not empty', $duration ],
                [ __FILE__, __LINE__ ]
            );
        }

        try {
            $dateInterval = $this->interval_decode($duration);
        }
        catch ( \Throwable $e ) {
            return Ret::err(
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($dateInterval);
    }

    /**
     * @return Ret<\DateInterval>
     */
    public function type_interval_datestring($datestring)
    {
        if ( $datestring instanceof \DateInterval ) {
            return Ret::val($datestring);
        }

        if ( ! (is_string($datestring) && ('' !== $datestring)) ) {
            return Ret::err(
                [ 'The `datestring` should be string, not empty', $datestring ],
                [ __FILE__, __LINE__ ]
            );
        }

        try {
            $dateInterval = \DateInterval::createFromDateString($datestring);

        }
        catch ( \Throwable $e ) {
            return Ret::err(
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( false !== $dateInterval ) {
            return Ret::val($dateInterval);
        }

        return Ret::err(
            [ 'Cannot encode `datestring` to intervel', $datestring ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<\DateInterval>
     */
    public function type_interval_microtime($microtime)
    {
        if ( $microtime instanceof \DateInterval ) {
            return Ret::val($microtime);
        }

        $theType = Lib::type();

        if ( ! $theType->numeric($microtime, false)->isOk([ 1 => &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $microtimeNumeric = $ret->getValue();

        try {
            $dateInterval = $this->interval_decode("PT{$microtimeNumeric}S");
        }
        catch ( \Throwable $e ) {
            return Ret::err(
                $e,
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($dateInterval);
    }

    /**
     * @return Ret<\DateInterval>
     */
    public function type_interval_ago($date, ?\DateTimeInterface $from = null, ?bool $reverse = null)
    {
        $reverse = $reverse ?? false;

        $isFrom = (false === $reverse);
        // $isUntil = (true === $reverse);

        if ( $date instanceof \DateInterval ) {
            return Ret::val($date);
        }

        if ( $date instanceof \DateTimeInterface ) {
            $fromDate = $from ?? new \DateTime('now');

            if ( $isFrom ) {
                $date = $fromDate->diff($date);

            } else {
                // } elseif ($isUntil) {
                $date = $date->diff($fromDate);
            }

            return Ret::val($date);
        }

        return Ret::err(
            [ 'The `date` should be interval', $date ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<\DateTimeInterface>
     */
    public function type_date($datestring, $timezoneFallback = null)
    {
        $theType = Lib::type();

        $dateTime = null;

        if ( $datestring instanceof \DateTimeInterface ) {
            $dateTime = $datestring;

        } else {
            if ( ! $theType->string_not_empty($datestring)->isOk([ &$datestringString, &$ret ]) ) {
                return Ret::err(
                    $ret,
                    [ __FILE__, __LINE__ ]
                );
            }

            $timezoneFallbackObject = null;
            if ( null !== $timezoneFallback ) {
                $timezoneFallbackObject = $this->type_timezone($timezoneFallback)->orThrow();
            }

            try {
                $dateTime = new \DateTime(
                    $datestringString,
                    $timezoneFallbackObject
                );
            }
            catch ( \Throwable $e ) {
            }
        }

        if ( null === $dateTime ) {
            return Ret::err(
                [ 'The `datestring` should be valid datestring', $datestring ],
                [ __FILE__, __LINE__ ]
            );
        }

        $dateTimeClone = $this->cloneToDate($dateTime);

        return Ret::val($dateTimeClone);
    }

    /**
     * @return Ret<\DateTime>
     */
    public function type_adate($datestring, $timezoneFallback = null)
    {
        $theType = Lib::type();

        $dateTime = null;

        if ( $datestring instanceof \DateTimeInterface ) {
            $dateTime = $datestring;

        } else {
            if ( ! $theType->string_not_empty($datestring)->isOk([ &$datestringString, &$ret ]) ) {
                return Ret::err(
                    $ret,
                    [ __FILE__, __LINE__ ]
                );
            }

            $timezoneFallbackObject = null;
            if ( null !== $timezoneFallback ) {
                $timezoneFallbackObject = $this->type_timezone($timezoneFallback)->orThrow();
            }

            try {
                $dateTime = new \DateTime(
                    $datestringString,
                    $timezoneFallbackObject
                );
            }
            catch ( \Throwable $e ) {
            }
        }

        if ( null === $dateTime ) {
            return Ret::err(
                [ 'The `datestring` should be valid datestring', $datestring ],
                [ __FILE__, __LINE__ ]
            );
        }

        $dateTimeClone = $this->cloneToADate($dateTime);

        return Ret::val($dateTimeClone);
    }

    /**
     * @return Ret<\DateTimeImmutable>
     */
    public function type_idate($datestring, $timezoneFallback = null)
    {
        $theType = Lib::type();

        $dateTimeImmutable = null;

        if ( $datestring instanceof \DateTimeInterface ) {
            $dateTimeImmutable = $datestring;

        } else {
            if ( ! $theType->string_not_empty($datestring)->isOk([ &$datestringString, &$ret ]) ) {
                return Ret::err(
                    $ret,
                    [ __FILE__, __LINE__ ]
                );
            }

            $timezoneFallbackObject = null;
            if ( null !== $timezoneFallback ) {
                $timezoneFallbackObject = $this->type_timezone($timezoneFallback)->orThrow();
            }

            try {
                $dateTimeImmutable = new \DateTimeImmutable(
                    $datestringString,
                    $timezoneFallbackObject
                );
            }
            catch ( \Throwable $e ) {
            }
        }

        if ( null === $dateTimeImmutable ) {
            return Ret::err(
                [ 'The `datestring` should be valid datestring', $datestring ],
                [ __FILE__, __LINE__ ]
            );
        }

        $dateTimeImmutableClone = $this->cloneToIDate($dateTimeImmutable);

        return Ret::val($dateTimeImmutableClone);
    }


    /**
     * @return Ret<\DateTimeInterface>
     */
    public function type_date_formatted($dateFormatted, $formats, $timezoneFallback = null)
    {
        $thePhp = Lib::php();
        $theType = Lib::type();

        $dateTime = null;

        if ( $dateFormatted instanceof \DateTimeInterface ) {
            $dateTime = $dateFormatted;

        } else {
            if ( ! $theType->string_not_empty($dateFormatted)->isOk([ &$dateFormattedString, &$ret ]) ) {
                return Ret::err(
                    $ret,
                    [ __FILE__, __LINE__ ]
                );
            }

            $formatsList = $thePhp->to_list($formats);
            $formatsList = $theType->array_not_empty($formatsList)->orThrow();

            foreach ( $formatsList as $i => $format ) {
                $formatValid = $theType->string_not_empty($format)->orThrow();

                $formatsList[$i] = $formatValid;
            }

            $timezoneFallbackObject = null;
            if ( null !== $timezoneFallback ) {
                $timezoneFallbackObject = $this->type_timezone($timezoneFallback)->orThrow();
            }

            foreach ( $formatsList as $format ) {
                try {
                    $dateTime = \DateTime::createFromFormat(
                        $format,
                        $dateFormattedString,
                        $timezoneFallbackObject
                    );
                }
                catch ( \Throwable $e ) {
                    $dateTime = false;
                }

                if ( false !== $dateTime ) {
                    break;
                }
            }

            if ( false === $dateTime ) {
                $dateTime = null;
            }
        }

        if ( null === $dateTime ) {
            return Ret::err(
                [ 'The `dateFormatted` should be valid date of passed format', $dateFormatted, $formats ],
                [ __FILE__, __LINE__ ]
            );
        }

        $dateTimeClone = $this->cloneToDate($dateTime);

        return Ret::val($dateTimeClone);
    }

    /**
     * @return Ret<\DateTime>
     */
    public function type_adate_formatted($dateFormatted, $formats, $timezoneFallback = null)
    {
        $thePhp = Lib::php();
        $theType = Lib::type();

        $dateTime = null;

        if ( $dateFormatted instanceof \DateTimeInterface ) {
            $dateTime = $dateFormatted;

        } else {
            if ( ! $theType->string_not_empty($dateFormatted)->isOk([ &$dateFormattedString, &$ret ]) ) {
                return Ret::err(
                    $ret,
                    [ __FILE__, __LINE__ ]
                );
            }

            $formatsList = $thePhp->to_list($formats);
            $formatsList = $theType->array_not_empty($formatsList)->orThrow();

            foreach ( $formatsList as $i => $format ) {
                $formatValid = $theType->string_not_empty($format)->orThrow();

                $formatsList[$i] = $formatValid;
            }

            $timezoneFallbackObject = null;
            if ( null !== $timezoneFallback ) {
                $timezoneFallbackObject = $this->type_timezone($timezoneFallback)->orThrow();
            }

            foreach ( $formatsList as $format ) {
                try {
                    $dateTime = \DateTime::createFromFormat(
                        $format,
                        $dateFormattedString,
                        $timezoneFallbackObject
                    );
                }
                catch ( \Throwable $e ) {
                    $dateTime = false;
                }

                if ( false !== $dateTime ) {
                    break;
                }
            }

            if ( false === $dateTime ) {
                $dateTime = null;
            }
        }

        if ( null === $dateTime ) {
            return Ret::err(
                [ 'The `dateFormatted` should be valid date of passed format', $dateFormatted, $formats ],
                [ __FILE__, __LINE__ ]
            );
        }

        $dateTimeClone = $this->cloneToADate($dateTime);

        return Ret::val($dateTimeClone);
    }

    /**
     * @return Ret<\DateTimeImmutable>
     */
    public function type_idate_formatted($dateFormatted, $formats, $timezoneFallback = null)
    {
        $thePhp = Lib::php();
        $theType = Lib::type();

        $dateTimeImmutable = null;

        if ( $dateFormatted instanceof \DateTimeInterface ) {
            $dateTimeImmutable = $dateFormatted;

        } else {
            if ( ! $theType->string_not_empty($dateFormatted)->isOk([ &$dateFormattedString, &$ret ]) ) {
                return Ret::err(
                    $ret,
                    [ __FILE__, __LINE__ ]
                );
            }

            $formatsList = $thePhp->to_list($formats);
            $formatsList = $theType->array_not_empty($formatsList)->orThrow();

            foreach ( $formatsList as $i => $format ) {
                $formatValid = $theType->string_not_empty($format)->orThrow();

                $formatsList[$i] = $formatValid;
            }

            $timezoneFallbackObject = null;
            if ( null !== $timezoneFallback ) {
                $timezoneFallbackObject = $this->type_timezone($timezoneFallback)->orThrow();
            }

            foreach ( $formatsList as $format ) {
                try {
                    $dateTimeImmutable = \DateTimeImmutable::createFromFormat(
                        $format,
                        $dateFormattedString,
                        $timezoneFallbackObject
                    );
                }
                catch ( \Throwable $e ) {
                    $dateTimeImmutable = false;
                }

                if ( false !== $dateTimeImmutable ) {
                    break;
                }
            }

            if ( false === $dateTimeImmutable ) {
                $dateTimeImmutable = null;
            }
        }

        if ( null === $dateTimeImmutable ) {
            return Ret::err(
                [ 'The `dateFormatted` should be valid date of passed format', $dateFormatted, $formats ],
                [ __FILE__, __LINE__ ]
            );
        }

        $dateTimeImmutableClone = $this->cloneToIDate($dateTimeImmutable);

        return Ret::val($dateTimeImmutableClone);
    }


    /**
     * @return Ret<\DateTimeInterface>
     */
    public function type_date_tz($datestring, ?array $allowedTimezoneTypes = null)
    {
        $theType = Lib::type();

        $dateTime = null;

        $timezoneNil = $this->the_timezone_nil();

        if ( $datestring instanceof \DateTimeInterface ) {
            $dateTime = $datestring;

        } else {
            if ( ! $theType->string_not_empty($datestring)->isOk([ &$datestringString, &$ret ]) ) {
                return Ret::err(
                    $ret,
                    [ __FILE__, __LINE__ ]
                );
            }

            try {
                $dateTime = new \DateTime(
                    $datestringString,
                    $timezoneNil
                );
            }
            catch ( \Throwable $e ) {
            }
        }

        if ( null === $dateTime ) {
            return Ret::err(
                [ 'The `datestring` should be valid date or datestring', $datestring ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezone = $dateTime->getTimezone();

        if ( $timezone->getName() === $timezoneNil->getName() ) {
            return Ret::err(
                [ 'The `datestring` should be date with timezone', $datestring ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( null !== $allowedTimezoneTypes ) {
            $timezoneType = $this->timezone_type($timezone);

            if ( ! in_array($timezoneType, $allowedTimezoneTypes, true) ) {
                return Ret::err(
                    [ 'The `datestring` should be date with timezone of one of the allowed types', $datestring, $allowedTimezoneTypes ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        $dateTimeClone = $this->cloneToDate($dateTime);

        return Ret::val($dateTimeClone);
    }

    /**
     * @return Ret<\DateTime>
     */
    public function type_adate_tz($datestring, ?array $allowedTimezoneTypes = null)
    {
        $theType = Lib::type();

        $dateTime = null;

        $timezoneNil = $this->the_timezone_nil();

        if ( $datestring instanceof \DateTimeInterface ) {
            $dateTime = $datestring;

        } else {
            if ( ! $theType->string_not_empty($datestring)->isOk([ &$datestringString, &$ret ]) ) {
                return Ret::err(
                    $ret,
                    [ __FILE__, __LINE__ ]
                );
            }

            try {
                $dateTime = new \DateTime(
                    $datestringString,
                    $timezoneNil
                );
            }
            catch ( \Throwable $e ) {
            }
        }

        if ( null === $dateTime ) {
            return Ret::err(
                [ 'The `datestring` should be valid date or datestring', $datestring ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezone = $dateTime->getTimezone();

        if ( $timezone->getName() === $timezoneNil->getName() ) {
            return Ret::err(
                [ 'The `datestring` should be date with timezone', $datestring ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( null !== $allowedTimezoneTypes ) {
            $timezoneType = $this->timezone_type($timezone);

            if ( ! in_array($timezoneType, $allowedTimezoneTypes, true) ) {
                return Ret::err(
                    [ 'The `datestring` should be date with timezone of one of the allowed types', $datestring, $allowedTimezoneTypes ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        $dateTimeClone = $this->cloneToADate($dateTime);

        return Ret::val($dateTimeClone);
    }

    /**
     * @return Ret<\DateTimeImmutable>
     */
    public function type_idate_tz($datestring, ?array $allowedTimezoneTypes = null)
    {
        $theType = Lib::type();

        $dateTimeImmutable = null;

        $timezoneNil = $this->the_timezone_nil();

        if ( $datestring instanceof \DateTimeInterface ) {
            $dateTimeImmutable = $datestring;

        } else {
            if ( ! $theType->string_not_empty($datestring)->isOk([ &$datestringString, &$ret ]) ) {
                return Ret::err(
                    $ret,
                    [ __FILE__, __LINE__ ]
                );
            }

            try {
                $dateTimeImmutable = new \DateTimeImmutable(
                    $datestringString,
                    $timezoneNil
                );
            }
            catch ( \Throwable $e ) {
            }
        }

        if ( null === $dateTimeImmutable ) {
            return Ret::err(
                [ 'The `datestring` should be valid date or datestring', $datestring ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezone = $dateTimeImmutable->getTimezone();

        if ( $timezone->getName() === $timezoneNil->getName() ) {
            return Ret::err(
                [ 'The `datestring` should be date with timezone', $datestring ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( null !== $allowedTimezoneTypes ) {
            $timezoneType = $this->timezone_type($timezone);

            if ( ! in_array($timezoneType, $allowedTimezoneTypes, true) ) {
                return Ret::err(
                    [ 'The `datestring` should be date with timezone of one of the allowed types', $datestring, $allowedTimezoneTypes ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        $dateTimeClone = $this->cloneToDate($dateTimeImmutable);

        return Ret::val($dateTimeClone);
    }


    /**
     * @return Ret<\DateTimeInterface>
     */
    public function type_date_tz_formatted($dateFormatted, $formats, ?array $allowedTimezoneTypes = null)
    {
        $thePhp = Lib::php();
        $theType = Lib::type();

        $dateTime = null;

        $timezoneNil = $this->the_timezone_nil();

        if ( $dateFormatted instanceof \DateTimeInterface ) {
            $dateTime = $dateFormatted;

        } else {
            if ( ! $theType->string_not_empty($dateFormatted)->isOk([ &$dateFormattedString, &$ret ]) ) {
                return Ret::err(
                    $ret,
                    [ __FILE__, __LINE__ ]
                );
            }

            $formatsList = $thePhp->to_list($formats);
            $formatsList = $theType->array_not_empty($formatsList)->orThrow();

            foreach ( $formatsList as $i => $format ) {
                $formatValid = $theType->string_not_empty($format)->orThrow();

                $formatsList[$i] = $formatValid;
            }

            foreach ( $formatsList as $format ) {
                try {
                    $dateTime = \DateTime::createFromFormat(
                        $format,
                        $dateFormattedString,
                        $timezoneNil
                    );
                }
                catch ( \Throwable $e ) {
                    $dateTime = false;
                }

                if ( false !== $dateTime ) {
                    break;
                }
            }

            if ( false === $dateTime ) {
                $dateTime = null;
            }
        }

        if ( null === $dateTime ) {
            return Ret::err(
                [ 'The `dateFormatted` should be valid date of passed format', $dateFormatted, $formats ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezone = $dateTime->getTimezone();

        if ( $timezone->getName() === $timezoneNil->getName() ) {
            return Ret::err(
                [ 'The `dateFormatted` should be date with timezone', $dateFormatted ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( null !== $allowedTimezoneTypes ) {
            $timezoneType = $this->timezone_type($timezone);

            if ( ! in_array($timezoneType, $allowedTimezoneTypes, true) ) {
                return Ret::err(
                    [ 'The `dateFormatted` should be date with timezone of one of the allowed types', $dateFormatted, $allowedTimezoneTypes ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        $dateTimeClone = $this->cloneToDate($dateTime);

        return Ret::val($dateTimeClone);
    }

    /**
     * @return Ret<\DateTime>
     */
    public function type_adate_tz_formatted($dateFormatted, $formats, ?array $allowedTimezoneTypes = null)
    {
        $thePhp = Lib::php();
        $theType = Lib::type();

        $dateTime = null;

        $timezoneNil = $this->the_timezone_nil();

        if ( $dateFormatted instanceof \DateTimeInterface ) {
            $dateTime = $dateFormatted;

        } else {
            if ( ! $theType->string_not_empty($dateFormatted)->isOk([ &$dateFormattedString, &$ret ]) ) {
                return Ret::err(
                    $ret,
                    [ __FILE__, __LINE__ ]
                );
            }

            $formatsList = $thePhp->to_list($formats);
            $formatsList = $theType->array_not_empty($formatsList)->orThrow();

            foreach ( $formatsList as $i => $format ) {
                $formatValid = $theType->string_not_empty($format)->orThrow();

                $formatsList[$i] = $formatValid;
            }

            foreach ( $formatsList as $format ) {
                try {
                    $dateTime = \DateTime::createFromFormat(
                        $format,
                        $dateFormattedString,
                        $timezoneNil
                    );
                }
                catch ( \Throwable $e ) {
                    $dateTime = false;
                }

                if ( false !== $dateTime ) {
                    break;
                }
            }

            if ( false === $dateTime ) {
                $dateTime = null;
            }
        }

        if ( null === $dateTime ) {
            return Ret::err(
                [ 'The `dateFormatted` should be valid date of passed format', $dateFormatted, $formats ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezone = $dateTime->getTimezone();

        if ( $timezone->getName() === $timezoneNil->getName() ) {
            return Ret::err(
                [ 'The `dateFormatted` should be date with timezone', $dateFormatted ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( null !== $allowedTimezoneTypes ) {
            $timezoneType = $this->timezone_type($timezone);

            if ( ! in_array($timezoneType, $allowedTimezoneTypes, true) ) {
                return Ret::err(
                    [ 'The `dateFormatted` should be date with timezone of one of the allowed types', $dateFormatted, $allowedTimezoneTypes ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        $dateTimeClone = $this->cloneToADate($dateTime);

        return Ret::val($dateTimeClone);
    }

    /**
     * @return Ret<\DateTimeImmutable>
     */
    public function type_idate_tz_formatted($dateFormatted, $formats, ?array $allowedTimezoneTypes = null)
    {
        $thePhp = Lib::php();
        $theType = Lib::type();

        $dateTimeImmutable = null;

        $timezoneNil = $this->the_timezone_nil();

        if ( $dateFormatted instanceof \DateTimeInterface ) {
            $dateTimeImmutable = $dateFormatted;

        } else {
            if ( ! $theType->string_not_empty($dateFormatted)->isOk([ &$dateFormattedString, &$ret ]) ) {
                return Ret::err(
                    $ret,
                    [ __FILE__, __LINE__ ]
                );
            }

            $formatsList = $thePhp->to_list($formats);
            $formatsList = $theType->array_not_empty($formatsList)->orThrow();

            foreach ( $formatsList as $i => $format ) {
                $formatValid = $theType->string_not_empty($format)->orThrow();

                $formatsList[$i] = $formatValid;
            }

            foreach ( $formatsList as $format ) {
                try {
                    $dateTimeImmutable = \DateTimeImmutable::createFromFormat(
                        $format,
                        $dateFormattedString,
                        $timezoneNil
                    );
                }
                catch ( \Throwable $e ) {
                    $dateTimeImmutable = false;
                }

                if ( false !== $dateTimeImmutable ) {
                    break;
                }
            }

            if ( false === $dateTimeImmutable ) {
                $dateTimeImmutable = null;
            }
        }

        if ( null === $dateTimeImmutable ) {
            return Ret::err(
                [ 'The `dateFormatted` should be valid date of passed format', $dateFormatted, $formats ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezone = $dateTimeImmutable->getTimezone();

        if ( $timezone->getName() === $timezoneNil->getName() ) {
            return Ret::err(
                [ 'The `dateFormatted` should be date with timezone', $dateFormatted ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( null !== $allowedTimezoneTypes ) {
            $timezoneType = $this->timezone_type($timezone);

            if ( ! in_array($timezoneType, $allowedTimezoneTypes, true) ) {
                return Ret::err(
                    [ 'The `dateFormatted` should be date with timezone of one of the allowed types', $dateFormatted, $allowedTimezoneTypes ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        $dateTimeImmutableClone = $this->cloneToIDate($dateTimeImmutable);

        return Ret::val($dateTimeImmutableClone);
    }


    /**
     * @return Ret<\DateTimeInterface>
     */
    public function type_date_no_tz($datestring, $timezoneFallback = null)
    {
        $timezoneFallback = $timezoneFallback ?? $this->the_timezone_utc();

        $theType = Lib::type();

        $dateTime = null;

        if ( ! $theType->string_not_empty($datestring)->isOk([ &$datestringString, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $timezoneNil = $this->the_timezone_nil();

        try {
            $dateTime = new \DateTime(
                $datestringString,
                $timezoneNil
            );
        }
        catch ( \Throwable $e ) {
        }

        if ( null === $dateTime ) {
            return Ret::err(
                [ 'The `datestring` should be valid date or datestring', $datestring ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezone = $dateTime->getTimezone();

        if ( $timezone->getName() !== $timezoneNil->getName() ) {
            return Ret::err(
                [ 'The `datestring` should be date without timezone', $datestring ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezoneFallbackObject = $this->type_timezone($timezoneFallback)->orThrow();

        try {
            $dateTime = new \DateTime(
                $datestringString,
                $timezoneFallbackObject
            );
        }
        catch ( \Throwable $e ) {
        }

        $dateTimeClone = $this->cloneToDate($dateTime);

        return Ret::val($dateTimeClone);
    }

    /**
     * @return Ret<\DateTime>
     */
    public function type_adate_no_tz($datestring, $timezoneFallback = null)
    {
        $timezoneFallback = $timezoneFallback ?? $this->the_timezone_utc();

        $theType = Lib::type();

        $dateTime = null;

        if ( ! $theType->string_not_empty($datestring)->isOk([ &$datestringString, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $timezoneNil = $this->the_timezone_nil();

        try {
            $dateTime = new \DateTime(
                $datestringString,
                $timezoneNil
            );
        }
        catch ( \Throwable $e ) {
        }

        if ( null === $dateTime ) {
            return Ret::err(
                [ 'The `datestring` should be valid date or datestring', $datestring ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezone = $dateTime->getTimezone();

        if ( $timezone->getName() !== $timezoneNil->getName() ) {
            return Ret::err(
                [ 'The `datestring` should be date without timezone', $datestring ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezoneFallbackObject = $this->type_timezone($timezoneFallback)->orThrow();

        try {
            $dateTime = new \DateTime(
                $datestringString,
                $timezoneFallbackObject
            );
        }
        catch ( \Throwable $e ) {
        }

        $dateTimeClone = $this->cloneToADate($dateTime);

        return Ret::val($dateTimeClone);
    }

    /**
     * @return Ret<\DateTimeImmutable>
     */
    public function type_idate_no_tz($datestring, $timezoneFallback = null)
    {
        $timezoneFallback = $timezoneFallback ?? $this->the_timezone_utc();

        $theType = Lib::type();

        $dateTimeImmutable = null;

        if ( ! $theType->string_not_empty($datestring)->isOk([ &$datestringString, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $timezoneNil = $this->the_timezone_nil();

        try {
            $dateTimeImmutable = new \DateTimeImmutable(
                $datestringString,
                $timezoneNil
            );
        }
        catch ( \Throwable $e ) {
        }

        if ( null === $dateTimeImmutable ) {
            return Ret::err(
                [ 'The `datestring` should be valid date or datestring', $datestring ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezone = $dateTimeImmutable->getTimezone();

        if ( $timezone->getName() !== $timezoneNil->getName() ) {
            return Ret::err(
                [ 'The `datestring` should be date without timezone', $datestring ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezoneFallbackObject = $this->type_timezone($timezoneFallback)->orThrow();

        try {
            $dateTimeImmutable = new \DateTimeImmutable(
                $datestringString,
                $timezoneFallbackObject
            );
        }
        catch ( \Throwable $e ) {
        }

        $dateTimeImmutableClone = $this->cloneToIDate($dateTimeImmutable);

        return Ret::val($dateTimeImmutableClone);
    }


    /**
     * @return Ret<\DateTimeInterface>
     */
    public function type_date_no_tz_formatted($dateFormatted, $formats, $timezoneFallback = null)
    {
        $timezoneFallback = $timezoneFallback ?? $this->the_timezone_utc();

        $thePhp = Lib::php();
        $theType = Lib::type();

        $dateTime = null;

        if ( ! $theType->string_not_empty($dateFormatted)->isOk([ &$dateFormattedString, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $formatsList = $thePhp->to_list($formats);
        $formatsList = $theType->array_not_empty($formatsList)->orThrow();

        foreach ( $formatsList as $i => $format ) {
            $formatValid = $theType->string_not_empty($format)->orThrow();

            $formatsList[$i] = $formatValid;
        }

        $timezoneNil = $this->the_timezone_nil();

        foreach ( $formatsList as $format ) {
            try {
                $dateTime = \DateTime::createFromFormat(
                    $format,
                    $dateFormattedString,
                    $timezoneNil
                );
            }
            catch ( \Throwable $e ) {
                $dateTime = false;
            }

            if ( false !== $dateTime ) {
                break;
            }
        }

        if ( false === $dateTime ) {
            $dateTime = null;
        }

        if ( null === $dateTime ) {
            return Ret::err(
                [ 'The `dateFormatted` should be valid date of passed format', $dateFormatted, $formats ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezone = $dateTime->getTimezone();

        if ( $timezone->getName() !== $timezoneNil->getName() ) {
            return Ret::err(
                [ 'The `dateFormatted` should be date without timezone', $dateFormatted ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezoneFallbackObject = $this->type_timezone($timezoneFallback)->orThrow();

        foreach ( $formatsList as $format ) {
            try {
                $dateTime = \DateTime::createFromFormat(
                    $format,
                    $dateFormattedString,
                    $timezoneFallbackObject
                );
            }
            catch ( \Throwable $e ) {
                $dateTime = false;
            }

            if ( false !== $dateTime ) {
                break;
            }
        }

        $dateTimeClone = $this->cloneToDate($dateTime);

        return Ret::val($dateTimeClone);
    }

    /**
     * @return Ret<\DateTime>
     */
    public function type_adate_no_tz_formatted($dateFormatted, $formats, $timezoneFallback = null)
    {
        $timezoneFallback = $timezoneFallback ?? $this->the_timezone_utc();

        $thePhp = Lib::php();
        $theType = Lib::type();

        $dateTime = null;

        if ( ! $theType->string_not_empty($dateFormatted)->isOk([ &$dateFormattedString, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $formatsList = $thePhp->to_list($formats);
        $formatsList = $theType->array_not_empty($formatsList)->orThrow();

        foreach ( $formatsList as $i => $format ) {
            $formatValid = $theType->string_not_empty($format)->orThrow();

            $formatsList[$i] = $formatValid;
        }

        $timezoneNil = $this->the_timezone_nil();

        foreach ( $formatsList as $format ) {
            try {
                $dateTime = \DateTime::createFromFormat(
                    $format,
                    $dateFormattedString,
                    $timezoneNil
                );
            }
            catch ( \Throwable $e ) {
                $dateTime = false;
            }

            if ( false !== $dateTime ) {
                break;
            }
        }

        if ( false === $dateTime ) {
            $dateTime = null;
        }

        if ( null === $dateTime ) {
            return Ret::err(
                [ 'The `dateFormatted` should be valid date of passed format', $dateFormatted, $formats ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezone = $dateTime->getTimezone();

        if ( $timezone->getName() !== $timezoneNil->getName() ) {
            return Ret::err(
                [ 'The `dateFormatted` should be date without timezone', $dateFormatted ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezoneFallbackObject = $this->type_timezone($timezoneFallback)->orThrow();

        foreach ( $formatsList as $format ) {
            try {
                $dateTime = \DateTime::createFromFormat(
                    $format,
                    $dateFormattedString,
                    $timezoneFallbackObject
                );
            }
            catch ( \Throwable $e ) {
                $dateTime = false;
            }

            if ( false !== $dateTime ) {
                break;
            }
        }

        $dateTimeClone = $this->cloneToADate($dateTime);

        return Ret::val($dateTimeClone);
    }

    /**
     * @return Ret<\DateTimeImmutable>
     */
    public function type_idate_no_tz_formatted($dateFormatted, $formats, $timezoneFallback = null)
    {
        $timezoneFallback = $timezoneFallback ?? $this->the_timezone_utc();

        $thePhp = Lib::php();
        $theType = Lib::type();

        $dateTimeImmutable = null;

        if ( ! $theType->string_not_empty($dateFormatted)->isOk([ &$dateFormattedString, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $formatsList = $thePhp->to_list($formats);
        $formatsList = $theType->array_not_empty($formatsList)->orThrow();

        foreach ( $formatsList as $i => $format ) {
            $formatValid = $theType->string_not_empty($format)->orThrow();

            $formatsList[$i] = $formatValid;
        }

        $timezoneNil = $this->the_timezone_nil();

        foreach ( $formatsList as $format ) {
            try {
                $dateTimeImmutable = \DateTimeImmutable::createFromFormat(
                    $format,
                    $dateFormattedString,
                    $timezoneNil
                );
            }
            catch ( \Throwable $e ) {
                $dateTimeImmutable = false;
            }

            if ( false !== $dateTimeImmutable ) {
                break;
            }
        }

        if ( false === $dateTimeImmutable ) {
            $dateTimeImmutable = null;
        }

        if ( null === $dateTimeImmutable ) {
            return Ret::err(
                [ 'The `dateFormatted` should be valid date of passed format', $dateFormatted, $formats ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezone = $dateTimeImmutable->getTimezone();

        if ( $timezone->getName() !== $timezoneNil->getName() ) {
            return Ret::err(
                [ 'The `dateFormatted` should be date without timezone', $dateFormatted ],
                [ __FILE__, __LINE__ ]
            );
        }

        $timezoneFallbackObject = $this->type_timezone($timezoneFallback)->orThrow();

        foreach ( $formatsList as $format ) {
            try {
                $dateTimeImmutable = \DateTimeImmutable::createFromFormat(
                    $format,
                    $dateFormattedString,
                    $timezoneFallbackObject
                );
            }
            catch ( \Throwable $e ) {
                $dateTimeImmutable = false;
            }

            if ( false !== $dateTimeImmutable ) {
                break;
            }
        }

        $dateTimeImmutableClone = $this->cloneToIDate($dateTimeImmutable);

        return Ret::val($dateTimeImmutableClone);
    }


    /**
     * @return Ret<\DateTimeInterface>
     */
    public function type_date_microtime($microtime, $timezoneSet = null)
    {
        $timezoneSet = $timezoneSet ?? date_default_timezone_get();

        $theType = Lib::type();

        $dateTime = null;

        $timezoneSetObject = $this->type_timezone($timezoneSet)->orThrow();

        if ( ! $theType->numeric($microtime, false, [ &$split ])->isOk([ &$microtimeNumeric, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $timezoneDefault = $this->the_timezone_utc();

        try {
            if ( PHP_VERSION_ID >= 80000 ) {
                $dateTime = new \DateTime("@{$microtimeNumeric}", $timezoneDefault);

            } else {
                $int = $split[1];
                $seconds = $int;

                $dateTime = new \DateTime("@{$seconds}", $timezoneDefault);

                if ( '' !== $split[2] ) {
                    $frac = $split[2];

                    $microseconds = ltrim($frac, '.');
                    $microseconds = substr($microseconds, 0, 6);
                    $microseconds = str_pad($microseconds, 6, '0', STR_PAD_RIGHT);

                    $dateTime = $dateTime->setTime(
                        (int) $dateTime->format('H'),
                        (int) $dateTime->format('i'),
                        (int) $dateTime->format('s'),
                        (int) $microseconds
                    );
                }
            }
        }
        catch ( \Throwable $e ) {
        }

        if ( null === $dateTime ) {
            return Ret::err(
                [ 'The `microtime` should be valid microtime', $microtime ],
                [ __FILE__, __LINE__ ]
            );
        }

        $dateTimeClone = $this->cloneToDate($dateTime);

        $dateTimeClone = $dateTimeClone->setTimezone($timezoneSetObject);

        return Ret::val($dateTimeClone);
    }

    /**
     * @return Ret<\DateTime>
     */
    public function type_adate_microtime($microtime, $timezoneSet = null)
    {
        $timezoneSet = $timezoneSet ?? date_default_timezone_get();

        $theType = Lib::type();

        $dateTime = null;

        $timezoneSetObject = $this->type_timezone($timezoneSet)->orThrow();

        if ( ! $theType->numeric($microtime, false, [ &$split ])->isOk([ &$microtimeNumeric, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $timezoneDefault = $this->the_timezone_utc();

        try {
            if ( PHP_VERSION_ID >= 80000 ) {
                $dateTime = new \DateTime("@{$microtimeNumeric}", $timezoneDefault);

            } else {
                $int = $split[1];
                $seconds = $int;

                $dateTime = new \DateTime("@{$seconds}", $timezoneDefault);

                if ( '' !== $split[2] ) {
                    $frac = $split[2];

                    $microseconds = ltrim($frac, '.');
                    $microseconds = substr($microseconds, 0, 6);
                    $microseconds = str_pad($microseconds, 6, '0', STR_PAD_RIGHT);

                    $dateTime = $dateTime->setTime(
                        (int) $dateTime->format('H'),
                        (int) $dateTime->format('i'),
                        (int) $dateTime->format('s'),
                        (int) $microseconds
                    );
                }
            }
        }
        catch ( \Throwable $e ) {
        }

        if ( null === $dateTime ) {
            return Ret::err(
                [ 'The `microtime` should be valid microtime', $microtime ],
                [ __FILE__, __LINE__ ]
            );
        }

        $dateTimeClone = $this->cloneToADate($dateTime);

        $dateTimeClone = $dateTimeClone->setTimezone($timezoneSetObject);

        return Ret::val($dateTimeClone);
    }

    /**
     * @return Ret<\DateTimeImmutable>
     */
    public function type_idate_microtime($microtime, $timezoneSet = null)
    {
        $timezoneSet = $timezoneSet ?? date_default_timezone_get();

        $theType = Lib::type();

        $dateTimeImmutable = null;

        $timezoneSetObject = $this->type_timezone($timezoneSet)->orThrow();

        if ( ! $theType->numeric($microtime, false, [ &$split ])->isOk([ &$microtimeNumeric, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $timezoneDefault = $this->the_timezone_utc();

        try {
            if ( PHP_VERSION_ID >= 80000 ) {
                $dateTimeImmutable = new \DateTimeImmutable("@{$microtimeNumeric}", $timezoneDefault);

            } else {
                $int = $split[1];
                $seconds = $int;

                $dateTimeImmutable = new \DateTimeImmutable("@{$seconds}", $timezoneDefault);

                if ( '' !== $split[2] ) {
                    $frac = $split[2];

                    $microseconds = ltrim($frac, '.');
                    $microseconds = substr($microseconds, 0, 6);
                    $microseconds = str_pad($microseconds, 6, '0', STR_PAD_RIGHT);

                    $dateTimeImmutable = $dateTimeImmutable->setTime(
                        (int) $dateTimeImmutable->format('H'),
                        (int) $dateTimeImmutable->format('i'),
                        (int) $dateTimeImmutable->format('s'),
                        (int) $microseconds
                    );
                }
            }
        }
        catch ( \Throwable $e ) {
        }

        if ( null === $dateTimeImmutable ) {
            return Ret::err(
                [ 'The `microtime` should be valid microtime', $microtime ],
                [ __FILE__, __LINE__ ]
            );
        }

        $dateTimeImmutableClone = $this->cloneToIDate($dateTimeImmutable);

        $dateTimeImmutableClone = $dateTimeImmutableClone->setTimezone($timezoneSetObject);

        return Ret::val($dateTimeImmutableClone);
    }


    /**
     * @param string|\DateTimeInterface|\DateTimeZone $a
     * @param string|\DateTimeInterface|\DateTimeZone $b
     */
    public function timezone_same($a, $b) : bool
    {
        $theType = Lib::type();

        $aObject = $theType->timezone($a)->orThrow();
        $bObject = $theType->timezone($b)->orThrow();

        return $aObject->getName() === $bObject->getName();
    }

    public function timezone_type(\DateTimeZone $timezone) : int
    {
        return (PHP_VERSION_ID >= 70400)
            ? json_decode(json_encode($timezone))->timezone_type
            : get_object_vars($timezone)['timezone_type'];
    }


    public function interval_encode(\DateInterval $interval) : string
    {
        // > ISO 8601

        $search = [ 'M0S', 'H0M', 'DT0H', 'M0D', 'P0Y', 'Y0M', 'P0M' ];
        $replace = [ 'M', 'H', 'DT', 'M', 'P', 'Y', 'P' ];

        if ( $interval->f ) {
            $microseconds = sprintf('%.6f', $interval->f);
            $microseconds = substr($microseconds, 2);
            $microseconds = rtrim($microseconds, '0.');

            $microseconds = (int) $microseconds;

            $result = $interval->format("P%yY%mM%dDT%hH%iM%s.{$microseconds}S");

        } else {
            $result = $interval->format('P%yY%mM%dDT%hH%iM%sS');
        }

        $result = str_replace($search, $replace, $result);
        $result = rtrim($result, 'PT') ?: 'P0D';

        return $result;
    }

    /**
     * @template-covariant T of \DateInterval
     *
     * @param string               $duration
     * @param class-string<T>|null $intervalClass
     *
     * @return T
     *
     * @noinspection PhpDocSignatureInspection
     */
    public function interval_decode(string $duration, ?string $intervalClass = null) : \DateInterval
    {
        // > ISO 8601

        if ( null !== $intervalClass ) {
            if ( ! is_a($intervalClass, \DateInterval::class, true) ) {
                throw new LogicException(
                    [
                        'The `intervalClass` should be a class-string of: ' . \DateInterval::class,
                        $intervalClass,
                    ]
                );
            }
        }

        $intervalClass = $intervalClass ?? \DateInterval::class;

        $theStr = Lib::str();
        $theType = Lib::type();

        $durationValid = $theType->string_not_empty($duration)->orThrow();

        $regex = '/(\d+\.\d+)([YMWDHS])/';

        $hasDecimalValue = preg_match_all($regex, $durationValid, $matches);

        $decimalValueFrac = null;
        $decimalLetter = null;
        if ( $hasDecimalValue ) {
            $decimal = $matches[0];
            $decimalSubstr = $matches[0][0];
            $decimalValue = $matches[1][0];
            $decimalLetter = $matches[2][0];

            if ( count($decimal) > 1 ) {
                throw new LogicException(
                    [
                        'The `duration` can contain only one `.` in smallest period (according ISO 8601)',
                        $duration,
                    ]
                );
            }

            if ( ! $theStr->str_ends($duration, $decimalSubstr, false) ) {
                throw new LogicException(
                    [
                        'The `duration` can contain only one `.` in smallest period (according ISO 8601)',
                        $duration,
                    ]
                );
            }

            $decimalValueFloat = (float) $decimalValue;
            $decimalValueInt = (int) $decimalValue;

            $decimalValueFrac = $decimalValueFloat - (float) $decimalValueInt;

            $durationValid = str_replace($decimalValue, $decimalValueInt, $durationValid);
        }

        try {
            $instance = new $intervalClass($durationValid);
        }
        catch ( \Throwable $e ) {
            throw new LogicException($e);
        }

        if ( $hasDecimalValue ) {
            $now = new \DateTime('now');
            $nowModified = clone $now;

            $nowModified->add($instance);

            $seconds = null;
            switch ( $decimalLetter ):
                case 'Y':
                    $seconds = (int) ($decimalValueFrac * static::INTERVAL_YEAR);

                    break;

                case 'W':
                    $seconds = (int) ($decimalValueFrac * static::INTERVAL_WEEK);

                    break;

                case 'D':
                    $seconds = (int) ($decimalValueFrac * static::INTERVAL_DAY);

                    break;

                case 'H':
                    $seconds = (int) ($decimalValueFrac * static::INTERVAL_HOUR);

                    break;

                case 'M':
                    if ( false === strpos($duration, 'T') ) {
                        $seconds = (int) ($decimalValueFrac * static::INTERVAL_MONTH);

                    } else {
                        $seconds = (int) ($decimalValueFrac * static::INTERVAL_MINUTE);
                    }

                    break;

            endswitch;

            if ( null !== $seconds ) {
                $nowModified->modify("+{$seconds} seconds");
            }

            $interval = $nowModified->diff($now);

            $instance->y = $interval->y;
            $instance->m = $interval->m;
            $instance->d = $interval->d;
            $instance->h = $interval->h;
            $instance->i = $interval->i;
            $instance->s = $interval->s;

            if ( null !== $decimalValueFrac ) {
                if ( 'S' === $decimalLetter ) {
                    $instance->f = $decimalValueFrac;
                }
            }
        }

        return $instance;
    }


    public function date_remote(\DateTimeInterface $date, $timezoneSet = null) : \DateTimeInterface
    {
        $timezoneSet = $timezoneSet ?? date_default_timezone_get();

        $theType = Lib::type();

        $timezoneSetObject = $theType->timezone($timezoneSet)->orThrow();

        $clone = $this->cloneToDate($date);

        $clone = $clone->setTimezone($timezoneSetObject);

        return $clone;
    }

    public function adate_remote(\DateTimeInterface $date, $timezoneSet = null) : \DateTime
    {
        $timezoneSet = $timezoneSet ?? date_default_timezone_get();

        $theType = Lib::type();

        $timezoneSetObject = $theType->timezone($timezoneSet)->orThrow();

        $clone = $this->cloneToADate($date);

        $clone = $clone->setTimezone($timezoneSetObject);

        return $clone;
    }

    public function idate_remote(\DateTimeInterface $date, $timezoneSet = null) : \DateTimeImmutable
    {
        $timezoneSet = $timezoneSet ?? date_default_timezone_get();

        $theType = Lib::type();

        $timezoneSetObject = $theType->timezone($timezoneSet)->orThrow();

        $clone = $this->cloneToIDate($date);

        $clone = $clone->setTimezone($timezoneSetObject);

        return $clone;
    }


    public function adate_now($timezoneFallback = null) : \DateTime
    {
        $timezoneFallbackObject = null;
        if ( null !== $timezoneFallback ) {
            $timezoneFallbackObject = $this->type_timezone($timezoneFallback)->orThrow();
        }

        try {
            $dateTime = new \DateTime('now', $timezoneFallbackObject);
        }
        catch ( \Exception $e ) {
            throw new LogicException('Unable to create datetime', $e);
        }

        return $dateTime;
    }

    public function idate_now($timezoneFallback = null) : \DateTimeImmutable
    {
        $timezoneFallbackObject = null;
        if ( null !== $timezoneFallback ) {
            $timezoneFallbackObject = $this->type_timezone($timezoneFallback)->orThrow();
        }

        try {
            $dateTimeImmutable = new \DateTimeImmutable('now', $timezoneFallbackObject);
        }
        catch ( \Exception $e ) {
            throw new LogicException('Unable to create datetime', $e);
        }

        return $dateTimeImmutable;
    }


    public function adate_epoch($timezoneFallback = null) : \DateTime
    {
        $timezoneFallbackObject = null;
        if ( null !== $timezoneFallback ) {
            $timezoneFallbackObject = $this->type_timezone($timezoneFallback)->orThrow();
        }

        try {
            $dateTime = new \DateTime('1970-01-01 00:00:00.000000', $timezoneFallbackObject);
        }
        catch ( \Exception $e ) {
            throw new LogicException('Unable to create datetime', $e);
        }

        return $dateTime;
    }

    public function idate_epoch($timezoneFallback = null) : \DateTimeImmutable
    {
        $timezoneFallbackObject = null;
        if ( null !== $timezoneFallback ) {
            $timezoneFallbackObject = $this->type_timezone($timezoneFallback)->orThrow();
        }

        try {
            $dateTimeImmutable = new \DateTimeImmutable('1970-01-01 00:00:00.000000', $timezoneFallbackObject);
        }
        catch ( \Exception $e ) {
            throw new LogicException('Unable to create datetime', $e);
        }

        return $dateTimeImmutable;
    }


    public function adate_zero($timezoneFallback = null) : \DateTime
    {
        $timezoneFallbackObject = null;
        if ( null !== $timezoneFallback ) {
            $timezoneFallbackObject = $this->type_timezone($timezoneFallback)->orThrow();
        }

        try {
            $dateTime = new \DateTime('0000-01-01 00:00:00.000000', $timezoneFallbackObject);
        }
        catch ( \Exception $e ) {
            throw new LogicException('Unable to create datetime', $e);
        }

        return $dateTime;
    }

    public function idate_zero($timezoneFallback = null) : \DateTimeImmutable
    {
        $timezoneFallbackObject = null;
        if ( null !== $timezoneFallback ) {
            $timezoneFallbackObject = $this->type_timezone($timezoneFallback)->orThrow();
        }

        try {
            $dateTimeImmutable = new \DateTimeImmutable('0000-01-01 00:00:00.000000', $timezoneFallbackObject);
        }
        catch ( \Exception $e ) {
            throw new LogicException('Unable to create datetime', $e);
        }

        return $dateTimeImmutable;
    }


    public function datefloor_year(\DateTimeInterface $date) : \DateTimeInterface
    {
        $clone = $this->cloneToDate($date);

        $clone = $clone
            ->setDate((int) $clone->format('Y'), 0, 0)
            ->setTime(0, 0, 0, 0)
        ;

        return $clone;
    }

    public function datefloor_month(\DateTimeInterface $date) : \DateTimeInterface
    {
        $clone = $this->cloneToDate($date);

        $clone = $clone
            ->setDate(
                (int) $clone->format('Y'),
                (int) $clone->format('m'),
                0
            )
            ->setTime(0, 0, 0, 0)
        ;

        return $clone;
    }

    public function datefloor_day(\DateTimeInterface $date) : \DateTimeInterface
    {
        $clone = $this->cloneToDate($date);

        $clone = $clone
            ->setDate(
                (int) $clone->format('Y'),
                (int) $clone->format('m'),
                (int) $clone->format('d'),
            )
            ->setTime(0, 0, 0, 0)
        ;

        return $clone;
    }

    public function datefloor_hour(\DateTimeInterface $date) : \DateTimeInterface
    {
        $clone = $this->cloneToDate($date);

        $clone = $clone
            ->setDate(
                (int) $clone->format('Y'),
                (int) $clone->format('m'),
                (int) $clone->format('d'),
            )
            ->setTime((int) $clone->format('H'), 0, 0, 0)
        ;

        return $clone;
    }

    public function datefloor_minute(\DateTimeInterface $date) : \DateTimeInterface
    {
        $clone = $this->cloneToDate($date);

        $clone = $clone
            ->setDate(
                (int) $clone->format('Y'),
                (int) $clone->format('m'),
                (int) $clone->format('d'),
            )
            ->setTime(
                (int) $clone->format('H'),
                (int) $clone->format('i'),
                0,
                0
            )
        ;

        return $clone;
    }

    public function datefloor_second(\DateTimeInterface $date) : \DateTimeInterface
    {
        $clone = $this->cloneToDate($date);

        $clone = $clone
            ->setDate(
                (int) $clone->format('Y'),
                (int) $clone->format('m'),
                (int) $clone->format('d'),
            )
            ->setTime(
                (int) $clone->format('H'),
                (int) $clone->format('i'),
                (int) $clone->format('s'),
                0
            )
        ;

        return $clone;
    }


    public function format_timestamp(\DateTimeInterface $dateTime) : string
    {
        return (string) $dateTime->getTimestamp();
    }

    public function format_timestamp_non_utc(\DateTimeInterface $dateTime) : string
    {
        return (string) ($dateTime->getTimestamp() + $dateTime->getOffset());
    }


    public function format_sec(\DateTimeInterface $dateTime) : string
    {
        return (string) $dateTime->getTimestamp();
    }

    public function format_msec(\DateTimeInterface $dateTime) : string
    {
        $seconds = (string) $dateTime->getTimestamp();

        $milliseconds = $dateTime->format('v');
        $milliseconds = str_pad($milliseconds, 3, '0', STR_PAD_RIGHT);

        return "{$seconds}.{$milliseconds}";
    }

    public function format_usec(\DateTimeInterface $dateTime) : string
    {
        $seconds = (string) $dateTime->getTimestamp();

        $microseconds = $dateTime->format('u');
        $microseconds = str_pad($microseconds, 6, '0', STR_PAD_RIGHT);

        return "{$seconds}.{$microseconds}";
    }


    public function format_sec_non_utc(\DateTimeInterface $dateTime) : string
    {
        return (string) ($dateTime->getTimestamp() + $dateTime->getOffset());
    }

    public function format_msec_non_utc(\DateTimeInterface $dateTime) : string
    {
        $seconds = (string) ($dateTime->getTimestamp() + $dateTime->getOffset());

        $milliseconds = $dateTime->format('v');
        $milliseconds = str_pad($milliseconds, 3, '0', STR_PAD_RIGHT);

        return "{$seconds}.{$milliseconds}";
    }

    public function format_usec_non_utc(\DateTimeInterface $dateTime) : string
    {
        $seconds = (string) ($dateTime->getTimestamp() + $dateTime->getOffset());

        $microseconds = $dateTime->format('u');
        $microseconds = str_pad($microseconds, 6, '0', STR_PAD_RIGHT);

        return "{$seconds}.{$microseconds}";
    }


    public function format_sql(\DateTimeInterface $dateTime) : string
    {
        $clone = (clone $dateTime)->setTimezone($this->the_timezone_utc());

        $formatted = $clone->format(static::FORMAT_SQL);

        return $formatted;
    }

    public function format_sql_sec(\DateTimeInterface $dateTime) : string
    {
        $clone = (clone $dateTime)->setTimezone($this->the_timezone_utc());

        $formatted = $clone->format(static::FORMAT_SQL_SEC);

        return $formatted;
    }

    public function format_sql_msec(\DateTimeInterface $dateTime) : string
    {
        $clone = (clone $dateTime)->setTimezone($this->the_timezone_utc());

        $formatted = $clone->format(static::FORMAT_SQL_SEC);

        $milliseconds = $clone->format('v');
        $milliseconds = str_pad($milliseconds, 3, '0', STR_PAD_RIGHT);

        return "{$formatted}.{$milliseconds}";
    }

    public function format_sql_usec(\DateTimeInterface $dateTime) : string
    {
        $clone = (clone $dateTime)->setTimezone($this->the_timezone_utc());

        $formatted = $clone->format(static::FORMAT_SQL_SEC);

        $microseconds = $clone->format('v');
        $microseconds = str_pad($microseconds, 6, '0', STR_PAD_RIGHT);

        return "{$formatted}.{$microseconds}";
    }


    public function format_sql_non_utc(\DateTimeInterface $dateTime) : string
    {
        return $dateTime->format(static::FORMAT_SQL);
    }

    public function format_sql_sec_non_utc(\DateTimeInterface $dateTime) : string
    {
        return $dateTime->format(static::FORMAT_SQL_SEC);
    }

    public function format_sql_msec_non_utc(\DateTimeInterface $dateTime) : string
    {
        $formatted = $dateTime->format(static::FORMAT_SQL_SEC);

        $milliseconds = $dateTime->format('v');
        $milliseconds = str_pad($milliseconds, 3, '0', STR_PAD_RIGHT);

        return "{$formatted}.{$milliseconds}";
    }

    public function format_sql_usec_non_utc(\DateTimeInterface $dateTime) : string
    {
        $formatted = $dateTime->format(static::FORMAT_SQL_SEC);

        $microseconds = $dateTime->format('v');
        $microseconds = str_pad($microseconds, 6, '0', STR_PAD_RIGHT);

        return "{$formatted}.{$microseconds}";
    }


    public function array_sql(\DateTimeInterface $dateTime, ?bool $keys = null) : array
    {
        $keys = $keys ?? false;

        $timezone = $dateTime->getTimezone();

        $formatted = [];

        if ( $keys ) {
            $formatted['date'] = $this->format_sql_non_utc($dateTime);
            $formatted['date_utc'] = $this->format_sql($dateTime);
            $formatted['timezone_name'] = $timezone->getName();
            $formatted['timezone_offset_integer'] = $dateTime->getOffset();
            $formatted['timezone_offset_string'] = $dateTime->format('P');
            $formatted['timezone_type'] = $this->timezone_type($timezone);

        } else {
            $formatted[] = $this->format_sql_non_utc($dateTime);
            $formatted[] = $this->format_sql($dateTime);
            $formatted[] = $timezone->getName();
            $formatted[] = $dateTime->format('P');
            $formatted[] = $dateTime->getOffset();
            $formatted[] = $this->timezone_type($timezone);
        }

        return $formatted;
    }

    public function array_sql_sec(\DateTimeInterface $dateTime, ?bool $keys = null) : array
    {
        $keys = $keys ?? false;

        $timezone = $dateTime->getTimezone();

        $formatted = [];

        if ( $keys ) {
            $formatted['date'] = $this->format_sql_sec_non_utc($dateTime);
            $formatted['date_utc'] = $this->format_sql_sec($dateTime);
            $formatted['timezone_name'] = $timezone->getName();
            $formatted['timezone_offset_integer'] = $dateTime->getOffset();
            $formatted['timezone_offset_string'] = $dateTime->format('P');
            $formatted['timezone_type'] = $this->timezone_type($timezone);

        } else {
            $formatted[] = $this->format_sql_sec_non_utc($dateTime);
            $formatted[] = $this->format_sql_sec($dateTime);
            $formatted[] = $timezone->getName();
            $formatted[] = $dateTime->format('P');
            $formatted[] = $dateTime->getOffset();
            $formatted[] = $this->timezone_type($timezone);
        }

        return $formatted;
    }

    public function array_sql_msec(\DateTimeInterface $dateTime, ?bool $keys = null) : array
    {
        $keys = $keys ?? false;

        $timezone = $dateTime->getTimezone();

        $formatted = [];

        if ( $keys ) {
            $formatted['date'] = $this->format_sql_msec_non_utc($dateTime);
            $formatted['date_utc'] = $this->format_sql_msec($dateTime);
            $formatted['timezone_name'] = $timezone->getName();
            $formatted['timezone_offset_integer'] = $dateTime->getOffset();
            $formatted['timezone_offset_string'] = $dateTime->format('P');
            $formatted['timezone_type'] = $this->timezone_type($timezone);

        } else {
            $formatted[] = $this->format_sql_msec_non_utc($dateTime);
            $formatted[] = $this->format_sql_msec($dateTime);
            $formatted[] = $timezone->getName();
            $formatted[] = $dateTime->format('P');
            $formatted[] = $dateTime->getOffset();
            $formatted[] = $this->timezone_type($timezone);
        }

        return $formatted;
    }

    public function array_sql_usec(\DateTimeInterface $dateTime, ?bool $keys = null) : array
    {
        $keys = $keys ?? false;

        $timezone = $dateTime->getTimezone();

        $formatted = [];

        if ( $keys ) {
            $formatted['date'] = $this->format_sql_usec_non_utc($dateTime);
            $formatted['date_utc'] = $this->format_sql_usec($dateTime);
            $formatted['timezone_name'] = $timezone->getName();
            $formatted['timezone_offset_integer'] = $dateTime->getOffset();
            $formatted['timezone_offset_string'] = $dateTime->format('P');
            $formatted['timezone_type'] = $this->timezone_type($timezone);

        } else {
            $formatted[] = $this->format_sql_usec_non_utc($dateTime);
            $formatted[] = $this->format_sql_usec($dateTime);
            $formatted[] = $timezone->getName();
            $formatted[] = $dateTime->format('P');
            $formatted[] = $dateTime->getOffset();
            $formatted[] = $this->timezone_type($timezone);
        }

        return $formatted;
    }


    public function format_javascript(\DateTimeInterface $dateTime) : string
    {
        $formatted = $dateTime->format(static::FORMAT_JAVASCRIPT_NO_OFFSET);

        $offset = $dateTime->format('P');

        return "{$formatted}{$offset}";
    }

    public function format_javascript_sec(\DateTimeInterface $dateTime) : string
    {
        $formatted = $dateTime->format(static::FORMAT_JAVASCRIPT_NO_OFFSET_SEC);

        $offset = $dateTime->format('P');

        return "{$formatted}{$offset}";
    }

    public function format_javascript_msec(\DateTimeInterface $dateTime) : string
    {
        $formatted = $dateTime->format(static::FORMAT_JAVASCRIPT_NO_OFFSET_SEC);

        $milliseconds = $dateTime->format('v');
        $milliseconds = str_pad($milliseconds, 3, '0', STR_PAD_RIGHT);

        $offset = $dateTime->format('P');

        return "{$formatted}.{$milliseconds}{$offset}";
    }

    public function format_javascript_usec(\DateTimeInterface $dateTime) : string
    {
        $formatted = $dateTime->format(static::FORMAT_JAVASCRIPT_NO_OFFSET_SEC);

        $microseconds = $dateTime->format('u');
        $microseconds = str_pad($microseconds, 6, '0', STR_PAD_RIGHT);

        $offset = $dateTime->format('P');

        return "{$formatted}.{$microseconds}{$offset}";
    }


    public function format_javascript_utc(\DateTimeInterface $dateTime) : string
    {
        $clone = (clone $dateTime)->setTimezone($this->the_timezone_utc());

        $formatted = $clone->format(static::FORMAT_JAVASCRIPT_NO_OFFSET);

        return "{$formatted}Z";
    }

    public function format_javascript_sec_utc(\DateTimeInterface $dateTime) : string
    {
        $clone = (clone $dateTime)->setTimezone($this->the_timezone_utc());

        $formatted = $clone->format(static::FORMAT_JAVASCRIPT_NO_OFFSET_SEC);

        return "{$formatted}Z";
    }

    public function format_javascript_msec_utc(\DateTimeInterface $dateTime) : string
    {
        $clone = (clone $dateTime)->setTimezone($this->the_timezone_utc());

        $formatted = $clone->format(static::FORMAT_JAVASCRIPT_NO_OFFSET_SEC);

        $milliseconds = $clone->format('v');
        $milliseconds = str_pad($milliseconds, 3, '0', STR_PAD_RIGHT);

        return "{$formatted}.{$milliseconds}Z";
    }

    public function format_javascript_usec_utc(\DateTimeInterface $dateTime) : string
    {
        $clone = (clone $dateTime)->setTimezone($this->the_timezone_utc());

        $formatted = $clone->format(static::FORMAT_JAVASCRIPT_NO_OFFSET_SEC);

        $microseconds = $clone->format('u');
        $microseconds = str_pad($microseconds, 6, '0', STR_PAD_RIGHT);

        return "{$formatted}.{$microseconds}Z";
    }


    public function array_javascript(\DateTimeInterface $dateTime, ?bool $keys = null) : array
    {
        $keys = $keys ?? false;

        $timezone = $dateTime->getTimezone();

        $formatted = [];

        if ( $keys ) {
            $formatted['date'] = $this->format_javascript($dateTime);
            $formatted['date_utc'] = $this->format_javascript_utc($dateTime);
            $formatted['timezone_name'] = $timezone->getName();
            $formatted['timezone_offset_integer'] = $dateTime->getOffset();
            $formatted['timezone_offset_string'] = $dateTime->format('P');
            $formatted['timezone_type'] = $this->timezone_type($timezone);

        } else {
            $formatted[] = $this->format_javascript($dateTime);
            $formatted[] = $this->format_javascript_utc($dateTime);
            $formatted[] = $timezone->getName();
            $formatted[] = $dateTime->format('P');
            $formatted[] = $dateTime->getOffset();
            $formatted[] = $this->timezone_type($timezone);
        }

        return $formatted;
    }

    public function array_javascript_sec(\DateTimeInterface $dateTime, ?bool $keys = null) : array
    {
        $keys = $keys ?? false;

        $timezone = $dateTime->getTimezone();

        $formatted = [];

        if ( $keys ) {
            $formatted['date'] = $this->format_javascript_sec($dateTime);
            $formatted['date_utc'] = $this->format_javascript_sec_utc($dateTime);
            $formatted['timezone_name'] = $timezone->getName();
            $formatted['timezone_offset_integer'] = $dateTime->getOffset();
            $formatted['timezone_offset_string'] = $dateTime->format('P');
            $formatted['timezone_type'] = $this->timezone_type($timezone);

        } else {
            $formatted[] = $this->format_javascript_sec($dateTime);
            $formatted[] = $this->format_javascript_sec_utc($dateTime);
            $formatted[] = $timezone->getName();
            $formatted[] = $dateTime->format('P');
            $formatted[] = $dateTime->getOffset();
            $formatted[] = $this->timezone_type($timezone);
        }

        return $formatted;
    }

    public function array_javascript_msec(\DateTimeInterface $dateTime, ?bool $keys = null) : array
    {
        $keys = $keys ?? false;

        $timezone = $dateTime->getTimezone();

        $formatted = [];

        if ( $keys ) {
            $formatted['date'] = $this->format_javascript_msec($dateTime);
            $formatted['date_utc'] = $this->format_javascript_msec_utc($dateTime);
            $formatted['timezone_name'] = $timezone->getName();
            $formatted['timezone_offset_integer'] = $dateTime->getOffset();
            $formatted['timezone_offset_string'] = $dateTime->format('P');
            $formatted['timezone_type'] = $this->timezone_type($timezone);

        } else {
            $formatted[] = $this->format_javascript_msec($dateTime);
            $formatted[] = $this->format_javascript_msec_utc($dateTime);
            $formatted[] = $timezone->getName();
            $formatted[] = $dateTime->format('P');
            $formatted[] = $dateTime->getOffset();
            $formatted[] = $this->timezone_type($timezone);
        }

        return $formatted;
    }

    public function array_javascript_usec(\DateTimeInterface $dateTime, ?bool $keys = null) : array
    {
        $keys = $keys ?? false;

        $timezone = $dateTime->getTimezone();

        $formatted = [];

        if ( $keys ) {
            $formatted['date'] = $this->format_javascript_usec($dateTime);
            $formatted['date_utc'] = $this->format_javascript_usec_utc($dateTime);
            $formatted['timezone_name'] = $timezone->getName();
            $formatted['timezone_offset_integer'] = $dateTime->getOffset();
            $formatted['timezone_offset_string'] = $dateTime->format('P');
            $formatted['timezone_type'] = $this->timezone_type($timezone);

        } else {
            $formatted[] = $this->format_javascript_usec($dateTime);
            $formatted[] = $this->format_javascript_usec_utc($dateTime);
            $formatted[] = $timezone->getName();
            $formatted[] = $dateTime->format('P');
            $formatted[] = $dateTime->getOffset();
            $formatted[] = $this->timezone_type($timezone);
        }

        return $formatted;
    }



    public function format_locale_month(\DateTimeInterface $dateTime, string $locale) : string
    {
        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            null,
            \IntlDateFormatter::GREGORIAN,
            'LLLL'
        );

        $content = $formatter->format($dateTime);

        $content = mb_strtolower($content);

        return $content;
    }

    public function format_locale_month_short(\DateTimeInterface $dateTime, string $locale) : string
    {
        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            null,
            \IntlDateFormatter::GREGORIAN,
            'MMM'
        );

        $content = $formatter->format($dateTime);

        $content = mb_strtolower($content);

        return $content;
    }


    public function format_locale_day(\DateTimeInterface $dateTime, string $locale) : string
    {
        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            null,
            \IntlDateFormatter::GREGORIAN,
            'EEEE'
        );

        $content = $formatter->format($dateTime);

        $content = mb_strtolower($content);

        return $content;
    }

    public function format_locale_day_short(\DateTimeInterface $dateTime, string $locale) : string
    {
        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            null,
            \IntlDateFormatter::GREGORIAN,
            'EEE'
        );

        $content = $formatter->format($dateTime);

        $content = mb_strtolower($content);

        return $content;
    }


    public function get_locale_months(string $locale) : array
    {
        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            null,
            \IntlDateFormatter::GREGORIAN,
            'LLLL'
        );

        $date = new \DateTime('1970-01-01');

        for ( $i = 1; $i <= 12; $i++ ) {
            $content = $formatter->format($date);
            $content = mb_strtolower($content);

            $days[] = $content;

            $date->modify("+1 month");
        }

        return $days;
    }

    public function get_locale_months_short(string $locale) : array
    {
        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            null,
            \IntlDateFormatter::GREGORIAN,
            'MMM'
        );

        $date = new \DateTime('1970-01-01');

        for ( $i = 1; $i <= 12; $i++ ) {
            $content = $formatter->format($date);
            $content = mb_strtolower($content);

            $days[] = $content;

            $date->modify("+1 month");
        }

        return $days;
    }


    public function get_locale_days(string $locale) : array
    {
        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            null,
            \IntlDateFormatter::GREGORIAN,
            'EEEE'
        );

        $date = new \DateTime('Sunday this week midnight');

        for ( $i = 1; $i <= 7; $i++ ) {
            $content = $formatter->format($date);
            $content = mb_strtolower($content);

            $days[] = $content;

            $date->modify("+1 day");
        }

        return $days;
    }

    public function get_locale_days_short(string $locale) : array
    {
        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            null,
            \IntlDateFormatter::GREGORIAN,
            'EEE'
        );

        $date = new \DateTime('Sunday this week midnight');

        for ( $i = 1; $i <= 7; $i++ ) {
            $content = $formatter->format($date);
            $content = mb_strtolower($content);

            $days[] = $content;

            $date->modify("+1 day");
        }

        return $days;
    }



    protected function cloneToDate(\DateTimeInterface $dateTime) : ?\DateTimeInterface
    {
        return clone $dateTime;
    }

    protected function cloneToADate(\DateTimeInterface $dateTime) : ?\DateTime
    {
        if ( $dateTime instanceof \DateTime ) {
            return clone $dateTime;

        } elseif ( $dateTime instanceof \DateTimeImmutable ) {
            return \DateTime::createFromImmutable($dateTime);
        }

        throw new LogicException([ 'Unknown `dateTime`', $dateTime ]);
    }

    protected function cloneToIDate(\DateTimeInterface $dateTime) : ?\DateTimeImmutable
    {
        if ( $dateTime instanceof \DateTime ) {
            return \DateTimeImmutable::createFromMutable($dateTime);

        } elseif ( $dateTime instanceof \DateTimeImmutable ) {
            return clone $dateTime;
        }

        throw new LogicException([ 'Unknown `dateTime`', $dateTime ]);
    }
}
