<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateQuestionOptionTable extends Migration {

	public function up()
	{
		Schema::create('question_option', function(Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->string('text', 255);
		});
	}

	public function down()
	{
		Schema::drop('question_option');
	}
}