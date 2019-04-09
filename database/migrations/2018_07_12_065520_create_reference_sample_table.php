<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReferenceSampleTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('reference_sample', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('reference_id');
            $table->unsignedInteger('sample_id');
            // $table->primary(['reference_id', 'sample_id']);

            // $table->timestamps();


            $table->foreign('reference_id')->references('id')->on('references')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('reference_sample');
    }
}
