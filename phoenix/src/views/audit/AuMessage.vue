<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>审批管理</el-breadcrumb-item>
        <el-breadcrumb-item>私信聊天申请</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu"> 
      <el-row class="top-tabs">
        <el-tabs v-model="activeName" @tab-click="handleClick">
          <el-tab-pane label="待审批" name="first"></el-tab-pane>
          <el-tab-pane label="审批成功" name="second"></el-tab-pane>
          <el-tab-pane label="审批失败" name="third"></el-tab-pane>
          <el-tab-pane label="不达标取消" name="fourth"></el-tab-pane>
        </el-tabs> 
      </el-row>    
      <el-form :inline="true" :model="formInline">
        
        <el-row>
          <el-form-item label="客户姓名" prop="name"> 
            <el-input v-model="formInline.customer_name" placeholder="请输入"></el-input>  
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
      <!-- 用户列表 -->
      <el-table
        :data="tablePageData"
        stripe
        style="width: 100%">
        <el-table-column prop="customer_name" label="客户姓名" width="166">
          <template slot-scope="scope">
            <el-popover trigger="hover" placement="bottom">
              <p>姓名: {{ scope.row.customer_name }}</p>
              <p>关注动态: {{ scope.row.twitterNames }}</p>
              <p>聊天私信: {{ scope.row.pmNames }}</p>
              <p>最后入金时间: {{ scope.row.money_date }}</p>
              <p>净入金: {{ scope.row.net_proceeds }}</p>
              <el-tag slot="reference" size="medium">{{ scope.row.customer_name}}</el-tag>
            </el-popover>
            <img style="position: absolute; margin: 2px;" v-if="activeName ==='first' && scope.row.review_status === 1" src="../../assets/images/review_refuse.png" >
          </template>
        </el-table-column>
        <el-table-column prop="teacher.category_name" label="申请栏目"></el-table-column>
        <el-table-column prop="teacher.name" label="申请老师"></el-table-column>
        <el-table-column prop="created_at" label="申请提交时间" width="180" :formatter="formatData"></el-table-column>
        <el-table-column prop="status" label="审批状态" :formatter="auditState" v-if="activeName!=='first'" key="status"></el-table-column>
        <el-table-column prop="operator_user_name" label="审批人员" v-if="activeName!=='first'" key="name"></el-table-column>
        <el-table-column prop="updated_at" label="审批时间" v-if="activeName!=='first'" key="at" :formatter="formatData"></el-table-column>
        <el-table-column align="center" label="操作" width="100" v-if="activeName==='first'">
          <template slot-scope="scope">
            <el-button  @click.native="audit(scope.row.id)" type="text" size="small">通过</el-button>
            <el-button  @click.native="unaudit(scope.row.id)" type="text" size="small">拒绝</el-button>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>
  </div> 
</template>

<script>
import API_CONTENT from '../../http/api_content'
import Pagination from '@/components/Pagination'

export default {
  name: 'AuMessage',
  data () {
    return {
      activeName: 'first',
      // 搜索区表单
      formInline: {customer_name: '', status: 0},

      // 缓存搜索数据
      searchParams: {customer_name: '', status: 0},

      totalAll: 0,          // 列表总数目
      pageSize: 10,         // 分页显示数目
      pageNo: 1,            // 当前分页
      pageRefresh: true,    // 分页内容刷新

      tablePageData: []     // 分页显示数据
    }
  },
  components: {
    Pagination
  },
  mounted: function () {
    this.getList()
  },
  methods: {
    handleClick (tab, event) {
      if (tab.index === '1') {
        this.activeName = 'second'
        this.formInline.status = 1
      } else if (tab.index === '2') {
        this.activeName = 'third'
        this.formInline.status = 2
      } else if (tab.index === '3') {
        this.activeName = 'fourth'
        this.formInline.status = 3
      } else {
        this.activeName = 'first'
        this.formInline.status = 0
      }
      this.onSearch()
    },

    initPagination () {
      this.pageRefresh = false
      this.$nextTick(() => { this.pageRefresh = true })
    },

    gotoPage (page) {
      this.pageNo = page
      this.getList()
    },

    // 获取列表内容
    getList () {
      var params = Object.assign({}, this.searchParams)
      params.page_no = this.pageNo
      params.page_size = this.pageSize
      API_CONTENT.getMessageRequestOfPaging(params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.private_message_request_list
          this.totalAll = res.data.private_message_request_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '获取私信申请列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    putMessageRequest (id) {
      API_CONTENT.putMessageRequest(id).then(res => {
        console.log(res.data)
      }).catch(err => {
        console.error(err)
      })
    },

    // 更新表格
    updateList () {
      this.getList()
    },

    onSearch () {
      this.pageNo = 1
      this.searchParams = this.formInline
      let searchParams = this.filterParams(this.searchParams)
      API_CONTENT.getMessageRequestOfPaging(searchParams).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.private_message_request_list
          this.totalAll = res.data.private_message_request_cnt
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

    // 添加审核
    showAddDialog () {
      this.addVisible = true
      this.addForm = {
        name: '',
        checkList: []
      }
      setTimeout(() => {
        this.$refs.addForm.clearValidate()
      }, 100)
    },

    addSubmit () {
      let _this = this
      this.$refs.addForm.validate((valid) => {
        if (valid) {
          _this.$message('已提交')
        }
      })
    },

    // 通过审核
    audit (id) {
      API_CONTENT.putMessageRequest(id, {'operate': 1}).then(res => {
        this.$message.success({showClose: true, message: '审核已通过', duration: 2000})
        this.getList()
      }).catch(err => {
        this.$message.error({showClose: true, message: '网络连接错误，审核失败', duration: 2000})
        console.error(err)
      })
    },

    // 拒绝审核
    unaudit (id) {
      API_CONTENT.putMessageRequest(id, {'operate': 2}).then(res => {
        this.$message.warning({showClose: true, message: '审核已拒绝', duration: 2000})
        this.getList()
      }).catch(err => {
        this.$message.error({showClose: true, message: '网络连接错误，审核失败', duration: 2000})
        console.error(err)
      })
    },

    getCustomerCardInfo () {
      if (this.tablePageData.length > 0) {
        this.tablePageData.forEach(d => {
          API_CONTENT.getCustomerCardInfo(d.open_id).then(res => {
            // console.log(res)
            let cardData = res.data.customer_info_card
            let pmNames = ''
            let twitterNames = ''
            if (cardData.approved_pm_guard_list.length > 0) {
              cardData.approved_pm_guard_list.forEach(pm => {
                pmNames += pm.name + ' '
              })
            }
            if (cardData.approved_twitter_guard_list.length > 0) {
              cardData.approved_twitter_guard_list.forEach(twitter => {
                twitterNames += twitter.name + ' '
              })
            }
            this.$set(d, 'pmNames', pmNames)
            this.$set(d, 'twitterNames', twitterNames)
            this.$set(d, 'mobile', cardData.mobile)
            this.$set(d, 'money_date', cardData.money_date)
            this.$set(d, 'money_total', cardData.money_total)
            this.$set(d, 'net_proceeds', cardData.net_proceeds)
          }).catch(err => {
            console.error(err)
          })
        })
      }
    },

    // 日期时间
    formatData (row, column, value) {
      let newVal = value.substring(0, 16)
      return newVal
    },

    // 审批状态
    auditState (row) {
      let newStr = row.status === 0 ? '待审批' : row.status === 1 ? '审批成功' : '审批失败'
      return newStr
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
