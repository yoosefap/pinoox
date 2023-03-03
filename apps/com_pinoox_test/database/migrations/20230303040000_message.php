<?php

/**
 * ***  *  *     *  ****  ****  *    *
 *   *  *  * *   *  *  *  *  *   *  *
 * ***  *  *  *  *  *  *  *  *    *
 *      *  *   * *  *  *  *  *   *  *
 *      *  *    **  ****  ****  *    *
 *
 * @author   Pinoox
 * @link https://www.pinoox.com
 * @license  https://opensource.org/licenses/MIT MIT License
 */

namespace pinoox\app\com_pinoox_test\database\migrations;

use Illuminate\Database\Schema\Blueprint;
use pinoox\component\migration\MigrationBase;

class Message extends MigrationBase
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->schema->create("com_pinoox_test_message", function (Blueprint $table) {
            $table->increments("id");
            $table->string("title");
            $table->string("message");
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->schema->dropIfExists("com_pinoox_test_message");
    }
}
