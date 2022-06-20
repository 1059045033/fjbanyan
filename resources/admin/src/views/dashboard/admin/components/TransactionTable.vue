<template>
  <el-table :data="list" style="width: 100%;padding-top: 15px;">
    <el-table-column label="公司" min-width="200">
      <template slot-scope="scope">
        {{ scope.row.company}}
      </template>
    </el-table-column>
    <el-table-column label="任务数" width="195" align="center">
      <template slot-scope="scope">
        {{ scope.row.tasks}}
      </template>
    </el-table-column>
    <el-table-column label="排名" width="100" align="center">
      <template slot-scope="{row}">
        <el-tag :type="row.status | statusFilter">
          {{ row.status }}
        </el-tag>
      </template>
    </el-table-column>
  </el-table>
</template>

<script>
import { transactionList } from '@/api/remote-search'

export default {
  filters: {
    statusFilter(status) {
      const statusMap = {
        1: 'danger',
        2: 'success',
        3: 'warning',
      }
      return statusMap[status]
    },
    orderNoFilter(str) {
      return str.substring(0, 30)
    }
  },
  data() {
    return {
      list: null
    }
  },
  created() {
    this.fetchData()
  },
  methods: {
    fetchData() {

      this.list = [
        {"company":"公司002","timestamp":1472712699293,"username":"Paul Lee","tasks":100,"status":"1"},
        {"company":"公司001","timestamp":1472712699293,"username":"Paul Lee","tasks":80,"status":"2"},
        {"company":"公司003","timestamp":1472712699293,"username":"Paul Lee","tasks":70,"status":"3"},
        {"company":"公司004","timestamp":1472712699293,"username":"Paul Lee","tasks":60,"status":"4"},
        {"company":"公司005","timestamp":1472712699293,"username":"Paul Lee","tasks":60,"status":"5"},
        ];
      //   transactionList().then(response => {
      //   this.list = response.data.items.slice(0, 8)
      // })
    }
  }
}
</script>
