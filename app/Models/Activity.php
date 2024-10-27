<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_time',
        'end_time',
        'max_students',
        'day_of_week',
        'user_id', // Campo para associar atividade ao professor
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
        
    }

    public function isExpired(): bool
    {
        return now()->format('H:i:s') > $this->end_time && now()->format('l') === $this->day_of_week;
    }
}
