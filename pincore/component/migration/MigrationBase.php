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
use pinoox\component\database\Database;
use \Illuminate\Database\Schema\Builder;

class MigrationBase extends Migration
{
    public Database $db;
    public Builder $schema;

    public function __construct()
    {
        $db = Database::establish();
        $this->db = $db;
        $this->schema = $db->getSchema();
    }

}