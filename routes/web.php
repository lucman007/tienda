<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['permission:Caja: egresos|Inventario: productos']], function () {
    Route::get('caja/movimientos', 'GastoController@index');
    Route::get('caja/movimientos/total', 'GastoController@obtenerTotal');
    Route::delete('caja/destroy/{id}', 'GastoController@destroy');
    Route::post('caja/store', 'GastoController@store');
    Route::post('caja/gasto/obtener-datos', 'GastoController@obtener_datos');
    Route::post('caja/gasto/obtener-empleados', 'GastoController@obtenerEmpleados');
    Route::post('caja/gasto/obtener-pago-pendiente', 'GastoController@obtenerPagoPendiente');
    Route::get('caja/obtener-detalle-venta/{idventa}', 'GastoController@obtenerDetalleVenta');
    Route::post('caja/devolver-productos', 'GastoController@devolver_productos');
});

Route::group(['middleware' => ['can:Caja: gestionar']], function () {
    //Rutas para caja
    Route::post('caja/datos-cierre', 'CajaController@obtener_datos_cierre');
    Route::post('caja/obtener-datos', 'CajaController@obtener_datos');
    Route::post('caja/procesar-cierre', 'CajaController@procesar_cierre');
    Route::post('caja/update', 'CajaController@update');
    Route::post('caja/abrir', 'CajaController@abrir_caja');
    Route::delete('caja/borrar-caja/{id}', 'CajaController@borrar_caja');
    Route::get('caja/imprimir/{id}', 'CajaController@imprimir_cierre');
    Route::get('caja/editar-apertura/{id}', 'CajaController@editar_caja');
    Route::get('caja/ver-cache', 'CajaController@ver_cache');
    Route::get('caja/{fecha}', 'CajaController@index');
    Route::get('caja', 'CajaController@index');
});

Route::group(['middleware' => ['can:Inventario: productos']], function () {
//Rutas para productos
    Route::get('productos', 'ProductoController@index');
    Route::post('productos/store', 'ProductoController@store')->name('guardarProducto');
    Route::delete('/productos/destroy/{id}', 'ProductoController@destroy')->name('eliminarProducto');
    Route::get('productos/edit/{id}', 'ProductoController@edit')->name('editarProducto');
    Route::get('productos/inventario/{id}', 'ProductoController@inventario')->name('inventarioProducto');
    Route::post('productos/update', 'ProductoController@update')->name('actualizarProducto');
    Route::get('productos/exportar', 'ProductoController@exportar');
    Route::post('productos/importar-productos', 'ProductoController@importar_productos');
    Route::get('productos/descargar-formato-importacion', 'ProductoController@descargar_formato_importacion');
    Route::get('productos/mostrar_categorias', 'ProductoController@mostrar_categorias');
    Route::post('productos/agregar-imagen', 'ProductoController@agregar_imagen');
    Route::get('productos/update-saldo', 'ProductoController@temp_saldo_productos');
    Route::get('productos/mostrar-almacen', 'ProductoController@mostrar_almacen');
    Route::get('productos/mostrar-ubicacion/{id}', 'ProductoController@mostrar_ubicacion');
    Route::post('productos/ocultar-columnas', 'ProductoController@ocultar_columnas');
    Route::get('productos/temp-almacen', 'ProductoController@temp_almacen');
});

Route::group(['middleware' => ['can:Mantenimiento: categorías']], function () {
//Rutas para categorias
    Route::resource('categorias', 'CategoriaController');
    Route::post('/categorias/store', 'CategoriaController@store')->name('guardarCategoria');
    Route::delete('/categorias/destroy/{id}', 'CategoriaController@destroy')->name('eliminarCategoria');
    Route::get('/categorias/show', 'CategoriaController@show')->name('obtenerCategorias');
    Route::get('/categorias/edit/{id}', 'CategoriaController@edit')->name('editarCategorias');
    Route::put('/categorias/update', 'CategoriaController@update')->name('actualizarCategoria');
});

