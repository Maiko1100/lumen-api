<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserQuestionTable extends Migration {

	public function up()
	{
		Schema::create('user_question', function(Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->integer('user_year_id')->unsigned();
			$table->integer('question_id')->unsigned();
			$table->string('question_answer', 255)->nullable();
			$table->string('approved', 255)->nullable();
		});
	}

	public function down()
	{
		Schema::drop('user_question');
	}
}