<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>内容管理</el-breadcrumb-item>
        <el-breadcrumb-item>评论管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu">
      <el-row class="top-tabs">
        <el-tabs v-model="activeName" @tab-click="handleClick">
          <el-tab-pane label="待处理" name="first"></el-tab-pane>
          <el-tab-pane label="审核成功" name="second"></el-tab-pane>
          <el-tab-pane label="审核失败" name="third"></el-tab-pane>
        </el-tabs> 
      </el-row>    
      <el-form :inline="true" :model="formInline">
        <el-row>
          <el-form-item label="牛人" prop="article_author_user_id"> 
            <el-select v-model="formInline.article_author_user_id" clearable placeholder="全部">
              <el-option v-for="item in searchTeacherList" :value-key="item.id" :key="item.id" :label="item.name" :value="item.id"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="内容标题" prop="article_title"> 
            <el-input v-model="formInline.article_title" placeholder="请输入"></el-input>  
          </el-form-item>
          <el-form-item label="类型" prop="article_type"> 
            <el-select v-model="formInline.article_type" clearable placeholder="全部">
              <el-option v-for="item in searchTypeList" :value-key="item.type" :key="item.type" :label="item.name" :value="item.type"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item>
            <el-button type="primary" icon="el-icon-search" @click="onSearch" @keydown.13="KeySearch($event)" class="search" round>查询</el-button>
            <el-button type="primary" @click="toggleSelection(true)" round v-if="activeName==='first'">批量通过</el-button>
            <el-button type="primary" @click="toggleSelection(false)" round v-if="activeName==='first'">批量拒绝</el-button>
          </el-form-item>
        </el-row>
      </el-form>       
    </el-row>

    <!-- 列表 -->
    <el-row class="table-menu">
      <!-- 用户列表 -->
      <el-table
        ref="multipleTable"
        :data="tablePageData"
        stripe
        style="width: 100%"
        @selection-change="handleSelectionChange">
        <el-table-column
          type="selection"
          width="55"
          v-if="activeName==='first'" 
          :key="Math.random()">
        </el-table-column>
        <el-table-column prop="content" label="评论">
          <template slot-scope="scope">
              <div v-html="scope.row.content"></div>
          </template>
        </el-table-column>
        <el-table-column align="center" label="操作" width="130" v-if="activeName==='first'" :key="Math.random()">
          <template slot-scope="scope">
            <el-button  @click.native="audit(scope.row.id)" type="text" size="small">通过</el-button>
            <el-button  @click.native="unaudit(scope.row.id)" type="text" size="small">拒绝</el-button>
            <el-button  @click.native="replyDialog(scope.row)" type="text" size="small" v-if="scope.row.is_auth">回复</el-button>
          </template>
        </el-table-column>
        <el-table-column prop="customer_name" label="客户昵称"></el-table-column>
        <el-table-column prop="created_at" label="提交时间" :formatter="formatData" :key="Math.random()"></el-table-column>
        <el-table-column prop="article_title" label="内容标题" :key="Math.random()"></el-table-column>
        <el-table-column prop="article_author_name" label="牛人" :key="Math.random()"></el-table-column>
        <el-table-column prop="type" label="类型" :key="Math.random()"></el-table-column>
        <el-table-column prop="examine_user_name" label="审核人" v-if="activeName !=='first'" :key="Math.random()"></el-table-column>
        <el-table-column prop="updated_at" label="审核时间" :formatter="formatData" v-if="activeName !=='first'" :key="Math.random()"></el-table-column>
        <el-table-column fixed="right" align="center" label="操作" width="120" v-if="activeName !=='first'" :key="Math.random()">
          <template slot-scope="scope">
            <el-button  @click.native="audit(scope.row.id)" type="text" size="small" v-if="activeName ==='third'">复审通过</el-button>
            <el-button  @click.native="unaudit(scope.row.id)" type="text" size="small" v-if="activeName ==='second'">复审拒绝</el-button>
            <el-button  @click.native="replyDialog(scope.row)" type="text" size="small" v-if="scope.row.is_auth">回复</el-button>
          </template>
        </el-table-column>
      </el-table>
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <el-dialog title="回复" :visible.sync="replyVisible" :close-on-click-modal="false" center>
      <el-form :model="replyForm" label-width="100px" :rules="replyFormRules" ref="replyForm">
        <el-row>
            <el-form-item label="评论内容" class="limit-content">
              {{replyForm.ref_content}}
            </el-form-item>
        </el-row>
        <el-row>
            <el-form-item label="回复内容" prop="content">
              <el-input type="textarea" v-model="replyForm.content" placeholder="请输入回复内容" :maxlength="255" :rows="5"></el-input>
            </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="全员可见">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              active-color="#13ce66"
              inactive-color="#999"
              v-model="replyForm.is_all_visible"
            >
            </el-switch>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="replySubmit" :loading="replyLoading">确定</el-button>
        <el-button @click.native="replyVisible = false">取消</el-button>
      </div>
    </el-dialog>
  </div> 
