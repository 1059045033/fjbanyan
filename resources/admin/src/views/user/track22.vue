<template>

  <div>
    <aside>
      1.点击开始绘制按钮。 2.右键开始绘制。 3.双击结束绘制。 4.给网格取个名字并设置网格经理保存
    </aside>
    <el-row :gutter="8">
      <el-col :xs="{span: 24}" :sm="{span: 12}" :md="{span: 12}" :lg="{span: 5}" :xl="{span: 5}" style="margin-bottom:30px;">
      <!--<todo-list @selectOverlay="selectOverlay" />-->
      </el-col>
      <el-col :xs="{span: 24}" :sm="{span: 24}" :md="{span: 24}" :lg="{span: 19}" :xl="{span: 19}" style="padding-right:8px;margin-bottom:30px;">
        <baidu-map
          class="map" :center="map_center" :zoom="16" :double-click-zoom="false" :keyboard="false" :pinch-to-zoom="false"
          :map-click="false" :scroll-wheel-zoom="true" >

          <!-- 绘制大量的点 -->
<!--          <bm-point-collection v-for="item of tracksDatas.items"-->
<!--                               :points="item.positions"-->
<!--                               shape="BMAP_POINT_SHAPE_CIRCLE"-->
<!--                               :color="item.color"-->
<!--                               size="BMAP_POINT_SIZE_SMALL">-->
<!--          </bm-point-collection>-->

          <!-- 绘制折线 -->
<!--          <bm-polyline v-for="item of tracksDatas.items" :path="item.positions"-->
<!--                       :stroke-color="item.color"-->
<!--                       :stroke-opacity="2"-->
<!--                       :stroke-weight="2"-->
<!--                       stroke-style="dashed">-->
<!--          </bm-polyline>-->





<!--          <bm-overlay-->
<!--            pane="labelPane"-->
<!--            :class="{sample: true, active}"-->
<!--            @draw="draw"-->
<!--            @mouseover.native="active = true"-->
<!--            @mouseleave.native="active = false">-->
<!--            <div>我爱林老板</div>-->
<!--          </bm-overlay>-->
          <bm-overlay
            ref="customoverlay"
            :class="{sample: true}"
            :style="pointcolor"
            pane="labelpane"
            @draw="draw">
          </bm-overlay>



        </baidu-map>
      </el-col>
    </el-row>



    <!--  ============= 弹窗 start =================  -->

    <!--  ============= 弹窗 end   =================  -->
  </div>
</template>

