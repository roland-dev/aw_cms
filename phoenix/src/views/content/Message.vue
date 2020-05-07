<template>
  <div class="private-message">
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>内容管理</el-breadcrumb-item>
        <el-breadcrumb-item>私信管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <el-row class="category">   
      <el-form :inline="true" :model="formInline">
        <el-row>
          <el-form-item label="栏目名称" prop="category_code"> 
            <el-select v-model="formInline.category_code" placeholder="请选择" @change="getTeacherId()">
             <el-option v-for="item in searchCategoryList" :value-key="item.code" :key="item.code" :label="item.name" :value="item.code"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
      </el-form>       
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu"> 
      <el-form :inline="true" :model="customInline">
        <el-row>
          <el-form-item label="客户姓名" prop="name">
            <el-input v-model="customInline.name" placeholder="请输入" :maxlength="64"></el-input>
          </el-form-item>
        </el-row> 
        <el-row>
          <el-form-item>
            <el-button type="primary" icon="el-icon-search" @click="onSearch"  @keydown.13="KeySearch($event)" class="search" round>查询</el-button>
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
        style="width: 100%" 
        @row-click="getMessageSession">
        <el-table-column prop="name" label="客户姓名" width="100">
          <template slot-scope="scope">
            <el-popover trigger="hover" placement="bottom">
              <p>姓名: {{ scope.row.name }}</p>
              <p>关注动态: {{ scope.row.twitterNames }}</p>
              <p>聊天私信: {{ scope.row.pmNames }}</p>
              <p>入金时间: {{ scope.row.money_date }}</p>
              <p>入金金额: {{ scope.row.money_total }}</p>
              <div slot="reference" class="name-wrapper">
                <el-tag size="medium">{{ scope.row.name}}</el-tag>
              </div>
            </el-popover>
          </template>
        </el-table-column>
        <el-table-column prop="content" label="最新消息">
          <template slot-scope="scope">
                <div v-html="scope.row.content" class="ellipsis"></div>
                <div class="read-num" v-if="scope.row.read_num>0">{{scope.row.read_num}}</div>
            </template>
        </el-table-column>
        <el-table-column prop="created_at" label="发送时间" :formatter="formatData"></el-table-column>
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

    <!-- 添加私信 -->
    <el-dialog :title="sessionName" :visible.sync ="addVisible" :close-on-click-modal="false" center @close="closeSession">
      <el-row class="message">
        <div v-for="item in MessageList" :key="item.id">
          <div class="clearfix" v-if="item.direction === 0">   
            <div class="time">{{item.created_at | filterDate}}</div>
            <div class="card">
              <div class="content" v-html="item.content"></div>
            </div>
          </div>
          <div class="clearfix" v-if="item.direction === 1">   
            <div class="time" style="text-align: right; margin-right:8px">{{item.created_at | filterDate}}</div>
            <div class="card send"> 
              <div class="content" v-html="item.content"></div>
            </div>
          </div>
        </div>
      </el-row>
      <div slot="footer">
        <el-form>
          <el-row>
            <el-form-item>
              <letter ref="sendMessage" editorId="message" :content="addMessage"></letter>
              <el-row>
                <el-button type="primary" round @click="sendMessage" class="fr">发送</el-button>
              </el-row>
            </el-form-item>
          </el-row>
        </el-form>       
      </div>
    </el-dialog>
  </div> 
</template>

<script>
import API_CONTENT from '../../http/api_content'
import Letter from '@/components/Letter' // 调用编辑器

