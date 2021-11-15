<?php declare(strict_types = 1);
namespace noxkiwi\queue\Consumer;

use Exception;
use noxkiwi\core\Environment;
use noxkiwi\queue\Consumer;
use noxkiwi\queue\Exception\InvalidMessageException;
use noxkiwi\queue\Message;
use noxkiwi\queue\Observer\ConsumerObserver;
use noxkiwi\queue\Queue;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use function get_class;
use function microtime;

/**
 * I am the core RMQ Consumer class.
 *
 * @package      noxkiwi\queue\Consumer
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2020 noxkiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
abstract class RabbitmqConsumer extends Consumer
{
    /** @var \PhpAmqpLib\Connection\AMQPStreamConnection I am the connection to the desired RMQ Server. */
    private AMQPStreamConnection $connection;
    /** @var \PhpAmqpLib\Channel\AMQPChannel I am the channel to the correct queue. */
    private AMQPChannel $channel;
    /** @var \noxkiwi\core\Environment I am the current Environment. */
    private Environment $environment;
    /** @var string I am the identifier for the queue configuration in the environment. */
    private string $identifier;

    /**
     * RabbitmqConsumer constructor.
     *
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        $this->environment = Environment::getInstance();
        $env = $this->environment;
        $this->identifier = $identifier;
        parent::__construct();
        $this->logNotice(' [*] Hello, I am a Consumer of type ' . $this::class);
        $this->logDebug(" [*] Connecting to RMQ: {$identifier}");
        $this->logDebug(" [*] - Hostname  is {$env->get("queue>$identifier>host")}");
        $this->logDebug(" [*] - Port      is {$env->get("queue>$identifier>port")}");
        $this->logDebug(" [*] - User name is {$env->get("queue>$identifier>user")}");
        $this->logDebug(" [*] - Password  is {$env->get("queue>$identifier>pass")}");
        $this->logDebug(" [*] - vHost     is {$env->get("queue>$identifier>vhost")}");
        $this->connection = new AMQPStreamConnection(
            $env->get("queue>$identifier>host"),
            $env->get("queue>$identifier>port"),
            $env->get("queue>$identifier>user"),
            $env->get("queue>$identifier>pass"),
            $env->get("queue>$identifier>vhost")
        );
        $this->logInfo(' [*] Connected to RMQ Server ' . $env->get("queue>$identifier>host"));
        $this->logDebug(' [*] Declaring Queue:');
        $this->channel = $this->connection->channel();
        $this->logDebug(" [*] - Q-Name       is {$env->get("queue>$identifier>queue>name")}");
        $this->logDebug(" [*] - Q-Passive    is {$env->get("queue>$identifier>queue>passive")}");
        $this->logDebug(" [*] - Q-Durable    is {$env->get("queue>$identifier>queue>durable")}");
        $this->logDebug(" [*] - Q-Exclusive  is {$env->get("queue>$identifier>queue>exclusive")}");
        $this->logDebug(" [*] - Q-Autodelete is {$env->get("queue>$identifier>queue>autodelete")}");
        $this->channel->queue_declare(
            $env->get("queue>$identifier>queue>name"),
            $env->get("queue>$identifier>queue>passive"),
            $env->get("queue>$identifier>queue>durable"),
            $env->get("queue>$identifier>queue>exclusive"),
            $env->get("queue>$identifier>queue>autodelete")
        );
        $this->logInfo(' [*] Successfully declared Queue ' . $env->get("queue>$identifier>queue>name"));
    }

    /**
     * @inheritDoc
     */
    final public function run(): void
    {
        $this->logNotice(" [*] I'm now waiting for messages. To exit press [CTRL] + [C]");
        $callback = function (AMQPMessage $rawMessage) {
            $this->logDebug(' [*] Received a Message of any kind.');
            try {
                $message = $this->deserializeMessage($rawMessage->body);
            } catch (InvalidMessageException $exception) {
                $this->logError(" [*] Sorry, I wasn't able to identify this message. I will toss it.");
                $this->logError(" [*]  - Exception message is: {$exception->getMessage()}");
                $this->notify(ConsumerObserver::TOSSED);

                return;
            }
            $messageType = $message::class;
            $this->logDebug(" [*] I identified the message as $messageType");
            try {
                $this->notify(ConsumerObserver::TRY);
                $this->logInfo(' [*] I will now begin Process with ' . static::class);
                $start   = microtime(true);
                $success = $this->process($message);
                $elapsed = (microtime(true) - $start) * 1000;
                $this->logInfo(' [*] End Process');
                $this->logDebug(" [*] The Process took {$elapsed}ms to finish.");
                if ($success) {
                    $this->logInfo(' [*] Process finished with SUCCESS result.');
                    $this->notify(ConsumerObserver::SUCCESS);
                } else {
                    $this->logWarning(' [*] Process finished with ERROR result.');
                    $this->notify(ConsumerObserver::FAIL);
                    $message->tries++;
                    if ($message->tries > $message->maxTries) {
                        $reQueue = Queue::getInstance('Tossed');
                        $this->notify(ConsumerObserver::TOSSED);
                    } else {
                        $reQueue = Queue::getInstance($this->identifier);
                    }
                    $reQueue->add($message);
                }
            } catch (Exception $exception) {
                $this->logError(' [*] Uncaught Exception during Process.');
                $this->logError(' [*]   Message: ' . $exception->getMessage());
                $this->notify(ConsumerObserver::FAIL);
            }
        };
        $this->channel->basic_consume($this->environment->get("queue>{$this->identifier}>queue>name"), '', false, true, false, false, $callback);

        while ($this->channel->is_consuming()) {
            $this->notify(ConsumerObserver::RUN);
            $this->logNotice(" [*] Waiting for messages in queue {$this->identifier}.");
            $this->channel->wait();
        }
    }

    /**
     * I will take care to de-serialize the given $rawMessage into a valid Message for our consumers.
     * Valid types are specified at the Consumer classes in the MESSAGE_TYPES property.
     * @see \noxkiwi\queue\Consumer::MESSAGE_TYPES
     *
     * @param string $messageBody
     *
     * @throws \noxkiwi\queue\Exception\InvalidMessageException  The $rawMessage is not of a known message type.
     * @return \noxkiwi\queue\Message
     */
    final protected function deserializeMessage(string $messageBody): Message
    {
        $message = unserialize($messageBody, static::MESSAGE_TYPES);
        if (empty($message)) {
            throw new InvalidMessageException('MESSAGE_NOT_UNSERIALIZABLE', E_WARNING, $messageBody);
        }

        return $message;
    }
}

