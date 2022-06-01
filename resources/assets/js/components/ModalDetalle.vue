<template>
    <div>
        <b-modal id="modal-detalle" centered ref="modal-detalle" size="md" @show="getData" @hidden="limpiar">
            <template slot="modal-title">
                {{item.nombre}}
            </template>
            <div class="container">
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Precio</label>
                            <input v-model="precio" :disabled="!canEditPrecio" placeholder="0.00" type="number" class="form-control" onfocus="this.select()">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Cantidad</label>
                            <b-input-group>
                                <b-input-group-prepend>
                                    <b-button variant="primary" @click="cantidad--">-</b-button>
                                </b-input-group-prepend>
                                <input v-model="cantidad" type="number" class="form-control" onfocus="this.select()">
                                <b-input-group-append>
                                    <b-button variant="primary" @click="cantidad++">+</b-button>
                                </b-input-group-append>
                            </b-input-group>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Observación</label>
                            <input v-model="presentacion" placeholder="" type="text" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div v-for="item in errorDatos">
                            <p class="texto-error">{{ item }}</p>
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
        name: 'modal-detalle',
        props: ['item','canEditPrecio'],
        data() {
            return {
                error:0,
                errorDatos:[],
                cantidad:1,
                precio:1,
                presentacion:''
            }
        },
        methods: {
            getData(){
                this.cantidad = this.item.cantidad;
                this.precio = this.item.precio;
                this.presentacion = this.item.presentacion;
                console.log(this.canEditPrecio)
            },
            limpiar(){
                this.cantidad = 1;
                this.precio = 1;
                this.presentacion = '';
            },
            guardarDatos(){
                if (this.validar()) {
                    return;
                }
                this.item.cantidad = this.cantidad;
                this.item.precio = this.precio;
                this.item.presentacion = this.presentacion;
                this.$emit('actualizar');
                this.$refs['modal-detalle'].hide()
            },
            validar(){
                this.error = 0;
                this.errorDatos = [];
                if(this.item.cantidad<0) this.errorDatos.push('No se aceptan cantidades menores a 0');
                if(this.item.precio<0) this.errorDatos.push('No se aceptan precios menores a 0');
                if (this.errorDatos.length) this.error = 1;
                return this.error;
            }
        }
    }
</script>