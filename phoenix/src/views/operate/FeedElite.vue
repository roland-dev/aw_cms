<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>运营管理</el-breadcrumb-item>
        <el-breadcrumb-item>内容精选</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu">   
      <el-form :inline="true" :model="formInline">
        <el-row>
          <el-form-item label="内容主题" prop="name"> 
            <el-input v-model="formInline.title" placeholder="请输入"></el-input>  
          </el-form-item>
          <el-form-item label="归属栏目">
            <el-select v-model="formInline.category_code" clearable placeholder="请选择">
              <el-option v-for="item in searchCategoryList" :value-key="item.code" :key="item.code" :label="item.name" :value="item.code"></el-option>
            </el-select>
          </el-form-item>  
           <el-form-item label="归属老师">
            <el-select v-model="formInline.owner_id" clearable placeholder="请选择">
              <el-option v-for="item in searchTeacherList" :value-key="item.enterprise_userid" :key="item.enterprise_userid" :label="item.name" :value="item.enterprise_userid"></el-option>
            </el-select>
          </el-form-item>  
          <el-form-item label="精选状态">
            <el-select v-model="formInline.elite" clearable placeholder="是否精选">
              <el-option :value="1" label="是"></el-option>
              <el-option :value="0" label="否"></el-option>
            </el-select>
          </el-form-item>  
          <el-form-item label="开始时间">
            <el-date-picker
              v-model="formInline.begin_time"
              align="right"
              type="date"
              value-format="yyyy-MM-dd"
              format="yyyy-MM-dd"
              placeholder="选择日期">
            </el-date-picker>
          </el-form-item>
          <el-form-item label="结束时间">
            <el-date-picker
              v-model="formInline.end_time"
              align="right"
              type="date"
              value-format="yyyy-MM-dd"
              format="yyyy-MM-dd"
              placeholder="选择日期">
            </el-date-picker>
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
        <el-table-column label="内容主题">
          <template slot-scope="scope">
            <el-popover
              placement="right"
              title=""
              trigger="click">
              <img :src="scope.row.thumb_cdn_url" alt="" v-if="scope.row.msg_type ==='message' && scope.row.thumb_cdn_url">
              <img slot="reference" :src="scope.row.thumb_cdn_url" alt="" v-if="scope.row.msg_type ==='message' && scope.row.thumb_cdn_url">
            </el-popover>
            <span class="table_title" @click="showStrategyDialog(scope.$index)">{{scope.row.title || scope.row.summary}}</span>
          </template>
        </el-table-column>
        <el-table-column label="精选状态" width="100">
          <template slot-scope="scope">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              active-color="#13ce66"
              inactive-color="#999"
              v-model="scope.row.is_elite"
              @change="changeElite(scope.row)">
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="可免费查看" width="125">
          <template slot-scope="scope">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              active-color="#13ce66"
              inactive-color="#999"
              v-model="scope.row.bypass"
              v-if="scope.row.feed_type===11"
              @change="changeByPass(scope.row)">
            </el-switch>
          </template>
        </el-table-column> 

        <el-table-column prop="category_name" label="归属栏目" width="160"></el-table-column>
        <el-table-column prop="feed_owner" label="相关老师" width="100"></el-table-column>

        <el-table-column prop="push_time" label="推送时间" :formatter="formatData" width="160"></el-table-column>
        <el-table-column align="center" label="操作" width="100">
          <template slot-scope="scope">
            <div v-if="scope.row.feed_type === 2 || scope.row.feed_type === 4 || scope.row.feed_type === 11 || scope.row.feed_type === 12">
              <el-button @click.native="showDelContactDialog(scope.row.feed_id)" type="text" size="small" >删除</el-button>
            </div>
            <div v-else>
              <el-button @click.native="showDelDialog(scope.row.feed_id)" type="text" size="small" >删除</el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <div class="block" v-if="totalAll > pageSize">
        <el-pagination
          @current-change="handleCurrentChange"
          :page-size="pageSize"
          layout="prev, pager, next"
          :total="totalAll">
        </el-pagination>
      </div>
    </el-row>
    <!-- 查看海报 -->
    <el-dialog :title="strategyTitle" :visible.sync ="strategyVisible" :close-on-click-modal="false" center class="mt5">
      <el-row style="min-height: 600px;">
       <iframe :src ="strategyUrl" width="100%" height="600px" class="iframe-content" frameborder="no" border="0"></iframe>
      </el-row>
    </el-dialog>

    <!-- 关联删除feed表内容 -->
    <el-dialog title="提示" :visible.sync="delVisible" custom-class="del-contact" top="40vh">
      <i class="el-icon-warning el-message-box__status"></i><span style="display: inline-block; padding-left: 2.5em; height: 16px; line-height: 25px;">是否关联删除原始记录</span> 
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="delContact(1)">是</el-button>
        <el-button @click="delContact(0)">否</el-button>
        <el-button @click="delVisible = false">取消</el-button>
      </span>
    </el-dialog>
  </div> 
  
