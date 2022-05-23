<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="listQuery.name" placeholder="用户名" style="width: 200px;padding-top: 8px;" class="filter-item" @keyup.enter.native="handleFilter"  />

      <el-date-picker v-model="listQuery.start_date" type="date" value-format="yyyy-MM-dd" placeholder="请选择一个时间"/>

      <span style="padding-left: 10px" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter" style="padding-top: 8px;">
        搜索
      </el-button>

      <el-button @click="exportExcel">导出</el-button>
<!--      <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-edit" @click="handleCreate">-->
<!--        新增-->
<!--      </el-button>-->
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
      id="task_table"
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
          <span>{{ row.user_info.name }}</span>
        </template>
      </el-table-column>

      <el-table-column label="手机" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.user_info.phone }}</span>
        </template>
      </el-table-column>

      <el-table-column label="地址" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.address }}</span>
        </template>
      </el-table-column>

      <el-table-column label="图集" min-width="150px">
        <template slot-scope="{row}">
          <span v-for="item of row.atlas"><a class="document-btn" target="_blank" :href="item"><img :src="item"></a></span>
        </template>
      </el-table-column>



      <el-table-column label="时间" min-width="150px">
        <template slot-scope="{row}">
          <span >{{ row.created_at }}</span>
        </template>
      </el-table-column>
      <el-table-column label="备注" min-width="150px">
        <template slot-scope="{row}">
          <span >{{ row.content }}</span>
        </template>
      </el-table-column>

      <el-table-column label="所属网格" min-width="150px">
        <template slot-scope="{row}">
          <span v-if="row.work_region_info">{{ row.work_region_info.name }}</span>
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

        <el-form-item label="网格" prop="region">
          <el-select v-model="temp.region" class="filter-item" placeholder="请选择">
            <el-option v-for="item in regionOptions" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
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
  import { fetchTaskLogAllList,exportTaskLogAllList } from '@/api/task_log'
  import { getAllRegions, getAllCompany } from '@/api/common'

  import { companylist, deleteCompany } from '@/api/company'
  import waves from '@/directive/waves' // waves directive
  import { parseTime } from '@/utils'
  import Pagination from '@/components/Pagination'
  import { fetchList } from '@/api/regions' // secondary package based on el-pagination


  import FileSaver from "file-saver";
  import XLSX from "xlsx";

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
          name: [{ required: true, message: '名字必填', trigger: 'blur' }],
          phone: [{ required: true, message: '号码必填', trigger: 'blur' }],
          role: [{ required: true, message: '等级必选', trigger: 'change' }],
          company: [{ required: true, message: '公司必选', trigger: 'change' }]
        },
        downloadLoading: false,
        isExport:true
      }
    },
    created() {
      this.getList()
    },
    methods: {
      exportExcel() {

        // 导出交给服务器
        this.listQuery.start_date = parseTime(this.listQuery.start_date);
        console.log('listQuery = ', this.listQuery)
        if(this.isExport){
          //this.isExport = false ;
          exportTaskLogAllList(this.listQuery).then(response => {
            this.isExport = true ;
            console.log('导出任务的返回数据',response)
            if(response.data.state == true){
              //this.$router.push({ path: 'http://www.baidu.com' })
              window.open(response.data.url, '_blank')
            }else{
              this.$notify({
                title: '失败',
                message: '稍后重试',
                type: 'success',
                duration: 2000
              })
            }
          })
        }


        // 将excle导出交给浏览器
        // //  .table要导出的是哪一个表格
        // var wb = XLSX.utils.table_to_book(document.querySelector("#task_table"));
        // /* get binary string as output */
        // var wbout = XLSX.write(wb, {
        //   bookType: "xlsx",
        //   bookSST: true,
        //   type: "array"
        // });
        // try {
        //   //  name+'.xlsx'表示导出的excel表格名字
        //   FileSaver.saveAs(new Blob([wbout], {type: "application/octet-stream"}),"cshimu.xlsx");
        // } catch (e) {
        //   if (typeof console !== "undefined") console.log(e, wbout);
        // }
        // return wbout;
      },
      getList() {
        this.listLoading = true
        this.listQuery.start_date = parseTime(this.listQuery.start_date);
        console.log('listQuery = ', this.listQuery)
        fetchTaskLogAllList(this.listQuery).then(response => {
          this.list = response.data.items
          console.log('任务列表轨迹 ',this.list)
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
        console.log('获取网格')
        getAllRegions(this.listQuery).then(response => {
          this.regionOptions = response.data.items
        })
      },
      handleFilter() {
        this.listQuery.page = 1
        console.log('this.listQuery == ',parseTime(this.listQuery.start_date))
        this.listQuery.start_date = parseTime(this.listQuery.start_date);
        // this.temp.timestamp = new Date(this.temp.timestamp)
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

<style>

  img{
    width: 40px;
    height: 40px;
    object-fit: cover;
    padding: 2px;
  }
</style>
