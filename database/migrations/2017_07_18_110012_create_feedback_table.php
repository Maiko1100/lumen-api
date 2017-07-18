<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFeedbackTable extends Migration {

	public function up()
	{
		Schema::create('feedback', function(Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->integer('user_question_id')->unsigned();
			$table->integer('person_id')->unsigned();
			$table->string('text', 255);
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('feedback');
	}
}