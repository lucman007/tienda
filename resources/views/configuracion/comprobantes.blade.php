<div class="col-lg-12">
    <h4>Impresión:</h4>
</div>
<div class="col-lg-3">
    <div class="form-group">
        <label>Formato comprobantes:</label>
        <select v-model="impresion.formato" class="custom-select">
            <option value="80_1">Ticket 80mm</option>
            <option value="55_1">Ticket 55mm</option>
            <option value="A4_1">A4 - modelo 1</option>
            <option value="A4_2">A4 - modelo 2</option>
            <option value="A4_3">A4 - modelo 3</option>
            <option value="A4_4">A4 - modelo 4</option>
            <option value="A4_5">A4 - modelo 5</option>
            <option value="A4_6">A4 - modelo 6</option>
            <option value="A4_7">A4 - modelo 7</option>
        </select>
    </div>
</div>
<div class="col-lg-2" style="margin-top: 18px">
    <div class="form-group">
        <b-button :href="'/configuracion/mostrar-plantilla/'+impresion.formato" target="_blank" variant="primary"><i class="fas fa-eye"></i> Ver plantilla</b-button>
    </div>
</div>
<div class="col-lg-3 mt-3">
    <b-form-checkbox v-model="impresion.mostrar_mozo" switch size="lg">
        <p style="font-size: 1rem;">Mostrar vendedor</p>
    </b-form-checkbox>
</div>
<div class="col-lg-3 mt-3">
    <b-form-checkbox v-model="impresion.ocultar_razon_social" switch size="lg">
        <p style="font-size: 1rem;">Ocultar Razon Social</p>
    </b-form-checkbox>
</div>
<div class="col-lg-3 mt-3">
    <b-form-checkbox v-model="impresion.mostrar_logo_ticket" switch size="lg">
        <p style="font-size: 1rem;">Mostrar logo en tickets</p>
    </b-form-checkbox>
</div>
<div class="col-lg-12">
    <b-button @click="guardarConfiguracion('impresion')" class="mr-2 mb-5 float-right" variant="success"><i class="fas fa-save"></i> Guardar</b-button>
</div>
<div class="col-lg-12">
    <h4>Series:</h4>
</div>
<div class="col-lg-2">
    <div class="form-group">
        <label>Factura:</label>
        <input type="text" v-model="series.factura" autocomplete="nope" maxlength="4" class="form-control">
    </div>
</div>
<div class="col-lg-2">
    <div class="form-group">
        <label>Boleta:</label>
        <input type="text" v-model="series.boleta" autocomplete="nope" maxlength="4" class="form-control">
    </div>
</div>
<div class="col-lg-2">
    <div class="form-group">
        <label>NC (Boletas):</label>
        <input type="text" v-model="series.nc_boletas" autocomplete="nope" maxlength="4" class="form-control">
    </div>
</div>
<div class="col-lg-2">
    <div class="form-group">
        <label>NC (Facturas):</label>
        <input type="text" v-model="series.nc_facturas" autocomplete="nope" maxlength="4" class="form-control">
    </div>
</div>
<div class="col-lg-2">
    <div class="form-group">
        <label>ND (Boletas):</label>
        <input type="text" v-model="series.nd_boletas" autocomplete="nope" maxlength="4" class="form-control">
    </div>
</div>
<div class="col-lg-2">
    <div class="form-group">
        <label>ND (Facturas):</label>
        <input type="text" v-model="series.nd_facturas" autocomplete="nope" maxlength="4" class="form-control">
    </div>
</div>
<div class="col-lg-2">
    <div class="form-group">
        <label>Guía de remisión:</label>
        <input type="text" v-model="series.guia_remision" autocomplete="nope" maxlength="4" class="form-control">
    </div>
</div>
<div class="col-lg-2">
    <div class="form-group">
        <label>Recibo:</label>
        <input type="text" v-model="series.recibo" autocomplete="nope" maxlength="4" class="form-control">
    </div>
</div>
<div class="col-lg-12">
    <b-button @click="guardarConfiguracion('series')" class="mr-2 mb-5 float-right" variant="success"><i class="fas fa-save"></i> Guardar</b-button>
</div>