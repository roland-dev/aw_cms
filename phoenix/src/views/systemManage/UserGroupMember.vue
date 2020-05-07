<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>系统管理</el-breadcrumb-item>
        <el-breadcrumb-item :to="{ path: '/admin/user-group'}">用户组管理</el-breadcrumb-item>
        <el-breadcrumb-item>用户组成员管理</el-breadcrumb-item>
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
        <el-table-column prop="name" label="用户名称" align="center"></el-table-column>
        <el-table-column prop="sort" label="序号" align="center"></el-table-column>
        <el-table-column label="操作" align="center" minWidth="140">
          <template slot-scope="scope">
            <el-button @click.native="showEditDialog(scope.row.user_group_id)" type="text" size="small">编辑</el-button>
            <el-button @click.native="delUserGroupMember(scope.row.user_group_id)" type="text" size="small">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <!-- 新增组成员 -->
    <el-dialog title="添加组成员" :visible.sync="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="55px" :rules="addFormRules" ref="addForm">
        <el-row>
          <el-form-item label="用户" prop="user_id">
            <el-select v-model="addForm.user_id" placeholder="请选择">
              <el-option v-for="user in selectUserList" :key="user.id" :value-key="user.id" :label="user.name" :value="user.id">
              </el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="序号" prop="sort">
            <el-input-number v-model="addForm.sort" :min="0" :max="9999"></el-input-number>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确定</el-button>
        <el-button @click.native="addVisible = false">取消</el-button>
      </div>
    </el-dialog>

    <el-dialog title="编辑组成员" :visible.sync="editVisible" :close-on-click-modal="false" center>
      <el-form :model="editForm" label-width="55px" :rules="editFormRules" ref="editForm">
        <el-row>
          <el-form-item label="用户" prop="user_id">
            <el-select v-model="editForm.user_id" placeholder="请选择">
              <el-option v-for="user in selectUserList" :key="user.id" :value-key="user.id" :label="user.name" :value="user.id">
              </el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="序号" prop="sort">
            <el-input-number v-model="editForm.sort" :min="0" :max="9999"></el-input-number>
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
import HTTP from '../../http/api_system'
import Pagination from '@/components/Pagination'

export default {
  name: 'UserGroupMember',
  data () {
    return {
      totalAll: 0,        // 列表总数目
      pageSize: 10,       // 分页显示数目
      pageNo: 1,          // 当前页码
      pageRefresh: true,  // 分页内容刷新

      tablePageData: [],  // 分页显示数据

      // user list
      userList: [],
      selectUserList: [],

      seletedUserIds: [],

      // userGroup detail
      userGroupInfo: {
        code: '',
        name: ''
      },

      // 新增组成员
      addVisible: false,
      addLoading: false,
      addFormRules: {
        user_id: [
          {required: true, message: '请选择用户', trigger: 'blur'}
        ],
        sort: [
          {required: true, message: '请输入排序序号', trigger: 'blur'}
        ]
      },
      addForm: {
        code: '',
        name: '',
        user_id: '',
        sort: ''
      },

      // 编辑组成员
      editVisible: false,
      editLoading: false,
      editFormRules: {
        user_id: [
          {required: true, message: '请选择用户', trigger: 'blur'}
        ],
        sort: [
          {required: true, message: '请输入排序序号', trigger: 'blur'}
        ]
      },
      editForm: {
        id: '',
        code: '',
        name: '',
        user_id: '',
        sort: ''
      }
    }
  },
  components: {
    Pagination
  },
  created: function () {
  },
  mounted: function () {
    this.getUserList()
    this.getList()
    this.getUserIdOfSelected()
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

    getUserList () {
      HTTP.getUserListOfAll().then(res => {
        if (res.code === 0) {
          this.userList = res.data.user_list
        } else {
          this.$message.error(res.msg)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    getUserIdOfSelected () {
      HTTP.getUserIdOfUserGroupCode(this.$route.params.userGroupCode).then(res => {
        if (res.code === 0) {
          this.seletedUserIds = res.data.user_id_list
        } else {
          this.$message.error({showClose: true, message: res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    getList () {
      var params = {
        'page_no': this.pageNo,
        'page_size': this.pageSize
      }
      HTTP.getUserGroupDetail(this.$route.params.userGroupCode, params).then(res => {
        if (res.code === 0) {
          this.userGroupInfo = res.data.user_group
          this.tablePageData = res.data.user_list
          this.totalAll = res.data.user_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '获取组成员列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取 selectUserList
    getSelectUserList (userId) {
      let _this = this
      _this.selectUserList = JSON.parse(JSON.stringify(_this.userList))
      _this.userList.forEach(d => {
        if (_this.seletedUserIds.indexOf(d.id) > -1 && d.id !== userId) {
          let i = _this.array_search(_this.selectUserList, 'id', d.id)
          i !== -1 && _this.selectUserList.splice(i, 1)
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
      this.selectUserList = JSON.parse(JSON.stringify(this.userList))
      this.addForm = {
        code: this.userGroupInfo.code,
        name: this.userGroupInfo.name,
        user_id: '',
        sort: ''
      }
      this.getSelectUserList()
      setTimeout(() => {
        this.$refs.addForm.clearValidate()
      })
    },

    addSubmit () {
      let _this = this
      _this.$refs.addForm.validate((valid) => {
        if (valid) {
          _this.addLoading = true
          HTTP.createUserGroupMember(_this.addForm).then(res => {
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
        }
      })
    },

    showEditDialog (id) {
      let _this = this
      _this.editVisible = true
      _this.selectUserList = JSON.parse(JSON.stringify(_this.userList))
      HTTP.getUserGroupMember(id).then(res => {
        if (res.code === 0) {
          let userGroupMember = res.data.user_group_member
          this.editForm = {
            id: userGroupMember.id,
            code: userGroupMember.code,
            name: userGroupMember.name,
            user_id: userGroupMember.user_id,
            sort: userGroupMember.sort
          }

          this.getSelectUserList(userGroupMember.user_id)
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
          HTTP.updateUserGroupMember(_this.editForm.id, _this.editForm).then(res => {
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

    delUserGroupMember (id) {
      this.$confirm('是否确认删除该组成员？', '提示', {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        HTTP.deleteUserGroupMember(id).then(res => {
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

