<template>

  <div>

    <div class="filter-container" style="margin: 5px">
      <div>
        提醒 ： 人员是三级人员(是已经有归属网格的三级人员)
      </div>
<!--      <el-input v-model="listQuery.name" placeholder="用户名" style="width: 200px;" class="filter-item" @keyup.enter.native="handleFilter"  />-->

      <el-select v-model="listQuery.user_id" placeholder="选择人员" clearable class="filter-item" style="width: 250px;margin-top: 8px">
        <el-option v-for="item in userOptions" :key="item.user_id" :label="item.label" :value="item.user_id" />
      </el-select>

      <el-date-picker v-model="listQuery.start_date" type="date" value-format="yyyy-MM-dd" placeholder="请选择一个时间" style="margin-left: 5px;margin-top: -10px"/>
      <span style="padding-left: 10px" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter" >
        查看轨迹
      </el-button>
    </div>

    <el-row :gutter="8" style="margin-left: 3px;">
<!--      <el-col :xs="{span: 24}" :sm="{span: 12}" :md="{span: 12}" :lg="{span: 5}" :xl="{span: 5}" style="margin-bottom:30px;">-->
<!--        <todo-list @selectOverlay="selectOverlay" :pageType="pageType"/>-->
<!--      </el-col>-->
      <el-col :xs="{span: 24}" :sm="{span: 24}" :md="{span: 24}" :lg="{span: 24}" :xl="{span: 24}" style="padding-right:8px;margin-bottom:30px;">
        <baidu-map
          class="bm-view"
          :ak="mapAk"
          :center="center"
          :zoom="zoom"
          NavigationControlType="BMAP_NAVIGATION_CONTROL_LARGE"
          :double-click-zoom="false"
          :keyboard="false"
          :pinch-to-zoom="false"
          :map-click="false"
          :scroll-wheel-zoom="true"
          @ready="mapReady">
          <bm-control>
            <button @click="dingwei" v-if="show_btn.dingwei"><i class="el-icon-location-outline" /></button>
          </bm-control>
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
  import TodoList from './components/TodoList'
  import { BaiduMap, BmNavigation } from 'vue-baidu-map/components';
  import {fetchRole10List} from "@/api/regions";
  import {parseTime} from "@/utils";
  const userOptions = []

  // arr to obj, such as { CN : "China", US : "USA" }
  const userKeyValue = userOptions.reduce((acc, cur) => {
    acc[cur.user_id] = cur.label
    return acc
  }, {})

  export default {
    directives: { waves },
    components:{TodoList,BaiduMap,BmNavigation},
    data() {
      return {
        BMap:'',
        map:'',
        mapAk:'WYl8agaHEG0pVYBOQxlO9RBKekU3zbzT',
        drawerShow:false,
        zoom:18,
        // 地图中心
        center:{ lng:119.30688799999997,lat:26.08764198888898 },
        centerPoint:{ lng:119.30688799999997,lat:26.08764198888898 },
        pageType:'whereabouts',
        // 颜色列表
        colorList:[
          {lineColor:'#688F82',icon:require('@/assets/icon/icon-circle1.png')},
          {lineColor:'#C7895C',icon:require('@/assets/icon/icon-circle2.png')},
          {lineColor:'#5370DD',icon:require('@/assets/icon/icon-circle3.png')},
          {lineColor:'#8349AD',icon:require('@/assets/icon/icon-circle4.png')}
        ],
        listQuery: {
          page: 1,
          limit: 20,
          importance: undefined,
          title: undefined,
          type: undefined,
          sort: '-id',
          name: undefined,
          start_date: new Date(),
          user_id:undefined
        },
        // 存放数据点
        mapList : [
          [
            { lng: 116.404, lat: 39.915 },
            { lng: 116.403, lat: 39.914 },
          ],
          [
            { lng: 116.414, lat: 39.925 },
            { lng: 116.413, lat: 39.924 },
            { lng: 116.503, lat: 39.913 },
          ]
        ],
        mapListUser : {
          items: []
        },
        userOptions,
        show_btn:{
          dingwei:false
        }
      }
    },
    methods: {
      dingwei(){
        console.log("当前zoom = ",this.zoom)
          this.center.lng = this.center.lng+ 0.00000000000001;
        },
      mapReady ({ BMap, map }) {
        this.BMap = BMap;
        this.map = map;
        // 获取下来人员列表
        this.getRole10List(1)

      },
      getRole10List(region_id) {
        fetchRole10List({has_region:1,region_id:region_id}).then(response => {
          this.userOptions = response.data.users
          console.log("三级人员 === ",this.works_options)
        })
      },

      // 绘制图形
      drawMap () {
        let BMap = this.BMap;
        let map = this.map;
        map.clearOverlays();      // 清除覆盖物

        let data = this.mapList;  // 这里的mapList是处理过的数据，下面会附上数据格式；

        for (let i = 0; i < data.length; i++) {
          let points = [];
          for (let j = 0; j < data[i].length; j++) {
            points.push(new BMap.Point(data[i][j].lng, data[i][j].lat));
          }

          this.addPolyline(BMap, map, data, points, i);
        }

      },
      // 添加折线
      addPolyline (BMap, map, data, points, index) {
        let polyline = '';
        polyline = new BMap.Polyline(points, {
          // 创建折线
          enableEditing: false, // 是否启用线编辑，默认为false
          enableClicking: true, // 是否响应点击事件，默认为true
          strokeColor: this.colorList[index % 8].lineColor, // 设置折线颜色
          strokeWeight: 9, // 折线宽度
          strokeOpacity: 1, // 折线透明度
          strokeStyle:'solid'
        });

        var name = this.mapListUser.items[index].name;
        polyline.addEventListener("click", function(e){
          this.setStrokeStyle = 'dashed';
          var opts = {
            width : 100,        // 信息窗口宽度
            height: 50,         // 信息窗口高度
            title : "信息窗口" , // 信息窗口标题
            enableMessage:false//设置允许信息窗发送短息
          };
          var point = new BMap.Point(points[0].lng, points[0].lat);
          var infoWindow = new BMap.InfoWindow("人员 <br> 姓名 :" + name, opts);  // 创建信息窗口对象
          map.openInfoWindow(infoWindow, point); //开启信息窗口
        });

        map.addOverlay(polyline); // 将折线添加到地图

        for (let j = 0; j < points.length; j++) {
          this.addMarker(BMap, map, new BMap.Point(data[index][j].lng, data[index][j].lat), j + 1, index);
        }
      },
      // 添加标注
      addMarker (BMap, map, point, number, index) {
        let marker = '';
        let label = '';
        // url: 图标地址, Size: 图标可视网格大小, anchor: 图标定位点相对于图标左上角的偏移值
        let myIcon = new BMap.Icon(
          this.colorList[index % 8].icon,
          new BMap.Size(20, 20),
          {
            anchor: new BMap.Size(10, 10)
          }
        );
        myIcon.setImageSize(new BMap.Size(20, 20));         // 设置icon大小
        marker = new BMap.Marker(point, { icon: myIcon });  // 创建图像标注

        map.addOverlay(marker);                             // 将标注添加到地图

        label = new BMap.Label(number, {
          offset: new BMap.Size(6, 3.5)
        });
        label.setStyle({
          // 设置文本标注样式
          fontWeight: 600,
          fontSize: '10px',
          color: '#fff',
          backgroundColor: '0',
          border: 0,
        });
        marker.setLabel(label); // 为标注添加文本标注
      },

      handleFilter() {
        this.listQuery.page = 1
        this.listQuery.start_date = parseTime(this.listQuery.start_date);
        console.log("请求轨迹用的参数 ",this.listQuery)
        fetchAllList(this.listQuery).then(response => {
          this.mapList.length = 0
          console.log("获取 页面数据 ",response.data.items)
          for (let i = 0; i < response.data.items.length; i++) {
            this.mapList[i] = response.data.items[i].positions;
            if(i == 0){

              this.center = response.data.items[i].positions[0];
            }
            this.mapListUser.items[i] = response.data.items[i];
          }
          console.log('this.mapList = ',this.mapList.length);
          if(this.mapList.length > 0) {
            this.show_btn.dingwei = true;
            this.$notify({
              title: '轨迹查看',
              message: parseTime(this.listQuery.start_date,'{y}-{m}-{d}') + '找到'+this.mapList.length+"条轨迹数据",
              type: 'success',
              duration: 2000
            })
            this.drawMap()
          }else{
            this.show_btn.dingwei = false;
            this.$notify({
              title: '轨迹查看',
              message: parseTime(this.listQuery.start_date,'{y}-{m}-{d}') + '没有找到轨迹数据',
              type: 'success',
              duration: 2000
            })
          }
        })
      },
    }
  }
</script>

<style>
  .bm-view {
    width: 100%;
    height: 550px;
  }
</style>
