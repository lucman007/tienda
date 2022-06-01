<div class="col-lg-12 mb-1">
    <h4>Roles y permisos</h4>
</div>
<div class="col-lg-6">
    @can('Configuración: crear roles')
        <div class="form-group">
            <label for="nombre_rol">Nuevo rol:</label>
            <input placeholder="Nombre de rol" type="text" v-model="nombre_rol" name="nombre_rol"  autocomplete="nope" class="form-control">
            <button @click="guardar_rol" class="boton_adjunto btn btn-success"><i class="fas fa-save"></i></button>
        </div>
    @endcan
    <ul>
        @foreach($roles as $rol)
            @if($rol['name']!='Superusuario')
                <li class="alert alert-info">{{$rol['name']}}
                    <div class="rol-opciones">
                        <a href="{{'/configuracion/permisos/'.$rol['id']}}"  class="btn btn-info" title="Privilegios"><i class="fas fa-key"></i></a>
                        <a onclick="return confirm('¿Seguro de eliminar el rol?')" href="{{'/configuracion/permisos/eliminar/'.$rol['id']}}"  class="btn btn-danger" title="Eliminar"><i class="fas fa-trash"></i></a>
                    </div>
                </li>
            @endif
        @endforeach
    </ul>
</div>
@can('Configuración: crear permisos')
    <div class="col-lg-6">
        <div class="form-group">
            <label for="nombre_rol">Nuevo Permiso:</label>
            <input placeholder="Nombre de permiso" type="text" v-model="nombre_permiso" name="nombre_permiso"  autocomplete="nope" class="form-control">
            <button @click="guardar_permiso" class="boton_adjunto btn btn-success"><i class="fas fa-save"></i></button>
        </div>
        <ul>
            @foreach($permisos as $permiso)
                <li style="list-style: disc; margin-left: 30px">{{$permiso['name']}}
                    <div class="float-right">
                        <a v-on:click="editarPermiso({{$permiso['id']}},'{{$permiso['name']}}')" class="text-success" href="javascript:void(0)">
                            <i class="fas fa-edit mr-2"></i>
                        </a>
                        <a v-on:click="borrarPermiso({{$permiso['id']}})" href="javascript:void(0)" class="text-danger"><i class="fas fa-trash"></i>
                        </a>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endcan