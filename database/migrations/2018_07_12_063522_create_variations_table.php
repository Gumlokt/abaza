<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVariationsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('variations', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('word_id');
            $table->string('variation');
            $table->string('stress');

            $table->timestamps();

            $table->foreign('word_id')->references('id')->on('words')->onUpdate('cascade')->onDelete('cascade');


            $table->collation = 'utf8mb4_general_ci';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('variations');
    }
}
