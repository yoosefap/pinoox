<?php
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

namespace pinoox\component\migration;

use Illuminate\Database\Migrations\Migration;
use pinoox\portal\Database;
use \Illuminate\Database\Schema\Builder;

class MigrationBase extends Migration
{
    public Builder $schema;

    public function __construct()
    {
        $this->schema = Database::getSchema();
    }

}