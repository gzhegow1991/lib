<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;


class DateModule
{
    const INTERVAL_MINUTE = 60;
    const INTERVAL_HOUR   = 3600;
    const INTERVAL_DAY    = 86400;
    const INTERVAL_WEEK   = 604800;
    const INTERVAL_MONTH  = 2592000;
    const INTERVAL_YEAR   = 31536000;

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

    const FORMAT_JAVASCRIPT      = self::FORMAT_JAVASCRIPT_SEC;
    const FORMAT_JAVASCRIPT_SEC  = "Y-m-d\TH:i:sP";
    const FORMAT_JAVASCRIPT_MSEC = "Y-m-d\TH:i:s.vP";
    const FORMAT_JAVASCRIPT_USEC = "Y-m-d\TH:i:s.uP";

    const FORMAT_HUMAN_DATE = "D, d M Y O";
    const FORMAT_HUMAN      = "D, d M Y H:i:s O";


    const LIST_INTERVAL = [
        self::INTERVAL_MINUTE => true,
        self::INTERVAL_HOUR   => true,
        self::INTERVAL_DAY    => true,
        self::INTERVAL_WEEK   => true,
        self::INTERVAL_MONTH  => true,
        self::INTERVAL_YEAR   => true,
    ];

    const LIST_FORMAT = [
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

        self::FORMAT_HUMAN_DATE => true,
        self::FORMAT_HUMAN      => true,
    ];


    /**
     * @param \DateTimeZone|null $r
     */
    public function type_timezone(&$r, $timezone, ?array $allowedTimezoneTypes = null) : bool
    {
        $r = null;

        $dateTimeZone = null;

        if ($timezone instanceof \DateTimeZone) {
            $dateTimeZone = $timezone;

        } elseif ($timezone instanceof \DateTimeInterface) {
            $dateTimeZone = $timezone->getTimezone();

        } else {
            try {
                $dateTimeZone = new \DateTimeZone($timezone);
            }
            catch ( \Throwable $e ) {
            }
        }

        if (null !== $allowedTimezoneTypes) {
            if (null !== $dateTimeZone) {
                $timezoneType = $this->timezone_type($dateTimeZone);

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTimeZone = null;
                }
            }
        }

        if (null !== $dateTimeZone) {
            $r = $dateTimeZone;

            return true;
        }

