<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>系统管理</el-breadcrumb-item>
        <el-breadcrumb-item>用户管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu"> 
      <el-row class="nav clearfix">
        <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr">添加用户</el-button>
      </el-row>    
      <el-form :inline="true" :model="formInline">
        <el-row>
          <el-form-item label="用户姓名" prop="name"> 
            <el-input v-model="formInline.name" placeholder="请输入"></el-input>  
          </el-form-item>
          <el-form-item label="标记类型">
            <el-select v-model="formInline.type" clearable placeholder="请选择">
              <el-option v-for="item in signType" :value-key="item" :key="item" :label="item" :value="item"></el-option>
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
      <!-- 用户列表 -->
      <el-table
        :data="tablePageData"
        stripe
        style="width: 100%">
        <el-table-column fixed prop="icon_url" label="头像" width="50" align="center">
          <template slot-scope="scope">
            <img :src="scope.row.icon_url" alt="" style="width: 30px; height: 30px;">
          </template>
        </el-table-column>
        <el-table-column fixed prop="name" label="用户姓名"></el-table-column>
        <el-table-column prop="email" label="企业邮箱"></el-table-column>
        <el-table-column prop="type" label="标记类型"></el-table-column>
        <el-table-column prop="cert_no" label="证书编号"></el-table-column>
        <el-table-column prop="description" label="简介" show-overflow-tooltip></el-table-column>
        <el-table-column prop="created_at" label="创建时间"></el-table-column>

        <el-table-column label="活跃状态" width="100">
  <!--         <template slot-scope="scope">
            <span>{{}}</span>
          </template>  -->
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
        <el-table-column label="开通时可选" width="100">
          <template slot-scope="scope">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              active-color="#13ce66"
              inactive-color="#999"
              :disabled="scope.row.is_can_selected === 0"
              v-if="scope.row.is_can_selected !== 0"
              v-model="scope.row.selected"
              @change="changeSelected(scope.row)">
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column fixed="right" align="center" label="操作" width="100">
          <template slot-scope="scope">
            <el-button  @click.native="showEditDialog(scope.row.id)" type="text" size="small">编辑</el-button>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage">></pagination>
    </el-row>

    <!-- 添加用户 -->
    <el-dialog title="新增用户" :visible.sync ="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">
        <el-row>
          <el-form-item label="用户姓名" prop="name">
            <el-input v-model="addForm.name" placeholder="请输入" :maxlength="6"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="企业邮箱" prop="email">
            <el-input v-model="addForm.email" placeholder="请输入"></el-input>
          </el-form-item>
        </el-row>  
        <el-row>
          <el-form-item label="企业微信" prop="enterprise_userid">
            <el-input v-model="addForm.enterprise_userid" placeholder="请输入" :maxlength='64'></el-input>
          </el-form-item>
        </el-row>  
        <el-row>
          <el-form-item label="标记类型" prop="type">
            <el-select v-model="addForm.type" placeholder="请选择">
              <el-option v-for="item in signType" :value-key="item" :key="item" :label="item" :value="item"></el-option>
            </el-select>
          </el-form-item>  
        </el-row> 
        <!-- <el-row v-if="addForm.type === 'teacher'">
          <el-form-item label="头像地址" prop="icon_url">
            <el-input v-model="addForm.icon_url" placeholder="请输入"></el-input>
          </el-form-item>
        </el-row>    -->
        <el-row v-if="addForm.type === 'teacher'">
          <el-form-item label="头像" prop="icon_url">
            <el-upload :action="iconUrl" :file-list="iconImgFile" list-type="picture"
                        :on-success="uploadSuccessOfAdd" :on-error="uploadError" :data="iconObj"
                        :before-upload="uploadBefore" :limit="1" :on-remove="handleRemove"
                        :on-exceed="handleExceed" :with-credentials="true">
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="el-upload__tip">(要求：图片尺寸 120*120 px、大小不超过 30 K)</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row v-if="addForm.type === 'teacher'">
          <el-form-item label="证书编号" prop="cert_no">
            <el-input v-model="addForm.cert_no" placeholder="请输入"></el-input>
          </el-form-item>
        </el-row>
        <el-row v-if="addForm.type === 'teacher'">
          <el-form-item label="简介" prop="description">
            <el-input type="textarea" v-model="addForm.description" placeholder="请输入" :maxlength="120"></el-input>
          </el-form-item>
        </el-row>
        <el-row v-if="addForm.type === 'teacher'">
          <el-form-item label="标签" prop="teacher_tabs">
            <template>
              <el-checkbox-group v-model="addForm.teacher_tabs">
                <el-checkbox v-for="item in  teacherTabs" :label="item.code" :key="item.code" border size="mini">
                  {{item.name}}
                </el-checkbox>
              </el-checkbox-group>
            </template>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确定</el-button>
        <el-button @click.native="addVisible = false">取消</el-button>        
      </div>
    </el-dialog>

    <!-- 编辑用户 -->
    <el-dialog title="编辑用户" :visible.sync ="editVisible" :close-on-click-modal="false" center>
      <el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">
        <el-row>
          <el-form-item label="用户姓名" prop="name">
            <el-input v-model="editForm.name" placeholder="请输入" :maxlength="6"></el-input>
          </el-form-item>
        </el-row>         
        <el-row>
          <el-form-item label="企业邮箱" prop="email">
            <el-input v-model="editForm.email" placeholder="请输入"></el-input>
          </el-form-item>
        </el-row>  
        <el-row>
          <el-form-item label="企业微信" prop="enterprise_userid">
            <el-input v-model="editForm.enterprise_userid" placeholder="请输入" :maxlength='64'></el-input>
          </el-form-item>
        </el-row>  
        <el-row>
          <el-form-item label="标记类型" prop="type">
            <el-select v-model="editForm.type" placeholder="请选择">
              <el-option v-for="item in signType" :key="item" :label="item" :value="item"></el-option>
            </el-select>
          </el-form-item>  
        </el-row> 
        <!-- <el-row  v-if="editForm.type === 'teacher'">
          <el-form-item label="头像地址" prop="icon_url">
            <el-input v-model="editForm.icon_url" placeholder="请输入"></el-input>
          </el-form-item>
        </el-row>  -->
        <el-row v-if="editForm.type === 'teacher'">
          <el-form-item label="头像" prop="icon_url">
            <el-upload :action="iconUrl" :file-list="iconImgFile" list-type="picture"
                        :on-success="uploadSuccessOfEdit" :on-error="uploadError" :data="iconObj"
                        :before-upload="uploadBefore" :limit="1" :on-remove="handleRemove"
                        :on-exceed="handleExceed" :with-credentials="true">
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="el-upload__tip">(要求：图片尺寸 120*120 px、大小不超过 30 K)</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row v-if="editForm.type === 'teacher'">
          <el-form-item label="证书编号" prop="cert_no">
            <el-input v-model="editForm.cert_no" placeholder="请输入"></el-input>
          </el-form-item>
        </el-row>
        <el-row v-if="editForm.type === 'teacher'">
          <el-form-item label="简介" prop="description">
            <el-input type="textarea" v-model="editForm.description" placeholder="请输入" :maxlength="120"></el-input>
          </el-form-item>
        </el-row>
        <el-row v-if="editForm.type === 'teacher'">
          <el-form-item label="标签" prop="teacher_tabs">
            <template>
              <el-checkbox-group v-model="editForm.teacher_tabs">
                <el-checkbox v-for="item in  teacherTabs" :label="item.code" :key="item.code" border size="mini">
                  {{item.name}}
                </el-checkbox>
              </el-checkbox-group>
            </template>
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
import API_SYSTEM from '../../http/api_system'
import Env from '../../http/env'
import Pagination from '@/components/Pagination'

