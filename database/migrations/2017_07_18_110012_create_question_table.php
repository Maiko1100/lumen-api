<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateQuestionTable extends Migration {

	public function up()
	{
		Schema::create('question', function(Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->string('text', 255);
			$table->integer('answer_option');
			$table->integer('year_id')->unsigned();
			$table->integer('parent')->unsigned();
			$table->string('category', 255);
			$table->string('condition', 255)->nullable();
			$table->string('type', 255);
			$table->boolean('is_static');
		});
	}

	public function down()
	{
		Schema::drop('question');
	}
}