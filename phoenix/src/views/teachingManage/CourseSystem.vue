<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>教学管理</el-breadcrumb-item>
        <el-breadcrumb-item>课程体系管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu"> 
      <el-row class="nav clearfix">
        <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr">添加课程体系</el-button>
      </el-row>
    </el-row>

    <!-- 列表 -->
    <el-row class="table-menu">
      <el-table
        :data="tablePageData"
        stripe
        style="width: 100%">
        <el-table-column fixed prop="name" label="课程体系名称"></el-table-column>
        <el-table-column prop="user_name" label="最后修改人"></el-table-column>
        <el-table-column prop="updated_at" label="最后修改时间"></el-table-column>
        <el-table-column prop="sort_no" label="排序序号" width="160">
          <template slot-scope="scope">
            <div @click="dbClickEdit(scope.row.id, scope.row.sort_no,  $event.target)">{{scope.row.sort_no}}</div>
            <input type="text" v-model.number="scope.row.sort_no" style="display: none;" @blur="ClickEditEnd(scope.row.id, scope.row.sort_no, $event.target)">
          </template>
        </el-table-column>
        <el-table-column fixed="right" align="center" label="操作" width="100">
          <template slot-scope="scope">
            <el-button  @click.native="showEditDialog(scope.row.id)" type="text" size="small">编辑</el-button>
            <el-button  @click.native="delCourseSystem(scope.row.id, scope.row.code)" type="text" size="small">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <!-- 添加用户 -->
    <el-dialog title="添加课程体系" :visible.sync ="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="120px" :rules="addFormRules" ref="addForm">
        <el-row>
          <el-form-item label="课程体系名称" prop="name">
            <el-input v-model="addForm.name" placeholder="请输入" :maxlength="12"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="key值" prop="code">
            <el-input v-model="addForm.code" placeholder="请输入"></el-input>
          </el-form-item>
        </el-row> 
        <el-row>
          <el-form-item label="排序序号" prop="sort_no">
            <el-input v-model.number="addForm.sort_no" placeholder="请输入" :maxlength="12"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="归属栏目" prop="category_code">
            <el-select v-model="addForm.category_code" clearable placeholder="请选择">
              <el-option v-for="category in categoryList" :value-key="category.code" :key="category.name" :label="category.name" :value="category.code"></el-option>            </el-select>
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
      <el-form :model="editForm" label-width="120px" :rules="editFormRules" ref="editForm">
        <el-row>
          <el-form-item label="课程体系名称" prop="name">
            <el-input v-model="editForm.name" placeholder="请输入" :maxlength="12"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="key值" prop="code">
            <el-input v-model="editForm.code" placeholder="请输入"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="归属栏目" prop="category_code">
            <el-select v-model="editForm.category_code" clearable placeholder="请选择">
              <el-option v-for="category in categoryList" :value-key="category.code" :key="category.name" :label="category.name" :value="category.code"></el-option>            </el-select>
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
import API_TEACHING from '../../http/api_teaching'
import Pagination from '@/components/Pagination'