Route::group(['middleware' => ['can:Clientes']], function () {
//Rutas para cliente
    Route::get('clientes', 'ClienteController@index');
    Route::post('/clientes/store', 'ClienteController@store')->name('guardarCliente');
    Route::delete('/clientes/destroy/{id}', 'ClienteController@destroy')->name('eliminarCliente');
    Route::get('/clientes/edit/{id}', 'ClienteController@edit')->name('editarCliente');
    Route::post('/clientes/update', 'ClienteController@update')->name('actualizarCliente');
    Route::get('/clientes/exportar', 'ClienteController@exportar')->name('exportarClientes');
    Route::post('clientes/importar-clientes', 'ClienteController@importar_clientes');
    Route::get('clientes/descargar-formato-importacion', 'ClienteController@descargar_formato_importacion');
});

Route::group(['middleware' => ['can:Mantenimiento: empleados']], function () {
//Rutas para trabajador
    Route::resource('trabajadores', 'TrabajadorController');
    Route::post('trabajadores/store', 'TrabajadorController@store')->name('guardarTrabajador');
    Route::delete('trabajadores/destroy/{id}', 'TrabajadorController@destroy')->name('eliminarTrabajador');
    Route::get('trabajadores/edit/{id}', 'TrabajadorController@edit')->name('editarTrabajador');
    Route::get('trabajadores/usuario/{id}', 'TrabajadorController@gestionar_usuario')->name('gestionarUsuario');
    Route::post('trabajadores/guardar-credenciales', 'TrabajadorController@guardar_credenciales')->name('guardarCredenciales');
    Route::post('trabajadores/eliminar-credenciales', 'TrabajadorController@eliminar_credenciales');
    Route::post('trabajadores/update', 'TrabajadorController@update')->name('actualizarTrabajador');
    Route::post('trabajadores/verificar', 'TrabajadorController@verificarUsuario')->name('verificarUsuario');
    Route::get('trabajadores/pagos/{id}', 'TrabajadorController@pagos');
    Route::post('trabajadores/obtener-pagos', 'TrabajadorController@obtenerPagos');
    Route::get('trabajadores/imprimir-pagos/{id}/{fecha}', 'TrabajadorController@imprimir_pagos');
    Route::get('trabajadores/exportar-pagos/{id}/{fecha}', 'TrabajadorController@exportar_pagos');
});


Route::group(['middleware' => ['can:Pedido']], function () {
//Rutas para pedidos
    Route::get('pedidos','PedidoController@index');
    Route::get('pedidos/mesa/{numero}','PedidoController@ver_mesa');
    Route::post('pedidos/mesa/verificar','PedidoController@verificar_mesa');
    //Route::get('pedidos/obtener-mesas','PedidoController@obtener_mesas');
    Route::get('pedidos/obtener-pedidos','PedidoController@obtener_pedidos');
    Route::post('pedidos/productos_por_categoria', 'PedidoController@productos_por_categoria');
    Route::get('pedidos/productos/{search}', 'PedidoController@obtenerProductos');
    Route::post('pedidos/store', 'PedidoController@store');
    Route::post('pedidos/update', 'PedidoController@update');
    Route::get('pedidos/editar/{idpedido}','PedidoController@editar_pedido');
    Route::get('pedidos/cambiar-mesa','PedidoController@cambiar_mesa');
    Route::post('pedidos/guardar-cambio-mesa','PedidoController@guardar_cambio_mesa');
    Route::post('pedidos/marcar-como-reservado','PedidoController@marcar_como_reservado');
    Route::get('pedidos/imprimir/{id}','PedidoController@imprimir_pedido');
    Route::get('pedidos/imprimir_entrega/{id}','PedidoController@imprimir_datos_entrega');
    Route::get('pedidos/nuevo/','PedidoController@nuevo_pedido_sin_mesa');
    Route::get('pedidos/lista/','PedidoController@lista');
    Route::get('pedidos/ventas/','PedidoController@ventas');
    Route::delete('pedidos/destroy/{id}','PedidoController@destroy');
    Route::get('pedidos/obtenerCategorias', 'PedidoController@obtenerCategorias');
    Route::post('pedidos/guardar-producto', 'PedidoController@guardarProducto');
    Route::post('pedidos/nuevo_cliente', 'PedidoController@nuevo_cliente');
    Route::get('pedidos/imprimir-historial', 'PedidoController@imprimir_historial');
    Route::get('pedidos/obtener-data-pedido/{id}', 'PedidoController@obtener_data_pedido');
    Route::post('pedidos/borrar-item-pedido', 'PedidoController@borrarItemPedido');
    Route::post('pedidos/actualizar-detalle', 'PedidoController@actualizarDetalle');
    Route::get('pedidos/obtener-mesas/{piso}','PedidoController@obtener_mesas');
    Route::get('pedidos/obtener-empleados','PedidoController@obtenerEmpleados');
    Route::post('pedidos/cambiar-vendedor','PedidoController@cambiar_vendedor');
    Route::get('pedidos/obtener-datos-entrega/{id}','PedidoController@obtener_datos_entrega');
    Route::post('pedidos/guardar-datos-entrega','PedidoController@guardar_datos_entrega');
    Route::get('pedidos/nuevo', 'PedidoController@nueva_orden');
    Route::get('pedidos/imprimir-lista', 'PedidoController@imprimir_lista');

});


