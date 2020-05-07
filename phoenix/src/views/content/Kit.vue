<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>内容管理</el-breadcrumb-item>
        <el-breadcrumb-item>锦囊管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu">
      <el-row class="nav clearfix">
        <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr" v-if="isTeacherStockA">添加锦囊</el-button>
      </el-row>
      <el-form :inline="true" :model="formInline">
        <el-row>
          <el-form-item label="锦囊名称">
            <el-input v-model="formInline.name" placeholder="请输入"></el-input>
          </el-form-item>
          <el-form-item label="归属牛人">
            <el-select v-model="formInline.belong_user_id" clearable placeholder="全部">
              <el-option v-for="item in teacherList" :value-key="item.id" :key="item.id" :label="item.name" :value="item.id"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="购买类型">
            <el-select v-model="formInline.buy_type" clearable placeholder="全部">
              <el-option v-for="item in kitBuyTypes" :value-key="item.status" :key="item.status" :label="item.name" :value="item.status"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="购买状态">
            <el-select v-model="formInline.buy_state" clearable placeholder="全部">
              <el-option v-for="item in kitBuyStates" :value-key="item.status" :key="item.status" :label="item.name" :value="item.status"></el-option>
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
      <!-- 报告列表 -->
      <el-table
        :data="tablePageData"
        stripe
        style="width: 100%">
        <el-table-column fixed prop="name" label="锦囊名称"></el-table-column>
        <el-table-column prop="belong_user_name" label="归属牛人"></el-table-column>
        <el-table-column prop="buy_type_name" label="购买类型"></el-table-column>
        <el-table-column prop="buy_state_name" label="购买状态"></el-table-column>
        <el-table-column prop="service_key" label="服务Key"></el-table-column>
        <el-table-column prop="sort_num" label="序号"></el-table-column>
        <el-table-column prop="last_modify_user_name" label="最后修改人"></el-table-column>
        <el-table-column prop="updated_at" label="最后修改时间"></el-table-column>
        <el-table-column fixed="right" label="操作" width="130" align="center">
          <template slot-scope="scope">
            <el-dropdown>
              <el-button type="primary">
                锦囊管理<i class="el-icon-arrow-down el-icon--right"></i>
              </el-button>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item @click.native="showEditDialog(scope.row.id)" v-if="isTeacherStockA">编辑锦囊</el-dropdown-item>
                <el-dropdown-item @click.native="delKit(scope.row.id)" v-if="isTeacherStockA">删除锦囊</el-dropdown-item>
                <el-dropdown-item @click.native="gotoKitReport(scope.row.code)">查看报告</el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <!-- 添加锦囊 -->
    <el-dialog title="添加锦囊" :visible.sync="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">
        <el-row>
          <el-form-item label="锦囊名称" prop="name">
            <el-input v-model="addForm.name" placeholder="请输入" :maxlength="64"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="封面图片" prop="cover_url">
            <el-upload ref="addCover" :action="uploadUrl" :file-list="addUploadCoverImg" list-type="picture" :on-success="addUploadSuccess"
                      :on-error="uploadError" :data="uploadObj" :before-upload="uploadBefore" :limit="1"
                      :on-remove="handleRemove" :on-exceed="handleExceed" :with-credentials="true">
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="el-upload__tip">(要求：图片尺寸 1125*360 px、大小不超过 100 K)</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="锦囊介绍" prop="descript">
            <editor ref="addEditor" editorId="addKit" :content="addForm.descript"></editor>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="归属牛人">
            <el-input :disabled="true" :value="authInfo.name"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="购买类型" prop="buy_type">
            <el-select v-model="addForm.buy_type" placeholder="请选择">
              <el-option v-for="item in kitBuyTypes" :value-key="item.status" :key="item.status" :label="item.name" :value="item.status"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="购买状态" prop="buy_state">
            <el-select v-model="addForm.buy_state" placeholder="请选择">
              <el-option v-for="item in kitBuyStates" :value-key="item.status" :key="item.status" :label="item.name" :value="item.status"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="排列序号" prop="sort_num">
            <el-input v-model="addForm.sort_num" placeholder="不填默认为“0”" type="number" :min=0 class="short-input"></el-input>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确定</el-button>
        <el-button @click.native="addVisible = false">取消</el-button>
      </div>
    </el-dialog>

    <!-- 编辑锦囊 -->
    <el-dialog title="编辑锦囊" :visible.sync="editVisible" :close-on-click-model="false" center>
      <el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">
        <el-row>
          <el-form-item label="锦囊名称" prop="name">
            <el-input v-model="editForm.name" placeholder="请输入" :maxlength="64"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="封面图片" prop="cover_url">
            <el-upload ref="editCover" :action="uploadUrl" :file-list="editUploadCoverImg" list-type="picture" :on-success="editUploadSuccess"
                      :on-error="uploadError" :data="uploadObj" :before-upload="uploadBefore" :limit="1"
                      :on-remove="handleRemove" :on-exceed="handleExceed" :with-credentials="true">
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="el-upload__tip">(要求：图片尺寸 1125*360 px、大小不超过 100 K)</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="锦囊介绍" prop="descript">
            <editor ref="editEditor" editorId="editKit" :content="editForm.descript"></editor>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="归属牛人">
            <el-input :disabled="true" :value="authInfo.name"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="购买类型" prop="buy_type">
            <el-select v-model="editForm.buy_type" placeholder="请选择">
              <el-option v-for="item in kitBuyTypes" :value-key="item.status" :key="item.status" :label="item.name" :value="item.status"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="购买状态" prop="buy_state">
            <el-select v-model="editForm.buy_state" placeholder="请选择">
              <el-option v-for="item in kitBuyStates" :value-key="item.status" :key="item.status" :label="item.name" :value="item.status"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
          <el-row>
          <el-form-item label="排列序号" prop="sort_num">
            <el-input v-model="editForm.sort_num" placeholder="不填默认为“0”" type="number" :min=0 class="short-input"></el-input>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="editSubmit" :loading="editLoading">确定</el-button>
        <el-button @click.native="editVisible = false">取消</el-button>
      </div>
    </el-dialog>

    <!-- 查看锦囊 -->
    <el-dialog title="查看锦囊" :visible.sync="previewVisible" :close-on-click-model="false" center>
      <el-form :model="preview" label-width="80px">
        <el-row>
          <el-form-item label="锦囊名称">
            <el-input v-model="preview.name" disabled ></el-input>
          </el-form-item>
          <el-form-item label="封面图片">
            <el-upload ref="editCover" :action="uploadUrl" :file-list="editUploadCoverImg" list-type="picture" :on-success="editUploadSuccess"
                      :on-error="uploadError" :data="uploadObj" :before-upload="uploadBefore" :limit="1"
                      :on-remove="handleRemove" :on-exceed="handleExceed" :with-credentials="true" disabled>
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="el-upload__tip">(要求：图片尺寸 1125*360 px、大小不超过 100 K)</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="锦囊介绍">
            <div class="w-e-text-container" v-html="preview.descript"></div>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="归属牛人">
            <el-input disabled :value="preview.belong_teacher_name"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="购买类型">
            <el-select v-model="preview.buy_type" disabled>
              <el-option v-for="item in kitBuyTypes" :value-key="item.status" :key="item.status" :label="item.name" :value="item.status"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="购买状态">
            <el-select v-model="preview.buy_state" disabled>
              <el-option v-for="item in kitBuyStates" :value-key="item.status" :key="item.status" :label="item.name" :value="item.status"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="排列序号">
            <el-input disabled :value="preview.sort_num" class="short-input"></el-input>
          </el-form-item>
        </el-row>
      </el-form>
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
  name: 'Kit',
  data () {
    return {
      authInfo: [],                   // 当前登录人信息
      isTeacherStockA: false,         // 是否是A股牛人老师
      teacherList: [],                // 牛人老师列表
      kitBuyTypes: [],                // 锦囊 购买类型
      kitBuyStates: [],               // 锦囊 购买状态

      // 搜索区表单
      formInline: {name: '', belong_user_id: '', buy_type: '', buy_state: ''},

      // 缓存搜索数据
      searchParams: {name: '', belong_user_id: '', buy_type: '', buy_state: ''},

      // 表格内容
      tablePageData: [],

      // 上传图片 参数
      addUploadCoverImg: [],
      editUploadCoverImg: [],
      uploadUrl: `${Env.baseURL}/kit/upload/cover`,
      uploadObj: {'image': {}},

      // 分页初始化
      totalAll: 0,        // 列表总数目
      pageSize: 10,       // 分页尺寸
      pageNo: 1,          // 当前页
      pageRefresh: true,  // 分页内容刷新

      // ---- 新增锦囊 ----
      addVisible: false,
      addLoading: false,
      addFormRules: {
        name: [{required: true, message: '请输入锦囊名称', trigger: 'blur'}],
        cover_url: [{required: true, message: '请上传封面图片', trigger: 'blur'}],
        descript: [{required: true, message: '请输入锦囊介绍', trigger: 'blur'}],
        buy_type: [{required: true, message: '请选择购买类型', trigger: 'blur'}],
        buy_state: [{required: true, message: '请选择购买状态', trigger: 'blur'}],
        sort_num: [{validator: this.checkSortNum, trigger: 'blur'}]
      },
      addForm: {name: '', cover_url: '', descript: '', buy_type: '', buy_state: '', sort_num: 0},

      // 编辑报告
      editKitId: '',
      editVisible: false,
      editLoading: false,
      editFormRules: {
        name: [{required: true, message: '请输入锦囊名称', trigger: 'blur'}],
        cover_url: [{required: true, message: '请上传封面图片', trigger: 'blur'}],
        descript: [{required: true, message: '请输入锦囊介绍', trigger: 'blur'}],
        buy_type: [{required: true, message: '请选择购买类型', trigger: 'blur'}],
        buy_state: [{required: true, message: '请选择购买状态', trigger: 'blur'}],
        sort_num: [{validator: this.checkSortNum, trigger: 'blur'}]
      },
      editForm: {name: '', cover_url: '', descript: '', buy_type: '', buy_statue: '', sort_num: 0},

      // 查看锦囊
      previewVisible: false,
      preview: {name: '', cover_url: '', descript: '', buy_type: '', buy_state: '', sort_num: 0, belong_teacher_name: ''}
    }
  },
  components: {
    Editor,
    Pagination
  },
  created: function () {
  },
  mounted: function () {
    this.getList()
    this.getAuthInfo()
    this.getTeacherList()
    this.getBuyTypes()
    this.getBuyStates()
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

    // 获取牛人老师列表
    getTeacherList () {
      API_CONTENT.getTeacherListOfKit().then(res => {
        if (res.code === 0) {
          this.teacherList = res.data.teacher_list
        } else {
          this.teacherList = []
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取购买类型
    getBuyTypes () {
      API_CONTENT.getBuyTypes().then(res => {
        if (res.code === 0) {
          this.kitBuyTypes = res.data.buy_types
        } else {
          this.kitBuyTypes = []
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取购买状态
    getBuyStates () {
      API_CONTENT.getBuyStates().then(res => {
        if (res.code === 0) {
          this.kitBuyStates = res.data.buy_states
        } else {
          this.kitBuyStates = []
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 格式化分页
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

    checkSortNum (rule, value, callback) {
      if (value < 0) {
        callback(new Error('最小值为0！'))
      } else if (!this.isInteger(value)) {
        callback(new Error('请输入正整数'))
      } else {
        callback()
      }
    },

    isInteger (obj) {
      return obj % 1 === 0
    },

    getList () {
      var params = Object.assign({}, this.searchParams)
      params.page_no = this.pageNo
      params.page_size = this.pageSize
      API_CONTENT.getKitList(params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.kit_list
          this.totalAll = res.data.kit_cnt
          this.isTeacherStockA = res.data.is_teacher_stock_a
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '获取锦囊列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    onSearch () {
      this.pageNo = 1
      this.searchParams = this.formInline
      let searchForm = this.filterParams(this.searchParams)
      API_CONTENT.searchKitList(searchForm).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.kit_list
          this.totalAll = res.data.kit_cnt
          this.isTeacherStockA = res.data.is_teacher_stock_a
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

      const isLtFileSize = file.size / 1024 <= 100

      if (!isJPG) {
        this.$message.error('上传图片只能是 JPG/PNG/GIF/SVG 格式!')
      }

      if (!isLtFileSize) {
        this.$message.error('上传图片大小不能超过 100 K！')
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
      console.error('上传失败，请重试！')
    },

    handleRemove (file, fileList) {
      this.addForm.cover_url = ''
      this.editForm.cover_url = ''
    },

    handleExceed (file, fileList) {
      this.$alert('只能上传一个文件')
    },

    // 新增锦囊
    showAddDialog () {
      let _this = this
      _this.addUploadCoverImg = []
      setTimeout(() => {
        _this.addVisible = true
      }, 500)
      _this.addForm = {name: '', cover_url: '', descript: '', buy_type: '', buy_state: '', sort_num: 0}
      setTimeout(() => {
        _this.$refs.addForm.clearValidate()
        _this.$refs.addEditor.clear()
      }, 600)
    },

    addSubmit () {
      let _this = this
      if (_this.$refs.addEditor) {
        this.addForm.descript = _this.$refs.addEditor.setContent()
      }
      this.$refs.addForm.validate((valid) => {
        if (valid) {
          _this.addLoading = true
          API_CONTENT.addKit(_this.addForm).then(res => {
            if (res.code === 0) {
              _this.$message.success({showClose: true, message: '新增成功', duration: 2000})
              _this.updateList()
              _this.addVisible = false
              if (_this.$refs.addCover) {
                _this.$refs.addCover.clearFiles()
              }
            } else {
              _this.$message.error({showClose: true, message: '新增失败：' + res.msg, duration: 2000})
            }
            _this.addLoading = false
          }).catch(err => {
            console.error(err)
            this.addLoading = false
          })
        }
      })
    },

    // 编辑锦囊
    showEditDialog (id) {
      let _this = this
      _this.editUploadCoverImg = []
      _this.editKitId = id
      setTimeout(() => {
        _this.editVisible = true
      }, 500)
      API_CONTENT.getKit(id).then(res => {
        if (res.code === 0) {
          _this.editForm = {
            name: res.data.kit.name,
            cover_url: res.data.kit.cover_url,
            descript: res.data.kit.descript,
            buy_type: res.data.kit.buy_type,
            buy_state: res.data.kit.buy_state,
            sort_num: res.data.kit.sort_num
          }
          _this.editUploadCoverImg = [{
            name: res.data.kit.cover_url.substr(res.data.kit.cover_url.lastIndexOf('/') + 1),
            url: res.data.kit.cover_url
          }]
        } else {
          this.$message.error({showClose: true, message: '查询失败：' + res.msg, duration: 2000})
        }
        setTimeout(() => {
          _this.$refs.editForm.clearValidate()
          if (_this.$refs.editEditor) {
            _this.$refs.editEditor.getContent(_this.editForm.descript)
          }
        }, 600)
      }).catch(err => {
        console.error(err)
      })
    },

    editSubmit () {
      let _this = this
      if (_this.$refs.editEditor) {
        _this.editForm.descript = _this.$refs.editEditor.setContent()
      }
      this.$refs.editForm.validate((valid) => {
        if (valid) {
          _this.editLoading = true
          API_CONTENT.editKit(_this.editKitId, _this.editForm).then(res => {
            if (res.code === 0) {
              _this.$message.success({showClose: true, message: '编辑成功', duration: 2000})
              _this.updateList()
              _this.editVisible = false
              if (_this.$refs.editCover) {
                _this.$refs.editCover.clearFiles()
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

    delKit (id) {
      this.$confirm('该操作会同步删除该锦囊下所有报告, 是否确认删除该锦囊?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        API_CONTENT.delKit(id).then(res => {
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
      this.previewVisible = true
      API_CONTENT.getKit(id).then(res => {
        if (res.code === 0) {
          _this.preview = {
            id: id,
            name: res.data.kit.name,
            cover_url: res.data.kit.cover_url,
            descript: res.data.kit.descript,
            buy_type: res.data.kit.buy_type,
            buy_state: res.data.kit.buy_state,
            sort_num: res.data.kit.sort_num,
            belong_teacher_name: res.data.kit.belong_teacher_name
          }
          _this.editUploadCoverImg = [{
            name: res.data.kit.cover_url.substr(res.data.kit.cover_url.lastIndexOf('/') + 1),
            url: res.data.kit.cover_url
          }]
        } else {
          this.$message.error({showClose: true, message: '查询失败：' + res.msg, duration: 2000})
        }
        setTimeout(() => {
          if (_this.$refs.previewEditor) {
            _this.$refs.previewEditor.getContent(_this.preview.descript)
          }
        }, 600)
      }).catch(err => {
        console.log(err)
      })
    },

    gotoKitReport (code) {
      this.$router.push({
        name: '锦囊报告管理',
        params: {
          'kit_code': code
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
<style scoped>
.short-input {
  width: 275px;
}
</style>


