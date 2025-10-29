<template>
    <div>
        <b-modal id="modal-whatsapp" ref="modal-whatsapp">
            <template slot="modal-title">
                Enviar por Whatsapp
            </template>
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <input-whatsapp :params="params" :codigos="codigos" :link="link"></input-whatsapp>
                    </div>
                </div>
            </div>
            <template #modal-footer="{ ok, cancel}">
                <b-button variant="secondary" @click="cancel()">Cancelar</b-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
    export default {
        name: 'modal-whatsapp',
        props: ['link','params','codigos'],
        data() {
            return {
                whatsapp:''
            }
        },
        methods: {
        }
    }
</script>