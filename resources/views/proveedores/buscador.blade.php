<form action="proveedores" method="GET" autocomplete="off" role="search">
    <div class="input-group" id="buscador">
        <input type="text" class="form-control" name="textoBuscado" placeholder="Buscar..." value="{{$textoBuscado}}">
        <div class="input-group-append">
            <b-button variant="primary" type="submit" v-b-popover.hover.top="'Busca por nombre o código de proveedor'">
                <i class="fas fa-search"></i></button>
            </b-button>
        </div>
    </div>
</form>
