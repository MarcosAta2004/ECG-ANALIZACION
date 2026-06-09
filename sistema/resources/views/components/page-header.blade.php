{{-- ============================================================
     Componente: Page Header
     Props:
       - $title     (string) — Título principal de la página
       - $subtitle  (string, opcional) — Descripción o subtítulo
       - $breadcrumbs (array, opcional) — [['label' => '...', 'route' => '...']]
============================================================ --}}
@props([
    'title'       => '',
    'subtitle'    => null,
    'breadcrumbs' => [],
])

<div class="row">
    <div class="col-md-12">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">

            {{-- Título y subtítulo --}}
            <div>
                <h1 class="h4 mb-1 fw-semibold">{{ $title }}</h1>
                @if($subtitle)
                    <p class="mb-0 text-secondary small">{{ $subtitle }}</p>
                @endif
            </div>

            {{-- Breadcrumbs --}}
            @if(!empty($breadcrumbs))
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Inicio</a>
                    </li>
                    @foreach($breadcrumbs as $crumb)
                        @if(!$loop->last)
                            <li class="breadcrumb-item">
                                @if(isset($crumb['route']))
                                    <a href="{{ $crumb['route'] }}">{{ $crumb['label'] }}</a>
                                @else
                                    {{ $crumb['label'] }}
                                @endif
                            </li>
                        @else
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ $crumb['label'] }}
                            </li>
                        @endif
                    @endforeach
                </ol>
            </nav>
            @endif

            {{-- Slot para acciones (botones, etc.) --}}
            @if($slot->isNotEmpty())
            <div class="d-flex align-items-center gap-2">
                {{ $slot }}
            </div>
            @endif

        </div>
    </div>
</div>
