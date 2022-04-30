<template>
  <ul>
    <li> <button @click=" $emit( 'emit', '方式1：传参给父组件第1个参数', '方式1：传参给父组件第2个参数', '...' ) " > 方式1：用$emit传参给父组件（推荐此方式） </button> </li> <br />
    <li> <button @click=" emit( '方式2：传参给父组件第1个参数', '方式2：传参给父组件第2个参数', '...' ) " > 方式2：用props属性传进来的函数（方法Function）传递值给父组件 </button> </li> <br />
    <li> <button @click=" $parent.emit( '方式3：传参给父组件第1个参数', '方式3：传参给父组件第2个参数', '...' ) " > 方式3：用$parent.functionName调用父组件的方法（不推荐，需要依赖父组件初始化完毕该方法） </button> </li> <br />
    <li> <button @click=" inject_emit( '方式4：传参给父组件第1个参数', '方式4：传参给父组件第2个参数', '...' ) " > 方式4：用inject关联父组件provide的方法（这种方式比较生僻，子孙组件都可以直接调用，适用于层级较深的组件关系） </button> </li> <br />
    <li> <button @click=" window_emit( '方式5：传参给父组件第1个参数', '方式5：传参给父组件第2个参数', '...' ) " > 方式5：用window的全局方法（此方式属于直男模式，呵呵~） </button> </li> <br />
  </ul>
</template>
<script>
  export default {
    inject: ["inject_emit"], //注入方法（子孙组件均可直接调用）
    props: ["emit"], //传入方法
    methods: {
      window_emit(v1,v2,v3){
        window.emit(v1,v2,v3)
      }
    }
  };
</script>
