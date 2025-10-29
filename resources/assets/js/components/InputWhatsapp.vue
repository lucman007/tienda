<template>
    <div>
        <b-input-group class="codigo_pais">
            <template #prepend>
                <b-dropdown :text="codigoPais" variant="outline-secondary">
                    <b-dropdown-item v-for="item, index in codigos" :key="index"
                                     @click="setCodigoPais(item['num_val'])">{{item['label']}}
                    </b-dropdown-item>
                </b-dropdown>
            </template>
            <input @keyup.enter="enviarWhatsapp" onfocus="this.select()" v-model="whatsapp" type="number" class="form-control" placeholder="Enviar a whatsapp">
            <b-input-group-append>
                <b-button
                        @click="enviarWhatsapp" target="_blank" variant="success">
                    <i class="fab fa-whatsapp"></i> Enviar
                </b-button>
            </b-input-group-append>
        </b-input-group>
    </div>
</template>
<script>
    export default{
        name: 'input-whatsapp',
        props: ['link','params','codigos'],
        data(){
            return {
                whatsapp:'',
                codigoPais:'+51'
            }
        },
        methods: {
            setCodigoPais(codigo){
                this.codigoPais = codigo;
            },
          enviarWhatsapp() {
            const telefono = this.codigoPais.replace('+', '') + this.whatsapp

            let mensaje = "";

            if(this.params.esCotizacion){
              mensaje = `¡Hola! 😃  Descarga tu cotización aquí: 👇🏻\n\n✅ ${this.params.pdf}`
            } else {
              mensaje = `¡Hola! 😃  Descarga tu comprobante aquí: 👇🏻\n\n✅ PDF: ${this.params.pdf}`
            }

            if (!this.params.usarPortal) {
              if(this.params.xml){
                mensaje += `\n\n✅ XML: ${this.params.xml}`
              }
              if(this.params.cdr){
                mensaje += `\n\n✅ CDR: ${this.params.cdr}`
              }
            }

            mensaje += `\n\n*${this.params.empresa}*`

            mensaje += `\n\n_ℹ️ ¡Recuerda que los links son válidos solo por 24 horas! ⏳_`

            const encoded = encodeURIComponent(mensaje)
            const url = `${this.link}/send?phone=${telefono}&text=${encoded}&app_absent=1`
            window.open(url, '_blank')
          },
          reset() {
              this.whatsapp = '';
              this.codigoPais = '+51';
            }
        }
    }
</script>

<style>
    .codigo_pais .dropdown-item{
        padding-top: 3px;
        padding-bottom: 3px;
    }
</style>