<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>栏目管理</el-breadcrumb-item>
        <el-breadcrumb-item>栏目老师管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <el-row class="top-menu">
      <el-row class="nav clearfix">
        <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr">添加栏目老师</el-button>
      </el-row>
      <el-form :inline="true" :model="formInline">
        <el-row>
          <el-form-item label="所属栏目">
            <el-select v-model="formInline.category_code" clearable placeholder="请选择">
              <el-option v-for="category in categoryList" :value-key="category.code" :key="category.code" :label="category.name" :value="category.code"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item>
            <el-button type="primary" icon="el-icon-search" @click="onSearch" @keydown.13="KeySearch($event)" class="search" round>查询</el-button>
          </el-form-item>
        </el-row>
      </el-form>
    </el-row>

    <!-- 列表 -->
    <el-row class="table-menu">
      <el-table
        :data="tablePageData"
        stripe
        style="width: 100%">
        <el-table-column fixed prop="user_name" label="老师姓名"></el-table-column>
        <el-table-column prop="category_code" label="栏目分类Code"></el-table-column>
        <el-table-column prop="description" label="老师简介"></el-table-column>
        <el-table-column prop="created_at" label="创建时间"></el-table-column>
        <el-table-column label="活跃状态" width="100">
          <template slot-scope="scope">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              active-color="#13ce66"
              inactive-color="#999"
              v-model="scope.row.active"
              @change="changeActive(scope.row)">
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column fixed="right" align="center" label="操作" wdith="160">
          <template slot-scope="scope">
            <el-button @click.native="showEditDialog(scope.row.id)" type="text" size="small">编辑</el-button>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <!-- 添加栏目老师 -->
    <el-dialog title="添加栏目老师" :visible.sync="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="125px" :rules="addFormRules" ref="addForm">
        <el-row>
          <el-form-item label="所属栏目分类" prop="category_code">
            <el-select v-model="addForm.category_code" clearable placeholder="请选择" @change="getUserList">
              <el-option v-for="category in categoryList" :value-key="category.code" :key="category.code" :label="category.name" :value="category.code"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="关联用户" prop="user_id" >
            <el-select v-model="addForm.user_id" clearable placeholder="请选择">
              <el-option v-for="user in userList" :value-key="user.id" :key="user.id" :label="user.name" :value="user.id"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="头像">
            <el-upload ref="addIcon" :action="iconUploadUrl" :file-list="addIconImgFile"
                      list-type="picture" :on-success="addIconUploadSuccess" :on-error="uploadError"
                      :data="imgObj" :before-upload="uploadIconBefore" :limit="1" :on-remove="handleIconImgRemove"
                      :on-exceed="handleExceed" :with-credentials="true">
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="ul-upload__tip">(要求：图片尺寸 {{iconImg.size}} px、大小不超过 {{iconImg.fileSize}} K)</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="视频地址（访客）">
            <el-input v-model="addForm.visitor_video_url" placeholder="请输入"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="视频地址（客户）">
            <el-input v-model="addForm.customer_video_url" placeholder="请输入"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="封面">
            <el-upload ref="addCover" :action="coverUploadUrl" :file-list="addCoverImgFile"
                      list-type="picture" :on-success="addCoverUploadSuccess" :on-error="uploadError"
                      :data="imgObj" :before-upload="uploadCoverBefore" :limit="1" :on-remove="handleCoverImgRemove"
                      :on-exceed="handleExceed" :with-credentials="true">
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="ul-upload__tip">(要求：图片尺寸 {{coverImg.size}} px、大小不超过 {{coverImg.fileSize}} K)</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="详细描述">
            <el-input v-model="addForm.description" placeholder="请输入"></el-input>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确定</el-button>
        <el-button @click.native="addVisible = false">取消</el-button>
      </div>
    </el-dialog>

    <!-- 编辑栏目老师 -->
    <el-dialog title="编辑栏目老师" :visible.sync="editVisible" :close-on-click-modal="false" center>
      <el-form :model="editForm" label-width="125px" :rules="editFormRules" ref="editForm">
        <el-row>
          <el-form-item label="所属栏目分类" prop="category_code">
            <el-select v-model="editForm.category_code" clearable placeholder="请选择" @change="getUserListOfEdit">
              <el-option v-for="category in categoryList" :value-key="category.code" :key="category.code" :label="category.name" :value="category.code"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="关联用户" prop="user_id">
            <el-select v-model="editForm.user_id" clearable placeholder="请选择">
              <el-option v-for="user in userList" :value-key="user.id" :key="user.id" :label="user.name" :value="user.id"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="头像">
            <el-upload ref="editIcon" :action="iconUploadUrl" :file-list="editIconImgFile"
                      list-type="picture" :on-success="editIconUploadSuccess" :on-error="uploadError"
                      :data="imgObj" :before-upload="uploadIconBefore" :limit="1" :on-remove="handleIconImgRemove"
                      :on-exceed="handleExceed" :with-credentials="true">
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="ul-upload__tip">(要求：图片尺寸 {{iconImg.size}} px、大小不超过 {{iconImg.fileSize}} K)</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="视频地址（访客）">
            <el-input v-model="editForm.visitor_video_url" placeholder="请输入"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="视频地址（客户）">
            <el-input v-model="editForm.customer_video_url" placeholder="请输入"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="封面">
            <el-upload ref="editCover" :action="coverUploadUrl" :file-list="editCoverImgFile"
                      list-type="picture" :on-success="editCoverUploadSuccess" :on-error="uploadError"
                      :data="imgObj" :before-upload="uploadCoverBefore" :limit="1" :on-remove="handleCoverImgRemove"
                      :on-exceed="handleExceed" :with-credentials="true">
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="ul-upload__tip">(要求：图片尺寸 {{coverImg.size}} px、大小不超过 {{coverImg.fileSize}} K)</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="详细描述">
            <el-input v-model="editForm.description" placeholder="请输入"></el-input>
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
import HTTP from '../../http/api_column'
import Env from '../../http/env'
import Pagination from '@/components/Pagination'