Route::group(['middleware' => ['can:Cotizaciones']], function () {
//Rutas para presupuesto
    Route::get('presupuestos','PresupuestoController@index');
    Route::post('presupuestos/store','PresupuestoController@store');
    Route::post('presupuestos/crear_presupuesto','PresupuestoController@presupuesto_desde_orden');
    Route::post('presupuestos/aprobar_presupuesto','PresupuestoController@aprobar_presupuesto');
    Route::post('presupuestos/productos','PresupuestoController@obtenerProductos');
    Route::post('presupuestos/update','PresupuestoController@update')->name('actualizarPresupuesto');
    Route::get('presupuestos/nuevo','PresupuestoController@nuevo_presupuesto')->name('nuevoPresupuesto');
    Route::get('presupuestos/editar/{id}','PresupuestoController@editar');
    Route::delete('presupuestos/destroy/{id}','PresupuestoController@destroy');
    Route::post('presupuestos/obtenerClientes','PresupuestoController@obtenerClientes');
    Route::get('presupuestos/imprimir/{id}','PresupuestoController@imprimir_presupuesto');
    Route::get('presupuestos/descargar/{id}','PresupuestoController@descargar_presupuesto');
    Route::get('presupuestos/duplicar/{id}','PresupuestoController@duplicar');
    Route::get('presupuestos/obtenerCorrelativo/','PresupuestoController@obtenerCorrelativo');
    Route::post('presupuestos/mail','PresupuestoController@enviar_presupuesto_por_email');
    Route::get('presupuestos/obtenerCategorias', 'PresupuestoController@obtenerCategorias');
    Route::post('presupuestos/guardar-producto', 'PresupuestoController@guardarProducto');
    Route::post('presupuestos/nuevo_cliente', 'PresupuestoController@nuevo_cliente');
    Route::get('presupuestos/configuracion', 'PresupuestoController@configuracion');
    Route::post('presupuestos/guardar-configuracion', 'PresupuestoController@guardarConfiguracion');
});

Route::group(['middleware' => ['can:Mantenimiento: proveedores']], function () {

//Rutas para proveedores
    Route::get('proveedores', 'ProveedorController@index');
    Route::post('proveedores/store', 'ProveedorController@store');
    Route::post('proveedores/update', 'ProveedorController@update');
    Route::get('/proveedores/edit/{id}', 'ProveedorController@edit')->name('editarProveedor');
    Route::delete('/proveedores/destroy/{id}', 'ProveedorController@destroy')->name('eliminarProveedor');

});


