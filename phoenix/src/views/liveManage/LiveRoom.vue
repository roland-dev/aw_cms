<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>直播管理</el-breadcrumb-item>
        <el-breadcrumb-item>直播室管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu">
      <el-row class="nav clearfix">
        <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr">添加直播室</el-button>
      </el-row>
    </el-row>

    <!-- 列表 -->
    <el-row class="table-menu">
      <!-- 直播室列表 -->
      <el-table
        :data="tablePageData"
        stripe
        style="width: 100%">
        <el-table-column prop="name" label="直播室名称"></el-table-column>
        <el-table-column prop="last_modify_user_name" label="最后修改人"></el-table-column>
        <el-table-column prop="updated_at" label="最后修改时间"></el-table-column>
        <el-table-column align="center" label="操作" width="200">
          <template slot-scope="scope">
            <el-button @click.native="showEditDialog(scope.row.code)" type="text" size="small">编辑直播室</el-button>
            <el-button @click.native="delLiveRoom(scope.row.code)" type="text" size="small">删除直播室</el-button>
          </template>
        </el-table-column>
      </el-table>
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <!-- 添加直播室 -->
    <el-dialog title="添加直播室" :visible.sync="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="55px" :rules="addFormRules" ref="addForm">
        <el-row>
          <el-form-item label="名称" prop="name">
            <el-input v-model="addForm.name" placeholder="请输入" :maxlength="32"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="编码" prop="code" :maxlength="64">
            <el-input v-model="addForm.code" placeholder="请输入"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="密码" prop="password">
            <el-input v-model="addForm.password" placeholder="请输入"></el-input>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确定</el-button>
        <el-button @click.native="addVisible = false">取消</el-button>
      </div>
    </el-dialog>

    <!-- 编辑直播室 -->
    <el-dialog title="编辑直播室" :visible.sync="editVisible" :close-on-click-modal="false" center>
      <el-form :model="editForm" label-width="55px" :rules="editFormRules" ref="editForm">
        <el-row>
          <el-form-item label="名称" prop="name">
            <el-input v-model="editForm.name" placeholder="请输入" :maxlength="32"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="编码" prop="code">
            <el-input v-model="editForm.code" placeholder="请输入" :disabled="true"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="密码" prop="password">
            <el-input v-model="editForm.password" placeholder="请输入"></el-input>
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
import Pagination from '@/components/Pagination'
import HTTP from '../../http/api_live'

export default {
  name: 'LiveRoom',
  data () {
    return {
      totalAll: 0,        // 列表总数目
      pageNo: 1,          // 当前页
      pageSize: 10,       // 分页显示数目
      tablePageData: [],  // 分页显示数据
      pageRefresh: true,   // 分页内容刷新

      // 新增直播室
      addVisible: false,
      addLoading: false,
      addFormRules: {
        code: [
          {required: true, message: '请输入名称', trigger: 'blur'}
        ],
        name: [
          {required: true, message: '请输入编码', trigger: 'blur'}
        ]
      },
      addForm: {
        code: '',
        name: '',
        password: ''
      },

      // 编辑直播室
      editVisible: false,
      editLoading: false,
      editFormRules: {
        code: [
          {required: true, message: '请输入名称', trigger: 'blur'}
        ],
        name: [
          {required: true, message: '请输入编码', trigger: 'blur'}
        ]
      },
      editForm: {
        code: '',
        name: '',
        password: ''
      }
    }
  },
  components: {
    Pagination
  },
  created: function () {
  },
  mounted: function () {
    this.getLiveRoomList()
  },
  methods: {
    // 获取直播室列表
    getLiveRoomList () {
      var params = {
        page_no: this.pageNo,
        page_size: this.pageSize
      }
      HTTP.getLiveRoomList(params).then(res => {
        this.tablePageData = res.data.live_room_list
        this.totalAll = res.data.live_room_cnt
        this.initPagination()
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
      this.getLiveRoomList()
    },

    // 添加直播室
    showAddDialog () {
      this.addVisible = true
      this.addForm = {
        code: '',
        name: '',
        password: ''
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
          HTTP.addLiveRoom(_this.addForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: '新增成功', duration: 2000})
              _this.getLiveRoomList()
              _this.addVisible = false
            } else {
              _this.$message.error({showClose: true, message: '新增失败:' + data.msg, duration: 2000})
            }
            _this.addLoading = false
          }).catch(err => {
            console.err(err)
            _this.addLoading = false
          })
        }
      })
    },

    // 编辑直播室
    showEditDialog (code) {
      this.editVisible = true
      // 获取直播室详情
      HTTP.getLiveRoomInfo(code).then(res => {
        if (res.code === 0) {
          this.editForm = {
            code: code,
            name: res.data.live_room.name,
            password: res.data.live_room.password
          }
          setTimeout(() => {
            this.$refs.editForm.clearValidate()
          }, 100)
        } else {
          this.$message.error(res.msg)
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
          HTTP.updateLiveRoom(_this.editForm.code, _this.editForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: '编辑成功', duration: 2000})
              _this.getLiveRoomList()
              _this.editVisible = false
            } else {
              _this.$message.error({showClose: true, message: '编辑失败:' + data.msg, duration: 2000})
            }
            _this.editLoading = false
          }).catch(err => {
            console.err(err)
            _this.editLoading = false
          })
        }
      })
    },

    // 删除直播室
    delLiveRoom (code) {
      let _this = this
      _this.$confirm('是否确认删除该直播室？', '提示', {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        HTTP.deleteLiveRoom(code).then(data => {
          if (data.code === 0) {
            _this.$message.success({showClose: true, message: '删除成功', duration: 2000})
            _this.getLiveRoomList()
          } else {
            _this.$message.error({showClose: true, message: '删除失败:' + data.msg, duration: 2000})
          }
        }).catch(err => {
          console.error(err)
        })
      }).catch(() => {
        _this.$message({type: 'info', message: '已取消删除'})
      })
    }
  }
}
</script>
