<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserFileTable extends Migration {

	public function up()
	{
		Schema::create('user_file', function(Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->integer('user_year_id')->unsigned();
			$table->string('name', 255);
			$table->integer('type');
		});
	}

	public function down()
	{
		Schema::drop('user_file');
	}
}