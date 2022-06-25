<template>
  <div :style="{height:height,width:width}">
    <div :class="className">
      <p class="date">{{ clock.date }}</p>
      <p class="time">{{ clock.time }}</p>
    </div>
  </div>
</template>

<script>
require('echarts/theme/macarons') // echarts theme
import resize from './mixins/resize'

export default {
  mixins: [resize],
  props: {
    className: {
      type: String,
      default: 'clock'
    },
    width: {
      type: String,
      default: '100%'
    },
    height: {
      type: String,
      default: '100px'
    },
    currentData: {
      type: String,
      default: ''
    }
    // chartData: {
    //   type: Array,
    //   required: true
    // },
  },
  data() {
    return {
      chart: null,
      week: ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
      timerID: undefined,
      date: undefined,
      time: undefined,
      clock: {
        time: '',
        date: ''
      }

    }
  },
  watch: {
    // chartData: {
    //   deep: true,
    //   handler(val) {
    //     console.log('pieChart :',val)
    //     this.setOptions(val)
    //   }
    // }
    currentData: {
      deep: true,
      handler(val) {
        console.log('currentData :', val)
        this.setOptions(val)
      }
    }
  },
  mounted() {
    this.$nextTick(() => {
      this.init()
    })
  },
  beforeDestroy() {
  },
  methods: {
    sing(v) {
      console.log('ffff = ', v)
      this.setOptions(v)
    },
    init() {
      const _that = this
      this.timerID = setInterval(function() {
        const cd = new Date()
        _that.clock.time = _that.zeroPadding(cd.getHours(), 2) + ':' + _that.zeroPadding(cd.getMinutes(), 2) + ':' + _that.zeroPadding(cd.getSeconds(), 2)
        _that.clock.date = _that.zeroPadding(cd.getFullYear(), 4) + '-' + _that.zeroPadding(cd.getMonth() + 1, 2) + '-' + _that.zeroPadding(cd.getDate(), 2) + ' ' + _that.week[cd.getDay()]
        // console.log(_that.clock.time);
      }, 1000)
    },
    zeroPadding(num, digit) {
      var zero = ''
      for (var i = 0; i < digit; i++) {
        zero += '0'
      }
      return (zero + num).slice(-digit)
    },
    setOptions(v) {
      const _that = this
      if (this.timerID != undefined) {
        clearInterval(this.timerID)
      }
      const currt_ = v
      console.log('当前修改的时间为 :', currt_)

      const cds = new Date(currt_)
      this.timerID = setInterval(function() {
        const cd = new Date()
        _that.clock.time = _that.zeroPadding(cd.getHours(), 2) + ':' + _that.zeroPadding(cd.getMinutes(), 2) + ':' + _that.zeroPadding(cd.getSeconds(), 2)
        _that.clock.date = _that.zeroPadding(cds.getFullYear(), 4) + '-' + _that.zeroPadding(cds.getMonth() + 1, 2) + '-' + _that.zeroPadding(cds.getDate(), 2) + ' ' + _that.week[cds.getDay()]
        // console.log(_that.clock.time);
      }, 1000)
    }

  }
}
</script>

<style>
  html, body {
    height: 100%;
  }

  body {
    background: #0f3854;
    background: radial-gradient(ellipse at center, #0a2e38 0%, #000000 70%);
    background-size: 100%;
  }

  p {
    margin: 0;
    padding: 0;
  }

  .clock {
    font-family: "Share Tech Mono", monospace;
    color: #ffffff;
    text-align: center;
    position: relative;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    color: #daf6ff;
    text-shadow: 0 0 20px #0aafe6, 0 0 20px rgba(10, 175, 230, 0);
  }
  .clock .time {
    letter-spacing: 0.05em;
    font-size: 80px;
    padding: 5px 0;
  }
  .clock .date {
    letter-spacing: 0.1em;
    font-size: 24px;
  }
  .clock .text {
    letter-spacing: 0.1em;
    font-size: 12px;
    padding: 20px 0 0;
  }
</style>
