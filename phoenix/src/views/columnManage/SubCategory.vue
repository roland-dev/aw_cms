<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>栏目管理</el-breadcrumb-item>
        <el-breadcrumb-item :to="{ path: '/column/category'}">栏目分类管理</el-breadcrumb-item>
        <el-breadcrumb-item>子栏目分类管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu">
      <el-row class="nav clearfix">
        <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr">添加子栏目分类</el-button>
      </el-row>
    </el-row>

    <!-- 列表 -->
    <el-row class="table-menu">
      <el-table
        :data="tablePageData"
        stripe
        style="width: 100%">
        <el-table-column fixed prop="name" label="子栏目分类名称"></el-table-column>
        <el-table-column prop="code" label="子栏目分类Code"></el-table-column>
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
        <el-table-column fixed="right" align="center" label="操作" width="160">
          <template slot-scope="scope">
            <el-button @click.native="showEditDialog(scope.row.id)" type="text" size="small">编辑</el-button>
            <el-button @click.native="delSubCategory(scope.row.id)" type="text" size="small">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <!-- 新增子栏目分类 -->
    <el-dialog title="添加子栏目分类" :visible.sync="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="120px" :rules="addFormRules" ref="addForm">
        <el-row>
          <el-form-item label="子栏目名称" prop="name">
            <el-input v-model="addForm.name" placeholder="请输入" :maxlength="20"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="子栏目Code值" prop="code">
            <el-input v-model="addForm.code" placeholder="请输入" :maxlength="64"></el-input>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确定</el-button>
        <el-button @click.native="addVisible = false">取消</el-button>
      </div>
    </el-dialog>

    <!-- 编辑子栏目分类 -->
    <el-dialog title="编辑子栏目分类" :visible.sync="editVisible" :close-on-click-modal="false" center>
      <el-form :model="editForm" label-width="120px" :rules="editFormRules" ref="editForm">
        <el-row>
          <el-form-item label="子栏目名称" prop="name">
            <el-input v-model="editForm.name" placeholder="请输入" :maxlength="20"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="子栏目Code值" prop="code">
            <el-input v-model="editForm.code" placeholder="请输入" :maxlength="64" :disabled="true"></el-input>
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
import Pagination from '@/components/Pagination'

