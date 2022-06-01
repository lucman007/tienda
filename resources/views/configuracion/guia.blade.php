<div class="col-lg-12">
    <h4>Datos predeterminados:</h4>
</div>
<div class="col-lg-3">
    <div class="form-group">
        <label>Tipo de transporte:</label>
        <select v-model="guia.tipo_transporte" class="custom-select">
            <option value="01">Público</option>
            <option value="02">Privado</option>
        </select>
    </div>
</div>
<div class="col-lg-4">
    <div class="form-group">
        <label>Dni/ruc:</label>
        <input type="text" v-model="guia.num_doc" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-4">
    <div class="form-group">
        <label>Placa:</label>
        <input type="text" v-model="guia.placa" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-12">
    <b-button @click="guardarConfiguracion('guia')" class="mr-2 mb-5 float-right" variant="success"><i class="fas fa-save"></i> Guardar</b-button>
</div>