export default {
  name: 'Message',
  data () {
    return {
      // 搜索区表单
      formInline: {category_code: '', teacher_id: '', open_id: ''},
      customInline: {name: ''},
      totalAll: 0,          // 列表总数目
      pageSize: 10,         // 分页显示数目
      tableData: [],        // 列表总数据
      tablePageData: [],    // 分页显示数据
      searchCategoryList: {},

      // 会话列表
      sessionListArr: [],

      // ----新增栏目----
      sessionName: '',
      sessionObj: {},
      addVisible: false, // 是否显示
      addLoading: false,
      MessageList: [],
      addMessage: ''
    }
  },
  components: {
    Letter    // 引入发送消息模块
  },
  mounted: function () {
    this.getCategoryList()
    this.loopGetSessionMessageList()
  },
  methods: {
    // 获取栏目列表
    getCategoryList () {
      API_CONTENT.getCategoryMyList({'type': 'my'}).then(res => {
        if (res.data.category_list) {
          this.searchCategoryList = res.data.category_list
          this.formInline.category_code = res.data.category_list[0].code     // 初始化第一选项
          this.getTeacherId()                   // 获取当前老师id
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取主笔老师id
    getTeacherId () {
      API_CONTENT.getTeacherId(this.formInline.category_code).then(res => {
        if (res.code === 0) {
          res.data.teacher_list.forEach(d => {
            if (d.primary === 1) {
              this.formInline.teacher_id = d.id
              return false
            }
          })
          this.getSessionMessageList()
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 循环获取列表刷新未读
    loopGetSessionMessageList () {
      let _this = this
      setInterval(() => {
        _this.getSessionMessageList()
      }, 60 * 1000)
    },

    // 获取私信列表内容
    getSessionMessageList () {
      let teacherObj = {'teacher_id': this.formInline.teacher_id}
      API_CONTENT.getSessionMessageList(teacherObj).then(res => {
        this.sessionListArr = []
        if (res.data.session_list.length !== 0) {
          let openIdArr = res.data.session_list
          for (let i = 0; i < openIdArr.length; i++) {
            let obj = {'teacher_id': this.formInline.teacher_id, 'open_id': openIdArr[i]}
            this.setTableData(obj)
          }
        } else {
          this.tableData = []
          this.initPageTable()
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 通过teacher_id和open_id去获取列表数据
    setTableData (obj) {
      API_CONTENT.getMessage(obj).then(res => {
        if (res.data.private_message_list) {
          let list = res.data.private_message_list
          let costomer = res.data.customer
          let msgObj = {}
          msgObj.name = costomer.name
          msgObj.content = list[list.length - 1].content
          msgObj.created_at = list[list.length - 1].created_at
          msgObj.open_id = list[list.length - 1].open_id
          msgObj.read_num = 0
          list.forEach(d => {
            if (d.direction === 0 && d.read === 0) {
              msgObj.read_num ++
              msgObj.read_num = msgObj.read_num > 9 ? '...' : msgObj.read_num
            }
          })
          // 往会话表格里面塞一行数据
          this.sessionListArr.push(msgObj)
          this.tableData = this.sessionListArr
          this.initPageTable()
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 搜索私信列表内容
    searchSessionMessageList () {
      let teacherObj = {'teacher_id': this.formInline.teacher_id}
      API_CONTENT.getSessionMessageList(teacherObj).then(res => {
        this.sessionListArr = []
        if (res.data.session_list.length !== 0) {
          let openIdArr = res.data.session_list
          for (let i = 0; i < openIdArr.length; i++) {
            let obj = {'teacher_id': this.formInline.teacher_id, 'open_id': openIdArr[i]}
            this.searchTableData(obj)
          }
        } else {
          this.tableData = []
          this.initPageTable()
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 通过teacher_id和open_id去获取列表数据
    searchTableData (obj) {
      API_CONTENT.getMessage(obj).then(res => {
        if (res.data.private_message_list) {
          let list = res.data.private_message_list
          let costomer = res.data.customer
          let msgObj = {}
          msgObj.name = costomer.name
          msgObj.content = list[list.length - 1].content
          msgObj.created_at = list[list.length - 1].created_at
          msgObj.open_id = list[list.length - 1].open_id
          msgObj.read_num = 0
          list.forEach(d => {
            if (d.read === 0) {
              msgObj.read_num ++
              msgObj.read_num = msgObj.read_num > 9 ? '...' : msgObj.read_num
            }
          })
          // 往会话表格里面塞一行数据
          this.sessionListArr = []
          if (msgObj.name.indexOf(this.customInline.name) > -1) {
            this.sessionListArr.push(msgObj)
          }
          this.tableData = this.sessionListArr
          this.initPageTable()
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取弹出层对话信息
    getMessageSession (row) {
      this.formInline.open_id = row.open_id
      this.sessionObj = {'teacher_id': this.formInline.teacher_id, 'open_id': row.open_id}
      API_CONTENT.getMessage(this.sessionObj).then(res => {
        if (res.code === 0) {
          this.MessageList = res.data.private_message_list
          this.MessageList.forEach(d => {
            if (d.read === 0 && d.direction === 0) {
              this.readMessage(d.id)
            }
          })
          this.sessionName = row.name
          this.showAddDialog()
          setTimeout(() => {
            let message = document.querySelector('.message')
            message.scrollTop = message.scrollHeight
          }, 500)
        } else {
          this.$message({
            message: '没有找到对应的会话信息',
            type: 'warning'
          })
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 循环返回私信
    loopSession () {
      let sessionLoop = setInterval(() => {
        if (!this.addVisible) {
          clearInterval(sessionLoop)
        }
        if (this.addVisible) {
          this.sessionObj = {'teacher_id': this.formInline.teacher_id, 'open_id': this.formInline.open_id}
          API_CONTENT.getMessage(this.sessionObj).then(res => {
            if (res.code === 0) {
              this.MessageList = res.data.private_message_list
              this.MessageList.forEach(d => {
                if (d.read === 0 && d.direction === 0) {
                  this.readMessage(d.id)
                }
              })
              setTimeout(() => {
                let message = document.querySelector('.message')
                message.scrollTop = message.scrollHeight
              }, 500)
            } else {
              this.$message({
                message: '没有找到对应的会话信息',
                type: 'warning'
              })
            }
          }).catch(err => {
            console.error(err)
          })
        }
      }, 5000)
    },

    // 私信阅读接口
    readMessage (id) {
      API_CONTENT.readMessage(id).then(res => {
        if (res.code === 0) {
          console.log('已读' + id)
        } else {
          this.$message({
            message: '阅读请求错误',
            type: 'warning'
          })
        }
      }).catch(err => {
        console.error(err)
      })
    },

    refreshMessageSession () {
      API_CONTENT.getMessage(this.sessionObj).then(res => {
        if (res.code === 0) {
          this.MessageList = res.data.private_message_list
        } else {
          this.$message({
            message: '没有找到对应的会话信息',
            type: 'warning'
          })
        }
      }).catch(err => {
        console.error(err)
      })
    },

    onSearch () {
      console.log('开始搜索')
      this.searchSessionMessageList()
    },

    KeySearch (ev) {
      console.log('开始搜索')
      this.searchSessionMessageList()
    },

    // 新增
    showAddDialog () {
      this.addVisible = true
      this.loopSession()
    },

    // 发送私信
    sendMessage () {
      this.addMessage = this.$refs.sendMessage.setContent()
      let sendObj = {'content': this.addMessage, 'open_id': this.sessionObj.open_id, 'teacher_id': this.sessionObj.teacher_id}
      API_CONTENT.addMessage(sendObj).then(res => {
        if (res.code === 0) {
          this.$refs.sendMessage.clear()
          this.refreshMessageSession()
          setTimeout(() => {
            let message = document.querySelector('.message')
            message.scrollTop = message.scrollHeight
          }, 200)
        } else if (res.code === 100001) {
          this.$message({
            message: '您没有该栏目权限~',
            type: 'warning'
          })
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 关闭私信对话窗口
    closeSession () {
      this.getSessionMessageList()
      this.$refs.sendMessage.clear()
    },

    // 格式化分页数据
    initPageTable () {
      this.tablePageData = this.tableData.slice(0, this.pageSize)
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
          }).catch(err => {
            console.error(err)
          })
        })
      }
      this.totalAll = this.tableData.length
      let pager = document.getElementsByClassName('el-pager')[0]
      if (pager) {
        pager.getElementsByTagName('li')[0].click()
      }
    },

    // 设置表格分页页面
    handleCurrentChange (page) {
      let start = (page - 1) * this.pageSize
      let end = page * this.pageSize
      this.tablePageData = page === this.totalAll ? this.tableData.slice(start) : this.tablePageData = this.tableData.slice(start, end)
    },

    // 日期时间
    formatData (row) {
      let newVal = row.created_at.substring(0, 16)
      return newVal
    }
  },
  filters: {
  // 截取字符串
    filterDate: (val) => {
      var newVal = val.substring(0, 10)
      if (val) {
        if (new Date(newVal).toDateString() === new Date().toDateString()) {
          newVal = '今天' + val.substring(10, 16)
        } else {
          newVal = val.substring(5, 16)
        }
      }
      return newVal
    }
  }
}
</script>

<style lang="less">
.private-message {
  .category{
    padding: 10px;
    background: #fff;
    border-radius: 6px;
    margin-bottom: 6px;
    .el-form-item{
      padding: 10px 0  0 10px;
    }
  }
  .message{
    height: 310px;
    border-radius: 10px;
    box-sizing: content-box;
    overflow-y: scroll;
    .time{
      &.send{
        float: right;
        margin-right: 6px;
      }
    }
    .card{
      overflow: hidden;
      width: 70%;
      max-width: 440px;
      padding: 4px 8px;
      margin-top: 4px;
      margin-bottom: 8px; 
      border-radius: 8px;
      border: 1px solid #999;
      &.send{
        background: #409EFF;
        border-color: #409EFF;
        float: right; 
      }
      .content{
        img{
          max-width: 100%;
        }
      }
    }
  }
  .el-dialog--center{
    .el-dialog__footer{
      padding-bottom: 0
    }
  } 

  .ellipsis {
    width:calc(100% - 20px);  
    display: inline-block;
    overflow: hidden;    
    text-overflow:ellipsis;    
    white-space: nowrap;
    img {
      max-width: 30px;
    }
  }
  .read-num{
    display: inline-block;
    background: #fe2d3a;
    border-radius: 50%;
    width: 14px;
    height: 14px;
    line-height: 14px;
    color: #fff;
    text-align: center;
    vertical-align: 6px;
  }
}

</style>
