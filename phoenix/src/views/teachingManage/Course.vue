<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>教学管理</el-breadcrumb-item>
        <el-breadcrumb-item>课程管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu"> 
      <el-row class="nav clearfix">
        <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr">添加课程</el-button>
      </el-row>
      <el-form :inline="true" :model="formInline">
        <el-row>
          <el-form-item label="课程名称" prop="name"> 
            <el-input v-model="formInline.course_name" placeholder="请输入"></el-input>  
          </el-form-item>
          <el-form-item label="所属课程体系">
            <el-select v-model="formInline.course_system_code" clearable placeholder="请选择">
              <el-option v-for="courseSystem in courseSystemType" :value-key="courseSystem.code" :key="courseSystem.name" :label="courseSystem.name" :value="courseSystem.code"></el-option>
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
      <el-table
        :data="tablePageData"
        stripe
        style="width: 100%">
        <el-table-column fixed prop="name" label="课程名称"></el-table-column>
        <!-- <el-table-column prop="description" label="课程简介"></el-table-column> -->
        <el-table-column prop="course_system_name" label="课程所属体系"></el-table-column>
        <el-table-column prop="user_name" label="最后修改人"></el-table-column>
        <el-table-column prop="updated_at" label="最后修改时间"></el-table-column>
        <el-table-column prop="sort_no" label="排序序号" width="160">
          <template slot-scope="scope">
            <div @click="dbClickEdit(scope.row.id, scope.row.sort_no,  $event.target)">{{scope.row.sort_no}}</div>
			<input type="text" v-model.number="scope.row.sort_no" style="display: none;" @blur="ClickEditEnd(scope.row.id, scope.row.sort_no, $event.target)">
          </template>
        </el-table-column>
        <el-table-column fixed="right" align="center" label="操作" width="160">
          <template slot-scope="scope">
            <el-button  @click.native="toVideoManage(scope.row.code)" type="text" size="small">视频管理</el-button>
            <el-button  @click.native="showEditDialog(scope.row.id, scope.row.course_system_id, scope.row.code)" type="text" size="small">编辑</el-button>
            <el-button  @click.native="delCourse(scope.row.id, scope.row.code)" type="text" size="small">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <!-- 添加 -->
    <el-dialog title="添加课程" :visible.sync ="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="120px" :rules="addFormRules" ref="addForm">
        <el-row>
          <el-form-item label="课程名称" prop="name">
            <el-input v-model="addForm.name" placeholder="请输入" :maxlength="20"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="课程code值" prop="code">
            <el-input v-model="addForm.code" placeholder="请输入" :maxlength="20"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="所属课程体系" prop="course_system_code">
            <el-select v-model="addForm.course_system_code" clearable placeholder="请选择">
              <el-option v-for="courseSystem in courseSystemType" :value-key="courseSystem.code" :key="courseSystem.name" :label="courseSystem.name" :value="courseSystem.code"></el-option>
            </el-select>
          </el-form-item>  
        </el-row>        
        <el-row>
          <el-form-item label="课程简介" prop="description">
            <el-input type="textarea" :rows="4"  v-model.number="addForm.description" placeholder="请输入" :maxlength="200"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="课程介绍" prop="full_text_description">
            <editor ref="courseAddEditor" editorId="addCourse" :content="addForm.full_text_description" ></editor>
          </el-form-item>
        </el-row> 
        <el-row>
          <el-form-item label="服务key值" prop="service_code">
            <el-select v-model="addForm.service_code" clearable placeholder="请选择">
              <el-option v-for="service in serviceType" :value-key="service.code" :key="service.name" :label="service.name" :value="service.code"></el-option>
            </el-select>
          </el-form-item>  
        </el-row>  
        <el-row>
          <el-form-item label="排序序号" prop="sort_no">
            <el-input v-model.number="addForm.sort_no" placeholder="请输入" :maxlength="20"></el-input>
          </el-form-item>
        </el-row>

      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确定</el-button>
        <el-button @click.native="addVisible = false">取消</el-button>        
      </div>
    </el-dialog>

    <!-- 编辑 -->
    <el-dialog title="编辑课程" :visible.sync ="editVisible" :close-on-click-modal="false" center>
      <el-form :model="editForm" label-width="120px" :rules="editFormRules" ref="editForm">
        <el-row>
          <el-form-item label="课程名称" prop="name">
            <el-input v-model="editForm.name" placeholder="请输入" :maxlength="20"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="课程code值" prop="code">
            <el-input v-model="editForm.code" placeholder="请输入" :maxlength="20"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="所属课程体系" prop="course_system_code">
            <el-select v-model="editForm.course_system_code" placeholder="请选择">
              <el-option v-for="courseSystem in courseSystemType" :value-key="courseSystem.code" :key="courseSystem.name" :label="courseSystem.name" :value="courseSystem.code"></el-option>
            </el-select>
          </el-form-item>  
        </el-row>        
        <el-row>
          <el-form-item label="课程简介" prop="description">
            <el-input type="textarea" :rows="4"  v-model.number="editForm.description" placeholder="请输入" :maxlength="200"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="课程介绍" prop="full_text_description" >
            <!-- <el-input type="textarea" v-model="editForm.full_text_description" placeholder="请输入" :maxlength="50"></el-input> -->
            <editor ref="courseEditEditor" editorId="editCourse" :content="editForm.full_text_description" ></editor>
          </el-form-item>
        </el-row> 
        <el-row>
          <el-form-item label="服务key值" prop="service_code">
            <el-select v-model="editForm.service_code" clearable placeholder="请选择">
              <el-option v-for="service in serviceType" :value-key="service.code" :key="service.name" :label="service.name" :value="service.code"></el-option>
            </el-select>
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
import Editor from '@/components/Editor' // 调用编辑器
import Pagination from '@/components/Pagination'

