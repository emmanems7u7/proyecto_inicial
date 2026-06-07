@extends('layouts.argon')

@section('content')

    

        <div class="card shadow-lg mx-4 card-profile-bottom">
            <div class="card-body p-3">
                <p>Actualiza tu contraseña</p>
            </div>
        </div>
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                    <div class="card-body">
                                <form action="{{ route('password.actualizar') }}" method="POST">
                                <hr class="horizontal dark">
                                <div class="d-flex align-items-center">
                                        <p class="text-uppercase text-sm">Actualizar Contraseña</p>
                                        <button type="submit"  class="btn btn-primary btn-sm ms-auto">Actualizar Contraseña</button>
                                        
                                    
                                    </div>
                                    <p>{{ $tiempo_cambio_contraseña }}</p>
                                
                                            @csrf
                                            @method('PUT') <!-- Usamos PUT porque estamos actualizando los datos -->
                                            
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label for="current-password" class="form-control-label">Contraseña Actual</label>
                                                            <div class="input-group">
                                                                <input id="current-password" class="form-control @error('current_password') is-invalid @enderror" type="password" name="current_password" required>
                                                            </div>
                                                            @error('current_password')
                                                                <div class="invalid-feedback" style="    display: block !important;">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="new-password" class="form-control-label">Nueva Contraseña</label>
                                                            <div class="input-group">
                                                                <input id="new-password" class="form-control @error('new_password') is-invalid @enderror" type="password" name="new_password" required>
                                                            </div>
                                                            @error('new_password')
                                                                <div class="invalid-feedback" style="    display: block !important;">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="confirm-password" class="form-control-label">Repita su Contraseña</label>
                                                            <div class="input-group">
                                                                <input id="confirm-password" class="form-control @error('new_password_confirmation') is-invalid @enderror" type="password" name="new_password_confirmation" required>
                                                            </div>
                                                            @error('new_password_confirmation')
                                                                <div class="invalid-feedback" style="    display: block !important;">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                </form>

                        </div>    
                    </div>
                    </div>
                </div>
               
            </div>
        
    
    

  


@endsection