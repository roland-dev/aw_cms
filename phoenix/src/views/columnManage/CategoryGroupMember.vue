<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>栏目管理</el-breadcrumb-item>
        <el-breadcrumb-item :to="{ path: '/column/category_group'}">栏目分类管理</el-breadcrumb-item>
        <el-breadcrumb-item>栏目组成员管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu">
      <el-row class="nav clearfix">
        <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr">添加组成员</el-button>
      </el-row>
    </el-row>

    <!-- 列表 -->
    <el-row class="table-menu">
      <el-table :data="tablePageData" stripe style="width: 100%">
        <el-table-column prop="category_name" label="栏目分类名称" align="center"></el-table-column>
        <el-table-column prop="sort" label="序号" align="center"></el-table-column>
        <el-table-column label="操作" align="center" minWidth="140">
          <template slot-scope="scope">
            <el-button @click.native="showEditDialog(scope.row.id)" type="text" size="small">编辑</el-button>
            <el-button @click.native="delCategoryGroupMember(scope.row.id)" type="text" size="samll">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <!-- 新增组成员 -->
    <el-dialog title="添加组成员" :visible.sync="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">
        <el-row>
          <el-form-item label="栏目分组" prop="category_code">
            <el-select v-model="addForm.category_code" placeholder="请选择">
              <el-option v-for="category in selectCategoryList" :key="category.code" :value-key="category.code" :label="category.name" :value="category.code">
              </el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="序号">
            <el-input-number v-model="addForm.sort" :min="0" :max="9999"></el-input-number>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="备注">
            <el-input type="textarea" v-model="addForm.description" placeholder="请输入" :maxlength="50">
            </el-input>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确定</el-button>
        <el-button @click.native="addVisible = false">取消</el-button>
      </div>
    </el-dialog>

    <!-- 编辑组成员 -->
    <el-dialog title="编辑组成员" :visible.sync="editVisible" :close-on-click-model="false" center>
      <el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">
        <el-row>
          <el-form-item label="栏目分组" prop="category_code">
            <el-select v-model="editForm.category_code" placeholder="请选择">
              <el-option v-for="category in selectCategoryList" :key="category.code" :value-key="category.code" :label="category.name" :value="category.code">
              </el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="序号">
            <el-input-number v-model="editForm.sort" :min="0" :max="9999"></el-input-number>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="备注">
            <el-input type="textarea" v-model="editForm.description" placeholder="请输入" :maxlength="50">
            </el-input>
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
  data () {
    return {
      totalAll: 0,        // 列表总数目
      pageSize: 10,       // 分页显示数目
      pageNo: 1,          // 当前页码
      pageRefresh: true,  // 分页内容刷新

      tablePageData: [],  // 分页显示数据

      // user list
      categoryList: [],
      selectCategoryList: [],

      // category detail
      categoryGroupInfo: {
        code: '',
        name: ''
      },

      // 新增栏目组成员
      addVisible: false,
      addLoading: false,
      addFormRules: {
        category_code: [
          {required: true, message: '请输入栏目分类', trigger: 'blur'}
        ]
      },
      addForm: {
        code: '',
        name: '',
        category_code: '',
        sort: '',
        description: ''
      },

      // 编辑栏目组成员
      editVisible: false,
      editLoading: false,
      editFormRules: {
        category_code: [
          {required: true, message: '请输入栏目分类', trigger: 'blur'}
        ]
      },
      editForm: {
        id: '',
        code: '',
        name: '',
        category_code: '',
        sort: '',
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
    this.getCategoryList()
    this.getList()
  },
  methods: {
    getCategoryList () {
      HTTP.getCategoryListByGroup().then(res => {
        if (res.code === 0) {
          this.categoryList = res.data.category_list
          this.selectCategoryList = res.data.category_list
        } else {
          console.log(res.msg)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    getCategoryGroupInfo () {
      HTTP.getCategoryGroupDetail(this.$route.params.columnGroupCode).then(res => {
        if (res.code === 0) {
          this.categoryGroupInfo = res.data.category_group
        } else {
          console.error(res.msg)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    initPagination () {
      this.pageRefresh = false
      this.$nextTick(() => { this.pageRefresh = true })
    },

    gotoPage (page) {
      this.pageNo = page
      this.getList()
    },

    getList () {
      var params = {
        page_no: this.pageNo,
        page_size: this.pageSize,
        category_group_code: this.$route.params.columnGroupCode
      }
      HTTP.getCategoryGroupMemberList(params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.category_group_member_list
          this.totalAll = res.data.category_group_member_cnt
          this.initPagination()
        } else {
          this.$message.error({show: true, message: '获取栏目分组组成员列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取 selectCategoryList
    getSelectCategoryList (categoryCode) {
      let _this = this
      let codes = _this.array_column(_this.tableData, 'category_code')
      _this.selectCategoryList = JSON.parse(JSON.stringify(_this.categoryList))
      _this.categoryList.forEach(d => {
        if (codes.indexOf(d.code) > -1 && d.code !== categoryCode) {
          let i = _this.array_search(this.selectCategoryList, 'code', d.code)
          i !== -1 && _this.selectCategoryList.splice(i, 1)
        }
      })
    },

    array_column (arr, param) {
      let resultArr = []

      arr.forEach(d => {
        resultArr.push(d[param])
      })

      return resultArr
    },

    array_search (arr, param, paramValue) {
      let index = -1

      arr.forEach(d => {
        if (d[param] === paramValue) {
          index = arr.indexOf(d)
        }
      })

      return index
    },

    showAddDialog () {
      this.addVisible = true
      this.selectCategoryList = JSON.parse(JSON.stringify(this.categoryList))
      this.addForm = {
        code: this.categoryGroupInfo.code,
        name: this.categoryGroupInfo.name,
        category_code: '',
        sort: '',
        description: ''
      }
      this.getSelectCategoryList()
      setTimeout(() => {
        this.$refs.addForm.clearValidate()
      })
    },

    addSubmit () {
      let _this = this
      _this.$refs.addForm.validate((valid) => {
        _this.addLoading = true
        HTTP.createCategoryGroupMember(_this.addForm).then(res => {
          if (res.code === 0) {
            _this.$message.success({showClose: true, message: res.msg, duration: 2000})
            _this.addVisible = false
            _this.getList()
          } else {
            _this.$message.error(res.msg)
          }
          _this.addLoading = false
        }).catch(err => {
          console.error(err)
          _this.addLoading = false
        })
      })
    },

    showEditDialog (id) {
      let _this = this
      _this.editVisible = true
      _this.selectCategoryList = JSON.parse(JSON.stringify(_this.categoryList))
      HTTP.getCategoryGroupMember(id).then(res => {
        if (res.code === 0) {
          let categoryGroupMember = res.data.category_group_member
          this.editForm = {
            id: categoryGroupMember.id,
            code: categoryGroupMember.code,
            name: categoryGroupMember.name,
            category_code: categoryGroupMember.category_code,
            sort: categoryGroupMember.sort,
            description: categoryGroupMember.description
          }

          this.getSelectCategoryList(categoryGroupMember.category_code)
          setTimeout(() => {
            this.$refs.editForm.clearValidate()
          })
        }
      })
    },

    editSubmit () {
      let _this = this
      _this.$refs.editForm.validate((valid) => {
        if (valid) {
          _this.editLoading = true
          HTTP.updateCategoryGroupMember(_this.editForm.id, _this.editForm).then(res => {
            if (res.code === 0) {
              _this.$message.success({showClose: true, message: res.msg, duration: 2000})
              _this.editVisible = false
              _this.getList()
            } else {
              _this.$message.error(res.msg)
            }
            _this.editLoading = false
          }).catch(err => {
            console.log(err)
            _this.editLoading = false
          })
        }
      })
    },

    delCategoryGroupMember (id) {
      this.$confirm('是否确认删除该组成员？', '提示', {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        HTTP.deleteCategoryGroupMember(id).then(res => {
          if (res.code === 0) {
            this.$message.success({showClose: true, message: '删除成功', duration: 2000})
            this.getList()
          } else {
            this.$message.error(res.msg)
          }
        }).catch(err => {
          console.error(err)
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消删除'
        })
      })
    }
  }
}
</script>
