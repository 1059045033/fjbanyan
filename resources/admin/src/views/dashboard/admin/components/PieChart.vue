<template>
  <div :class="className" :style="{height:height,width:width}" />
</template>

<script>
import echarts from 'echarts'
require('echarts/theme/macarons') // echarts theme
import resize from './mixins/resize'

export default {
  mixins: [resize],
  props: {
    className: {
      type: String,
      default: 'chart'
    },
    width: {
      type: String,
      default: '100%'
    },
    height: {
      type: String,
      default: '300px'
    },
    chartData: {
      type: Array,
      required: true
    },
  },
  watch: {
    chartData: {
      deep: true,
      handler(val) {
        //console.log('pieChart :',val)
        this.setOptions(val)
      }
    }
  },
  data() {
    return {
      chart: null
    }
  },
  mounted() {
    this.$nextTick(() => {
      this.initChart()
    })
  },
  beforeDestroy() {
    if (!this.chart) {
      return
    }
    this.chart.dispose()
    this.chart = null
  },
  methods: {
    initChart() {
      this.chart = echarts.init(this.$el, 'macarons')
      this.setOptions(this.chartData)

    },
    setOptions(pieData) {
        this.chart.setOption({
            title:{
              show:true,
              text:'网格组缺岗统计',
              textStyle:{
                color:'#333',
                fontWeight : 'bolder',
              }
            },
            tooltip: {
              trigger: 'item',
              formatter: '{a} <br/>{b} : {c} ({d}%)'
            },
            // legend: {
            //   left: 'center',
            //   bottom: '10',
            //   data: ['公司02', '公司01', '公司03', '公司04', '公司05','其他公司']
            // },
            series: [
              {
                name: '缺岗网格个数',
                type: 'pie',
                roseType: 'radius',
                radius: [15, 95],
                center: ['50%', '38%'],
                data: pieData,
                animationEasing: 'cubicInOut',
                animationDuration: 2600
              }
            ]
          })

    }


  }
}
</script>
