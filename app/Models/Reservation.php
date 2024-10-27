<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity_id',
        'reservation_date',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
