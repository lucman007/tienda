<div class="col-lg-12">
    <h4>Email:</h4>
</div>
<div class="col-lg-6">
    <div class="form-group">
        <label>Remitente:</label>
        <input type="text" v-model="mail_send_from.remitente" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-12">
    <b-button @click="guardarConfiguracion('mail_send_from')" class="mr-2 mb-5 float-right" variant="success"><i class="fas fa-save"></i> Guardar</b-button>
</div>
<div class="col-lg-12">
    <h4>Datos de firma:</h4>
</div>
<div class="col-lg-4">
    <div class="form-group">
        <label>Email:</label>
        <input type="text" v-model="mail_contact.email" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-4">
    <div class="form-group">
        <label>Teléfono:</label>
        <input type="text" v-model="mail_contact.telefono" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-4">
    <div class="form-group">
        <label>Página web:</label>
        <input type="text" v-model="mail_contact.website" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-12">
    <b-button @click="guardarConfiguracion('mail_contact')" class="mr-2 mb-5 float-right" variant="success"><i class="fas fa-save"></i> Guardar</b-button>
</div>
<div class="col-lg-12">
    <h4>Notificaciones por email:</h4>
</div>
<div class="col-lg-4">
    <div class="form-group">
        <label>Caja:</label>
        <input type="text" v-model="mail_contact.notificacion_caja" autocomplete="nope" class="form-control">
    </div>
</div>
<div class="col-lg-12">
    <b-button @click="guardarConfiguracion('mail_contact')" class="mr-2 mb-5 float-right" variant="success"><i class="fas fa-save"></i> Guardar</b-button>
</div>