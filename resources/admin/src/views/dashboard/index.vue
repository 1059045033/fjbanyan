<template>
  <div class="dashboard-container">
    <component :is="currentRole" />
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import adminDashboard from './admin'
import editorDashboard from './editor'
import { getAllCompany } from '@/api/common'

export default {
  name: 'Dashboard',
  components: { adminDashboard, editorDashboard },
  data() {
    return {
      currentRole: 'adminDashboard',
      // companies:[],
      listQuery: {
        id: undefined
      },
    }
  },
  computed: {
    ...mapGetters([
      'roles'
    ])
  },
  created() {
    if (!this.roles.includes('admin')) {
      this.currentRole = 'editorDashboard'
    }
    // console.log('父组件 : 获取公司列表')
    // getAllCompany(this.listQuery).then(response => {
    //   this.companies = [];
    //   for(let i=0;i<response.data.length;i++){
    //     this.companies.push(response.data[i]['name'])
    //   }
    //   console.log("父组件 : 公司列表 ",this.companies)
    // })
  }
}
</script>
