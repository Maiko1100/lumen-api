<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateQuestionHasQuestionOptionTable extends Migration {

	public function up()
	{
		Schema::create('question_has_question_option', function(Blueprint $table) {
			$table->integer('question_id')->unsigned();
			$table->integer('question_option_id')->unsigned();
		});
	}

	public function down()
	{
		Schema::drop('question_has_question_option');
	}
}