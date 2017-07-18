<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePersonTable extends Migration {

	public function up()
	{
		Schema::create('person', function(Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->string('first_name', 255);
			$table->string('last_name', 255);
			$table->integer('passport')->nullable();
		});
	}

	public function down()
	{
		Schema::drop('person');
	}
}