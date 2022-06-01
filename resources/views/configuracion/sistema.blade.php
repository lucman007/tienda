<div class="col-lg-12 mb-4">
    <h4>Conexion SUNAT</h4>
</div>
<div class="col-lg-12 mb-4">
    <h4>Prueba</h4>
    <b-button @click="imprimir()" class="mr-2" variant="primary"><i class="fas fa-power-off"></i> Prueba</b-button>
</div>
<div class="col-lg-4">
    <div class="form-group">
        <label for="dir">Usuario secundario</label>
        <input placeholder="Ejm: BCP: 00045465465" type="text"  v-model="conexion.usuario" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-4">
    <div class="form-group">
        <label for="dir">Contraseña:</label>
        <input placeholder="Ejm: 0000454545" type="text"  v-model="conexion.clave" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-4 mt-3">
    <b-form-checkbox v-model="conexion.esProduccion" switch size="lg">
        <p style="font-size: 1rem;">Modo producción</p>
    </b-form-checkbox>
</div>
<div class="col-lg-12">
    <b-button @click="guardarConfiguracion('conexion')" class="mr-2 mb-5 float-right" variant="success"><i class="fas fa-save"></i> Guardar</b-button>
</div>
<div class="col-lg-12 mb-4">
    <h4>Certificado digital</h4>
    <div class="alert alert-primary" style="display: flow-root;">
        <b-button class="mr-2" variant="primary"><i class="fas fa-plus"></i> Agregar</b-button>
    </div>
</div>
<div class="col-lg-12 mb-4">
    <h4>Logs</h4>
    <b-button target="_blank" href="/logs" class="mr-2" variant="primary"><i class="fas fa-bug"></i> Ver logs</b-button>
</div>
<div class="col-lg-12 mb-4">
    <h4>acciones</h4>
    <div class="row">
        <div class="col-lg-2">
            <b-button onclick="return confirm('Confirma esta acción')" href="/configuracion/reiniciar-vistas" class="mr-2" variant="success"><i class="fas fa-power-off"></i> Reiniciar vistas</b-button>
        </div>
        <div class="col-lg-2">
            <b-button onclick="return confirm('Confirma esta acción')" href="/configuracion/cerrar-sesiones" class="mr-2" variant="danger"><i class="fas fa-power-off"></i> Cerrar sesiones</b-button>
        </div>
    </div>
</div>
