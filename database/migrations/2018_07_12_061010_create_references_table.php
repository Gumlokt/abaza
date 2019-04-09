<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReferencesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('references', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('type_id');
            $table->string('abbreviation');
            $table->string('description');

            $table->timestamps();


            $table->foreign('type_id')->references('id')->on('types')->onUpdate('cascade')->onDelete('cascade');


            $table->collation = 'utf8mb4_general_ci';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('references');
    }
}
