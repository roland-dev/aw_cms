<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>教学管理</el-breadcrumb-item>
        <el-breadcrumb-item :to="{ path: '/teaching/course' }">课程管理</el-breadcrumb-item>
        <el-breadcrumb-item>视频管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu"> 
      <el-row class="nav clearfix">
        <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr">添加视频</el-button>
      </el-row>
    </el-row>

    <!-- 列表 -->
    <el-row class="table-menu">
      <el-table
        :data="tablePageData"
        stripe
        style="width: 100%">
        <el-table-column fixed prop="title" label="视频名称"></el-table-column>
        <el-table-column prop="display" label="是否可见"></el-table-column>
        <el-table-column prop="access" label="访问PV/UV"></el-table-column>
        <el-table-column prop="watch" label="观看PV/UV"></el-table-column>
        <el-table-column prop="end" label="看完PV/UV"></el-table-column>
        <el-table-column prop="sort_no" label="排序序号" width="160">
          <template slot-scope="scope">
            <div @click="dbClickEdit(scope.row.id, scope.row.sort_no,  $event.target)">{{scope.row.sort_no}}</div>
            <input type="text" v-model.number="scope.row.sort_no" style="display: none;" @blur="ClickEditEnd(scope.row.id, scope.row.sort_no, $event.target)">
          </template>
        </el-table-column>
        <el-table-column prop="creator_name" label="最后修改人"></el-table-column>
        <el-table-column prop="updated_at" label="最后修改时间"></el-table-column>
        <el-table-column fixed="right" align="center" label="操作" width="100">
          <template slot-scope="scope">
            <el-button  @click.native="showEditDialog(scope.row.video_signin_id, scope.row.id)" type="text" size="small">编辑</el-button>
            <el-button  @click.native="deleteCourseVideo(scope.row.video_signin_id, scope.row.id)" type="text" size="small">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <!-- 添加 -->
    <el-dialog title="添加课程视频" :visible.sync ="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="120px" :rules="addFormRules" ref="addForm">
        <el-row>
          <el-form-item label="视频名称" prop="name">
            <el-input v-model="addForm.name" placeholder="请输入" :maxlength="20"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="封面图片" prop="thumbnail_preview_path">
            <el-upload class="upload-demo" :on-preview="handlePreview" :on-remove="handleRemove"  :action="imgUrl" :file-list="addImgFile" list-type="picture" :on-success="uploadSuccess" :on-error="uploadError" :data="imgObj" :before-upload="uploadBefore" :limit="1" :with-credentials='true'>
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="el-upload__tip">(推荐图片尺寸238*133)</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="视频链接" prop="url">
            <el-input v-model="addForm.url" placeholder="请输入" :maxlength="300"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="是否可见" prop="is_display">
            <el-switch
              active-color="#13ce66"
              inactive-color="#999"
              v-model="addForm.is_display"
              :active-value="1"
              :inactive-value="0">
            </el-switch>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="视频作者" prop="author_id">
            <el-select v-model="addForm.author_id" clearable placeholder="请选择">
              <el-option v-for="authorInfo in authorList" :value-key="authorInfo.id" :key="authorInfo.name" :label="authorInfo.name" :value="authorInfo.id"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="排序序号" prop="sort_no">
            <el-input v-model.number="addForm.sort_no" placeholder="请输入" :maxlength="20"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="专属标签" prop="tag">
            <el-input v-model.number="addForm.tag" placeholder="请输入" :maxlength="20"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="试看视频" prop="demo_url">
            <el-input v-model.number="addForm.demo_url" placeholder="请输入" :maxlength="500"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="广告语" prop="ad_guide">
            <el-input type="textarea" :rows="4"  v-model="addForm.ad_guide" placeholder="请输入" :maxlength="200"></el-input>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确定</el-button>
        <el-button @click.native="addVisible = false">取消</el-button>        
      </div>
    </el-dialog>

    <!-- 编辑 -->
    <el-dialog title="编辑课程视频" :visible.sync ="editVisible" :close-on-click-modal="false" center>
      <el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">
        <el-row>
          <el-form-item label="视频名称" prop="name">
            <el-input v-model="editForm.name" placeholder="请输入" :maxlength="20"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="封面图片" prop="thumbnail_path">
            <el-upload class="upload-demo" :action="editImgUrl" :on-preview="handlePreview" :on-remove="editHandleRemove"  :data="editImgObj" :file-list="editForm.editImgFile" list-type="picture" :on-success="editUploadSuccess" :on-error="editUploadError" :before-upload="editUploadBefore" :with-credentials='true' :limit="1">
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="el-upload__tip">推荐尺寸238*133</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="视频链接" prop="url">
            <el-input v-model="editForm.url" placeholder="请输入" :maxlength="300"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="是否可见" prop="is_display">
            <el-switch :active-value="1" :inactive-value="0"  active-color="#13ce66" inactive-color="#999" v-model="editForm.is_display"></el-switch>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="视频作者" prop="author_id">
            <el-select v-model="editForm.author_id" clearable placeholder="请选择">
              <el-option v-for="authorInfo in authorList" :value-key="authorInfo.id" :key="authorInfo.name" :label="authorInfo.name" :value="authorInfo.id"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="专属标签" prop="tag">
            <el-input v-model="editForm.tag" placeholder="请输入" :maxlength="300"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="试看视频" prop="demo_url">
            <el-input v-model.number="editForm.demo_url" placeholder="请输入" :maxlength="500"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="广告语" prop="ad_guide">
            <el-input type="textarea" :rows="4" v-model="editForm.ad_guide" placeholder="请输入" :maxlength="200"></el-input>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="editSubmit" :loading="editLoading">确定</el-button>
        <el-button @click.native="editVisible = false">取消</el-button>        
      </div>
    </el-dialog>
  </div> 