export default {
  name: 'Course',
  data () {
    return {
      // 搜索区表单
      formInline: {course_name: '', course_system_code: ''},

      // 缓存搜索数据
      searchParams: {course_name: '', course_system_code: ''},

      totalAll: 0,          // 列表总数目
      pageSize: 10,         // 分页显示数目
      pageNo: 1,            // 当前页码
      pageRefresh: true,    // 分页内容刷新

      tablePageData: [],    // 分页显示数据
      courseSystemType: [], // 课程体系
      serviceType: [],

      // 新增课程
      addVisible: false, // 是否显示
      addLoading: false,
      addFormRules: {
        name: [{required: true, message: '请输入课程名称', trigger: 'blur'}],
        sort_no: [{required: true, message: '请输课程排序', trigger: 'blur'}, {type: 'number', message: '请输入数字', trigger: 'blur'}],
        code: [{required: true, message: '请输入课程code值', trigger: 'blur'}, {validator: this.checkCodeUnique, trigger: 'blur'}],
        course_system_code: [{required: true, message: '请选择课程体系code', trigger: 'blur'}],
        service_code: [{required: true, message: '请选择服务key值', trigger: 'blur'}]
      },
      addForm: {name: '', code: '', course_system_code: '', description: '', full_text_description: '', service_code: '', sort_no: ''},
      // 编辑课程
      editVisible: false, // 是否显示
      editLoading: false,
      editFormRules: {
        name: [{required: true, message: '请输入课程名称', trigger: 'blur'}],
        code: [{required: true, message: '请输入课程code值', trigger: 'blur'}, {validator: this.editCheckCodeUnique, trigger: 'blur'}],
        course_system_code: [{required: true, message: '请选择课程体系code', trigger: 'blur'}],
        service_code: [{required: true, message: '请选择服务key值', trigger: 'blur'}]
      },
      editForm: {name: '', code: '', course_system_code: '', description: '', full_text_description: '', service_code: ''},

      // sortNo
      globalSortNo: ''
    }
  },
  mounted: function () {
    this.getList()
    this.getCourseSystem()
    this.getService()
  },
  components: {
    Pagination,
    Editor  // 引入wangEditor富文本编辑器模块
  },
  methods: {
    initPagination () {
      this.pageRefresh = false
      this.$nextTick(() => { this.pageRefresh = true })
    },

    // 跳转分页
    gotoPage (page) {
      this.pageNo = page
      this.updateList()
    },

    // 获取列表内容
    getList () {
      var params = {
        page_no: this.pageNo,
        page_size: this.pageSize
      }
      API_TEACHING.getCourseList(params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.course_list
          this.totalAll = res.data.course_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '获取课程列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
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
      let orderForm = {sequence: element.value, course_id: id}
      if (element.value.length > 0) {
        if (this.globalSortNo !== parseInt(element.value)) {
          API_TEACHING.courseOrder(orderForm).then(data => {
            this.updateList()
          }).catch(err => {
            console.error(err)
          })
        } else {
          element.value = this.globalSortNo
        }
      } else {
        // 为空则不修改，并返回oldhtml(这个地方后面要绑定你的model对象)
        this.updateList()
      }
      element.style = 'display: none'
      element.previousElementSibling.style = 'display: block'
    },

    // 双击修改表格区域内容
    // dbClickEdit (id, sortNo, element) {
    //   let _this = this
    //   let oldhtml = element.innerHTML
    //   let newobj = document.createElement('input')
    //   newobj.type = 'text'
    //   newobj.style = 'width: 50px'
    //   newobj.value = oldhtml
    //   element.innerHTML = ''
    //   element.appendChild(newobj)
    //   newobj.focus()
    //   // 焦点离开时间
    //   newobj.onblur = function () {
    //     let orderForm = {sequence: this.value, course_id: id}
    //     // 下面判断是否发生修改，发生了就去进行post请求，并刷新排序
    //     if (sortNo !== parseInt(this.value)) {
    //       if (this.value === '') {
    //         element.innerHTML = oldhtml
    //       } else {
    //         API_TEACHING.courseOrder(orderForm).then(data => {
    //           element.innerHTML = this.value ? this.value : sortNo
    //           console.log(data)
    //         }).catch(err => {
    //           console.error(err)
    //         })
    //         _this.getList()
    //       }
    //     } else {
    //       // 为空则不修改，并返回oldhtml(这个地方后面要绑定你的model对象)
    //       element.innerHTML = oldhtml
    //     }
    //     // console.log(element.innerHTML)
    //     console.log(id)     // 通过id发送post请求
    //   }
    // },

    // 更新表格
    updateList () {
      var params = Object.assign({}, this.searchParams)
      params.page_no = this.pageNo
      params.page_size = this.pageSize
      API_TEACHING.searchCourse(params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.course_list
          this.totalAll = res.data.course_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '获取视频列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.log(err)
      })
    },

    onSearch () {
      this.pageNo = 1
      this.searchParams = this.formInline
      let searchParams = this.filterParams(this.searchParams)
      API_TEACHING.searchCourse(searchParams).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.course_list
          this.totalAll = res.data.course_cnt
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
    // 获取课程体系分类
    getCourseSystem () {
      API_TEACHING.getCourseSystemListOfAll().then(res => {
        this.courseSystemType = res.data.course_system_list
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取服务
    getService () {
      API_TEACHING.getServiceList().then(res => {
        this.serviceType = res.data
      }).catch(err => {
        console.error(err)
      })
    },

    // 跳转到视频管理页面
    toVideoManage (code) {
      this.$router.push({name: '视频管理', params: {'code': code}})
    },

    // check courseCode unique
    checkCodeUnique (rule, value, callback) {
      API_TEACHING.checkCourseCodeUnique(value).then(res => {
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
    editCheckCodeUnique (rule, value, callback) {
      API_TEACHING.checkCourseCodeUnique(value).then(res => {
        if (res.data.check_res.length > 1) {
          callback(new Error('课程code重复,请重新输入'))
        } else if (res.data.check_res.length === 1 && res.data.check_res[0].id !== parseInt(this.editForm.course_id)) {
          callback(new Error('课程code重复,请重新输入'))
        } else {
          callback()
        }
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
        course_system_code: '',
        description: '',
        full_text_description: '',
        service_code: '',
        sort_no: ''
      }
      // 清空input框验证状态
      setTimeout(() => {
        this.$refs.addForm.clearValidate()
        this.$refs.courseAddEditor.clear()
      }, 100)
    },

    addSubmit () {
      let _this = this
      this.addForm.full_text_description = _this.$refs.courseAddEditor.setContent()
      this.$refs.addForm.validate((valid) => {
        if (valid) {
          API_TEACHING.addCourse(_this.addForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: '新增成功', duration: 2000})
              _this.updateList()
            } else if (data.code === 110004) {
              _this.$message.error({showClose: true, message: '课程已经存在', duration: 2000})
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

    showEditDialog (id, controlId, courseCode) {
      let courseId = String(id)
      this.editVisible = true
      // 请求一个当前id
      API_TEACHING.findCourse(id, controlId, courseCode).then(res => {
        this.editForm = {
          course_id: courseId,
          name: res.data.one_course_info.name,
          code: res.data.one_course_info.code,
          course_system_code: res.data.one_course_info.course_system_code,
          description: res.data.one_course_info.description,
          full_text_description: res.data.one_course_info.full_text_description,
          service_code: res.data.one_course_info.content_guard_service_code,
          content_guard_id: res.data.one_course_info.content_guard_id
        }
        setTimeout(() => {
          this.$refs.editForm.clearValidate()
          this.$refs.courseEditEditor.clear()
          this.$refs.courseEditEditor.getContent(this.editForm.full_text_description)
        }, 100)
      })
    },

    editSubmit () {
      let _this = this
      this.editForm.full_text_description = _this.$refs.courseEditEditor.setContent()
      this.$refs.editForm.validate((valid) => {
        if (valid) {
          API_TEACHING.updateCourse(_this.editForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: '编辑成功', duration: 2000})
              this.updateList()
            } else if (data.code === 110004) {
              _this.$message.error({showClose: true, message: '课程已经存在', duration: 2000})
            } else if (data.code === 110006) {
              _this.$message.error({showClose: true, message: '课程已经被删除', duration: 2000})
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

    delCourse (id, courseCode) {
      this.$confirm('是否确定删除该条记录?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        API_TEACHING.removeCourse(id, courseCode).then(res => {
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