Route::group(['middleware' => ['can:Facturación: facturar']], function () {
//Rutas para ventas
    Route::get('facturacion', 'VentaController@registrar');
    Route::post('ventas/facturacion-rapida', 'VentaController@facturacion_rapida');
    Route::post('ventas/facturacion-rapida-alt', 'VentaController@facturacion_rapida_alt');
    Route::post('ventas/facturacion-desde-ticket', 'VentaController@facturacion_desde_ticket');
    Route::post('ventas/facturacion-desde-ticket-alt', 'VentaController@facturacion_desde_ticket_alt');
    Route::post('facturacion/copiar-presupuesto','VentaController@copiarPresupuesto');
    Route::post('facturacion/copiar-guia','VentaController@copiarGuia');
    Route::post('facturacion/copiar-orden', 'VentaController@copiarOrden');
    Route::post('facturacion/copiar-produccion','VentaController@copiarProduccion');
    Route::get('facturacion/documento/{id}', 'VentaController@show');
    Route::get('ventas/obtenerCorrelativo/{id}', 'VentaController@obtenerCorrelativo');
    Route::post('ventas/obtenerClientes', 'VentaController@obtenerClientes')->name('ventasObtenerClientes');
    Route::post('ventas/obtenerProductos', 'VentaController@obtenerProductos')->name('ventasObteneProductos');
    Route::post('ventas/obtenerDocumentos', 'VentaController@obtenerDocumentos')->name('ventasObteneDocumentos');
    Route::post('ventas/copiarVenta', 'VentaController@copiarVenta')->name('ventasCopiarVenta');
    Route::post('ventas/store', 'VentaController@store')->name('procesarVenta');
    Route::post('ventas/obtenerVentas', 'VentaController@obtenerVentas');
    Route::get('ventas/imprimir/{id}', 'VentaController@imprimir_venta');
    Route::get('ventas/eliminar-venta/{id}', 'VentaController@eliminar_venta');
    Route::get('ventas/imprimir_recibo/{id}', 'VentaController@imprimir_recibo');
    Route::get('ventas/obtenerCorrelativoGuia', 'VentaController@obtenerCorrelativoGuia');
    Route::post('ventas/mail', 'VentaController@enviar_comprobantes_por_email');
    Route::get('ventas/obtenerCategorias', 'VentaController@categorias');
    Route::get('ventas/obtenerPedidos', 'VentaController@obtenerPedidos');
    Route::post('ventas/guardar-producto', 'VentaController@guardarProducto');
    Route::post('facturacion/copiar-pedido','VentaController@copiarPedido');
    Route::post('ventas/nuevo_cliente', 'VentaController@nuevo_cliente');
    Route::post('ventas/update_tipo_pago', 'VentaController@update_tipo_pago');
    Route::post('ventas/anulacion-rapida', 'VentaController@anulacion_rapida');
    Route::post('ventas/verificar-cdr-mail','VentaController@verificar_cdr_previo_mail');

    //Rutas facturacion
    Route::get('ventas/reenviar/{id}/{file}/{doc_relacionado}','Cpe\CpeController@reenviar');
    Route::get('ventas/regenerar/{doc}/{id}','Cpe\CpeController@regenerarArchivos');
    Route::get('ventas/enviar-resumen/{fecha}', 'Cpe\CpeController@sendSummary');
    Route::get('ventas/descargar/{file}', 'Cpe\CpeController@descargarArchivo');
    Route::get('ventas/getstatus/{ticket}/{nombre}', 'Cpe\CpeController@getStatus');
    Route::post('ventas/getStatusCdr', 'Cpe\CpeController@getStatusCdr');
    Route::get('cpe/generar-tocken-guia', 'Cpe\CpeController@generarTockenGRE');
    Route::get('cpe/enviar-guia-api/{id}', 'Cpe\CpeController@enviarGuiaApi');
});

