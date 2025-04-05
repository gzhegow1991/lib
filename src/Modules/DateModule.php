<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;


class DateModule
{
    /**
     * @param \DateTimeZone|null $result
     */
    public function type_timezone(&$result, $value, array $allowedTimezoneTypes = null) : bool
    {
        $result = null;

        $timezone = null;

        if ($value instanceof \DateTimeZone) {
            $timezone = $value;

        } elseif ($value instanceof \DateTimeInterface) {
            $timezone = $value->getTimezone();

        } else {
            try {
                $timezone = new \DateTimeZone($value);
            }
            catch ( \Throwable $e ) {
            }
        }

        if (null !== $timezone) {
            if (null !== $allowedTimezoneTypes) {
                $timezoneType = get_object_vars($timezone)[ 'timezone_type' ];

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    return false;
                }
            }
        }

        if (null !== $timezone) {
            $result = $timezone;

            return true;
        }

        return false;
    }

    /**
     * @param \DateTimeZone|null $result
     */
    public function type_timezone_offset(&$result, $timezoneOrOffset) : bool
    {
        $result = null;

        $timezone = null;

        if ($timezoneOrOffset instanceof \DateTimeZone) {
            $timezone = $timezoneOrOffset;

        } elseif ($timezoneOrOffset instanceof \DateTimeInterface) {
            $timezone = $timezoneOrOffset->getTimezone();

        } else {
            try {
                $timezone = new \DateTimeZone($timezoneOrOffset);
            }
            catch ( \Throwable $e ) {
            }
        }

        if (null !== $timezone) {
            $timezoneType = get_object_vars($timezone)[ 'timezone_type' ];
            if ($timezoneType !== 1) {
                $timezone = null;
            }
        }

        if (null !== $timezone) {
            $result = $timezone;

            return true;
        }

        return false;
    }

    /**
     * @param \DateTimeZone|null $result
     */
    public function type_timezone_abbr(&$result, $timezoneOrAbbr) : bool
    {
        $result = null;

        $timezone = null;

        if ($timezoneOrAbbr instanceof \DateTimeZone) {
            $timezone = $timezoneOrAbbr;

        } elseif ($timezoneOrAbbr instanceof \DateTimeInterface) {
            $timezone = $timezoneOrAbbr->getTimezone();

        } else {
            try {
                $timezone = new \DateTimeZone($timezoneOrAbbr);
            }
            catch ( \Throwable $e ) {
            }
        }

        if (null !== $timezone) {
            $timezoneType = get_object_vars($timezone)[ 'timezone_type' ];
            if ($timezoneType !== 2) {
                $timezone = null;
            }
        }

        if (null !== $timezone) {
            $result = $timezone;

            return true;
        }

        return false;
    }

    /**
     * @param \DateTimeZone|null $result
     */
    public function type_timezone_name(&$result, $timezoneOrName) : bool
    {
        $result = null;

        $timezone = null;

        if ($timezoneOrName instanceof \DateTimeZone) {
            $timezone = $timezoneOrName;

        } elseif ($timezoneOrName instanceof \DateTimeInterface) {
            $timezone = $timezoneOrName->getTimezone();

        } else {
            try {
                $timezone = new \DateTimeZone($timezoneOrName);
            }
            catch ( \Throwable $e ) {
            }
        }

        if (null !== $timezone) {
            $timezoneType = get_object_vars($timezone)[ 'timezone_type' ];
            if ($timezoneType !== 3) {
                $timezone = null;
            }
        }

        if (null !== $timezone) {
            $result = $timezone;

            return true;
        }

        return false;
    }

    /**
     * @param \DateTimeZone|null $result
     */
    public function type_timezone_nameabbr(&$result, $timezoneOrNameOrAbbr) : bool
    {
        $result = null;

        $timezone = null;

        if ($timezoneOrNameOrAbbr instanceof \DateTimeZone) {
            $timezone = $timezoneOrNameOrAbbr;

        } elseif ($timezoneOrNameOrAbbr instanceof \DateTimeInterface) {
            $timezone = $timezoneOrNameOrAbbr->getTimezone();

        } else {
            try {
                $timezone = new \DateTimeZone($timezoneOrNameOrAbbr);
            }
            catch ( \Throwable $e ) {
            }
        }

        if (null !== $timezone) {
            $timezoneType = get_object_vars($timezone)[ 'timezone_type' ];

            if (! (
                ($timezoneType === 2)
                || ($timezoneType === 3)
            )) {
                $timezone = null;
            }
        }

        if (null !== $timezone) {
            $result = $timezone;

            return true;
        }

        return false;
    }


    /**
     * @param \DateTimeInterface|null $result
     */
    public function type_date(&$result, $datestring, $timezoneFallback = null, array $allowedTimezoneTypes = null) : bool
    {
        $result = null;

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

        if (null !== $allowedTimezoneTypes) {
            if (null !== $dateTime) {
                $timezone = $dateTime->getTimezone();
                $timezoneType = get_object_vars($timezone)[ 'timezone_type' ];

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTime = null;
                }
            }
        }

        if (null !== $dateTime) {
            $result = $this->cloneToDate($dateTime);

            return true;
        }

        return false;
    }

    /**
     * @param \DateTime|null $result
     */
    public function type_adate(&$result, $datestring, $timezoneFallback = null, array $allowedTimezoneTypes = null) : bool
    {
        $result = null;

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

        if (null !== $allowedTimezoneTypes) {
            if (null !== $dateTime) {
                $timezone = $dateTime->getTimezone();
                $timezoneType = get_object_vars($timezone)[ 'timezone_type' ];

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTime = null;
                }
            }
        }

        if (null !== $dateTime) {
            $result = $this->cloneToADate($dateTime);

            return true;
        }

        return false;
    }

    /**
     * @param \DateTimeImmutable|null $result
     */
    public function type_idate(&$result, $datestring, $timezoneFallback = null, array $allowedTimezoneTypes = null) : bool
    {
        $result = null;

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

        if (null !== $allowedTimezoneTypes) {
            if (null !== $dateTimeImmutable) {
                $timezone = $dateTimeImmutable->getTimezone();
                $timezoneType = get_object_vars($timezone)[ 'timezone_type' ];

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTimeImmutable = null;
                }
            }
        }

        if (null !== $dateTimeImmutable) {
            $result = $this->cloneToIDate($dateTimeImmutable);

            return true;
        }

        return false;
    }


    /**
     * @param \DateTimeInterface|null $result
     */
    public function type_date_tz(&$result, $datestring, array $allowedTimezoneTypes = null) : bool
    {
        $result = null;

        $dateTime = null;

        $timezoneNil = Lib::type()->the_timezone_nil();

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
                $timezoneType = get_object_vars($timezone)[ 'timezone_type' ];

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTime = null;
                }
            }
        }

        if (null !== $dateTime) {
            $result = $this->cloneToDate($dateTime);

            return true;
        }

        return false;
    }

    /**
     * @param \DateTime|null $result
     */
    public function type_adate_tz(&$result, $datestring, array $allowedTimezoneTypes = null) : bool
    {
        $result = null;

        $dateTime = null;

        $timezoneNil = Lib::type()->the_timezone_nil();

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
                $timezoneType = get_object_vars($timezone)[ 'timezone_type' ];

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTime = null;
                }
            }
        }

        if (null !== $dateTime) {
            $result = $this->cloneToADate($dateTime);

            return true;
        }

        return false;
    }

    /**
     * @param \DateTimeImmutable|null $result
     */
    public function type_idate_tz(&$result, $datestring, array $allowedTimezoneTypes = null) : bool
    {
        $result = null;

        $dateTimeImmutable = null;

        $timezoneNil = Lib::type()->the_timezone_nil();

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
                $timezoneType = get_object_vars($timezone)[ 'timezone_type' ];

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTimeImmutable = null;
                }
            }
        }

        if (null !== $dateTimeImmutable) {
            $result = $this->cloneToIDate($dateTimeImmutable);

            return true;
        }

        return false;
    }


    /**
     * @param \DateTimeInterface|null $result
     */
    public function type_date_formatted(&$result, string $format, $dateFormatted, $timezoneFallback = null, array $allowedTimezoneTypes = null) : bool
    {
        $result = null;

        $dateTime = null;

        if ($dateFormatted instanceof \DateTimeInterface) {
            $dateTime = $dateFormatted;

        } else {
            if ('' === $format) {
                return false;
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

            if (false === $dateTime) {
                $dateTime = null;
            }
        }

        if (null !== $allowedTimezoneTypes) {
            if (null !== $dateTime) {
                $timezone = $dateTime->getTimezone();
                $timezoneType = get_object_vars($timezone)[ 'timezone_type' ];

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTime = null;
                }
            }
        }

        if (null !== $dateTime) {
            $result = $this->cloneToDate($dateTime);

            return true;
        }

        return false;
    }

    /**
     * @param \DateTime|null $result
     */
    public function type_adate_formatted(&$result, string $format, $dateFormatted, $timezoneFallback = null, array $allowedTimezoneTypes = null) : bool
    {
        $result = null;

        $dateTime = null;

        if ($dateFormatted instanceof \DateTimeInterface) {
            $dateTime = $dateFormatted;

        } else {
            if ('' === $format) {
                return false;
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

            if (false === $dateTime) {
                $dateTime = null;
            }
        }

        if (null !== $allowedTimezoneTypes) {
            if (null !== $dateTime) {
                $timezone = $dateTime->getTimezone();
                $timezoneType = get_object_vars($timezone)[ 'timezone_type' ];

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTime = null;
                }
            }
        }

        if (null !== $dateTime) {
            $result = $this->cloneToADate($dateTime);

            return true;
        }

        return false;
    }

    /**
     * @param \DateTimeImmutable|null $result
     */
    public function type_idate_formatted(&$result, string $format, $dateFormatted, $timezoneFallback = null, array $allowedTimezoneTypes = null) : bool
    {
        $result = null;

        $allowedTimezoneTypes = $allowedTimezoneTypes ?? true;

        $dateTimeImmutable = null;

        if ($dateFormatted instanceof \DateTimeInterface) {
            $dateTimeImmutable = $dateFormatted;

        } else {
            if ('' === $format) {
                return false;
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

            if (false === $dateTimeImmutable) {
                $dateTimeImmutable = null;
            }
        }

        if (null !== $allowedTimezoneTypes) {
            if (null !== $dateTimeImmutable) {
                $timezone = $dateTimeImmutable->getTimezone();
                $timezoneType = get_object_vars($timezone)[ 'timezone_type' ];

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTimeImmutable = null;
                }
            }
        }

        if (null !== $dateTimeImmutable) {
            $result = $this->cloneToIDate($dateTimeImmutable);

            return true;
        }

        return false;
    }


    /**
     * @param \DateTimeInterface|null $result
     */
    public function type_date_tz_formatted(&$result, string $format, $dateFormatted, array $allowedTimezoneTypes = null) : bool
    {
        $result = null;

        $dateTime = null;

        $timezoneNil = Lib::type()->the_timezone_nil();

        if ($dateFormatted instanceof \DateTimeInterface) {
            $dateTime = $dateFormatted;

        } else {
            if ('' === $format) {
                return false;
            }

            if (! (is_string($dateFormatted) && ('' !== $dateFormatted))) {
                return false;
            }

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
                $timezoneType = get_object_vars($timezone)[ 'timezone_type' ];

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTime = null;
                }
            }
        }

        if (null !== $dateTime) {
            $result = $this->cloneToADate($dateTime);

            return true;
        }

        return false;
    }

    /**
     * @param \DateTime|null $result
     */
    public function type_adate_tz_formatted(&$result, string $format, $dateFormatted, array $allowedTimezoneTypes = null) : bool
    {
        $result = null;

        $dateTime = null;

        $timezoneNil = Lib::type()->the_timezone_nil();

        if ($dateFormatted instanceof \DateTimeInterface) {
            $dateTime = $dateFormatted;

        } else {
            if ('' === $format) {
                return false;
            }

            if (! (is_string($dateFormatted) && ('' !== $dateFormatted))) {
                return false;
            }

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
                $timezoneType = get_object_vars($timezone)[ 'timezone_type' ];

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTime = null;
                }
            }
        }

        if (null !== $dateTime) {
            $result = $this->cloneToADate($dateTime);

            return true;
        }

        return false;
    }

    /**
     * @param \DateTimeImmutable|null $result
     */
    public function type_idate_tz_formatted(&$result, string $format, $dateFormatted, array $allowedTimezoneTypes = null) : bool
    {
        $result = null;

        $dateTimeImmutable = null;

        $timezoneNil = Lib::type()->the_timezone_nil();

        if ($dateFormatted instanceof \DateTimeInterface) {
            $dateTimeImmutable = $dateFormatted;

        } else {
            if ('' === $format) {
                return false;
            }

            if (! (is_string($dateFormatted) && ('' !== $dateFormatted))) {
                return false;
            }

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
                $timezoneType = get_object_vars($timezone)[ 'timezone_type' ];

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTimeImmutable = null;
                }
            }
        }

        if (null !== $dateTimeImmutable) {
            $result = $this->cloneToIDate($dateTimeImmutable);

            return true;
        }

        return false;
    }


    /**
     * @param \DateTimeInterface|null $result
     */
    public function type_date_microtime(&$result, $microtime, $timezoneSet = null, array $allowedTimezoneTypes = null) : bool
    {
        $result = null;

        $dateTime = null;

        $_timezoneSet = null;
        if ($hasTimezoneSet = (null !== $timezoneSet)) {
            $status = $this->type_timezone(
                $_timezoneSet, $timezoneSet
            );

            if (! $status) {
                return false;
            }
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

                $dateTime = $dateTime->setTime(
                    (int) $dateTime->format('H'),
                    (int) $dateTime->format('i'),
                    (int) $dateTime->format('s'),
                    (int) $microseconds
                );
            }
        }

        if ($hasTimezoneSet) {
            /** @var \DateTimeZone $_timezoneSet */

            $dateTime = $dateTime->setTimezone($_timezoneSet);
        }

        if (null !== $allowedTimezoneTypes) {
            if (null !== $dateTime) {
                $timezone = $dateTime->getTimezone();
                $timezoneType = get_object_vars($timezone)[ 'timezone_type' ];

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTime = null;
                }
            }
        }

        if (null !== $dateTime) {
            $result = $this->cloneToDate($dateTime);

            return true;
        }

        return false;
    }

    /**
     * @param \DateTime|null $result
     */
    public function type_adate_microtime(&$result, $microtime, $timezoneSet = null, array $allowedTimezoneTypes = null) : bool
    {
        $result = null;

        $dateTime = null;

        $_timezoneSet = null;
        if ($hasTimezoneSet = (null !== $timezoneSet)) {
            $status = $this->type_timezone(
                $_timezoneSet, $timezoneSet
            );

            if (! $status) {
                return false;
            }
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

                $dateTime = $dateTime->setTime(
                    (int) $dateTime->format('H'),
                    (int) $dateTime->format('i'),
                    (int) $dateTime->format('s'),
                    (int) $microseconds
                );
            }
        }

        if ($hasTimezoneSet) {
            /** @var \DateTimeZone $_timezoneSet */

            $dateTime = $dateTime->setTimezone($_timezoneSet);
        }

        if (null !== $allowedTimezoneTypes) {
            if (null !== $dateTime) {
                $timezone = $dateTime->getTimezone();
                $timezoneType = get_object_vars($timezone)[ 'timezone_type' ];

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTime = null;
                }
            }
        }

        if (null !== $dateTime) {
            $result = $this->cloneToDate($dateTime);

            return true;
        }

        return false;
    }

    /**
     * @param \DateTimeImmutable|null $result
     */
    public function type_idate_microtime(&$result, $microtime, $timezoneSet = null, array $allowedTimezoneTypes = null) : bool
    {
        $result = null;

        $dateTimeImmutable = null;

        $_timezoneSet = null;
        if ($hasTimezoneSet = (null !== $timezoneSet)) {
            $status = $this->type_timezone(
                $_timezoneSet, $timezoneSet
            );

            if (! $status) {
                return false;
            }
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

                $dateTimeImmutable = $dateTimeImmutable->setTime(
                    (int) $dateTimeImmutable->format('H'),
                    (int) $dateTimeImmutable->format('i'),
                    (int) $dateTimeImmutable->format('s'),
                    (int) $microseconds
                );
            }
        }

        if ($hasTimezoneSet) {
            /** @var \DateTimeZone $_timezoneSet */

            $dateTimeImmutable = $dateTimeImmutable->setTimezone($_timezoneSet);
        }

        if (null !== $allowedTimezoneTypes) {
            if (null !== $dateTimeImmutable) {
                $timezone = $dateTimeImmutable->getTimezone();
                $timezoneType = get_object_vars($timezone)[ 'timezone_type' ];

                if (! in_array($timezoneType, $allowedTimezoneTypes, true)) {
                    $dateTimeImmutable = null;
                }
            }
        }

        if (null !== $dateTimeImmutable) {
            $result = $this->cloneToDate($dateTimeImmutable);

            return true;
        }

        return false;
    }


    /**
     * @param \DateInterval|null $result
     */
    public function type_interval(&$result, $interval) : bool
    {
        $result = null;

        if ($interval instanceof \DateInterval) {
            $result = $interval;

            return true;
        }

        $var = null;

        $status = null
            || $this->type_interval_duration($var, $interval)
            || $this->type_interval_datestring($var, $interval)
            //
            // > commented, autoparsing integers is bad practice
            // || $this->type_interval_microtime($var, $interval)
        ;

        if ($status) {
            $result = $var;

            return true;
        }

        return false;
    }

    /**
     * @param \DateInterval|null $result
     */
    public function type_interval_duration(&$result, $duration) : bool
    {
        $result = null;

        if ($duration instanceof \DateInterval) {
            $result = $duration;

            return true;
        }

        if (! (is_string($duration) && ('' !== $duration))) {
            return false;
        }

        try {
            $dateInterval = Lib::format()->interval_decode($duration);

            $result = $dateInterval;

            return true;
        }
        catch ( \Throwable $e ) {
        }

        return false;
    }

    /**
     * @param \DateInterval|null $result
     */
    public function type_interval_datestring(&$result, $datestring) : bool
    {
        $result = null;

        if ($datestring instanceof \DateInterval) {
            $result = $datestring;

            return true;
        }

        if (! (is_string($datestring) && ('' !== $datestring))) {
            return false;
        }

        try {
            $dateInterval = \DateInterval::createFromDateString($datestring);

            if (false !== $dateInterval) {
                $result = $dateInterval;

                return true;
            }
        }
        catch ( \Throwable $e ) {
        }

        return false;
    }

    /**
     * @param \DateInterval|null $result
     */
    public function type_interval_microtime(&$result, $microtime) : bool
    {
        $result = null;

        if ($microtime instanceof \DateInterval) {
            $result = $microtime;

            return true;
        }

        if (! Lib::type()->numeric($numeric, $microtime, false)) {
            return false;
        }

        try {
            $dateInterval = Lib::format()->interval_decode('PT' . $numeric . 'S');

            $result = $dateInterval;

            return true;
        }
        catch ( \Throwable $e ) {
        }

        return false;
    }

    /**
     * @param \DateInterval|null $result
     */
    public function type_interval_ago(&$result, $date, \DateTimeInterface $from = null, bool $reverse = null) : bool
    {
        $result = null;

        $reverse = $reverse ?? false;

        $isFrom = (false === $reverse);
        $isUntil = (true === $reverse);

        if ($date instanceof \DateInterval) {
            $result = $date;

            return true;
        }

        if ($date instanceof \DateTimeInterface) {
            $_from = $from ?? new \DateTime('now');

            if ($isFrom) {
                $result = $_from->diff($date);

            } else {
                // } elseif ($isUntil) {
                $result = $date->diff($_from);
            }

            return true;
        }

        return false;
    }


    public function date_remote(\DateTimeInterface $date, $timezoneSet, array $allowedTimezoneTypes = null) : \DateTimeInterface
    {
        $status = $this->type_timezone(
            $_timezoneRemote,
            $timezoneSet,
            $allowedTimezoneTypes
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

    public function adate_remote(\DateTimeInterface $date, $timezoneSet, array $allowedTimezoneTypes = null) : \DateTime
    {
        $status = $this->type_timezone(
            $_timezoneRemote,
            $timezoneSet,
            $allowedTimezoneTypes
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

    public function idate_remote(\DateTimeInterface $date, $timezoneSet, array $allowedTimezoneTypes = null) : \DateTimeInterface
    {
        $status = $this->type_timezone(
            $_timezoneRemote,
            $timezoneSet,
            $allowedTimezoneTypes
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


    public function adate_now($timezone = null, array $allowedTimezoneTypes = null) : \DateTime
    {
        $_timezone = null;
        if (null !== $timezone) {
            $status = $this->type_timezone(
                $_timezone, $timezone
            );

            if (! $status) {
                throw new LogicException(
                    [ 'The `timezone` is invalid', $timezone ]
                );
            }
        }

        try {
            $dateTime = new \DateTime('now', $_timezone);
        }
        catch ( \Exception $e ) {
            throw new LogicException('Unable to create datetime', $e);
        }

        if (null !== $allowedTimezoneTypes) {
            $tz = $dateTime->getTimezone();
            $tzType = get_object_vars($timezone)[ 'timezone_type' ];

            if (! in_array($tzType, $allowedTimezoneTypes, true)) {
                throw new LogicException(
                    [ 'This `timezone` is not allowed', $tz ]
                );
            }
        }

        return $dateTime;
    }

    public function idate_now($timezone = null, array $allowedTimezoneTypes = null) : \DateTimeInterface
    {
        $_timezone = null;
        if (null !== $timezone) {
            $status = $this->type_timezone(
                $_timezone, $timezone
            );

            if (! $status) {
                throw new LogicException(
                    [ 'The `timezone` is invalid', $timezone ]
                );
            }
        }

        try {
            $dateTimeImmutable = new \DateTimeImmutable('now', $_timezone);
        }
        catch ( \Exception $e ) {
            throw new LogicException('Unable to create datetime', $e);
        }

        if (null !== $allowedTimezoneTypes) {
            $tz = $dateTimeImmutable->getTimezone();
            $tzType = get_object_vars($timezone)[ 'timezone_type' ];

            if (! in_array($tzType, $allowedTimezoneTypes, true)) {
                throw new LogicException(
                    [ 'This `timezone` is not allowed', $tz ]
                );
            }
        }

        return $dateTimeImmutable;
    }


    public function adate_epoch($timezone = null, array $allowedTimezoneTypes = null) : \DateTime
    {
        $allowOffset = $allowOffset ?? true;

        $_timezone = null;
        if (null !== $timezone) {
            $status = $this->type_timezone(
                $_timezone, $timezone
            );

            if (! $status) {
                throw new LogicException(
                    [ 'The `timezone` is invalid', $timezone ]
                );
            }
        }

        try {
            $dateTime = new \DateTime('1970-01-01 00:00:00.000000', $_timezone);
        }
        catch ( \Exception $e ) {
            throw new LogicException('Unable to create datetime', $e);
        }

        if (null !== $allowedTimezoneTypes) {
            $tz = $dateTime->getTimezone();
            $tzType = get_object_vars($timezone)[ 'timezone_type' ];

            if (! in_array($tzType, $allowedTimezoneTypes, true)) {
                throw new LogicException(
                    [ 'This `timezone` is not allowed', $tz ]
                );
            }
        }

        return $dateTime;
    }

    public function idate_epoch($timezone = null, array $allowedTimezoneTypes = null) : \DateTimeInterface
    {
        $allowOffset = $allowOffset ?? true;

        $_timezone = null;
        if (null !== $timezone) {
            $status = $this->type_timezone(
                $_timezone, $timezone
            );

            if (! $status) {
                throw new LogicException(
                    [ 'The `timezone` is invalid', $timezone ]
                );
            }
        }

        try {
            $dateTimeImmutable = new \DateTimeImmutable('1970-01-01 00:00:00.000000', $_timezone);
        }
        catch ( \Exception $e ) {
            throw new LogicException('Unable to create datetime', $e);
        }

        if (null !== $allowedTimezoneTypes) {
            $tz = $dateTimeImmutable->getTimezone();
            $tzType = get_object_vars($timezone)[ 'timezone_type' ];

            if (! in_array($tzType, $allowedTimezoneTypes, true)) {
                throw new LogicException(
                    [ 'This `timezone` is not allowed', $tz ]
                );
            }
        }

        return $dateTimeImmutable;
    }


    public function adate_zero($timezone = null, array $allowedTimezoneTypes = null) : \DateTime
    {
        $allowOffset = $allowOffset ?? true;

        $_timezone = null;
        if (null !== $timezone) {
            $status = $this->type_timezone(
                $_timezone, $timezone
            );

            if (! $status) {
                throw new LogicException(
                    [ 'The `timezone` is invalid', $timezone ]
                );
            }
        }

        try {
            $dateTime = new \DateTime('0000-01-01 00:00:00.000000', $_timezone);
        }
        catch ( \Exception $e ) {
            throw new LogicException('Unable to create datetime', $e);
        }

        if (null !== $allowedTimezoneTypes) {
            $tz = $dateTime->getTimezone();
            $tzType = get_object_vars($timezone)[ 'timezone_type' ];

            if (! in_array($tzType, $allowedTimezoneTypes, true)) {
                throw new LogicException(
                    [ 'This `timezone` is not allowed', $tz ]
                );
            }
        }

        return $dateTime;
    }

    public function idate_zero($timezone = null, array $allowedTimezoneTypes = null) : \DateTimeInterface
    {
        $allowOffset = $allowOffset ?? true;

        $_timezone = null;
        if (null !== $timezone) {
            $status = $this->type_timezone(
                $_timezone, $timezone
            );

            if (! $status) {
                throw new LogicException(
                    [ 'The `timezone` is invalid', $timezone ]
                );
            }
        }

        try {
            $dateTimeImmutable = new \DateTimeImmutable('0000-01-01 00:00:00.000000', $_timezone);
        }
        catch ( \Exception $e ) {
            throw new LogicException('Unable to create datetime', $e);
        }

        if (null !== $allowedTimezoneTypes) {
            $tz = $dateTimeImmutable->getTimezone();
            $tzType = get_object_vars($timezone)[ 'timezone_type' ];

            if (! in_array($tzType, $allowedTimezoneTypes, true)) {
                throw new LogicException(
                    [ 'This `timezone` is not allowed', $tz ]
                );
            }
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
