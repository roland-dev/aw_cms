<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>直播管理</el-breadcrumb-item>
        <el-breadcrumb-item>直播互动管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <el-row class="top-menu">
      <el-row class="top-tabs">
        <el-tabs v-model="activeName" @tab-click="handleClick">
          <el-tab-pane label="待处理" name="first"></el-tab-pane>
          <el-tab-pane label="审核成功" name="second"></el-tab-pane>
          <el-tab-pane label="审核失败" name="third"></el-tab-pane>
        </el-tabs>
      </el-row>
      <el-form :inline="true" :model="searchParams">
        <el-row>
          <el-form-item label="直播室" prop="live_room_code">
            <el-select v-model="searchParams.live_room_code" clearable placeholder="请选择">
              <el-option v-for="liveRoom in liveRoomList" :value-key="liveRoom.code" :key="liveRoom.code" :label="liveRoom.name" :value="liveRoom.code"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="关联栏目" prop="category_code">
            <el-select v-model="searchParams.category_code" clearable placeholder="请选择">
              <el-option v-for="category in categoryList" :value-key="category.code" :key="category.code" :label="category.name" :value="category.code"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="节目名称" prop="title">
            <el-input v-model="searchParams.title" placeholder="请输入"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="提交开始时间">
            <el-date-picker
                    v-model="searchParams.start_time"
                    align="right"
                    type="datetime"
                    value-format="yyyy-MM-dd HH:mm"
                    format="yyyy-MM-dd HH:mm"
                    placeholder="请选择时间">
            </el-date-picker>
          </el-form-item>
          <el-form-item label="提交结束时间">
            <el-date-picker
                    v-model="searchParams.end_time"
                    align="right"
                    type="datetime"
                    value-format="yyyy-MM-dd HH:mm"
                    format="yyyy-MM-dd HH:mm"
                    placeholder="请选择时间">
            </el-date-picker>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item>
            <el-button type="primary" icon="el-icon-search" @click="onSearch"
                        @keydown.13="KeySearch($event)" class="search" round>查询
            </el-button>
            <el-button type="primary" @click="toggleSelection(true)" round v-if="activeName==='first'">批量通过</el-button>
            <el-button type="primary" @click="toggleSelection(false)" round v-if="activeName==='first'">批量拒绝</el-button>
          </el-form-item>
        </el-row>
      </el-form>
    </el-row>

    <!-- 列表 -->
    <el-row class="table-menu">
      <!-- 节目列表 -->
      <el-table ref="multipleTable"
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
        <el-table-column prop="content" label="互动">
          <template slot-scope="scope">
            <div v-html="scope.row.content"></div>
          </template>
        </el-table-column>
        <el-table-column align="center" label="操作" width="150" v-if="activeName === 'first'" :key="Math.random()">
          <template slot-scope="scope">
            <el-button @click.native="audit(scope.row.id)" type="text" size="small">通过</el-button>
            <el-button @click.native="unaudit(scope.row.id)" type="text" size="small">拒绝</el-button>
            <el-button @click.native="replyDialog(scope.row)" type="text" size="small">回复</el-button>
          </template>
        </el-table-column>
        <el-table-column prop="customer_name" label="客户昵称"></el-table-column>
        <el-table-column prop="created_at" label="提交时间" :formatter="dateFormat"></el-table-column>
        <el-table-column prop="title" label="节目名称"></el-table-column>
        <el-table-column prop="category_name" label="关联栏目"></el-table-column>
        <el-table-column prop="examine_user_name" label="审核人" v-if="activeName !== 'first'" :key="Math.random()"></el-table-column>
        <el-table-column prop="examine_at" label="审核时间" :formatter="dateFormat" v-if="activeName !== 'first'" :key="Math.random()"></el-table-column>
      </el-table>
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <el-dialog width="825px" title="回复互动" :visible.sync="replyVisible" :close-on-click-modal="false" center>
      <el-form :model="replyForm" label-width="100px" :rules="replyFormRules" ref="replyForm">
        <el-row>
          <el-form-item label="互动内容">
            {{replyForm.reply_content}}
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="回复内容" prop="content">
            <el-input type="textarea" v-model="replyForm.content" placeholder="请输入回复内容" :maxlength="255"></el-input>
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
import HTTP from '../../http/api_live'
import API_COLUMN from '../../http/api_column'

