<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deputados', function (Blueprint $table) {
            $table->id();
            $table->integer('deputado_id')->unique();
            $table->string('nome');
            $table->string('sigla_partido')->nullable();
            $table->string('sigla_uf', 2);
            $table->integer('id_legislatura');
            $table->string('url_foto')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            
            $table->index(['deputado_id', 'id_legislatura']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deputados');
    }
};