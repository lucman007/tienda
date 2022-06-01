<div class="col-lg-12">
    <h4>Plantilla:</h4>
</div>
<div class="col-lg-3">
    <div class="form-group">
        <label>Formato cotización:</label>
        <select v-model="cotizacion.formato" class="custom-select">
            @foreach($templates as $plantilla)
            <option {{$plantilla['disabled']}} value="{{$plantilla['value']}}">{{$plantilla['text']}}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="col-lg-2" style="margin-top: 18px">
    <div class="form-group">
        <b-button :href="'/configuracion/mostrar-plantilla-cotizacion/'+cotizacion.formato" target="_blank" variant="primary"><i class="fas fa-eye"></i> Ver plantilla</b-button>
    </div>
</div>
<hr class="my-4">
<div class="col-lg-12">
    <h4>Datos predeterminados:</h4>
</div>
<div class="col-lg-2 mt-3">
    <div class="form-group">
        <label>Días de validez</label>
        <input type="text" v-model="cotizacion.validez" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-4 mt-3">
    <div class="form-group">
        <label>Condiciones de pago</label>
        <input type="text" v-model="cotizacion.condicion_pago" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-2 mt-3">
    <div class="form-group">
        <label>Tiempo entrega</label>
        <input type="text" v-model="cotizacion.tiempo_entrega" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-2 mt-3">
    <div class="form-group">
        <label>Garantía</label>
        <input type="text" v-model="cotizacion.garantia" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-2 mt-3">
    <div class="form-group">
        <label>Impuesto</label>
        <input type="text" v-model="cotizacion.impuesto" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-4 mt-3">
    <div class="form-group">
        <label>Lugar de entrega</label>
        <input type="text" v-model="cotizacion.lugar_entrega" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-5 mt-3">
    <div class="form-group">
        <label>Atentamente</label>
        <input type="text" v-model="cotizacion.remitente" autocomplete="nope" class="form-control" placeholder="Al dejar en blanco se usará el nombre de usuario del sistema">
    </div>
</div>
<div class="col-lg-3 mt-3">
    <div class="form-group">
        <label>Teléfonos remitente</label>
        <input type="text" v-model="cotizacion.remitente_telefonos" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-12 mt-2">
    <h4>Texto correo:</h4>
</div>
<div class="col-lg-6 mt-3">
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <label>Saludo (Si deja en blanco el saludo lo generará el sistema)</label>
                <input v-model="cotizacion.texto_saludo" class="form-control">
            </div>
        </div>
        <div class="col-lg-12">
            <div class="form-group">
                <label>Cuerpo</label>
                <textarea rows="10" v-model="cotizacion.texto" class="form-control"></textarea>
            </div>
        </div>
    </div>
</div>
<div class="col-lg-12">
    <b-button @click="guardarConfiguracion('cotizacion')" class="mr-2 mb-5 float-right" variant="success"><i class="fas fa-save"></i> Guardar</b-button>
</div>