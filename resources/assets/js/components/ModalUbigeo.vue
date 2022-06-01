<template>
    <div>
        <b-modal size="md" id="modal-ubigeo" ref="modal-ubigeo" @ok="agregarUbigeo" @show="obtenerUbigeo('departamentos')">
            <template slot="modal-title">
                Código de ubigeo
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
                    <div class="col-lg-12 mt-3">
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
        data() {
            return {
                lista_ubigeos: dataUbigeo,
                ubigeo: {
                    departamentos: [],
                    provincias: [],
                    distritos: [],
                    dep_seleccionado: '-1',
                    prov_seleccionado: '-1',
                    dist_seleccionado: '-1',
                    codigo: ''
                },
            }
        },
        methods: {
            obtenerUbigeo(tipo){

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
                this.$emit('agregar_ubigeo',this.ubigeo.codigo);
            }
        }
    }
</script>