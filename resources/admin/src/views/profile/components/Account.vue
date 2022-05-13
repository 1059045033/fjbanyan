<template>
  <el-form ref="loginForm" :rules="updateRules" :model="user">
    <el-form-item label="新密码" prop="password">
      <el-input v-model.trim="user.password" />
    </el-form-item>
    <el-form-item>
      <el-button type="primary" @click="submit">更新</el-button>
    </el-form-item>
  </el-form>
</template>

<script>
import { validUsername } from '@/utils/validate'
import { updateInfo } from '@/api/user'

export default {
  props: {
    user: {
      type: Object,
      default: () => {
        return {
          name: '',
          email: '',
          password: ''
        }
      }
    }
  },
  data() {
    const validatePassword = (rule, value, callback) => {
      if (value.length < 6) {
        callback(new Error('密码不能少于6位'))
      } else {
        callback()
      }
    }
    return {
      updateRules: {
        password: [{ trigger: 'blur', validator: validatePassword }]
      }
    }
  },
  methods: {
    submit() {
      this.$refs.loginForm.validate(valid => {
        console.log('handleLogin = ', this.user)
        if (valid) {
          const tempData = Object.assign({}, this.user)
          console.log('tempData = ', tempData)
          updateInfo(tempData).then((res) => {
            this.$notify({
              title: '成功',
              message: '更新成功',
              type: 'success',
              duration: 2000
            })

            // await this.$store.dispatch('user/logout')
            // console.log('--=更新密成功 退出当前登入=---',this.$route.fullPath)
            // this.$store.dispatch('user/logout')
            // this.$router.push({ path: '/login'})
          })
        } else {
          console.log('error submit!!')
          return false
        }
      })
      // this.$message({
      //   message: '管理员信息更新成功',
      //   type: 'success',
      //   duration: 5 * 1000
      // })
    }
  }
}
</script>
