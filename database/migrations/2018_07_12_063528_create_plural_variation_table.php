<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePluralVariationTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('plural_variation', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('plural_id');
            $table->unsignedInteger('variation_id');
            // $table->primary(['plural_id', 'variation_id']);

            // $table->timestamps();


            $table->foreign('plural_id')->references('id')->on('plurals')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('variation_id')->references('id')->on('variations')->onUpdate('cascade')->onDelete('cascade');


            $table->collation = 'utf8mb4_general_ci';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('plural_variation');
    }
}
