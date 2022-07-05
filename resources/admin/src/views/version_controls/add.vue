<template>
  <div class="createPost-container">
    <el-form ref="postForm" :model="postForm" :rules="rules" class="form-container">

      <sticky :z-index="10" :class-name="'sub-navbar '+postForm.status">
        <el-button v-loading="loading" style="margin-left: 10px;" type="success" @click="submitForm">
          保存
        </el-button>
      </sticky>

      <div class="createPost-main-container">
        <el-row>
          <el-col :span="24">
            <el-form-item style="margin-bottom: 40px;" prop="version">
              <MDinput v-model="postForm.version" :maxlength="100" name="version" required>
                版本号
              </MDinput>
            </el-form-item>

            <el-form-item style="margin-bottom: 40px;" prop="version_name">
              <MDinput v-model="postForm.version_name" :maxlength="100" name="version_name" required>
                版本名称
              </MDinput>
            </el-form-item>

            <el-form-item style="margin-bottom: 40px;" prop="description">
              <MDinput v-model="postForm.description" :maxlength="100" name="description" required>
                描述
              </MDinput>
            </el-form-item>

            <el-form-item style="margin-bottom: 40px;" prop="update_url">
              <MDinput v-model="postForm.update_url" :maxlength="100" name="update_url" required>
                apkUrl
              </MDinput>
            </el-form-item>

            <el-form-item style="margin-bottom: 40px;" prop="radio_force" >
              <el-radio-group v-model="postForm.update_force">
                <el-radio :label="0">
                  不强制更新
                </el-radio>
                <el-radio :label="1">
                  强制更新
                </el-radio>
              </el-radio-group>
            </el-form-item>

            <el-form-item style="margin-bottom: 40px;" prop="radio_type">
              <el-radio-group v-model="postForm.update_type">
                <el-radio :label="0">
                  所有
                </el-radio>
                <el-radio :label="1">
                  3级别
                </el-radio>
                <el-radio :label="2">
                  一二级别
                </el-radio>
              </el-radio-group>
            </el-form-item>


          </el-col>
        </el-row>

        <el-form-item prop="image_uri" style="margin-bottom: 30px;">
            <Upload v-model="postForm.image_uri"  @upload-success="uploadSuccess"/>
        </el-form-item>


      </div>
    </el-form>
  </div>
</template>
<script>

  import Upload from '@/components/Upload/SingleApk' //上传apk
  import MDinput from '@/components/MDinput'   //输入框
  import Sticky from '@/components/Sticky'     // 粘性header组件
  import { fetchVersion,saveVersion } from '@/api/common'



  const defaultForm = {
    status: 'draft',
    version: '',
    version_name: '',
    description: '',
    update_url: '',
    update_force: 1,
    update_type: 0,
    id: undefined,
  }

  export default {
    name: 'ArticleDetail',
    components: {  MDinput, Upload, Sticky },
    props: {
      isEdit: {
        type: Boolean,
        default: false
      }
    },
    data() {
      return {
        postForm: Object.assign({}, defaultForm),
        loading: false,
        userListOptions: [],
        rules: {
          version: [{ required: true, message: '名字必填', trigger: 'blur' }],
          version_name: [{ required: true, message: '名字必填', trigger: 'blur' }],
          description: [{ required: true, message: '名字必填', trigger: 'blur' }],
          update_url: [{ required: true, message: '名字必填', trigger: 'blur' }],
        },
      }
    },
    created() {
      this.fetchData(2)
    },
    methods: {
      uploadSuccess(resData) {
        this.postForm.update_url = resData.url
      },
      fetchData(id) {
        fetchVersion(id).then(response => {
          console.log('版本信息 :',response.data)
          this.postForm = response.data

          console.log('update_force : ',response.data.update_force)
          console.log('update_type : ',response.data.update_type)

          console.log('postForm : ',this.postForm)

          // this.postForm.radio_force = response.data.update_force
          // this.postForm.radio_type = response.data.update_type
        }).catch(err => {
          console.log(err)
        })
      },
      submitForm() {

        saveVersion(this.postForm).then(response => {
          console.log('版本信息 :',response.data)
          this.postForm = response.data
          if(response.code == 200)
          {
            this.$notify({
              title: '成功',
              message: '版本信息更新成功',
              type: 'success',
              duration: 2000
            })
            this.$emit('upload-success', res.data)
          }

        }).catch(err => {
          console.log(err)
        })
      },
      prepareUpload() {
        const { url, createImgUrl, field, ki } = this
        this.$emit('crop-success', createImgUrl, field, ki)
        if (typeof url === 'string' && url) {
          this.upload()
        } else {
          this.off()
        }
      },
    }
  }
</script>

<style lang="scss" scoped>
  @import "~@/styles/mixin.scss";

  .createPost-container {
    position: relative;

    .createPost-main-container {
      padding: 40px 45px 20px 50px;

      .postInfo-container {
        position: relative;
        @include clearfix;
        margin-bottom: 10px;

        .postInfo-container-item {
          float: left;
        }
      }
    }

    .word-counter {
      width: 40px;
      position: absolute;
      right: 10px;
      top: 0px;
    }
  }

  .article-textarea ::v-deep {
    textarea {
      padding-right: 40px;
      resize: none;
      border: none;
      border-radius: 0px;
      border-bottom: 1px solid #bfcbd9;
    }
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
