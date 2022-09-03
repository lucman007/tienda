<div class="col-lg-12">
    <h4>Interfaz:</h4>
</div>
<div class="col-lg-6">
    <div class="form-group">
        <label>Estilo cabecera superior</label>
        <input type="text" v-model="interfaz.top_header_style" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-6">
    <div class="form-group">
        <label>Estilo cabecera inferior</label>
        <input type="text" v-model="interfaz.bottom_header_style" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-4 mt-3">
    <b-form-checkbox v-model="interfaz.layout" switch size="lg">
        <p style="font-size: 1rem;">Ancho completo</p>
    </b-form-checkbox>
</div>
<div class="col-lg-4 mt-3">
    <b-form-checkbox v-model="interfaz.igv_incluido" switch size="lg">
        <p style="font-size: 1rem;">Igv incluido</p>
    </b-form-checkbox>
</div>
<div class="col-lg-4 mt-3">
    <b-form-checkbox v-model="interfaz.rawbt" switch size="lg">
        <p style="font-size: 1rem;">Usar RawBt App</p>
    </b-form-checkbox>
</div>
<div class="col-lg-4 mt-3">
    <b-form-checkbox v-model="interfaz.emitir_solo_ticket" switch size="lg">
        <p style="font-size: 1rem;">Emitir solo ticket</p>
    </b-form-checkbox>
</div>
<div class="col-lg-4 mt-3">
    <b-form-checkbox v-model="interfaz.notificar_caja" switch size="lg">
        <p style="font-size: 1rem;">Notificar movimientos de caja</p>
    </b-form-checkbox>
</div>
<div class="col-lg-4 mt-3">
    <b-form-checkbox v-model="interfaz.cierre_detallado" switch size="lg">
        <p style="font-size: 1rem;">Cierre de caja detallado</p>
    </b-form-checkbox>
</div>
<div class="col-lg-4 mt-3">
    <b-form-checkbox v-model="interfaz.buscador_productos_alt" switch size="lg">
        <p style="font-size: 1rem;">Buscador de productos alternativo</p>
    </b-form-checkbox>
</div>
<div class="col-lg-12 mt-3">
    <div class="row">
        <div class="col-lg-2">
            <label>Ancho de logo</label>
            <b-input-group>
                <input type="number" v-model="interfaz.ancho_logo" autocomplete="nope" class="form-control">
                <b-input-group-append>
                    <b-input-group-text>
                        mm
                    </b-input-group-text>
                </b-input-group-append>
            </b-input-group>
        </div>
    </div>
</div>
<div class="col-lg-12">
    <b-button @click="guardarConfiguracion('interfaz')" class="mr-2 mb-5 float-right" variant="success"><i class="fas fa-save"></i> Guardar</b-button>
</div>
<hr class="my-4">
<div class="col-lg-12">
    <h4>Pedidos:</h4>
</div>
<div class="col-lg-4 mt-3">
    <select v-model="interfaz_pedidos.tipo" class="custom-select">
        <option value="modo_1">Interfaz 1</option>
        <option value="modo_2">Interfaz 2</option>
        <option value="modo_3">Interfaz 3</option>
    </select>
</div>
<div class="col-lg-4 mt-3">
    <b-form-checkbox v-model="interfaz_pedidos.solo_comprobantes" switch size="lg">
        <p style="font-size: 1rem;">Ver solo comprobantes</p>
    </b-form-checkbox>
</div>
<div class="col-lg-12">
    <b-button @click="guardarConfiguracion('interfaz_pedidos')" class="mr-2 mb-5 float-right" variant="success"><i class="fas fa-save"></i> Guardar</b-button>
</div>