Route::group(['middleware' => ['can:Facturación: comprobantes']], function () {
//Rutas para comprobantes
    Route::get('comprobantes/detalle-resumen/{id}', 'ComprobanteController@detalle_resumen');
    /*Route::get('comprobantes/{filtro}/{param_1}/{param_2}/{param_3}','ComprobanteController@comprobantesEmitidos');
    Route::get('comprobantes/{filtro}/{param_1}/{param_2}','ComprobanteController@comprobantesEmitidos');
    Route::get('comprobantes/{filtro}/{param_1}','ComprobanteController@comprobantesEmitidos');*/
    Route::get('comprobantes/consulta-cdr', 'ComprobanteController@consulta');
    Route::get('comprobantes/anular', 'ComprobanteController@anular');
    Route::post('comprobantes/anular-facturas', 'ComprobanteController@anular_facturas');
    Route::post('comprobantes/anular-boletas', 'ComprobanteController@anular_boletas');
    Route::post('comprobantes/obtener-comprobantes', 'ComprobanteController@obtenerComprobantes');
    Route::get('comprobantes/resumenes', 'ComprobanteController@resumenes_enviados');
    Route::post('comprobantes/obtener-resumen', 'ComprobanteController@obtener_resumen');
    Route::get('comprobantes/{desde}/{hasta}','ComprobanteController@comprobantes');
    Route::get('comprobantes', 'ComprobanteController@comprobantes');
    Route::get('comprobantes/update-serie', 'ComprobanteController@temp_update_serie');
});

Route::group(['middleware' => ['can:Reportes']], function () {
//Rutas para reportes
    Route::get('reportes/ventas/diario/{mes}','ReporteController@reporte_ventas_diario');
    Route::get('reportes/ventas/mensual/{anio}','ReporteController@reporte_ventas_mensual');
    Route::get('reportes/ventas/generar-mes/{mes}','ReporteController@reporte_mensual_generar_mes');

    Route::get('reportes/gastos/diario/{mes}','ReporteController@reporte_gastos_diario');
    Route::get('reportes/gastos/mensual/{anio}','ReporteController@reporte_gastos_mensual');
    Route::get('reportes/exportar/gastos_diario/{mes}','ReporteController@reporte_gastos_diario_export');
    Route::get('reportes/exportar/gastos_mensual/{anio}','ReporteController@reporte_gastos_mensual_export');

    Route::get('reportes/productos/stock_bajo','ReporteController@reporte_stock_bajo');
    Route::get('reportes/productos/mas-vendidos','ReporteController@mas_vendidos');
    Route::get('reportes/productos/badge','ReporteController@mas_vendidos_badge');

    Route::get('reportes/comprobantes/{desde}/{hasta}','ReporteController@reporte_comprobantes');
    Route::get('reportes/comprobantes','ReporteController@reporte_comprobantes');
    Route::get('reportes/descargar/comprobante/{param_1}','ReporteController@descargar_archivo');

    Route::get('reportes/ventas/{desde}/{hasta}','ReporteController@reporte_ventas');
    Route::get('reportes/ventas','ReporteController@reporte_ventas');
    Route::get('reportes/ventas/badge/{desde}/{hasta}','ReporteController@reporte_ventas_badge');
    Route::get('reportes/ventas/mail/{desde}/{hasta}','ReporteController@reporte_ventas_por_email');
    Route::get('reportes/ventas/imprimir/{desde}/{hasta}','ReporteController@reporte_ventas_imprimir');

    Route::get('reportes/gastos/{desde}/{hasta}','ReporteController@reporte_gastos');
    Route::get('reportes/gastos','ReporteController@reporte_gastos');

    Route::get('reportes/caja/{desde}/{hasta}','ReporteController@reporte_caja');
    Route::get('reportes/caja','ReporteController@reporte_caja');

    Route::get('reportes/utilidad','ReporteController@reporte_utilidad');
    Route::get('reportes/obtener-vendedores','ReporteController@obtener_vendedores');
});

