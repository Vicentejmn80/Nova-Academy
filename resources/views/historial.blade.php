<x-app-layout>
    @push('styles')
    <style>
        :root {
            --hist-grad-dark:    linear-gradient(160deg, #1e0f3c 0%, #2d1569 60%, #4a1072 100%);
            --hist-grad-primary: linear-gradient(135deg, #7c3aed 0%, #c026d3 100%);
            --hist-grad-manual:  linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }
        body { background: #f5f3ff !important; }

        .hist-banner {
            background: var(--hist-grad-dark);
            border-radius: 1.5rem;
            padding: 1.75rem 2rem;
            margin-bottom: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .hist-banner { flex-direction: row; align-items: center; justify-content: space-between; }
        }
        .hist-banner h1 {
            font-size: 1.65rem; font-weight: 900; margin: 0;
            background: linear-gradient(90deg, #e9d5ff, #fce7f3);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .hist-banner p { color: #c4b5fd; margin: .25rem 0 0; font-size: .9rem; }

        .btn-hist-primary {
            display: inline-flex; align-items: center; gap: .5rem;
            background: var(--hist-grad-primary); color: #fff !important;
            font-weight: 700; font-size: .85rem;
            padding: .65rem 1.25rem; border-radius: .875rem; border: none;
            text-decoration: none; cursor: pointer; white-space: nowrap;
            box-shadow: 0 4px 16px rgba(192,38,211,.3);
            transition: opacity .15s, transform .15s;
        }
        .btn-hist-primary:hover { opacity: .9; transform: translateY(-1px); }

        .historial-card {
            background: #fff;
            border: 1px solid #ede9fe;
            border-radius: 1.25rem;
            padding: 1.25rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: transform .2s ease, box-shadow .2s ease;
            position: relative;
            overflow: hidden;
        }
        .historial-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: var(--hist-grad-primary);
        }
        .historial-card.is-manual::before {
            background: var(--hist-grad-manual);
        }

        .historial-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(124,58,237,.12);
        }
        .hist-badge {
            display: inline-block; font-size: .72rem; font-weight: 700;
            padding: .25rem .7rem; border-radius: 999px; margin-bottom: .6rem;
            background: #f3f4f6; color: #374151;
        }
        .hist-badge.bulk { background: linear-gradient(135deg,#c026d3,#db2777); color: #fff; }
        .hist-badge.manual { background: var(--hist-grad-manual); color: #fff; }
        .hist-badge.ai { background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; }

        .historial-card h3 {
            font-size: .95rem; font-weight: 800; color: #1e1b4b; margin: 0 0 .5rem;
        }
        .hist-obj {
            font-size: .82rem; color: #6b7280; flex: 1;
            display: -webkit-box; -webkit-line-clamp: 3;
            -webkit-box-orient: vertical; overflow: hidden;
            margin-bottom: .75rem;
        }
        .hist-meta { font-size: .78rem; color: #8b5cf6; margin-bottom: .85rem; }
        .hist-actions { display: flex; justify-content: space-between; align-items: center; gap: .5rem; }
        
        .btn-hist-delete {
            width: 32px; height: 32px; border-radius: .625rem; border: 1px solid #fce7f3;
            background: #fdf4ff; color: #c026d3; display: flex; align-items: center;
            justify-content: center; cursor: pointer; transition: background .15s;
        }
        .btn-hist-delete:hover { background: #fce7f3; }
        
        .btn-hist-open {
            display: inline-flex; align-items: center; gap: .4rem;
            background: var(--hist-grad-primary); color: #fff !important;
            font-weight: 700; font-size: .8rem; padding: .45rem 1rem;
            border-radius: .75rem; text-decoration: none; transition: opacity .15s;
        }
        .btn-hist-open.btn-manual { background: var(--hist-grad-manual); }
        .btn-hist-open:hover { opacity: .88; }

        /* Modal Styles */
        .historial-modal-backdrop {
            position: fixed; inset: 0;
            background: rgba(30,15,60,.55); backdrop-filter: blur(4px);
            display: none; align-items: center; justify-content: center;
            z-index: 11000; padding: 1rem;
        }
        .historial-modal-backdrop.show { display: flex; }
        .historial-modal-card {
            width: min(96vw, 420px); background: #fff;
            border-radius: 1.25rem; box-shadow: 0 24px 48px rgba(30,15,60,.25);
            overflow: hidden;
        }
        .hist-modal-header { background: var(--hist-grad-dark); padding: 1rem 1.25rem; color: #fff; font-weight: 800; }
        .hist-modal-body { padding: 1.25rem; }
        .hist-modal-actions { display: flex; justify-content: flex-end; gap: .5rem; margin-top: 1rem; }
        .btn-cancel-hist { background: #f3f4f6; color: #374151; font-weight: 600; padding: .5rem 1.1rem; border-radius: .625rem; border: none; cursor: pointer; }
        .btn-delete-hist { background: linear-gradient(135deg,#db2777,#9d174d); color: #fff; font-weight: 700; padding: .5rem 1.1rem; border-radius: .625rem; border: none; cursor: pointer; }
    </style>
    @endpush

    <div class="container py-5">
        <div class="hist-banner">
            <div>
                <h1>📚 Historial de Planificaciones</h1>
                <p>Gestiona, visualiza y mejora tus clases guardadas bajo el sello Nova Academy.</p>
            </div>
            <a href="{{ route('teacher.hub') }}" class="btn-hist-primary">
                <i class="fas fa-plus"></i> Nueva Planificación
            </a>
        </div>

        @if($plans->isEmpty())
            <div class="text-center py-5">
                <div style="font-size: 4rem; margin-bottom: 1rem;">📂</div>
                <h2 class="h4 text-dark font-weight-bold">No hay planes guardados</h2>
                <p class="text-muted">Tus planificaciones aparecerán aquí una vez que las guardes.</p>
            </div>
        @else
            <div class="row g-4" id="historialGrid">
                @foreach($plans as $plan)
                    @php
                        // Aseguramos que el payload sea un array
                        $payload = is_array($plan->payload) ? $plan->payload : json_decode($plan->payload, true) ?? [];
                        $type = $payload['type'] ?? 'ai_plan';
                        $isManual = $type === 'manual_plan';
                        $isBulk = $type === 'bulk_plan';
                        $courseId = $payload['course_id'] ?? null;
                    @endphp

                    <div class="col-12 col-md-6 col-lg-4" data-plan-card-id="{{ $plan->id }}">
                        <div class="historial-card {{ $isManual ? 'is-manual' : '' }}">
                            
                            {{-- Badge Dinámico --}}
                            @if($isManual)
                                <span class="hist-badge manual"><i class="fas fa-hand-paper me-1"></i> Plan Manual</span>
                            @elseif($isBulk)
                                <span class="hist-badge bulk"><i class="fas fa-calendar-alt me-1"></i> Plan Mensual</span>
                            @else
                                <span class="hist-badge ai"><i class="fas fa-robot me-1"></i> Generado por IA</span>
                            @endif

                            <h3>{{ $plan->tema ?: 'Sin título' }}</h3>
                            
                            <p class="hist-obj">
                                {{ \Illuminate\Support\Str::limit($plan->objetivo ?: 'Sin objetivo definido', 120) }}
                            </p>

                            <div class="hist-meta">
                                <span><i class="far fa-calendar-alt me-1"></i> {{ $plan->created_at?->format('d/m/Y') }}</span>
                                @if(isset($payload['nivel_educativo']))
                                    <span><i class="fas fa-graduation-cap me-1"></i> {{ ucfirst($payload['nivel_educativo']) }}</span>
                                @endif
                            </div>

                            <div class="hist-actions">
                                {{-- Botón Eliminar --}}
                                <button type="button" 
                                        class="btn-hist-delete btn-delete-plan" 
                                        data-plan-id="{{ $plan->id }}" 
                                        data-plan-title="{{ $plan->tema }}"
                                        title="Eliminar">
                                    <i class="fas fa-trash-alt"></i>
                                </button>

                                {{-- Botón Abrir Dinámico --}}
                                @if($isManual)
                                    {{-- Enlace corregido a la ficha técnica --}}
                                    <a href="{{ route('teacher.planner.show', $plan->id) }}" class="btn-hist-open btn-manual">
                                        <i class="fas fa-eye me-1"></i> Ver Sesiones
                                    </a>
                                @elseif($isBulk && $courseId)
                                    <a href="{{ route('teacher.hub', ['course' => $courseId, 'plan_block' => $plan->id]) }}" class="btn-hist-open">
                                        <i class="fas fa-calendar-check me-1"></i> Calendario
                                    </a>
                                @else
                                    <a href="{{ route('dashboard', ['plan' => $plan->id]) }}" class="btn-hist-open">
                                        <i class="fas fa-folder-open me-1"></i> Abrir
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Modal de Confirmación de Eliminación --}}
    <div id="deletePlanModal" class="historial-modal-backdrop" aria-hidden="true">
        <div class="historial-modal-card">
            <div class="hist-modal-header">
                <i class="fas fa-exclamation-triangle me-2"></i> Confirmar eliminación
            </div>
            <div class="hist-modal-body">
                <p class="text-muted mb-2">¿Estás seguro de que deseas eliminar esta planificación? Esta acción es irreversible.</p>
                <p class="font-weight-bold" id="deletePlanModalTitle"></p>
                <div class="hist-modal-actions">
                    <button type="button" class="btn-cancel-hist" id="btnCancelDelete">Cancelar</button>
                    <button type="button" class="btn-delete-hist" id="btnConfirmDelete">Eliminar ahora</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            'use strict';
            let pendingDeleteId = null;
            const modal = document.getElementById('deletePlanModal');
            const btnConfirm = document.getElementById('btnConfirmDelete');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            // Abrir modal
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.btn-delete-plan');
                if (btn) {
                    pendingDeleteId = btn.dataset.planId;
                    document.getElementById('deletePlanModalTitle').textContent = btn.dataset.planTitle;
                    modal.classList.add('show');
                }
            });

            // Cerrar modal
            document.getElementById('btnCancelDelete').addEventListener('click', () => modal.classList.remove('show'));

            // Confirmar eliminación
            btnConfirm.addEventListener('click', function () {
                if (!pendingDeleteId) return;
                
                btnConfirm.disabled = true;
                btnConfirm.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                fetch(`/planificaciones/${pendingDeleteId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector(`[data-plan-card-id="${pendingDeleteId}"]`).remove();
                        modal.classList.remove('show');
                    }
                })
                .catch(() => alert('Error al eliminar'))
                .finally(() => {
                    btnConfirm.disabled = false;
                    btnConfirm.textContent = 'Eliminar ahora';
                });
            });
        })();
    </script>
    @endpush
</x-app-layout>