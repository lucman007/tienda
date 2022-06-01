@extends('layouts.main')
@section('titulo', 'Proveedores')
@section('contenido')
    <div class="{{json_decode(cache('config')['interfaz'], true)['layout']?'container-fluid':'container'}}">
        <div class="row">
            <div class="col-lg-9">
                <h3 class="titulo-admin-1">Proveedores</h3>
                <b-button @click="abrirModalProveedor" variant="primary"><i class="fas fa-plus"></i> Nuevo proveedor</b-button>
            </div>
            <div class="col-lg-3">
                @include('proveedores.buscador')
            </div>
        </div>
        @if($textoBuscado!='')
        <div class="row">
            <div class="col-lg-12 mt-5">
                <div class="alert alert-dark" role="alert"><h5 class="mb-0">Resultados de búsqueda para: {{$textoBuscado}}
                        <a href="{{url('/proveedores')}}"><i class="fa fa-times float-right"></i></a></h5></div>
            </div>
        </div>
        @endif
        <div class="row">
            <div class="col-sm-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        Lista de proveedores
                    </div>
                    <div class="card-body">
                        <div class="table-responsive tabla-gestionar">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="bg-custom-green">
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">Codigo</th>
                                    <th scope="col">Nombre</th>
                                    <th scope="col">Contacto</th>
                                    <th scope="col">Telefono 1</th>
                                    <th scope="col">Telefono 2</th>
                                    <th scope="col">Correo</th>
                                    <th scope="col">Web</th>
                                    <th scope="col">Opciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($proveedores as $proveedor)
                                    <tr>
                                        <td></td>
                                        <td style="display:none">{{$proveedor->idproveedor}}</td>
                                        <td>{{$proveedor->codigo}}</td>
                                        <td>{{$proveedor->nombre}}</td>
                                        <td>{{$proveedor->contacto}}</td>
                                        <td>{{$proveedor->telefono}}</td>
                                        <td>{{$proveedor->telefono_2}}</td>
                                        <td>{{$proveedor->correo}}</td>
                                        <td>{{$proveedor->web}}</td>
                                        <td class="botones-accion">
                                            <a @@click="editarProveedor({{$proveedor->idproveedor}})" href="javascript:void(0)">
                                                <button class="btn btn-success" title="Editar proveedor"><i
                                                            class="fas fa-edit"></i></button>
                                            </a>
                                            <a @@click="borrarProveedor({{$proveedor->idproveedor}})" href="javascript:void(0)">
                                                <button class="btn btn-danger" title="Eliminar"><i class="fas fa-trash-alt"></i>
                                                </button>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{$proveedores->links('layouts.paginacion')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--INICIO MODAL -->
    <b-modal id="modal-1" ref="modal-nuevo-proveedor" size="lg"
              @@ok="agregarProveedor" @@hidden="resetModal">
<template slot="modal-title">
    @{{tituloModal}}
</template>
<div class="container">
    <div class="card" style="box-shadow:none; margin-bottom:20px" v-show="accion=='insertar'">
        <div class="card-header">
            Consulta RUC / DNI
        </div>
        <div class="card-body row">
            <div class="col-lg-3">
                <label>Documento:</label>
                <select v-model="tipo_documento_buscar" class="custom-select">
                    <option value="6">RUC</option>
                    <option value="1">DNI</option>
                </select>
            </div>
            <div class="col-lg-5 form-group">
                <label>@{{tipo_documento_buscar==6?'Número de RUC:':'Número de DNI:'}}</label>
                <input v-on:keyup="shortcut_buscar" autocomplete="off" type="number" v-model="ruc_buscar" class="form-control" :maxlength="tipo_documento_buscar==6?11:8">
                <b-button @click="buscar_en_sunat" variant="primary" class="boton_adjunto">
                    <span v-show="!spinnerRuc"><i class="fas fa-search"></i></span>
                    <b-spinner v-show="spinnerRuc" small label="Loading..." ></b-spinner>
                </b-button>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <label for="nombre">*Nombre / Razón social:</label>
                <input id="nombre" autocomplete="off" type="text" v-model="nombre" class="form-control">
            </div>
        </div>
        <div class="col-lg-3">
            <div class="form-group">
                <label>Tipo de documento:</label>
                <select v-model="tipo_documento" class="custom-select">
                    <option value="6">RUC</option>
                    <option value="1">DNI</option>
                    <option value="0">NIF</option>
                    <option value="4">Carnet de extrajería</option>
                    <option value="7">Pasaporte</option>
                </select>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="form-group">
                <label>*N° documento:</label>
                <input autocomplete="off" type="text" v-model="num_documento" class="form-control" :maxlength="max_num">
            </div>
        </div>
        <div class="col-lg-6">
            <div class="form-group">
                <label for="direccion">*Dirección:</label>
                <input autocomplete="off" type="text"  v-model="direccion" class="form-control">
            </div>
        </div>
        <div class="col-lg-3">
            <div class="form-group">
                <label for="telefono">Teléfono 1:</label>
                <input autocomplete="off" type="text" v-model="telefono" class="form-control">
            </div>
        </div>
        <div class="col-lg-3">
            <div class="form-group">
                <label for="telefono_2">Teléfono 2:</label>
                <input autocomplete="off" type="text" v-model="telefono_2" class="form-control">
            </div>
        </div>
        <div class="col-lg-6">
            <div class="form-group">
                <label for="correo">Correo:</label>
                <input autocomplete="off" type="text"  v-model="correo" class="form-control">
            </div>
        </div>
        <div class="col-lg-6">
            <div class="form-group">
                <label for="web">Sitio web:</label>
                <input autocomplete="off" type="text" v-model="web" class="form-control">
            </div>
        </div>
        <div class="col-lg-6">
            <div class="form-group">
                <label for="contacto">Persona de contacto:</label>
                <input autocomplete="off" type="text" v-model="contacto" class="form-control">
            </div>
        </div>
        <div class="col-lg-6">
            <div v-for="error in errorDatosProveedor">
                <p class="texto-error">@{{ error }}</p>
            </div>
        </div>
    </div>
</div>
</b-modal>

@endsection
@section('script')
    <script>
        let app = new Vue({
            el: '.app',
            data: {
                errorDatosProveedor: [],
                errorProveedor: 0,
                tituloModal:'Agregar proveedor',
                accion:'insertar',
                idproveedor: -1,
                codigo: '',
                nombre: '',
                num_documento:'',
                direccion: '',
                telefono:'',
                telefono_2:'',
                correo: '',
                web:'',
                contacto:'',
                observaciones:'',
                ultimo_id:<?php echo $ultimo_id; ?>,
                ruc_buscar:"",
                tipo_doc:'ruc',
                spinnerRuc:false,
                tipo_documento_buscar:6,
                max_num:8,
                tipo_documento:'1',
            },
            methods: {
                agregarProveedor(e){
                    if (this.validarProveedor()) {
                        e.preventDefault();
                        return;
                    }

                    let dataset={
                        'idproveedor': this.idproveedor,
                        'codigo': this.codigo,
                        'nombre': this.nombre,
                        'num_documento': this.num_documento,
                        'direccion': this.direccion,
                        'telefono': this.telefono,
                        'telefono_2': this.telefono_2,
                        'correo': this.correo,
                        'web': this.web,
                        'contacto': this.contacto,
                        'observaciones': this.observaciones
                    };

                    let tipo_accion=this.accion=='insertar'?'{{action('ProveedorController@store')}}':'{{action('ProveedorController@update')}}';

                    axios.post(tipo_accion, dataset)
                        .then(function () {
                            window.location.href = "/proveedores"
                        })
                        .catch(function (error) {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });

                },
                editarProveedor(id){
                    this.tituloModal='Editar proveedor';
                    this.accion='editar';
                    this.idproveedor=id;
                    axios.get('{{url('/proveedores/edit')}}' + '/' + id)
                        .then(response => {
                            let datos = response.data;
                            this.codigo= datos.codigo;
                            this.nombre= datos.nombre;
                            this.num_documento=datos.num_documento;
                            this.direccion= datos.direccion;
                            this.telefono=datos.telefono;
                            this.telefono_2=datos.telefono_2;
                            this.correo= datos.correo;
                            this.web=datos.web;
                            this.contacto=datos.contacto;
                            this.observaciones=datos.observaciones;
                            this.$refs['modal-nuevo-proveedor'].show();
                        })
                        .catch(function (error) {
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });

                },
                borrarProveedor(id){
                    if(confirm('Realmente desea eliminar el proveedor')){
                        axios.delete('{{url('/proveedores/destroy')}}' + '/' + id)
                            .then(function () {
                                window.location.href = "/proveedores"
                            })
                            .catch(function (error) {
                                console.log(error);
                            });
                    }
                },
                validarProveedor(){
                    this.errorProveedor = 0;
                    this.errorDatosProveedor = [];
                    if (this.nombre.length==0) this.errorDatosProveedor.push('*Nombre de proveedor no puede estar vacio');
                    if(isNaN(this.telefono)) this.errorDatosProveedor.push('*El campo telefono debe contener un número');
                    if(isNaN(this.telefono_2)) this.errorDatosProveedor.push('*El campo telefono 2 debe contener un número');
                    if(isNaN(this.num_documento)) this.errorDatosProveedor.push('*El campo RUC debe contener un número');
                    if(this.num_documento!=null){
                        if(this.num_documento.length>11) this.errorDatosProveedor.push('*El campo RUC ha excedido la cantidad de dígitos');
                    }
                    if (this.errorDatosProveedor.length) this.errorProveedor = 1;
                    return this.errorProveedor;
                },
                generarCodigo(){
                    let obj= this.ultimo_id;
                    let year=(new Date().getYear()-100).toString();
                    this.codigo='PRV'+year+obj['idproveedor'];
                },
                abrirModalProveedor(){
                    this.generarCodigo();
                    this.$refs['modal-nuevo-proveedor'].show();
                },
                resetModal(){
                    this.errorDatosProveedor=[];
                    this.errorProveedor= 0;
                    this.tituloModal='Agregar proveedor';
                    this.accion='insertar';
                    this.idproveedor= -1;
                    this.codigo= '';
                    this.nombre= '';
                    this.num_documento='';
                    this.direccion= '';
                    this.telefono='';
                    this.telefono_2='';
                    this.correo= '';
                    this.web='';
                    this.contacto='';
                    this.observaciones='';
                    this.ruc_buscar="";
                    this.tipo_doc='ruc';
                    this.spinnerRuc=false;
                    this.tipo_documento_buscar=6;
                    this.max_num=8;
                    this.tipo_documento='1';
                },
                shortcut_buscar(event){
                    switch (event.code){
                        case 'Enter':
                        case 'NumpadEnter':
                            this.buscar_en_sunat();
                            break;
                    }
                },
                buscar_en_sunat(){
                    if((this.tipo_documento_buscar==6 && this.ruc_buscar.length != 11) || (this.tipo_documento_buscar==6 && isNaN(this.ruc_buscar))){
                        alert('Ingresa un RUC válido');
                        return;
                    }
                    if((this.tipo_documento_buscar==1 && this.ruc_buscar.length != 8) || (this.tipo_documento_buscar==1 && isNaN(this.ruc_buscar))){
                        alert('Ingresa un DNI válido');
                        return;
                    }
                    this.spinnerRuc=true;
                    axios.post('/helper/buscar-ruc', {
                        'num_doc': this.ruc_buscar,
                        'tipo_doc': this.tipo_documento_buscar,
                    })
                        .then(response => {
                            let data=response.data;
                            if(!data || data.length == 0){
                                alert('No se obtuvieron resultados, ingresar manualmente.');
                                let input = document.getElementById("nombre");
                                input.focus();
                            } else {
                                this.num_documento=data.ruc;
                                this.nombre=data.nombre_o_razon_social;
                                this.tipo_documento=this.tipo_documento_buscar;
                                this.direccion=data.direccion;

                            }
                            this.spinnerRuc=false;
                        })
                        .catch(error => {
                            this.spinnerRuc=false;
                            alert('Ha ocurrido un error.');
                            console.log(error);
                        });
                },
            },
            watch:{
                tipo_documento(val){
                    switch(val){
                        case '1':
                            this.max_num = 8;
                            break;
                        case '4':
                            this.max_num = 12;
                            break;
                        case '6':
                            this.max_num = 11;
                            break;
                        case '7':
                            this.max_num = 12;
                            break;
                        default:
                            this.max_num = 30;
                    }
                }
            }

        });
    </script>
@endsection