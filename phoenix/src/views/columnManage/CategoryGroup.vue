<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>栏目管理</el-breadcrumb-item>
        <el-breadcrumb-item>栏目分组管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu">
      <el-row class="nav clearfix">
        <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr">添加栏目分组</el-button>
      </el-row>
    </el-row>

    <!-- 列表 -->
    <el-row class="table-menu">
      <!-- 栏目分组表格 -->
      <el-table :data="tablePageData" stripe style="width: 100%">
        <el-table-column prop="code" label="栏目分组Code" aligin="center"></el-table-column>
        <el-table-column prop="name" label="栏目分组名称" aligin="center"></el-table-column>
        <el-table-column prop="descript" label="栏目分组描述" aligin="center" show-overflow-tooltip></el-table-column>
        <el-table-column align="center" label="操作" width="200">
          <template slot-scope="scope">
            <el-button @click.native="toCategoryGroupMemberSystem(scope.row.code)" type="text" size="small">组成员管理</el-button>
            <el-button @click.native="showEditDialog(scope.row.code)" type="text" size="small">编辑</el-button>
            <el-button @click.native="delCategoryGroup(scope.row.code)" type="text" size="small">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <!-- 添加栏目分组 -->
    <el-dialog width="827px" title="添加栏目分组" :visible.sync="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="115px" :rules="addFormRules" ref="addForm">
        <el-row>
          <el-form-item label="栏目分组Code" prop="code">
            <el-input v-model="addForm.code" placeholder="请输入" :maxlength="64"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="栏目分组名称" prop="name">
            <el-input v-model="addForm.name" placeholder="请输入" :maxlength="64"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="栏目分组描述" prop="descript">
            <el-input v-model="addForm.descript" placeholder="请输入" :maxlength="64"></el-input>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dislog-footer">
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确定</el-button>
        <el-button @click.native="addVisible = false">取消</el-button>
      </div>
    </el-dialog>

    <!-- 编辑栏目分组 -->
    <el-dialog width="827px" title="编辑栏目分组" :visible.sync="editVisible" :close-on-click-modal="false" center>
      <el-form :model="editForm" label-width="115px" :rules="editFormRules" ref="editForm">
        <el-row>
          <el-form-item label="栏目分组Code" prop="code">
            <el-input v-model="editForm.code" placeholder="请输入" :maxlength="64" :disabled="true"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="栏目分组名称" prop="name">
            <el-input v-model="editForm.name" placeholder="请输入" :maxlength="50"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="栏目分组描述" prop="descript">
            <el-input v-model="editForm.descript" placeholder="请输入" :maxlength="64"></el-input>
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
import Pagination from '@/components/Pagination'

export default {
  name: 'CategoryGroup',
  data () {
    return {
      totalAll: 0,        // 列表总数目
      pageSize: 10,       // 分页显示数目
      pageNo: 1,          // 当前页码
      pageRefresh: true,  // 分页内容刷新

      tablePageData: [],  // 分页显示数据

      // 新增栏目分组
      addVisible: false,
      addLoading: false,
      addFormRules: {
        code: [{required: true, message: '请输入栏目分组Code', trigger: 'blur'}, {validator: this.checkCodeUnique, trigger: 'blur'}],
        name: [{required: true, message: '请输入栏目分组名称', trigger: 'blur'}]
      },
      addForm: {
        code: '',
        name: '',
        descript: ''
      },

      // 编辑栏目分组
      editVisible: false,
      editLoading: false,
      editFormRules: {
        code: [{required: true, message: '请输入栏目分组Code', trigger: 'blur'}],
        name: [{required: true, message: '请输入栏目分组名称', trigger: 'blur'}]
      },
      editForm: {
        code: '',
        name: '',
        descript: ''
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
      HTTP.getCategoryGroupList(params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.category_group_list
          this.totalAll = res.data.category_group_cnt
          this.initPagination()
        } else {
          this.$message({showClose: true, message: '获取栏目分组列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // check categoryGroupCode unique
    checkCodeUnique (rule, value, callback) {
      HTTP.checkCategoryGroupCodeUnique(value).then(res => {
        if (res.code === 0) {
          if (res.data.check_res.length > 0) {
            callback(new Error('栏目分组Code重复，请重新输入'))
          } else {
            callback()
          }
        } else {
          console.error(res.code)
        }
      })
    },

    toCategoryGroupMemberSystem (columnGroupCode) {
      this.$router.push({name: '栏目组成员管理', params: {'columnGroupCode': columnGroupCode}})
    },

    // 添加
    showAddDialog () {
      this.addVisible = true
      this.addForm = {
        code: '',
        name: '',
        descript: ''
      }
      setTimeout(() => {
        this.$refs.addForm.clearValidate()
      })
    },

    addCategoryofAdd () {
      this.addForm.category_list.push({
        category_code: '',
        sort: '',
        description: ''
      })
      setTimeout(() => {
        let addList = document.getElementById('addList')
        addList.scrollTop = addList.scrollHeight
      }, 100)
    },

    delCategoryofAdd (index) {
      this.addForm.category_list.splice(index, 1)
    },

    addSubmit () {
      let _this = this
      this.$refs.addForm.validate((valid) => {
        if (valid) {
          this.addLoading = true
          HTTP.createCategoryGroup(_this.addForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: '添加成功', duration: 2000})
              _this.addVisible = false
              _this.getList()
            } else {
              _this.$message.error({showClose: true, message: '添加失败', duration: 2000})
            }
            _this.addLoading = false
          }).catch(err => {
            console.error(err)
            _this.addLoading = false
          })
        }
      })
    },

    // 编辑
    showEditDialog (code) {
      this.editVisible = true
      HTTP.getCategoryGroupDetail(code).then(data => {
        if (data.code === 0) {
          let categoryGroup = data.data.category_group
          this.editForm = {
            code: categoryGroup.code,
            name: categoryGroup.name,
            descript: categoryGroup.descript
          }

          setTimeout(() => {
            this.$refs.editForm.clearValidate()
          }, 100)
        }
      })
    },

    editSubmit () {
      let _this = this
      this.$refs.editForm.validate((valid) => {
        if (valid) {
          this.editLoading = true
          HTTP.updateCategoryGroup(_this.editForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: '编辑成功', duration: 2000})
              _this.editVisible = false
              _this.getList()
            } else {
              _this.$message.error({showClose: true, message: '编辑失败', duration: 2000})
            }
            _this.editLoading = false
          }).catch(err => {
            console.error(err)
            _this.editLoading = false
          })
        }
      })
    },

    delCategoryGroup (code) {
      let _this = this
      this.$confirm('是否确认删除该栏目分组？', '提示', {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        HTTP.deleteCategoryGroup(code).then(data => {
          if (data.code === 0) {
            _this.$message.success({showClose: true, message: '删除成功', duration: 2000})
            _this.getList()
          } else {
            _this.$message.error({showClose: true, message: '删除失败', duration: 2000})
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
<style>
  .text {
    font-size: 14px;
  }

  .item {
    margin-bottom: 18px;
  }

  .clearfix:before,
  .clearfix:after {
    display: table;
    content: "";
  }
  .clearfix:after {
    clear: both
  }

  .box-card {
    width: 656px;
    line-height: 22px;
    box-shadow: none;
  }
</style>