export default {
  name: 'User',
  data () {
    return {
      show: 1,
      // 搜索区表单
      formInline: {name: '', type: ''},
      // 缓存搜索数据
      searchParams: {name: '', type: ''},

      totalAll: 0,          // 列表总数目
      pageSize: 10,         // 分页显示数目
      pageNo: 1,            // 当前页码
      pageRefresh: true,    // 分页内容刷新

      tablePageData: [],    // 分页显示数据

      teacherTabs: [],      // teacherTabs

      // 上传头像
      iconUrl: `${Env.baseURL}/user/icon/upload`,
      iconImgFile: [],
      iconObj: {'image': {}},

      // 新增用户
      addVisible: false, // 是否显示
      addLoading: false,
      addFormRules: {
        name: [{required: true, message: '请输入用户姓名', trigger: 'blur'}],
        email: [{required: true, message: '请输入企业邮箱', trigger: 'blur'}, {validator: this.checkEmail, trigger: 'blur'}],
        enterprise_userid: [{validator: this.checkEnterpriseUseridRequiredOfAdd, trigger: 'blur'}],
        type: [{required: true, message: '请选择权限类型', trigger: 'change'}]
      },
      addForm: {name: '', email: '', enterprise_userid: '', type: '', icon_url: '', password: '', cert_no: '', description: '', teacher_tabs: []},
      signType: [],

      // 编辑用户
      editVisible: false, // 是否显示
      editLoading: false,
      editFormRules: {
        name: [{required: true, message: '请输入用户姓名', trigger: 'blur'}],
        email: [{required: true, message: '请输入企业邮箱', trigger: 'blur'}, {validator: this.checkEmail, trigger: 'blur'}],
        enterprise_userid: [{validator: this.checkEnterpriseUseridRequiredOfEdit, trigger: 'blur'}],
        type: [{required: true, message: '请选择权限类型', trigger: 'change'}]
      },
      editForm: {name: '', email: '', enterprise_userid: '', type: '', icon_url: '', password: '', cert_no: '', description: '', teacher_tabs: []}
    }
  },
  components: {
    Pagination
  },
  created: function () {
  },
  mounted: function () {
    this.getList()
    this.getTeacherTabs()
    this.getSignTypes()
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
      var params = Object.assign({}, this.searchParams)
      params.page_no = this.pageNo
      params.page_size = this.pageSize
      API_SYSTEM.getUserList(params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.user_list
          this.totalAll = res.data.user_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '获取用户列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 更新表格
    updateList () {
      this.getList()
    },

    // 获取teacherTabs
    getTeacherTabs () {
      API_SYSTEM.getTeacherTabs().then(data => {
        if (data.code === 0) {
          this.teacherTabs = data.data.teacher_tab_list
        } else {
          console.error(data.msg)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    getSignTypes () {
      API_SYSTEM.getSignTypes().then(res => {
        if (res.code === 0) {
          this.signType = res.data.sign_type_list
        } else {
          console.error(res.msg)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    checkEnterpriseUseridRequiredOfAdd (rule, value, callback) {
      if (this.addForm.type === 'teacher' && (this.addForm.enterprise_userid === undefined || this.addForm.enterprise_userid.length === 0)) {
        callback(new Error('标记标签为teacher，企业微信不能为空'))
      } else {
        callback()
      }
    },

    checkEnterpriseUseridRequiredOfEdit (rule, value, callback) {
      if (this.editForm.type === 'teacher' && (this.editForm.enterprise_userid === undefined || this.editForm.enterprise_userid.length === 0)) {
        callback(new Error('标记标签为teacher，企业微信不能为空'))
      } else {
        callback()
      }
    },

    onSearch () {
      this.pageNo = 1
      this.searchParams = this.formInline
      let searchParams = this.filterParams(this.searchParams)
      API_SYSTEM.searchUser(searchParams).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.user_list
          this.totalAll = res.data.user_cnt
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

    // 改变用户活跃状态
    changeActive (row) {
      // 活跃状态取反并发送请求
      let activeStatus = row.active === 1 ? 1 : 0
      API_SYSTEM.changeActive(row.id, activeStatus).then(data => {
        if (data.code === 0) { console.log('活跃状态改变') }
      }).catch(err => {
        console.error(err)
      })
    },

    // 改变用户选中状态
    changeSelected (row) {
      let selectedStatus = row.selected === 1 ? 1 : 0
      API_SYSTEM.changeSelected(row.id, selectedStatus).then(data => {
        if (data.code === 0) {
          console.log('选中状态改变')
        } else {
          console.log('选中状态更新错误:', data)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // -------------------------- 上传图片 -------------------------------

    uploadBefore (file) {
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

      const isLtFilSize = file.size / 1024 <= 30

      if (!isJPG) {
        this.$message.error('上传图片只能是 JPG/PNG/GIF/SVG 格式!')
      }
      if (!isLtFilSize) {
        this.$message.error('上传图片大小不能超过 30 K！')
      }

      if (isJPG && isLtFilSize) {
        this.iconObj.image = file
      }

      return isJPG && isLtFilSize
    },

    uploadSuccessOfAdd (response, file, iconImgFile) {
      if (response.code === 0) {
        this.addForm.icon_url = response.data.path
        this.iconImgFile = [{
          name: response.data.path.substr(response.data.path.lastIndexOf('/') + 1),
          url: response.data.path
        }]
      } else {
        console.error(response.msg)
      }
    },

    uploadSuccessOfEdit (response, file, iconImgFile) {
      if (response.code === 0) {
        this.editForm.icon_url = response.data.path
        this.iconImgFile = [{
          name: response.data.path.substr(response.data.path.lastIndexOf('/') + 1),
          url: response.data.path
        }]
      } else {
        console.error(response.msg)
      }
    },

    uploadError (response, file, iconImgFile) {
      console.error('上传失败，请重试！')
    },

    handleRemove (file, fileList) {
      this.addForm.icon_url = ''
      this.editForm.icon_url = ''
    },

    handleExceed (file, fileList) {
      this.$alert('只能上传一张图片')
    },

    // 登记视频
    showAddDialog () {
      this.addVisible = true
      this.iconImgFile = []
      this.addForm = {
        name: '',
        email: '',
        enterprise_userid: '',
        icon_url: '',
        type: '',
        password: '',
        cert_no: '',
        description: '',
        teacher_tabs: []
      }
      setTimeout(() => {
        this.$refs.addForm.clearValidate()
      }, 100)
    },

    addSubmit () {
      let _this = this
      this.$refs.addForm.validate((valid) => {
        if (valid) {
          API_SYSTEM.addUser(_this.addForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: '新增成功', duration: 2000})
              _this.updateList()
              _this.addVisible = false
            } else if (data.code === 210003) {
              _this.$message({
                message: '企业邮箱已存在',
                type: 'warning'
              })
              return false
            } else if (data.code === 220001) {
              _this.$message({
                message: '企业微信已存在',
                type: 'warning'
              })
              return false
            } else {
              _this.$message.error({showClose: true, message: '新增失败', duration: 2000})
              _this.addVisible = false
            }
          }).catch(err => {
            console.error(err)
          })
        }
      })
    },

    // 编辑用户 dialog
    showEditDialog (id) {
      this.editVisible = true
      this.iconImgFile = []
      // 请求一个当前的海报
      API_SYSTEM.findUser(id).then(res => {
        this.editForm = {
          id: id,
          name: res.data.user_info.name,
          email: res.data.user_info.email,
          enterprise_userid: res.data.uc_info.enterprise_userid,
          type: res.data.user_info.type,
          icon_url: res.data.user_info.icon_url,
          cert_no: res.data.user_info.cert_no,
          description: res.data.user_info.description,
          teacher_tabs: res.data.teacher_tabs
        }

        let iconFileUrl = res.data.user_info.icon_url
        if (iconFileUrl.length > 0) {
          let iconFileName = iconFileUrl.substr(iconFileUrl.lastIndexOf('/') + 1)
          this.iconImgFile = [{
            name: iconFileName,
            url: iconFileUrl
          }]
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
          API_SYSTEM.updateUser(_this.editForm.id, _this.editForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: '编辑成功', duration: 2000})
              this.updateList()
              _this.editVisible = false
            } else if (data.code === 210004) {
              _this.$message({
                message: '企业邮箱已存在',
                type: 'warning'
              })
              return false
            } else if (data.code === 220001) {
              _this.$message({
                message: '企业微信已存在',
                type: 'warning'
              })
              return false
            } else if (data.code === 210006) {
              _this.$message({
                message: '该用户为A股老师，请先进行解绑用户组再进行标记类型修改',
                type: 'warning'
              })
            } else {
              _this.$message.error({showClose: true, message: '编辑失败', duration: 2000})
              _this.editVisible = false
            }
          }).catch(err => {
            console.error(err)
          })
        }
      })
    },

        // 检查邮箱是否重复
    checkEmail (rule, value, callback) {
      let reg = /^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/
      if (!reg.test(value)) {
        callback(new Error('请输入格式正确的邮箱地址'))
      } else {
        callback()
      }
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
