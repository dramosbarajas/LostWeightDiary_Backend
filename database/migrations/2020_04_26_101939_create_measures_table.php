<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeasuresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('measures', function (Blueprint $table) {
            $table->id();
            $table->double('estatura', 4, 1);
            $table->double('peso', 4, 1);
            $table->double('cadera', 4, 1);
            $table->double('cintura', 4, 1);
            $table->double('pecho', 4, 1);
            $table->double('brazo', 4, 1);
            $table->double('pierna', 4, 1);
            $table->double('cuello', 4, 1);
            $table->foreignId('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('measures');
    }
}
