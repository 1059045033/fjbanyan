<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="listQuery.name" placeholder="用户名" style="width: 200px;" class="filter-item" @keyup.enter.native="handleFilter" />

      <el-select v-model="listQuery.type" placeholder="角色" clearable class="filter-item" style="width: 130px">
        <el-option v-for="item in calendarTypeOptions" :key="item.key" :label="item.display_name+'('+item.key+')'" :value="item.key" />
      </el-select>

      <span style="padding-left: 10px" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        搜索
      </el-button>
      <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-edit" @click="handleCreate">
        新增
      </el-button>
      <!--      <router-link :to="'add'">-->
      <!--        <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-edit">-->
      <!--          新增-->
      <!--        </el-button>-->
      <!--      </router-link>-->
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
          <span>{{ row.phone }}</span>
        </template>
      </el-table-column>

      <el-table-column label="角色" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.role | roleFilter }}</span>
        </template>
      </el-table-column>

      <el-table-column label="公司" min-width="150px">
        <template slot-scope="{row}">
          <span v-if="row.company">{{ row.company.name }}</span>
        </template>
      </el-table-column>

      <el-table-column label="所属区域" min-width="150px">
        <template slot-scope="{row}">
          <span v-if="row.region">{{ row.region.name }}</span>
        </template>
      </el-table-column>

      <el-table-column v-if="true" label="操作" align="center" width="230" class-name="small-padding fixed-width">
        <template slot-scope="{row,$index}">
          <el-button type="primary" size="mini" @click="handleUpdate(row)">
            编辑
          </el-button>
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
        <el-form-item label="号码" prop="phone">
          <el-input v-model="temp.phone" />
        </el-form-item>

        <el-form-item label="等级" prop="role">
          <el-select v-model="temp.role" class="filter-item" placeholder="请选择">
            <el-option v-for="item in calendarTypeOptions" :key="item.key" :label="item.display_name" :value="item.key" />
          </el-select>
        </el-form-item>

        <el-form-item label="公司" prop="company">
          <el-select v-model="temp.company" class="filter-item" placeholder="请选择">
            <el-option v-for="item in companyOptions" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
        </el-form-item>

        <el-form-item label="区域" prop="region">
          <el-select v-model="temp.region" class="filter-item" placeholder="请选择">
            <el-option v-for="item in regionOptions" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
        </el-form-item>

        <el-form-item label="省份证" prop="ID_Card">
          <el-input v-model="temp.ID_Card" />
        </el-form-item>

        <el-form-item label="邮箱" prop="email">
          <el-input v-model="temp.email" />
        </el-form-item>

        <el-form-item label="地址" prop="address">
          <el-input v-model="temp.address" />
        </el-form-item>

        <el-form-item label="紧急联系人" prop="emergency_contact">
          <el-input v-model="temp.emergency_contact" />
        </el-form-item>

        <el-form-item label="紧急联系人电话" prop="emergency_contact_phone">
          <el-input v-model="temp.emergency_contact_phone" />
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

    <el-dialog :visible.sync="dialogPvVisible" title="Reading statistics">
      <el-table :data="pvData" border fit highlight-current-row style="width: 100%">
        <el-table-column prop="key" label="Channel" />
        <el-table-column prop="pv" label="Pv" />
      </el-table>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="dialogPvVisible = false">Confirm</el-button>
      </span>
    </el-dialog>
    <!--  ============= 弹窗 end   =================  -->

  </div>
</template>

<script>
import { userlist, createUser, deleteUser, updateUser } from '@/api/users'
import { getAllRegions, getAllCompany } from '@/api/common'

import { companylist, deleteCompany } from '@/api/company'
import waves from '@/directive/waves' // waves directive
import { parseTime } from '@/utils'
import Pagination from '@/components/Pagination'
import { fetchList } from '@/api/regions'
import {validUsername} from "@/utils/validate"; // secondary package based on el-pagination

const calendarTypeOptions = [
  { key: '10', display_name: '三级' },
  { key: '20', display_name: '二级' },
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
        name: undefined
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
        ID_Card:'',
        email:'',
        address:'',
        emergency_contact:'',
        emergency_contact_phone:'',
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
        phone: [{ required: true, trigger: 'blur',validator: validatePhoneNotnull }],
        role: [{ required: true, message: '等级必选', trigger: 'change' }],
        email: [{ trigger: 'blur',validator: validateEmail }],
        emergency_contact_phone: [{ trigger: 'blur',validator: validatePhone }]
      },
      downloadLoading: false
    }
  },
  created() {
    this.getList()
    this.getCompanies()
    this.getRegions()
  },
  methods: {
    getList() {
      this.listLoading = true
      console.log('listQuery = ', this.listQuery)
      userlist(this.listQuery).then(response => {
        console.log(response)
        this.list = response.data.items
        this.total = response.data.total
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
      console.log('获取区域')
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
          createUser(this.temp).then((res) => {
            console.log(res)
            this.temp.id = res.data.id
            this.temp.company = res.data.company
            this.temp.region = res.data.region
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
      deleteUser({ id: row.id }).then(($res) => {
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
