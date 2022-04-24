<template>

  <div>
    <aside>
      1.点击开始绘制按钮。 2.右键开始绘制。 3.双击结束绘制。 4.给区域取个名字并设置区域经理保存
    </aside>
    <baidu-map
      class="map"
      center="福州"
      :zoom="16"
      :double-click-zoom="false"
      :keyboard="false"
      :pinch-to-zoom="false"
      :map-click="false"
      :scroll-wheel-zoom="true"
      @mousemove="syncPolyline"
      @dblclick="newPolyline"
      @rightclick="paintPolyline"
    >
      <bm-control>
        <button
          class="el-button el-button--primary el-button--medium"
          @click="toggle('polyline')"
        >{{ polyline.editing ? '停止绘制' : '开始绘制' }}</button>
      </bm-control>
      <bm-polygon v-for="path of polyline.paths" :path="path" stroke-color="blue" :stroke-opacity="0.5" :stroke-weight="2" />
      <bm-polygon
        v-for="item of polylines.items"
        :path="item.region_scope"
        stroke-color="blue"
        :stroke-opacity="0.5"
        :stroke-weight="2"
        :data-region="1"
        @click="clickOverlay($event,item)"
      />
    </baidu-map>

    <!--  ============= 弹窗 start =================  -->
    <el-dialog :title="textMap[dialogStatus]" :visible.sync="dialogFormVisible">
      <el-form ref="dataForm" :rules="rules" :model="temp" label-position="left" label-width="70px" style="width: 400px; margin-left:50px;">

        <el-form-item label="管理员" prop="manager_id">
          <el-select v-model="temp.manager_id" class="filter-item" placeholder="Please select">
            <el-option v-for="item in calendarTypeOptions" :key="item.user_id" :label="item.label" :value="item.user_id" />
          </el-select>
        </el-form-item>

        <el-form-item label="区域名" prop="title">
          <el-input v-model="temp.title" />
        </el-form-item>

      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="cancelData()">
          Cancel
        </el-button>
        <el-button type="primary" @click="dialogStatus==='create'?createData():updateData()">
          Confirm
        </el-button>
      </div>
    </el-dialog>

    <!--  ============= 弹窗 end   =================  -->
  </div>
</template>

<script>
import { fetchList, fetchManagerList, createRegion } from '@/api/regions'
import waves from '@/directive/waves' // waves directive
import { parseTime } from '@/utils'

const calendarTypeOptions = []

// arr to obj, such as { CN : "China", US : "USA" }
const calendarTypeKeyValue = calendarTypeOptions.reduce((acc, cur) => {
  acc[cur.key] = cur.display_name
  return acc
}, {})

export default {
  directives: { waves },
  data() {
    return {
      polyline: {
        editing: false,
        paths: []
      },
      polylines: {
        items: []
      },
      quyu: [],
      textMap: {
        update: 'Edit',
        create: 'Create'
      },
      dialogFormVisible: false, // 弹窗是否表单验证
      dialogStatus: '', // 弹窗状态
      rules: {
        manager_id: [{ required: true, message: '请选择区域管理员', trigger: 'change' }],
        title: [{ required: true, message: '请填写区域名称', trigger: 'blur' }]
      },
      temp: {
        id: undefined,
        manager_id: undefined,
        title: ''
      },
      statusOptions: ['published', 'draft', 'deleted'],
      calendarTypeOptions,
      currLines: [],
      regionData: ''
    }
  },
  created() {
    this.getManagerList()
  },
  methods: {
    // 切换开关
    toggle(name) {
      this[name].editing = !this[name].editing
    },
    // 同步线
    syncPolyline(e) {
      // 是否处于绘制状态
      if (!this.polyline.editing) {
        return
      }

      // 实时获取线的点信息
      const { paths } = this.polyline
      if (!paths.length) {
        return
      }

      const path = paths[paths.length - 1]
      if (!path.length) {
        return
      }

      if (path.length === 1) {
        path.push(e.point)
      }
      this.$set(path, path.length - 1, e.point)
    },
    // 新的线
    newPolyline(e) {
      console.log('点击右键完成绘制')
      if (!this.polyline.editing) {
        return
      }
      const { paths } = this.polyline
      if (!paths.length) {
        paths.push([])
      }
      const path = paths[paths.length - 1]
      path.pop()
      if (path.length) {
        paths.push([])
      }
      const temp = []
      for (let i = 0; i < path.length; i++) {
        // this.currLines += '{"lng":'+path[i]['lng']+',"lat":'+path[i]['lat']+'},';
        const temp_ = { 'lng': path[i]['lng'], 'lat': path[i]['lat'] }
        this.currLines[i] = temp_
      }

      this.quyu.push(this.currLines)
      // 弹窗
      setTimeout(() => {
        this.handleCreate()
      }, 0.5 * 1000)
    },
    // 绘制线
    paintPolyline(e) {
      console.log('绘制线')
      if (!this.polyline.editing) {
        return
      }
      const { paths } = this.polyline
      !paths.length && paths.push([])
      paths[paths.length - 1].push(e.point)
    },
    // 开启弹窗
    handleCreate() {
      // this.resetTemp()
      this.dialogStatus = 'create'
      this.dialogFormVisible = true
      this.$nextTick(() => {
        this.$refs['dataForm'].clearValidate()
      })
    },
    // resetTemp() {
    //   this.temp = {
    //     id: undefined,
    //     importance: 1,
    //     remark: '',
    //     timestamp: new Date(),
    //     title: '',
    //     status: 'published',
    //     type: ''
    //   }
    // },
    createData() {
      console.log('创建新的区域 = ', this.temp)

      this.$refs['dataForm'].validate((valid) => {
        if (valid) {
          this.temp.scope = this.currLines
          createRegion(this.temp).then(() => {
            // this.list.unshift(this.temp)
            const n_index = 0
            if (this.polylines.items.length > 0) {
              const n_index = this.polylines.items.length + 1
            }
            this.polylines.items[n_index].region_scope = this.polyline.paths
            this.polylines.items[n_index].region_manager_info.name = this.temp.manager_id
            this.polylines.items[n_index].name = this.temp.title
            this.polyline.paths = []

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
    cancelData() {
      this.dialogFormVisible = false
      // this.polyline.paths.pop();
      this.polyline.paths = []
    },
    getManagerList() {
      fetchManagerList(this.listQuery).then(response => {
        this.calendarTypeOptions = response.data.users
        // console.log( response.data.regions)
        for (let i = 0; i < response.data.regions.length; i++) {
          // console.log(response.data.regions[i].region_scope);
          this.polylines.items[i] = response.data.regions[i]
        }
      })
    },
    clickOverlay(e) {
      console.log('点击覆盖物 = ', this.$refs.dataRegion)
      // console.log("点击覆盖物 = ",e.target.getAttribute("data-region"))
      // console.log("点击覆盖物 = ",e.currentTarget.getAttribute("data-region"))
    }
  }
}
</script>

<style>
  .map {
    width: 100%;
    height: 550px;
  }
</style>