export default {
  name: 'CourseSystem',
  data () {
    return {
      // 搜索区表单
      totalAll: 0,          // 列表总数目
      pageSize: 10,         // 分页显示数目
      pageNo: 1,            // 当前页码
      pageRefresh: true,    // 分页内容刷新

      tablePageData: [],    // 分页显示数据
      categoryList: [],     // 显示分类列表

      // 新增课程体系
      addVisible: false, // 是否显示
      addLoading: false,
      addFormRules: {
        name: [{required: true, message: '请输入课程体系名称', trigger: 'blur'}],
        code: [{required: true, message: '请输入key值', trigger: 'blur'}, {validator: this.checkSystemCodeUnique, trigger: 'blur'}],
        sort_no: [{required: true, message: '请输入排序', trigger: 'blur'}, {type: 'number', message: '请输入数字', trigger: 'blur'}],
        category_code: [{required: true, message: '请选择栏目', trigger: 'blur'}]
      },
      addForm: {name: '', code: '', sort_no: ''},

      // 编辑课程体系
      editVisible: false, // 是否显示
      editLoading: false,
      editFormRules: {
        name: [{required: true, message: '请输入课程体系名称', trigger: 'blur'}],
        code: [{required: true, message: '请输入key值', trigger: 'blur'}, {validator: this.editCheckSystemCodeUnique, trigger: 'blur'}],
        category_code: [{required: true, message: '请选择栏目', trigger: 'blur'}]
      },
      editForm: {name: '', code: '', category_code: ''},
      // sortNo
      globalSortNo: ''
    }
  },
  components: {
    Pagination
  },
  mounted: function () {
    this.getCategory()
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
      var params = {
        page_no: this.pageNo,
        page_size: this.pageSize
      }
      API_TEACHING.getCourseSystemList(params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.course_system_list
          this.totalAll = res.data.course_system_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '获取课程体系列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // // 更新表格
    updateList () {
      this.getList()
    },

    // 双击修改表格区域内容
    dbClickEdit (id, sortNo, element) {
      // 消失div显示input
      this.globalSortNo = sortNo
      element.style = 'display: none'
      element.nextSibling.nextSibling.style = 'display: block; width: 100px;'
      // 聚焦点到input
      element.nextSibling.nextSibling.focus()
    },

    // 双击修改表格区离开事件
    ClickEditEnd (id, sortNo, element) {
      // 发送请求
      let orderForm = {sequence: element.value, course_system_id: id}
      if (element.value.length > 0) {
        if (this.globalSortNo !== parseInt(element.value)) {
          API_TEACHING.courseSystemOrder(orderForm).then(data => {
            console.log(data)
            this.getList()
          }).catch(err => {
            console.error(err)
          })
        } else {
          element.value = this.globalSortNo
        }
      } else {
        // 为空则不修改，并返回oldhtml(这个地方后面要绑定你的model对象)
        this.getList()
      }
      element.style = 'display: none'
      element.previousElementSibling.style = 'display: block'
    },

    // 登记视频
    showAddDialog () {
      this.addVisible = true
      this.addForm = {
        name: '',
        code: '',
        sort_no: '',
        category_code: ''
      }
      // 清空input框验证状态
      setTimeout(() => {
        this.$refs.addForm.clearValidate()
      }, 100)
    },

    // 获取分类
    getCategory () {
      API_TEACHING.getCategoryList().then(res => {
        this.categoryList = res.data
      }).catch(err => {
        console.error(err)
      })
    },

    addSubmit () {
      let _this = this
      this.$refs.addForm.validate((valid) => {
        if (valid) {
          API_TEACHING.addCourseSystem(_this.addForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: '新增成功', duration: 2000})
              _this.updateList()
            } else if (data.code === 110003) {
              _this.$message.error({showClose: true, message: '课程体系已经存在', duration: 2000})
            } else {
              _this.$message.error({showClose: true, message: '新增失败', duration: 2000})
            }
            _this.addVisible = false
          }).catch(err => {
            console.error(err)
          })
          _this.addVisible = false
        }
      })
    },

    showEditDialog (id) {
      this.editVisible = true
      // 请求一个当前id
      API_TEACHING.findCourseSystem(id).then(res => {
        // console.log('courseSystem', res)
        this.editForm = {
          course_system_id: id,
          name: res.data.one_course_system.name,
          code: res.data.one_course_system.code,
          category_code: res.data.one_course_system.primary_category
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
          API_TEACHING.updateCourseSystem(_this.editForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: '编辑成功', duration: 2000})
              this.updateList()
            } else if (data.code === 110003) {
              _this.$message.error({showClose: true, message: '课程体系已经存在', duration: 2000})
            } else if (data.code === 110007) {
              _this.$message.error({showClose: true, message: '课程体系已经删除', duration: 2000})
            } else {
              _this.$message.error({showClose: true, message: '编辑失败', duration: 2000})
            }

            _this.editVisible = false
          }).catch(err => {
            console.error(err)
          })
          _this.editVisible = false
        }
      })
    },

    delCourseSystem (id, code) {
      this.$confirm('是否确定删除该条记录?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        API_TEACHING.removeCourseSystem(id, code).then(res => {
          if (res.code === 0) {
            this.$message.success({showClose: true, message: '删除成功', duration: 2000})
            this.updateList()
          }
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消删除'
        })
      })
    },

    // check courseCode unique
    checkSystemCodeUnique (rule, value, callback) {
      API_TEACHING.checkCourseSystemCodeUnique(value).then(res => {
        if (res.data.check_res.length > 0) {
          callback(new Error('课程code重复,请重新输入'))
        } else {
          callback()
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // check courseCode unique
    editCheckSystemCodeUnique (rule, value, callback) {
      API_TEACHING.checkCourseSystemCodeUnique(value).then(res => {
        if (res.data.check_res.length > 1) {
          callback(new Error('课程code重复,请重新输入'))
        } else if (res.data.check_res.length === 1 && res.data.check_res[0].id !== parseInt(this.editForm.course_system_id)) {
          callback(new Error('课程code重复,请重新输入'))
        } else {
          callback()
        }
      }).catch(err => {
        console.error(err)
      })
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
