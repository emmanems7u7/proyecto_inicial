@extends('layouts.argon')

@section('content')


    <script src="{{ asset('js/app.js') }}"></script>

    <div class="card card-frame card-profile-bottom">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    Acciones
                </div>

            </div>

            <div class="row mt-3">
                <div class="col">
                    <div class="col">

                        @can('usuarios.crear')
                            <a class="btn btn-primary mb-3" href="{{ route('users.create') }}">Crear Usuario</a>
                        @endcan

                        @can('usuarios.exportar_excel')
                            <a href="{{ route('usuarios.exportar_excel') }}" class="btn btn-success mb-3">Exportar a Excel</a>
                        @endcan
                        @can('usuarios.exportar_pdf')
                            <a href="{{ route('usuarios.exportar_pdf') }}" class="btn btn-success mb-3" target="_blank">Exportar
                                a PDF</a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="table-responsive">
            @include('usuarios.tabla_usuarios', ['usuarios' => $users])
        </div>
        <div class="d-flex justify-content-center">
            <nav>
                <ul class="pagination">
                    <!-- Página Anterior -->
                    @if ($users->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link" aria-label="Previous">
                                <i class="fa fa-angle-left"></i>
                                <span class="sr-only">Previous</span>
                            </span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $users->previousPageUrl() }}" aria-label="Previous">
                                <i class="fa fa-angle-left"></i>
                                <span class="sr-only">Previous</span>
                            </a>
                        </li>
                    @endif

                    <!-- Páginas Numeradas -->
                    @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $users->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach

                    <!-- Página Siguiente -->
                    @if ($users->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $users->nextPageUrl() }}" aria-label="Next">
                                <i class="fa fa-angle-right"></i>
                                <span class="sr-only">Next</span>
                            </a>
                        </li>
                    @else
                        <li class="page-item disabled">
                            <span class="page-link" aria-label="Next">
                                <i class="fa fa-angle-right"></i>
                                <span class="sr-only">Next</span>
                            </span>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>

    </div>

    <script>

        var ModalEditar = new bootstrap.Modal(document.getElementById('modal_edit_usuario'));
        function editarModal(user_id) {

            fetch(`/datos/usuario/${user_id}`)
                .then(response => response.json())
                .then(data => {

                    var actionUrl = "{{ route('users.update', ['id' => '_id_', 'perfil' => 0]) }}";
                    actionUrl = actionUrl.replace('_id_', user_id);

                    document.getElementById('form_edit').action = actionUrl;
                    document.getElementById('user_id').value = data.user_id;
                    document.getElementById('name_edit').value = data.name;
                    document.getElementById('email_edit').value = data.email;
                    document.getElementById('usuario_nombres_edit').value = data.usuario_nombres;
                    document.getElementById('usuario_app_edit').value = data.usuario_app;
                    document.getElementById('usuario_apm_edit').value = data.usuario_apm;
                    document.getElementById('usuario_telefono_edit').value = data.usuario_telefono;
                    document.getElementById('usuario_direccion_edit').value = data.usuario_direccion;
                    ModalEditar.show();
                })
                .catch(error => console.error('Error al obtener los datos:', error));
        }

        document.addEventListener('DOMContentLoaded', function () {

            // Verifica si hay errores de validación
            let hasErrors = @json($errors->any());
            let formAction = @json(session('form_action'));
            let user_id = @json(session('user_id'));
            if (hasErrors) {
                if (formAction == 'update') {
                    editarModal(user_id)
                }
                else {
                    document.getElementById('modal_create_usuario_button').click();
                }

            }

        });

    </script>
@endsection