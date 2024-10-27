<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        $activityId = $request->input('activity_id');
        $activity = Activity::findOrFail($activityId);

        $planLimit = $this->getPlanLimit($user->plan_id);
        $dailyReservations = Reservation::where('user_id', $user->id)
                                        ->whereDate('reservation_date', today())
                                        ->count();

        if ($dailyReservations >= $planLimit) {
            throw ValidationException::withMessages([
                'limit' => 'Você atingiu o limite de reservas diárias para o seu plano.',
            ]);
        }

        $conflictingReservation = Reservation::where('user_id', $user->id)
                                             ->whereDate('reservation_date', today())
                                             ->whereHas('activity', function ($query) use ($activity) {
                                                 $query->where('start_time', $activity->start_time);
                                             })->exists();

        if ($conflictingReservation) {
            throw ValidationException::withMessages([
                'conflict' => 'Você já possui uma reserva para esse horário.',
            ]);
        }

        if ($activity->reservations->count() >= $activity->max_students) {
            throw ValidationException::withMessages([
                'full' => 'Essa atividade já atingiu o limite de participantes.',
            ]);
        }

        Reservation::create([
            'user_id' => $user->id,
            'activity_id' => $activity->id,
            'reservation_date' => today(),
        ]);

        return redirect()->route('activities.index')->with('success', 'Reserva realizada com sucesso!');
    }

    private function getPlanLimit($planId)
    {
        return match ($planId) {
            1 => 1,     // Bronze
            2 => 5,     // Prata
            default => PHP_INT_MAX,  // Gold é ilimitado
        };
    }

    public function index()
{
    $user = Auth::user();
    
    // Filtra atividades que ainda estão disponíveis para inscrição
    $activities = Activity::with('reservations')->get()->filter(function ($activity) {
        return !$activity->isExpired();
    });

    $dailyReservations = Reservation::where('user_id', $user->id)
                                    ->whereDate('reservation_date', today())
                                    ->count();

    return view('activities.index', [
        'activities' => $activities,
        'dailyReservations' => $dailyReservations,
        'planLimit' => $this->getPlanLimit($user->plan_id),
    ]);
}

public function destroy(Request $request)
{
    $user = Auth::user();
    $activityId = $request->input('activity_id');

    $reservation = Reservation::where('user_id', $user->id)
                              ->where('activity_id', $activityId)
                              ->first();

    if (!$reservation) {
        return redirect()->route('activities.week')->with('error', 'Reserva não encontrada.');
    }

    $reservation->delete();

    return redirect()->route('activities.week')->with('success', 'Reserva cancelada com sucesso.');
}


}
