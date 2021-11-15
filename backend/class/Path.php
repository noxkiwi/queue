<?php declare(strict_types = 1);
namespace noxkiwi\queue;

/**
 * I am the path class.
 *
 * @package      noxkiwi\queue
 * @author       Jan Nox <jan.nox@pm.me>
 * @license      https://nox.kiwi/license
 * @copyright    2020 noxkiwi
 * @version      1.0.0
 * @link         https://nox.kiwi/
 */
abstract class Path extends \noxkiwi\core\Path
{
    public const MESSAGE_LOG = self::LOG_DIR . 'message/';
}
