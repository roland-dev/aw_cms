<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>内容管理</el-breadcrumb-item>
        <el-breadcrumb-item>动态管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 动态发布区域 -->
    <el-row class="top-menu"> 
      <el-form :model="formInline" label-width="80px">
        <el-row>
          <el-form-item label="栏目" prop="title"> 
            <el-select v-model="formInline.category_code" placeholder="请选择" @change="onSearch()">
             <el-option v-for="item in searchCategoryList" :value-key="item.code" :key="item.code" :label="item.name" :value="item.code"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="发送内容">
            <letter ref="sendMessage" editorId="message" :content="formInline.content"></letter>
            <el-row>
              <el-button type="primary" round @click="previewTwitter" class="fr">发送预览</el-button>
            </el-row>
          </el-form-item>
        </el-row>
      </el-form>       
    </el-row>

    <!-- 动态列表 -->
    <el-row class="twitter">
      <div v-for="item in tablePageData" :key="item.id">
        <div class="clearfix">
          <div class="twitter-card">
            <div class="time">{{item.created_at | filterDate}}</div>
            <div class="content" v-html="item.content"></div>
            <div class="like" v-if="item.like_count > 0">
              <img src="../../assets/images/icon_like_small_highlight.png" alt="">
              <span>{{item.like_count}}</span></div>
            <div class="like" v-else>
              <img src="../../assets/images/icon_like_small_normal.png" alt="">
              <span>{{item.like_count}}</span>
            </div>
          </div>
        </div>
      </div>
      <div class="footer">
        <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
      </div>
    </el-row>

    <!-- 预览动态 -->
    <el-dialog title="预览动态" :visible.sync ="previewVisible" :close-on-click-modal="false" center width="424px">
      <el-row>
        <div id="preview" class="preview">
          <div class="content w-e-text-container" id="article" v-html="preview.content"></div>
        </div>
      </el-row>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="sendTwitter" :loading="previewLoading">发送</el-button>
        <el-button @click.native="previewVisible = false">取消</el-button>  
      </div>
    </el-dialog>
   
  </div> 
</template>

<script>
import API_CONTENT from '../../http/api_content'
import Letter from '@/components/Letter' // 调用编辑器
import Pagination from '@/components/Pagination'

export default {
  name: 'Twitter',
  data () {
    return {
      // 搜索区表单
      formInline: {category_code: '', content: ''},

      totalAll: 0,        // 列表总数目
      pageSize: 50,       // 分页显示数目
      pageNo: 1,          // 当前页码
      pageRefresh: true,  // 分页内容刷新

      tablePageData: [],  // 分页显示数据

      searchCategoryList: {},

      // ----预览动态----
      previewVisible: false, // 是否显示
      previewLoading: false,
      preview: {content: ''}
    }
  },
  components: {
    Pagination,
    Letter // 引入发送消息模块
  },
  mounted: function () {
    this.getCategoryList()
  },
  methods: {
    initPagination () {
      this.pageRefresh = false
      this.$nextTick(() => { this.pageRefresh = true })
    },

    gotoPage (page) {
      this.pageNo = page
      this.getTwitterList()
    },

    // 获取动态列表
    getTwitterList () {
      this.$refs.sendMessage.clear()
      var params = {
        category_code: this.formInline.category_code,
        page_no: this.pageNo,
        page_size: this.pageSize
      }
      API_CONTENT.getTwitterList(params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.twitter_list
          this.totalAll = res.data.twitter_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '获取动态列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取栏目列表
    getCategoryList () {
      API_CONTENT.getCategoryMyList({'type': 'my'}).then(res => {
        if (res.data.category_list) {
          this.searchCategoryList = res.data.category_list
          this.formInline.category_code = res.data.category_list[0].code
          this.getTwitterList()
        }
      }).catch(err => {
        console.error(err)
      })
    },

    onSearch () {
      this.pageNo = 1
      this.getTwitterList()
    },

    // 预览动态
    previewTwitter () {
      console.log(this.$refs.sendMessage.setContent)
      if (this.$refs.sendMessage.setContent()) {
        this.previewVisible = true
        this.preview.content = this.$refs.sendMessage.setContent()
      } else {
        this.$message({
          message: '不能发送空动态~',
          type: 'warning'
        })
      }
    },

    // 发送动态
    sendTwitter () {
      this.previewVisible = false
      this.formInline.content = this.$refs.sendMessage.setContent()
      API_CONTENT.addTwitter(this.formInline).then(res => {
        if (res.code === 0) {
          this.$refs.sendMessage.clear()
          this.getTwitterList()
        } else if (res.code === 100001) {
          this.$message({
            message: '您没有该栏目权限, 不能发送动态~',
            type: 'warning'
          })
        }
      }).catch(err => {
        this.$message({
          message: '不能发送空动态~',
          type: 'warning'
        })
        console.error(err)
      })
    }
  },
  filters: {
  // 截取字符串
    filterDate: (value) => {
      var newVal = value.substring(0, 10)
      if (value) {
        if (new Date(newVal).toDateString() === new Date().toDateString()) {
          newVal = '今天' + value.substring(10, 16)
        } else {
          newVal = value.substring(5, 16)
        }
      }
      return newVal
    }
  }
}
</script>

<style lang="less">
.twitter{
  background: #F9F9F9;
  padding: 20px;
  margin: 20px 0;
  min-height: 440px;
  border-radius: 10px;
  box-sizing: content-box;
  .twitter-card{
    position: relative;
    margin-right: 70px;
    padding: 16px;
    margin-top: 10px;
    border-radius: 8px;
    border: 1px solid #999;
    .time{
      float: left;
      width: 108px;
    }
    .content{
      margin-left: 108px;
      word-break: break-all;
      img {
        max-width: 100%;
      }
    }
    .like{
      position: absolute;
      right: -44px;
      bottom: -2px;
      color: #999;
      img{
        width: 18px;
      }
    }
  }
}

.preview .content {
  border: 1px solid #e2e2e2;
  line-height: 1.5;
  padding: 12px;
  background-color: #ffffff;
  border-radius: 4px;
  font-size: 15px;
  color: #333333;
  overflow: hidden;
  min-height: 48px;
}

.footer {
    text-align: right;
    margin: 10px;
}
</style>
