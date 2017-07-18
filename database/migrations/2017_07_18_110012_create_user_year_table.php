<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserYearTable extends Migration {

	public function up()
	{
		Schema::create('user_year', function(Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->integer('person_id')->unsigned();
			$table->integer('year_id')->unsigned();
		});
	}

	public function down()
	{
		Schema::drop('user_year');
	}
}