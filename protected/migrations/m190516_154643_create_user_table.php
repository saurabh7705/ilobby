<?php

class m190516_154643_create_user_table extends CDbMigration
{
	public function safeUp() {
		$this->createTable(
			'user',
			array(
				'id'=>'int(11) UNSIGNED NOT NULL AUTO_INCREMENT',
				'email' => 'varchar(255) NOT NULL',
				'password' => 'varchar(255)',
				'name' => 'varchar(255)',
				'age'=>'int(11)',
				'address'=>'varchar(255)',
				'zipcode'=>'int(11)',
				'sex'=>'varchar(255)',
				'phone' => 'int(11)',
				'education_level'=>'varchar(255)',
				'ethnicity'=>'varchar(255)',
				'is_verified' => 'int(11)',
				'created_at' => 'int(11)',
				'updated_at' => 'int(11)',
				'PRIMARY KEY (id)',
			),
			'ENGINE=InnoDB'
		);

		$this->createTable(
			'api_token',
			array(
				'id'=>'int(11) UNSIGNED NOT NULL AUTO_INCREMENT',			
				'user_id' => 'int(11)',
				'token' => 'varchar(255)',
				'status' => 'tinyint(1)',
				'created_at' => 'int(11)',
				'updated_at' => 'int(11)',
				'PRIMARY KEY (id)',
			),
			'ENGINE=InnoDB'
		);
	}

	public function safeDown() {
		$this->dropTable('user');
		$this->dropTable('api_token');
	}
}