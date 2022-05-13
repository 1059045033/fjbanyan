<template>

  <div>
    <aside>
      1.点击开始绘制按钮。 2.右键开始绘制。 3.双击结束绘制。 4.给网格取个名字并设置网格经理保存
    </aside>
    <el-row :gutter="8">
      <el-col :xs="{span: 24}" :sm="{span: 12}" :md="{span: 12}" :lg="{span: 5}" :xl="{span: 5}" style="margin-bottom:30px;">
        <todo-list @selectOverlay="selectOverlay" />
      </el-col>
      <el-col :xs="{span: 24}" :sm="{span: 24}" :md="{span: 24}" :lg="{span: 19}" :xl="{span: 19}" style="padding-right:8px;margin-bottom:30px;">
        <baidu-map
          class="map"
          :center="map_center"
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
          <!--<bm-map-type :map-types="['BMAP_NORMAL_MAP', 'BMAP_HYBRID_MAP']" anchor="BMAP_ANCHOR_TOP_RIGHT"></bm-map-type>-->
          <!--<bm-city-list anchor="BMAP_ANCHOR_TOP_RIGHT"></bm-city-list>-->

          <bm-control>
            <button class="el-button el-button--primary" style="margin: 5px" @click="toggle('polyline',$event)">{{ polyline.editing ? '停止绘制' : '开始绘制' }}</button>
          </bm-control>

          <bm-polygon v-for="path of polyline.paths" :path="path" stroke-color="blue" :stroke-opacity="0.5" :stroke-weight="2" />
          <bm-polygon
            v-for="item of polylines.items"
            v-if="chick_id == item.region_id"
            :path="item.region_scope"
            stroke-style="dashed"
            stroke-color="blue"
            fill-color="red"
            :stroke-opacity="0.5"
            :stroke-weight="2"
            @click="clickOverlay($event,item)"
          />

          <bm-polygon
            v-for="item of polylines.items"
            v-if="chick_id != item.region_id"
            :path="item.region_scope"
            stroke-style="dashed"
            stroke-color="blue"
            fill-color="white"
            :stroke-opacity="0.5"
            :stroke-weight="2"
            @click="clickOverlay($event,item)"
          />

          <bm-label v-for="item of polylines.items" :content="item.name" :position="item.zhongxin" :label-style="{fontSize : '12px'}" :offset="{width: -35}" />

          <!--<bm-overlay-->
          <!--pane="labelPane"-->
          <!--:class="{sample: true, active}"-->
          <!--@draw="draw($event,111)"-->
          <!--@mouseover.native="active = true"-->
          <!--@mouseleave.native="active = false">-->
          <!--<div>我爱北京天安门</div>-->
          <!--</bm-overlay>-->

        </baidu-map>
      </el-col>
    </el-row>

    <!--  ============= 弹窗 start =================  -->
    <el-dialog :title="textMap[dialogStatus]" :visible.sync="dialogFormVisible">
      <el-form ref="dataForm" :rules="rules" :model="temp" label-position="left" label-width="70px" style="width: 400px; margin-left:50px;">

        <el-form-item label="管理员" prop="manager_id">
          <el-select v-model="temp.manager_id" class="filter-item" placeholder="Please select">
            <el-option v-for="item in calendarTypeOptions" :key="item.user_id" :label="item.label" :value="item.user_id" />
          </el-select>
        </el-form-item>

        <el-form-item label="网格名" prop="title">
          <el-input v-model="temp.title" />
        </el-form-item>

      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="cancelData()">
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
import { fetchList, fetchManagerList, createRegion } from '@/api/regions'
import waves from '@/directive/waves' // waves directive
import { parseTime } from '@/utils'
import BoxCard from './components/BoxCard'
import TransactionTable from './components/TransactionTable'
import TodoList from './components/TodoList'

const calendarTypeOptions = []

// arr to obj, such as { CN : "China", US : "USA" }
const calendarTypeKeyValue = calendarTypeOptions.reduce((acc, cur) => {
  acc[cur.key] = cur.display_name
  return acc
}, {})

