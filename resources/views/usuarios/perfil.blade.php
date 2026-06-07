@extends('layouts.argon')

@section('content')

    

        <div class="card shadow-lg mx-4 card-profile-bottom">
            <div class="card-body p-3">
                <div class="row gx-4">
                    <div class="col-auto">
                        <div class="avatar avatar-xl position-relative">
                            @if ($user->foto_perfil)
                                <img src="{{ asset($user->foto_perfil) }}" alt="profile_image"
                                    class="w-100 border-radius-lg shadow-sm">
                            @else
                                <img src="{{ asset('update/imagenes/user.jpg') }}" alt="profile_image"
                                class="w-100 border-radius-lg shadow-sm">
                            @endif
                        </div>
                    </div>
                    <div class="col-auto my-auto">
                        <div class="h-100">
                            <h5 class="mb-1">
                                {{ $user->usuario_nombres }} {{ $user->usuario_app }} {{ $user->usuario_apm }}
                            </h5>
                            @foreach($user->roles as $role) 
                            <p class="mb-0 font-weight-bold text-sm">
                              
                                {{$role->name;}}
                             
                               
                            </p>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 my-sm-auto ms-sm-auto me-sm-0 mx-auto mt-3">
                    <div class="nav-wrapper position-relative end-0">
                        <ul class="nav nav-pills nav-fill p-1" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1 active d-flex align-items-center justify-content-center" data-bs-toggle="tab" href="javascript:;" role="tab" aria-selected="true">
                                    <i class="fas fa-mobile-alt"></i> 
                                    <span class="ms-2">Aplicación</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1 d-flex align-items-center justify-content-center" data-bs-toggle="tab" href="javascript:;" role="tab" aria-selected="false">
                                    <i class="fas fa-envelope"></i> 
                                    <span class="ms-2">Mensajes</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1 d-flex align-items-center justify-content-center" data-bs-toggle="tab" href="javascript:;" role="tab" aria-selected="false">
                                    <i class="fas fa-cogs"></i>
                                    <span class="ms-2">Configuración</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                    <form action="{{ route('users.update',['id'=>  Auth::user()->id, 'perfil' => 1]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                        <div class="card-header pb-0">
                            <div class="d-flex align-items-center">
                                <p class="mb-0">Editar Perfil</p>
                                <button type="submit" class="btn btn-primary btn-sm ms-auto">Actualizar Datos</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-uppercase text-sm">Informacion de Usuario</p>
                           
                            <div class="row">
                                <div class="col-md-6">
                                <div class="form-group">
                                    <label for="profile_picture" class="form-control-label">Foto de Perfil</label>
                                    <div class="d-flex align-items-center">
                                        <!-- Campo para cargar imagen -->
                                        <input type="file" id="profile_picture" name="profile_picture" class="form-control @error('profile_picture') is-invalid @enderror" accept="image/*" onchange="previewImage(event)">
                                        
                                        <!-- Imagen previsualizada -->
                                        <div class="ms-3" id="preview-container">
                                            <img id="preview-img" src="#" alt="Previsualización" style="display: none; width: 80px; height: 80px; border-radius: 10%; object-fit: cover;">
                                        </div>
                                        
                                        <!-- Botón para eliminar imagen -->
                                        <button type="button" id="remove-img" class="btn btn-danger ms-2" style="display: none;" onclick="removeImage()">Eliminar</button>
                                    </div>
                                    @error('profile_picture')
                                        <div class="invalid-feedback" style="display: block !important;">{{ $message }}</div>
                                    @enderror
                                </div>

                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-control-label">Nombre de usuario</label>
                                        <input id="name" class="form-control @error('name') is-invalid @enderror" name="name" type="text" value="{{ old('name', $user->name) }}">
                                        @error('name')
                                            <div class="invalid-feedback" style="display: block !important;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-control-label">Email</label>
                                        <input id="email" class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email', $user->email) }}">
                                        @error('email')
                                            <div class="invalid-feedback" style="display: block !important;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="usuario_nombres" class="form-control-label">Nombre</label>
                                        <input id="usuario_nombres" class="form-control @error('usuario_nombres') is-invalid @enderror" type="text" name="usuario_nombres" value="{{ old('usuario_nombres', $user->usuario_nombres) }}">
                                        @error('usuario_nombres')
                                            <div class="invalid-feedback" style="display: block !important;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="usuario_app" class="form-control-label">Apellido Paterno</label>
                                        <input id="usuario_app" class="form-control @error('usuario_app') is-invalid @enderror" name="usuario_app" type="text" value="{{ old('usuario_app', $user->usuario_app) }}">
                                        @error('usuario_app')
                                            <div class="invalid-feedback" style="display: block !important;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="usuario_apm" class="form-control-label">Apellido Materno</label>
                                        <input id="usuario_apm" class="form-control @error('usuario_apm') is-invalid @enderror" name="usuario_apm" type="text" value="{{ old('usuario_apm', $user->usuario_apm) }}">
                                        @error('usuario_apm')
                                            <div class="invalid-feedback" style="display: block !important;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="usuario_telefono" class="form-control-label">Teléfono</label>
                                        <input id="usuario_telefono" class="form-control @error('usuario_telefono') is-invalid @enderror" name="usuario_telefono" type="text" value="{{ old('usuario_telefono', $user->usuario_telefono) }}">
                                        @error('usuario_telefono')
                                            <div class="invalid-feedback" style="display: block !important;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="usuario_direccion" class="form-control-label">Dirección</label>
                                        <input id="usuario_direccion" class="form-control @error('usuario_direccion') is-invalid @enderror" name="usuario_direccion" type="text" value="{{ old('usuario_direccion', $user->usuario_direccion) }}">
                                        @error('usuario_direccion')
                                            <div class="invalid-feedback" style="display: block !important;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                    </form>
                          
                            <hr class="horizontal dark">
                            <p class="text-uppercase text-sm">Informacion Adicional</p>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="example-text-input" class="form-control-label">Address</label>
                                        <input class="form-control" type="text"
                                            value="Bld Mihail Kogalniceanu, nr. 8 Bl 1, Sc 1, Ap 09">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="example-text-input" class="form-control-label">City</label>
                                        <input class="form-control" type="text" value="New York">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="example-text-input" class="form-control-label">Country</label>
                                        <input class="form-control" type="text" value="United States">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="example-text-input" class="form-control-label">Postal code</label>
                                        <input class="form-control" type="text" value="437300">
                                    </div>
                                </div>
                            </div>


                            <hr class="horizontal dark">
                            <p class="text-uppercase text-sm">About me</p>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="example-text-input" class="form-control-label">About me</label>
                                        <input class="form-control" type="text"
                                            value="A beautiful Dashboard for Bootstrap 5. It is Free and Open Source.">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
               
            </div>
            <footer class="footer pt-3  ">
                <div class="container-fluid">
                    <div class="row align-items-center justify-content-lg-between">
                        <div class="col-lg-6 mb-lg-0 mb-4">
                            <div class="copyright text-center text-sm text-muted text-lg-start">
                                ©
                                <script>
                                    document.write(new Date().getFullYear())
                                </script>,
                                made with <i class="fa fa-heart"></i> by
                                <a href="https://www.creative-tim.com" class="font-weight-bold" target="_blank">Creative
                                    Tim</a>
                                for a better web.
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                                <li class="nav-item">
                                    <a href="https://www.creative-tim.com" class="nav-link text-muted"
                                        target="_blank">Creative Tim</a>
                                </li>
                                <li class="nav-item">
                                    <a href="https://www.creative-tim.com/presentation" class="nav-link text-muted"
                                        target="_blank">About Us</a>
                                </li>
                                <li class="nav-item">
                                    <a href="https://www.creative-tim.com/blog" class="nav-link text-muted"
                                        target="_blank">Blog</a>
                                </li>
                                <li class="nav-item">
                                    <a href="https://www.creative-tim.com/license" class="nav-link pe-0 text-muted"
                                        target="_blank">License</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
       
    

   


    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }

       
        function previewImage(event) {
            const file = event.target.files[0];
            const previewImg = document.getElementById("preview-img");
            const removeBtn = document.getElementById("remove-img");
            const previewContainer = document.getElementById("preview-container");

          
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;  
                    previewImg.style.display = "block";  
                    removeBtn.style.display = "inline-block"; 
                }
                
                reader.readAsDataURL(file);  
            }
        }

        
        function removeImage() {
            const previewImg = document.getElementById("preview-img");
            const removeBtn = document.getElementById("remove-img");
            const inputFile = document.getElementById("profile_picture");
            
         
            previewImg.style.display = "none";
            removeBtn.style.display = "none";
            inputFile.value = ""; 
        }

    </script>


@endsection