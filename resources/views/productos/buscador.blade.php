<form action="{{url()->full()}}" method="GET" autocomplete="off" role="search">
    <div class="input-group" id="buscador">
        <input type="text" class="form-control" name="textoBuscado" placeholder="Buscar..." v-model="search">
        <div class="input-group-append">
            <b-dropdown variant="outline-secondary" class="variant-alt" text="Filtro">
                <b-dropdown-item :href="'?textoBuscado='+search+'&filtro=categoria'">Categoría</b-dropdown-item>
                <b-dropdown-item :href="'?textoBuscado='+search+'&filtro=marca'">Marca</b-dropdown-item>
                <b-dropdown-item :href="'?textoBuscado='+search+'&filtro=param_1'">Montaje</b-dropdown-item>
                <b-dropdown-item :href="'?textoBuscado='+search+'&filtro=param_2'">Cápsula</b-dropdown-item>
                <b-dropdown-item :href="'?textoBuscado='+search+'&filtro=param_3'">Tipo</b-dropdown-item>
            </b-dropdown>
            <b-button variant="primary" type="submit" v-b-popover.hover.top="'Busca por nombre o código de producto'">
                <i class="fas fa-search"></i>
            </b-button>
        </div>
    </div>
</form>
