<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>审批管理</el-breadcrumb-item>
        <el-breadcrumb-item>动态关注申请</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu"> 
      <!-- 一期不更新此功能，to do... -->
      <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="top-menu-add">添加审批</el-button>
      <el-row class="top-tabs">
        <el-tabs v-model="activeName" @tab-click="handleClick">
          <el-tab-pane label="待处理" name="first"></el-tab-pane>
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
        <el-table-column prop="customer_name" label="客户姓名"  width="166">
          <template slot-scope="scope">
            <el-popover trigger="hover" placement="bottom">
              <p>姓名: {{ scope.row.customer_name }}</p>
              <p>关注动态: {{ scope.row.twitterNames }}</p>
              <p>聊天私信: {{ scope.row.pmNames }}</p>
              <p>最后入金时间: {{ scope.row.money_date }}</p>
              <p>净入金金额: {{ scope.row.net_proceeds }}</p>
              <el-tag slot="reference" size="medium">{{ scope.row.customer_name}}</el-tag>
            </el-popover>
              <img style="position: absolute; margin: 2px;" v-if="activeName ==='first' && scope.row.status === 0 && scope.row.review_status === 0" src="../../assets/images/review_adopt.png" >
              <img style="position: absolute; margin: 2px;" v-if="activeName ==='second' && scope.row.status === 1 && scope.row.is_qualified === 0" src="../../assets/images/review_adopt.png" >
              <img style="position: absolute; margin: 2px;" v-if="activeName ==='first' && scope.row.status === 0 && scope.row.review_status === 1" src="../../assets/images/review_refuse.png" >
          </template>
        </el-table-column>
        <el-table-column prop="category_name" label="申请关注动态"></el-table-column>
        <el-table-column prop="created_at" label="申请提交时间" width="180" :formatter="formatData"></el-table-column>
        <el-table-column prop="status" label="审批状态" v-if="activeName!=='first'" :formatter="auditState" key="status"></el-table-column>
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


    <!-- 添加审批 -->
    <el-dialog title="添加审批" :visible.sync ="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="120px" :rules="addFormRules" ref="addForm">
        <el-row>
          <el-form-item label="客户电话号码" prop="mobile">
            <el-input v-model="addForm.mobile" placeholder="请输入" :maxlength="11" @keyup.enter.native="KeySearchCustomerInfo">
              <el-button slot="append" icon="el-icon-search" @click="onSearchCustomerInfo" ></el-button>
            </el-input>
          </el-form-item>
        </el-row>
        <el-row v-if="customerInfo.length > 0">
          <el-form-item>
            <el-card>
              <template>
                <el-table :data="customerInfo" style="width: 100%">
                  <el-table-column prop="name" label="客户姓名"></el-table-column>
                  <el-table-column prop="mobile" label="客户电话号码"></el-table-column>
                  <el-table-column prop="net_proceeds" label="净入金"></el-table-column>
                </el-table>
              </template>
            </el-card>
          </el-form-item>
        </el-row>
        <el-row v-if="customerInfo.length > 0">
          <el-form-item label="申请关注动态" prop="checkList">
            <el-checkbox-group v-model="addForm.checkList">
              <el-checkbox v-for="category in categoryList" :label="category.code" :key="category.code" :disabled="category.disabled">{{category.name}}</el-checkbox>
            </el-checkbox-group>
          </el-form-item>
        </el-row>  
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading" :disabled="customerInfo.length == 0">确定</el-button>
        <el-button @click.native="addVisible = false">取消</el-button>        
      </div>
    </el-dialog>
  </div> 
</template>

<script>
import API_CONTENT from '../../http/api_content'
import Pagination from '@/components/Pagination'

