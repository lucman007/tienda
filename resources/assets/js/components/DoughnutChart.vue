<template>
    <v-chart class="chart" :option="option" :setOption="option"/>
</template>
<script>
    import { use } from "echarts/core";
    import { TooltipComponent, LegendComponent } from 'echarts/components';
    import { PieChart } from 'echarts/charts';
    import { LabelLayout } from 'echarts/features';
    import { CanvasRenderer } from 'echarts/renderers';
    import VChart from "vue-echarts";
    import { UPDATE_OPTIONS_KEY } from 'vue-echarts'
    use([TooltipComponent,LegendComponent,PieChart,CanvasRenderer,LabelLayout]);

    export default {
        name: "doughnut-chart",
        props:['chartData', 'labels'],
        components: {
            VChart
        },
        data() {
            return {
                option: {
                    tooltip: {
                        trigger: 'item'
                    },
                    series: [
                        {
                            type: 'pie',
                            radius: ['35%', '90%'],
                            avoidLabelOverlap: false,
                            label: {
                                show: false,
                                position: 'center'
                            },
                            emphasis: {
                                label: {
                                    show: true,
                                    fontSize: '20',
                                    fontWeight: 'bold'
                                }
                            },
                            data: this.chartData
                        }
                    ]
                },
            };
        },
    };
</script>

<style scoped>
    .chart {
        height: 320px;
        padding: 0
    }
</style>