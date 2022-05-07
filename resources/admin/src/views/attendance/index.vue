<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="listQuery.name" placeholder="人员名字" style="width: 200px;padding-top: 8px;" class="filter-item" @keyup.enter.native="handleFilter" />

      <el-date-picker v-model="listQuery.start_date" type="date" value-format="yyyy-MM-dd" placeholder="请选择一个时间"/>

      <span style="padding-left: 10px" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        搜索
      </el-button>

    </div>
    <!--  ============= Table 表内容 start =================  -->
    <el-table
      :key="tableKey"
      v-loading="listLoading"
      :data="list"
      border
      fit
      highlight-current-row
      style="width: 100%;"
      @sort-change="sortChange"
    >
      <el-table-column label="ID" prop="id" sortable="custom" align="center" width="80" :class-name="getSortClass('id')">
        <template slot-scope="{row}">
          <span>{{ row.id }}</span>
        </template>
      </el-table-column>

      <el-table-column label="姓名" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.user_name }}</span>
        </template>
      </el-table-column>

      <el-table-column label="手机号" min-width="100px">
        <template slot-scope="{row}">
          <span>{{ row.user_phone }}</span>
        </template>
      </el-table-column>

      <el-table-column label="公司" min-width="200px">
        <template slot-scope="{row}">
          <span>{{ row.user_company }}</span>
        </template>
      </el-table-column>

      <el-table-column label="所属区域" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.user_region }}</span>
        </template>
      </el-table-column>

      <el-table-column label="工作区域" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.user_work_region }}</span>
        </template>
      </el-table-column>

      <el-table-column label="上线时间" min-width="150px">
        <template slot-scope="{row}">
          <p v-for="item in  row.online_times_arr">{{ item | timestringFilter }}</p>
        </template>
      </el-table-column>

      <el-table-column label="下线时间" min-width="150px">
        <template slot-scope="{row}">
<!--          <span>{{ row.offline_times }}</span>-->
          <p v-for="item in  row.offline_times_arr">{{ item | timestringFilter }}</p>
        </template>
      </el-table-column>

      <el-table-column label="任务完成量" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.task_complete_nums }}</span>
        </template>
      </el-table-column>

      <el-table-column label="任务完成度" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.task_progress }}</span>
        </template>
      </el-table-column>

      <el-table-column label="迟到次数" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.late_nums }}</span>
        </template>
      </el-table-column>

      <el-table-column label="早退次数" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.early_nums }}</span>
        </template>
      </el-table-column>

      <el-table-column label="任务断档次数" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.task_dd_nums }}</span>
        </template>
      </el-table-column>

      <el-table-column label="是否有人出勤网格" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.region_not_user_nums }}</span>
        </template>
      </el-table-column>

      <el-table-column label="扣款" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.money }}</span>
        </template>
      </el-table-column>

      <el-table-column label="扣款说明" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.money_details }}</span>
        </template>
      </el-table-column>

      <el-table-column v-if="false" label="操作" align="center" width="230" class-name="small-padding fixed-width">
        <template slot-scope="{row,$index}">
          <el-button size="mini" type="danger" @click="handleDelete(row,$index)">
            删除
          </el-button>
        </template>
      </el-table-column>

    </el-table>
    <!--  ============= Table 表内容 end   =================  -->

    <!--  ============= 页码 start =================  -->
    <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getList" />
    <!--  ============= 页码 end   =================  -->

    <!--  ============= 弹窗 start =================  -->
    <el-dialog :title="textMap[dialogStatus]" :visible.sync="dialogFormVisible">
      <el-form ref="dataForm" :rules="rules" :model="temp" label-position="left" label-width="70px" style="width: 400px; margin-left:50px;">

        <el-form-item label="名字" prop="title">
          <el-input v-model="temp.title" />
        </el-form-item>

      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogFormVisible = false">
          取消
        </el-button>
        <el-button type="primary" @click="dialogStatus==='create'?createData():updateData()">
          确认
        </el-button>
      </div>
    </el-dialog>
    <!--  ============= 弹窗 end   =================  -->

  </div>
</template>

<script>
import { userlist, createUser } from '@/api/users'
import { getAllRegions,fetchAttendanceAllList} from '@/api/common'

import { companyList, createCompany, deleteCompany } from '@/api/company'
import waves from '@/directive/waves' // waves directive
import { parseTime } from '@/utils'
import Pagination from '@/components/Pagination'
import { fetchList } from '@/api/regions'
import {fetchTaskLogAllList} from "@/api/task_log"; // secondary package based on el-pagination

