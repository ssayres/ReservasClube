<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Importação necessária
use Illuminate\Validation\ValidationException;

class ActivityController extends Controller
{
    
    use AuthorizesRequests;
    public function index()
{
    $user = Auth::user();

    // Carrega as atividades conforme o tipo de usuário
    if ($user->plan_id == 4) {
        // Se o usuário for professor, carregar apenas suas atividades
        $activities = Activity::where('user_id', $user->id)->with('reservations')->get();
    } else {
        // Caso contrário, exibir atividades com vagas disponíveis
        $activities = Activity::with('reservations')->get()->filter(function ($activity) {
            return $activity->reservations->count() < $activity->max_students;
        });
    }

    // Carregar as reservas do usuário com as atividades relacionadas
    $userReservations = Reservation::where('user_id', $user->id)->with('activity')->get();

    // Identificar atividades conflitantes
    $conflictingActivityIds = [];
    foreach ($activities as $activity) {
        foreach ($userReservations as $reservation) {
            $reservedActivity = $reservation->activity;

            // Verifica conflito de horário
            if ($activity->day_of_week === $reservedActivity->day_of_week &&
                $this->hasTimeOverlap($activity->start_time, $activity->end_time, $reservedActivity->start_time, $reservedActivity->end_time)) {
                $conflictingActivityIds[] = $activity->id;
                break; // Pode parar de verificar outras reservas para essa atividade
            }
        }
    }

    // Reservas do usuário no dia atual
    $dailyReservations = Reservation::where('user_id', $user->id)
                                     ->whereDate('reservation_date', today())
                                     ->count();

    return view('activities.index', [
        'activities' => $activities,
        'dailyReservations' => $dailyReservations,
        'planLimit' => $this->getPlanLimit($user->plan_id),
        'isProfessor' => $user->plan_id == 4,
        'conflictingActivityIds' => $conflictingActivityIds,
    ]);
}

    private function getPlanLimit($planId)
    {
        return match ($planId) {
            1 => 1,     // Bronze
            2 => 5,     // Prata
            default => PHP_INT_MAX,  // Gold é ilimitado
        };
    }

    public function create()
    {
        $user = Auth::user();

        // Verifica se o usuário é um professor (plan_id = 4)
        if ($user->plan_id != 4) {
            abort(403, 'Acesso negado. Apenas professores podem criar atividades.');
        }

        return view('activities.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->plan_id != 4) {
            abort(403, 'Acesso negado.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required',
            'end_time' => 'required',
            'day_of_week' => 'required|string',
            'max_students' => 'required|integer|min:1',
        ]);

        // Verificar se já existe uma atividade no mesmo dia da semana e horário
        $conflict = Activity::where('day_of_week', $request->day_of_week)
                            ->where('start_time', $request->start_time)
                            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'conflict' => 'Já existe uma atividade nesse horário e dia da semana.',
            ]);
        }

        Activity::create([
            'name' => $request->name,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'day_of_week' => $request->day_of_week,
            'max_students' => $request->max_students,
            'user_id' => $request->user_id,
        ]);

        return redirect()->route('activities.index')->with('success', 'Atividade criada com sucesso!');
    }

    // app/Http/Controllers/ActivityController.php

public function edit(Activity $activity)
{
    // Confirma que apenas o professor que criou a atividade pode editá-la
    $this->authorize('update', $activity);
    return view('activities.edit', compact('activity'));
}

public function update(Request $request, Activity $activity)
{
    $this->authorize('update', $activity);

    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'start_time' => 'required',
        'end_time' => 'required',
        'day_of_week' => 'required|string',
        'max_students' => 'required|integer|min:1',
    ]);

    $activity->update($request->only(['name', 'description', 'start_time', 'end_time', 'day_of_week', 'max_students']));

    return redirect()->route('activities.index')->with('success', 'Atividade atualizada com sucesso!');
}

public function destroy(Activity $activity)
{
    $this->authorize('delete', $activity);
    $activity->delete();

    return redirect()->route('activities.index')->with('success', 'Atividade excluída com sucesso!');
}

public function week()
{
    $user = Auth::user();

    // Verifica se o usuário não é um professor
    if ($user->plan_id == 4) {
        abort(403, 'Acesso negado. Apenas usuários comuns podem acessar esta página.');
    }

    // Busca as reservas do usuário na semana atual
    $reservations = Reservation::with('activity')
        ->where('user_id', $user->id)
        ->whereBetween('reservation_date', [now()->startOfWeek(), now()->endOfWeek()])
        ->get();

    // Extrai as atividades associadas às reservas
    $activities = $reservations->map->activity;

    return view('activities.week', [
        'activities' => $activities,
    ]);
}

private function hasTimeOverlap($startA, $endA, $startB, $endB)
{
    return ($startA < $endB) && ($startB < $endA);
}

}
