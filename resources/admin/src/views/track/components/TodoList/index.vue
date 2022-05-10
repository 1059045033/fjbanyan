<template>
  <section class="todoapp">
    <!-- header -->
    <header class="header">
      <input class="new-todo" autocomplete="no" placeholder="人员列表">
    </header>

    <!-- main section -->
    <section v-show="todos.length" class="main">
      <ul class="todo-list">
        <todo
          v-for="(todo, index) in filteredTodos"
          :key="index"
          :todo="todo"
          @toggleTodo="toggleTodo"
        />
      </ul>
    </section>
    <!-- footer -->
    <footer v-show="todos.length" class="footer">
          <ul class="filters">
            <li><a @click="pre_page()">上页</a></li>
            <li><a >第{{listQuery.page}}页</a></li>
            <li><a @click="next_page()">下页</a></li>
          </ul>
    </footer>
  </section>
</template>

<script>
import { fetchAllList } from '@/api/tracks'
import Todo from './Todo.vue'

const STORAGE_KEY = 'todos'

const filters = {
  all: todos => todos,
  active: todos => todos.filter(todo => !todo.done),
  completed: todos => todos.filter(todo => todo.done)
}
const regionList = []
export default {
  components: { Todo },
  filters: {
    pluralize: (n, w) => n === 1 ? w : w + 's',
    capitalize: s => s.charAt(0).toUpperCase() + s.slice(1)
  },
  data() {
    return {
      visibility: 'all',
      filters,
      regionList,
      todos: regionList,
      listQuery: {
        page: 1,
        limit: 10,
        importance: undefined,
        title: undefined,
        type: undefined,
        sort: '+id',
        name: undefined,
      },
      page_total:0,
      myPageType:this.pageType
    }
  },
  created() {
    console.log('当前是[task | whereabouts]',this.myPageType)
    this.getList()
  },
  props:['pageType'],
  computed: {
    allChecked() {
      return this.todos.every(todo => todo.done)
    },
    filteredTodos() {
      return filters[this.visibility](this.todos)
    },
    remaining() {
      return this.todos.filter(todo => !todo.done).length
    }
  },
  methods: {
    getList() {
      console.log("地图左侧获取 网格列表");
      this.listQuery.pageType = this.myPageType;
      fetchAllList(this.listQuery).then(response => {
        // 1.将列表数据置空
        this.regionList.splice(0);
        console.log('date = ',response.data)
        this.page_total = Math.ceil(response.data.total/this.listQuery.limit)
        // 2.重新填充列表数据
        for (let i = 0; i < response.data.items.length; i++) {
          this.regionList.push({
            text:response.data.items[i]['name'],
            done:false,
            id:response.data.items[i]['user_id'],
            zhongxin:response.data.items[i]['positions'][0],
            positions:response.data.items[i]['positions']

          })
        }
      })
    },
    next_page() {
      console.log("点击下一页 ")
      if(this.page_total != this.listQuery.page){
        this.listQuery.page = this.listQuery.page + 1;
        this.getList();
        console.log(this.todos);
      }else{
        console.log("最后一页了 ")
      }
    },
    pre_page() {
      console.log("点击上一页 ")
      if(this.listQuery.page > 1)
      {
        this.listQuery.page = this.listQuery.page - 1;
        this.getList();

      }
    },
    refresh_page(){
      this.listQuery.page = 1;
      this.getList();
      //console.log(' ==== ',filters[this.visibility](this.todos))
    },
    setLocalStorage() {
      //console.log("设置本地缓存",JSON.stringify(this.todos))
      window.localStorage.setItem(STORAGE_KEY, JSON.stringify(this.todos))
    },
    addTodo(e) {
      console.log("添加Todo 改为查找 Todo")
      // const text = e.target.value
      // if (text.trim()) {
      //   this.todos.push({
      //     text,
      //     done: false
      //   })
      //   this.setLocalStorage()
      // }
      // e.target.value = ''
    },
      toggleTodo(val) {
      console.log('点击Todo组件')

      for (let i=0;i<this.todos.length;i++){
        console.log('name',this.todos[i]['text'],' done',this.todos[i]['done'],' id=',this.todos[i]['id']);//this.todos[i]['done'] = false;
      }

      // 调用父组件的 clickOverlay 方法
      console.log('调用父组件的 clickOverlay 方法')
      this.$emit('selectOverlay', val)
      //val.done = !val.done //这个false与否只是改变 是否划线
      this.setLocalStorage()
    },
    deleteTodo(todo) {
      this.todos.splice(this.todos.indexOf(todo), 1)
      this.setLocalStorage()
    },
    editTodo({ todo, value }) {
      todo.text = value
      this.setLocalStorage()
    },
    clearCompleted() {
      this.todos = this.todos.filter(todo => !todo.done)
      this.setLocalStorage()
    },
    toggleAll({ done }) {
      this.todos.forEach(todo => {
        todo.done = done
        this.setLocalStorage()
      })
    }
  }
}
</script>


<style lang="scss">
  @import './index.scss';
</style>
