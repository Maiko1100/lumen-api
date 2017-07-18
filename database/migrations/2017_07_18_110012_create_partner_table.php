<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePartnerTable extends Migration {

	public function up()
	{
		Schema::create('partner', function(Blueprint $table) {
			$table->integer('person_id')->unsigned();
		});
	}

	public function down()
	{
		Schema::drop('partner');
	}
}