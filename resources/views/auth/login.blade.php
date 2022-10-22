@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6 offset-md-3 col-lg-4 offset-lg-4">
            <figure style="text-align: center; margin-top: 120px;">
                <img style="width: 50%" src="{{isset( cache('config')['logo_comprobantes'] )?url('images/'.cache('config')['logo_comprobantes']):''}}" alt="">
            </figure>
            <div class="card">
                <div class="card-header">
                    <div class="col-md-12">Login</div>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" method="POST" action="{{ route('login') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('usuario') ? ' has-error' : '' }}">
                            <label for="usuario" class="col-md-12 control-label">Usuario</label>
                            <div class="col-md-12">
                                <input id="usuario" type="text" class="form-control" name="usuario" value="{{ old('usuario') }}" required autofocus>
                            </div>
                        </div>
                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password" class="col-md-12 control-label">Contraseña</label>
                            <div class="col-md-12">
                                <input id="password" type="password" class="form-control" name="password" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                            <span class="help-block">
                                <strong>{{ $errors->first('mensaje') }}</strong>
                            </span>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Recordar
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary float-right">
                                    Acceder
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
