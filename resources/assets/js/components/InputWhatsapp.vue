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
        props: ['text','link','codigos'],
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
            enviarWhatsapp(){
                if(this.whatsapp==""){
                    alert('Ingresa un número válido')
                } else {
                    let codigoPais = this.codigoPais.replace(/\+/g, '');
                    window.open(this.link+'/send/?phone='+codigoPais+this.whatsapp+'&text='+this.text+'&app_absent=1', '_blank');
                }
            },
            reset(){
                this.whatsapp = '';
                this.codigoPais = '+51';
            },
        }
    }
</script>

<style>
    .codigo_pais .dropdown-item{
        padding-top: 3px;
        padding-bottom: 3px;
    }
</style>