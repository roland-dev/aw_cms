<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>资源管理</el-breadcrumb-item>
        <el-breadcrumb-item>视频登记</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu"> 
      <el-row class="nav clearfix">
        <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr">登记视频</el-button>
      </el-row>    
      <el-form :inline="true" :model="formInline">
        <el-row>
          <el-form-item label="视频分类">
            <el-select v-model="formInline.category_code" clearable placeholder="视频分类">
              <el-option v-for="item in searchVideoCategories" :value-key="item.code" :key="item.code" :label="item.name" :value="item.code"></el-option>
            </el-select>
          </el-form-item>  
          <el-form-item label="视频作者">
            <el-select v-model="formInline.author" clearable placeholder="视频作者">
              <el-option v-for="item in searchAuthors" :value-key="item.id" :key="item.id" :label="item.name" :value="item.id"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="海报主题">
            <el-input v-model="formInline.title" placeholder="海报主题"></el-input>
          </el-form-item>
        </el-row>  
        <el-form-item label="开始时间">
          <el-date-picker
            v-model="formInline.s_time"
            align="right"
            type="date"
            value-format="yyyy-MM-dd"
            format="yyyy-MM-dd"
            placeholder="选择日期">
          </el-date-picker>
        </el-form-item>
        <el-form-item label="结束时间">
          <el-date-picker
            v-model="formInline.e_time"
            align="right"
            type="date"
            value-format="yyyy-MM-dd"
            format="yyyy-MM-dd"
            placeholder="选择日期">
          </el-date-picker>
        </el-form-item>
        <el-row>
          <el-form-item>
            <el-button type="primary" icon="el-icon-search" @click="onSearch" @keydown.13="KeySearch($event)" class="search" round>查询</el-button>
          </el-form-item>
        </el-row>
      </el-form>       
    </el-row>

    <!-- 列表 -->
    <el-row class="table-menu">
      <!-- 日股票池表格 -->
      <el-table
        :data="tablePageData"
        stripe
        style="width: 100%">
        <el-table-column fixed label="海报主题" >
          <template slot-scope="scope">
            <span class="table_title" @click="showStrategyDialog(scope.$index)">{{scope.row.title}}</span>
          </template>
        </el-table-column>
        <el-table-column prop="category" label="视频分类"></el-table-column>
        <el-table-column prop="author" label="视频作者"></el-table-column>
        <el-table-column prop="published_at" label="视频日期"></el-table-column>
        <el-table-column prop="creator" label="最后修改人"></el-table-column>
        <el-table-column prop="updated_at" label="最后修改时间"></el-table-column>
        <el-table-column fixed="right" label="操作" width="130" align="center">
          <template slot-scope="scope">
            <el-button @click.native="showEditDialog(scope.$index)"  type="text" size="small">编辑</el-button>
            <el-button @click.native="showStrategyDialog(scope.$index)"  type="text" size="small">查看</el-button>
            <el-button @click.native="delPoster(scope.$index)"  type="text" size="small">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <!-- 添加海报 -->
    <el-dialog title="登记视频" :visible.sync ="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">
        <!-- <h3>视频信息</h3> -->
        <el-row>
          <el-form-item label="视频分类" prop="category">
            <el-select v-model="addForm.category" placeholder="请选择" @change="addChange()">
              <el-option v-for="item in videoCategories" :key="item.code" :value-key="item.code" :label="item.name" :value="item.code"></el-option>
            </el-select>
          </el-form-item>
        </el-row>        
        <el-row>
          <el-form-item label="视频作者" prop="author" placeholder="请输入">
            <el-select v-model="addForm.author" placeholder="请选择">
              <el-option v-for="item in authors" :key="item.user_id" :value-key="item.user_id" :label="item.name" :value="item.user_id"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="视频日期" prop="date" placeholder="请输入">
            <div class="block">
              <el-date-picker
                v-model="addForm.published_at"
                :default-value="addForm.published_at"
                type="date"
                @change="getTime"
                value-format="yyyy-MM-dd"
                format="yyyy-MM-dd"              
                placeholder="选择日期">
              </el-date-picker>
            </div>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="海报主题" prop="title" >
            <el-input v-model="addForm.title" placeholder="请输入" :maxlength="20"></el-input>
          </el-form-item>
        </el-row>  
        <el-row>
          <el-form-item label="视频链接" prop="url"> 
            <el-input v-model="addForm.url" placeholder="请输入"></el-input>  
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="海报描述" prop="description">
            <el-input type="textarea" :rows="4" v-model="addForm.description" placeholder="最多输入130字" ></el-input>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addSubmitTostrategy" :loading="addLoading">确定并查看</el-button>
        <el-button @click.native="addVisible = false">取消</el-button>        
      </div>
    </el-dialog>

    <!-- 编辑海报 -->
    <el-dialog title="编辑海报" :visible.sync ="editVisible" :close-on-click-modal="false" center>
      <el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">

        <el-row>
          <el-form-item label="视频分类" prop="category">
            <el-select v-model="editForm.category_code" placeholder="请选择"  @change="editToChange()">
              <el-option v-for="item in videoCategories" :value-key="item.code" :key="item.code" :label="item.name" :value="item.code"></el-option>
            </el-select>
          </el-form-item>
        </el-row>        
        <el-row>
          <el-form-item label="视频作者" prop="author" placeholder="请输入">
            <el-select v-model="editForm.author_id" placeholder="请选择">
              <el-option v-for="item in authors" :value-key="item.user_id" :key="item.user_id" :label="item.name" :value="item.user_id"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="视频日期" prop="date" placeholder="请输入">
            <div class="block">
              <el-date-picker
                v-model="editForm.published_at"
                :default-value="editForm.published_at"
                type="date"
                @change="getTime"
                value-format="yyyy-MM-dd"
                format="yyyy-MM-dd"
                placeholder="修改日期">
              </el-date-picker>
            </div>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="海报主题" prop="title">
            <el-input v-model="editForm.title" placeholder="最多输入20字" :maxlength="20"></el-input>
          </el-form-item>
        </el-row>  
        <el-row>
          <el-form-item label="视频链接" prop="url"> 
            <el-input v-model="editForm.url" placeholder="请输入"></el-input>  
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="海报描述" prop="description">
            <el-input type="textarea" :rows="4" v-model="editForm.description" placeholder="最多输入130字"></el-input>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="editSubmitTostrategy" :loading="editLoading">确定并查看</el-button>
        <el-button @click.native="editVisible = false">取消</el-button>        
      </div>
    </el-dialog>

    <!-- 查看海报 -->
    <el-dialog title="查看海报" :visible.sync ="strategyVisible" :close-on-click-modal="false" center class="mt5">
      <el-row style="min-height: 200px;">
        <el-col :span="8" :offset="1">
          <div id="info">
            <p>【{{strategyForm.category}}】{{strategyForm.published_at}}  {{strategyForm.author}}老师</p>
            <p>【主题】{{strategyForm.title}}</p>
            <p>【链接】{{strategyForm.url}}</p>
            <p v-if="strategyForm.description">【主要观点】</p>
            <p>{{strategyForm.description}}</p>
            <textarea id="copyer"></textarea>
          </div>
          <div @click="copyText" class="copytext">复制文案</div>
        </el-col>
        <el-col :span="11" :offset="3">
          <img :src="qrcodeShowImg" alt="努力生成中..."  id="showPoster">
          <a href="" id="downPoster"></a>
          <div @click="downloadImg" class="download">下载海报</div>
        </el-col>
      </el-row>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="strategyToEdit">编辑</el-button>
      </div>
    </el-dialog>
  </div> 
