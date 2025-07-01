<form action="comprobantes" method="GET" autocomplete="off" role="search">
    <input type="hidden" name="notasDeVenta" value="true">
    <div class="input-group" id="buscador">
        <input type="text" class="form-control" name="textoBuscado" placeholder="Buscar por cliente o correlativo..." value="{{$textoBuscado}}">
        <div class="input-group-append">
            <b-button variant="primary" type="submit" v-b-popover.hover.top="'Busca por cliente o correlativo'">
                <i class="fas fa-search"></i></button>
            </b-button>
        </div>
    </div>
</form>
