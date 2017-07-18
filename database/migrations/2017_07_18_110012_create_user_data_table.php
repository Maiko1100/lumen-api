<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserDataTable extends Migration {

	public function up()
	{
		Schema::create('user_data', function(Blueprint $table) {
			$table->integer('person_id')->unsigned();
			$table->integer('question_id')->unsigned();
			$table->string('answer', 255)->nullable();
		});
	}

	public function down()
	{
		Schema::drop('user_data');
	}
}