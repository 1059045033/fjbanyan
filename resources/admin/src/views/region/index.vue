<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="listQuery.title" placeholder="区域标题" style="width: 200px;" class="filter-item" @keyup.enter.native="handleFilter" />

      <span style="padding-left: 10px" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        搜索
      </el-button>
      <!--      <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-edit" @click="handleCreate">-->
      <!--        新增11-->
      <!--      </el-button>-->
      <router-link :to="'map-drawing'">
        <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-edit">
          新增
        </el-button>
      </router-link>
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

      <el-table-column label="区域标题" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.name }}</span>
        </template>
      </el-table-column>

      <el-table-column label="区域经理" width="110px" align="center">
        <template slot-scope="{row}">
          <span v-if="row.region_manager_info">{{ row.region_manager_info.name }}</span>
        </template>
      </el-table-column>

      <el-table-column label="操作" align="center" width="230" class-name="small-padding fixed-width">
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
      <el-form ref="dataForm" :rules="rules" :model="temp" label-position="left" label-width="70px" style="width: 400px; margin-left:50px;">

        <el-form-item label="管理员" prop="manager_id">
          <el-select v-model="temp.manager_id" class="filter-item" placeholder="请选择">
            <el-option v-for="item in calendarTypeOptions" :key="item.user_id" :label="item.label" :value="item.user_id" />
          </el-select>
        </el-form-item>

        <el-form-item label="区域名" prop="title">
          <el-input v-model="temp.title" />
        </el-form-item>


        <el-form-item label="三级" prop="works">
          <el-drag-select v-model="works" style="width:500px;" multiple placeholder="请选择">
            <el-option v-for="item in works_options" :key="item.user_id" :label="item.label" :value="item.user_id" />
          </el-drag-select>
        </el-form-item>


      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button  @click="cancelData()">
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
  import {fetchList, fetchPv, createArticle, updateArticle, deleteRegion,fetchManagerList,fetchRole10List,updateWorksUser} from '@/api/regions'
import waves from '@/directive/waves' // waves directive
import { parseTime } from '@/utils'
import Pagination from '@/components/Pagination'
import { deleteCompany } from '@/api/company' // secondary package based on el-pagination
import ElDragSelect from '@/components/DragSelect'

const calendarTypeOptions = []

// arr to obj, such as { CN : "China", US : "USA" }
const calendarTypeKeyValue = calendarTypeOptions.reduce((acc, cur) => {
  acc[cur.user_id] = cur.label
  return acc
}, {})

export default {
  name: 'ComplexTable',
  components: { Pagination,ElDragSelect },
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
    typeFilter(type) {
      return calendarTypeKeyValue[type]
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
        sort: '+id'
      },
      importanceOptions: [1, 2, 3],
      calendarTypeOptions,
      sortOptions: [{ label: 'ID Ascending', key: '+id' }, { label: 'ID Descending', key: '-id' }],
      statusOptions: ['published', 'draft', 'deleted'],
      showReviewer: false,
      temp: {
        id: undefined,
        manager_id: undefined,
        title: '',
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
        manager_id: [{ required: true, message: '请选择区域管理员', trigger: 'change' }],
        title: [{ required: true, message: '请填写区域名称', trigger: 'blur' }]
      },
      downloadLoading: false,

      works: [],
      works_options: []
    }
  },
  created() {
    this.getList()
    this.getManagerList()
  },
  methods: {
    getList() {
      this.listLoading = true
      fetchList(this.listQuery).then(response => {

        this.list = response.data.items
        this.total = response.data.total
        console.log('区域列表 : ', this.list)
        // Just to simulate the time of the request
        setTimeout(() => {
          this.listLoading = false
        }, 0.5 * 1000)
      })
    },
    getManagerList() {
      fetchManagerList(this.listQuery).then(response => {

        this.calendarTypeOptions = response.data.users
        console.log("response === ",this.calendarTypeOptions)
      })
    },
    getRole10List(region_id) {

      fetchRole10List({has_region:2,region_id:region_id}).then(response => {
        this.works_options = response.data.old
        for (let i=0;i<response.data.old.length;i++)
        {
          this.works.push(response.data.old[i].user_id)
        }

        for (let j=0;j<response.data.users.length;j++)
        {
          this.works_options.push(response.data.users[j])
        }

        console.log("三级人员 === ",this.works_options)
      })
    },
    cancelData() {
      this.dialogFormVisible = false
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
        type: '',
        manager_id: undefined,
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
          this.temp.id = parseInt(Math.random() * 100) + 1024 // mock a id
          this.temp.author = 'vue-element-admin'
          createArticle(this.temp).then(() => {
            this.list.unshift(this.temp)
            this.dialogFormVisible = false
            this.$notify({
              title: 'Success',
              message: 'Created Successfully',
              type: 'success',
              duration: 2000
            })
          })
        }
      })
    },
    handleUpdate(row) {
      if(row.region_manager_info != null){
        row.manager_id = row.region_manager_info.id;
      }
      row.title =  row.name;
      this.getRole10List(row.id);
      this.temp = Object.assign({}, row) // copy obj
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
          tempData.works = this.works
          console.log("选择的三级人员 ",tempData)

          updateWorksUser(tempData).then((res) => {
            const index = this.list.findIndex(v => v.id === this.temp.id)
            //this.temp
            this.temp.title = res.data.name
            if(res.data.region_manager_info != null)
            {
              this.temp.region_manager_info = res.data.region_manager_info
            }
            this.list.splice(index, 1, this.temp)
            this.dialogFormVisible = false
            this.works = [];
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
      deleteRegion({ id: row.id }).then(($res) => {
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
