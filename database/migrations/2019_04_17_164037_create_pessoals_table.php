<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePessoalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pessoal', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('matricula');
            $table->string('cpf')->nullable();
            $table->string('nome');
            $table->string('data_cessao')->nullable();
            $table->string('data_admissao')->nullable();
            $table->string('data_inatividade')->nullable();
            $table->string('orgao_cessao')->nullable();
            $table->string('municipio_cessao')->nullable();
            $table->string('cedido_para')->nullable();

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
        Schema::dropIfExists('pessoal');
    }
}
