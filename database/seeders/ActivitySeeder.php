<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;

class ActivitySeeder extends Seeder
{
    public function run()
    {
        Activity::create([
            'name' => 'Yoga Matinal',
            'description' => 'Sessão de Yoga para começar bem o dia.',
            'start_time' => '07:00:00',
            'end_time' => '08:00:00',
            'max_students' => 40,
        ]);
        Activity::create([
            'name' => 'Futebol',
            'description' => 'Futebol em Quadra Society.',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'max_students' => 40,
        ]);
        // Adicione mais atividades conforme necessário
    }
}
