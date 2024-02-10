@php
    switch ($filtro){
        case 'param_1':
            $name_filtro = 'Montaje';
            break;
        case 'param_2':
            $name_filtro = 'Capsula';
            break;
        case 'param_3':
            $name_filtro = 'Tipo';
            break;
        default:
            $name_filtro = $filtro;
    }
@endphp
<form action="productos" method="GET" autocomplete="off" role="search">
    <div class="input-group" id="buscador">
        <input type="text" class="form-control" name="textoBuscado" placeholder="Buscar por {{$name_filtro=='Filtro'?'nombre, código o características':$name_filtro}}..." v-model="search" @keydown="buscar">
        <div class="input-group-append">
            <b-dropdown variant="{{$filtro == 'Filtro'?'outline-secondary':'success'}}" class="variant-alt" text="{{ucfirst($name_filtro)}}">
                <b-dropdown-item :href="'?textoBuscado='+search+'&filtro=categoria'">Categoría</b-dropdown-item>
                <b-dropdown-item :href="'?textoBuscado='+search+'&filtro=marca'">Marca</b-dropdown-item>
                <b-dropdown-item :href="'?textoBuscado='+search+'&filtro=param_1'">Montaje</b-dropdown-item>
                <b-dropdown-item :href="'?textoBuscado='+search+'&filtro=param_2'">Cápsula</b-dropdown-item>
                <b-dropdown-item :href="'?textoBuscado='+search+'&filtro=param_3'">Tipo</b-dropdown-item>
                @if($filtro != 'Filtro')
                <b-dropdown-item :href="'/productos'"><span style="color:red">Quitar filtro</span></b-dropdown-item>
                @endif
            </b-dropdown>
            <b-button variant="primary" type="button" :href="'{{$filtro}}'=='Filtro'?'?textoBuscado='+encodeURIComponent(search):'?textoBuscado='+encodeURIComponent(search)+'&filtro={{$filtro}}'">
                <i class="fas fa-search"></i>
            </b-button>
        </div>
    </div>
</form>
