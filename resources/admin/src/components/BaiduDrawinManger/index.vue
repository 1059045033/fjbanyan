<template>
  <baidu-map class="map" center="福州" @ready="ready" />
</template>
<script>

import DrawingManager from 'bmaplib.drawingmanager'

export default {
  name: 'BaiduDrawingManger',
  props: {},
  date() {
    return {
    }
  },
  mounted() {
  },
  methods: {
    ready({ BMap, map }) {
      console.log('地图加载完了')
      this.BMap = BMap
      this.map = map
      // this.map.setDefaultCursor('pointer');

      const styleOptions = {
        strokeColor: 'red', // 边线颜色。
        fillColor: 'red', // 填充颜色。当参数为空时，圆形将没有填充效果。
        strokeWeight: 3, // 边线的宽度，以像素为单位。
        strokeOpacity: 0.8, // 边线透明度，取值范围0 - 1。
        fillOpacity: 0.6, // 填充的透明度，取值范围0 - 1。
        strokeStyle: 'dashed' // 边线的样式，solid或dashed。
      }
      console.log('BMap ==== ', this.BMap)
      console.log('map ==== ', this.map)

      // myBMapLib().then(BMapLib => {
      //   console.log(BMapLib);
      //   let drawingManager = new BMapLib.DrawingManager(this.map, {
      //     isOpen: true, //是否开启绘制模式
      //     enableDrawingTool: true, //是否显示工具栏
      //     drawingToolOptions: {
      //       anchor: BMAP_ANCHOR_TOP_RIGHT, //位置
      //       offset: new this.BMap.Size(5, 5),   //偏离值
      //       drawingModes:[
      //         BMAP_DRAWING_POLYGON       //工具只显示多边形
      //       ]
      //     }
      //   });
      // })

      const drawingManager = new DrawingManager(this.map, {
        isOpen: true, // 是否开启绘制模式
        enableDrawingTool: true // 是否显示工具栏
      })

      console.log('dangqidrawingManager = ', drawingManager)

      // let drawingManager = new DrawingManager(this.map, {
      //     isOpen: true,    //是否开启绘制模式
      //     enableDrawingTool: true,
      //     drawingType : 'BMAP_DRAWING_POLYGON',
      //     enableCalculate:false,
      //     enableSorption:true,
      //     sorptiondistance:20,
      //     rectangleOptions:styleOptions
      // });
      // drawingManager._setDrawingMode('BMAP_DRAWING_POLYGON');
      // console.log('drawingManager = ',drawingManager)
      // drawingManager.open();
    }
  }
}
</script>
<style>
  #allmap {
    width: 100%;
    height: 300px;
  }
  #map {
    width: 100%;
    height: 300px;
  }
  .map {
    width: 100%;
    height: 400px;
  }
</style>

