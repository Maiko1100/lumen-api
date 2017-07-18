<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Model;

class CreateForeignKeys extends Migration {

	public function up()
	{
		Schema::table('user', function(Blueprint $table) {
			$table->foreign('person_id')->references('id')->on('person')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
		Schema::table('user', function(Blueprint $table) {
			$table->foreign('employee_id')->references('id')->on('person')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
		Schema::table('user', function(Blueprint $table) {
			$table->foreign('partner_id')->references('id')->on('person')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
		Schema::table('partner', function(Blueprint $table) {
			$table->foreign('person_id')->references('id')->on('person')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
		Schema::table('question', function(Blueprint $table) {
			$table->foreign('year_id')->references('id')->on('year')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
		Schema::table('question', function(Blueprint $table) {
			$table->foreign('parent')->references('id')->on('question')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
		Schema::table('user_question', function(Blueprint $table) {
			$table->foreign('user_year_id')->references('id')->on('user_year')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
		Schema::table('user_question', function(Blueprint $table) {
			$table->foreign('question_id')->references('id')->on('question')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
		Schema::table('user_year', function(Blueprint $table) {
			$table->foreign('person_id')->references('person_id')->on('user')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
		Schema::table('user_year', function(Blueprint $table) {
			$table->foreign('year_id')->references('id')->on('year')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
		Schema::table('question_has_question_option', function(Blueprint $table) {
			$table->foreign('question_id')->references('id')->on('question')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
		Schema::table('question_has_question_option', function(Blueprint $table) {
			$table->foreign('question_option_id')->references('id')->on('question_option')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
		Schema::table('feedback', function(Blueprint $table) {
			$table->foreign('user_question_id')->references('id')->on('user_question')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
		Schema::table('feedback', function(Blueprint $table) {
			$table->foreign('person_id')->references('id')->on('person')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
		Schema::table('user_data', function(Blueprint $table) {
			$table->foreign('person_id')->references('id')->on('person')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
		Schema::table('user_data', function(Blueprint $table) {
			$table->foreign('question_id')->references('id')->on('question')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
		Schema::table('child', function(Blueprint $table) {
			$table->foreign('person_id')->references('person_id')->on('user_data')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
		Schema::table('child', function(Blueprint $table) {
			$table->foreign('question_id')->references('question_id')->on('user_data')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
		Schema::table('user_file', function(Blueprint $table) {
			$table->foreign('user_year_id')->references('id')->on('user_year')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
		Schema::table('employee_file', function(Blueprint $table) {
			$table->foreign('user_year_id')->references('id')->on('user_year')
						->onDelete('cascade')
						->onUpdate('cascade');
		});
	}

	public function down()
	{
		Schema::table('user', function(Blueprint $table) {
			$table->dropForeign('user_person_id_foreign');
		});
		Schema::table('user', function(Blueprint $table) {
			$table->dropForeign('user_employee_id_foreign');
		});
		Schema::table('user', function(Blueprint $table) {
			$table->dropForeign('user_partner_id_foreign');
		});
		Schema::table('partner', function(Blueprint $table) {
			$table->dropForeign('partner_person_id_foreign');
		});
		Schema::table('question', function(Blueprint $table) {
			$table->dropForeign('question_year_id_foreign');
		});
		Schema::table('question', function(Blueprint $table) {
			$table->dropForeign('question_parent_foreign');
		});
		Schema::table('user_question', function(Blueprint $table) {
			$table->dropForeign('user_question_user_year_id_foreign');
		});
		Schema::table('user_question', function(Blueprint $table) {
			$table->dropForeign('user_question_question_id_foreign');
		});
		Schema::table('user_year', function(Blueprint $table) {
			$table->dropForeign('user_year_person_id_foreign');
		});
		Schema::table('user_year', function(Blueprint $table) {
			$table->dropForeign('user_year_year_id_foreign');
		});
		Schema::table('question_has_question_option', function(Blueprint $table) {
			$table->dropForeign('question_has_question_option_question_id_foreign');
		});
		Schema::table('question_has_question_option', function(Blueprint $table) {
			$table->dropForeign('question_has_question_option_question_option_id_foreign');
		});
		Schema::table('feedback', function(Blueprint $table) {
			$table->dropForeign('feedback_user_question_id_foreign');
		});
		Schema::table('feedback', function(Blueprint $table) {
			$table->dropForeign('feedback_person_id_foreign');
		});
		Schema::table('user_data', function(Blueprint $table) {
			$table->dropForeign('user_data_person_id_foreign');
		});
		Schema::table('user_data', function(Blueprint $table) {
			$table->dropForeign('user_data_question_id_foreign');
		});
		Schema::table('child', function(Blueprint $table) {
			$table->dropForeign('child_person_id_foreign');
		});
		Schema::table('child', function(Blueprint $table) {
			$table->dropForeign('child_question_id_foreign');
		});
		Schema::table('user_file', function(Blueprint $table) {
			$table->dropForeign('user_file_user_year_id_foreign');
		});
		Schema::table('employee_file', function(Blueprint $table) {
			$table->dropForeign('employee_file_user_year_id_foreign');
		});
	}
}