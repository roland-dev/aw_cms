<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>接口管理</el-breadcrumb-item>
        <el-breadcrumb-item>密钥管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu"> 
      <el-row class="nav clearfix">
        <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr">添加密钥</el-button>
      </el-row>    
      <el-form :inline="true" :model="formInline">
        <el-row>
          <el-form-item label="名称" prop="name"> 
            <el-input v-model="formInline.name" placeholder="请输入"></el-input>  
          </el-form-item>
          <el-form-item label="code值" prop="code"> 
            <el-input v-model="formInline.code" placeholder="请输入"></el-input>  
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
        <el-table-column prop="name" label="名称"></el-table-column>
        <el-table-column prop="code" label="code值"></el-table-column>
        <el-table-column prop="secret" label="密钥值"></el-table-column>
        <el-table-column prop="remark" label="简介" show-overflow-tooltip></el-table-column>
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
        <el-table-column fixed="right" align="center" label="操作" width="200">
          <template slot-scope="scope">
            <el-button  @click.native="showEditDialog(scope.row.code)" type="text" size="small">编辑</el-button>
            <el-button  @click.native="showDialog(scope.row.code)" type="text" size="small">查看</el-button>
            <el-button  @click.native="changeSecret(scope.row.code)" type="text" size="small">改变secret</el-button>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <!-- 添加用户 -->
    <el-dialog title="新增密钥" :visible.sync ="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">
        <el-row>
          <el-form-item label="名称" prop="name">
            <el-input v-model="addForm.name" placeholder="请输入" :maxlength="20"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="简介" prop="remark">
            <el-input v-model="addForm.remark" placeholder="请输入" :maxlength="50"></el-input>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确定</el-button>
        <el-button @click.native="addVisible = false">取消</el-button>        
      </div>
    </el-dialog>

    <!-- 编辑密钥 -->
    <el-dialog title="编辑密钥" :visible.sync ="editVisible" :close-on-click-modal="false" center>
      <el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">
        <el-row>
          <el-form-item label="名称" prop="name">
            <el-input v-model="editForm.name" placeholder="请输入" :maxlength="6"></el-input>
          </el-form-item>
        </el-row>         
        <el-row>
          <el-form-item label="简介" prop="remark">
            <el-input v-model="editForm.remark" placeholder="请输入"></el-input>
          </el-form-item>
        </el-row>  
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="editSubmit" :loading="editLoading">确定</el-button>
        <el-button @click.native="editVisible = false">取消</el-button>        
      </div>
    </el-dialog>

    <!-- 查看密钥 -->
    <el-dialog title="查看密钥" :visible.sync ="showVisible" :close-on-click-modal="false" center>
      <el-form :model="showForm" label-width="80px" :rules="showFormRules" ref="showForm">
        <el-row>
          <el-form-item label="名称" prop="name">
            {{showForm.name}}
          </el-form-item>
        </el-row>         
        <el-row>
          <el-form-item label="code" prop="code">
            {{showForm.code}}
          </el-form-item>
        </el-row>         
        <el-row>
          <el-form-item label="密钥" prop="secret">
            {{showForm.secret}}
          </el-form-item>
        </el-row>         
        <el-row>
          <el-form-item label="简介" prop="remark">
            {{showForm.remark}}
          </el-form-item>
        </el-row>  
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="changeSecret(showForm.code)" :loading="showLoading">修改secret</el-button>        
        <el-button @click.native="showVisible = false">取消</el-button>        
      </div>
    </el-dialog>

  </div> 
</template>

<script>
import API_OPENAPI from '../../http/api_openapi'
import Pagination from '@/components/Pagination'

