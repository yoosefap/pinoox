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
use pinoox\storage\Database;
use \Illuminate\Database\Schema\Builder;

class MigrationBase extends Migration
{

    protected Database $db;
    protected Builder $schema;
    public string $prefix;

    public function __construct()
    {
        $this->db = new Database();
        $this->schema = $this->db->getSchema();
    }


}