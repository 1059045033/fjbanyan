<template>
  <div class="dashboard-editor-container">
    <!--    <github-corner class="github-corner" />-->
    <div class="filter-container">
      <el-date-picker v-model="listQuery.start_date" type="date" value-format="yyyy-MM-dd" placeholder="请选择一个时间" @change="handleFilter" />
      <!--      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" style="padding-top: 8px;" @click="handleFilter">-->
      <!--        搜索-->
      <!--      </el-button>-->
    </div>

    <el-row :gutter="32">
      <el-col :xs="24" :sm="24" :lg="8">
        <div class="chart-wrapper" style="background-color: #0f3854" :current-data="listQuery.start_date">
          <date-clock ref="child" />
        </div>
        <div class="chart-wrapper">
          <pie-chart :chart-data="pieChartData" />
        </div>
      </el-col>
      <el-col :xs="48" :sm="48" :lg="16">
        <div class="chart-wrapper">
          <bar-chart :chart-data="barChartData" />
        </div>
      </el-col>
    </el-row>

    <panel-group v-if="false" @handleSetLineChartData="handleSetLineChartData" />

    <el-row v-if="false" style="background:#fff;padding:16px 16px 0;margin-bottom:32px;">
      <el-select v-if="false" v-model="company" placeholder="公司" clearable class="filter-item" style="width: 130px" @change="changeLineCart">
        <el-option v-for="item in companyOptions" :key="item.id" :label="item.name" :value="item.id" />
      </el-select>
      <line-chart :chart-data="lineChartData" />
    </el-row>

    <el-row v-if="false" style="background:#fff;padding:16px 16px 0;margin-bottom:32px;">
      <pie-chart :chart-data="pieChartData" />
    </el-row>

    <el-row style="background:#fff;padding:16px 16px 0;margin-bottom:32px;">
      <div class="chart-wrapper">
        <line-chart :chart-data="lineChartData" />
      </div>
    </el-row>

    <el-row v-if="false" :gutter="32">
      <el-col :xs="48" :sm="48" :lg="16">
        <div class="chart-wrapper">
          <transaction-table />
        </div>
      </el-col>
      <el-col :xs="24" :sm="24" :lg="8">
        <div class="chart-wrapper">
          <pie-chart />
        </div>
      </el-col>
    </el-row>

  </div>
</template>

<script>

import PanelGroup from './components/PanelGroup'
import LineChart from './components/LineChart'
import RaddarChart from './components/RaddarChart'
import PieChart from './components/PieChart'
import DateClock from './components/DateClock'
import BarChart from './components/BarChart'
import TransactionTable from './components/TransactionTable'
import TodoList from './components/TodoList'
import BoxCard from './components/BoxCard'
import { getAllCompany, getdashboradAttendances, getdashboradRegionNoBody, getdashboradlateEarly } from '@/api/common'
import { parseTime } from '@/utils'
import waves from '@/directive/waves' // waves directive

const lineChartData = {
  newVisitis: {
    expectedData: [100, 120, 161, 134, 105, 160, 165, 10, 0],
    actualData: [190, 82, 91, 154, 162, 140, 145, 188, 192],
    xAxisData: ['福州勘测有限公司', '青桔单车', '美团单车', '哈罗单车', '福州市共享单车行业自律会', '福州诚辉保安服务有限公司', '福建天安保安服务有限公司', '福州市城管委', '泉州钧泰保安服务有限公司']
  }
}

const pieChartData = {
  newVisitis: [
    { value: 0, name: '网格01' },
    { value: 0, name: '网格02' }
  ]
}

const barChartData = {
  newVisitis: {
    lateData: [0, 120, 161, 134, 105, 160, 165, 10, 0],
    earlyData: [0, 82, 91, 154, 162, 140, 145, 188, 192],
    xAxisData: ['福州勘测有限公司', '青桔单车', '美团单车', '哈罗单车', '福州市共享单车行业自律会', '福州诚辉保安服务有限公司', '福建天安保安服务有限公司', '福州市城管委', '泉州钧泰保安服务有限公司']
  }
}

