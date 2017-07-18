<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEmployeeFileTable extends Migration {

	public function up()
	{
		Schema::create('employee_file', function(Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->integer('user_year_id')->unsigned();
			$table->string('name', 255);
			$table->integer('type');
		});
	}

	public function down()
	{
		Schema::drop('employee_file');
	}
}