export default {
  name: 'Discuss',
  data () {
    return {
      activeName: 'first',

      totalAll: 0,          // 列表总数目
      pageNo: 1,            // 当前页
      pageSize: 10,         // 分页显示数目
      tablePageData: [],    // 分页显示数据
      pageRefresh: true,    // 分页内容刷新

      categoryList: [],     // 栏目列表
      liveRoomList: [],     // 直播室列表

      // 搜索框
      searchParams: {
        status: 10,
        live_room_code: '',
        category_code: '',
        title: ''
      },

      // 默认搜索条件
      initSearchParams: {
        status: 10,
        live_room_code: '',
        category_code: ''
      },

      // 回复
      replyVisible: false,
      replyLoading: false,
      replyFormRules: {
        content: [
          {required: true, message: '请输入回复内容', trigger: 'blur'}
        ]
      },
      replyForm: {
        live_room_code: '',
        talkshow_code: '',
        content: '',
        reply_to_open_id: '',
        reply_to_name: '',
        reply_discuss_id: '',
        reply_content: ''
      },
      multipleSelection: []      // 多选数组
    }
  },
  components: {
    Pagination
  },
  created: function () {
  },
  mounted: function () {
    this.getLiveRoomList()
    this.getColumnList()
    if (this.$route.params.title) {
      this.searchParams.live_room_code = this.$route.params.live_room_code
      this.searchParams.category_code = this.$route.params.category_code
      this.searchParams.title = this.$route.params.title
    }
    this.onSearch()
  },
  methods: {
    // ------------------------------- 公共方法 --------------------------------------
    // 清空对象中为空的属性
    removeEmpty (obj) {
      let newObj = {}
      for (let i in obj) {
        if (obj[i] !== '') {
          newObj[i] = obj[i]
        }
      }
      return newObj
    },
    // ---------------------------- tabs -------------------------------------
    handleClick (tab, event) {
      if (tab.index === '1') {
        this.activeName = 'second'
        this.searchParams.status = 20
      } else if (tab.index === '2') {
        this.activeName = 'third'
        this.searchParams.status = 30
      } else {
        this.activeName = 'first'
        this.searchParams.status = 10
      }
      this.onSearch()
    },

    // ---------------------------- 参数列表 ------------------------------------
    getLiveRoomList () {
      let params = {
        page_no: 1,
        page_size: 1000
      }
      HTTP.getLiveRoomList(params).then(res => {
        if (res.code === 0) {
          this.liveRoomList = res.data.live_room_list
        } else {
          console.log(res.msg)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    getColumnList () {
      API_COLUMN.getCategoryList().then(res => {
        if (res.code === 0) {
          this.categoryList = res.data.category_list
        } else {
          console.log(res.msg)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // ------------------------------ 搜索 ---------------------------------------
    getLiveDiscussList () {
      let params = Object.assign({}, this.searchParams)
      params.page_no = this.pageNo
      params.page_size = this.pageSize
      HTTP.getLiveDiscussList(this.removeEmpty(params)).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.discuss_list
          this.totalAll = res.data.discuss_cnt
          this.initPagination()
        } else {
          console.error(res.msg)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    onSearch () {
      this.pageNo = 1
      this.getLiveDiscussList()
    },

    // 多选
    handleSelectionChange (val) {
      this.multipleSelection = val
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

    KeySearch () {
      this.onSearch()
    },

    // 格式化分页
    initPagination () {
      this.pageRefresh = false
      this.$nextTick(() => { this.pageRefresh = true })
    },

    // 跳转分页
    gotoPage (page) {
      this.pageNo = page
      this.getLiveDiscussList()
    },

    // --------------------------------- dateFormat ---------------------------------------
    dateFormat (row, column) {
      let date = row[column.property]
      if (date === undefined || date === null) {
        return ''
      }
      return date.substr(0, 16)
    },

    // ------------------------------------- 操作 -------------------------------------------
    // 通过审核
    audit (id) {
      let params = {'operate': 20}
      HTTP.putLiveDiscussRequest(id, params).then(res => {
        if (res.code === 0) {
          this.$message.success({showClose: true, message: '审批已通过', duration: 2000})
          this.getLiveDiscussList()
        } else {
          this.$message.error({showClose: true, message: res.msg, duration: 2000})
        }
      }).catch(err => {
        this.$message.error({showClose: true, message: '网络连接错误，审批失败', duration: 2000})
        console.error(err)
      })
    },

    // 拒绝审核
    unaudit (id) {
      let params = {'operate': 30}
      HTTP.putLiveDiscussRequest(id, params).then(res => {
        if (res.code === 0) {
          this.$message.warning({showClose: true, message: '审核已拒绝', duration: 2000})
          this.getLiveDiscussList()
        } else {
          this.$message.error({showClose: true, message: res.msg, duration: 2000})
        }
      }).catch(err => {
        this.$message.error({showClose: true, message: '网络连接错误，审批失败', duration: 2000})
        console.error(err)
      })
    },

    // 批量通过审核
    auditgroup (idArr) {
      let params = {'discuss_id_list': idArr, 'operate': 20}
      HTTP.putLiveDiscussRequests(params).then(res => {
        this.$message.success({showClose: true, message: '批量审核已通过', duration: 2000})
        this.getLiveDiscussList()
      }).catch(err => {
        this.$message.error({showClose: true, message: '网络连接错误，审核失败', duration: 2000})
        console.error(err)
      })
    },

    // 批量拒绝审核
    unauditgroup (idArr) {
      let params = {'discuss_id_list': idArr, 'operate': 30}
      HTTP.putLiveDiscussRequests(params).then(res => {
        this.$message.warning({showClose: true, message: '批量审核已拒绝', duration: 2000})
        this.getLiveDiscussList()
      }).catch(err => {
        this.$message.error({showClose: true, message: '网络连接错误，审核失败', duration: 2000})
        console.error(err)
      })
    },

    // 回复
    replyDialog (talkshow) {
      this.replyVisible = true
      this.replyForm = {
        live_room_code: talkshow.live_room_code,
        talkshow_code: talkshow.talkshow_code,
        content: '',
        reply_to_open_id: talkshow.open_id,
        reply_to_name: talkshow.customer_name,
        reply_discuss_id: talkshow.id,
        reply_content: talkshow.content
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
          _this.replyForm.content = '@' + this.replyForm.reply_to_name + ':' + this.replyForm.content
          HTTP.replyLiveDiscuss(_this.replyForm).then(res => {
            if (res.code === 0) {
              _this.$message.success({showClose: true, message: '回复成功', duration: 2000})
              _this.getLiveDiscussList()
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

