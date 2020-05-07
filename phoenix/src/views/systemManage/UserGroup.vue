<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>系统管理</el-breadcrumb-item>
        <el-breadcrumb-item>用户组管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>
		<!-- 搜索区域 -->
		<el-row class="top-menu">
			<el-row class="nav clearfix">
				<el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr">添加用户组</el-button>
			</el-row>
		</el-row>

    <!-- 列表 -->
    <el-row class="table-menu">
      <!-- 用户组表格 -->
      <el-table :data="tablePageData" stripe style="width: 100%">
        <el-table-column prop="code" label="用户组Code" align="center"></el-table-column>
        <el-table-column prop="name" label="用户组名称" align="center"></el-table-column>
        <el-table-column label="操作" align="center" Width="200">
          <template slot-scope="scope">
            <el-button @click.native="toUserGroupMemberSystem(scope.row.code)" type="text" size="small">组成员管理</el-button>
            <el-button @click.native="showEditDialog(scope.row.code)" type="text" size="small">编辑</el-button>
            <el-button @click.native="delUserGroup(scope.row.code)" type="text" size="small">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <!-- 添加用户组 -->
    <el-dialog width="825px" title="添加用户组" :visible.sync="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" :inline="true" label-width="100px" :rules="addFormRules" ref="addForm">
        <el-row>
          <el-form-item label="用户组Code" prop="code">
            <el-input v-model="addForm.code" placeholder="请输入用户组Code" :maxlength="64"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="用户组名称" prop="name">
            <el-input v-model="addForm.name" placeholder="请输入用户组名称" :maxlength="50"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="用户列表">
            <el-card class="box-card">
              <div slot="header" class="clearfix">
                <span style="line-height: 32px;">包含用户</span>
              </div>
              <div style="maxHeight: 350px; overflow-y:auto;" id="addList">
                <div v-for="(item, index) in addForm.user_list" :key="item.key" class="text item">
                  <template>
                    <el-form-item label="用户" label-width="60px"
                      :prop="'user_list.' + index + '.user_id'"
                      :rules="{
                        required: true, message: '请选择用户', trigger: 'blur'
                      }">
                      <el-select v-model="item.user_id" placeholder="请选择" @change="setSelectUserList" @click.native="getSelectUserList(addForm.user_list, item.user_id)">
                        <el-option
                          v-for="user in selectUserList"
                          :key="user.id"
                          :label="user.name"
                          :value="user.id">
                        </el-option>
                      </el-select>
                    </el-form-item>
                    <el-form-item label="序号" label-width="60px"
                      :prop="'user_list.' + index + '.sort'"
                      :rules="{
                        required: true, message: '请输入排列序号', trigger: 'blur'
                      }">
                      <el-input v-model="item.sort" placeholder="请输入序号" type="number" :min="0" class="short-input">
                      </el-input>
                    </el-form-item>
                    <el-button v-if="addForm.user_list.length > 1" style="float: right; margin-right: 5px;" type="primary" round icon="el-icon-delete" @click="delUserofAdd(index)"></el-button>
                  </template>
                </div>
              </div>
              <div style="width:100%; text-align: center;  margin-top: 15px;">
                <el-button type="primary" round icon="el-icon-plus" @click="addUserofAdd">添加用户</el-button>  
              </div>
            </el-card>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确定</el-button>
        <el-button @click.native="addVisible = false">取消</el-button>
      </div>
    </el-dialog>

    <!-- 编辑用户组 -->
    <el-dialog width="825px" title="编辑用户组" :visible.sync="editVisible" :close-on-click-modal="false" center>
      <el-form :model="editForm" :inline="true" label-width="100px" :rules="editFormRules" ref="editForm">
        <el-row>
          <el-form-item label="用户组Code" prop="code">
            <el-input v-model="editForm.code" placeholder="请输入用户组Code" :maxlength="64" :disabled="true"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="用户组名称" prop="name">
            <el-input v-model="editForm.name" placeholder="请输入用户组名称" :maxlength="50"></el-input>
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
import Pagination from '@/components/Pagination'