export default {
  name: 'AuTwitter',
  data () {
    return {
      activeName: 'first',
      // 搜索区表单
      formInline: {customer_name: '', status: 0},

      // 缓存搜索数据
      searchParams: {customer_name: '', status: 0},

      // 分页初始化
      totalAll: 0,          // 列表总数目
      pageSize: 10,         // 分页显示数目
      pageNo: 1,            // 当前页码
      pageRefresh: true,    // 分页内容刷新

      tablePageData: [],    // 分页显示数据
      categoryList: [],     // 显示栏目列表
      customerInfo: [],

      // 新增用户
      addVisible: false, // 是否显示
      addLoading: false,
      addFormRules: {
        mobile: [
          {required: true, message: '请输入手机号码', trigger: 'blur'},
          {validator: this.checkMobile, trigger: 'blur'}
        ],
        checkList: [{ required: true, type: 'array', message: '请至少选择一个选项', trigger: 'change' }]
      },
      addForm: {mobile: '', checkList: []}
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
      API_CONTENT.getTwitterRequestOfPaging(params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.twitter_request_list
          this.totalAll = res.data.twitter_request_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '获取动态申请列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    putTwitterRequest (id) {
      API_CONTENT.putTwitterRequest(id).then(res => {
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
      API_CONTENT.getTwitterRequestOfPaging(searchParams).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.twitter_request_list
          this.totalAll = res.data.twitter_request_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '查询失败：' + res.msg, duration: 2000})
        }
      }).error(err => {
        console.error(err)
      })
    },

    KeySearch (ev) {
      this.onSearch()
    },

    checkMobile (rule, value, callback) {
      let reg = /^1\d{10}$/
      if (!reg.test(value)) {
        callback(new Error('请输入格式正确的手机号码'))
      } else {
        callback()
      }
    },

    onSearchCustomerInfo () {
      this.customerInfo = []
      this.$refs.addForm.validateField('mobile', (valid) => {
        if (valid.length === 0) {
          API_CONTENT.getCustomerInfoByMobile(this.addForm.mobile).then(res => {
            if (res.code === 0) {
              if (res.data.customer_info.is_rfzq_user) {
                this.customerInfo = [res.data.customer_info]
                this.categoryList = res.data.category_list
              } else {
                this.$message.error({showClose: true, message: '此客户未在港股开户！', duration: 2000})
              }
            } else {
              this.$message.error({showClose: true, message: '无此用户信息', duration: 2000})
            }
          }).catch(err => {
            console.log(err)
            this.$message.error({showClose: true, message: '服务器错误', duration: 2000})
          })
        }
      })
    },

    KeySearchCustomerInfo (event) {
      this.customerInfo = []
      this.$refs.addForm.validateField('mobile', (valid) => {
        if (valid.length === 0) {
          API_CONTENT.getCustomerInfoByMobile(this.addForm.mobile).then(res => {
            if (res.code === 0) {
              if (res.data.customer_info.is_rfzq_user) {
                this.customerInfo = [res.data.customer_info]
                this.categoryList = res.data.category_list
              } else {
                this.$message.error({showClose: true, message: '此客户未在港股开户！', duration: 2000})
              }
            } else {
              this.$message.error({showClose: true, message: '无此用户信息', duration: 2000})
            }
          }).catch(err => {
            console.log(err)
            this.$message.error({showClose: true, message: '服务器错误', duration: 2000})
          })
        }
      })
    },

    // 添加审核
    showAddDialog () {
      this.addVisible = true
      this.addForm = {
        mobile: '',
        checkList: []
      }
      this.customerInfo = []
      setTimeout(() => {
        this.$refs.addForm.clearValidate()
      }, 100)
    },

    addSubmit () {
      let _this = this
      this.$refs.addForm.validate((valid) => {
        if (valid) {
          this.addLoading = true
          API_CONTENT.addTwitterApproval(_this.addForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: '新增成功', duration: 2000})
              _this.updateList()
              _this.addVisible = false
            } else {
              _this.$message.error({showClose: true, message: data.msg, duration: 2000})
              _this.addVisible = false
            }
            this.addLoading = false
          }).catch(err => {
            console.error(err)
            this.addLoading = false
          })
        }
      })
    },

    // 通过审核
    audit (id) {
      API_CONTENT.putTwitterRequest(id, {'operate': 1}).then(res => {
        this.$message.success({showClose: true, message: '审核已通过', duration: 2000})
        this.getList()
      }).catch(err => {
        this.$message.error({showClose: true, message: '网络连接错误，审核失败', duration: 2000})
        console.error(err)
      })
    },

    // 拒绝审核
    unaudit (id) {
      API_CONTENT.putTwitterRequest(id, {'operate': 2}).then(res => {
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
      let newStr = row.status === 0 ? '待审批'
        : row.status === 1 ? '审批成功'
          : '审批失败'
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

</style>
