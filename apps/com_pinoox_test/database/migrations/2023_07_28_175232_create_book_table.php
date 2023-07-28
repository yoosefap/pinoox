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

return new class extends MigrationBase
{
	/**
	 * Run the migrations.
	 */
    public function up()
    {
        $this->schema->create('com_pinoox_test_book', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$this->schema->dropIfExists('com_pinoox_test_book');
	}
};