export default {
  name: 'UserGroup',
  data () {
    return {
      totalAll: 0,
      pageSize: 10,
      pageNo: 1,
      pageRefresh: true,

      tablePageData: [],

      // user list
      userList: [],
      selectUserList: [],

      // 新增用户组
      addVisible: false, // 是否显示
      addLoading: false,
      addFormRules: {
        code: [{required: true, message: '请输入用户组Code', trigger: 'blur'}],
        name: [{required: true, message: '请输入用户组名称', trigger: 'blur'}]
      },
      addForm: {
        code: '',
        name: '',
        user_list: [{
          user_id: '',
          sort: ''
        }]
      },

      // 编辑用户组
      editVisible: false, // 是否显示
      editLoading: false,
      editFormRules: {
        code: [{required: true, message: '请输入用户组Code', trigger: 'blur'}],
        name: [{required: true, message: '请输入用户组名称', trigger: 'blur'}]
      },
      editForm: {
        code: '',
        name: ''
      }

    }
  },
  components: {
    Pagination
  },
  created: function () {},
  mounted: function () {
    this.getUserList()
    this.getList()
  },
  methods: {
    initPagination () {
      this.pageRefresh = false
      this.$nextTick(() => { this.pageRefresh = true })
    },

    // 跳转参数
    gotoPage (page) {
      this.pageNo = page
      this.getList()
    },

    getUserList () {
      API_SYSTEM.getUserListOfAll().then(data => {
        if (data.code === 0) {
          this.userList = data.data.user_list
          this.selectUserList = data.data.user_list
        } else {
          this.$message.error(data.msg)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    setSelectUserList (value) {
      let _this = this
      _this.selectUserList.forEach(d => {
        if (d.id === value) {
          let i = _this.selectUserList.indexOf(d)
          _this.selectUserList.splice(i, 1)
        }
      })
    },

    getSelectUserList (arr, userId) {
      let _this = this
      let ids = _this.array_column(arr, 'user_id')
      _this.selectUserList = JSON.parse(JSON.stringify(_this.userList))
      _this.userList.forEach(d => {
        if (ids.indexOf(d.id) > -1 && d.id !== userId) {
          let i = _this.array_search(this.selectUserList, 'id', d.id)
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

    getList () {
      var params = {
        page_no: this.pageNo,
        page_size: this.pageSize
      }
      API_SYSTEM.getUserGroups(params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.user_group_list
          this.totalAll = res.data.user_group_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '获取用户组列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    toUserGroupMemberSystem (userGroupCode) {
      this.$router.push({name: '用户组成员管理', params: {'userGroupCode': userGroupCode}})
    },

    showAddDialog () {
      this.addVisible = true
      this.selectUserList = JSON.parse(JSON.stringify(this.userList))
      this.addForm = {
        code: '',
        name: '',
        user_list: [{
          user_id: '',
          sort: ''
        }]
      }
      setTimeout(() => {
        this.$refs.addForm.clearValidate()
      })
    },

    addUserofAdd () {
      this.addForm.user_list.push({
        user_id: '',
        sort: ''
      })
      setTimeout(() => {
        let addList = document.getElementById('addList')
        addList.scrollTop = addList.scrollHeight
      }, 100)
    },

    delUserofAdd (index) {
      this.addForm.user_list.splice(index, 1)
    },

    addSubmit () {
      let _this = this
      this.$refs.addForm.validate((valid) => {
        if (valid) {
          this.addLoading = true
          API_SYSTEM.createUserGroup(_this.addForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: data.msg, duration: 2000})
              _this.addVisible = false
              _this.getList()
            } else {
              _this.$message.error(data.msg)
            }
            _this.addLoading = false
          }).catch(err => {
            console.error(err)
            _this.addLoading = false
          })
        }
      })
    },

    showEditDialog (code) {
      this.editVisible = true
      this.selectUserList = JSON.parse(JSON.stringify(this.userList))
      API_SYSTEM.getUserGroupDetail(code).then(data => {
        if (data.code === 0) {
          this.editForm = {
            code: code,
            name: data.data.user_group.name
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
          API_SYSTEM.updateUserGroup(_this.editForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: data.msg, duration: 2000})
              _this.editVisible = false
              _this.getList()
            } else {
              _this.$message.error(data.msg)
            }
            _this.editLoading = false
          }).catch(err => {
            console.error(err)
            _this.editLoading = false
          })
        }
      })
    },

    delUserGroup (code) {
      this.$confirm('是否确认删除该用户组？', '提示', {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        API_SYSTEM.deleteUserGroup(code).then(data => {
          if (data.code === 0) {
            this.$message.success({showClose: true, message: '删除成功', duration: 2000})
            this.getList()
          } else {
            this.$message.error(data.msg)
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