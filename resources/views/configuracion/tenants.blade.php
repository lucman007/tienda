<div class="col-lg-12 mb-2">
    <h4>Gestionar tenants</h4>
</div>
<div class="col-lg-6 mb-4 mb-lg-0">
    <form action="tenant/crear-tenant" method="GET" autocomplete="off">
        <div class="input-group">
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
        <div class="input-group">
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
        <div class="input-group">
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
<div class="col-lg-12 mb-2">
    <h4>Enviar aviso</h4>
</div>
<div class="col-lg-3">
    <select v-model="destino_socket" class="custom-select">
        <option value="1">Enviar a todos</option>
        <option value="2">Enviar a tenant</option>
    </select>
</div>
<div v-show="destino_socket == 2" class="col-lg-6">
    <input type="text" class="form-control" placeholder="Escribe la url del tenant: https://empresa.facturacion.xyz/" v-model="tenant_socket">
</div>
<div class="col-lg-3">
    <select v-model="clave_socket" class="custom-select">
        <option value="alerta_error_sunat">Error sunat</option>
        <option value="otro">Otro</option>
    </select>
</div>
<div class="col-lg-12 mt-2">
    <div class="input-group">
        <input type="text" class="form-control" placeholder="Redacta tu mensaje" v-model="aviso">
        <div class="input-group-append">
            <b-button @click="enviar" variant="success">
                <i class="fas fa-save"></i> Enviar
            </b-button>
        </div>
    </div>
</div>

