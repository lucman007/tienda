<template>
    <div>
        <b-modal size="md" id="modal-ubigeo" ref="modal-ubigeo" @ok="agregarUbigeo" @hidden="reset()" @show="obtenerUbigeo('departamentos')">
            <template slot="modal-title">
                {{titulo}}
            </template>
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <label>Departamento</label>
                        <select v-model="ubigeo.dep_seleccionado" @change="obtenerUbigeo('provincias')" class="custom-select">
                            <option value="-1">Ninguno</option>
                            <option v-for="d in ubigeo.departamentos" :value="d.departamento">{{d.nombre}}</option>
                        </select>
                    </div>
                    <div class="col-lg-12 mt-3">
                        <label>Provincia</label>
                        <select :disabled="ubigeo.dep_seleccionado==='-1'" v-model="ubigeo.prov_seleccionado"
                                @change="obtenerUbigeo('distritos')" class="custom-select">
                            <option value="-1">Ninguno</option>
                            <option v-for="d in ubigeo.provincias" :value="d.provincia">{{d.nombre}}</option>
                        </select>
                    </div>
                    <div class="col-lg-12 mt-3">
                        <label>Distrito</label>
                        <select :disabled="ubigeo.prov_seleccionado==='-1'" v-model="ubigeo.dist_seleccionado"
                                @change="setUbigeo" class="custom-select">
                            <option value="-1">Ninguno</option>
                            <option v-for="d in ubigeo.distritos" :value="d.distrito">{{d.nombre}}</option>
                        </select>
                    </div>
                    <div v-if="mostrarDireccion" class="col-lg-12 mt-3">
                        <label>Direccion:</label>
                        <input v-model="ubigeo.direccion" type="text" class="form-control">
                    </div>
                    <div class="col-lg-12 mt-3" v-else>
                        <label>Ubigeo:</label>
                        <h3>{{ ubigeo.codigo }}</h3>
                    </div>
                </div>
            </div>
        </b-modal>
    </div>
</template>
<script>
    import dataUbigeo from '../ubigeo.json';
    export default {
        name:'modal-ubigeo',
        props:['es_ubigeo'],
        data() {
            return {
                lista_ubigeos: dataUbigeo,
                titulo:'Código de ubigeo',
                mostrarDireccion:false,
                ubigeo: {
                    departamentos: [],
                    provincias: [],
                    distritos: [],
                    dep_seleccionado: '-1',
                    prov_seleccionado: '-1',
                    dist_seleccionado: '-1',
                    codigo: '',
                    direccion:''
                },
            }
        },
        methods: {
            obtenerUbigeo(tipo){
                if(!this.es_ubigeo){
                    this.titulo = 'Direccion de partida';
                    this.mostrarDireccion = true;
                } else {
                    this.titulo = 'Código de ubigeo';
                    this.mostrarDireccion = false;
                }
                switch (tipo) {
                    case 'departamentos':
                        this.ubigeo.departamentos = [];
                        this.ubigeo.dep_seleccionado = '-1';
                        this.ubigeo.prov_seleccionado = '-1';
                        this.ubigeo.dist_seleccionado = '-1';
                        this.ubigeo.codigo = '';
                        break;
                    case 'provincias':
                        this.ubigeo.provincias = [];
                        this.ubigeo.prov_seleccionado = '-1';
                        this.ubigeo.dist_seleccionado = '-1';
                        this.ubigeo.codigo = '';
                        break;
                    case 'distritos':
                        this.ubigeo.distritos = [];
                        this.ubigeo.dist_seleccionado = '-1';
                        this.ubigeo.codigo = '';
                        break;
                }

                for (let ubigeo of this.lista_ubigeos) {
                    switch (tipo) {
                        case 'departamentos':
                            if (ubigeo.provincia === '00' && ubigeo.distrito === '00') {
                                this.ubigeo.departamentos.push(ubigeo);
                            }
                            break;
                        case 'provincias':
                            if (ubigeo.departamento === this.ubigeo.dep_seleccionado && ubigeo.provincia !== '00' && ubigeo.distrito === '00') {
                                this.ubigeo.provincias.push(ubigeo);
                            }
                            break;
                        case 'distritos':
                            if (ubigeo.departamento === this.ubigeo.dep_seleccionado && ubigeo.provincia === this.ubigeo.prov_seleccionado && ubigeo.distrito !== '00') {
                                this.ubigeo.distritos.push(ubigeo);
                            }
                            break;
                    }

                }
            },
            setUbigeo(){
                if (this.ubigeo.dist_seleccionado === '-1') {
                    this.ubigeo.codigo = '';
                } else {
                    this.ubigeo.codigo = this.ubigeo.dep_seleccionado + this.ubigeo.prov_seleccionado + this.ubigeo.dist_seleccionado;
                }
            },
            agregarUbigeo(){
                if(this.es_ubigeo){
                    this.$emit('agregar_ubigeo',this.ubigeo.codigo);
                } else {
                    if(this.ubigeo.codigo !== ''){
                        let direccion = '';
                        let dep = this.ubigeo.departamentos.find(
                            (d) => d.departamento === this.ubigeo.dep_seleccionado
                        );
                        let prov = this.ubigeo.provincias.find(
                            (d) => d.provincia === this.ubigeo.prov_seleccionado
                        );
                        let dist = this.ubigeo.distritos.find(
                            (d) => d.distrito === this.ubigeo.dist_seleccionado
                        );
                        direccion = this.ubigeo.direccion + ' ' + dep.nombre + ' ' + prov.nombre + ' ' + dist.nombre;
                        this.$emit('cambiar_direccion',{direccion:direccion.toUpperCase(),ubigeo:this.ubigeo.codigo});
                    }
                }

            },
            reset(){
                this.titulo='Código de ubigeo';
                this.mostrarDireccion=false;
                this.ubigeo.direccion='';
            }
        }
    }
</script>