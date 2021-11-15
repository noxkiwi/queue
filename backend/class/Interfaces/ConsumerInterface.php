<?php declare(strict_types = 1);
namespace noxkiwi\queue\Interfaces;

use noxkiwi\queue\Message;

/**
 * I am the interface for Consumers.
 *
 * @package      noxkiwi\queue\Interfaces
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2020 noxkiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
interface ConsumerInterface
{
    /**
     * I will process the given $message.
     *
     * @param \noxkiwi\queue\Message $message
     *
     * @return bool
     */
    public function process(Message $message): bool;

    /**
     * I will take care that the Consumer connects and starts polling for messages.
     */
    public function run(): void;
}
