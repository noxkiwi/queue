<?php declare(strict_types = 1);
namespace noxkiwi\queue\Queue;

use noxkiwi\core\Environment;
use noxkiwi\queue\Message;
use noxkiwi\queue\Queue;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use function serialize;

/**
 * I am the RabbitMQ Queue manager.
 *
 * @package      noxkiwi\core\Queue
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2020 noxkiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
abstract class RabbitmqQueue extends Queue
{
    /** @var \PhpAmqpLib\Connection\AMQPStreamConnection I am the connection to the desired RMQ Server. */
    private AMQPStreamConnection $connection;
    /** @var \PhpAmqpLib\Channel\AMQPChannel I am the channel to the correct queue. */
    private AMQPChannel $channel;
    /** @var \noxkiwi\core\Environment I am the current Environment. */
    private Environment $environment;

    /**
     * RabbitmqConsumer constructor.
     *
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        parent::__construct($identifier);
        $this->environment = Environment::getInstance();
        $this->connection  = new AMQPStreamConnection(
            $this->environment->get("queue>{$this->identifier}>host"),
            $this->environment->get("queue>{$this->identifier}>port"),
            $this->environment->get("queue>{$this->identifier}>user"),
            $this->environment->get("queue>{$this->identifier}>pass"),
            $this->environment->get("queue>{$this->identifier}>vhost")
        );
        $this->channel     = $this->connection->channel();
        $this->channel->queue_declare(
            $this->environment->get("queue>{$this->identifier}>queue>name"),
            $this->environment->get("queue>{$this->identifier}>queue>passive"),
            $this->environment->get("queue>{$this->identifier}>queue>durable"),
            $this->environment->get("queue>{$this->identifier}>queue>exclusive"),
            $this->environment->get("queue>{$this->identifier}>queue>autodelete")
        );
    }

    /**
     * @inheritDoc
     */
    public function add(Message $message): void
    {
        parent::add($message);
        $amqpMessage = new AMQPMessage(serialize($message));
        $this->channel->basic_publish($amqpMessage, $this->environment->get("queue>{$this->identifier}>exchange>name"));
    }
}

