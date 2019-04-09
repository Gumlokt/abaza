<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWordsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('words', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('category_id');

            $table->string('word')->index();
            $table->string('stress');
            $table->boolean('independent')->default(1);

            $table->timestamps();


            $table->foreign('category_id')->references('id')->on('categories')->onUpdate('cascade')->onDelete('cascade');


            $table->collation = 'utf8mb4_general_ci';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('words');
    }
}