export default {
  name: 'Teacher',
  data () {
    return {
      totalAll: 0,        // 列表总数目
      pageSize: 10,       // 分页显示数目
      pageNo: 1,          // 当前页码
      pageRefresh: true,   // 分页内容刷新

      tablePageData: [],  // 分页显示数据
      categoryList: [],   // 栏目代码列表
      userList: [],       // 用户列表
      // 搜索区表单
      formInline: {category_code: ''},

      // 缓存搜索数据
      searchParams: {category_code: ''},

      // 上传图片规格
      iconImg: {
        'size': '120*120',
        'fileSize': '30'
      },

      coverImg: {
        'size': '750*416',
        'fileSize': '100'
      },

      //  上传图片
      addIconImgFile: [],
      addCoverImgFile: [],
      editIconImgFile: [],
      editCoverImgFile: [],
      iconUploadUrl: `${Env.baseURL}/column/teacher/upload/icon`,
      coverUploadUrl: `${Env.baseURL}/column/teacher/upload/cover`,
      imgObj: {'image': {}},

      // 新增栏目老师
      addVisible: false,
      addLoading: false,
      addFormRules: {
        user_id: [
          {required: true, message: '请选择关联用户', trigger: 'blur'}
        ],
        category_code: [
          {required: true, message: '请选择栏目分类Code', trigger: 'blur'}
        ]
      },
      addForm: {
        user_id: '',
        category_code: '',
        icon_url: '',
        visitor_video_url: '',
        customer_video_url: '',
        cover_url: '',
        description: ''
      },

      // 编辑栏目老师
      editVisible: false,
      editLoading: false,
      editFormRules: {
        category_code: [
          {required: true, message: '请选择栏目分类Code', trigger: 'blur'}
        ],
        user_id: [
          {required: true, message: '请选择关联用户', trigger: 'blur'}
        ]
      },
      editForm: {
        id: '',
        user_id: '',
        category_code: '',
        icon_url: '',
        visitor_video_url: '',
        customer_video_url: '',
        cover_url: '',
        description: ''
      }

    }
  },
  components: {
    Pagination
  },
  created: function () {
  },
  mounted: function () {
    this.getColumnList()
    this.getList()
  },
  methods: {
    // 获取栏目列表
    getColumnList () {
      HTTP.getCategoryList().then(data => {
        if (data.code === 0) {
          this.categoryList = data.data.category_list
        } else {
          console.log(data.msg)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    getUserList (value) {
      this.addForm.user_id = ''
      let params = {
        category_code: value
      }
      HTTP.getUserList(params).then(data => {
        if (data.code === 0) {
          this.userList = data.data.user_list
        } else {
          console.log('服务器错误:' + data.msg)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    getUserListByCategoryCodeAndTeacherId (categoryCode, teacherId) {
      let params = {
        category_code: categoryCode,
        teacher_id: teacherId
      }
      HTTP.getUserList(params).then(data => {
        if (data.code === 0) {
          this.userList = data.data.user_list
        } else {
          console.log('服务器错误：' + data.msg)
        }
      })
    },

    getUserListOfEdit (value) {
      this.editForm.user_id = ''
      this.getUserListByCategoryCodeAndTeacherId(value, this.editForm.id)
    },

    initPagination () {
      this.pageRefresh = false
      this.$nextTick(() => { this.pageRefresh = true })
    },

    gotoPage (page) {
      this.pageNo = page
      this.getList()
    },

    // 获取列表
    getList () {
      var params = Object.assign({}, this.searchParams)
      params.page_no = this.pageNo
      params.page_size = this.pageSize
      HTTP.getTeacherListOfPaging(params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.teacher_list
          this.totalAll = res.data.teacher_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '获取栏目老师列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    updateList () {
      this.getList()
    },

    // ---------------------------   上传图片模块   ----------------------------------
    uploadIconBefore (file) {
      console.log(file)

      return this.uploadBefore(file, this.iconImg)
    },

    uploadCoverBefore (file) {
      console.log(file)

      return this.uploadBefore(file, this.coverImg)
    },

    uploadBefore (file, filetype) {
      let imgType = [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/bmp',
        'image/gif',
        'image/svg'
      ]

      let isJPG = false
      for (let i = 0; i < imgType.length; i++) {
        if (imgType[i] === file.type) {
          isJPG = true
        }
      }

      const isLtFileSize = file.size / 1024 <= filetype.fileSize

      if (!isJPG) {
        this.$message.error('上传图片只能是 JPG/PNG/GIF/SVG 格式！')
      }
      if (!isLtFileSize) {
        this.$message.error('上传图片大小不能超过 ' + filetype.fileSize + ' K!')
      }
      if (isJPG && isLtFileSize) {
        this.imgObj.image = file
      }

      return isJPG && isLtFileSize
    },

    // 上传图片成功
    addIconUploadSuccess (response, file, addIconImgFile) {
      if (response.code === 0) {
        this.addForm.icon_url = response.data.path
        this.addIconImgFile = [{
          name: response.data.path.substr(response.data.path.lastIndexOf('/') + 1),
          url: response.data.path
        }]
      } else {
        console.error(response.msg)
      }
    },
    addCoverUploadSuccess (response, file, addCoverImgFile) {
      if (response.code === 0) {
        this.addForm.cover_url = response.data.path
        this.addCoverImgFile = [{
          name: response.data.path.substr(response.data.path.lastIndexOf('/') + 1),
          url: response.data.path
        }]
      } else {
        console.error(response.msg)
      }
    },
    editIconUploadSuccess (response, file, editIconImgFile) {
      if (response.code === 0) {
        this.editForm.icon_url = response.data.path
        this.editIconImgFile = [{
          name: response.data.path.substr(response.data.path.lastIndexOf('/') + 1),
          url: response.data.path
        }]
      } else {
        console.error(response.msg)
      }
    },
    editCoverUploadSuccess (response, file, editCoverImgFile) {
      if (response.code === 0) {
        this.editForm.cover_url = response.data.path
        this.edieCoverImgFile = [{
          name: response.data.path.substr(response.data.path.lastIndexOf('/') + 1),
          url: response.data.path
        }]
      } else {
        console.error(response.msg)
      }
    },

    // 上传图片失败
    uploadError (response, file, ImgFile) {
      console.error('上传失败，请重试! ')
    },

    handleIconImgRemove (file, fileList) {
      this.addForm.icon_url = ''
      this.editForm.icon_url = ''
    },

    handleCoverImgRemove (file, fileList) {
      this.addForm.cover_url = ''
      this.editForm.cover_url = ''
    },

    handleExceed (file, ImageFile) {
      this.$alert('只能上传一张图片')
    },

    onSearch () {
      this.pageNo = 1
      this.searchParams = this.formInline
      let searchParams = this.filterParams(this.searchParams)
      HTTP.getTeacherListOfPaging(searchParams).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.teacher_list
          this.totalAll = res.data.teacher_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '查询失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    KeySearch (ev) {
      this.onSearch()
    },

    // 改变老师活跃状态
    changeActive (row) {
      // 活跃状态取反并发送请求
      let activeStatus = row.active === 1 ? 1 : 0
      HTTP.changeActive(row.id, activeStatus).then(data => {
        if (data.code === 0) { console.log('活跃状态改变') }
      }).catch(err => {
        console.error(err)
      })
    },

    // 添加
    showAddDialog () {
      this.userList = []
      this.addIconImgFile = []
      this.addCoverImgFile = []
      this.addVisible = true
      this.addForm = {
        user_id: '',
        category_code: '',
        icon_url: '',
        visitor_video_url: '',
        customer_video_url: '',
        cover_url: '',
        description: ''
      }

      // 清空input框验证状态
      setTimeout(() => {
        this.$refs.addForm.clearValidate()
      }, 100)
    },

    addSubmit () {
      let _this = this
      this.$refs.addForm.validate((valid) => {
        if (valid) {
          _this.addLoading = true
          HTTP.addTeacher(_this.addForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: '新增成功', duration: 2000})
              _this.updateList()
              _this.addVisible = false
            } else {
              _this.$message.error({showClose: true, message: '新增失败'})
              _this.addVisible = false
            }
            _this.addLoading = false
          }).catch(err => {
            console.err(err)
            _this.addLoading = false
          })
          _this.$refs.addIcon.clearFiles()
          _this.$refs.addCover.clearFiles()
        }
      })
    },

    // 编辑
    showEditDialog (teacherId) {
      this.userList = []
      this.editIconImgFile = []
      this.editCoverImgFile = []
      this.editVisible = true
      HTTP.getTeacherInfo(teacherId).then(data => {
        if (data.code === 0) {
          let teacherInfo = data.data.teacher_info
          this.editForm = {
            id: teacherInfo.id,
            user_id: teacherInfo.user_id,
            category_code: teacherInfo.category_code,
            icon_url: teacherInfo.icon_url,
            visitor_video_url: teacherInfo.visitor_video_url,
            customer_video_url: teacherInfo.customer_video_url,
            cover_url: teacherInfo.cover_url,
            description: teacherInfo.description
          }
          if (teacherInfo.icon_url) {
            this.editIconImgFile = [{
              name: teacherInfo.icon_url.substr(teacherInfo.icon_url.lastIndexOf('/') + 1),
              url: teacherInfo.icon_url
            }]
          }

          if (teacherInfo.cover_url) {
            this.editCoverImgFile = [{
              name: teacherInfo.cover_url.substr(teacherInfo.cover_url.lastIndexOf('/') + 1),
              url: teacherInfo.cover_url
            }]
          }
          this.getUserListByCategoryCodeAndTeacherId(teacherInfo.category_code, teacherInfo.id)
        }
        setTimeout(() => {
          this.$refs.editForm.clearValidate()
        }, 100)
      })
    },

    editSubmit () {
      let _this = this
      this.$refs.editForm.validate((valid) => {
        if (valid) {
          _this.editLoading = true
          HTTP.updateTeacher(_this.editForm.id, _this.editForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: '编辑成功', duration: 2000})
              _this.editVisible = false
              _this.updateList()
            } else {
              _this.$message.error({showClose: true, message: '编辑失败', duration: 2000})
              _this.editVisible = false
            }
            _this.editLoading = false
          }).catch(err => {
            console.error(err)
            _this.editLoading = false
          })
          _this.$refs.editIcon.clearFiles()
          _this.$refs.editCover.clearFiles()
        }
      })
    },

    // 删除对象中值为空的字属性
    filterParams (obj) {
      let newObj = {}
      for (var key in obj) {
        if (obj[key] !== '') {
          newObj[key] = obj[key]
        }
      }
      return newObj
    }

  }
}
</script>
