<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fotos', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('titulo', 120);
            $table->text('descricao')->nullable();
            $table->string('arquivo_path');
            $table->unsignedSmallInteger('largura')->nullable();
            $table->unsignedSmallInteger('altura')->nullable();
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->boolean('publicada')->default(true);
            $table->boolean('exemplo')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['publicada', 'ordem']);
        });

        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('titulo', 120);
            $table->text('descricao')->nullable();

            $table->string('provedor', 20);
            $table->string('codigo', 60);
            $table->string('capa_path')->nullable();
            $table->boolean('na_home')->default(false);
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->boolean('publicado')->default(true);
            $table->boolean('exemplo')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['publicado', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
        Schema::dropIfExists('fotos');
    }
};
