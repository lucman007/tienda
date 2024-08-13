<template>
    <div>
        <b-modal id="modal-entrega" ref="modal-entrega" size="md" @show="obtenerDatos">
            <template slot="modal-title">
                Cliente
            </template>
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Nombre o alias</label>
                            <autocomplete-cliente-pedido v-on:agregar_cliente="agregarCliente"
                                                v-on:borrar_cliente="borrarCliente"
                                                ref="suggestCliente"></autocomplete-cliente-pedido>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Nota</label>
                            <textarea  v-model="datosEntrega.direccion" class="form-control" rows="3"/>
                        </div>
                    </div>
                </div>
            </div>
            <template #modal-footer="{ ok, cancel}">
                <b-button variant="secondary" @click="cancel()">Cancel</b-button>
                <b-button variant="primary" @click="guardarDatos">Guardar</b-button>
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
      datosEntrega: {
      },
    }
  },
  methods: {
    obtenerDatos() {
      axios.get('/pedidos/obtener-datos-entrega' + '/' + this.idpedido)
          .then(response => {
            this.datosEntrega = response.data;
            if (!response.data.idcontacto) {
              this.datosEntrega.idcontacto = null;
            }
            let obj = {idcliente:this.datosEntrega.idcontacto,nombre:this.datosEntrega.contacto};
            this.$refs['suggestCliente'].setCliente(obj);
          })
          .catch(error => {
            alert('Ha ocurrido un error.');
            console.log(error);
          });
    },
    agregarCliente(obj) {
      this.datosEntrega.idcontacto = obj['idcliente'];
      this.datosEntrega.contacto = obj['nombre'];
    },
    borrarCliente() {

    },
    guardarDatos() {
      axios.post('/pedidos/guardar-datos-entrega', {
        'idpedido': this.idpedido,
        'datos_entrega': JSON.stringify(this.datosEntrega)
      })
          .then(response => {
            if (!response.data) {
              alert('No se ha podido guardar los datos de entrega. Intenta nuevamente');
            }
            this.$emit('delivery');
            this.$emit('send');
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