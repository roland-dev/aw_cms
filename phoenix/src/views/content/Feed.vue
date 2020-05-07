<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>内容管理</el-breadcrumb-item>
        <el-breadcrumb-item>推送记录管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu">
      <el-form :inline="true" :model="searchParams">
        <el-row>
          <el-form-item label="日期">
            <el-date-picker v-model="searchParams.date"
                            align="right"
                            type="date"
                            value-format="yyyy-MM-dd"
                            format="yyyy-MM-dd"
                            placeholder="选择日期"
                            :clearable="false">
            </el-date-picker>
          </el-form-item>
          <el-form-item label="推送内容类型">
            <el-select v-model="searchParams.feed_type" clearable placeholder="全部">
              <el-option v-for="item in feedTypes" :value-key="item.id" :key="item.id" :label="item.name" :value="item.id"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item>
            <el-button type="primary" icon="el-icon-search" @click="onSearch"
                        @Keydown.13="keySearch($event)" class="search" round>查询
            </el-button>
          </el-form-item>
        </el-row>
      </el-form>
    </el-row>
    
    <!-- 列表 -->
    <el-row class="table-menu">
      <!-- 推送记录列表 -->
      <el-table :data="tablePageData" stripe style="width: 100%">
        <el-table-column prop="title" label="推送消息标题"></el-table-column>
        <el-table-column prop="feed_type_text" label="内容类型"></el-table-column>
        <el-table-column prop="push_status_text" label="app推送状态"></el-table-column>
        <el-table-column prop="push_time" label="推送时间"></el-table-column>
        <el-table-column prop="qywx_status_text" label="企业微信推送状态"></el-table-column>
        <el-table-column prop="qywx_time" label="企业微信推送时间"></el-table-column>
      </el-table>
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'
import HTTP from '../../http/api_content'

export default {
  name: 'Feed',
  data () {
    return {
      totalAll: 0,          // 列表总数目
      pageNo: 1,            // 当前页
      pageSize: 10,         // 分页显示数目
      tablePageData: [],    // 分页显示数据
      pageRefresh: true,    // 分页内容刷新

      // 搜索框
      searchParams: {
        date: this.getNowFormatDate(),
        feed_type: ''
      },
      feedTypes: []
    }
  },
  components: {
    Pagination
  },
  created: function () {
    this.getFeedTypes()
    this.search()
  },
  methods: {
    getFeedTypes () {
      HTTP.getFeedTypeList().then(res => {
        if (res.code === 0) {
          this.feedTypes = res.data.feed_type_list
        } else {
          this.feedTypes = []
          console.error(res.msg)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    search () {
      let params = Object.assign({}, this.searchParams)
      params.page_no = this.pageNo
      params.page_size = this.pageSize
      HTTP.searchFeedList(params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.feed_list
        } else {
          console.error(res.msg)
        }
      }).catch(err => {
        console.error(err)
      })
    },
    onSearch () {
      this.pageNo = 1
      this.search()
    },
    keySearch (ev) {
      this.onSearch()
    },
    initPagination () {
      this.pageRefresh = false
      this.$nextTick(() => { this.pageRefresh = true })
    },
    gotoPage (page) {
      this.pageNo = page
      this.search()
    },

    // ------------------------ DateFormat ----------------------------
    getNowFormatDate () {
      let date = new Date()
      let seperator1 = '-'
      let year = date.getFullYear()
      let month = date.getMonth() + 1
      let strDate = date.getDate()
      if (month >= 1 && month <= 9) {
        month = '0' + month
      }
      if (strDate >= 0 && strDate <= 9) {
        strDate = '0' + strDate
      }
      let currentdate = year + seperator1 + month + seperator1 + strDate
      return currentdate
    }
  }
}
</script>

