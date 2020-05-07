<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>内容管理</el-breadcrumb-item>
        <el-breadcrumb-item>锦囊报告管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu">
      <el-row class="nav clearfix">
        <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr" v-if="isTeacherStockA">添加报告</el-button>
      </el-row>
      <el-form :inline="true" :model="formInline">
        <el-row>
          <el-form-item label="报告名称">
            <el-input v-model="formInline.title" placeholder="请输入"></el-input>
          </el-form-item>
          <el-form-item label="归属锦囊">
            <el-select v-model="formInline.kit_code" clearable placeholder="全部">
              <el-option v-for="item in kits" :value-key="item.code" :key="item.code" :label="item.name" :value="item.code"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="有效状态">
            <el-select v-model="formInline.valid_status" clearable placeholder="全部">
              <el-option v-for="item in validStatus" :value-key="item.status" :key="item.status" :label="item.name" :value="item.status"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="推送状态">
            <el-select v-model="formInline.publish" clearable placeholder="全部">
              <el-option v-for="item in publishStatus" :value-key="item.status" :key="item.status" :label="item.name" :value="item.status"></el-option>
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
      <!-- 锦囊报告列表 -->
      <el-table
        :data="tablePageData"
        stripe
        style="width: 100%">
        <el-table-column fixed prop="title" label="报告名称"></el-table-column>
        <el-table-column prop="kit_name" label="归属锦囊"></el-table-column>
        <el-table-column prop="valid_status_name" label="有效状态"></el-table-column>
        <el-table-column prop="publish_status_name" label="推送状态"></el-table-column>
        <el-table-column prop="last_modify_user_name" label="最后修改人"></el-table-column>
        <el-table-column prop="updated_at" label="最后修改时间"></el-table-column>
        <el-table-column fixed="right" label="操作" width="145" align="center">
          <template slot-scope="scope">
            <el-dropdown>
              <el-button type="primary">
                锦囊报告管理<i class="el-icon-arrow-down el-icon--right"></i>
              </el-button>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item @click.native="showEditDialog(scope.row.id)" v-if="(!scope.row.publish && isTeacherStockA) || modifyPermission">编辑锦囊报告</el-dropdown-item>
                <el-dropdown-item @click.native="delKitReport(scope.row.id)" v-if="!scope.row.publish && isTeacherStockA">删除锦囊报告</el-dropdown-item>
                <el-dropdown-item @click.native="showPreviewDialog(scope.row.id)">查看锦囊报告</el-dropdown-item>
                <el-dropdown-item @click.native="pushKitReport(scope.row.id)" v-if="!scope.row.publish && isTeacherStockA">发布锦囊报告</el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <!-- 添加锦囊报告 -->
    <el-dialog title="添加锦囊报告" :visible.sync="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">
        <el-row>
          <el-form-item label="报告名称" prop="title">
            <el-input v-model="addForm.title" placeholder="请输入" :maxlength="64"></el-input>
          </el-form-item>
          <el-form-item label="作者">
            <el-input :disabled="true" :value="authInfo.name"></el-input>
          </el-form-item>
          <el-form-item label="归属锦囊" prop="kit_code">
            <el-select v-model="addForm.kit_code" placeholder="请选择">
              <el-option v-for="item in kits" :value-key="item.code" :key="item.code" :label="item.name" :value="item.code"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="有效时间" prop="valid_time">
            <el-date-picker
              v-model="addForm.valid_time"
              type="datetimerange"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
              value-format="yyyy-MM-dd HH:mm:ss"
              format="yyyy-MM-dd HH:mm:ss">
            </el-date-picker>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="封面图片" prop="cover_url">
            <el-upload ref="addVCover" :action="uploadUrl" :file-list="addUploadCoverImg" list-type="picture" :on-success="addUploadSuccess"
                      :on-error="uploadError" :data="uploadObj" :before-upload="uploadBefore" :limit="1"
                      :on-remove="handleRemove" :on-exceed="handleExceed" :with-credentials="true">
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="el-upload__tip">(要求：图片尺寸 357*198 px、大小不超过 50 K)</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="报告摘要" prop="summary">
            <el-input type="textarea" v-model="addForm.summary" placeholder="最多输入120个字"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="内容类型" prop="format">
            <el-select v-model="addForm.format" placeholder="请选择" @change="changeFormat">
              <el-option v-for="item in contentTypes" :value-key="item.status" :key="item.status" :label="item.name" :value="item.status"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row v-if="addForm.format === 0">
          <el-form-item label="报告内容" prop="content">
            <editor ref="addEditor" editorId="addContent" :content="addForm.content"></editor>
          </el-form-item>
        </el-row>
        <el-row v-if="addForm.format === 1">
          <el-form-item label="添加附件" prop="url">
            <el-upload ref="addPdf" :action="uploadFileUrl" :file-list="addUploadFile" :on-success="addUploadFileSuccess"
                      :on-error="uploadFileError" :data="uploadFileObj" :before-upload="uploadFileBefore" :limit="1"
                      :on-remove="handleFileRemove" :on-exceed="handleFileExceed" :with-credentials="true">
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="el-uploa__tip">(只能上传pdf格式的文件，且不超过500kb)</span>
            </el-upload>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确定</el-button>
        <el-button @click.native="addVisible = false">取消</el-button>
      </div>
    </el-dialog>

    <!-- 编辑锦囊报告 -->
    <el-dialog title="编辑锦囊报告" :visible.sync="editVisible" :close-on-click-modal="false" center>
      <el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">
        <el-row>
          <el-form-item label="报告名称" prop="title">
            <el-input v-model="editForm.title" placeholder="请输入" :maxlength="64" :disabled="editFormPublish == 1"></el-input>
          </el-form-item>
          <el-form-item label="作者">
            <el-input :disabled="true" :value="editAuthName"></el-input>
          </el-form-item>
          <el-form-item label="归属锦囊" prop="kit_code">
            <el-select v-model="editForm.kit_code" placeholer="请选择" :disabled="editFormPublish == 1">
              <el-option v-for="item in kits" :value-key="item.code" :key="item.code" :label="item.name" :value="item.code"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="有效时间" prop="valid_time">
            <el-date-picker
              v-model="editForm.valid_time"
              type="datetimerange"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
              value-format="yyyy-MM-dd HH:mm:ss"
              format="yyyy-MM-dd HH:mm:ss"
              :disabled="editFormPublish == 1">
            </el-date-picker>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="封面图片" prop="cover_url">
            <el-upload ref="editCover" :action="uploadUrl" :file-list="editUploadCoverImg" list-type="picture" :on-success="editUploadSuccess"
                      :on-error="uploadError" :data="uploadObj" :before-upload="uploadBefore" :limit="1"
                      :on-remove="handleRemove" :on-exceed="handleExceed" :with-credentials="true" :disabled="editFormPublish == 1">
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="el-upload__tip">(要求：图片尺寸 357*198 px、大小不超过 50 K)</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="报告摘要" prop="summary">
            <el-input type="textarea" v-model="editForm.summary" placeholder="最多输入120个字" :disabled="editFormPublish == 1"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="内容类型" prop="format">
            <el-select v-model="editForm.format" placeholder="请选择" @change="changeFormat">
              <el-option v-for="item in contentTypes" :value-key="item.status" :key="item.status" :label="item.name" :value="item.status"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row v-if="editForm.format === 0">
          <el-form-item label="报告内容" prop="content">
            <editor ref="editEditor" editorId="editContent" :content="editForm.content"></editor>
          </el-form-item>
        </el-row>
        <el-row v-if="editForm.format === 1">
          <el-form-item label="添加附件" prop="url">
            <el-upload ref="editPdf" :action="uploadFileUrl" :file-list="editUploadFile" :on-success="editUploadFileSuccess"
                      :on-error="uploadFileError" :data="uploadFileObj" :before-upload="uploadFileBefore" :limit="1"
                      :on-remove="handleFileRemove" :on-exceed="handleFileExceed" :with-credentials="true">
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="el-upload__tip">(只能上传pdf格式的文件，且不超过500kb)</span>
            </el-upload>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="editSubmit" :loading="editLoading">确定</el-button>
        <el-button @click.native="editVisible = false">取消</el-button>
      </div>
    </el-dialog>

    <!-- 预览报告 -->
    <el-dialog title="预览报告" :visible.sync="previewVisible" :close-on-click-modal="false" center>
      <el-row>
        <div id="preview" class="preview">
          <div class="title">{{preview.title}}</div>
          <span class="views">
            <img class="views-icon" :src="preview.belong_user_icon">
            <span>{{preview.belong_user_name}}</span>
          </span>
          <span class="date">{{preview.start_at}}</span>
          <div class="content w-e-text-container" id="kit_report" v-html="preview.content" v-if="preview.format != 1"></div>
          <div><a :href="preview.url" v-if="preview.format == 1">{{preview.title}}.pdf</a></div>
        </div>
      </el-row>
      <div slot="footer" class="dialog-footer">
        <el-button @click.native="previewVisible = false">关闭</el-button>
      </div>
    </el-dialog>

  </div>
