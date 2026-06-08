@extends('layouts.argon')

@section('content')
    <div class="row">
        <div class="col-md-6 order-2 order-md-1">
            <div class="card shadow-lg">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Equipos Mundial 2026</h2>

                        <form action="{{ route('equipos.sincronizar') }}" method="POST">
                            @csrf

                            <button type="submit" class="btn btn-primary">
                                Sincronizar Equipos
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 order-1 order-md-2">
            <div class="card shadow-lg">
                <div class="card-body">

                </div>

            </div>
        </div>
    </div>

    <div class="card mt-3 shadow-lg">
        <div class="card-body">

            <div class="row">

                @forelse($equipos as $equipo)
                    <div class="col-md-3 mb-4">

                        <div class="card h-100 shadow-sm">

                            <div class="text-center p-3">

                                <img src="{{ $equipo->logo }}" alt="{{ $equipo->nombre }}"
                                    style="height:100px; object-fit:contain;">

                            </div>

                            <div class="card-body text-center">

                                <h5 class="card-title">
                                    {{ $equipo->nombre }}
                                </h5>

                                <small class="text-muted">
                                    API ID: {{ $equipo->api_id }}
                                </small>

                            </div>

                        </div>

                    </div>

                @empty

                    <div class="col-12">

                        <div class="alert alert-warning">
                            No existen equipos registrados.
                        </div>

                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
