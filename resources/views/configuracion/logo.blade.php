<div class="col-lg-6">
    <div class="row">
        <div class="col-lg-12">
            <h4>Logo para comprobantes</h4>
        </div>
        <div class="col-lg-12">
            <div class="form-group">
                @if($configuracion->logo_comprobantes == '')
                    <button @click="modalImagen('logo_comprobantes','{{$configuracion->logo_comprobantes}}')" class="btn btn-primary"><i class="fas fa-plus"></i> Agregar</button>
                @else
                    <img src="{{url('images/'.$configuracion->logo_comprobantes)}}" alt="logo" class="img-thumbnail" style="width: 250px; height: auto">
                    <button @click="borrarFichero('logo_comprobantes')" class="btn btn-danger btn-borrar-fichero">
                        <i class="fas fa-times"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
