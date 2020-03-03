<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSocialKapitalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_kapital', function (Blueprint $table) {
             $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->date('review_date');
            $table->text('review');
            $table->datetime('last_review_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('social_kapital');
    }
}
