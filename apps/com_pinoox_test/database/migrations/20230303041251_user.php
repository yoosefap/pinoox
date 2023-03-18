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

class User extends MigrationBase
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$this->schema->create("com_pinoox_test_user", function (Blueprint $table) {
			$table->increments("id");
			$table->string("username");
			$table->string("password");

            $table->rememberToken();
            $table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$this->schema->dropIfExists("com_pinoox_test_user");
	}
}
