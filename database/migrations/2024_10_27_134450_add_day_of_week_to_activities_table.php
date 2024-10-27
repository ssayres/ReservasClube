<?php

// database/migrations/xxxx_xx_xx_add_day_of_week_to_activities_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('day_of_week')->after('end_time'); // Adiciona o campo "dia da semana"
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('day_of_week');
        });
    }
};
