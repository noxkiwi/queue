<?php declare(strict_types = 1);
namespace noxkiwi\queue;

use function hash;
use function microtime;
use function mt_rand;
use function substr;

/**
 * I am the main Message object
 *
 * @package      noxkiwi\queue
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2020 noxkiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
abstract class Message
{
    /** @var string I am the unique ID of this message for log usage. */
    public string $uniqueId = '';
    /** @var int I am the amount of tries that have been used. */
    public int $tries = 0;
    /** @var int I am the amount of tries will be done. If exceeded, the message will be tossed. */
    public int $maxTries = 10;
    /** @var int I am the amount of seconds during that the message will be untouched in queue. */
    public int $interval = 60;
    /** @var int I am the UNIX timestamp of the last try. */
    public int $lastTry;

    /**
     * I will construct the message and put some defaults in it,
     * as well as the uniqueID of the message.
     */
    public function __construct()
    {
        $this->uniqueId = substr(hash('sha256', mt_rand() . microtime(true)), 0, 64);
        $this->tries    = 0;
        $this->maxTries = 10;
        $this->interval = 60;
        $this->uniqueId = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
    }
}
