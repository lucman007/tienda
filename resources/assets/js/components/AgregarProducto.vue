<template>
    <div>
        <b-modal id="modal-nuevo-producto" ref="modal-nuevo-producto" size="lg"
                 title="" @ok="agregarProducto" @hidden="resetModal" @shown="init">
            <template slot="modal-title">
                {{tituloModal}}
            </template>
            <div>
                <b-card no-body class="no-shadow">
                    <b-tabs card>
                        <b-tab title="General" active>
                            <div class="container">
                                <div class="row">
                                    <div v-show="tipo_producto == 3" class="col-lg-12">
                                        <p style="color: green">Ahora puedes armar un conjunto de productos y establecerle un precio. Cada producto se descontará del inventario individualmente. Agrega los productos al kit desde la pestaña <strong>AGREGAR PRODUCTOS</strong>.</p>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>Tipo:</label>
                                            <select v-model="tipo_producto" class="custom-select" :disabled="tipo_producto == 3">
                                                <option value="1">Producto</option>
                                                <option value="2">Servicio</option>
                                                <option value="3" v-show="tipo_producto == 3">Kit de productos</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div v-if="accion=='editar'" class="col-lg-3">
                                        <div class="form-group">
                                            <label>Código Producto:</label>
                                            <input autocomplete="off" type="text" v-model="cod_producto" name="cod_producto" class="form-control">
                                        </div>
                                    </div>
                                    <div :class="[accion=='insertar'?'col-lg-9':'col-lg-6']">
                                        <div class="form-group">
                                            <label v-show="tipo_producto != 3">Nombre del producto o servicio:</label>
                                            <label v-show="tipo_producto == 3">Nombre del kit de productos:</label>
                                            <input type="text" v-model="nombre" name="nombre"  class="form-control" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>Características:</label>
                                            <textarea v-model="presentacion" class="form-control" name="presentacion" id="" cols="30"
                                                      rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>Categoría:</label>
                                            <select v-model="idcategoria" name="idcategoria" class="custom-select" id="selectPlantilla">
                                                <option v-for="categoria in categorias" v-bind:value="categoria.idcategoria">{{categoria.nombre}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label>Unidad de medida:</label>
                                            <select v-model="medida" class="custom-select">
                                                <option v-for="unidad in unidad_medida" v-bind:value="unidad['text_val']">{{unidad['label']}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div v-if="can_gestionar && origen != 'requerimientos'" v-show="tipo_producto==1" class="col-lg-3">
                                        <div class="form-group">
                                            <label>Cantidad:</label>
                                            <input onfocus="this.select()" autocomplete="off" type="number" v-model="cantidad" name="cantidad" class="form-control">
                                        </div>
                                    </div>
                                    <div v-if="can_gestionar && origen != 'requerimientos'" v-show="tipo_producto==1" class="col-lg-3">
                                        <div class="form-group">
                                            <label>Stock mínimo:</label>
                                            <input onfocus="this.select()" autocomplete="off" type="number" v-model="stock_bajo" name="stock_bajo" class="form-control">
                                        </div>
                                    </div>
                                    <div v-if="can_gestionar" class="col-lg-4">
                                        <label>Precio de venta:</label>
                                        <b-input-group>
                                            <b-form-input onfocus="this.select()" type="number" v-model="precio"></b-form-input>
                                            <template #append>
                                                <b-dropdown :text="moneda" variant="secondary">
                                                    <b-dropdown-item @click="moneda = 'PEN'">PEN</b-dropdown-item>
                                                    <b-dropdown-item @click="moneda = 'USD'">USD</b-dropdown-item>
                                                </b-dropdown>
                                            </template>
                                        </b-input-group>
                                    </div>
                                    <div v-if="can_gestionar" class="col-lg-5" v-show="accion=='editar' && tipo_producto=='1'">
                                        <label>Código de barras:</label>
                                        <div class="form-group">
                                            <a :href="'data:image/png;base64,'+barcode" download>
                                                <img :src="'data:image/png;base64,'+barcode" alt="barcode">
                                            </a>
                                            <b-button variant="success" :href="'data:image/png;base64,'+barcode" download><i class="fas fa-download"></i></b-button>
                                        </div>
                                    </div>
                                    <div v-if="can_gestionar  && origen != 'requerimientos'" class="col-lg-12 mt-3">
                                        <p><strong>Data para reportes</strong></p>
                                    </div>
                                    <div v-if="can_gestionar  && origen != 'requerimientos'" class="col-lg-4">
                                        <label>{{tipo_producto==1?'Precio de compra':'Costo de producción'}}:</label>
                                        <b-input-group>
                                            <b-form-input onfocus="this.select()" type="number" v-model="costo"></b-form-input>
                                            <template #append>
                                                <b-dropdown :text="moneda_compra" variant="secondary">
                                                    <b-dropdown-item @click="moneda_compra = 'PEN'">PEN</b-dropdown-item>
                                                    <b-dropdown-item @click="moneda_compra = 'USD'">USD</b-dropdown-item>
                                                </b-dropdown>
                                            </template>
                                        </b-input-group>
                                    </div>
                                    <div v-if="can_gestionar && origen != 'requerimientos'" class="col-lg-3" v-show="moneda_compra == 'USD'">
                                        <label>Tipo de cambio</label>
                                        <b-input-group prepend="S/">
                                            <b-form-input onfocus="this.select()" type="number" v-model="tipo_cambio_compra"></b-form-input>
                                        </b-input-group>
                                    </div>
                                    <div v-if="can_gestionar  && origen != 'requerimientos'" v-show="accion == 'editar' && cantidad != cantidad_aux && tipo_producto==1" class="col-lg-12 mt-2">
                                        <div class="form-group">
                                            <label>Indica la razón de haber cambiado manualmente la cantidad:</label>
                                            <input v-model="observacion" class="form-control" type="text">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </b-tab>
                        <b-tab v-if="tipo_producto == 3" title="Agregar productos">
                            <div class="container">
                                <div class="row">
                                    <div class="col-lg-12" v-show="tipo_producto == 3">
                                        <div class="form-group">
                                            <autocomplete v-on:agregar_producto="agregarAlKit" :buscador-alt="true"></autocomplete>
                                        </div>
                                    </div>
                                    <div class="col-lg-12" v-show="tipo_producto == 3">
                                        <table class="table table-striped table-hover table-sm">
                                            <thead class="bg-custom-green">
                                            <tr>
                                                <th scope="col" style="width: 10px"></th>
                                                <th scope="col" style="width: 290px">Producto</th>
                                                <th scope="col" style="width: 100px">Cantidad</th>
                                                <th scope="col" style="width: 50px"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="(producto,index) in items_kit" :key="index">
                                                <td></td>
                                                <td>
                                                    {{producto.nombre}}
                                                </td>
                                                <td><input onfocus="this.select()"
                                                           class="form-control" type="number"
                                                           v-model="producto.cantidad">
                                                </td>
                                                <td class="">
                                                    <button @click="borrarItemKit(index)" class="btn btn-danger"
                                                            title="Borrar item"><i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr class="text-center" v-show="items_kit.length == 0"><td colspan="8">Agrega productos predeterminados al kit</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </b-tab>
                        <b-tab :disabled="tipo_producto!=1"  title="Descuentos">
                            <div class="container">
                                <div class="row">
                                    <div class="col-lg-4 mb-3">
                                        <button @click="agregarDescuento" class="btn btn-primary"><i class="fas fa-plus"></i> Agregar descuento
                                        </button>
                                    </div>
                                    <div v-show="tipo_producto==1"  class="col-lg-12">
                                        <div class="row">
                                            <div class="col-lg-12" v-for="(descuento,index) in descuentos" :key="index">
                                                <div class="row">
                                                    <div class="col-lg-4">
                                                        <label for="precio">Cantidad:</label>
                                                        <b-input-group>
                                                            <input onfocus="this.select()" class="form-control" v-model="descuento.cantidad" type="number" placeholder="cantidad">
                                                            <b-input-group-append>
                                                                <b-input-group-text>
                                                                    {{(medida.split('/'))[1]}}
                                                                </b-input-group-text>
                                                            </b-input-group-append>
                                                        </b-input-group>
                                                    </div>
                                                    <div class="col-lg-3 form-group">
                                                        <label for="precio">Precio por {{(medida.split('/'))[1]}}:</label>
                                                        <b-input-group>
                                                            <input onfocus="this.select()"  class="form-control" v-model="descuento.precio" type="number" placeholder="precio">
                                                            <b-input-group-append>
                                                                <b-input-group-text>
                                                                    {{moneda}}
                                                                </b-input-group-text>
                                                            </b-input-group-append>
                                                        </b-input-group>
                                                    </div>
                                                    <div class="col-lg-4 form-group">
                                                        <label for="precio">Etiqueta (Opcional):</label>
                                                        <input onfocus="this.select()"  class="form-control" v-model="descuento.etiqueta" type="text" maxlength="50" placeholder="Promo, por mayor, etc">
                                                    </div>
                                                    <div class="col-lg-1">
                                                        <button @click="borrarDescuento(index)" style="margin-top: 20px" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </b-tab>
                        <b-tab v-if="origen == 'productos'" title="Ubicación">
                            <div class="container">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>Almacén</label>
                                            <select :disabled="accion == 'editar'" v-model="idalmacen" class="custom-select" @change="obtener_ubicacion()">
                                                <option v-for="item in almacen" v-bind:value="item.idalmacen">{{item.nombre}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="w-100">Ubicación
                                                <a style="color:#0062cc" v-show="!nuevaUbicacion" @click="nuevaUbicacion = true" class="float-right"><i class="fas fa-plus"></i> Nueva ubicación</a>
                                                <a style="color:red" v-show="nuevaUbicacion" @click="nuevaUbicacion = false" class="float-right"><i class="fas fa-ban"></i> Cancelar</a>
                                            </label>
                                            <select v-show="!nuevaUbicacion" v-model="idubicacion" class="custom-select">
                                                <option v-for="item in ubicacion" v-bind:value="item.idubicacion">{{item.nombre}}</option>
                                            </select>
                                            <b-input-group v-show="nuevaUbicacion">
                                                <input type="text" class="form-control" v-model="nombreUbicacion">
                                                <b-input-group-append>
                                                    <button :disabled="nombreUbicacion.length == ''" @click="guardarUbicacion" class="btn btn-primary"><i class="fas fa-save"></i></button>
                                                </b-input-group-append>
                                            </b-input-group>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </b-tab>
                        <b-tab v-if="origen == 'productos'" :disabled="tipo_producto!=1" title="Otros atributos">
                            <div class="container">
                                <div class="row">
                                    <div class="col-lg-3 form-group">
                                        <label>Marca:</label>
                                        <input v-model="marca" class="form-control" maxlength="60" type="text">
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label>Modelo:</label>
                                        <input v-model="modelo" class="form-control" maxlength="60" type="text">
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label>Montaje</label>
                                        <input v-model="param_1" class="form-control" maxlength="60" type="text">
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label>Cápsula</label>
                                        <input v-model="param_2" class="form-control" maxlength="60" type="text">
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label>Tipo</label>
                                        <input v-model="param_3" class="form-control" maxlength="60" type="text">
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label>Precio mínimo</label>
                                        <b-input-group>
                                            <b-form-input type="number" v-model="param_4"></b-form-input>
                                            <template #append>
                                                <b-dropdown :text="param_5==null ? 'PEN':param_5" variant="secondary">
                                                    <b-dropdown-item @click="param_5 = 'PEN'">PEN</b-dropdown-item>
                                                    <b-dropdown-item @click="param_5 = 'USD'">USD</b-dropdown-item>
                                                </b-dropdown>
                                            </template>
                                        </b-input-group>
                                    </div>
                                </div>
                            </div>
                        </b-tab>
                    </b-tabs>
                </b-card>
            </div>
            <div class="col-lg-12 mt-3">
                <div v-for="error in errorDatosProducto">
                    <p class="texto-error">{{ error }}</p>
                </div>
            </div>
        </b-modal>
    </div>
</template>

<script>
    export default {
        name:'agregar-producto',
        props:[
            'ultimo_id','tipo_cambio','unidad_medida','can_gestionar','origen', 'tipo_de_producto'
        ],
        data() {
            return {
                errorDatosProducto: [],
                errorProducto: 0,
                tituloModal:'Agregar producto / servicio',
                accion:'insertar',
                idproducto: -1,
                cod_producto: '',
                nombre: '',
                presentacion: '',
                precio: '0.0',
                costo: '0.0',
                eliminado: 0,
                imagen: '',
                cantidad: 0,
                cantidad_aux: 0,
                stock_bajo: '0',
                idcategoria: -1,
                medida: 'NIU/UND',
                categorias: [],
                tipo_producto: 1,
                barcode:'',
                observacion: '',
                moneda: 'PEN',
                moneda_compra:'PEN',
                tipo_cambio_compra:0,
                descuentos: [],
                almacen:[],
                idalmacen:-1,
                ubicacion:[],
                idubicacion:null,
                marca:'',
                modelo:'',
                param_1:'',
                param_2:'',
                param_3:'',
                param_4:'0.00',
                param_5:'PEN',
                nuevaUbicacion:false,
                nombreUbicacion:'',
                items_kit:[],
                unidad_de_medida:[],
            }
        },
        mounted(){
            this.obtener_categorias();
            this.obtener_almacen();
        },
        methods: {
            init(){
                this.tipo_cambio_compra = this.tipo_cambio;
                this.unidad_de_medida = this.unidad_medida;
                this.tipo_producto = this.tipo_de_producto;

            },
            obtener_categorias(){
                axios.get('/helper/categorias')
                    .then(response => {
                        let datos = response.data;
                        this.categorias = datos.categorias;
                        if (datos.categorias.length > 0) {
                            this.idcategoria = datos.categorias[0]['idcategoria'];
                        }
                    })
                    .catch(error => {
                        alert('Ha ocurrido un error.');
                        console.log(error);
                    });
            },
            guardarUbicacion(e){
                axios.post('/almacenes/store-ubicacion?origen=productos', {
                    'idalmacen':this.idalmacen,
                    'ubicacion': JSON.stringify([{
                        idubicacion:null,
                        idalmacen:this.idalmacen,
                        nombre: this.nombreUbicacion,
                        descripcion: '',
                        eliminado: 0
                    }]),
                })
                    .then(response => {
                        this.obtener_ubicacion(response.data);
                        this.nombreUbicacion = '';
                        this.nuevaUbicacion = false;
                    })
                    .catch(function (error) {
                        alert(error);
                        console.log(error);
                    });
            },
            editarProducto(id){
                this.tituloModal = 'Editar producto';
                this.accion = 'editar';
                this.idproducto = id;

                axios.get('/productos/edit' + '/' + id)
                    .then(response => {
                        let datos = response.data;
                        this.cod_producto = datos.cod_producto;
                        this.nombre = datos.nombre;
                        this.costo = datos.costo;
                        this.precio = datos.precio;
                        this.cantidad = datos.cantidad;
                        this.cantidad_aux = datos.cantidad;
                        this.stock_bajo = datos.stock_bajo;
                        this.presentacion = datos.presentacion;
                        this.medida = datos.unidad_medida;
                        this.idcategoria = datos.idcategoria;
                        this.tipo_producto = datos.tipo_producto;
                        this.descuentos = datos.descuentos;
                        this.barcode = datos.barcode;
                        this.moneda = datos.moneda;
                        this.marca = datos.marca;
                        this.modelo = datos.modelo;
                        this.param_1 = datos.param_1;
                        this.param_2 = datos.param_2;
                        this.param_3 = datos.param_3;
                        this.param_4 = datos.param_4;
                        this.param_5 = datos.param_5;
                        this.moneda_compra = datos.moneda_compra;
                        this.tipo_cambio_compra = datos.tipo_cambio;
                        this.idalmacen = datos.almacen.idalmacen || null;
                        this.idubicacion = datos.almacen.idubicacion || null;
                        this.items_kit = datos.items_kit == null ? [] : JSON.parse(datos.items_kit);
                        this.$refs['modal-nuevo-producto'].show();
                        this.obtener_ubicacion(this.idubicacion);
                    })
                    .catch(error => {
                        this.alerta('Ha ocurrido un error.');
                        console.log(error);
                    });

            },
            obtener_almacen(){
                axios.get('/productos/mostrar-almacen')
                    .then(response => {
                        let datos = response.data;
                        this.almacen = datos.almacen;
                        if (datos.almacen.length > 0) {
                            this.idalmacen = datos.almacen[0]['idalmacen'];
                        }
                        this.obtener_ubicacion()
                    })
                    .catch(error => {
                        this.alerta('Ha ocurrido un error al obtener los datos');
                        console.log(error);
                    });
            },
            obtener_ubicacion(idubicacion = false){
                axios.get('/productos/mostrar-ubicacion' + '/' + this.idalmacen)
                    .then(response => {
                        let datos = response.data;
                        this.ubicacion = datos.ubicacion;
                        if(idubicacion){
                            this.idubicacion = idubicacion;
                        } else {
                            if (datos.ubicacion.length > 0) {
                                this.idubicacion = datos.ubicacion[0]['idubicacion'];
                            } else {
                                this.idubicacion = null;
                            }
                        }

                    })
                    .catch(error => {
                        this.alerta('Ha ocurrido un error al obtener los datos');
                        console.log(error);
                    });
            },
            agregarDescuento(){
                this.descuentos.push({
                    cantidad: 0,
                    precio: '0.00',
                    etiqueta: ''
                });
            },
            borrarDescuento(index){
                this.descuentos.splice(index,1);
            },
            agregarAlKit(obj){
                let plato = {idproducto:obj['idproducto'],cantidad:1,precio:obj['precio'],nombre:obj['nombre']};
                this.items_kit.push(plato);
            },
            borrarItemKit(index){
                this.items_kit.splice(index, 1);
            },
            agregarProducto(e){
                if (this.validarProducto()) {
                    e.preventDefault();
                    return;
                }

                let tipo_accion = '/productos/update';

                if (this.accion == 'insertar') {
                    if (this.cod_producto.length == 0) {
                        this.generarCodigo();
                    }
                    tipo_accion = '/helper/guardar-producto';
                }

                let nombre = this.nombre;
                axios.post(tipo_accion, {
                    'idproducto': this.idproducto,
                    'cod_producto': this.cod_producto,
                    'nombre': this.nombre,
                    'presentacion': this.presentacion,
                    'precio': this.precio,
                    'costo': this.costo,
                    'cantidad': this.cantidad,
                    'cantidad_aux': this.cantidad_aux,
                    'stock_bajo': this.stock_bajo,
                    'idcategoria': this.idcategoria,
                    'medida': this.medida,
                    'tipo_producto':this.tipo_producto,
                    'moneda': this.moneda,
                    'descuentos': JSON.stringify(this.descuentos),
                    'observacion': this.observacion,
                    'moneda_compra' : this.moneda_compra,
                    'tipo_cambio_compra' : this.tipo_cambio_compra,
                    'idalmacen': this.idalmacen,
                    'idubicacion': this.idubicacion,
                    'marca': this.marca,
                    'modelo': this.modelo,
                    'param_1': this.param_1,
                    'param_2': this.param_2,
                    'param_3': this.param_3,
                    'param_4': this.param_4,
                    'param_5': this.param_5,
                    'items_kit': this.tipo_producto==3?JSON.stringify(this.items_kit):null,
                })
                    .then((response) => {
                        if(this.origen == 'productos'){
                            location.reload(true)
                        } else {
                            this.$emit('agregar',nombre);
                            this.$swal({
                                position: 'top',
                                icon: 'success',
                                title: response.data.mensaje,
                                timer: 2000,
                                showConfirmButton: false,
                                toast:true
                            }).then(() => {
                                this.$refs['modal-nuevo-producto'].hide();
                            });
                        }

                    })
                    .catch(error => {
                        this.$swal({
                            position: 'top',
                            icon: 'error',
                            title: error.response.data.mensaje,
                            timer: 2000,
                            showConfirmButton: false,
                            toast:true
                        });
                    });
            },
            generarCodigo(){
                let obj= this.ultimo_id;
                let codigoNumero = obj['idproducto'];
                if (codigoNumero < 100) {
                    codigoNumero = '0' + codigoNumero;
                }
                this.cod_producto = codigoNumero;
            },
            validarProducto(){
                this.errorProducto = 0;
                this.errorDatosProducto = [];
                if (!this.nombre) this.errorDatosProducto.push('*Nombre de producto no puede estar vacio');
                if (this.idcategoria == -1) this.errorDatosProducto.push('*Crea una categoría antes de guardar el producto');
                if (isNaN(this.precio) || this.precio.length == 0) this.errorDatosProducto.push('*El campo precio debe contener un número');
                if (isNaN(this.costo) || this.costo.length == 0) this.errorDatosProducto.push('*El campo costo debe contener un número');
                if (isNaN(this.cantidad) || this.cantidad.length == 0) this.errorDatosProducto.push('*El campo cantidad debe contener un número');
                if (isNaN(this.stock_bajo) || this.stock_bajo.length == 0) this.errorDatosProducto.push('*El campo stock bajo debe contener un número');
                if (!this.idubicacion) this.errorDatosProducto.push('*Ubicación de producto no puede quedar en blanco');
                if (this.accion == 'editar' && this.cantidad != this.cantidad_aux && this.tipo_producto==1 && !this.observacion) this.errorDatosProducto.push('*El motivo de edición no puede estar vacío');

                if(this.tipo_producto == '1' && this.descuentos.length > 0){

                    for(let descuento of this.descuentos){
                        if (isNaN(descuento.precio) || descuento.precio.length == 0 || isNaN(descuento.cantidad) || descuento.cantidad.length == 0) this.errorDatosProducto.push('*Las casillas de descuento deben contener un número');
                        if (descuento.cantidad <= 0 || descuento.precio <= 0) this.errorDatosProducto.push('*Las casillas de descuento deben ser mayor a 0');
                        break;
                    }

                }

                if (this.errorDatosProducto.length) this.errorProducto = 1;
                return this.errorProducto;
            },
            resetModal(){
                this.errorDatosProducto = [];
                this.errorProducto = 0;
                this.tituloModal = 'Agregar producto';
                this.accion = 'insertar';
                this.idproducto = -1;
                this.cod_producto = '';
                this.nombre = '';
                this.presentacion = '';
                this.precio = '0.0';
                this.costo = '0.0';
                this.eliminado = 0;
                this.imagen = '';
                this.cantidad = '0';
                this.stock_bajo = '0';
                this.medida = 'NIU/UND';
                this.tipo_producto = 1;
                this.moneda = 'PEN';
                this.descuentos = [];
                this.observacion = '';
                this.moneda_compra = 'PEN';
                this.tipo_cambio_compra = this.tipo_cambio;
                this.idubicacion = this.ubicacion[0].idubicacion;
                this.idalmacen = this.almacen[0].idalmacen;
                this.marca = '';
                this.modelo = '';
                this.param_1 = '';
                this.param_2 = '';
                this.param_3 = '';
                this.param_4 = '0.00';
                this.param_5 = 'PEN';
                this.nombreUbicacion = '';
                this.nuevaUbicacion = false;
                this.items_kit = [];
            }
        }
    }
</script>