<script>
  import { fetchAllList } from '@/api/tracks'
  import waves from '@/directive/waves' // waves directive
  import { parseTime } from '@/utils'
  import TodoList from './components/TodoList'
  import { bmoverlay } from 'vue-baidu-map'


  const calendarTypeOptions = []
  const calendarTypeKeyValue = calendarTypeOptions.reduce((acc, cur) => {
    acc[cur.key] = cur.display_name
    return acc
  }, {})

  export default {
    directives: { waves },
    components:{TodoList,bmoverlay},
    data() {
      return {
        active: false,
        polylines: {
          items: []
        },
        tracksDatas:{
          items : [
            {
              'user_id':1,
              'name':'csh',
              'color':'red',
              'positions':[
                {
                  "lng": 119.300218,
                  "lat": 26.089661
                },
                {
                  "lng": 119.306039,
                  "lat": 26.087974
                },
                {
                  "lng": 119.307692,
                  "lat": 26.085021
                },
                {
                  "lng": 119.316028,
                  "lat": 26.086092
                }
              ]
            }
          ]
        },
        chick_id:undefined,//当前选中的线
        polylinePath: [
          {
            "lat": 26.08764198888898,
            "lng": 119.30688799999997
          },
          {"lat":26.040473989945284,"lng":119.32536799999998},
          {"lat":26.04047198994532,"lng":119.32536799999998},
          {"lat":26.04048698994498,"lng":119.32537599999999},
          {"lat":26.040476989945212,"lng":119.32538599999997},
          {"lat":26.04048398994505,"lng":119.32536799999998},
          {"lat":26.04047198994532,"lng":119.32537499999997},
          {"lat":26.040494989944804,"lng":119.32539899999998},
        ],
        polylinePath2:[],
        map_center:{
          "lat": 26.08764198888898,
          "lng": 119.30688799999997
        },
        markertitle:'cshimu',
        pointcolor:''
      }
    },
    created() {
      // 初始化数据
      this.getInit()
    },
    props: ['text', 'position', 'color'],
    watch: {
      position: {
        handler() {
          this.$refs.customoverlay.reload() // 当位置发生变化时，重新渲染，内部会调用draw
        },
        deep: true
      }
    },
    mounted () {
      this.pointcolor = this.color // 这里我是用来获取传入的值来定义样式的，可能有点多余了，pointcolor是组件中绑定的样式，color是传进来的样式。【这样就可以根据传入的样式来显示不同样子的点了】
    },
    methods: {
      draw ({el, BMap, map}) {
        console.log('map = ',map)
        console.log('BMap = ',BMap)
        // const pixel = map.pointToOverlayPixel(new BMap.Point(119.30688799999997, 26.08764198888898))
        // el.style.left = pixel.x - 60 + 'px'
        // el.style.top = pixel.y - 20 + 'px'
        const pixel = map.pointToOverlayPixel(
          new BMap.Point(119.30688799999997, 26.08764198888898)
        )
        el.style.left = pixel.x - 125 + 'px'
        el.style.top = pixel.y - 91.3 + 'px'
      },
      getInit(){
        // fetchAllList(this.listQuery).then(response => {
        //
        //   console.log('获取所有的轨迹数据 = ',response)
        //
        //   this.polylinePath2 = response.data.items[1].positions;
        //   //console.log('this.polylinePath2 ',this.polylinePath2);
        //   for (let i = 0; i < response.data.items.length; i++) {
        //     this.tracksDatas.items[i] = response.data.items[i];
        //   }
        //
        //   console.log('this.tracksDatas = ',this.tracksDatas);
        // })
      },
      // 开启弹窗
      handleCreate() {
        // this.resetTemp()
        this.dialogFormVisible = true
        this.$nextTick(() => {
          this.$refs['dataForm'].clearValidate()
        })
      },

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
                title: 'Success',
                message: 'Created Successfully',
                type: 'success',
                duration: 2000
              })
              this.$router.push({ path: '/region/index'})
            })
          }
        })
      },

      getManagerList() {
        fetchManagerList(this.listQuery).then(response => {
          this.calendarTypeOptions = response.data.users
          console.log( response.data.regions)
          for (let i = 0; i < response.data.regions.length; i++) {
            //console.log(response.data.regions[i]);
            this.polylines.items[i] = response.data.regions[i]
          }
        })
      },
      clickOverlay(e,item) {
        console.log('点击覆盖物 = ', item)
      },
      selectOverlay(item) {
        console.log("点击了左侧的菜单")
        this.map_center = item.zhongxin;
        //this.chick_id = item.id;
      },
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
  .sample {
    width: 100px;
    height: 40px;
    line-height: 40px;
    background: rgba(0,0,0,0.5);
    overflow: hidden;
    box-shadow: 0 0 5px #000;
    color: #fff;
    text-align: center;
    padding: 1px;
    position: absolute;
  }
  .sample.active {
    background: rgba(0,0,0,0.75);
    color: #fff;
  }


  /*.bm-overlay {*/
  /*  width: 250px;*/
  /*  height: 80px;*/
  /*  line-height: 1.6;*/
  /*  background-color: #fff;*/
  /*  box-shadow: 0 0 5px #ccc;*/
  /*  border-radius: 8px;*/
  /*  padding: 10px;*/
  /*  position: relative;*/
  /*  font-size: 0;*/
  /*  &::before {*/
  /*     position: absolute;*/
  /*     content: "";*/
  /*     width: 0;*/
  /*     height: 0;*/
  /*     font-size: 0;*/
  /*     position: absolute;*/
  /*     transform: rotate(45deg);*/
  /*     border-width: 8px;*/
  /*     border-style: solid dashed dashed;*/
  /*     border-color:  transparent #fff #fff transparent;*/
  /*     box-shadow: 1px 1px 1px #ccc;*/
  /*     bottom: -8px;*/
  /*     left: 113.7px;*/
  /*   }*/

  /*  .logo {*/
  /*    width: 60px;*/
  /*    height: 60px;*/
  /*    margin: 0 10px 0 0;*/
  /*    vertical-align: top;*/
  /*  }*/
  /*  .inline-block {*/
  /*    width: 160px;*/
  /*    vertical-align: top;*/
  /*  }*/
  /*  .title {*/
  /*    color: #333;*/
  /*    font-size: 14px;*/
  /*    font-weight: bold;*/
  /*  }*/
  /*  .contents {*/
  /*    color: #666;*/
  /*    font-size: 12px;*/
  /*  }*/
  /*}*/

</style>
