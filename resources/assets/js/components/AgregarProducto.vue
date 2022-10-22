<template>
    <div>
        <b-modal id="modal-nuevo-producto" ref="modal-nuevo-producto" size="lg"
                 title="" @ok="agregarProductoNuevo" @hidden="resetModal">
            <template slot="modal-title">
                {{tituloModal}}
            </template>
            <div class="container">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="idcategoria">Tipo:</label>
                            <select v-model="tipo_producto" class="custom-select">
                                <option value="1">Producto</option>
                                <option value="2">Servicio</option>
                            </select>
                        </div>
                    </div>
                    <div v-if="cod_producto.length > 0" class="col-lg-6">
                        <div class="form-group">
                            <label for="nombre">Nombre producto / servicio:</label>
                            <input type="text" v-model="nombre" name="nombre"  class="form-control" autocomplete="off">
                        </div>
                    </div>
                    <div v-if="cod_producto.length == 0" class="col-lg-9">
                        <div class="form-group">
                            <label for="nombre">Nombre producto / servicio:</label>
                            <input type="text" v-model="nombre" name="nombre"  class="form-control" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="presentacion">Características:</label>
                            <textarea v-model="presentacion" class="form-control" name="presentacion" id="" cols="30"
                                      rows="2"></textarea>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="idcategoria">Categoría:</label>
                            <select v-model="idcategoria" name="idcategoria" class="custom-select" id="selectPlantilla">
                                <option v-for="categoria in categorias" v-bind:value="categoria.idcategoria">{{categoria.nombre}}</option>
                            </select>
                        </div>
                    </div>
                     <div class="col-lg-3">
                        <div class="form-group">
                            <label for="undMedida">Unidad de medida:</label>
                            <select v-model="medida" name="undMedida" class="custom-select" id="selectUnd">
                                <option value="NIU/UND">Unidad</option>
                                <option value="MTR/M">Metro</option>
                                <option value="RO/ROL">Rollo</option>
                                <option value="KGM/KG">Kilogramo</option>
                                <option value="GRM/G">Gramo</option>
                                <option value="LTR/L">Litro</option>
                                <option value="MTK/M2">Metro cuadrado</option>
                                <option value="MTQ/M3">Metro cúbico</option>
                                <option value="PK/PQ">Paquete</option>
                                <option value="BX/CJ">Caja</option>
                                <option value="BE/BE">Fardo</option>
                                <option value="BG/BG">Bolsa</option>
                                <option value="BJ/BJ">Balde</option>
                            </select>
                        </div>
                    </div>
                     <div v-show="tipo_producto==1" class="col-lg-3">
                        <div class="form-group">
                            <label for="cantidad">Cantidad:</label>
                            <input autocomplete="off" type="number" v-model="cantidad" name="cantidad" class="form-control">
                        </div>
                    </div>
                    <div v-show="tipo_producto==1" class="col-lg-3">
                        <div class="form-group">
                            <label for="stock_bajo">Stock mínimo:</label>
                            <input autocomplete="off" type="number" v-model="stock_bajo" name="stock_bajo" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <label>Precio de venta:</label>
                        <b-input-group>
                            <b-form-input type="number" v-model="precio"></b-form-input>
                            <template #append>
                                <b-dropdown :text="moneda" variant="secondary">
                                    <b-dropdown-item @click="moneda = 'PEN'">PEN</b-dropdown-item>
                                    <b-dropdown-item @click="moneda = 'USD'">USD</b-dropdown-item>
                                </b-dropdown>
                            </template>
                        </b-input-group>
                    </div>
                    <div v-show="tipo_producto==1" class="col-lg-4 mb-3">
                        <label for="precio">Descuentos:</label>
                        <button @click="agregarDescuento" class="btn btn-primary"><i class="fas fa-plus"></i> Agregar descuento
                        </button>
                    </div>
                    <div v-show="tipo_producto==1"  class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-6" v-for="(descuento,index) in descuentos" :key="index">
                                <div class="row">
                                    <div class="col-lg-5 form-group">
                                        <label for="precio">Cantidad > ó =:</label>
                                        <input class="form-control" v-model="descuento.cantidad" type="number" placeholder="cantidad">
                                    </div>
                                    <div class="col-lg-5 form-group">
                                        <label for="precio">Precio:</label>
                                        <input class="form-control" v-model="descuento.precio" type="number" placeholder="precio">
                                    </div>
                                    <div class="col-lg-2">
                                        <i style="right: 50px" @click="borrarDescuento(index)" class="fas fa-times-circle borrarCliente"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 mt-3">
                        <p><strong>Data para reportes</strong></p>
                    </div>
                    <div class="col-lg-4">
                        <label>Costo por ofrecer el {{tipo_producto==1?'producto':'servicio'}}:</label>
                        <b-input-group>
                            <b-form-input type="number" v-model="costo"></b-form-input>
                            <template #append>
                                <b-dropdown :text="moneda_compra" variant="secondary">
                                    <b-dropdown-item @click="moneda_compra = 'PEN'">PEN</b-dropdown-item>
                                    <b-dropdown-item @click="moneda_compra = 'USD'">USD</b-dropdown-item>
                                </b-dropdown>
                            </template>
                        </b-input-group>
                    </div>
                    <div class="col-lg-3" v-show="moneda_compra == 'USD'">
                        <label>Tipo de cambio</label>
                        <b-input-group prepend="S/">
                            <b-form-input type="number" v-model="tipo_cambio_compra"></b-form-input>
                        </b-input-group>
                    </div>
                    <div class="col-lg-6">
                        <div v-for="error in errorDatosProducto">
                            <p class="texto-error">{{ error }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </b-modal>
    </div>
</template>

<script>
    export default {
        name:'agregar-producto',
        props:[
            'ultimo_id','tipo_cambio_compra'
        ],
        data() {
            return {
                errorDatosProducto: [],
                errorProducto: 0,
                tituloModal:'Agregar producto',
                accion:'insertar',
                idproducto: -1,
                cod_producto: '',
                nombre: '',
                presentacion: '',
                precio: '0.0',
                costo: '0.0',
                eliminado: 0,
                imagen: '',
                cantidad: '0',
                cantidad_aux:0,
                stock_bajo: '0',
                idcategoria: -1,
                idcategoria_aux:-1,
                medida:'NIU/UND',
                categorias:[],
                tipo_producto:1,
                moneda: 'PEN',
                moneda_compra:'PEN',
                descuentos: [],
            }
        },
        mounted(){
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
        methods: {
            agregarProductoNuevo(e){
                e.preventDefault();
                if (this.validarProducto()) {
                    return;
                }
                if(this.accion=='insertar'){
                    if(this.cod_producto.length==0){
                        this.generarCodigo();
                    }
                }
                let nombre=this.nombre;
                axios.post('/helper/guardar-producto', {
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
                    'moneda_compra' : this.moneda_compra,
                    'tipo_cambio_compra' : this.tipo_cambio_compra,
                })
                    .then((response) => {
                        this.$swal({
                            position: 'top',
                            icon: 'success',
                            title: response.data.mensaje,
                            timer: 2000,
                            showConfirmButton: false,
                            toast:true
                        }).then(() => {
                            this.$emit('agregar',nombre);
                            this.$refs['modal-nuevo-producto'].hide();
                        });
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
                let codigoCaracter=this.nombre.slice(0,3);
                let obj= this.ultimo_id;
                let codigoNumero=obj['idproducto'];
                if(this.nombre<=3){
                    codigoCaracter=this.nombre.trim();
                }
                if(codigoNumero<100){
                    codigoNumero='0'+codigoNumero;
                }
                this.cod_producto=codigoCaracter+codigoNumero;
            },
            validarProducto(){
                this.errorProducto = 0;
                this.errorDatosProducto = [];
                if (!this.nombre) this.errorDatosProducto.push('*Nombre de producto no puede estar vacio');
                if (this.idcategoria==-1) this.errorDatosProducto.push('*Crea una categoría antes de guardar el producto');
                if(isNaN(this.precio)||this.precio.length==0) this.errorDatosProducto.push('*El campo precio debe contener un número');
                if(isNaN(this.costo)||this.costo.length==0) this.errorDatosProducto.push('*El campo costo debe contener un número');
                if(isNaN(this.cantidad)||this.cantidad.length==0) this.errorDatosProducto.push('*El campo cantidad debe contener un número');
                if(isNaN(this.stock_bajo)||this.stock_bajo.length==0) this.errorDatosProducto.push('*El campo stock bajo debe contener un número');

                if (this.errorDatosProducto.length) this.errorProducto = 1;
                return this.errorProducto;
            },
            agregarDescuento(){
                this.descuentos.push({
                    cantidad: 0,
                    precio: '0.00'
                });
            },
            borrarDescuento(index){
                this.descuentos.splice(index,1);
            },
            resetModal(){
                this.errorDatosProducto=[];
                this.errorProducto= 0;
                this.tituloModal='Agregar producto';
                this.accion='insertar';
                this.idproducto=-1;
                this.cod_producto= '';
                this.nombre= '';
                this.presentacion= '';
                this.precio= '0.0';
                this.costo= '0.0';
                this.eliminado= 0;
                this.imagen= '';
                this.cantidad= '0';
                this.stock_bajo= '0';
                this.idcategoria= this.categorias[0]['idcategoria']
                this.medida='NIU/UND';
                this.tipo_producto=1;
                this.moneda='PEN';
            }
        }
    }
</script>