        return false;
    }

    /**
     * @param \DateTimeZone|null $r
     */
    public function type_timezone_offset(&$r, $timezoneOrOffset) : bool
    {
        $r = null;

        $dateTimeZone = null;

        if ($timezoneOrOffset instanceof \DateTimeZone) {
            $dateTimeZone = $timezoneOrOffset;

        } elseif ($timezoneOrOffset instanceof \DateTimeInterface) {
            $dateTimeZone = $timezoneOrOffset->getTimezone();

        } else {
            try {
                $dateTimeZone = new \DateTimeZone($timezoneOrOffset);
            }
            catch ( \Throwable $e ) {
            }
        }

        if (null !== $dateTimeZone) {
            $timezoneType = $this->timezone_type($dateTimeZone);

            if ($timezoneType !== 1) {
                $dateTimeZone = null;
            }
        }

        if (null !== $dateTimeZone) {
            $r = $dateTimeZone;

            return true;
        }

        return false;
    }

    /**
     * @param \DateTimeZone|null $r
     */
    public function type_timezone_abbr(&$r, $timezoneOrAbbr) : bool
    {
        $r = null;

        $dateTimeZone = null;

        if ($timezoneOrAbbr instanceof \DateTimeZone) {
            $dateTimeZone = $timezoneOrAbbr;

        } elseif ($timezoneOrAbbr instanceof \DateTimeInterface) {
            $dateTimeZone = $timezoneOrAbbr->getTimezone();

        } else {
            try {
                $dateTimeZone = new \DateTimeZone($timezoneOrAbbr);
            }
            catch ( \Throwable $e ) {
            }
        }

        if (null !== $dateTimeZone) {
            $timezoneType = $this->timezone_type($dateTimeZone);

            if ($timezoneType !== 2) {
                $dateTimeZone = null;
            }
        }

        if (null !== $dateTimeZone) {
            $r = $dateTimeZone;

            return true;
        }

        return false;
    }

    /**
     * @param \DateTimeZone|null $r
     */
    public function type_timezone_name(&$r, $timezoneOrName) : bool
    {
        $r = null;

        $dateTimeZone = null;

        if ($timezoneOrName instanceof \DateTimeZone) {
            $dateTimeZone = $timezoneOrName;

        } elseif ($timezoneOrName instanceof \DateTimeInterface) {
            $dateTimeZone = $timezoneOrName->getTimezone();

        } else {
            try {
                $dateTimeZone = new \DateTimeZone($timezoneOrName);
            }
            catch ( \Throwable $e ) {
            }
        }

        if (null !== $dateTimeZone) {
            $timezoneType = $this->timezone_type($dateTimeZone);

            if ($timezoneType !== 3) {
                $dateTimeZone = null;
            }
        }

        if (null !== $dateTimeZone) {
            $r = $dateTimeZone;

            return true;
        }

        return false;
    }

    /**
     * @param \DateTimeZone|null $r
     */
    public function type_timezone_nameabbr(&$r, $timezoneOrNameOrAbbr) : bool
    {
        $r = null;

        $dateTimeZone = null;

        if ($timezoneOrNameOrAbbr instanceof \DateTimeZone) {
            $dateTimeZone = $timezoneOrNameOrAbbr;

        } elseif ($timezoneOrNameOrAbbr instanceof \DateTimeInterface) {
            $dateTimeZone = $timezoneOrNameOrAbbr->getTimezone();

        } else {
            try {
                $dateTimeZone = new \DateTimeZone($timezoneOrNameOrAbbr);
            }
            catch ( \Throwable $e ) {
            }
        }

        if (null !== $dateTimeZone) {
            $timezoneType = $this->timezone_type($dateTimeZone);

            if (! (
                ($timezoneType === 2)
                || ($timezoneType === 3)
            )) {
                $dateTimeZone = null;
            }
        }

        if (null !== $dateTimeZone) {
            $r = $dateTimeZone;

            return true;
        }

        return false;
    }


    /**
     * @param \DateTimeInterface|null $r
     */
    public function type_date(&$r, $datestring, $timezoneFallback = null) : bool
    {
        $r = null;

        $dateTime = null;

        if ($datestring instanceof \DateTimeInterface) {
            $dateTime = $datestring;

        } else {
            if (! (is_string($datestring) && ('' !== $datestring))) {
                return false;
            }

            $_timezoneFallback = null;
            if (null !== $timezoneFallback) {
                $status = $this->type_timezone(
                    $_timezoneFallback, $timezoneFallback
                );

                if (! $status) {
                    return false;
                }
            }

            try {
                $dateTime = new \DateTime(
                    $datestring,
                    $_timezoneFallback
                );
            }
            catch ( \Throwable $e ) {
            }
        }

        if (null !== $dateTime) {
            $r = $this->cloneToDate($dateTime);

            return true;
        }

        return false;
    }

    /**
     * @param \DateTime|null $r
     */
    public function type_adate(&$r, $datestring, $timezoneFallback = null) : bool
    {
        $r = null;

        $dateTime = null;

        if ($datestring instanceof \DateTimeInterface) {
            $dateTime = $datestring;

        } else {
            if (! (is_string($datestring) && ('' !== $datestring))) {
                return false;
            }

            $_timezoneFallback = null;
            if (null !== $timezoneFallback) {
                $status = $this->type_timezone(
                    $_timezoneFallback, $timezoneFallback
                );

                if (! $status) {
                    return false;
                }
            }

            try {
                $dateTime = new \DateTime(
                    $datestring,
                    $_timezoneFallback
                );
            }
            catch ( \Throwable $e ) {
            }
        }

        if (null !== $dateTime) {
            $r = $this->cloneToADate($dateTime);

            return true;
        }

        return false;
    }

    /**
     * @param \DateTimeImmutable|null $r
     */
    public function type_idate(&$r, $datestring, $timezoneFallback = null) : bool
    {
        $r = null;

        $dateTimeImmutable = null;

        if ($datestring instanceof \DateTimeInterface) {
            $dateTimeImmutable = $datestring;

        } else {
            if (! (is_string($datestring) && ('' !== $datestring))) {
                return false;
            }

            $_timezoneFallback = null;
            if (null !== $timezoneFallback) {
                $status = $this->type_timezone(
                    $_timezoneFallback, $timezoneFallback
                );

                if (! $status) {
                    return false;
                }
            }

            try {
                $dateTimeImmutable = new \DateTime(
                    $datestring,
                    $_timezoneFallback
                );
            }
            catch ( \Throwable $e ) {
            }
        }

        if (null !== $dateTimeImmutable) {
            $r = $this->cloneToIDate($dateTimeImmutable);

            return true;
        }

        return false;
    }


    /**
     * @param \DateTimeInterface|null $r
     */
    public function type_date_formatted(&$r, $dateFormatted, $formats, $timezoneFallback = null) : bool
    {
        $r = null;

        $dateTime = null;

        if ($dateFormatted instanceof \DateTimeInterface) {
            $dateTime = $dateFormatted;

        } else {
            $formatsList = Lib::php()->to_list($formats);

            if ([] === $formatsList) {
                return false;
            }

            $theType = Lib::type();
            foreach ( $formatsList as $i => $format ) {
                if (! $theType->string_not_empty($formatString, $format)) {
                    return false;
                }

                $formatsList[ $i ] = $formatString;
            }

            if (! (is_string($dateFormatted) && ('' !== $dateFormatted))) {
                return false;
            }

            $_timezoneFallback = null;
            if (null !== $timezoneFallback) {
                $status = $this->type_timezone(
                    $_timezoneFallback, $timezoneFallback
                );

                if (! $status) {
                    return false;
                }
            }

            foreach ( $formatsList as $format ) {
                try {
                    $dateTime = \DateTime::createFromFormat(
                        $format,
                        $dateFormatted,
                        $_timezoneFallback
                    );
                }
                catch ( \Throwable $e ) {
                    $dateTime = false;
                }
            }

            if (false === $dateTime) {
                $dateTime = null;
            }
        }

        if (null !== $dateTime) {
            $r = $this->cloneToDate($dateTime);

            return true;
        }

        return false;
    }

    /**
     * @param \DateTime|null $r
     */
    public function type_adate_formatted(&$r, $dateFormatted, $formats, $timezoneFallback = null) : bool
    {
        $r = null;

        $dateTime = null;

        if ($dateFormatted instanceof \DateTimeInterface) {
            $dateTime = $dateFormatted;

        } else {
            $formatsList = Lib::php()->to_list($formats);

            if ([] === $formatsList) {
                return false;
            }

            $theType = Lib::type();
            foreach ( $formatsList as $i => $format ) {
                if (! $theType->string_not_empty($formatString, $format)) {
                    return false;
                }

                $formatsList[ $i ] = $formatString;
            }

            if (! (is_string($dateFormatted) && ('' !== $dateFormatted))) {
                return false;
            }

            $_timezoneFallback = null;
            if (null !== $timezoneFallback) {
                $status = $this->type_timezone(
                    $_timezoneFallback, $timezoneFallback
                );

                if (! $status) {
                    return false;
                }
            }

            foreach ( $formatsList as $format ) {
                try {
                    $dateTime = \DateTime::createFromFormat(
                        $format,
                        $dateFormatted,
                        $_timezoneFallback
                    );
                }
                catch ( \Throwable $e ) {
                    $dateTime = false;
                }
            }

            if (false === $dateTime) {
                $dateTime = null;
            }
        }

        if (null !== $dateTime) {
            $r = $this->cloneToADate($dateTime);

            return true;
        }

        return false;
    }

    /**
     * @param \DateTimeImmutable|null $r
     */
    public function type_idate_formatted(&$r, $dateFormatted, $formats, $timezoneFallback = null) : bool
    {
        $r = null;

        $dateTimeImmutable = null;

        if ($dateFormatted instanceof \DateTimeInterface) {
            $dateTimeImmutable = $dateFormatted;

        } else {
            $formatsList = Lib::php()->to_list($formats);

            if ([] === $formatsList) {
                return false;
            }

            $theType = Lib::type();
            foreach ( $formatsList as $i => $format ) {
                if (! $theType->string_not_empty($formatString, $format)) {
                    return false;
                }

                $formatsList[ $i ] = $formatString;
            }

            if (! (is_string($dateFormatted) && ('' !== $dateFormatted))) {
                return false;
            }

            $_timezoneFallback = null;
            if (null !== $timezoneFallback) {
                $status = $this->type_timezone(
                    $_timezoneFallback, $timezoneFallback
                );

                if (! $status) {
                    return false;
                }
            }

            foreach ( $formatsList as $format ) {
                try {
                    $dateTimeImmutable = \DateTime::createFromFormat(
                        $format,
                        $dateFormatted,
                        $_timezoneFallback
                    );
                }
                catch ( \Throwable $e ) {
                    $dateTimeImmutable = false;
                }
            }

            if (false === $dateTimeImmutable) {
                $dateTimeImmutable = null;
            }
        }

        if (null !== $dateTimeImmutable) {
            $r = $this->cloneToIDate($dateTimeImmutable);

            return true;
        }

        return false;
    }


    /**
     * @param \DateTimeInterface|null $r
     */
    public function type_date_tz(&$r, $datestring, ?array $allowedTimezoneTypes = null) : bool
    {
        $r = null;

        $dateTime = null;

        $timezoneNil = Lib::php()->the_timezone_nil();

        if ($datestring instanceof \DateTimeInterface) {
            $dateTime = $datestring;

        } else {
            if (! (is_string($datestring) && ('' !== $datestring))) {
                return false;
            }

            try {
                $dateTime = new \DateTime(
                    $datestring,
                    $timezoneNil
                );
            }
            catch ( \Throwable $e ) {
            }
        }

        if (null !== $dateTime) {
            $timezone = $dateTime->getTimezone();

            if ($timezone->getName() == $timezoneNil->getName()) {
                $dateTime = null;
            }
        }

        if (null !== $allowedTimezoneTypes) {
            if (null !== $dateTime) {
                $timezoneType = $this->timezone_type($timezone);

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTime = null;
                }
            }
        }

        if (null !== $dateTime) {
            $r = $this->cloneToDate($dateTime);

            return true;
        }

        return false;
    }

    /**
     * @param \DateTime|null $r
     */
    public function type_adate_tz(&$r, $datestring, ?array $allowedTimezoneTypes = null) : bool
    {
        $r = null;

        $dateTime = null;

        $timezoneNil = Lib::php()->the_timezone_nil();

        if ($datestring instanceof \DateTimeInterface) {
            $dateTime = $datestring;

        } else {
            if (! (is_string($datestring) && ('' !== $datestring))) {
                return false;
            }

            try {
                $dateTime = new \DateTime(
                    $datestring,
                    $timezoneNil
                );
            }
            catch ( \Throwable $e ) {
            }
        }

        if (null !== $dateTime) {
            $timezone = $dateTime->getTimezone();

            if ($timezone->getName() === $timezoneNil->getName()) {
                $dateTime = null;
            }
        }

        if (null !== $allowedTimezoneTypes) {
            if (null !== $dateTime) {
                $timezoneType = $this->timezone_type($timezone);

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTime = null;
                }
            }
        }

        if (null !== $dateTime) {
            $r = $this->cloneToADate($dateTime);

            return true;
        }

        return false;
    }

    /**
     * @param \DateTimeImmutable|null $r
     */
    public function type_idate_tz(&$r, $datestring, ?array $allowedTimezoneTypes = null) : bool
    {
        $r = null;

        $dateTimeImmutable = null;

        $timezoneNil = Lib::php()->the_timezone_nil();

        if ($datestring instanceof \DateTimeInterface) {
            $dateTimeImmutable = $datestring;

        } else {
            if (! (is_string($datestring) && ('' !== $datestring))) {
                return false;
            }

            try {
                $dateTimeImmutable = new \DateTime(
                    $datestring,
                    $timezoneNil
                );
            }
            catch ( \Throwable $e ) {
            }
        }

        if (null !== $dateTimeImmutable) {
            $timezone = $dateTimeImmutable->getTimezone();

            if ($timezone->getName() == $timezoneNil->getName()) {
                $dateTimeImmutable = null;
            }
        }

        if (null !== $allowedTimezoneTypes) {
            if (null !== $dateTimeImmutable) {
                $timezoneType = $this->timezone_type($timezone);

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTimeImmutable = null;
                }
            }
        }

        if (null !== $dateTimeImmutable) {
            $r = $this->cloneToIDate($dateTimeImmutable);

            return true;
        }

        return false;
    }


    /**
     * @param \DateTimeInterface|null $r
     */
    public function type_date_tz_formatted(&$r, $dateFormatted, $formats, ?array $allowedTimezoneTypes = null) : bool
    {
        $r = null;

        $dateTime = null;

        $timezoneNil = Lib::php()->the_timezone_nil();

        if ($dateFormatted instanceof \DateTimeInterface) {
            $dateTime = $dateFormatted;

        } else {
            $formatsList = Lib::php()->to_list($formats);

            if ([] === $formatsList) {
                return false;
            }

            $theType = Lib::type();
            foreach ( $formatsList as $i => $format ) {
                if (! $theType->string_not_empty($formatString, $format)) {
                    return false;
                }

                $formatsList[ $i ] = $formatString;
            }

            if (! (is_string($dateFormatted) && ('' !== $dateFormatted))) {
                return false;
            }

            foreach ( $formatsList as $format ) {
                try {
                    $dateTime = \DateTime::createFromFormat(
                        $format,
                        $dateFormatted,
                        $timezoneNil
                    );
                }
                catch ( \Throwable $e ) {
                    $dateTime = false;
                }
            }

            if (false === $dateTime) {
                $dateTime = null;
            }
        }

        if (null !== $dateTime) {
            $timezone = $dateTime->getTimezone();

            if ($timezone->getName() == $timezoneNil->getName()) {
                $dateTime = null;
            }
        }

        if (null !== $allowedTimezoneTypes) {
            if (null !== $dateTime) {
                $timezoneType = $this->timezone_type($timezone);

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTime = null;
                }
            }
        }

        if (null !== $dateTime) {
            $r = $this->cloneToADate($dateTime);

            return true;
        }

        return false;
    }

    /**
     * @param \DateTime|null $r
     */
    public function type_adate_tz_formatted(&$r, $dateFormatted, $formats, ?array $allowedTimezoneTypes = null) : bool
    {
        $r = null;

        $dateTime = null;

        $timezoneNil = Lib::php()->the_timezone_nil();

        if ($dateFormatted instanceof \DateTimeInterface) {
            $dateTime = $dateFormatted;

        } else {
            $formatsList = Lib::php()->to_list($formats);

            if ([] === $formatsList) {
                return false;
            }

            $theType = Lib::type();
            foreach ( $formatsList as $i => $format ) {
                if (! $theType->string_not_empty($formatString, $format)) {
                    return false;
                }

                $formatsList[ $i ] = $formatString;
            }

            if (! (is_string($dateFormatted) && ('' !== $dateFormatted))) {
                return false;
            }

            foreach ( $formatsList as $format ) {
                try {
                    $dateTime = \DateTime::createFromFormat(
                        $format,
                        $dateFormatted,
                        $timezoneNil
                    );
                }
                catch ( \Throwable $e ) {
                    $dateTime = false;
                }
            }

            if (false === $dateTime) {
                $dateTime = null;
            }
        }

        if (null !== $dateTime) {
            $timezone = $dateTime->getTimezone();

            if ($timezone->getName() == $timezoneNil->getName()) {
                $dateTime = null;
            }
        }

        if (null !== $allowedTimezoneTypes) {
            if (null !== $dateTime) {
                $timezoneType = $this->timezone_type($timezone);

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTime = null;
                }
            }
        }

        if (null !== $dateTime) {
            $r = $this->cloneToADate($dateTime);

            return true;
        }

        return false;
    }

    /**
     * @param \DateTimeImmutable|null $r
     */
    public function type_idate_tz_formatted(&$r, $dateFormatted, $formats, ?array $allowedTimezoneTypes = null) : bool
    {
        $r = null;

        $dateTimeImmutable = null;

        $timezoneNil = Lib::php()->the_timezone_nil();

        if ($dateFormatted instanceof \DateTimeInterface) {
            $dateTimeImmutable = $dateFormatted;

        } else {
            $formatsList = Lib::php()->to_list($formats);

            if ([] === $formatsList) {
                return false;
            }

            $theType = Lib::type();
            foreach ( $formatsList as $i => $format ) {
                if (! $theType->string_not_empty($formatString, $format)) {
                    return false;
                }

                $formatsList[ $i ] = $formatString;
            }

            if (! (is_string($dateFormatted) && ('' !== $dateFormatted))) {
                return false;
            }

            foreach ( $formatsList as $format ) {
                try {
                    $dateTimeImmutable = \DateTime::createFromFormat(
                        $format,
                        $dateFormatted,
                        $timezoneNil
                    );
                }
                catch ( \Throwable $e ) {
                    $dateTimeImmutable = false;
                }
            }

            if (false === $dateTimeImmutable) {
                $dateTimeImmutable = null;
            }
        }

        if (null !== $dateTimeImmutable) {
            $timezone = $dateTimeImmutable->getTimezone();

            if ($timezone->getName() == $timezoneNil->getName()) {
                $dateTimeImmutable = null;
            }
        }

        if (null !== $allowedTimezoneTypes) {
            if (null !== $dateTimeImmutable) {
                $timezoneType = $this->timezone_type($timezone);

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTimeImmutable = null;
                }
            }
        }

        if (null !== $dateTimeImmutable) {
            $r = $this->cloneToIDate($dateTimeImmutable);

            return true;
        }

        return false;
    }


    /**
     * @param \DateTimeInterface|null $r
     */
    public function type_date_microtime(&$r, $microtime, $timezoneFallback = null) : bool
    {
        $r = null;

        $timezoneFallback = $timezoneFallback ?? date_default_timezone_get();

        $dateTime = null;

        $status = $this->type_timezone(
            $_timezoneFallback, $timezoneFallback
        );

        if (! $status) {
            return false;
        }

        if ($microtime instanceof \DateTimeInterface) {
            $dateTime = clone $microtime;

        } else {
            if (! Lib::type()->numeric($numeric, $microtime, false, [ &$split ])) {
                return false;
            }

            try {
                if (PHP_VERSION_ID >= 80000) {
                    $dateTime = new \DateTime("@{$numeric}", new \DateTimeZone('UTC'));

                } else {
                    $int = $split[ 1 ];
                    $seconds = $int;

                    $dateTime = new \DateTime("@{$seconds}", new \DateTimeZone('UTC'));
                }
            }
            catch ( \Throwable $e ) {
            }

            if ('' !== $split[ 2 ]) {
                $frac = $split[ 2 ];

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

        $dateTime = $dateTime->setTimezone($_timezoneFallback);

        if (null !== $dateTime) {
            $r = $this->cloneToDate($dateTime);

            return true;
        }

        return false;
    }

    /**
     * @param \DateTime|null $r
     */
    public function type_adate_microtime(&$r, $microtime, $timezoneFallback = null) : bool
    {
        $r = null;

        $timezoneFallback = $timezoneFallback ?? date_default_timezone_get();

        $dateTime = null;

        $status = $this->type_timezone(
            $_timezoneFallback, $timezoneFallback
        );

        if (! $status) {
            return false;
        }

        if ($microtime instanceof \DateTimeInterface) {
            $dateTime = clone $microtime;

        } else {
            if (! Lib::type()->numeric($numeric, $microtime, false, [ &$split ])) {
                return false;
            }

            try {
                if (PHP_VERSION_ID >= 80000) {
                    $dateTime = new \DateTime("@{$numeric}", new \DateTimeZone('UTC'));

                } else {
                    $int = $split[ 1 ];
                    $seconds = $int;

                    $dateTime = new \DateTime("@{$seconds}", new \DateTimeZone('UTC'));
                }
            }
            catch ( \Throwable $e ) {
            }

            if ('' !== $split[ 2 ]) {
                $frac = $split[ 2 ];

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

        $dateTime = $dateTime->setTimezone($_timezoneFallback);

        if (null !== $dateTime) {
            $r = $this->cloneToDate($dateTime);

            return true;
        }

        return false;
    }

    /**
     * @param \DateTimeImmutable|null $r
     */
    public function type_idate_microtime(&$r, $microtime, $timezoneFallback = null) : bool
    {
        $r = null;

        $timezoneFallback = $timezoneFallback ?? date_default_timezone_get();

        $dateTimeImmutable = null;

        $status = $this->type_timezone(
            $_timezoneFallback, $timezoneFallback
        );

        if (! $status) {
            return false;
        }

        if ($microtime instanceof \DateTimeInterface) {
            $dateTimeImmutable = clone $microtime;

        } else {
            if (! Lib::type()->numeric($numeric, $microtime, false, [ &$split ])) {
                return false;
            }

            $timezoneDefault = new \DateTimeZone('UTC');

            try {
                if (PHP_VERSION_ID >= 80000) {
                    $dateTimeImmutable = new \DateTimeImmutable("@{$numeric}", $timezoneDefault);

                } else {
                    $int = $split[ 1 ];
                    $seconds = $int;

                    $dateTimeImmutable = new \DateTimeImmutable("@{$seconds}", $timezoneDefault);
                }
            }
            catch ( \Throwable $e ) {
            }

            if ('' !== $split[ 2 ]) {
                $frac = $split[ 2 ];

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

        $dateTimeImmutable = $dateTimeImmutable->setTimezone($_timezoneFallback);

        if (null !== $dateTimeImmutable) {
            $r = $this->cloneToDate($dateTimeImmutable);

            return true;
        }

        return false;
    }


    /**
     * @param \DateInterval|null $r
     */
    public function type_interval(&$r, $interval) : bool
    {
        $r = null;

        if ($interval instanceof \DateInterval) {
            $r = $interval;

            return true;
        }

        $dateInterval = null;

        $status = false
            || $this->type_interval_duration($dateInterval, $interval)
            || $this->type_interval_datestring($dateInterval, $interval)
            //
            // > commented, autoparsing integers is bad practice
            // || $this->type_interval_microtime($var, $interval)
        ;

        if ($status) {
            $r = $dateInterval;

            return true;
        }

        return false;
    }

    /**
     * @param \DateInterval|null $r
     */
    public function type_interval_duration(&$r, $duration) : bool
    {
        $r = null;

        if ($duration instanceof \DateInterval) {
            $r = $duration;

            return true;
        }

        if (! (is_string($duration) && ('' !== $duration))) {
            return false;
        }

        try {
            $dateInterval = $this->interval_decode($duration);

            $r = $dateInterval;

            return true;
        }
        catch ( \Throwable $e ) {
        }

        return false;
    }

    /**
     * @param \DateInterval|null $r
     */
    public function type_interval_datestring(&$r, $datestring) : bool
    {
        $r = null;

        if ($datestring instanceof \DateInterval) {
            $r = $datestring;

            return true;
        }

        if (! (is_string($datestring) && ('' !== $datestring))) {
            return false;
        }

        try {
            $dateInterval = \DateInterval::createFromDateString($datestring);

            if (false !== $dateInterval) {
                $r = $dateInterval;

                return true;
            }
        }
        catch ( \Throwable $e ) {
        }

        return false;
    }

    /**
     * @param \DateInterval|null $r
     */
    public function type_interval_microtime(&$r, $microtime) : bool
    {
        $r = null;

        if ($microtime instanceof \DateInterval) {
            $r = $microtime;

            return true;
        }

        if (! Lib::type()->numeric($numeric, $microtime, false)) {
            return false;
        }

        try {
            $dateInterval = $this->interval_decode('PT' . $numeric . 'S');

            $r = $dateInterval;

            return true;
        }
        catch ( \Throwable $e ) {
        }

        return false;
    }

    /**
     * @param \DateInterval|null $r
     */
    public function type_interval_ago(&$r, $date, ?\DateTimeInterface $from = null, ?bool $reverse = null) : bool
    {
        $r = null;

        $reverse = $reverse ?? false;

        $isFrom = (false === $reverse);
        // $isUntil = (true === $reverse);

        if ($date instanceof \DateInterval) {
            $r = $date;

            return true;
        }

        if ($date instanceof \DateTimeInterface) {
            $fromDate = $from ?? new \DateTime('now');

            if ($isFrom) {
                $r = $fromDate->diff($date);

            } else {
                // } elseif ($isUntil) {
                $r = $date->diff($fromDate);
            }

            return true;
        }

        return false;
    }


    public function date_remote(\DateTimeInterface $date, $timezoneSet) : \DateTimeInterface
    {
        $status = $this->type_timezone(
            $_timezoneRemote,
            $timezoneSet
        );
        if (! $status) {
            throw new LogicException(
                [ 'This `timezoneRemote` is not allowed', $timezoneSet ]
            );
        }

        $clone = $this->cloneToDate($date);

        $clone = $clone->setTimezone($_timezoneRemote);

        return $clone;
    }

    public function adate_remote(\DateTimeInterface $date, $timezoneSet) : \DateTime
    {
        $status = $this->type_timezone(
            $_timezoneRemote,
            $timezoneSet
        );
        if (! $status) {
            throw new LogicException(
                [ 'This `timezoneRemote` is not allowed', $timezoneSet ]
            );
        }

        $clone = $this->cloneToADate($date);

        $clone = $clone->setTimezone($_timezoneRemote);

        return $clone;
    }

    public function idate_remote(\DateTimeInterface $date, $timezoneSet) : \DateTimeImmutable
    {
        $status = $this->type_timezone(
            $_timezoneRemote,
            $timezoneSet
        );
        if (! $status) {
            throw new LogicException(
                [ 'This `timezoneRemote` is not allowed', $timezoneSet ]
            );
        }

        $clone = $this->cloneToIDate($date);

        $clone = $clone->setTimezone($_timezoneRemote);

        return $clone;
    }


    public function adate_now($timezoneFallback = null) : \DateTime
    {
        $_timezone = null;
        if (null !== $timezoneFallback) {
            $status = $this->type_timezone(
                $_timezone, $timezoneFallback
            );

            if (! $status) {
                throw new LogicException(
                    [ 'The `timezone` is invalid', $timezoneFallback ]
                );
            }
        }

        try {
            $dateTime = new \DateTime('now', $_timezone);
        }
        catch ( \Exception $e ) {
            throw new LogicException('Unable to create datetime', $e);
        }

        return $dateTime;
    }

    public function idate_now($timezoneFallback = null) : \DateTimeImmutable
    {
        $_timezone = null;
        if (null !== $timezoneFallback) {
            $status = $this->type_timezone(
                $_timezone, $timezoneFallback
            );

            if (! $status) {
                throw new LogicException(
                    [ 'The `timezone` is invalid', $timezoneFallback ]
                );
            }
        }

        try {
            $dateTimeImmutable = new \DateTimeImmutable('now', $_timezone);
        }
        catch ( \Exception $e ) {
            throw new LogicException('Unable to create datetime', $e);
        }

        return $dateTimeImmutable;
    }


    public function adate_epoch($timezoneFallback = null) : \DateTime
    {
        $_timezone = null;
        if (null !== $timezoneFallback) {
            $status = $this->type_timezone(
                $_timezone, $timezoneFallback
            );

            if (! $status) {
                throw new LogicException(
                    [ 'The `timezone` is invalid', $timezoneFallback ]
                );
            }
        }

        try {
            $dateTime = new \DateTime('1970-01-01 00:00:00.000000', $_timezone);
        }
        catch ( \Exception $e ) {
            throw new LogicException('Unable to create datetime', $e);
        }

        return $dateTime;
    }

    public function idate_epoch($timezoneFallback = null) : \DateTimeImmutable
    {
        $_timezone = null;
        if (null !== $timezoneFallback) {
            $status = $this->type_timezone(
                $_timezone, $timezoneFallback
            );

            if (! $status) {
                throw new LogicException(
                    [ 'The `timezone` is invalid', $timezoneFallback ]
                );
            }
        }

        try {
            $dateTimeImmutable = new \DateTimeImmutable('1970-01-01 00:00:00.000000', $_timezone);
        }
        catch ( \Exception $e ) {
            throw new LogicException('Unable to create datetime', $e);
        }

        return $dateTimeImmutable;
    }


    public function adate_zero($timezoneFallback = null) : \DateTime
    {
        $_timezone = null;
        if (null !== $timezoneFallback) {
            $status = $this->type_timezone(
                $_timezone, $timezoneFallback
            );

            if (! $status) {
                throw new LogicException(
                    [ 'The `timezone` is invalid', $timezoneFallback ]
                );
            }
        }

        try {
            $dateTime = new \DateTime('0000-01-01 00:00:00.000000', $_timezone);
        }
        catch ( \Exception $e ) {
            throw new LogicException('Unable to create datetime', $e);
        }

        return $dateTime;
    }

    public function idate_zero($timezoneFallback = null) : \DateTimeImmutable
    {
        $_timezone = null;
        if (null !== $timezoneFallback) {
            $status = $this->type_timezone(
                $_timezone, $timezoneFallback
            );

            if (! $status) {
                throw new LogicException(
                    [ 'The `timezone` is invalid', $timezoneFallback ]
                );
            }
        }

        try {
            $dateTimeImmutable = new \DateTimeImmutable('0000-01-01 00:00:00.000000', $_timezone);
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


    /**
     * @param string|\DateTimeInterface|\DateTimeZone $timezone
     *
     * @return int
     */
    public function timezone_type($timezone) : int
    {
        if (! $this->type_timezone($dateTimeZone, $timezone)) {
            throw new LogicException(
                [ 'The `timezone` should be string or instance of \DateTimeZone', $timezone ]
            );
        }

        return (PHP_VERSION_ID >= 70400)
            ? json_decode(json_encode($dateTimeZone))->timezone_type
            : get_object_vars($dateTimeZone)[ 'timezone_type' ];
    }

    /**
     * @param string|\DateTimeInterface|\DateTimeZone $a
     * @param string|\DateTimeInterface|\DateTimeZone $b
     *
     * @noinspection PhpNonStrictObjectEqualityInspection
     */
    public function timezone_same($a, $b) : bool
    {
        if (! $this->type_timezone($aTz, $a)) {
            throw new LogicException(
                [ 'The `a` should be string or instance of \DateTimeZone', $a ]
            );
        }

        if (! $this->type_timezone($bTz, $b)) {
            throw new LogicException(
                [ 'The `a` should be string or instance of \DateTimeZone', $b ]
            );
        }

        return (PHP_VERSION_ID >= 70400)
            ? ($aTz == $bTz)
            : ($aTz->getName() === $bTz->getName());
    }


    public function interval_encode(\DateInterval $interval) : string
    {
        // > ISO 8601

        $search = [ 'M0S', 'H0M', 'DT0H', 'M0D', 'P0Y', 'Y0M', 'P0M' ];
        $replace = [ 'M', 'H', 'DT', 'M', 'P', 'Y', 'P' ];

        if ($interval->f) {
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
     */
    public function interval_decode(string $duration, ?string $intervalClass = null) : \DateInterval
    {
        // > ISO 8601

        $theStr = Lib::str();

        if ('' === $duration) {
            throw new LogicException(
                [ 'The `duration` should be non-empty string' ]
            );
        }

        if (null !== $intervalClass) {
            if (! is_a($intervalClass, \DateInterval::class, true)) {
                throw new LogicException(
                    [
                        'The `intervalClass` should be class-string of: ' . \DateInterval::class,
                        $intervalClass,
                    ]
                );
            }
        }

        $intervalClass = $intervalClass ?? \DateInterval::class;

        $_duration = $duration;

        $regex = '/(\d+\.\d+)([YMWDHS])/';

        $hasDecimalValue = preg_match_all($regex, $_duration, $matches);

        $decimalValueFrac = null;
        $decimalLetter = null;
        if ($hasDecimalValue) {
            $decimal = $matches[ 0 ];
            $decimalSubstr = $matches[ 0 ][ 0 ];
            $decimalValue = $matches[ 1 ][ 0 ];
            $decimalLetter = $matches[ 2 ][ 0 ];

            if (count($decimal) > 1) {
                throw new LogicException(
                    [
                        'The `duration` can contain only one `.` in smallest period (according ISO 8601)',
                        $duration,
                    ]
                );
            }

            if (! $theStr->str_ends($duration, $decimalSubstr, false)) {
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

            $_duration = str_replace($decimalValue, $decimalValueInt, $_duration);
        }

        try {
            $instance = new $intervalClass($_duration);
        }
        catch ( \Throwable $e ) {
            throw new LogicException($e);
        }

        if ($hasDecimalValue) {
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
                    if (false === strpos($duration, 'T')) {
                        $seconds = (int) ($decimalValueFrac * static::INTERVAL_MONTH);

                    } else {
                        $seconds = (int) ($decimalValueFrac * static::INTERVAL_MINUTE);
                    }

                    break;

            endswitch;

            if (null !== $seconds) {
                $nowModified->modify("+{$seconds} seconds");
            }

            $interval = $nowModified->diff($now);

            $instance->y = $interval->y;
            $instance->m = $interval->m;
            $instance->d = $interval->d;
            $instance->h = $interval->h;
            $instance->i = $interval->i;
            $instance->s = $interval->s;

            if (null !== $decimalValueFrac) {
                if ('S' === $decimalLetter) {
                    $instance->f = $decimalValueFrac;
                }
            }
        }

        return $instance;
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
        $formattedSql = $dateTime->format(static::FORMAT_SQL);

        return $formattedSql;
    }

    public function format_sql_msec(\DateTimeInterface $dateTime) : string
    {
        $formattedSql = $dateTime->format(static::FORMAT_SQL);

        $milliseconds = $dateTime->format('v');
        $milliseconds = str_pad($milliseconds, 3, '0', STR_PAD_RIGHT);

        return "{$formattedSql}.{$milliseconds}";
    }

    public function format_sql_usec(\DateTimeInterface $dateTime) : string
    {
        $formattedSql = $dateTime->format(static::FORMAT_SQL);

        $microseconds = $dateTime->format('v');
        $microseconds = str_pad($microseconds, 6, '0', STR_PAD_RIGHT);

        return "{$formattedSql}.{$microseconds}";
    }


    public function format_sql_utc(\DateTimeInterface $dateTime) : string
    {
        $clone = (clone $dateTime)->setTimezone(new \DateTimeZone('UTC'));

        return $this->format_sql($clone);
    }

    public function format_sql_msec_utc(\DateTimeInterface $dateTime) : string
    {
        $clone = (clone $dateTime)->setTimezone(new \DateTimeZone('UTC'));

        return $this->format_sql_msec($clone);
    }

    public function format_sql_usec_utc(\DateTimeInterface $dateTime) : string
    {
        $clone = (clone $dateTime)->setTimezone(new \DateTimeZone('UTC'));

        return $this->format_sql_usec($clone);
    }



    public function format_javascript(\DateTimeInterface $dateTime) : string
    {
        $formatted = $dateTime->format(static::FORMAT_JAVASCRIPT);

        return $formatted;
    }

    public function format_javascript_msec(\DateTimeInterface $dateTime) : string
    {
        $formattedJavascript = $dateTime->format(static::FORMAT_JAVASCRIPT);

        $milliseconds = $dateTime->format('v');
        $milliseconds = str_pad($milliseconds, 3, '0', STR_PAD_RIGHT);

        return "{$formattedJavascript}.{$milliseconds}";
    }

    public function format_javascript_usec(\DateTimeInterface $dateTime) : string
    {
        $formattedJavascript = $dateTime->format(static::FORMAT_JAVASCRIPT);

        $microseconds = $dateTime->format('u');
        $microseconds = str_pad($microseconds, 6, '0', STR_PAD_RIGHT);

        return "{$formattedJavascript}.{$microseconds}";
    }


    protected function cloneToDate(\DateTimeInterface $dateTime) : ?\DateTimeInterface
    {
        return clone $dateTime;
    }

    protected function cloneToADate(\DateTimeInterface $dateTime) : ?\DateTime
    {
        if ($dateTime instanceof \DateTime) {
            return clone $dateTime;

        } elseif ($dateTime instanceof \DateTimeImmutable) {
            return \DateTime::createFromImmutable($dateTime);
        }

        throw new LogicException([ 'Unknown `dateTime`', $dateTime ]);
    }

    protected function cloneToIDate(\DateTimeInterface $dateTime) : ?\DateTimeImmutable
    {
        if ($dateTime instanceof \DateTime) {
            return \DateTimeImmutable::createFromMutable($dateTime);

        } elseif ($dateTime instanceof \DateTimeImmutable) {
            return clone $dateTime;
        }

        throw new LogicException([ 'Unknown `dateTime`', $dateTime ]);
    }
}
