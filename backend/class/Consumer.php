<?php declare(strict_types = 1);
namespace noxkiwi\queue;

use noxkiwi\log\Log\CliLog;
use noxkiwi\log\LogLevel;
use noxkiwi\log\Traits\LogTrait;
use noxkiwi\observing\Observable\ObservableInterface;
use noxkiwi\observing\Traits\ObservableTrait;
use noxkiwi\queue\Interfaces\ConsumerInterface;

/**
 * I am the core Message object
 *
 * @package      noxkiwi\queue
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2020 noxkiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
abstract class Consumer implements ConsumerInterface, ObservableInterface
{
    use ObservableTrait;
    use LogTrait;

    protected const MESSAGE_TYPES = [];

    /**
     * @throws \noxkiwi\singleton\Exception\SingletonException
     */
    public function __construct()
    {
        $this->addLogger(
            [
                LogLevel::EMERGENCY,
                LogLevel::ALERT,
                LogLevel::CRITICAL,
                LogLevel::ERROR,
                LogLevel::WARNING,
                LogLevel::NOTICE,
                LogLevel::INFO,
                LogLevel::DEBUG
            ],
            CliLog::getInstance()
        );
    }
}
