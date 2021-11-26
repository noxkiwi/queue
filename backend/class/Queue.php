<?php declare(strict_types = 1);
namespace noxkiwi\queue;

use noxkiwi\queue\Interfaces\QueueInterface;
use noxkiwi\singleton\Singleton;
use RuntimeException;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function serialize;
use function sprintf;

/**
 * I am the main Queue class.
 *
 * @package      noxkiwi\queue
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2020 noxkiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
abstract class Queue extends Singleton implements QueueInterface
{
    protected const USE_DRIVER = true;
    /** @var string I am the identifier for the queue class. */
    protected string $identifier;

    /**
     * Queue constructor.
     *
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        parent::__construct();
        $this->identifier = $identifier;
    }

    /**
     * @inheritDoc
     */
    public function add(Message $message): void
    {
        $concurrentDirectory = Path::MESSAGE_LOG . $this->identifier;
        if (! is_dir($concurrentDirectory) && ! mkdir($concurrentDirectory) && ! is_dir($concurrentDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
        file_put_contents(Path::MESSAGE_LOG . "{$this->identifier}/{$message->uniqueId}.json", serialize($message));
    }
}
