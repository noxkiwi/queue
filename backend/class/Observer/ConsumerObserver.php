<?php declare(strict_types = 1);
namespace noxkiwi\queue\Observer;

use noxkiwi\observing\Observable\ObservableInterface;
use noxkiwi\observing\Observer;

/**
 * I am the observer for all consumers.
 *
 * @package      noxkiwi\queue\Observer
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2020 noxkiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
class ConsumerObserver extends Observer
{
    public const RUN     = 'run';
    public const FAIL    = 'fail';
    public const SUCCESS = 'success';
    public const TRY     = 'try';
    public const TOSSED  = 'tossed';
    /** @var int I am the amount of run cycles that were used. */
    public static int $runs = 0;
    /** @var int I am the amount of tries that were used. */
    public static int $tries = 0;
    /** @var int I am the amount of successfully processed messages. */
    public static int $success = 0;
    /** @var int I am the amount of failed messages. */
    public static int $fail = 0;
    /** @var int I am the amount of tossed messages. */
    public static int $tossed = 0;

    /**
     * @inheritDoc
     */
    public function update(ObservableInterface $observable, string $type): void
    {
        switch ($type) {
            case self::RUN:
                static::$runs++;

                return;
            case self::TRY:
                static::$tries++;

                return;
            case self::SUCCESS:
                static::$success++;

                return;
            case self::FAIL:
                static::$fail++;

                return;
            case self::TOSSED:
                static::$tossed++;

                return;
            default:
        }
    }
}
