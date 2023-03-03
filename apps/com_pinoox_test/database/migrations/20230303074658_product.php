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

class Product extends MigrationBase
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$this->schema->create("com_pinoox_test_product", function (Blueprint $table) {
			$table->increments("product_id");
			$table->string("product_name");
			$table->string("summary");
			$table->string("content");
		});
	}


	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$this->schema->dropIfExists("com_pinoox_test_product");
	}
}