</template>
<script>
import Env from '../../http/env'
import API_CONTENT from '../../http/api_content'
import API_USER from '../../http/api_user'
import Pagination from '@/components/Pagination'
import Editor from '@/components/Editor'

export default {
  name: 'KitReport',
  data () {
    return {
      authInfo: [],             // 当前登录人信息
      isTeacherStockA: false,   // 是否是A股牛人老师
      modifyPermission: false,  // 已发布 锦囊报告 编辑权限
      kits: [],                 // 锦囊列表
      validStatus: [],          // 锦囊报告有效状态
      publishStatus: [],        // 锦囊报告发布状态
      contentTypes: [
        {status: 0, name: '图文'},
        {status: 1, name: 'PDF'}
      ],

      // 搜索区表单
      formInline: {title: '', kit_code: '', valid_status: '', publish: ''},

      // 缓存搜索数据
      searchParams: {title: '', kit_code: '', valid_status: '', publish: ''},

      // 表格内容
      tablePageData: [],

      // 分页初始化
      totalAll: 0,        // 列表总数目
      pageSize: 10,       // 分页尺寸
      pageNo: 1,          // 当前页
      pageRefresh: true,  // 分页内容刷新

      // 上传图片 参数
      addUploadCoverImg: [],
      editUploadCoverImg: [],
      uploadUrl: `${Env.baseURL}/kit/report/upload/cover`,
      uploadObj: {'image': {}},

      // 上传文件 参数
      addUploadFile: [],
      editUploadFile: [],
      uploadFileUrl: `${Env.baseURL}/kit/report/upload/file`,
      uploadFileObj: {'file': {}},

      // 新增锦囊报告
      addVisible: false,
      addLoading: false,
      addFormRules: {
        title: [{required: true, message: '请输入报告名称', trigger: 'blur'}],
        kit_code: [{required: true, message: '请选择归属锦囊', trigger: 'blur'}],
        valid_time: [{type: 'array', required: true, message: '请选择有效时间', trigger: 'blur'}],
        cover_url: [{required: true, message: '请上传封面图片', trigger: 'blur'}],
        summary: [
          {required: true, message: '请输入报告摘要', trigger: 'blur'},
          {max: 120, message: '请输入内容最大长度不超过120个字', trigger: 'blur'}
        ],
        format: [{type: 'number', required: true, message: '请选择内容类型', trigger: 'blur'}],
        content: [{required: true, message: '请输入报告内容', trigger: 'blur'}],
        url: [{required: true, message: '请上传pdf文件', trigger: 'blur'}]
      },
      addForm: {title: '', kit_code: '', start_at: '', end_at: '', cover_url: '', summary: '', format: 0, content: '', url: '', valid_time: []},

      // 编辑锦囊报告
      editFormPublish: false,
      editVisible: false,
      editLoading: false,
      editAuthName: '',
      editFormRules: {
        title: [{required: true, message: '请输入报告名称', trigger: 'blur'}],
        kit_code: [{required: true, message: '请选择归属锦囊', trigger: 'blur'}],
        valid_time: [{type: 'array', required: true, message: '请选择有效时间', trigger: 'blur'}],
        cover_url: [{required: true, message: '请上传封面图片', trigger: 'blur'}],
        summary: [
          {required: true, message: '请输入报告摘要', trigger: 'blur'},
          {max: 120, message: '请输入内容最大长度不超过120个字', trigger: 'blur'}
        ],
        format: [{type: 'number', required: true, message: '请选择内容类型', trigger: 'blur'}],
        content: [{required: true, message: '请输入报告内容', trigger: 'blur'}],
        url: [{required: true, message: '请上传pdf文件', trigger: 'blur'}]
      },
      editForm: {id: '', title: '', kit_code: '', start_at: '', end_at: '', cover_url: '', summary: '', format: 0, content: '', url: '', valid_time: []},

      // --- 预览报告 ---
      previewVisible: false,
      preview: {title: '', kit_code: '', start_at: '', end_at: '', cover_url: '', summary: '', format: 0, content: '', url: '', belong_user_icon: '', belong_user_name: ''}
    }
  },
  components: {
    Editor,
    Pagination
  },
  created: function () {
  },
  mounted: function () {
    this.getAuthInfo()
    this.getKits()
    this.getValidStatus()
    this.getPublishStatus()
    if (this.$route.params.kit_code) {
      this.searchParams.kit_code = this.$route.params.kit_code
      this.formInline.kit_code = this.$route.params.kit_code
    }
    this.getList()
  },
  methods: {
    // 获取当前登录人信息
    getAuthInfo () {
      API_USER.user().then(res => {
        if (res.code === 0) {
          this.authInfo = res.data.user_info
        } else {
          this.$message.error({showClose: true, message: '获取登录人信息失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    getKits () {
      API_CONTENT.getKits().then(res => {
        if (res.code === 0) {
          this.kits = res.data.kits
        } else {
          this.kits = []
        }
      }).catch(err => {
        console.error(err)
      })
    },

    getValidStatus () {
      API_CONTENT.getValidStatus().then(res => {
        if (res.code === 0) {
          this.validStatus = res.data.valid_status
        } else {
          this.validStatus = []
        }
      }).catch(err => {
        console.error(err)
      })
    },

    getPublishStatus () {
      API_CONTENT.getPublishStatus().then(res => {
        if (res.code === 0) {
          this.publishStatus = res.data.publish_status
        } else {
          this.publishStatus = []
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

    // 更新表格
    updateList () {
      this.getList()
    },

    getList () {
      var params = Object.assign({}, this.searchParams)
      params.page_no = this.pageNo
      params.page_size = this.pageSize
      API_CONTENT.getKitReportList(this.filterParams(params)).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.kit_report_list
          this.totalAll = res.data.kit_report_cnt
          this.isTeacherStockA = res.data.is_teacher_stock_a
          this.modifyPermission = res.data.modify_permission
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '获取锦囊报告列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    onSearch () {
      this.pageNo = 1
      this.searchParams = this.formInline
      let searchParams = this.filterParams(this.searchParams)
      API_CONTENT.searchKitReportList(searchParams).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.kit_report_list
          this.totalAll = res.data.kit_report_cnt
          this.isTeacherStockA = res.data.is_teacher_stock_a
          this.modifyPermission = res.data.modify_permission
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

    changeFormat (value) {
      this.addForm.content = ''
      this.addForm.url = ''
      this.addUploadFile = []
      this.editFomr.content = ''
      this.editForm.url = ''
      this.editUploadFile = ''
    },

    // 图片上传
    uploadBefore (file) {
      console.log(file)

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

      const isLtFileSize = file.size / 1024 <= 50

      if (!isJPG) {
        this.$message.error('上传图片只能是 JPG/PNG/GIF/SVG 格式!')
      }

      if (!isLtFileSize) {
        this.$message.error('上传图片大小不能超过 50 K!')
      }

      if (isJPG && isLtFileSize) {
        this.uploadObj.image = file
      }

      return isJPG && isLtFileSize
    },

    addUploadSuccess (response, file, addUploadCoverImg) {
      if (response.code === 0) {
        this.addForm.cover_url = response.data.path
        console.log(file)
        this.addUploadCoverImg = [{
          name: response.data.path.substr(response.data.path.lastIndexOf('/') + 1),
          url: response.data.path
        }]
      } else {
        console.error(response.msg)
      }
    },

    editUploadSuccess (response, file, editUploadCoverImg) {
      if (response.code === 0) {
        this.editForm.cover_url = response.data.path
        console.log(file)
        this.editUploadCoverImg = [{
          name: response.data.path.substr(response.data.path.lastIndexOf('/') + 1),
          url: response.data.path
        }]
      } else {
        console.error(response.msg)
      }
    },

    uploadError (response, file, ImgFile) {
      console.error('上传失败，请重试')
    },

    handleRemove (file, fileList) {
      this.addForm.cover_url = ''
      this.editForm.cover_url = ''
    },

    handleExceed (file, fileList) {
      this.$alert('只能上传一个文件')
    },

    // 上传文件
    uploadFileBefore (file) {
      console.log(file)

      let fileType = [
        'application/pdf'
      ]
      let isPDF = false
      for (let i = 0; i < fileType.length; i++) {
        if (fileType[i] === file.type.toLowerCase()) {
          isPDF = true
        }
      }

      const isLtFileSize = file.size / 1024 <= 500

      if (!isPDF) {
        this.$message.error('上传文件只能是 PDF 格式')
      }

      if (!isLtFileSize) {
        this.$message.error('上传文件大小不能超过 500 K!')
      }

      if (isPDF && isLtFileSize) {
        this.uploadFileObj.file = file
      }

      return isPDF && isLtFileSize
    },

    addUploadFileSuccess (response, file, addUploadFile) {
      if (response.code === 0) {
        this.addForm.url = response.data.path
        console.log(file)
        this.addUploadFile = [{
          name: response.data.path.substr(response.data.path.lastIndexOf('/') + 1),
          url: response.data.path
        }]
      } else {
        console.error(response.msg)
      }
    },

    editUploadFileSuccess (response, file, editUploadFile) {
      if (response.code === 0) {
        this.editForm.url = response.data.path
        console.log(file)
        this.editUploadFile = [{
          name: response.data.path.substr(response.data.path.lastIndexOf('/') + 1),
          url: response.data.path
        }]
      } else {
        console.error(response.msg)
      }
    },

    uploadFileError (response, file, PDFfile) {
      console.error('上传失败，请重试!')
    },

    handleFileRemove (file, fileList) {
      this.addForm.url = ''
      this.editForm.url = ''
    },

    handleFileExceed (file, fileList) {
      this.$alert('只能上传一个文件')
    },

    // 新增锦囊
    showAddDialog () {
      let _this = this
      _this.addUploadCoverImg = []
      _this.addUploadFile = []
      setTimeout(() => {
        _this.addVisible = true
      }, 500)
      _this.addForm = {title: '', kit_code: '', start_at: '', end_at: '', cover_url: '', summary: '', format: 0, content: '', url: '', valid_time: []}
      setTimeout(() => {
        _this.$refs.addForm.clearValidate()
        _this.$refs.addEditor.clear()
      }, 600)
    },

    addSubmit () {
      let _this = this
      if (_this.$refs.addEditor) {
        this.addForm.content = _this.$refs.addEditor.setContent()
      }
      this.$refs.addForm.validate((valid) => {
        if (valid) {
          _this.addLoading = true
          _this.addForm.start_at = _this.addForm.valid_time[0]
          _this.addForm.end_at = _this.addForm.valid_time[1]
          let addFormData = Object.assign({}, _this.addForm)
          delete addFormData.valid_time
          console.log(addFormData)
          API_CONTENT.addKitReport(addFormData).then(res => {
            if (res.code === 0) {
              _this.$message.success({showClose: true, message: '新增成功', duration: 2000})
              _this.updateList()
              _this.addVisible = false
              if (_this.$refs.addCover) {
                _this.$refs.addCover.clearFiles()
              }
              if (_this.$refs.addPdf) {
                _this.$refs.addPdf.clearFiles()
              }
            } else {
              this.$message.error({showClose: true, message: '新增失败：' + res.msg, duration: 2000})
            }
            _this.addLoading = false
          }).catch(err => {
            console.error(err)
            _this.addLoading = false
          })
        }
      })
    },

    // 编辑报告
    showEditDialog (id) {
      let _this = this
      _this.editUploadCoverImg = []
      _this.editUploadFile = []
      setTimeout(() => {
        _this.editVisible = true
      })
      API_CONTENT.getKitReport(id).then(res => {
        if (res.code === 0) {
          _this.editForm = {
            id: res.data.kit_report.id,
            title: res.data.kit_report.title,
            kit_code: res.data.kit_report.kit_code,
            start_at: res.data.kit_report.start_at,
            end_at: res.data.kit_report.end_at,
            cover_url: res.data.kit_report.cover_url,
            summary: res.data.kit_report.summary,
            format: res.data.kit_report.format,
            content: res.data.kit_report.content,
            url: res.data.kit_report.url,
            valid_time: [
              res.data.kit_report.start_at,
              res.data.kit_report.end_at
            ]
          }
          _this.editAuthName = res.data.kit_report.belong_user_name
          _this.editFormPublish = res.data.kit_report.publish
          _this.editUploadCoverImg = [{
            name: res.data.kit_report.cover_url.substr(res.data.kit_report.cover_url.lastIndexOf('/') + 1),
            url: res.data.kit_report.cover_url
          }]
          if (res.data.kit_report.url) {
            _this.editUploadFile = [{
              name: res.data.kit_report.url.substr(res.data.kit_report.url.lastIndexOf('/') + 1),
              url: res.data.kit_report
            }]
          }
          setTimeout(() => {
            _this.$refs.editForm.clearValidate()
            if (_this.$refs.editEditor) {
              _this.$refs.editEditor.getContent(_this.editForm.content)
            }
          }, 600)
        } else {
          this.$message.error({showClose: true, message: '查询失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    editSubmit () {
      let _this = this
      if (_this.$refs.editEditor) {
        _this.editForm.content = _this.$refs.editEditor.setContent()
      }
      _this.$refs.editForm.validate((valid) => {
        if (valid) {
          _this.editLoading = true
          _this.editForm.start_at = _this.editForm.valid_time[0]
          _this.editForm.end_at = _this.editForm.valid_time[1]
          let editFormData = Object.assign({}, _this.editForm)
          delete editFormData.valid_time
          API_CONTENT.editKitReport(editFormData.id, editFormData).then(res => {
            if (res.code === 0) {
              _this.$message.success({showClose: true, message: '编辑成功', duration: 2000})
              _this.updateList()
              _this.editVisible = false
              if (_this.$refs.editCover) {
                _this.$refs.editCover.clearFiles()
              }
              if (_this.$refs.editPdf) {
                _this.$refs.editPdf.clearFiles()
              }
            } else {
              _this.$message.error({showClose: true, message: '编辑失败：' + res.msg, duration: 2000})
            }
            _this.editLoading = false
          }).catch(err => {
            console.error(err)
            _this.editLoading = false
          })
        }
      })
    },

    delKitReport (id) {
      this.$confirm('是否确认删除该锦囊报告?', '提示', {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        API_CONTENT.delKitReport(id).then(res => {
          if (res.code === 0) {
            this.$message.success({showClose: true, message: '删除成功', duration: 2000})
            this.updateList()
          } else {
            this.$message.error({showClose: true, message: '删除失败：' + res.msg, duration: 2000})
          }
        }).catch(err => {
          console.error(err)
        })
      }).catch(() => {
        this.$message({type: 'info', message: '已取消删除'})
      })
    },

    showPreviewDialog (id) {
      let _this = this
      _this.previewVisible = true
      API_CONTENT.getKitReport(id).then(res => {
        if (res.code === 0) {
          _this.preview = {
            id: id,
            title: res.data.kit_report.title,
            kit_code: res.data.kit_report.kit_code,
            start_at: res.data.kit_report.start_at,
            end_at: res.data.kit_report.end_at,
            cover_url: res.data.kit_report.cover_url,
            summary: res.data.kit_report.summary,
            format: res.data.kit_report.format,
            content: res.data.kit_report.content,
            url: res.data.kit_report.url,
            belong_user_icon: res.data.kit_report.belong_user_icon,
            belong_user_name: res.data.kit_report.belong_user_name
          }
          setTimeout(() => {
            _this.fitIframe()
          }, 200)
        } else {
          _this.$message.error({showClose: true, message: '查询失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    pushKitReport (id) {
      this.$confirm('发布后数据不能修改，请确认是否发布?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        API_CONTENT.pushKitReport(id).then(res => {
          if (res.code === 0) {
            this.$message.success({showClose: true, message: '发布成功', duration: 2000})
            this.updateList()
          } else {
            this.$message.error({showClose: true, message: '发布失败：' + res.msg, duration: 2000})
          }
        }).catch(err => {
          console.error(err)
          this.$message.error({showClose: true, message: '发布失败', duration: 2000})
        })
      }).catch(() => {
        this.$message({type: 'info', message: '已取消发布'})
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
    },

    fitIframe () {
      let kitReport = document.getElementById('kit_report')
      let iframes = kitReport.getElementsByTagName('iframe')
      if (iframes.length > 0) {
        for (let i = 0; i < iframes.length; i++) {
          iframes[i].style.width = '100%'
          iframes[i].style.height = '248px'
        }
      } else {
        return false
      }
    }
  }
}
</script>
<style scoped lang="less">
.preview{
  width: 375px;
  min-height: 600px;
  margin: 0 auto;
  border: 1px solid #e2e2e2;
  padding: 20px 12px;
  .title{
    font-size: 20px;
    font-weight: bold;
    line-height: 28px;
    margin-bottom: 8px;
  }
  .views {
    font-size: 12px;
    color: #999;
    line-height: 17px;
    .views-icon {
      border-radius: 50%;
      vertical-align: -3px;
      width: 17px;
      height: 17px;
    }
    span {
      color: #333;
    }
  }
  .date {
    font-size: 12px;
    color: #999;
    margin-left: 12px;
  }
  .intro{
  	margin-top: 16px;
  }
  .content{
    margin-top: 14px;
    font-size: 15px;
    color: #333;
  }
}
</style>
