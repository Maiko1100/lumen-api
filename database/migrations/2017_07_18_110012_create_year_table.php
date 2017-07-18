<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateYearTable extends Migration {

	public function up()
	{
		Schema::create('year', function(Blueprint $table) {
			$table->integer('id')->primary()->unsigned();
		});
	}

	public function down()
	{
		Schema::drop('year');
	}
}