</template>

<script>
import Env from '../../http/env'
import API_TEACHING from '../../http/api_teaching'
import Pagination from '@/components/Pagination'

export default {
  name: 'CourseVideo',
  data () {
    return {
      totalAll: 0,          // 列表总数目
      pageSize: 10,         // 分页显示数目
      pageNo: 1,            // 当前页码
      pageRefresh: true,    // 分页内容刷新

      tablePageData: [],    // 分页显示数据
      authorList: [],     // 获取视频作者列表
      // 新增课程
      addVisible: false, // 是否显示
      addLoading: false,
      addFormRules: {
        name: [{required: true, message: '请输入视频名称', trigger: 'blur'}],
        sort_no: [{required: true, message: '请输入排列顺序', trigger: 'blur'}, {type: 'number', message: '请输入数字', trigger: 'blur'}],
        thumbnail_preview_path: [{required: true, message: '请上传封面图片', trigger: 'blur'}],
        url: [{required: true, message: '请输入视频链接', trigger: 'blur'}, {type: 'url', message: '请输入格式正确的url', trigger: 'blur'}],
        author_id: [{required: true, message: '请选择视频作者', trigger: 'blur'}],
        demo_url: [{type: 'url', message: '请输入格式正确的url', trigger: 'blur'}]
      },
      // addForm: {name: '', image_path: '', thumbnail_path: '', thumbnail_preview_path: '', url: '', is_display: 0, author_id: '', course_id: '', sort_no: '', tag: ''},
      addForm: {name: '', thumbnail_preview_path: '', url: '', is_display: 0, author_id: '', course_id: '', sort_no: '', tag: '', demo_url: '', ad_guide: ''},

      // 上传图片    上传图片预览在addImgFile数组里面[{name: '', url: ''}]
      addImgFile: [],
      imgUrl: `${Env.baseURL}/resource/coursesystem/course/image`,
      imgObj: {'image': {}, 'image_path': '', 'thumbnail_path': ''},

      editImgFile: [],
      editImgUrl: `${Env.baseURL}/resource/coursesystem/course/image`,
      editImgObj: {'image': {}, 'image_path': '', 'thumbnail_path': ''},

      // 编辑课程
      editVisible: false, // 是否显示
      editLoading: false,
      editFormRules: {
        name: [{required: true, message: '请输入视频名称', trigger: 'blur'}],
        thumbnail_path: [{required: true, message: '请上传封面图片', trigger: 'blur'}],
        url: [{required: true, message: '请输入视频链接', trigger: 'blur'}, {type: 'url', message: '请输入格式正确的url', trigger: 'blur'}],
        author_id: [{required: true, message: '请选择视频作者', trigger: 'blur'}],
        demo_url: [{type: 'url', message: '请输入格式正确的url', trigger: 'blur'}]
      },
      editForm: {name: '', url: '', is_display: 0, author_id: '', course_id: '', thumbnail_path: '', video_signin_id: '', course_video_id: '', category_code: '', demo_url: '', ad_guide: ''},
      globalSortNo: ''
    }
  },
  components: {
    Pagination
  },
  mounted: function () {
    this.getList()
    this.getVideoAuthorList()
  },
  methods: {
    initPagination () {
      this.pageRefresh = false
      this.$nextTick(() => { this.pageRefresh = true })
    },

    // 跳转分页
    gotoPage (page) {
      this.pageNo = page
      this.getList()
    },

    // 获取列表内容
    getList () {
      var params = {
        page_no: this.pageNo,
        page_size: this.pageSize
      }
      // 获取当前页面的url中id
      API_TEACHING.getVideoList(this.$route.params.code, params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.course_video_list
          this.totalAll = res.data.course_video_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '获取课程视频列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    getVideoAuthorList () {
      API_TEACHING.getVideoAuthorList().then(data => {
        console.log(data)
        this.authorList = data.data.video_author_list
      }).catch(err => {
        console.error(err)
      })
    },

    // // 更新表格
    updateList () {
      this.getList()
    },

    // 双击修改表格区域内容
    dbClickEdit (id, sortNo, element) {
      // 消失div显示input
      this.globalSortNo = sortNo
      element.style = 'display: none'
      element.nextSibling.nextSibling.style = 'display: block; width: 100px;'
      // 聚焦点到input
      element.nextSibling.nextSibling.focus()
    },

    // 双击修改表格区离开事件
    ClickEditEnd (id, sortNo, element) {
      // 发送请求
      let orderForm = {sequence: element.value, course_video_id: id}
      if (element.value.length > 0) {
        if (this.globalSortNo !== parseInt(element.value)) {
          API_TEACHING.courseVideoOrder(orderForm).then(data => {
            this.getList()
          }).catch(err => {
            console.error(err)
          })
        } else {
          element.value = this.globalSortNo
        }
      } else {
        // 为空则不修改，并返回oldhtml(这个地方后面要绑定你的model对象)
        this.getList()
      }
      element.style = 'display: none'
      element.previousElementSibling.style = 'display: block'
    },

    // 添加
    showAddDialog () {
      this.addVisible = true
      this.addForm = {
        name: '',
        // image_path: '',
        url: '',
        is_display: 0,
        author_id: '',
        thumbnail_preview_path: '',
        sort_no: '',
        course_code: this.$route.params.code
      }
      this.addImgFile = []
      this.imgObj.image_path = ''
      this.imgObj.thumbnail_path = ''
      // 清空input框验证状态
      setTimeout(() => {
        this.$refs.addForm.clearValidate()
      }, 100)
    },

    addSubmit () {
      let _this = this
      this.$refs.addForm.validate((valid) => {
        if (valid) {
          API_TEACHING.addVideo(_this.addForm).then(data => {
            if (data.code === 300001) {
              this.checkConflictUrl(data.data.video.created_at, data.data.video.title)
            } else if (data.code === 0) {
              _this.$message.success({showClose: true, message: '新增成功', duration: 2000})
              _this.updateList()
            } else {
              _this.$message.error({showClose: true, message: '新增失败', duration: 2000})
            }
            _this.addVisible = false
          }).catch(err => {
            console.error(err)
          })
          _this.addVisible = false
        }
      })
    },

    checkConflictUrl (date, title) {
      this.$confirm('该视频已经于' + date + '登记， 海报主题为：' + title + ', 请您重新确定信息 ', '提示', {
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
      })
    },

    showEditDialog (videoId, courseVideoId) {
      this.editVisible = true
      // 请求一个当前id
      API_TEACHING.getOneVideoInfo(videoId, courseVideoId).then(res => {
        console.log(res)
        this.editForm = {
          url: res.data.info.url,
          author_id: res.data.info.author_id,
          is_display: res.data.info.is_display,
          course_id: res.data.info.course_id,
          editImgFile: [{name: res.data.info.image_name, url: res.data.info.thumbnail_preview_path}],

          name: res.data.info.title,
          // image_path: res.data.info.image_path,
          thumbnail_path: res.data.info.picture_path,
          // thumbnail_preview_path: res.data.info.thumbnail_preview_path,
          course_video_id: res.data.info.id,
          video_signin_id: res.data.info.video_signin_id,
          category_code: res.data.info.category_code,
          tag: res.data.info.tag,
          demo_url: res.data.info.demo_url,
          ad_guide: res.data.info.ad_guide
        }

        this.editImgObj.image_path = res.data.info.image_path
        this.editImgObj.thumbnail_path = res.data.info.thumbnail_path

        setTimeout(() => {
          this.$refs.editForm.clearValidate()
        }, 100)
      })
    },

    editSubmit () {
      let _this = this
      this.$refs.editForm.validate((valid) => {
        if (valid) {
          API_TEACHING.editVideo(_this.editForm).then(data => {
            if (data.code === 300001) {
              this.checkConflictUrl(data.data.video.created_at, data.data.video.title)
            } else if (data.code === 0) {
              _this.$message.success({showClose: true, message: '编辑成功', duration: 2000})
              this.updateList()
            } else {
              _this.$message.error({showClose: true, message: '编辑失败', duration: 2000})
            }
            _this.editVisible = false
          }).catch(err => {
            console.error(err)
          })
          _this.editVisible = false
        }
      })
    },

    deleteCourseVideo (videoId, courseVideoId) {
      this.$confirm('是否确定删除该条记录?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        API_TEACHING.removeVideo(videoId, courseVideoId).then(res => {
          if (res.code === 0) {
            this.$message.success({showClose: true, message: '删除成功', duration: 2000})
            this.updateList()
          }
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消删除'
        })
      })
    },

    // 上传图片模块
    uploadBefore (file) {
      // post请求中image类型为文件对象
      // this.imgObj.image = file
      let imgType = [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/bmp',
        'image/gif',
        'image/svg'
      ]
      let isJPG = false

      for (var i = 0; i < imgType.length; i++) {
        if (imgType[i] === file.type) {
          isJPG = true
        }
      }

      const isLt500K = file.size / 1024 <= 500

      if (!isJPG) {
        this.$message.error('上传图片只能是 JPG/PNG/GIF/SVG 格式!')
      }
      if (!isLt500K) {
        this.$message.error('上传图片大小不能超过 500k!')
      }
      if (isJPG && isLt500K) {
        this.imgObj.image = file
      }

      return isJPG && isLt500K
    },

    // 上传图片成功
    uploadSuccess (response, file, fileList) {
      // this.addForm.image_path = response.data.image.relatively_file_path
      // this.addForm.thumbnail_path = response.data.thumbnail.relatively_file_path
      // this.addForm.thumbnail_preview_path = response.data.thumbnail.relatively_file_path
      this.addForm.thumbnail_preview_path = response.data.thumbnail.cdn_relatively_file_path
      this.imgObj.image_path = response.data.image.relatively_file_path
      this.imgObj.thumbnail_path = response.data.thumbnail.relatively_file_path
    },
    // 上传错误
    uploadError (response, file, fileList) {
      console.log('上传失败，请重试！')
    },

    // 删除上传图片
    handleRemove () {
      // this.addForm.image_path = ''
      // this.addForm.thumbnail_path = ''
      this.addForm.thumbnail_preview_path = ''
      API_TEACHING.removeImage(this.imgObj).then(res => {
      })
    },

    // 删除上传图片
    editHandleRemove () {
      // this.editForm.thumbnail_preview_path = ''
      // this.editForm.image_path = ''
      this.editForm.thumbnail_path = ''

      // API_TEACHING.removeImage(this.editImgObj).then(res => {
      // })
    },

    // 展示上传图片预览
    handlePreview (file) {
      console.log(file)
    },

    editUploadBefore (file) {
      // this.editImgObj.image = file
      let imgType = [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/bmp',
        'image/gif',
        'image/svg'
      ]
      let isJPG = false

      for (var i = 0; i < imgType.length; i++) {
        if (imgType[i] === file.type) {
          isJPG = true
        }
      }

      const isLt500K = file.size / 1024 <= 500

      if (!isJPG) {
        this.$message.error('上传图片只能是 JPG/PNG/GIF/SVG 格式!')
      }
      if (!isLt500K) {
        this.$message.error('上传图片大小不能超过 500k!')
      }
      if (isJPG && isLt500K) {
        this.editImgObj.image = file
      }
      return isJPG && isLt500K
    },

    editUploadSuccess (response, file, fileList) {
      // this.editForm.image_path = response.data.image.relatively_file_path
      this.editForm.thumbnail_path = response.data.thumbnail.cdn_relatively_file_path
      // this.editForm.thumbnail_preview_path = response.data.thumbnail.relatively_file_path
      this.editImgObj.image_path = response.data.image.relatively_file_path
      this.editImgObj.thumbnail_path = response.data.thumbnail.relatively_file_path
    },

    // 上传错误
    editUploadError (response, file, fileList) {
      console.log('上传失败，请重试！')
    }
  }
}
</script>

<style scoped lang="less">
.avatar-uploader{
  .el-upload {
    border: 1px dashed #d9d9d9;
    border-radius: 6px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    .avatar-uploader-icon {
      font-size: 28px;
      color: #8c939d;
      width: 178px;
      height: 178px;
      line-height: 178px;
      text-align: center;
    }
    .avatar {
      width: 178px;
      height: 178px;
      display: block;
    }
  }
  .el-upload:hover {
    border-color: #409EFF;
  }
} 

</style>
