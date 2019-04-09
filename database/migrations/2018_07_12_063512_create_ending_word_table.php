<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEndingWordTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('ending_word', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('ending_id');
            $table->unsignedInteger('word_id');
            // $table->primary(['ending_id', 'word_id']);

            // $table->timestamps();


            $table->foreign('ending_id')->references('id')->on('endings')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('ending_word');
    }
}
