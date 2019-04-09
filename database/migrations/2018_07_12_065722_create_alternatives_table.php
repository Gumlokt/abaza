<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlternativesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('alternatives', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('sample_id');
            $table->string('alternative');

            $table->timestamps();


            $table->foreign('sample_id')->references('id')->on('samples')->onUpdate('cascade')->onDelete('cascade');


            $table->collation = 'utf8mb4_general_ci';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('alternatives');
    }
}
