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

class Test extends MigrationBase
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$this->schema->create($this->table('test'), function (Blueprint $table) {
			$table->increments("id");
		});
	}


	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$this->schema->dropIfExists($this->table('test'));
	}
}
