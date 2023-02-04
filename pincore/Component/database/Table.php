<?php

namespace pinoox\component\database;

use pinoox\component\app\AppProvider;

/**
 *      ****  *  *     *  ****  ****  *    *
 *      *  *  *  * *   *  *  *  *  *   *  *
 *      ****  *  *  *  *  *  *  *  *    *
 *      *     *  *   * *  *  *  *  *   *  *
 *      *     *  *    **  ****  ****  *    *
 * @author   Pinoox
 * @link https://www.pinoox.com/
 * @license  https://opensource.org/licenses/MIT MIT License
 */
trait Table
{
    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable(): string
    {
        if (isset($this->table)) {
            return $this->table;
        }

        return App::get('packageName') . '_' . strtolower(str_replace('\\', '', class_basename($this)));
    }
}