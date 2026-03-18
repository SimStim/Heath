<?php

declare(strict_types=1);

namespace Lea\Domain;

use DateTime;
use DateTimeZone;
use Exception;

class Date
{
    public function __construct(
        private(set) string $created = "" {
            set => $this->parseDate(value: $value, format: "Y-m-d");
        },
        private(set) string $modified = "" {
            set => $this->parseDate(value: $value, format: 'Y-m-d\TH:i:s\Z', tz: new DateTimeZone(timezone: "UTC"));
        },
        private(set) string $issued = "" {
            set => $this->parseDate(value: $value, format: "Y-m-d");
        },
    )
    {
    }

    /**
     * Try parsing the given date string strictly.
     *
     * Return values:
     * - Null if parsing was unsuccessful.
     * - DateTime object otherwise
     *
     * @param string $value
     * @param string $format
     * @param DateTimeZone|null $tz
     * @return DateTime|null
     */
    private function parseDateStrict(string $value, string $format, ?DateTimeZone $tz = null): ?DateTime
    {
        $dateTime = DateTime::createFromFormat(format: "!" . $format, datetime: $value, timezone: $tz);
        if ($dateTime === false) return null;
        $errors = DateTime::getLastErrors();
        if ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) return null;
        return $dateTime;
    }

    /**
     * Parse the given date string, supporting natural language like "now," "today," etc.
     *
     * Return values:
     * - Empty string if unsuccessful.
     * - Sanitized version of the date string if successful.
     *
     * @param string $value
     * @param string $format
     * @param DateTimeZone|null $tz
     * @return string
     */
    private function parseDate(string $value, string $format, ?DateTimeZone $tz = null): string
    {
        $dateTime = $this->parseDateStrict($value, $format, $tz);
        if ($dateTime === null) {
            try {
                $dateTime = new DateTime($value, $tz);
            } catch (Exception) {
                return "";
            }
        }
        return $dateTime->format($format);
    }

    /**
     * Checks if dates have been extracted correctly
     *
     * Return values:
     * - True on valid dates on file.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return ($this->created !== "" && $this->modified !== "" && $this->issued !== "");
    }
}