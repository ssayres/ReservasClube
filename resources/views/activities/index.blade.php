{{-- resources/views/activities/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Atividades Disponíveis') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @foreach ($activities as $activity)
                        <div class="mb-4 p-4 border rounded">
                            <h3 class="text-lg font-semibold">{{ $activity->name }}</h3>
                            <p>{{ $activity->description }}</p>
                            <p><strong>Horário:</strong> {{ $activity->start_time }} - {{ $activity->end_time }}</p>
                            <p><strong>Dia da Semana:</strong> {{ ucfirst($activity->day_of_week) }}</p>
                            <p><strong>Vagas:</strong> {{ $activity->max_students - $activity->reservations->count() }}</p>

                            @if ($isProfessor)
                                {{-- Opções de edição e exclusão para professores --}}
                                <a href="{{ route('activities.edit', $activity->id) }}" class="btn btn-secondary">Editar</a>
                                
                                <form action="{{ route('activities.destroy', $activity->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Excluir</button>
                                </form>
                            @else
                                {{-- Verifica se há conflito de horário com atividades reservadas --}}
                                @if (in_array($activity->id, $conflictingActivityIds))
                                    <p class="text-yellow-600">Horário Conflitante: Você já possui uma reserva neste horário.</p>
                                @endif

                                {{-- Botão de reserva para alunos, desde que dentro do limite do plano e sem conflito --}}
                                @if (!in_array($activity->id, $conflictingActivityIds) && $dailyReservations < $planLimit)
                                    <form action="{{ route('reservations.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="activity_id" value="{{ $activity->id }}">
                                        <button type="submit" class="btn btn-primary">Reservar</button>
                                    </form>
                                @elseif (!in_array($activity->id, $conflictingActivityIds))
                                    <p class="text-red-600">Limite de reservas diárias atingido pelo plano.</p>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
