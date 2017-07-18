<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateChildTable extends Migration {

	public function up()
	{
		Schema::create('child', function(Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->string('first_name', 255);
			$table->string('last_name', 255);
			$table->date('date_of_birth');
			$table->integer('person_id')->unsigned();
			$table->integer('question_id')->unsigned();
		});
	}

	public function down()
	{
		Schema::drop('child');
	}
}