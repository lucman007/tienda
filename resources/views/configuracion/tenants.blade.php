
<div class="col-lg-6 mb-4 mb-lg-0">
    <form action="tenant/crear-tenant" method="GET" autocomplete="off">
        <div class="input-group" id="buscador">
            <input type="text" class="form-control" name="dominio" placeholder="empresa.facturacion.xyz">
            <div class="input-group-append">
                <b-button variant="primary" type="submit">
                    <i class="fas fa-plus"></i> Crear inquilino
                </b-button>
            </div>
        </div>
        @if (\Session::has('mensaje_crear'))
            <div class="alert alert-info mt-3">{{ \Session::get('mensaje_crear') }}</div>
        @endif
    </form>
</div>
<div class="col-lg-6 mb-4 mb-lg-0">
    <form action="tenant/eliminar-tenant" method="GET" autocomplete="off">
        <div class="input-group" id="buscador">
            <input type="text" class="form-control" name="dominio" placeholder="empresa.facturacion.xyz">
            <div class="input-group-append">
                <b-button variant="danger" type="submit">
                    <i class="fas fa-trash-alt"></i> Eliminar inquilino
                </b-button>
            </div>
        </div>
        @if (\Session::has('mensaje_eliminar'))
            <div class="alert alert-info mt-3">{{ \Session::get('mensaje_eliminar') }}</div>
        @endif
    </form>
</div>
<div class="col-lg-6 mb-4 my-lg-5">
    <form action="tenant/config-cache" method="GET" autocomplete="off">
        <div class="input-group" id="buscador">
            <input type="text" class="form-control" name="dominio" placeholder="empresa.facturacion.xyz">
            <div class="input-group-append">
                <b-button variant="success" type="submit">
                    <i class="fas fa-save"></i> config cache
                </b-button>
            </div>
        </div>
        @if (\Session::has('mensaje_config'))
            <div class="alert alert-info mt-3">{{ \Session::get('mensaje_config') }}</div>
        @endif
    </form>
</div>
<div class="col-lg-6 mb-4 my-lg-5">
    <form action="tenant/mostrar-tenants" method="GET" autocomplete="off">
        <b-button variant="primary" type="submit">
            <i class="fas fa-list"></i> Mostrar inquilinos
        </b-button>
        @if (\Session::has('tenants_list'))
            @php
                $tenants = \Session::get('tenants_list');
                $i = 1;
            @endphp
            @foreach($tenants as $tenant)
                <div class="alert alert-info mt-3">{{$i.'. '.$tenant}}</div>
                @php
                    $i++;
                @endphp
            @endforeach
        @endif
    </form>
</div>

