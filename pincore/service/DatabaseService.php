<?php
/**
 *      ****  *  *     *  ****  ****  *    *
 *      *  *  *  * *   *  *  *  *  *   *  *
 *      ****  *  *  *  *  *  *  *  *    *
 *      *     *  *   * *  *  *  *  *   *  *
 *      *     *  *    **  ****  ****  *    *
 * @author   Erfan Ebrahimi
 * @link http://www.erfanebrahimi.ir/
 * @license  https://opensource.org/licenses/MIT MIT License
 */

namespace pinoox\service;

use pinoox\component\console;
use pinoox\component\interfaces\ServiceInterface;
use pinoox\component\Router;
use pinoox\component\Url;
use pinoox\storage\Database;

class DatabaseService implements ServiceInterface
{

    public function _run()
    {
        new Database();
    }

    public function _stop()
    {
    }
}

