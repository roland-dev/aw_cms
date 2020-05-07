<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>栏目管理</el-breadcrumb-item>
        <el-breadcrumb-item>栏目分类管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu">
      <el-row class="nav clearfix">
        <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr">添加栏目分类</el-button>
      </el-row>
      <el-form :inline="true" :model="formInline">
        <el-row>
          <el-form-item label="栏目分类名称" prop="name">
            <el-input v-model="formInline.name" placeholder="请输入"></el-input>
          </el-form-item>
          <el-form-item label="所属服务key">
            <el-select v-model="formInline.service_key" clearable placeholder="请选择">
              <el-option v-for="service in serviceList" :value-key="service.code" :key="service.code" :label="service.name" :value="service.code"></el-option>
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
        <el-table-column fixed prop="name" label="栏目分类名称"></el-table-column>
        <el-table-column prop="code" label="栏目分类Code"></el-table-column>
        <el-table-column prop="service_key" label="服务Key"></el-table-column>
        <el-table-column prop="created_at" label="创建时间"></el-table-column>
        <el-table-column fixed="right" align="center" label="操作" width="160">
          <template slot-scope="scope">
            <el-button @click.native="toSubCategoryManage(scope.row.code)" type="text" size="small">子栏目管理</el-button>
            <el-button @click.native="showEditDialog(scope.row.id)" type="text" size="small" >编辑</el-button>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <!-- 添加栏目分类 -->
    <el-dialog title="添加栏目分类" :visible.sync="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="100px" :rules="addFormRules" ref="addForm">
        <el-row>
          <el-form-item label="栏目名称" prop="name">
            <el-input v-model="addForm.name" placeholder="请输入" :maxlength="20"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="栏目code值" prop="code">
            <el-input v-model="addForm.code" placeholder="请输入" :maxlength="64"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="摘要" prop="summary">
            <el-input v-model="addForm.summary" placeholder="请输入" :maxlength="50"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="详细描述" prop="description">
            <el-input v-model="addForm.description" placeholder="请输入" type="textarea" :row="3">
            </el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="栏目海报">
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
          <el-form-item label="广告海报">
            <el-upload ref="addAdCover" :action="adCoverUploadUrl" :file-list="addAdCoverImgFile"
                      list-type="picture" :on-success="addAdCoverUploadSuccess" :on-error="uploadError"
                      :data="imgObj" :before-upload="uploadAdCoverBefore" :limit="1" :on-remove="handleAdCoverImgRemove"
                      :on-exceed="handleExceed" :with-credentials="true">
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="ul-upload__tip">(要求：图片尺寸 {{adCoverImg.size}} px、大小不超过 {{adCoverImg.fileSize}} K)</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="服务key值" prop="service_key">
            <el-select v-model="addForm.service_key" clearable placeholder="请选择">
              <el-option v-for="service in serviceList" :value-key="service.code" :key="service.code" :label="service.name" :value="service.code"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确定</el-button>
        <el-button @click.native="addVisible = false">取消</el-button> 
      </div>
    </el-dialog>

    <!-- 编辑栏目分类 -->
    <el-dialog title="编辑栏目分类" :visible.sync="editVisible" :close-on-click-modal="false" center>
      <el-form :model="editForm" label-width="100px" :rules="editFormRules" ref="editForm">
        <el-row>
          <el-form-item label="栏目名称" prop="name">
            <el-input v-model="editForm.name" placeholder="请输入" :maxlength="20"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="栏目code值" prop="code">
            <el-input v-model="editForm.code" placeholder="请输入" :maxlength="64" :disabled="true"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="摘要" prop="summary">
            <el-input v-model="editForm.summary" placeholder="请输入" :maxlength="50"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="详细描述" prop="description">
            <el-input v-model="editForm.description" placeholder="请输入" type="textarea" :row="3"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="栏目海报">
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
          <el-form-item label="广告海报">
            <el-upload ref="editAdCover" :action="adCoverUploadUrl" :file-list="editAdCoverImgFile"
                      list-type="picture" :on-success="editAdCoverUploadSuccess" :on-error="uploadError"
                      :data="imgObj" :before-upload="uploadAdCoverBefore" :limit="1" :on-remove="handleAdCoverImgRemove"
                      :on-exceed="handleExceed" :with-credentials="true">
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="ul-upload__tip">(要求：图片尺寸 {{adCoverImg.size}} px、大小不超过 {{adCoverImg.fileSize}} K)</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="服务key值" prop="service_key">
            <el-select v-model="editForm.service_key" clearable placeholder="请选择">
              <el-option v-for="service in serviceList" :value-key="service.code" :key="service.code" :label="service.name" :value="service.code"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="主笔老师" prop="primary_teacher_id">
            <el-select v-model="editForm.primary_teacher_id" clearable placeholder="请选择">
              <el-option v-for="teacher in teacherListOfCategoryCode" :value-key="teacher.id" :key="teacher.id" :label="teacher.name" :value="teacher.id"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="editSubmit" :loading="editLoading">确认</el-button>
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
  name: 'Category',
  data () {
    return {
      totalAll: 0,        // 列表总数目
      pageSize: 10,       // 分页显示数目
      pageNo: 1,          // 当前页码
      pageRefresh: true,  // 分页内容刷新

      tablePageData: [],  // 分页显示数据
      serviceList: [],    // 服务代码列表
      teacherListOfCategoryCode: [],
      // 搜索区表单
      formInline: {name: '', service_key: ''},

      // 缓存搜索数据
      searchParams: {name: '', service_key: ''},

      coverImg: {
        'size': '750*420',
        'fileSize': '80'
      },
      adCoverImg: {
        'size': '限制宽度 1125',
        'fileSize': '300'
      },

      // 上传图片
      addCoverImgFile: [],
      editCoverImgFile: [],
      coverUploadUrl: `${Env.baseURL}/column/category/upload/cover`,

      addAdCoverImgFile: [],
      editAdCoverImgFile: [],
      adCoverUploadUrl: `${Env.baseURL}/column/category/upload/adCover`,

      imgObj: {'image': {}},

      // 新增栏目分类
      addVisible: false,
      addLoading: false,
      addFormRules: {
        name: [{required: true, message: '请输入栏目分类名称', trigger: 'blur'}],
        code: [{required: true, message: '请输入栏目分类Code值', trigger: 'blur'}, {validator: this.checkCodeUnique, trigger: 'blur'}],
        service_key: [{required: true, message: '请选择服务key值', trigger: 'blur'}]
      },
      addForm: {
        name: '',
        code: '',
        summary: '',
        description: '',
        cover_url: '',
        ad_image_url: '',
        service_key: ''
      },

      // 编辑栏目分类
      editVisible: false,
      editLoading: false,
      editFormRules: {
        name: [{required: true, message: '请输入栏目分类名称', trigger: 'blur'}],
        code: [{required: true, message: '请输入栏目分类Code值', trigger: 'blur'}],
        service_key: [{required: true, message: '请选择服务key值', trigger: 'blur'}]
      },
      editForm: {
        id: '',
        name: '',
        code: '',
        summary: '',
        description: '',
        cover_url: '',
        service_key: '',
        ad_image_url: '',
        primary_teacher_id: ''
      }

    }
  },
  components: {
    Pagination
  },
  created: function () {
  },
  mounted: function () {
    this.getServiceList()
    this.getList()
  },
  methods: {
    // 获取服务列表
    getServiceList () {
      HTTP.getServiceList().then(data => {
        if (data.code === 0) {
          this.serviceList = data.data
        } else {
          console.log(data.msg)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    initPagination () {
      this.pageRefresh = false
      this.$nextTick(() => { this.pageRefresh = true })
    },

    // 跳转分页
    gotoPage (page) {
      this.pageNo = page
      this.getList()
    },

    // 获取列表
    getList () {
      let params = Object.assign({}, this.searchParams)
      params.page_no = this.pageNo
      params.page_size = this.pageSize
      HTTP.getCategoryListOfPaging(params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.category_list
          this.totalAll = res.data.category_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '获取栏目分类列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    updateList () {
      this.getList()
    },

    onSearch () {
      this.pageNo = 1
      this.searchParams = this.formInline
      let searchParams = this.filterParams(this.searchParams)
      HTTP.searchCategory(searchParams).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.category_list
          this.totalAll = res.data.category_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '查询失败:' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    KeySearch (ev) {
      this.onSearch()
    },

    // --------------------------    上传图片模块    ----------------------------
    uploadCoverBefore (file) {
      console.log(file)

      return this.uploadBefore(file, this.coverImg)
    },

    uploadAdCoverBefore (file) {
      console.log(file)
      return this.uploadBefore(file, this.adCoverImg)
    },

    uploadBefore (file, filetype) {
      let imgType = [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/bmg',
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
        this.$message.error('上传图片大小不能超过 ' + filetype.fileSize + 'K!')
      }
      if (isJPG && isLtFileSize) {
        this.imgObj.image = file
      }

      return isJPG && isLtFileSize
    },

    // 上传图片成功
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

    addAdCoverUploadSuccess (response, file, addAdCoverImgFile) {
      if (response.code === 0) {
        this.addForm.ad_image_url = response.data.path
        this.addAdCoverImgFile = [{
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
        this.editCoverImgFile = [{
          name: response.data.path.substr(response.data.path.lastIndexOf('/') + 1),
          url: response.data.path
        }]
      } else {
        console.log(response.msg)
      }
    },

    editAdCoverUploadSuccess (response, file, editAdCoverImgFile) {
      if (response.code === 0) {
        this.editForm.ad_image_url = response.data.path
        this.editAdCoverImgFile = [{
          name: response.data.path.substr(response.data.path.lastIndexOf('/') + 1),
          url: response.data.path
        }]
      } else {
        console.log(response.msg)
      }
    },

    // 上传图片失败
    uploadError (response, file, ImgFile) {
      console.log('上传失败，请重试！')
    },

    handleCoverImgRemove (file, fileList) {
      this.addForm.cover_url = ''
      this.editForm.cover_url = ''
    },

    handleAdCoverImgRemove (file, fileList) {
      this.addForm.ad_image_url = ''
      this.editForm.ad_image_url = ''
    },

    handleExceed (file, ImageFile) {
      this.$alert('只能上传一张图片')
    },

    toSubCategoryManage (categoryCode) {
      this.$router.push({name: '子栏目分类管理', params: {'categoryCode': categoryCode}})
    },

    // chech categoryCode unique
    checkCodeUnique (rule, value, callback) {
      HTTP.checkCategoryCodeUnique(value).then(res => {
        if (res.code === 0) {
          if (res.data.check_res.length > 0) {
            callback(new Error('栏目分类Code重复，请重新输入'))
          } else {
            callback()
          }
        } else {
          console.error(res.code)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 添加
    showAddDialog () {
      this.addCoverImgFile = []
      this.addAdCoverImgFile = []
      this.addVisible = true
      this.addForm = {
        name: '',
        code: '',
        summary: '',
        description: '',
        cover_url: '',
        ad_image_url: '',
        service_key: ''
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
          HTTP.addCategory(_this.addForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: '新增成功', duration: 2000})
              _this.updateList()
              _this.addVisible = false
            } else if (data.code === 351001) {
              _this.$message.error({showClose: true, message: '栏目分类已存在', duration: 2000})
              _this.addVisible = false
            } else {
              _this.$message.error({showClose: true, message: '新增失败', duration: 2000})
              _this.addVisible = false
            }
            _this.addLoading = false
          }).catch(err => {
            console.error(err)
            _this.addLoading = false
          })
          _this.$refs.addCover.clearFiles()
          _this.$refs.addAdCover.clearFiles()
        }
      })
    },

    // 编辑
    showEditDialog (categoryId) {
      this.editCoverImgFile = []
      this.editAdCoverImgFile = []
      this.editVisible = true
      HTTP.getCategoryInfo(categoryId).then(data => {
        if (data.code === 0) {
          let categoryInfo = data.data.category_info
          this.getTeacherListOfCategoryCode(categoryInfo.code)
          this.editForm = {
            id: categoryInfo.id,
            name: categoryInfo.name,
            code: categoryInfo.code,
            summary: categoryInfo.summary,
            description: categoryInfo.description,
            cover_url: categoryInfo.cover_url,
            ad_image_url: categoryInfo.ad_image_url,
            service_key: categoryInfo.service_key,
            primary_teacher_id: categoryInfo.primary_teacher_id
          }
          if (categoryInfo.cover_url) {
            this.editCoverImgFile = [{
              name: categoryInfo.cover_url.substr(categoryInfo.cover_url.lastIndexOf('/') + 1),
              url: categoryInfo.cover_url
            }]
          }
          if (categoryInfo.ad_image_url) {
            this.editAdCoverImgFile = [{
              name: categoryInfo.ad_image_url.substr(categoryInfo.ad_image_url.lastIndexOf('/') + 1),
              url: categoryInfo.ad_image_url
            }]
          }
          setTimeout(() => {
            this.$refs.editForm.clearValidate()
          }, 100)
        }
      })
    },

    // 获取 栏目当中的老师列表
    getTeacherListOfCategoryCode (categoryCode) {
      HTTP.getTeacherListOfCategoryCode(categoryCode).then(data => {
        if (data.code === 0) {
          this.teacherListOfCategoryCode = data.data.teacher_list
        } else {
          console.log(data.msg)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    editSubmit () {
      let _this = this
      this.$refs.editForm.validate((valid) => {
        if (valid) {
          _this.editLoading = true
          HTTP.updateCategory(_this.editForm.id, _this.editForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: '编辑成功', duration: 2000})
              _this.editVisible = false
              _this.updateList()
            } else if (data.code === 351001) {
              _this.$message.error({showClose: true, message: '栏目分类已存在', duration: 2000})
              _this.editVisible = false
            } else {
              _this.$message.error({showClose: true, message: '编辑失败', duration: 2000})
              _this.editVisible = false
            }
            _this.editLoading = false
          }).catch(err => {
            console.error(err)
            _this.editLoading = false
          })
          _this.$refs.editCover.clearFiles()
          _this.$refs.editAdCover.clearFiles()
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