Route::group(['middleware' => ['can:Inventario: requerimientos']], function () {
//Rutas para requerimientos
    Route::get('requerimientos', 'RequerimientoController@index');
    Route::get('requerimientos/nuevo', 'RequerimientoController@nuevo_requerimiento');
    Route::post('requerimientos/obtenerProveedores', 'RequerimientoController@obtenerProveedores');
    Route::post('requerimientos/store', 'RequerimientoController@store');
    Route::get('requerimientos/editar/{id}', 'RequerimientoController@editar');
    Route::post('requerimientos/update', 'RequerimientoController@update');
    Route::delete('requerimientos/destroy/{id}', 'RequerimientoController@destroy');
    Route::post('requerimientos/obtenerProductos', 'RequerimientoController@obtenerProductos');
    Route::post('requerimientos/recibir', 'RequerimientoController@recibir');
    Route::get('requerimientos/imprimir/{id}', 'RequerimientoController@imprimir');
});
Route::group(['middleware' => ['can:Configuración']], function () {
//Rutas para configuración
    Route::get('configuracion', 'ConfiguracionController@index');
    Route::post('configuracion/crear_rol', 'ConfiguracionController@crear_rol');
    Route::post('configuracion/crear_permiso', 'ConfiguracionController@crear_permiso');
    Route::post('configuracion/asignar_privilegios', 'ConfiguracionController@asignar_privilegios');
    Route::post('configuracion/editar_privilegios', 'ConfiguracionController@editar_privilegios');
    Route::get('configuracion/permisos/{id}', 'ConfiguracionController@permisos');
    Route::get('configuracion/permisos/eliminar/{id}', 'ConfiguracionController@eliminar_rol');
    Route::post('configuracion/borrar-permiso', 'ConfiguracionController@borrar_permiso');
    Route::post('configuracion/actualizar-permiso', 'ConfiguracionController@actualizar_permiso');
    Route::post('configuracion/guardarEmisor', 'ConfiguracionController@guardarEmisor');
    Route::post('configuracion/guardarMailFrom', 'ConfiguracionController@guardarMailFrom');
    Route::post('configuracion/guardarConfiguracion', 'ConfiguracionController@guardarConfiguracion');
    Route::get('configuracion/lista', 'ConfiguracionController@cacheSetting');
    Route::post('configuracion/agregar-imagen', 'ConfiguracionController@agregarImagen');
    Route::post('configuracion/borrar-imagen', 'ConfiguracionController@borrarImagen');
    Route::get('configuracion/mostrar-plantilla/{plantilla}', 'ConfiguracionController@mostrar_plantilla');
    Route::get('configuracion/mostrar-plantilla-cotizacion/{plantilla}', 'ConfiguracionController@mostrar_plantilla_cotizacion');
    Route::get('configuracion/cerrar-sesiones', 'ConfiguracionController@cerrarSesiones');
    Route::get('configuracion/reiniciar-vistas', 'ConfiguracionController@reiniciar_vistas');
    Route::get('configuracion/verificar-totales', 'ConfiguracionController@verificar_totales');
});

