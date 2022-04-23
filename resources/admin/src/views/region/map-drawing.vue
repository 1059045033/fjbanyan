<template>
  <baidu-map
    class="map"
    center="福州"
    :zoom="16"
    @mousemove="syncPolyline"
    @click="paintPolyline"
    @rightclick="newPolyline"
  >

    <bm-control>
      <button class="el-button el-button--primary el-button--medium" @click="toggle('polyline')"><i class="el-icon-share" />{{ polyline.editing ? '停止绘制1' : '开始绘制1' }}</button>
    </bm-control>
    <bm-polygon v-for="path of polyline.paths" :path="path" stroke-color="blue" :stroke-opacity="0.5" :stroke-weight="2" />
  </baidu-map>
</template>

<script>
export default {
  data() {
    return {
      polyline: {
        editing: false,
        paths: []
      },
      quyu: []
    }
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
        temp.push({ 'lng': path[i]['lng'], 'lat': path[i]['lat'] })
      }
      console.log('当前图形 = ', temp)
      this.quyu.push(temp)
      console.log('图形集合 = ', this.quyu)
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
    }
  }
}
</script>

<style>
  .map {
    width: 100%;
    height: 400px;
  }
</style>
