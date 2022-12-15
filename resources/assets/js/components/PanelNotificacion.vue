<template>
    <div>
        <div id="notificacion-panel" class="ml-2 mr-2" style="position: relative">
            <a class="notificacion" @click="obtenerNotificaciones">
                <i class="fas fa-bell"></i>
                <span v-show="numNotificaciones > 0" class="badge badge-danger" style="position: absolute">{{ numNotificaciones }}</span>
            </a>
            <div v-show="openPanel" class="dropdown-menu dropdown-menu-end dropdown-menu-card
                                dropdown-menu-notification dropdown-caret-bg show"
                 aria-labelledby="navbarDropdownNotification" data-bs-popper="none">
                <div class="card card-notification shadow-none">
                    <div class="card-header">
                        <div class="row justify-content-between align-items-center">
                            <div class="col-auto">
                                <h6 class="card-header-title mb-0">Notificaciones</h6>
                            </div>
                            <div class="col-auto ps-0 ps-sm-3"><a class="card-link fw-normal" href="javascript:void(0)" @click="marcar_todo">Marcar todo como leído</a></div>
                        </div>
                    </div>
                    <div class="scrollbar-overlay os-host os-theme-dark os-host-resize-disabled os-host-scrollbar-horizontal-hidden os-host-transition os-host-overflow os-host-overflow-y"
                         style="max-height:19rem">
                        <div class="os-resize-observer-host observed">
                            <div class="os-resize-observer" style="left: 0px; right: auto;"></div>
                        </div>
                        <div class="os-size-auto-observer observed" style="height: calc(100% + 1px); float: left;">
                            <div class="os-resize-observer"></div>
                        </div>
                        <div class="os-content-glue" style="margin: 0px; height: 515px; width: 317px;"></div>
                        <div class="os-padding">
                            <div class="os-viewport os-viewport-native-scrollbars-invisible" style="overflow-y: scroll;">
                                <div class="os-content" style="padding: 0px; height: auto; width: 100%;">
                                    <div class="list-group list-group-flush fw-normal fs--1">
                                        <div class="list-group-item" v-for="notificacion in notificaciones">
                                            <a class="notification notification-flush notification-unread" :href="'/notificaciones/marcar-como-leido/'+notificacion.id">
                                                <div class="notification-body" :style="{opacity:notificacion.read_at == null?'1':'0.6'}">
                                                    <p class="mb-1">
                                                        <span v-html="notificacion.titulo"></span>
                                                        <span v-html="notificacion.extracto"></span>
                                                    </p>
                                                    <span class="notification-time"><i
                                                            class="fas fa-calendar-alt"></i> {{ notificacion.fecha }}</span>
                                                    <i class="fas fa-circle" v-show="notificacion.read_at == null"></i>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="notificaciones.length == 0" class="text-center pt-5">No tienes notificaciones</div>
                            </div>
                        </div>
                        <div class="os-scrollbar os-scrollbar-horizontal os-scrollbar-unusable os-scrollbar-auto-hidden">
                            <div class="os-scrollbar-track os-scrollbar-track-off">
                                <div class="os-scrollbar-handle" style="transform: translate(0px, 0px); width: 100%;"></div>
                            </div>
                        </div>
                        <div class="os-scrollbar os-scrollbar-vertical os-scrollbar-auto-hidden">
                            <div class="os-scrollbar-track os-scrollbar-track-off">
                                <div class="os-scrollbar-handle" style="transform: translate(0px, 0px); height: 59.0291%;"></div>
                            </div>
                        </div>
                        <div class="os-scrollbar-corner"></div>
                    </div>
                    <div class="card-footer text-center border-top"><a class="card-link d-block"
                                                                       href="/notificaciones">Ver todo</a></div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        name: 'panel-notificacion',
        data() {
            return {
                numNotificaciones:0,
                notificaciones:[],
                openPanel: false,
                numComprobantes:11,
                disabledVentas:false,
            }
        },
        created(){
            this.countNotifications();
            window.addEventListener('click', e => {
                if (!document.getElementById('notificacion-panel').contains(e.target)) {
                    this.openPanel = false;
                }
            });
        },
        methods:{
            obtenerNotificaciones(){
                this.openPanel = true;
                axios.get('/notificaciones/obtener-notificaciones')
                    .then(response => {
                        this.notificaciones = response.data;
                    })
                    .catch(error => {
                        console.log(error);
                    });
            },
            countNotifications(){
                axios.get('/notificaciones/count')
                    .then(response => {
                        this.numNotificaciones = response.data;
                    })
                    .catch(error => {
                        console.log(error);
                    });
            },
            marcar_todo(){
                axios.get('/notificaciones/marcar-todo-como-leido?axios=true')
                    .then(response => {
                        this.notificaciones = response.data;
                        this.countNotifications();
                    })
                    .catch(error => {
                        console.log(error);
                    });
            }
        }
    }
</script>