Route::group(['middleware' => ['can:Facturación: guía']], function () {
    //Rutas para guia
    Route::get('guia/nuevo','GuiaController@nuevo');
    Route::get('guia/obtenerCorrelativo/','GuiaController@obtenerCorrelativo');
    Route::post('guia/store','GuiaController@store');
    Route::post('guia/update','GuiaController@update');
    Route::post('guia/obtenerProductos','GuiaController@obtenerProductos');
    Route::post('guia/obtenerDocumentos','GuiaController@obtenerDocumentos');
    Route::post('guia/copiar-guia','GuiaController@copiarGuia');
    Route::post('guia/obtenerClientes','GuiaController@obtenerClientes');
    Route::post('guia/copiarVenta','GuiaController@copiarDocumento');
    Route::post('guia/mail','GuiaController@enviar_comprobantes_por_email');
    Route::get('guia/emision/{id}','GuiaController@show');
    Route::get('guia/correccion/{id}','GuiaController@correccion');
    Route::get('guia/{fecha_in}/{fecha_out}/{busqueda}/{filtro}','GuiaController@guiasEmitidas');
    Route::get('guia/obtenerCategorias', 'GuiaController@categorias');
    Route::post('guia/guardar-producto', 'GuiaController@guardarProducto');
    Route::get('guia/imprimir/{id}', 'GuiaController@imprimir_guia');
    Route::get('guia/descargar/{file}', 'Cpe\CpeController@descargarArchivo');
    Route::get('guia/{desde}/{hasta}','GuiaController@index');
    Route::get('guia','GuiaController@index');
    Route::post('guia/consultar-ticket','Cpe\CpeController@consultarGRE');

});

Route::group(['middleware' => ['can:Producción']], function () {
//Rutan orden de producción
    Route::get('produccion/duplicar/{id}','ProduccionController@duplicar');
    Route::get('produccion/nuevo','ProduccionController@nueva_produccion');
    Route::get('produccion/editar/{id}','ProduccionController@editar');
    Route::get('produccion/obtener-correlativo','ProduccionController@obtenerCorrelativo');
    Route::delete('produccion/destroy/{id}','ProduccionController@destroy');
    Route::post('produccion/productos','ProduccionController@obtenerProductos');
    Route::post('produccion/obtenerClientes','ProduccionController@obtenerClientes');
    Route::post('produccion/store','ProduccionController@store');
    Route::post('produccion/update','ProduccionController@update');
    Route::get('produccion/editar/{id}','ProduccionController@editar');
    Route::get('produccion/imprimir/{id}','ProduccionController@imprimir');
    Route::delete('produccion/destroy/{id}','ProduccionController@destroy');
    Route::post('produccion/agregar-imagen', 'ProduccionController@agregar_imagen');
    Route::get('produccion/descargar-adjunto/{file}', 'ProduccionController@descargar_adjunto');
    Route::get('produccion/marcar-completado/{id}', 'ProduccionController@marcar_completado');
    Route::get('produccion/marcar-pendiente/{id}', 'ProduccionController@marcar_pendiente');
    Route::get('produccion/{estado}','ProduccionController@index');
    Route::get('produccion/nuevo-desde-cotizacion/{id}','ProduccionController@nuevo_desde_cotizacion');
});

Route::group(['middleware' => ['can:Créditos']], function () {

    Route::get('creditos', 'CreditoController@index');
    Route::get('creditos/editar/{id}', 'CreditoController@editar');
    Route::post('creditos/agregar_pago', 'CreditoController@agregar_pago');
    Route::post('creditos/ver_pagos', 'CreditoController@ver_pagos');
    Route::get('creditos/actualizar-pagos', 'CreditoController@actualizar_pagos');
});

//Ruta para consultar documentos
Route::get('consulta', 'ConsultaController@index');
Route::post('consulta/obtenerDocumento', 'ConsultaController@obtener_documento');
Route::get('consulta/descargar/{file}', 'ConsultaController@descargarArchivo');
Route::get('consulta/descargar-comprobante/{tipo}/{file}', 'ConsultaController@descargar_comprobante');
//Route::get('impresion/print-file/{file}','ConsultaController@printFile');

Route::group(['middleware' => ['can:Ventas']], function () {
    Route::get('notificaciones','NotificacionesController@index');
    Route::get('notificaciones/count','NotificacionesController@countNotificaciones');
    Route::get('notificaciones/marcar-como-leido/{id}','NotificacionesController@marcarComoLeido');
    Route::get('notificaciones/marcar-todo-como-leido','NotificacionesController@marcarTodoComoLeido');
    Route::get('notificaciones/obtener-notificaciones','NotificacionesController@obtenerNotificaciones');
});

