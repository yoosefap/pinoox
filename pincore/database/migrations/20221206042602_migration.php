<?php
/**
 *      ****  *  *     *  ****  ****  *    *
 *      *  *  *  * *   *  *  *  *  *   *  *
 *      ****  *  *  *  *  *  *  *  *    *
 *      *     *  *   * *  *  *  *  *   *  *
 *      *     *  *    **  ****  ****  *    *
 * @license    https://opensource.org/licenses/MIT MIT License
 * @link       pinoox.com
 * @copyright  pinoox
 */

namespace pinoox\database\migrations;

use Illuminate\Database\Schema\Blueprint;
use pinoox\component\migration\MigrationBase;

class Migration extends MigrationBase
{
    public function up()
    {
        $this->schema->create('migration', function (Blueprint $table) {
            // Auto-increment id
            $table->increments('log_id');
            $table->dateTime('version');
            $table->string('app');
            $table->string('migration_name');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            // Required for Eloquent's created_at and updated_at columns
            $table->timestamps();
        });
    }


    public function down()
    {
        if ($this->schema->hasTable('migration')){
            $this->schema->drop('migration');
        }
    }
}