</template>

<script>
import Pagination from '@/components/Pagination'
import API_CONTENT from '../../http/api_content'

export default {
  name: 'Comment',
  data () {
    return {
      activeName: 'first',
      // 搜索区表单
      formInline: {article_author_user_id: '', article_title: '', article_type: '', status: 10},

      // 缓存搜索数据
      searchParams: {article_author_user_id: '', article_title: '', article_type: '', status: 10},

      // 表格内容
      tablePageData: [],         // 分页显示数据
      searchTeacherList: [],     // 显示牛人列表
      searchTypeList: [],        // 评论所属列表类型

      // 分页初始化
      totalAll: 0,               // 列表总数目
      pageSize: 10,              // 分页尺寸
      pageNo: 1,                 // 当前页
      pageRefresh: true,         // 分页内容刷新

       // 回复
      replyVisible: false,
      replyLoading: false,
      replyFormRules: {
        content: [
          {required: true, message: '请输入回复内容', trigger: 'blur'}
        ]
      },
      replyForm: {
        type: '',
        article_id: '',
        content: '',
        ref_id: '',
        ref_content: '',
        ref_open_id: '',
        article_title: '',
        article_author_user_id: '',
        is_all_visible: 1
      },
      multipleSelection: []      // 多选数组
    }
  },
  components: {
    Pagination                   // 分页模块
  },
  mounted: function () {
    this.getTeacherList()
    this.getTypeList()
    if (this.$route.params.title) {
      this.formInline.article_author_user_id = parseInt(this.$route.params.article_author_user_id)
      this.formInline.article_type = this.$route.params.type
      this.formInline.article_title = this.$route.params.title
    }
    this.onSearch()
  },
  methods: {
    // tab切换
    handleClick (tab, event) {
      if (tab.index === '1') {
        this.activeName = 'second'
        this.formInline.status = 20
      } else if (tab.index === '2') {
        this.activeName = 'third'
        this.formInline.status = 30
      } else {
        this.activeName = 'first'
        this.formInline.status = 10
      }
      this.pageNo = 1
      this.searchParams.status = this.formInline.status
      this.getList()
      this.initPagination()
    },

    // 获取每页列表内容
    getList () {
      var params = Object.assign({}, this.searchParams)
      params.page_no = this.pageNo
      params.page_size = this.pageSize
      API_CONTENT.getReplyList(params).then(res => {
        this.tablePageData = res.data.reply_list
        this.totalAll = res.data.reply_cnt
        this.initPagination()
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取牛人列表
    getTeacherList () {
      API_CONTENT.getReplyTeacherList().then(res => {
        if (res.data.teacher_list) {
          this.searchTeacherList = res.data.teacher_list
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取内容类型列表
    getTypeList () {
      API_CONTENT.getContentTypeList().then(res => {
        if (res.data.content_type_list) {
          this.searchTypeList = res.data.content_type_list
        }
      }).catch(err => {
        console.error(err)
      })
    },

    toggleSelection (isSelect) {
      if (this.multipleSelection.length === 0) {
        this.$message.warning({showClose: true, message: '未选中任何评论', duration: 2000})
        return false
      }
      let allSelectArr = []
      this.multipleSelection.forEach(d => {
        allSelectArr.push(d.id)
      })
      if (isSelect) {
        this.auditgroup(allSelectArr)
        console.log(this.multipleSelection)
        // rows.forEach(row => {
        //   this.$refs.multipleTable.toggleRowSelection(row)
        // })
      } else {
        this.unauditgroup(allSelectArr)
        console.log(this.multipleSelection)
        // this.$refs.multipleTable.clearSelection()
      }
    },

    // 回复
    replyDialog (reply) {
      this.replyVisible = true
      this.replyForm = {
        type: reply.type,
        article_id: reply.article_id,
        content: '',
        ref_id: reply.id,
        ref_content: reply.content,
        ref_open_id: reply.open_id,
        article_title: reply.article_title,
        article_author_user_id: reply.article_author_user_id,
        is_all_visible: reply.is_all_visible
      }

      // 清空input框验证状态
      setTimeout(() => {
        this.$refs.replyForm.clearValidate()
      }, 100)
    },

    replySubmit () {
      let _this = this
      this.$refs.replyForm.validate((valid) => {
        if (valid) {
          _this.replyLoading = true
          _this.replyForm.content = this.replyForm.content
          API_CONTENT.reply(_this.replyForm).then(res => {
            if (res.code === 0) {
              _this.$message.success({showClose: true, message: '回复成功', duration: 2000})
              _this.getList()
              _this.replyVisible = false
            } else {
              _this.$message.error({showClose: true, message: '回复失败：' + res.msg, duration: 2000})
            }
            _this.replyLoading = false
          }).catch(err => {
            console.error(err)
            _this.replyLoading = false
          })
        }
      })
    },

    // 多选
    handleSelectionChange (val) {
      this.multipleSelection = val
    },

    onSearch () {
      this.pageNo = 1
      this.searchParams = this.formInline
      this.getList()
    },

    KeySearch (ev) {
      this.searchParams = this.formInline
      this.getList()
    },

    // 通过审核
    audit (id) {
      let params = {'reply_id': id, 'operate': 20}
      API_CONTENT.putReplyRequest(params).then(res => {
        this.$message.success({showClose: true, message: '审核已通过', duration: 2000})
        this.getList()
      }).catch(err => {
        this.$message.error({showClose: true, message: '网络连接错误，审核失败', duration: 2000})
        console.error(err)
      })
    },

    // 拒绝审核
    unaudit (id) {
      let params = {'reply_id': id, 'operate': 30}
      API_CONTENT.putReplyRequest(params).then(res => {
        this.$message.warning({showClose: true, message: '审核已拒绝', duration: 2000})
        this.getList()
      }).catch(err => {
        this.$message.error({showClose: true, message: '网络连接错误，审核失败', duration: 2000})
        console.error(err)
      })
    },

    // 批量通过审核
    auditgroup (idArr) {
      let params = {'reply_id_list': idArr, 'operate': 20}
      API_CONTENT.putReplyRequests(params).then(res => {
        this.$message.success({showClose: true, message: '批量审核已通过', duration: 2000})
        this.getList()
      }).catch(err => {
        this.$message.error({showClose: true, message: '网络连接错误，审核失败', duration: 2000})
        console.error(err)
      })
    },

    // 批量拒绝审核
    unauditgroup (idArr) {
      let params = {'reply_id_list': idArr, 'operate': 30}
      API_CONTENT.putReplyRequests(params).then(res => {
        this.$message.warning({showClose: true, message: '批量审核已拒绝', duration: 2000})
        this.getList()
      }).catch(err => {
        this.$message.error({showClose: true, message: '网络连接错误，审核失败', duration: 2000})
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
      this.getList()
    },

    // 日期时间
    formatData (row, column, value) {
      return value.substring(0, 16)
    }
  }
}
</script>

<style scoped lang="less"> 
.ellipsis {
  width:calc(100% - 20px);  
  display: inline-block;
  overflow: hidden;    
  text-overflow:ellipsis;    
  white-space: nowrap;
}
</style>