export default {
  name: 'User',
  data () {
    return {
      show: 1,
      // 搜索区表单
      formInline: {name: '', code: ''},

      // 缓存搜索数据
      searchParams: {name: '', code: ''},

      totalAll: 0,          // 列表总数目
      pageSize: 10,         // 分页显示数目
      pageNo: 1,            // 当前页码
      pageRefresh: true,    // 分页内容刷新

      tablePageData: [],    // 分页显示数据

      // 新增用户
      addVisible: false, // 是否显示
      addLoading: false,
      addFormRules: {
        name: [{required: true, message: '请输入名称', trigger: 'blur'}],
        remark: [{required: true, message: '请输入简介信息', trigger: 'blur'}]
      },
      addForm: {name: '', remark: ''},
      signType: [],

      // 编辑用户
      editVisible: false, // 是否显示
      editLoading: false,
      editFormRules: {
        name: [{required: true, message: '请输入名称', trigger: 'blur'}],
        remark: [{required: true, message: '请输入简介信息', trigger: 'blur'}]
      },
      editForm: {name: '', remark: ''},

      // 编辑用户
      showVisible: false, // 是否显示
      showLoading: false,
      showFormRules: {
      },
      showForm: {name: '', remark: '', code: '', secret: ''}
    }
  },
  components: {
    Pagination
  },
  created: function () {
  },
  mounted: function () {
    this.getList()
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
      let params = Object.assign({}, this.searchParams)
      params.page_no = this.pageNo
      params.page_size = this.pageSize
      API_OPENAPI.getCodeList(params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.custom_app_list
          this.totalAll = res.data.custom_app_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '获取秘钥列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    checkEnterpriseUseridRequiredOfAdd (rule, value, callback) {
      if (this.addForm.type === 'teacher' && this.addForm.enterprise_userid.length === 0) {
        callback(new Error('标记标签为teacher，企业微信不能为空'))
      } else {
        callback()
      }
    },

    checkEnterpriseUseridRequiredOfEdit (rule, value, callback) {
      if (this.editForm.type === 'teacher' && this.editForm.enterprise_userid.length === 0) {
        callback(new Error('标记标签为teacher，企业微信不能为空'))
      } else {
        callback()
      }
    },

    // getSignType () {
    //   let typeArr = []
    //   this.tableData.forEach(d => {
    //     if (typeArr.indexOf(d.type) < 0) {
    //       typeArr.push(d.type)
    //     }
    //   })
    //   this.signType = typeArr
    // },

    // 更新表格
    updateList () {
      this.getList()
    },

    onSearch () {
      this.pageNo = 1
      this.searchParams = this.formInline
      let searchParams = this.filterParams(this.searchParams)
      API_OPENAPI.searchCodeList(searchParams).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.custom_app_list
          this.totalAll = res.data.custom_app_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '查询失败' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    KeySearch (ev) {
      this.onSearch()
    },

    // 添加密钥
    showAddDialog () {
      this.addVisible = true
      this.addForm = {
        name: '',
        remark: ''
      }
      setTimeout(() => {
        this.$refs.addForm.clearValidate()
      }, 100)
    },

    addSubmit () {
      let _this = this
      this.$refs.addForm.validate((valid) => {
        if (valid) {
          API_OPENAPI.createCode(_this.addForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: '新增成功', duration: 2000})
              _this.updateList()
              _this.addVisible = false
            } else if (data.code === 210008) {
              _this.$message({
                message: '缺少传入的参数',
                type: 'warning'
              })
              return false
            } else if (data.code === 410003) {
              _this.$message({
                message: 'openApi 已经存在',
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
    showEditDialog (code) {
      this.editVisible = true
      // 请求一个当前的海报
      API_OPENAPI.getDetail(code).then(res => {
        this.editForm = {
          // id: id,
          name: res.data.name,
          remark: res.data.remark,
          code: res.data.code
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
          API_OPENAPI.updateBasicInfo(_this.editForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: '编辑成功', duration: 2000})
              this.updateList()
              _this.editVisible = false
            } else if (data.code === 210008) {
              _this.$message({
                message: '缺少传入的参数',
                type: 'warning'
              })
              return false
            } else if (data.code === 410003) {
              _this.$message({
                message: 'openApi 已经存在',
                type: 'warning'
              })
              return false
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

    // 查看dialog
    showDialog (code) {
      this.showVisible = true
      // 请求一个当前的海报
      API_OPENAPI.getDetail(code).then(res => {
        this.showForm = {
          // id: id,
          name: res.data.name,
          remark: res.data.remark,
          code: res.data.code,
          secret: res.data.secret
        }
      })
    },

    // 是否解锁
    changeActive (row) {
      // 活跃状态取反并发送请求
      let activeStatus = row.active === 1 ? 1 : 0
      console.log(activeStatus)
      let _code = {code: row.code}
      if (activeStatus === 1) {
        API_OPENAPI.codeUnlock(_code).then(data => {
          if (data.code === 0) { console.log('code解锁🔓') }
        }).catch(err => {
          console.error(err)
        })
      } else {
        API_OPENAPI.codeLock(_code).then(data => {
          if (data.code === 0) { console.log('code已锁🔒') }
        }).catch(err => {
          console.error(err)
        })
      }
    },

    // 改变密钥值
    changeSecret (customCode) {
      let _code = {code: customCode}
      this.$confirm('是否确认改变密钥值？', '提示', {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        API_OPENAPI.updateSecret(_code).then(data => {
          if (data.code === 0) {
            this.$message.success({showClose: true, message: '更新成功', duration: 2000})
            this.showVisible = false
            // this.getList()
            this.updateList()
          } else {
            this.$message.error(data.msg)
          }
        }).catch(err => {
          console.error(err)
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消更新'
        })
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
