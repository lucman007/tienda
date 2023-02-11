<template>
    <table class="table table-striped table-hover table-sm">
        <thead class="bg-custom-green">
        <tr>
            <th scope="col" style="width: 10px"></th>
            <th scope="col" style="width: 250px">Producto</th>
            <th scope="col" style="width: 350px">Caracteristicas</th>
            <th scope="col" style="width: 100px">Precio</th>
            <th scope="col" style="width: 100px">Cantidad</th>
            <th scope="col" style="width: 80px; text-align: center">Dscto</th>
            <th scope="col" style="width: 80px; text-align: center">Subtotal</th>
            <th scope="col" style="width: 80px; text-align: center">Igv</th>
            <th scope="col" style="width: 100px">Total</th>
            <th scope="col" style="width: 50px"></th>
        </tr>
        </thead>
        <draggable :list="productos" :element="'tbody'" :options="{animation:200}">
            <tr v-for="(producto,index) in productos" :key="producto.index" style="cursor: move">
                <td></td>
                <td>{{producto.nombre}}</td>
                <td style="white-space: break-spaces" class="text_desc">{{producto.presentacion}}</td>
                <td>{{producto.precio}}</td>
                <td>{{producto.cantidad}}</td>
                <td class="text-center">{{producto.tipo_descuento?producto.porcentaje_descuento+'%':(Number(producto.descuento)).toFixed(2)}}</td>
                <td class="text-center">{{(Number(producto.subtotal)).toFixed(2)}}</td>
                <td class="text-center">{{(Number(producto.igv)).toFixed(2)}}</td>
                <td>{{(Number(producto.total)).toFixed(2)}}</td>
                <td class="botones-accion" style="width: 120px">
                </td>
            </tr>
        </draggable>
    </table>
</template>

<script>
    import draggable from 'vuedraggable'
    export default {
        components: {
            draggable,
        },
        name: 'table-draggable',
        props: ['productos'],
        data() {
            return {
            }
        },
        methods: {

        }
    }
</script>