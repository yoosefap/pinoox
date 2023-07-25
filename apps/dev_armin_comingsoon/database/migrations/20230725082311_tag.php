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

namespace pinoox\app\dev_armin_comingsoon\database\migrations;

use Illuminate\Database\Schema\Blueprint;
use pinoox\component\migration\MigrationBase;

class Tag extends MigrationBase
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$this->schema->create("dev_armin_comingsoon_tag", function (Blueprint $table) {
			$table->increments("id");
		});
	}


	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$this->schema->dropIfExists("dev_armin_comingsoon_tag");
	}
}