export default {
  directives: { waves },
  components: { BoxCard, TransactionTable, TodoList },
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
        update: '编辑',
        create: '创建'
      },
      dialogFormVisible: false, // 弹窗是否表单验证
      dialogStatus: '', // 弹窗状态
      rules: {
        manager_id: [{ required: true, message: '请选择网格管理员', trigger: 'change' }],
        title: [{ required: true, message: '请填写网格名称', trigger: 'blur' }]
      },
      temp: {
        id: undefined,
        manager_id: undefined,
        title: ''
      },
      statusOptions: ['published', 'draft', 'deleted'],
      calendarTypeOptions,
      currLines: [],
      regionData: '',
      active: false,
      map_center: { lng: 119.313369, lat: 26.082198 },
      chick_id: undefined,
      dialogVisible: true
    }
  },
  created() {
    this.getManagerList()
  },
  methods: {
    draw({ el, BMap, map }, t) {
      const pixel = map.pointToOverlayPixel(new BMap.Point(119.313369, 26.082198))
      // el.style.left = pixel.x - 60 + 'px'
      // el.style.top = pixel.y - 2 + 'px'
      el.style.left = pixel.x + 'px'
      el.style.top = pixel.y + 'px'
    },
    // 切换开关
    toggle(name, { el, BMap, map }) {
      this[name].editing = !this[name].editing
    },
    // 同步线
    syncPolyline(e) {
      if (this['polyline'].editing) {
        e.currentTarget.platform.style.cursor = 'crosshair'
      }
      // console.log(e)
      // e.currentTarget.style.cursor = 'crosshair';
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
      if (this['polyline'].editing) {
        e.currentTarget.platform.style.cursor = 'crosshair'
      } else {
        e.currentTarget.platform.style.cursor = 'openhand'
      }
      // console.log('E 队形',e.currentTarget.platform.style.cursor);// = 'crosshair'
      // e.currentTarget.platform.style.cursor = 'crosshair';
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
        this.dialogStatus = 'create'
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
      console.log(e.point)
    },
    // 开启弹窗
    handleCreate() {
      // this.resetTemp()

      this.dialogFormVisible = true
      this.$nextTick(() => {
        this.$refs['dataForm'].clearValidate()
      })
    },
    // 网格详情弹窗
    regionDetailsPop(item) {
      this.dialogStatus = 'update'
      this.temp.id = item.region_id
      this.temp.title = item.name
      // this.calendarTypeOptions.push({
      //   'label':item.region_manager_info.name + "("+item.region_manager_info.phone+")",
      //   'user_id':item.region_manager_info.id
      // })

      console.log(this.calendarTypeOptions)
      this.handleCreate()
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
      console.log('创建新的网格 = ', this.temp)

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
              title: '成功',
              message: '成功',
              type: 'success',
              duration: 2000
            })
            this.$router.push({ path: '/region/index' })
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
        console.log(response.data.regions)
        for (let i = 0; i < response.data.regions.length; i++) {
          // console.log(response.data.regions[i]);
          this.polylines.items[i] = response.data.regions[i]
        }
      })
    },
    clickOverlay(e, item) {
      console.log('点击覆盖物 = ', item)
      this.map_center = item.zhongxin
      this.chick_id = item.region_id

      // 弹窗详情窗口
      // this.regionDetailsPop(item);

      // console.log("点击覆盖物 = ",e.target.getAttribute("data-region"))
      // console.log("点击覆盖物 = ",e.currentTarget.getAttribute("data-region"))
    },
    selectOverlay(item) {
      console.log('选择覆盖物 = ', item)
      this.map_center = item.zhongxin
      this.chick_id = item.id
      // console.log("点击覆盖物 = ",e.target.getAttribute("data-region"))
      // console.log("点击覆盖物 = ",e.currentTarget.getAttribute("data-region"))
    },
    toggleTodo(val) {
      console.log('点击了左侧的菜单 ', val)
    }
  }
}
</script>

<style>
  .map {
    width: 100%;
    height: 550px;
  }
  .el-button .el-button--primary{
    background:#409EFF !important;
  }
  /*.sample {*/
  /*  width: 100px;*/
  /*  height: 40px;*/
  /*  line-height: 40px;*/
  /*  background: rgba(0,0,0,0.5);*/
  /*  overflow: hidden;*/
  /*  box-shadow: 0 0 5px #000;*/
  /*  color: #fff;*/
  /*  text-align: center;*/
  /*  padding: 1px;*/
  /*  position: absolute;*/
  /*}*/
  /*.sample.active {*/
  /*  background: rgba(0,0,0,0.75);*/
  /*  color: #fff;*/
  /*}*/
</style>
