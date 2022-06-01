<template>
    <v-chart class="chart" :option="option" />
</template>
<script>
    import { use } from "echarts/core";
    import { GridComponent } from 'echarts/components';
    import { LineChart } from 'echarts/charts';
    import { UniversalTransition } from 'echarts/features';
    import { CanvasRenderer } from 'echarts/renderers';
    import VChart from "vue-echarts";
    use([GridComponent, LineChart, CanvasRenderer, UniversalTransition]);

    export default {
        name: "line-chart",
        props:['chartData', 'labels'],
        components: {
            VChart
        },
        data() {
            return {
                option: {
                    grid: {
                        left: '2%',
                        right: '2%',
                        bottom: '2%',
                        top:'3%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'category',
                        data: this.labels
                    },
                    yAxis: {
                        type: 'value'
                    },
                    series: this.chartData
                }
            };
        }
    };
</script>

<style scoped>
    .chart {
        height: 320px;
        padding: 0
    }
</style>