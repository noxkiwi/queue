<?php declare(strict_types = 1);
namespace noxkiwi\queue\Exception;

use noxkiwi\core\Exception;

/**
 * I am thrown whenever a Consumer cannot deserialize the message into the correct format.
 *
 * @package      noxkiwi\mailer\Consumer
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2020 noxkiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
final class InvalidMessageException extends Exception
{
}
