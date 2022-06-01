<template>
    <date-range-picker
            class="calendario"
            ref="picker"
            :opens="'center'"
            :locale-data="localeData"
            :minDate="minDate" :maxDate="maxDate"
            :singleDatePicker="false"
            :timePicker="false"
            :timePicker24Hour="false"
            :showWeekNumbers="false"
            :showDropdowns="true"
            :autoApply="true"
            :ranges="rangos"
            v-model="dateRange"
            @update="updateValues"
            :linkedCalendars="linkedCalendars"
            :dateFormat="dateFormat">
    </date-range-picker>
</template>
<script>
    import DateRangePicker from 'vue2-daterange-picker';
    import 'vue2-daterange-picker/dist/vue2-daterange-picker.css'
    export default {
        name:'range-calendar',
        components:{DateRangePicker},
        props:['inicio','fin'],
        data(){
            let startDate = new Date();
            let endDate = new Date();
            let today = new Date();
            today.setHours(0, 0, 0, 0);

            let yesterday = new Date();
            yesterday.setDate(today.getDate() - 1);
            yesterday.setHours(0, 0, 0, 0);
            return{
                localeData:{
                    direction: 'ltr',
                    format: 'dd/mm/yyyy',
                    separator: ' - ',
                    weekLabel: 'S',
                    customRangeLabel: 'Rango',
                    daysOfWeek: ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'],
                    monthNames: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    firstDay: 0
                },
                minDate:null,
                maxDate:null,
                dateRange: {
                    startDate:this.inicio,
                    endDate:this.fin,
                },
                linkedCalendars:false,
                dateFormat:null,
                date:null,
                rangos:{
                    'Hoy': [today, today],
                    'Ayer': [yesterday, yesterday],
                    'Este Mes': [new Date(today.getFullYear(), today.getMonth(), 1), new Date(today.getFullYear(), today.getMonth() + 1, 0)],
                    'Mes pasado': [new Date(today.getFullYear(), today.getMonth() - 1, 1), new Date(today.getFullYear(), today.getMonth(), 0)],
                    'Este año': [new Date(today.getFullYear(), 0, 1), new Date(today.getFullYear(), 11, 31)],
                    'Todo': [new Date(2017, 0, 1), today]
                }
            }
        },
        methods:{
            updateValues(){
                this.$emit('setparams',this.dateRange);
            }
        }
    }
</script>
<style>
    .calendario{
        width: 100%;
    }
    .calendario span{
        font-size:14px;
    }
    .reportrange-text{
        height: 36px;
    }
</style>