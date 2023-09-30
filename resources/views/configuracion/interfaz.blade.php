<div class="col-lg-12">
    <h4>Interfaz:</h4>
</div>
<div class="col-lg-4">
    <div class="form-group">
        <label>Color cabecera superior</label>
        <input type="text" v-model="interfaz.top_header_style" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-4">
    <div class="form-group">
        <label>Color cabecera inferior</label>
        <input type="text" v-model="interfaz.bottom_header_style" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-4">
    <div class="form-group">
        <label>Color texto cabecera superior</label>
        <input type="text" v-model="interfaz.text_top_header_style" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-3 mt-3">
    <b-form-checkbox v-model="interfaz.layout" switch size="lg">
        <p style="font-size: 14px;">Ancho completo</p>
    </b-form-checkbox>
</div>
<div class="col-lg-3 mt-3">
    <b-form-checkbox v-model="interfaz.igv_incluido" switch size="lg">
        <p style="font-size: 14px;">Igv incluido</p>
    </b-form-checkbox>
</div>
<div class="col-lg-3 mt-3">
    <b-form-checkbox v-model="interfaz.rawbt" switch size="lg">
        <p style="font-size: 14px;">Usar RawBt App</p>
    </b-form-checkbox>
</div>
<div class="col-lg-3 mt-3">
    <b-form-checkbox v-model="interfaz.emitir_solo_ticket" switch size="lg">
        <p style="font-size: 14px;">Emitir solo ticket</p>
    </b-form-checkbox>
</div>
<div class="col-lg-3 mt-3">
    <b-form-checkbox v-model="interfaz.notificar_caja" switch size="lg">
        <p style="font-size: 14px;">Notificar movimientos de caja</p>
    </b-form-checkbox>
</div>
<div class="col-lg-3 mt-3">
    <b-form-checkbox v-model="interfaz.cierre_detallado" switch size="lg">
        <p style="font-size: 14px;">Cierre de caja detallado</p>
    </b-form-checkbox>
</div>
<div class="col-lg-3 mt-3">
    <b-form-checkbox v-model="interfaz.cierre_automatico" switch size="lg">
        <p style="font-size: 14px;">Cierre de caja automatico</p>
    </b-form-checkbox>
</div>
<div class="col-lg-3 mt-3">
    <b-form-checkbox v-model="interfaz.reporte_ventas_manual" switch size="lg">
        <p style="font-size: 14px;">Reporte de ventas manual (Alto volumen)</p>
    </b-form-checkbox>
</div>
<div class="col-lg-3 mt-3">
    <b-form-checkbox v-model="interfaz.buscador_productos_alt" switch size="lg">
        <p style="font-size: 14px;">Buscador de productos alternativo</p>
    </b-form-checkbox>
</div>
<div class="col-lg-3 mt-3">
    <b-form-checkbox v-model="interfaz.colapsar_categorias" switch size="lg">
        <p style="font-size: 14px;">Colapsar categorías en pedidos</p>
    </b-form-checkbox>
</div>
<div class="col-lg-3 mt-3">
    <b-form-checkbox v-model="interfaz.aumentar_cantidad_producto" switch size="lg">
        <p style="font-size: 14px;">Aumentar cantidad al agregar un producto más de una vez al pedido</p>
    </b-form-checkbox>
</div>
<div class="col-lg-3 mt-3">
    <div class="form-group">
        <label>Impresión:</label>
        <select v-model="interfaz.tipo_impresion" class="custom-select">
            <option value="1">Abrir en nueva pestaña</option>
            <option value="2">Abrir en ventana emergente</option>
        </select>
    </div>
</div>
<div class="col-lg-3 mt-3">
    <div class="form-group">
        <label>Buscador de clientes:</label>
        <select v-model="interfaz.buscador_clientes" class="custom-select">
            <option value="1">Casilla autocompletable</option>
            <option value="2">Ventana emergente</option>
        </select>
    </div>
</div>
<div class="col-lg-3 mt-3">
    <div class="form-group">
        <label>Buscador de productos:</label>
        <select v-model="interfaz.buscador_productos" class="custom-select">
            <option value="1">Casilla autocompletable</option>
            <option value="2">Ventana emergente</option>
        </select>
    </div>
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
<div class="col-lg-12 mt-4">
    <div class="row">
        <div class="col-lg-3">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Atención de:</label>
                        <input type="time" v-model="interfaz.atencion_inicio" class="form-control">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Hasta:</label>
                        <input type="time" v-model="interfaz.atencion_fin" class="form-control">
                    </div>
                </div>
            </div>
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
        <option value="modo_4">Interfaz 4</option>
    </select>
</div>
<div class="col-lg-4 mt-3">
    <b-form-checkbox v-model="interfaz_pedidos.solo_comprobantes" switch size="lg">
        <p style="font-size: 14px;">Ver solo comprobantes</p>
    </b-form-checkbox>
</div>
<div class="col-lg-12">
    <b-button @click="guardarConfiguracion('interfaz_pedidos')" class="mr-2 mb-5 float-right" variant="success"><i class="fas fa-save"></i> Guardar</b-button>
</div>
<hr class="my-4">
<div class="col-lg-12">
    <h4>Plan:</h4>
</div>
<div class="col-lg-3">
    <label>Plan mensual</label>
    <select v-model="plan.tipo" class="custom-select">
        <option value="plan_100">100 comprobantes</option>
        <option value="plan_500">500 comprobantes</option>
        <option value="plan_ilimitado">Ilimitado</option>
        <option value="plan_personalizado">Personalizado</option>
    </select>
</div>
<div v-show="plan.tipo == 'plan_personalizado'" class="col-lg-3">
    <label>Cantidad</label>
    <input type="number" class="form-control" v-model="plan.cantidad">
</div>
<div class="col-lg-3">
    <label>Tolerancia</label>
    <input type="number" class="form-control" v-model="plan.tolerancia">
</div>
<div class="col-lg-3">
    <label>Ciclo de pago</label>
    <select v-model="plan.ciclo" class="custom-select">
        <option value="mensual">Mensual</option>
        <option value="trimestral">Trimestral</option>
        <option value="anual">Anual</option>
    </select>
</div>
<div class="col-lg-3">
    <label>Inicio</label>
    <input type="date" v-model="plan.fecha" class="form-control">
</div>
<div class="col-lg-12 mt-3">
    <b-button @click="guardarConfiguracion('plan')" class="mr-2 mb-5 float-right" variant="success"><i class="fas fa-save"></i> Guardar</b-button>
</div>