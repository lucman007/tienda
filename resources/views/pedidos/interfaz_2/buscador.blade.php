<form action="pedidos" method="GET" autocomplete="off" role="search">
    <div class="input-group" id="buscador">
        <input type="text" class="form-control" name="textoBuscado" placeholder="Buscar por cliente o N° de pedido..." value="{{$textoBuscado}}">
        <div class="input-group-append">
            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
        </div>
    </div>
</form>
