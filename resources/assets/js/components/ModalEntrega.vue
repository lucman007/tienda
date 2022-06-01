<template>
    <div>
        <b-modal id="modal-entrega" ref="modal-entrega" size="md" @show="obtenerDatos">
            <template slot="modal-title">
                Datos de entrega
            </template>
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input v-model="datosEntrega.contacto" placeholder="Nombre" type="text" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Dirección</label>
                            <input v-model="datosEntrega.direccion" placeholder="Dirección" type="text" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Referencia</label>
                            <input v-model="datosEntrega.referencia" placeholder="Referencia" type="text" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input v-model="datosEntrega.telefono" placeholder="Teléfono" type="text" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="form-group">
                            <label>Costo</label>
                            <input v-model="datosEntrega.costo" placeholder="Costo" type="number" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
            <template #modal-footer="{ ok, cancel}">
                <b-button variant="secondary" @click="cancel()">Cancel</b-button>
                <b-button variant="primary" @click="guardarDatos">OK</b-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
    export default {
        name: 'modal-entrega',
        props: ['idpedido'],
        data() {
            return {
                datosEntrega:{
                    contacto:'',
                    telefono:'',
                    direccion:'',
                    referencia:'',
                    costo:''
                },
            }
        },
        methods: {
            obtenerDatos(){
                axios.get('/pedidos/obtener-datos-entrega'+'/'+this.idpedido)
                    .then(response => {
                        this.datosEntrega = response.data;
                    })
                    .catch(error => {
                        alert('Ha ocurrido un error.');
                        console.log(error);
                    });
            },
            guardarDatos(){
                axios.post('/pedidos/guardar-datos-entrega', {
                    'idpedido': this.idpedido,
                    'datos_entrega':JSON.stringify(this.datosEntrega)
                })
                    .then(response => {
                         if(!response.data){
                             alert('No se ha podido guardar los datos de entrega. Intenta nuevamente');
                         }
                         this.$emit('delivery');
                         this.$refs['modal-entrega'].hide();
                    })
                    .catch(error => {
                        alert('Ha ocurrido un error.');
                        console.log(error);
                    });
            },
        }
    }
</script>