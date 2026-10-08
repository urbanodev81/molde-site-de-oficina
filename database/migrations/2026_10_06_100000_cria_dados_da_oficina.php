<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oficina', function (Blueprint $table) {
            $table->id();
            $table->string('whatsapp', 15)->nullable();
            $table->string('telefone', 15)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('instagram', 60)->nullable();
            $table->string('logradouro', 160)->nullable();
            $table->string('bairro', 80)->nullable();
            $table->string('cidade', 80)->nullable();
            $table->string('uf', 2)->nullable();
            $table->string('cep', 9)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->string('mapa_embed', 2000)->nullable();
            $table->json('horario')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oficina');
    }
};