Route::group(['middleware' => ['can:Inventario: almacenes']], function () {
    Route::get('almacenes','AlmacenController@index');
    Route::post('almacenes/store', 'AlmacenController@store');
    Route::post('almacenes/store-ubicacion', 'AlmacenController@storeUbicacion');
    Route::delete('almacenes/destroy/{id}', 'AlmacenController@destroy');
    Route::get('almacenes/show', 'AlmacenController@show');
    Route::get('almacenes/editar/{id}', 'AlmacenController@edit');
    Route::get('almacenes/editar-ubicacion/{id}', 'AlmacenController@editarUbicacion');
    Route::post('almacenes/update', 'AlmacenController@update');
    Route::post('almacenes/update-ubicacion', 'AlmacenController@updateUbicacion');
});

Route::group(['middleware' => ['can:Catalogos']], function () {
//Rutas para reportes
    Route::get('catalogos', 'CatalogoController@index');
    Route::get('catalogos/nuevo', 'CatalogoController@nuevo');
    Route::post('catalogos/store', 'CatalogoController@store');
    Route::post('catalogos/update', 'CatalogoController@update');
    Route::get('catalogos/editar/{id}', 'CatalogoController@editar');
    Route::get('catalogos/imprimir/{id}', 'CatalogoController@imprimir');
    Route::delete('catalogos/destroy/{id}', 'CatalogoController@destroy');
    Route::get('catalogos/duplicar/{id}', 'CatalogoController@duplicar');
});

//Helper
Route::get('helper/obtener-clientes/{search?}', 'Helpers\MainHelper@obtener_clientes');
Route::get('helper/obtener-proveedores/{search?}', 'Helpers\MainHelper@obtener_proveedores');
Route::get('helper/obtener-productos/{search?}', 'Helpers\MainHelper@obtener_productos');
Route::get('helper/agregar-producto/{search}', 'Helpers\MainHelper@agregar_producto');
Route::post('helper/buscar-ruc', 'Helpers\MainHelper@obtener_datos_usuario_de_sunat');
Route::post('helper/nuevo-cliente', 'Helpers\MainHelper@nuevo_cliente');
Route::get('helper/agregar-cliente/{id}', 'Helpers\MainHelper@agregar_cliente');
Route::get('helper/buscar-clientes/{search}', 'Helpers\MainHelper@buscar_clientes');
Route::get('helper/categorias', 'Helpers\MainHelper@categorias');
Route::get('helper/obtener-descuentos/{id}', 'Helpers\MainHelper@obtenerDescuentos');
Route::post('helper/guardar-producto', 'Helpers\MainHelper@guardarProducto');


Auth::routes(['register' => false]);
Route::post('login', 'Auth\LoginController@authenticate')->name('logueo');
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');

Route::get('/', 'HomeController@index')->name('home');
Route::get('home/stock', 'HomeController@obtener_stock_bajo');
Route::get('home/pagos', 'HomeController@obtener_pago_empleados');
Route::get('home/reporte', 'HomeController@obtenerReporte');
Route::get('home/creditos', 'HomeController@obtener_ventas_credito');

Route::get('tenant/crear-tenant', 'TenantController@crearTenant');
Route::get('tenant/eliminar-tenant', 'TenantController@eliminarTenant');
Route::get('tenant/mostrar-tenants', 'TenantController@mostrarTenants');
Route::get('tenant/config-cache', 'TenantController@guardarConfigTenant');

Route::get('logout', 'Auth\LoginController@logout')->name('logout');


Route::get('facturacion/generar-resumen/{anular}', 'Cpe\CpeController@generar_resumen_boletas');
Route::get('facturacion/status/{ticket}', 'Cpe\CpeController@getStatusResumenBoletas');
Route::get('facturacion/generar-boletas', 'Cpe\CpeController@generar_xml_boletas');

Route::group(['middleware' => ['auth']], function () {
    Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
});
