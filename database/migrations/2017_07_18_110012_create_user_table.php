<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserTable extends Migration {

	public function up()
	{
		Schema::create('user', function(Blueprint $table) {
			$table->integer('person_id')->unsigned();
			$table->timestamps();
			$table->string('email', 255)->unique();
			$table->string('password', 255);
			$table->integer('role');
			$table->boolean('is_active');
			$table->integer('employee_id')->unsigned()->nullable();
			$table->integer('partner_id')->unsigned();
		});
	}

	public function down()
	{
		Schema::drop('user');
	}
}