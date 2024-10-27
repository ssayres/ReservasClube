<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('activities', function (Blueprint $table) {
            // Adiciona a coluna `user_id` como nullable
            $table->unsignedBigInteger('user_id')->nullable();
        });

        // Define user_id para 1 para garantir um valor válido (ou defina para outro valor válido que exista em `users`)
        \DB::table('activities')->whereNotIn('user_id', function ($query) {
            $query->select('id')->from('users');
        })->update(['user_id' => 1]); // Atualize para um ID de usuário existente

        Schema::table('activities', function (Blueprint $table) {
            // Agora define user_id como chave estrangeira e remove o nullable
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
