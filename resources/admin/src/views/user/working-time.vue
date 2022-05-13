<template>
  <div class="app-container">
    <div class="filter-container">
      <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-edit" @click="handleCreate">
        新增
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

      <el-table-column label="名字" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.name }}</span>
        </template>
      </el-table-column>

      <el-table-column label="手机" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.start_time }}</span>
        </template>
      </el-table-column>

      <el-table-column label="角色" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.end_time}}</span>
        </template>
      </el-table-column>

      <el-table-column v-if="true" label="操作" align="center" width="230" class-name="small-padding fixed-width">
        <template slot-scope="{row,$index}">
<!--          <el-button type="primary" size="mini" @click="handleUpdate(row)">-->
<!--            编辑-->
<!--          </el-button>-->
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
      <el-form ref="dataForm" :rules="rules" :model="temp" label-position="left" label-width="110px" style="width: 400px; margin-left:50px;">

        <el-form-item label="名字" prop="name">
          <el-input v-model="temp.name" />
        </el-form-item>
        <el-form-item label="上班时间" prop="start_time">
          <el-date-picker v-model="temp.start_time" type="datetime" placeholder="请选择时间" value-format="HH:mm" format="HH:mm"/>
        </el-form-item>
        <el-form-item label="下班时间" prop="end_time">
          <el-date-picker v-model="temp.end_time" type="datetime" placeholder="请选择时间" value-format="HH:mm" format="HH:mm"/>
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
import { userlist, createUser, deleteUser, updateUser } from '@/api/users'
import { fetchWorkingTimeAllList,createWorkingTime,deleteWorkingTime } from '@/api/workingtime'
import { getAllRegions, getAllCompany } from '@/api/common'

import { companylist, deleteCompany } from '@/api/company'
import waves from '@/directive/waves' // waves directive
import { parseTime } from '@/utils'
import Pagination from '@/components/Pagination'
import { fetchList } from '@/api/regions'
import {validUsername} from "@/utils/validate"; // secondary package based on el-pagination

const calendarTypeOptions = [
  { key: '1', display_name: '三级' },
  { key: '2', display_name: '二级' },
  { key: '30', display_name: '一级' }
]
const regionOptions = []

// arr to obj, such as { CN : "China", US : "USA" }
const calendarTypeKeyValue = calendarTypeOptions.reduce((acc, cur) => {
  acc[cur.key] = cur.display_name
  return acc
}, {})

const regionKeyValue = regionOptions.reduce((acc, cur) => {
  acc[cur.id] = cur.name
  return acc
}, {})

const companyOptions = [
  { id: '1', name: '福州手动' },
  { id: '2', name: '大沙发' }
]

// arr to obj, such as { CN : "China", US : "USA" }
const companyKeyValue = companyOptions.reduce((acc, cur) => {
  acc[cur.id] = cur.name
  return acc
}, {})

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
    }
  },
  data() {
    const validateEmail = (rule, value, callback) => {
      var reg=/^[A-Za-z0-9\u4e00-\u9fa5]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/;
      console.log("邮箱 = ",value)
      if(value ==  undefined || value == ""){
        callback()
      }else{
        if(!reg.test(value) ){
          callback(new Error('请输入有效的邮箱'));
        }else {
          callback()
        }
      }

    }
    const validatePhone = (rule, value, callback) => {
      var reg=/^1[3456789]\d{9}$/;
        if(value ==  undefined || value == ""){
          callback()
        }else{
          if(value != "" && !reg.test(value)){
            callback(new Error('请输入有效的手机号码'));
          }else {
            callback()
          }
        }

    }
    const validatePhoneNotnull = (rule, value, callback) => {
      if (value === '') {
        callback(new Error('负责人手机号不可为空'));
      } else {
        if (value !== '') {
          var reg=/^1[3456789]\d{9}$/;
          if(!reg.test(value)){
            callback(new Error('请输入有效的手机号码'));
          }
        }
        callback();
      }
    }
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
        sort: '+id',
        name: undefined,
        id: undefined
      },
      importanceOptions: [1, 2, 3],
      calendarTypeOptions,
      companyOptions,
      regionOptions,
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
        status: 'published',
        start_time: Date.now(),
        end_time: Date.now(),
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
        name: [{ required: true, message: '名字必填', trigger: 'blur' }],
        start_time: [{ required: true, message: '时间必填', trigger: 'change' }],
        end_time: [{ required: true, message: '时间必填', trigger: 'change' }],
      },
      downloadLoading: false,
      // pickerOption:{
      //   disabledDate:(time)=>{
      //     return (Date.now()-3600*1000*24 > time.getTime()) || (Date.now()-3600*24 < time.getTime());
      //   }
      // }
    }
  },
  created() {
    this.getList()
    this.getCompanies()
    this.getRegions()
  },
  methods: {
    updateWorkingTime(row){
        console.log('点击');
    },
    // dateChange(){
    //   var startAt = new Date(this.date) *1000/1000;
    //   if(startAt < Date.now())
    //   {
    //     this.temp.start_time = new Date();
    //   }
    // },
    getList() {
      this.listLoading = true
      this.listQuery.id = this.$route.params && this.$route.params.id
      console.log('listQuery = ', this.listQuery)
      fetchWorkingTimeAllList(this.listQuery).then(response => {
        console.log(response)
        this.list = response.data.items
        this.total = response.data.total;
        this.listLoading = false
      })
    },
    getCompanies() {
      getAllCompany(this.listQuery).then(response => {
        console.log('公司列表： ', response)
        this.companyOptions = response.data
      })
    },
    getRegions() {
      console.log('获取网格')
      getAllRegions(this.listQuery).then(response => {
        this.regionOptions = response.data.items
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
          this.temp.user_id = this.listQuery.id
          console.log("创建上班时间的参数",this.temp)
          if(this.temp.start_time > this.temp.end_time){
              this.$notify({
                title: '失败提醒',
                message: '下班时间不能早于上班时间',
                type: 'success',
                duration: 2000
              })
          }


          createWorkingTime(this.temp).then((res) => {
            console.log(res)
            this.temp.id = res.data.id
            this.temp.name = res.data.name
            this.temp.start_time = res.data.start_time
            this.temp.end_time = res.data.end_time
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
      console.log('--------------', this.temp.company)
      this.temp.company = this.temp.company == null ? '' : this.temp.company.id
      this.temp.role = this.temp.role + ''
      this.temp.region = this.temp.region == null ? '' : this.temp.region.id

      console.log('calendarTypeOptions = ', calendarTypeKeyValue[10])
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
          updateUser(tempData).then((res) => {
            this.temp.id = res.data.id
            this.temp.company = res.data.company
            this.temp.region = res.data.region

            const index = this.list.findIndex(v => v.id === this.temp.id)
            this.list.splice(index, 1, this.temp)
            this.dialogFormVisible = false
            this.$notify({
              title: '成功',
              message: '更新成功',
              type: 'success',
              duration: 2000
            })
          })
        }
      })
    },
    handleDelete(row, index) {
      deleteWorkingTime({ id: row.id }).then(($res) => {
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
