<template>
    <div>
        <b-modal id="modal-whatsapp" ref="modal-whatsapp" @hidden="whatsapp = ''">
            <template slot="modal-title">
                Enviar por Whatsapp
            </template>
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <p>Ingresa el código de país (Perú = 51) + el número de celular de tu cliente, ejemplo: 51996861131</p>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <input v-model="whatsapp" type="number" class="form-control" placeholder="Enviar a whatsapp">
                            <b-button @click="enviarWhatsapp(whatsapp)" target="_blank"  variant="success" class="boton_adjunto" style="top:0">
                                <i class="fab fa-whatsapp"></i> Enviar
                            </b-button>
                        </div>
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
        props: ['text','link'],
        data() {
            return {
                whatsapp:''
            }
        },
        methods: {
            enviarWhatsapp(numero){
                if(numero==""){
                    alert('Ingresa un número válido')
                } else {
                    if(numero.includes('+')){
                        numero.replace('+', '')
                    }
                    window.open(this.link+'/send/?phone='+numero+'&text='+this.text+'&app_absent=1', '_blank');
                }
            }
        }
    }
</script>