</template>

<script>
import API_OPERATE from '../../http/api_operate'
import API_CONTENT from '../../http/api_content'
import API_RESOURCE from '../../http/api_resource'

export default {
  name: 'FeedElite',
  data () {
    return {
      // 搜索区表单
      formInline: {title: '', category_code: '', owner_id: '', elite: '', begin_time: '', end_time: '', page_no: 1, page_size: 20},

      // 搜索条件列表
      searchCategoryList: [],
      searchTeacherList: [],
      searchParams: {page_no: 1, page_size: 20},        // 搜索条件

      // 查看feed
      strategyVisible: false, // 是否显示
      strategyLoading: false,
      strategyTitle: '',
      strategyUrl: '',

      // 删除feed
      delVisible: false, // 是否显示
      delLoading: false,
      delId: 0,

      // // 日期区间限制
      // beginOptions: {
      //   disabledDate: (time) => {
      //     let curDate = (new Date(this.formInline.end_time)).getTime()
      //     let one = 30 * 24 * 3600 * 1000
      //     let oneMonth = curDate - one
      //     return time.getTime() > Date.now() || time.getTime() < oneMonth
      //   }
      // },
      // endOptions: {
      //   disabledDate: (time) => {
      //     return time.getTime() > Date.now() - 8.64e6
      //   }
      // },
      totalAll: 0,          // 列表总数目
      pageSize: 20,         // 分页显示数目
      tableData: [],        // 列表总数据
      tablePageData: []    // 分页显示数据
    }
  },
  mounted: function () {
    this.getCategoryList()
    this.getTeacherList()
    this.getInitDate()
    this.getList()
  },
  methods: {
    // 获取内容精选列表
    getList () {
      API_OPERATE.getFeedList(this.removeEmpty(this.searchParams)).then(res => {
        this.tableData = res.data.feed_list
        this.totalAll = res.data.feed_list_total_count
        this.initPageTable()
      }).catch(err => {
        console.error(err)
      })
    },

    getPageList () {
      API_OPERATE.getFeedList(this.removeEmpty(this.searchParams)).then(res => {
        this.tableData = res.data.feed_list
      }).catch(err => {
        console.error(err)
      })
    },

    onSearch () {
      let begin = new Date(this.formInline.begin_time)
      let end = new Date(this.formInline.end_time)
      if (!this.formInline.begin_time || !this.formInline.end_time) {
        this.$message.warning({showClose: true, message: '搜索条件中时间不可以为空,只可以搜索一个月内的内容', duration: 4000})
      } else if (end - begin > 30 * 24 * 3600 * 1000) {
        this.$message.warning({showClose: true, message: '只可以搜索一个月内的内容', duration: 4000})
      } else {
        this.searchParams = this.formInline
        this.searchParams.page_no = 1
        API_OPERATE.getFeedList(this.removeEmpty(this.searchParams)).then(res => {
          this.tableData = res.data.feed_list
          this.totalAll = res.data.feed_list_total_count
          this.initPageTable()
        }).catch(err => {
          console.error(err)
        })
      }
    },

    KeySearch (ev) {
      this.onSearch()
    },

    // 改变精选状态
    changeElite (row) {
      // 精选状态取反并发送请求
      let eliteStatus = row.is_elite === 1 ? 1 : 0
      API_OPERATE.putFeed(row.feed_id, {operate: eliteStatus}).then(data => {
        if (data.code === 0) { console.log('精选状态改变') }
      }).catch(err => {
        console.error(err)
      })
    },

    // 改变可免费查看状态
    changeByPass (row) {
      // 精选状态取反并发送请求
      let byPassStatus = row.bypass === 1 ? 1 : 0
      API_OPERATE.putByPass(row.feed_id, {operate: byPassStatus}).then(data => {
        if (data.code === 0) { console.log('可免费查看改变') }
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取归属栏目
    getCategoryList () {
      API_CONTENT.getAllCategoryList().then(res => {
        this.searchCategoryList = res.data.category_list
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取栏目老师
    getTeacherList () {
      API_RESOURCE.getAuthor().then(res => {
        var oldArr = res.teacherList.teacherList
        var allArr = []
        for (var i = 0; i < oldArr.length; i++) {
          var flag = true
          for (var j = 0; j < allArr.length; j++) {
            if (oldArr[i].name === allArr[j].name) {
              flag = false
            }
          }
          if (flag && oldArr[i].enterprise_userid) {
            allArr.push(oldArr[i])
          }
        }
        this.searchTeacherList = allArr
      }).catch(err => {
        console.log(err)
      })
    },

    // 查看内容 dialog
    showStrategyDialog (index) {
      // 请求展示海报的编辑页
      if (this.tablePageData[index].source_url) {
        this.strategyVisible = true
        this.strategyTitle = this.tablePageData[index].title ? this.tablePageData[index].title : this.tablePageData[index].summary
        this.strategyUrl = this.tablePageData[index].source_url
      } else {
        this.$message.warning({showClose: true, message: '该条内容还未有详情窗口。', duration: 2000})
      }
    },

    // 删除
    showDelDialog (id) {
      this.$confirm('是否确定删除该内容?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        API_OPERATE.delFeed(id, 0).then(data => {
          if (data.code === 0) {
            this.$message.success({showClose: true, message: '删除成功', duration: 2000})
            this.onSearch()
          } else {
            let msg = data.msg ? data.msg : '只能删除24小时发表的内容！'
            this.$message.warning({showClose: true, message: msg, duration: 2000})
          }
        })
      }).catch(() => {
      })
    },

    // 询问是否关联删除
    showDelContactDialog (id) {
      console.log(id)
      this.delVisible = true
      this.delId = id
    },

    delContact (contact) {
      API_OPERATE.delFeed(this.delId, contact).then(data => {
        if (data.code === 0) {
          this.$message.success({showClose: true, message: '删除成功', duration: 2000})
          this.delVisible = false
          this.onSearch()
        } else {
          let msg = data.msg ? data.msg : '只能删除24小时发表的内容！'
          this.$message.warning({showClose: true, message: msg, duration: 2000})
        }
      })
    },

    // 格式化分页数据
    initPageTable () {
      this.tablePageData = this.tableData
      let pager = document.getElementsByClassName('el-pager')[0]
      if (pager) {
        pager.getElementsByTagName('li')[0].click()
      }
    },

    // 设置表格分页页面
    handleCurrentChange (page) {
      this.searchParams.page_no = page
      API_OPERATE.getFeedList(this.removeEmpty(this.searchParams)).then(res => {
        this.tablePageData = res.data.feed_list
      }).catch(err => {
        console.error(err)
      })
    },

    // 日期时间
    formatData (row) {
      if (!row.push_time) {
        row.push_time = row.add_time
      }
      let newVal = row.push_time.substring(0, 16)
      return newVal
    },

    // 修改title字段优化范围

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

    getInitDate () {
      this.formInline.begin_time = this.getWeekAgeDate()
      this.formInline.end_time = this.getNowDate()
      this.searchParams.begin_time = this.formInline.begin_time
      this.searchParams.end_time = this.formInline.end_time
    },

    getNowDate () {
      let date = new Date()  // Tue Jul 16 01:07:00 CST 2013的时间对象
      let year = date.getFullYear()  // 年
      let month = date.getMonth() + 1  // 月份（月份是从0~11，所以显示时要加1）
      month = month < 10 ? '0' + month : month
      let day = date.getDate()  // 日期
      day = day < 10 ? '0' + day : day
      return year + '-' + month + '-' + day
    },

    // 获取一周前的日期
    getWeekAgeDate () {
      let timestamp = new Date().getTime()
      let WeekAgoTimestamp = new Date(timestamp - 7 * 24 * 60 * 60 * 1000)
      let year = WeekAgoTimestamp.getFullYear()  // 年
      let month = WeekAgoTimestamp.getMonth() + 1  // 月份（月份是从0~11，所以显示时要加1）
      month = month < 10 ? '0' + month : month
      let day = WeekAgoTimestamp.getDate()  // 日期
      day = day < 10 ? '0' + day : day
      return year + '-' + month + '-' + day
    }
  }
}
</script>

<style scoped>
.del-contact .el-dialog__body {
  padding: 10px 15px;
}

.el-date-editor.el-input{
  width: 200px;
}

.table-menu img {
  max-width: 100%;
}
</style>
