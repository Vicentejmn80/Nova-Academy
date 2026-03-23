<x-app-layout>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 text-slate-800">Planificaciones Guardadas</h1>
                <p class="text-muted mb-0">Tu historial de clases para reabrir, editar y mejorar.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-primary rounded-3">
                <i class="fas fa-plus me-1"></i> Nueva planificación
            </a>
        </div>

        @if($plans->isEmpty())
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                <i class="fas fa-folder-open fa-3x text-primary opacity-50 mb-3"></i>
                <h2 class="h5 mb-2">Aún no tienes planificaciones guardadas</h2>
                <p class="text-muted mb-4">Genera una nueva clase y presiona “Guardar en mi historial”.</p>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary rounded-3">Ir al dashboard</a>
            </div>
        @else
            <div class="row g-4">
                @foreach($plans as $plan)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100 rounded-4 p-3 bg-white">
                            <div class="card-body d-flex flex-column">
                                <span class="badge bg-primary bg-opacity-10 text-primary align-self-start mb-2">Plan guardado</span>
                                <h3 class="h6 fw-bold mb-2 text-dark">{{ $plan->tema ?: 'Sin tema' }}</h3>
                                <p class="text-muted small mb-3">
                                    {{ \Illuminate\Support\Str::limit($plan->objetivo ?: 'Sin objetivo', 140) }}
                                </p>
                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ $plan->created_at?->format('d/m/Y H:i') }}</small>
                                    <a href="{{ route('dashboard', ['plan' => $plan->id]) }}" class="btn btn-sm btn-primary rounded-3">
                                        <i class="fas fa-eye me-1"></i> Ver
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>

