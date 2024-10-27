<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'activity_limit_per_day',
    ];

    // Relacionamento com User
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