export default {
  name: 'SubCategory',
  data () {
    return {
      totalAll: 0,        // 列表总数目
      pageSize: 10,       // 分页显示数目
      pageNo: 1,          // 当前页码
      pageRefresh: true,  // 分页内容刷新

      tablePageData: [],  // 分页显示数据

      // 新增子栏目分类
      addVisible: false,
      addLoading: false,
      addFormRules: {
        name: [{required: true, message: '请输入子栏目分类名称', trigger: 'blur'}],
        code: [{required: true, message: '请输入子栏目分类Code值', trigger: 'blur'}, {validator: this.checkCodeUnique, trigger: 'blur'}]
      },
      addForm: {
        name: '',
        code: '',
        category_code: ''
      },

      // 编辑栏目分类
      editVisible: false,
      editLoading: false,
      editFormRules: {
        name: [{required: true, message: '请输入子栏目分类名称', trigger: 'blur'}],
        code: [{required: true, message: '请输入子栏目分类Code值', trigger: 'blur'}, {validator: this.editCheckCodeUnique, trigger: 'blur'}]
      },
      editForm: {
        id: '',
        name: '',
        code: ''
      }

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

    // 获取列表
    getList () {
      let params = {
        page_no: this.pageNo,
        page_size: this.pageSize
      }
      HTTP.getSubCategoryList(this.$route.params.categoryCode, params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.sub_category_list
          this.totalAll = res.data.sub_category_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '获取子栏目分类列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // check subCategoryCode unique
    checkCodeUnique (rule, value, callback) {
      HTTP.checkSubCategoryCodeUnique(this.$route.params.categoryCode, value).then(res => {
        if (res.code === 0) {
          if (res.data.check_res.length > 0) {
            callback(new Error('子栏目分类Code重复，请重复输入'))
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

    editCheckCodeUnique (rule, value, callback) {
      HTTP.checkSubCategoryCodeUnique(this.$route.params.categoryCode, value).then(res => {
        if (res.code === 0) {
          if (res.data.check_res.length > 1) {
            callback(new Error('子栏目分类Code重复，请重新输入'))
          } else if (res.data.check_res.length === 1 && res.data.check_res[0].id !== parseInt(this.editForm.id)) {
            callback(new Error('子栏目分类Code重复，请重新输入'))
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

    // 改变子栏目活跃状态
    changeActive (row) {
      // 活跃状态取反并发送请求
      let activeStatus = row.active === 1 ? 1 : 0
      HTTP.changeActiveOfSubCategory(row.id, activeStatus).then(data => {
        if (data.code === 0) { console.log('活跃状态改变') }
      }).catch(err => {
        console.error(err)
      })
    },

    // 添加
    showAddDialog () {
      this.addVisible = true
      this.addForm = {
        name: '',
        code: '',
        category_code: this.$route.params.categoryCode
      }
      setTimeout(() => {
        this.$refs.addForm.clearValidate()
      }, 100)
    },
    addSubmit () {
      let _this = this
      this.$refs.addForm.validate((valid) => {
        if (valid) {
          _this.addLoading = true
          HTTP.addSubCategory(_this.addForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: '新增成功', duration: 2000})
              _this.getList()
            } else if (data.code === 352001) {
              _this.$message.error({showClose: true, message: '子栏目分类已存在', duration: 2000})
            } else {
              _this.$message.error({showClose: true, message: '新增失败', duration: 2000})
            }
            _this.addVisible = false
            _this.addLoading = false
          }).catch(err => {
            console.error(err)
          })
          _this.addVisible = false
          _this.addLoading = false
        }
      })
    },

    // 编辑
    showEditDialog (subCategoryId) {
      this.editVisible = true
      HTTP.getSubCategoryInfo(subCategoryId).then(data => {
        if (data.code === 0) {
          let subCategoryInfo = data.data.sub_category_info
          this.editForm = {
            id: subCategoryInfo.id,
            name: subCategoryInfo.name,
            code: subCategoryInfo.code
          }
          setTimeout(() => {
            this.$refs.editForm.clearValidate()
          }, 100)
        } else if (data.code === 352003) {
          console.error('该对象不存在')
        }
      })
    },
    editSubmit () {
      let _this = this
      this.$refs.editForm.validate((valid) => {
        if (valid) {
          _this.editLoading = true
          HTTP.updateSubCategory(_this.editForm.id, _this.editForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: '编辑成功', duration: 2000})
              _this.getList()
            } else if (data.code === 352001) {
              _this.$message.error({showClose: true, message: '子栏目分类已存在', duration: 2000})
            } else {
              _this.$message.error({showClose: true, message: '编辑失败', duration: 2000})
            }
            _this.editVisible = false
            _this.editLoading = false
          }).catch(err => {
            console.error(err)
          })
          _this.editVisible = false
          _this.editLoading = false
        }
      })
    },

    delSubCategory (subCategoryId) {
      let _this = this
      this.$confirm('是否确认删除该子栏目分类记录？', '提示', {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        HTTP.deleteSubCategory(subCategoryId).then(data => {
          if (data.code === 0) {
            _this.$message.success({showClose: true, message: '删除成功', duration: 2000})
            _this.getList()
          } else if (data.code === 352003) {
            _this.$message.error({showClose: true, message: '删除对象不存在', duration: 2000})
          } else {
            _this.$message.error({showClose: true, message: '该栏目下有内容，不能被删除！', duration: 2000})
          }
        }).catch(err => {
          console.error(err)
        })
      }).catch(() => {
        _this.$message({
          type: 'info',
          message: '已取消删除'
        })
      })
    }
  }
}
</script>
