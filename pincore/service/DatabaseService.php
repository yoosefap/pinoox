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

use pinoox\component\interfaces\ServiceInterface;
use pinoox\portal\DatabaseManager;

class DatabaseService implements ServiceInterface
{

    public function _run()
    {
        DatabaseManager::run();
    }

    public function _stop()
    {
    }
}

