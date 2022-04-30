<template>
  <div>
    <child @emit="emit" :emit="emit"></child>
  </div>
</template>
<script>
  import child from "./child.vue";
  export default {
    components: {
      child,
    },
    created() {
      window.emit = this.emit;//声明全局方法（直男模式）
    },
    provide() {
      return {
        inject_emit: this.emit,//在这里对外提供方法，在子孙组件中都可以调用
      };
    },
    methods: {
      emit(v1, v2, v3) {
        alert(`${v1}\n${v2}\n${v3}`); //弹出子组件的传参
      },
    },
  };
</script>
