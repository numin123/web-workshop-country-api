<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('countries');
        Schema::disableForeignKeyConstraints();
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->String('name')->nullable();
            $table->String('capital')->nullable();
            $table->String('region')->nullable();
            $table->bigInteger('population')->nullable();
            $table->String('flag')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });


        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('countries');
        Schema::disableForeignKeyConstraints();
    }
}
