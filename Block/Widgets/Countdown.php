<?php
declare(strict_types=1);

namespace MageOS\Widgetkit\Block\Widgets;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use MageOS\Widgetkit\Block\Adminhtml\WidgetField\DateTime;

class Countdown extends Template implements BlockInterface
{
    public function __construct(
        protected TimezoneInterface $timezone,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Parses the `mageos_countdown_end` field value (stored in the DateTime field's fixed
     * format) to a unix timestamp.
     *
     * @param string $paramName
     * @return int|null
     */
    public function getTimestamp(string $paramName): ?int
    {
        $value = trim((string)$this->getData($paramName));
        if ($value === '') {
            return null;
        }

        // DateTime::createFromFormat() doesn't reject an out-of-range numeric field - it
        // silently *overflows* it into a wildly wrong date (e.g. a hand-typed day-first
        // "30/08/2026 15:00", month "30", silently became 2028-06-08 instead of failing to
        // parse). Validating the literal MM/DD/YYYY HH:MM shape up front closes that off:
        // anything malformed or ambiguous is treated as unset rather than silently misread.
        if (!preg_match('/^(0[1-9]|1[0-2])\/(0[1-9]|[12]\d|3[01])\/(\d{4}) ([01]\d|2[0-3]):([0-5]\d)$/', $value)) {
            return null;
        }

        // The leading "!" resets every field the format string doesn't cover (seconds) to
        // zero instead of defaulting them to the current wall-clock instant.
        $date = \DateTime::createFromFormat(
            '!' . DateTime::PHP_DATETIME_FORMAT,
            $value,
            new \DateTimeZone($this->timezone->getConfigTimezone())
        );

        return $date instanceof \DateTime ? $date->getTimestamp() : null;
    }

    /**
     * A genuine Unix timestamp - "now" is the same instant regardless of timezone, so unlike
     * getTimestamp() (which parses an admin-entered wall-clock string against the store's
     * timezone) this needs no timezone handling at all. Deliberately not using
     * TimezoneInterface::scopeTimeStamp() here: it formats "now" as a wall-clock string in the
     * store's timezone, then re-parses that same string against PHP's default timezone via
     * strtotime() - two different timezones for one value, off by the gap between them whenever
     * the store timezone isn't the PHP process's default.
     *
     * @return int
     */
    public function getNowTimestamp(): int
    {
        return time();
    }

    /**
     * Whether the countdown has already reached its end (now >= end). An unset end time is
     * treated as expired too: there's nothing to count down to, so the finished-state markup
     * is the correct thing to render rather than a live timer ticking down from a 0 timestamp.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        $end = $this->getTimestamp('mageos_countdown_end');
        return $end === null || $this->getNowTimestamp() >= $end;
    }
}
