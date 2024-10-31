<?php


use Phinx\Seed\AbstractSeed;

class TruncateAll extends AbstractSeed {
	/**
	 * Run Method.
	 *
	 * Write your database seeder using this method.
	 *
	 * More information on writing seeders is available here:
	 * https://book.cakephp.org/phinx/0/en/seeding.html
	 */
	public function run() {
		$tables = [
			'countries',
			//'country_names',
			'provinces',
			//'province_names',
			'cities',
			//'city_names'
		];

		$this->execute('SET FOREIGN_KEY_CHECKS = 0;');
		foreach ($tables as $table) {
			$this->execute('TRUNCATE TABLE ' . MCS_PREFIX . $table);
		}
		$this->execute('SET FOREIGN_KEY_CHECKS = 1;');
	}
}
