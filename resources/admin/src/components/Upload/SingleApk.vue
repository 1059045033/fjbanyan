<template>
  <div>
    <input ref="excel-upload-input" class="excel-upload-input" type="file" accept=".apk,.jpg,.png"
           @change="handleClick">
    <div class="drop" >
      <el-button :loading="loading" style="margin-left:16px;" size="mini" type="primary" @click="handleUpload">
        点击上传
      </el-button>

      <span :style="progressStyle" class="vicp-progress" v-if="false" />

    </div>

  </div>
</template>

<script>
  import { fileUpload } from '@/api/common'
  export default {
    props: {
      beforeUpload: Function, // eslint-disable-line
      onSuccess: Function// eslint-disable-line
    },
    data() {
      return {
        loading: false,
        progress: 10
      }
    },
    computed: {
      // 进度条样式
      progressStyle() {
        const {progress} = this
        return {
          width: progress + '%'
        }
      },
    },
    methods: {
      handleUpload() {
        console.log('点击 handleUpload')
        this.$refs['excel-upload-input'].click()
      },
      handleClick(e) {
        const files = e.target.files
        console.log('files : ',files)
        const rawFile = files[0] // only use files[0]
        console.log('rawFile : ',rawFile)
        if (!rawFile) return
        this.upload(rawFile)
      },
      upload(rawFile) {
        console.log("文件进行上传")
        console.log('input value : ',this.$refs['excel-upload-input'].value)

        var formData = new FormData();
        formData.append('file',rawFile);
        var flag;
        fileUpload(formData).then(res=>{
          console.log(res)
          if(res.code == 200)
          {
            this.$notify({
              title: '成功',
              message: 'apk上传成功',
              type: 'success',
              duration: 2000
            })
            this.$emit('upload-success', res.data)
          }

        });
        this.$refs['excel-upload-input'].value = null // fix can't select the same excel
      }
    }
  }
</script>

<style scoped>
  .excel-upload-input{
    display: none;
    z-index: -9999;
  }
  .drop{
    border: 2px dashed #bbb;
    width: 600px;
    height: 160px;
    line-height: 160px;
    margin: 0 auto;
    font-size: 24px;
    border-radius: 5px;
    text-align: center;
    color: #bbb;
    position: relative;
  }
  .vicp-progress {
    position: relative;
    display: block;
    height: 5px;
    border-radius: 3px;
    background-color: #4a7;
    -webkit-box-shadow: 0 2px 6px 0 rgba(68, 170, 119, 0.3);
    box-shadow: 0 2px 6px 0 rgba(68, 170, 119, 0.3);
    -webkit-transition: width 0.15s linear;
    transition: width 0.15s linear;
    background-image: -webkit-linear-gradient(
      135deg,
      rgba(255, 255, 255, 0.2) 25%,
      transparent 25%,
      transparent 50%,
      rgba(255, 255, 255, 0.2) 50%,
      rgba(255, 255, 255, 0.2) 75%,
      transparent 75%,
      transparent
    );
    background-image: linear-gradient(
      -45deg,
      rgba(255, 255, 255, 0.2) 25%,
      transparent 25%,
      transparent 50%,
      rgba(255, 255, 255, 0.2) 50%,
      rgba(255, 255, 255, 0.2) 75%,
      transparent 75%,
      transparent
    );
    background-size: 40px 40px;
    -webkit-animation: vicp_progress 0.5s linear infinite;
    animation: vicp_progress 0.5s linear infinite;
  }
</style>