</template>

<script>
import HTTP from '../../http/api_resource'
import pagination from '@/components/Pagination'

export default {
  name: 'Video',
  data () {
    return {
      totalAll: 0,        // 列表总数目
      pageSize: 10,       // 分页显示数目
      pageNo: 1,          // 当前页码
      pageRefresh: true,  // 分页内容刷新

      poster: '',    // 下载海报名字
      videoCategories: [],
      searchVideoCategories: [],
      authors: [],
      searchAuthors: [],
      formInline: {
        category_code: '',
        author: '',
        title: '',
        s_time: '',
        e_time: ''
      },

      // 缓存搜索数据
      searchParams: {
        category_code: '',
        author: '',
        title: '',
        s_time: '',
        e_time: ''
      },

      tablePageData: [],
      // 新增视频海报
      addVisible: false, // 是否显示
      addLoading: false,
      addFormRules: {
        category: [
          {required: true, message: '请选择分类', trigger: 'blur'}
        ],
        author: [
          {required: true, message: '请输入作者', trigger: 'blur'}
        ],
        published_at: [
          {required: true, message: '请选择时间', trigger: 'blur'}
        ],
        title: [
          {required: true, message: '请输入主题', trigger: 'blur'}
        ],
        url: [
          {required: true, message: '请输入链接', trigger: 'blur'},
          {type: 'url', message: '请输入格式正确的url', trigger: 'blur'},
          {validator: this.checkUrl, trigger: 'blur'}
        ],
        description: [
          {required: true, message: '请输入海报描述', trigger: 'blur'},
          {max: 130, message: '输入内容最大长度不超过130个字', trigger: 'blur'}
        ]
      },
      addForm: {
        category: '',
        author: '',
        published_at: '',
        title: '',
        url: '',
        description: ''
      },
      // 编辑视频海报
      editVisible: false, // 是否显示
      editLoading: false,
      editFormRules: {
        category: [
          {required: true, message: '请选择分类', trigger: 'blur'}
        ],
        author: [
          {required: true, message: '请输入作者', trigger: 'blur'}
        ],
        published_at: [
          {required: true, message: '请选择时间', trigger: 'blur'}
        ],
        title: [
          {required: true, message: '请输入主题', trigger: 'blur'}
        ],
        url: [
          {required: true, message: '请输入链接', trigger: 'blur'},
          {type: 'url', message: '请输入格式正确的url', trigger: 'blur'},
          {validator: this.checkUrl, trigger: 'blur'}
        ],
        description: [
          {required: true, message: '请输入海报描述', trigger: 'blur'},
          {max: 130, message: '输入内容最大长度不超过130个字', trigger: 'blur'}
        ]
      },
      editForm: {
        video_id: '',
        category: '',
        category_code: '',
        author: '',
        author_id: '',
        published_at: '',
        title: '',
        url: '',
        description: ''
      },
      // 查看视频海报
      strategyVisible: false, // 是否显示
      strategyLoading: false,
      strategyForm: {
        video_id: '',
        category: '',
        author: '',
        published_at: '',
        title: '',
        url: '',
        description: ''
      },
      qrcodeShowImg: ''
    }
  },
  components: {
    pagination
  },
  created: function () {
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
      this.updateList()
    },

    // 获取分类
    getCategory () {
      HTTP.getCategory().then(data => {
        this.videoCategories = data.categories
        this.searchVideoCategories = data.categories
        if (this.searchVideoCategories) {
          // this.getAuthor(this.videoCategories[0].id)
          this.getAuthor(this.videoCategories[0].code)
        }
      }).catch(err => {
        console.error(err)
      })
    },
    // 获取老师列表
    getAuthor (category) {
      // 搜索区老师分类
      HTTP.getAuthor().then(data => {
        this.searchAuthors = data.teacherList.teacherList
      })
      // 分类下老师分类
      HTTP.postAuthor({'category_code': category}).then(data => {
        this.authors = data.catToTchsList
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取表格
    getList () {
      var params = {
        'page_no': this.pageNo,
        'page_size': this.pageSize
      }
      HTTP.getList(params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.video_signin_list
          this.totalAll = res.data.video_signin_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '获取视频列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 更新表格
    updateList () {
      var params = Object.assign({}, this.searchParams)
      params.page_no = this.pageNo
      params.page_size = this.pageSize
      HTTP.searchVideo(params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.video_signin_list
          this.totalAll = res.data.video_signin_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '获取视频列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    onSearch () {
      this.pageNo = 1
      this.searchParams = this.formInline
      let searchParams = this.filterParams(this.searchParams)
      HTTP.searchVideo(searchParams).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.video_signin_list
          this.totalAll = res.data.video_signin_cnt
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

    // 响应改变视频分类下作者
    addChange () {
      HTTP.postAuthor({'category_code': this.addForm.category}).then(data => {
        this.authors = data.catToTchsList
        if (this.authors[0]) {
          this.addForm.author = this.authors[0].user_id
        } else {
          this.addForm.author = ''
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 响应改变视频分类下作者
    editToChange () {
      HTTP.postAuthor({'category_code': this.editForm.category_code}).then(data => {
        this.authors = data.catToTchsList
        if (this.authors[0]) {
          this.editForm.author_id = this.authors[0].user_id
        } else {
          this.editForm.author_id = ''
        }
      }).catch(err => {
        console.error(err)
      })
    },

    getTime (date) {
      this.time = date
    },
    changeMode (index) {
      this.stockPoolMode.forEach((d, i) => {
        if (i === index) {
          d.isActive = true
        } else {
          d.isActive = false
        }
      })
    },

    // 登记视频
    showAddDialog () {
      this.addVisible = true
      let now = this.formatDate()
      this.addForm = {
        category: this.videoCategories[0].code,
        author: this.authors[0].user_id,
        published_at: now,
        title: '',
        url: '',
        description: ''
      }
      this.addChange()
    },

    changeEnterWay () {
      if (this.addForm.enterWay === 2) {
        this.addDateDisable = false
      } else {
        this.addForm.date = ''
        this.addDateDisable = true
      }
    },

    // 确定并查看
    addSubmitTostrategy () {
      let _this = this
      this.$refs.addForm.validate((valid) => {
        if (valid) {
          HTTP.add(this.addForm).then(d => {
            // 重复链接判断提示
            if (d.code === 300001) {
              this.checkSameUrl(d.data.video.id, d.data.video.created_at, d.data.video.title)
            } else if (d.code === 300003) {
              this.$message({
                type: 'error',
                message: d.msg
              })
            } else {
              let id = d.data.videoSignin.data.videoSignin.id
              _this.$message.success({showClose: true, message: '新增成功', duration: 2000})
              _this.addVisible = false
              _this.updateList()
              // 查看新增页面
              setTimeout(() => {
                HTTP.findById(id).then(data => {
                  _this.strategyVisible = true
                  HTTP.postImg({video_id: data.oneRecordDetail.id}).then(img => {
                    _this.poster = data.oneRecordDetail.title
                    _this.qrcodeShowImg = 'data:image/png;base64,' + img.qrcode
                  }).catch(err => {
                    console.error(err)
                  })
                  _this.strategyForm = {
                    video_id: data.oneRecordDetail.id,
                    category: data.oneRecordDetail.category_name,
                    author: data.oneRecordDetail.author_name,
                    published_at: data.oneRecordDetail.published_at,
                    title: data.oneRecordDetail.title,
                    url: data.oneRecordDetail.video_url,
                    description: data.oneRecordDetail.description
                  }
                }).catch(err => {
                  console.error(err)
                })
              }, 2000)
            }
          }).catch(err => {
            this.$message({
              type: 'error',
              message: 'url地址错误，请重新输入'
            })
            console.log(err)
          })
        }
      })
    },

    // 重复链接提示
    checkSameUrl (id, date, title) {
      this.$confirm('该视频已经于' + date + '登记， 海报主题为：' + title + ', 是否跳转到该海报做修改？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.addVisible = false
        // 请求一个当前的海报
        HTTP.findById(id).then(data => {
          this.editVisible = true
          this.editForm = {
            video_id: data.oneRecordDetail.id,
            category_code: data.oneRecordDetail.category_code,
            category: data.oneRecordDetail.category_name,
            author_id: data.oneRecordDetail.author_id,
            author: data.oneRecordDetail.author_name,
            published_at: data.oneRecordDetail.published_at,
            title: data.oneRecordDetail.title,
            url: data.oneRecordDetail.url,
            description: data.oneRecordDetail.description
          }
          this.editChange()
        })
      })
    },

    // 编辑海报 dialog
    showEditDialog (index) {
      // 请求一个当前的海报
      HTTP.findById(this.tablePageData[index].id).then(data => {
        this.editVisible = true
        this.editForm = {
          video_id: this.tablePageData[index].id,
          category_code: data.oneRecordDetail.category_code,
          category: data.oneRecordDetail.category_name,
          author_id: data.oneRecordDetail.author_id,
          author: data.oneRecordDetail.author_name,
          published_at: data.oneRecordDetail.published_at,
          title: data.oneRecordDetail.title,
          url: data.oneRecordDetail.url,
          description: data.oneRecordDetail.description
        }
        this.editChange()
      })
    },

    // 响应改变视频分类下作者
    editChange () {
      HTTP.postAuthor({'category_code': this.editForm.category_code}).then(data => {
        this.authors = data.catToTchsList
      }).catch(err => {
        console.error(err)
      })
    },

    // 编辑海报跳转查看实现预览
    editSubmitTostrategy () {
      let _this = this
      let id = _this.editForm.video_id
      this.$refs.editForm.validate((valid) => {
        if (valid) {
          HTTP.update(_this.editForm).then(d => {
            if (d.code === 300001) {
              this.checkSameUrl(d.data.video.id, d.data.video.created_at, d.data.video.title)
            } else if (d.code === 300003) {
              this.$message({
                type: 'error',
                message: d.msg
              })
            } else {
              _this.$message.success({showClose: true, message: '编辑成功', duration: 2000})
              _this.editVisible = false
              this.updateList()
              setTimeout(() => {
                _this.strategyVisible = true
                HTTP.findById(id).then(data => {
                  _this.strategyForm = {
                    video_id: data.oneRecordDetail.id,
                    category: data.oneRecordDetail.category_name,
                    author: data.oneRecordDetail.author_name,
                    published_at: data.oneRecordDetail.published_at,
                    title: data.oneRecordDetail.title,
                    url: data.oneRecordDetail.video_url,
                    description: data.oneRecordDetail.description
                  }
                  HTTP.postImg({video_id: data.oneRecordDetail.id}).then(img => {
                    _this.poster = data.oneRecordDetail.title
                    _this.qrcodeShowImg = 'data:image/png;base64,' + img.qrcode
                  }).catch(err => {
                    console.log(err)
                  })
                }).catch(err => {
                  console.error(err)
                })
              }, 2000)
            }
          })
        }
      })
    },

    // 查看海报 dialog
    showStrategyDialog (index) {
      let _this = this
      HTTP.findById(_this.tablePageData[index].id).then(data => {
        _this.strategyForm = {
          video_id: data.oneRecordDetail.id,
          category: data.oneRecordDetail.category_name,
          author: data.oneRecordDetail.author_name,
          published_at: data.oneRecordDetail.published_at,
          title: data.oneRecordDetail.title,
          url: data.oneRecordDetail.video_url,
          description: data.oneRecordDetail.description
        }
        HTTP.postImg({video_id: _this.tablePageData[index].id}).then(data => {
          _this.poster = _this.tablePageData[index].title
          _this.qrcodeShowImg = 'data:image/png;base64,' + data.qrcode
        })
        // 请求展示海报的编辑页
        _this.strategyVisible = true
      })
    },

    // 查看跳转编辑海报 dialog
    strategyToEdit () {
      this.strategyVisible = false
      let _this = this
      let id = _this.strategyForm.video_id
      HTTP.findById(id).then(data => {
        _this.editForm = {
          video_id: id,
          category_code: data.oneRecordDetail.category_code,
          category: data.oneRecordDetail.category_name,
          author_id: data.oneRecordDetail.author_id,
          author: data.oneRecordDetail.author_name,
          published_at: data.oneRecordDetail.published_at,
          title: data.oneRecordDetail.title,
          url: data.oneRecordDetail.url,
          description: data.oneRecordDetail.description
        }
        _this.editChange()
        // 请求展示海报的编辑页
        setTimeout(() => {
          _this.editVisible = true
        }, 500)
      })
    },

    // 删除海报
    delPoster (index) {
      this.$confirm('是否确定删除该海报?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        HTTP.remove(this.tablePageData[index].id).then(data => {
          this.$message.success({showClose: true, message: '删除成功', duration: 2000})
          this.updateList()
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消删除'
        })
      })
    },

    // 下载图片
    downloadImg () {
      let showPoster = document.getElementById('showPoster')
      // 创建画布
      let canvas = document.createElement('canvas')
      // canvas.width = showPoster.width
      // canvas.height = showPoster.height
      // 后台提供原始图片大小
      canvas.width = 500
      canvas.height = 757
      canvas.getContext('2d').drawImage(showPoster, 0, 0)
      let url = canvas.toDataURL('image/png') // PNG格式
      // a标签实现下载图片
      let triggerDownload = document.getElementById('downPoster')
      triggerDownload.setAttribute('href', url)
      triggerDownload.setAttribute('download', `${this.poster}.png`)
      triggerDownload.click()
    },

    // 复制文本
    copyText () {
      let recommend = document.getElementById('info')
      let copyer = document.getElementById('copyer')
      copyer.value = recommend.innerText
      copyer.select() // 选择对象
      document.execCommand('Copy') // 执行浏览器复制命令
      this.$message.success({showClose: true, message: '已复制文本', duration: 2000})
    },

    // 获取当天时间
    formatDate () {
      let date = new Date()
      var y = date.getFullYear()
      var m = date.getMonth() + 1
      m = m < 10 ? '0' + m : m
      var d = date.getDate()
      d = d < 10 ? ('0' + d) : d
      return String(y) + String(m) + String(d)
    },

    // 检查是否有中文
    checkUrl (rule, value, callback) {
      let reg = /[\u4e00-\u9fa5]/
      if (reg.test(value)) {
        callback(new Error('url中不允许出现汉字'))
      } else {
        callback()
      }
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
  .add-report{
    margin-left: 16px;
    margin-bottom: 10px;
  }
  .el-rate__icon {
    margin-top: 6px;
  }
  #showPoster {
    width: 90%;
    margin-left: 5%;
  }
  #copyer {
    height: 1px;
		color: #fff;
		opacity: 0;
		border: none;
  }
  #info {
    word-break: break-all;
  }
  .copytext {
    text-decoration: underline;
    color: #409eff;
    cursor: pointer;
  }
  .download {
    margin-top: 10px;
    text-decoration: underline;
    text-align: center;
    color: #409eff;
    cursor: pointer;
  }
</style>