const companyOptions = [
  { id: '1', name: 'newVisitis' },
  { id: '2', name: 'messages' },
  { id: '3', name: 'purchases' },
  { id: '4', name: 'shoppings' }
]
// arr to obj, such as { CN : "China", US : "USA" }
const companyKeyValue = companyOptions.reduce((acc, cur) => {
  acc[cur.id] = cur.name
  return acc
}, {})

export default {
  name: 'DashboardAdmin',
  components: {
    // GithubCorner,
    PanelGroup,
    LineChart,
    RaddarChart,
    PieChart,
    BarChart,
    TransactionTable,
    TodoList,
    BoxCard,
    DateClock

  },
  directives: { waves },
  data() {
    return {
      lineChartData: lineChartData.newVisitis,
      pieChartData: pieChartData.newVisitis,
      barChartData: barChartData.newVisitis,
      // start_date: new Date(),
      companyOptions,
      company: undefined,
      // xAxisData: ['福州勘测有限公司', '青桔单车', '美团单车', '哈罗单车', '福州市共享单车行业自律会', '福州诚辉保安服务有限公司', '福建天安保安服务有限公司', '福州市城管委', '泉州钧泰保安服务有限公司'],
      companies: [],
      listQuery: {
        start_date: new Date()
      },
      timerID: undefined,
      currentDate: parseTime(new Date(), '{y}-{m}-{d}')
    }
  },
  created() {
    getAllCompany(this.listQuery).then(response => {
      this.companies = response.data
    })

    // 获取考勤数据
    this.getDashborad()
  },
  methods: {
    handleFilter() {
      // 选择日期
      this.$refs.child.sing(this.listQuery.start_date)
      if (this.timerID != undefined && this.currentDate != this.listQuery.start_date) {
        clearInterval(this.timerID)
        this.updateDate()
      } else {
        // 重新获取数据
        this.getDashborad()
      }
    },
    getDashborad() {
      const _that = this
      let ii = 0
      this.timerID = setInterval(function() {
        _that.updateDate(_that.listQuery)
        ii++
        if (ii > 300) {
          //clearInterval(this.timerID)
        }
      }, 5000)
    },
    updateDate() {
      console.log('获取 数据=============')
      this.listQuery.start_date = parseTime(this.listQuery.start_date)

      getdashboradAttendances(this.listQuery).then(response => {
        if (response.code == 200) {
          this.lineChartData.expectedData = response.data.chuqin
          this.lineChartData.actualData = response.data.nochuqin
          this.lineChartData.xAxisData = response.data.xAxisData
        }
      })

      getdashboradRegionNoBody(this.listQuery).then(response => {
        if (response.code == 200) {
          this.pieChartData = response.data
        }
      })

      getdashboradlateEarly(this.listQuery).then(response => {
        if (response.code == 200) {
          this.barChartData.lateData = response.data.late
          this.barChartData.earlyData = response.data.early
          this.barChartData.xAxisData = response.data.xAxisData
        }
      })
    },
    handleSetLineChartData(type) {
      this.lineChartData = lineChartData[type]
    },
    changeLineCart(e) {
      this.lineChartData = lineChartData[companyKeyValue[e]]
    }
  }
}
</script>

<style lang="scss" scoped>
.dashboard-editor-container {
  padding: 32px;
  background-color: rgb(240, 242, 245);
  position: relative;

  .github-corner {
    position: absolute;
    top: 0px;
    border: 0;
    right: 0;
  }

  .chart-wrapper {
    background: #fff;
    padding: 16px 16px 0;
    margin-bottom: 32px;
  }
}

@media (max-width:1024px) {
  .chart-wrapper {
    padding: 8px;
  }
}
</style>