export default {
  name: 'ComplexTable',
  components: { Pagination },
  directives: { waves },
  filters: {
    statusFilter(status) {
      const statusMap = {
        published: 'success',
        draft: 'info',
        deleted: 'danger'
      }
      return statusMap[status]
    },
    roleFilter(type) {
      return calendarTypeKeyValue[type]
    },
    timestringFilter(item){
      return parseTime(item,'{h}:{i}');
    }
  },
  data() {
    return {
      tableKey: 0,
      list: null,
      total: 0,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 20,
        importance: undefined,
        title: undefined,
        type: undefined,
        sort: '-id',
        name: undefined,
        start_date: new Date(),
      },
      importanceOptions: [1, 2, 3],
      sortOptions: [{ label: 'ID Ascending', key: '+id' }, { label: 'ID Descending', key: '-id' }],
      statusOptions: ['published', 'draft', 'deleted'],
      showReviewer: false,
      temp: {
        id: undefined,
        name: '',
        role: '',
        phone: '',
        company: '',
        region: '',
        status: 'published'
      },
      dialogFormVisible: false,
      dialogStatus: '',
      textMap: {
        update: '编辑',
        create: '创建'
      },
      dialogPvVisible: false,
      pvData: [],
      rules: {
        name: [{ required: true, message: '名字必填', trigger: 'blur' }]
      },
      downloadLoading: false
    }
  },
  created() {
    this.getList()
  },
  methods: {
    getList() {
      this.listLoading = true

      this.listQuery.start_date = parseTime(this.listQuery.start_date);
      console.log('listQuery = ', this.listQuery)
      fetchAttendanceAllList(this.listQuery).then(response => {
        this.list = response.data.items
        console.log('考勤列表 ',this.list)
        this.total = response.data.total
        this.listLoading = false
      })
    },
    handleFilter() {
      this.listQuery.page = 1
      this.getList()
    },
    handleModifyStatus(row, status) {
      this.$message({
        message: '操作Success',
        type: 'success'
      })
      row.status = status
    },
    sortChange(data) {
      const { prop, order } = data
      if (prop === 'id') {
        this.sortByID(order)
      }
    },
    sortByID(order) {
      if (order === 'ascending') {
        this.listQuery.sort = '+id'
      } else {
        this.listQuery.sort = '-id'
      }
      this.handleFilter()
    },
    resetTemp() {
      this.temp = {
        id: undefined,
        importance: 1,
        remark: '',
        timestamp: new Date(),
        title: '',
        status: 'published',
        type: ''
      }
    },
    handleCreate() {
      this.resetTemp()
      this.dialogStatus = 'create'
      this.dialogFormVisible = true
      this.$nextTick(() => {
        this.$refs['dataForm'].clearValidate()
      })
    },
    createData() {
      this.$refs['dataForm'].validate((valid) => {
        if (valid) {
          createCompany(this.temp).then((res) => {
            console.log(res)
            this.temp.id = res.data.id
            this.temp.name = res.data.name
            this.list.unshift(this.temp)
            this.dialogFormVisible = false
            this.$notify({
              title: '成功',
              message: '创建成功',
              type: 'success',
              duration: 2000
            })
          })
        }
      })
    },
    handleUpdate(row) {
      this.temp = Object.assign({}, row) // copy obj
      this.temp.timestamp = new Date(this.temp.timestamp)
      this.dialogStatus = 'update'
      this.dialogFormVisible = true
      this.$nextTick(() => {
        this.$refs['dataForm'].clearValidate()
      })
    },
    updateData() {
      this.$refs['dataForm'].validate((valid) => {
        if (valid) {
          const tempData = Object.assign({}, this.temp)
          tempData.timestamp = +new Date(tempData.timestamp) // change Thu Nov 30 2017 16:41:05 GMT+0800 (CST) to 1512031311464
          updateArticle(tempData).then(() => {
            const index = this.list.findIndex(v => v.id === this.temp.id)
            this.list.splice(index, 1, this.temp)
            this.dialogFormVisible = false
            this.$notify({
              title: 'Success',
              message: 'Update Successfully',
              type: 'success',
              duration: 2000
            })
          })
        }
      })
    },
    handleDelete(row, index) {
      deleteCompany({ id: row.id }).then(($res) => {
        console.log($res)
        if ($res.code == 200) {
          this.$notify({
            title: '成功',
            message: '删除成功',
            type: 'success',
            duration: 2000
          })
          this.list.splice(index, 1)
        } else {
          this.$notify({
            title: '失败',
            message: '删除失败',
            type: 'error',
            duration: 2000
          })
        }
      })
    },
    handleFetchPv(pv) {
      fetchPv(pv).then(response => {
        this.pvData = response.data.pvData
        this.dialogPvVisible = true
      })
    },
    handleDownload() {
      this.downloadLoading = true
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['timestamp', 'title', 'type', 'importance', 'status']
        const filterVal = ['timestamp', 'title', 'type', 'importance', 'status']
        const data = this.formatJson(filterVal)
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'table-list'
        })
        this.downloadLoading = false
      })
    },
    formatJson(filterVal) {
      return this.list.map(v => filterVal.map(j => {
        if (j === 'timestamp') {
          return parseTime(v[j])
        } else {
          return v[j]
        }
      }))
    },
    getSortClass: function(key) {
      const sort = this.listQuery.sort
      return sort === `+${key}` ? 'ascending' : 'descending'
    }
  }
}
</script>
