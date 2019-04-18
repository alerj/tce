<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pagamento', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('ano_referencia')->index();
            $table->integer('mes_referencia')->index();

            $table->string('tipo_folha');

            $table->string('tipo_cargo');

            $table->string('situacao_funcional');

            $table->string('matricula')->index();
            $table->string('matricula_sdv')->index();
            $table->string('nome')->index();
            $table->string('uadm');
            $table->string('cpf');
            $table->string('cargo')->nullable();
            $table->string('funcao')->nullable();
            $table->decimal('rend_func', 10, 2)->nullable();
            $table->decimal('comissao', 10, 2)->nullable();
            $table->decimal('represent', 10, 2)->nullable();
            $table->decimal('incorporado', 10, 2)->nullable();
            $table->decimal('trienio', 10, 2)->nullable();
            $table->decimal('abono', 10, 2)->nullable();
            $table->decimal('ferias', 10, 2)->nullable();
            $table->decimal('redutor', 10, 2)->nullable();
            $table->decimal('previdencia', 10, 2)->nullable();
            $table->decimal('ir', 10, 2)->nullable();
            $table->decimal('total_liquido', 10, 2);

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
        Schema::dropIfExists('pagamento');
    }
}
