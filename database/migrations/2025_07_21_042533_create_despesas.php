<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despesas', function (Blueprint $table) {
            $table->id();
            $table->integer('deputado_id');
            $table->integer('ano');
            $table->integer('mes');
            $table->string('tipo_despesa');
            $table->string('cod_documento')->nullable();
            $table->string('tipo_documento')->nullable();
            $table->integer('cod_tipo_documento')->nullable();
            $table->date('data_documento')->nullable();
            $table->string('num_documento')->nullable();
            $table->decimal('valor_documento', 10, 2);
            $table->string('url_documento')->nullable();
            $table->string('nome_fornecedor')->nullable();
            $table->string('cnpj_cpf_fornecedor')->nullable();
            $table->decimal('valor_liquido', 10, 2);
            $table->decimal('valor_glosa', 10, 2)->default(0);
            $table->string('num_ressarcimento')->nullable();
            $table->integer('cod_lote')->nullable();
            $table->integer('parcela')->nullable();
            $table->timestamps();
            
            $table->foreign('deputado_id')->references('deputado_id')->on('deputados');
            $table->index(['deputado_id', 'ano', 'mes']);
            $table->index('data_documento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despesas');
    }
};