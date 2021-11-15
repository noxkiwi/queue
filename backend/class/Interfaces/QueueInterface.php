<?php declare(strict_types = 1);
namespace noxkiwi\queue\Interfaces;

use noxkiwi\queue\Message;

/**
 * I am the interface for all queues.
 *
 * @package      noxkiwi\queue\Interfaces
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2020 noxkiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
interface QueueInterface
{
    /**
     * I will queue the given $message.
     *
     * @param \noxkiwi\queue\Message $message
     */
    public function add(Message $message): void;
}
