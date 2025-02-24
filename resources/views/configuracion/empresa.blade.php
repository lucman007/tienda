<div class="col-lg-12">
    <h4>Información tributaria</h4>
</div>
<div class="col-lg-6">
    <div class="form-group">
        <label for="rsocial">Razón social:</label>
        <input type="text" v-model="emisor.razon_social" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-4">
    <div class="form-group">
        <label for="rsocial">Nombre comercial:</label>
        <input placeholder="Colocar sólo si se ha registrado en SUNAT" type="text" v-model="emisor.nombre_comercial" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-2">
    <div class="form-group">
        <label for="rsocial">Código establecimiento:</label>
        <input placeholder="En caso de ser sucursal" type="text" v-model="emisor.codigo_establecimiento" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-3">
    <div class="form-group">
        <label for="ruc">RUC:</label>
        <input maxlength="11" type="text" v-model="emisor.ruc" autocomplete="nope"  class="form-control">
    </div>
</div>
<div class="col-lg-6">
    <div class="form-group">
        <label for="dir">Dirección:</label>
        <input placeholder="Mz B lote 15" type="text"  v-model="emisor.direccion" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-3">
    <div class="form-group">
        <label for="dir">Urbanización:</label>
        <input placeholder="El Trebol" type="text"  v-model="emisor.urbanizacion" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-3">
    <div class="form-group">
        <label for="dir">Departamento:</label>
        <input placeholder="Piura" type="text"  v-model="emisor.departamento" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-3">
    <div class="form-group">
        <label for="dir">Provincia:</label>
        <input placeholder="Piura" type="text"  v-model="emisor.provincia" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-3">
    <div class="form-group">
        <label for="dir">Distrito:</label>
        <input placeholder="Piura" type="text"  v-model="emisor.distrito" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-3">
    <div class="form-group">
        <label for="dir">Ubigeo:</label>
        <input type="text"  v-model="emisor.ubigeo" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-8">
    <div class="form-group">
        <label for="dir">Dirección simple:</label>
        <input placeholder="Dirección sin referencia" type="text"  v-model="emisor.direccion_resumida" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-2">
    <div class="form-group">
        <label for="dir">Teléfono 1:</label>
        <input type="text"  v-model="emisor.telefono_1" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-2">
    <div class="form-group">
        <label for="dir">Teléfono 2:</label>
        <input type="text"  v-model="emisor.telefono_2" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-4">
    <div class="form-group">
        <label for="dir">Email:</label>
        <input type="text"  v-model="emisor.email" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-3">
    <div class="form-group">
        <label for="dir">Nombre publicitario:</label>
        <input type="text"  v-model="emisor.nombre_publicitario" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-5">
    <div class="form-group">
        <label for="dir">Texto publicitario:</label>
        <input type="text"  v-model="emisor.texto_publicitario" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-4">
    <div class="form-group">
        <label for="dir">Cuenta comercial 1:</label>
        <input placeholder="Ejm: BBVA: 644454554" type="text"  v-model="emisor.cuenta_1" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-4">
    <div class="form-group">
        <label for="dir">Cuenta comercial 2:</label>
        <input placeholder="Ejm: BCP: 00045465465" type="text"  v-model="emisor.cuenta_2" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-4">
    <div class="form-group">
        <label for="dir">Cuenta detracciones:</label>
        <input placeholder="Ejm: 0000454545" type="text"  v-model="emisor.cuenta_detracciones" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-12">
    <b-button @click="guardarConfiguracion('emisor')" class="mr-2 mb-5 float-right" variant="success"><i class="fas fa-save"></i> Guardar</b-button>
</div>
<div class="col-lg-12">
    <h4>Información de bancos y cuentas</h4>
</div>
<div class="col-lg-12">
    <b-button v-b-modal.modal-cuentas class="mr-2 mb-5" variant="primary"><i class="fas fa-edit"></i> Editar cuentas</b-button>
</div>