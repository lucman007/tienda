
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
import BootstrapVue from 'bootstrap-vue';
import VueSweetalert2 from 'vue-sweetalert2';

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.use(BootstrapVue);
Vue.use(VueSweetalert2);
Vue.component('agregar-cliente', require('./components/AgregarCliente.vue'));
Vue.component('agregar-producto', require('./components/AgregarProducto.vue'));
Vue.component('agregar-proveedor', require('./components/AgregarProveedor.vue'));
Vue.component('paginacion-js', require('./components/Paginacion.vue'));
Vue.component('line-chart', require('./components/LineChart.vue'));
Vue.component('doughnut-chart', require('./components/DoughnutChart.vue'));
Vue.component('modal-cliente', require('./components/ModalCliente.vue'));
Vue.component('autocomplete',require('./components/Autocomplete.vue'));
Vue.component('autocomplete-cliente',require('./components/AutocompleteCliente.vue'));
Vue.component('pedidos',require('./components/ListaPedidos.vue'));
Vue.component('modal-facturacion',require('./components/ModalFacturacion.vue'));
Vue.component('modal-facturacion-ticket',require('./components/ModalFacturacionTicket.vue'));
Vue.component('modal-agregar-producto',require('./components/ModalAgregarProducto.vue'));
Vue.component('modal-entrega',require('./components/ModalEntrega.vue'));
Vue.component('modal-whatsapp',require('./components/ModalWhatsapp.vue'));
Vue.component('modal-detalle',require('./components/ModalDetalle.vue'));
Vue.component('panel-notificacion',require('./components/PanelNotificacion.vue'));
Vue.component('tipo-cambio',require('./components/TipoCambio.vue'));
Vue.component('range-calendar',require('./components/RangeCalendar.vue'));
Vue.component('modal-descuento',require('./components/ModalDescuento.vue'));
Vue.component('modal-producto', require('./components/Modalproducto.vue'));
Vue.component('modal-ubigeo',require('./components/ModalUbigeo.vue'));
Vue.component('modal-producto-descuento',require('./components/ProductoDescuento.vue'));
Vue.component('modal-devolucion',require('./components/ModalDevolucion.vue'));