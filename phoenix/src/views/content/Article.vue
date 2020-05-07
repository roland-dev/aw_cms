<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>内容管理</el-breadcrumb-item>
        <el-breadcrumb-item>文章管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu"> 
      <el-row class="nav clearfix">
        <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr">添加文章</el-button>
      </el-row>    
      <el-form :inline="true" :model="formInline">
        <el-row>
          <el-form-item label="文章名称">
            <el-input v-model="formInline.title" placeholder="文章名称"></el-input>
          </el-form-item>
          <el-form-item label="归属栏目">
            <el-select v-model="formInline.category_code" clearable placeholder="归属栏目" @change="getSubcategoryList()">
              <el-option v-for="item in searchCategoryList" :value-key="item.code" :key="item.code" :label="item.name" :value="item.code"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="归属分类">
            <el-select v-model="formInline.sub_category_code" clearable placeholder="归属分类" :disabled="formInline.category_code === ''">
              <el-option v-for="item in searchSubcategoryList" :value-key="item.code" :key="item.code" :label="item.name" :value="item.code"></el-option>
            </el-select>
          </el-form-item>  
          <el-form-item label="是否可见">
            <el-select v-model="formInline.show" clearable placeholder="是否可见">
              <el-option :value="1" label="是"></el-option>
              <el-option :value="0" label="否"></el-option>
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
      <!-- 文章列表 -->
      <el-table
        :data="tablePageData"
        stripe
        style="width: 100%">
        <el-table-column fixed prop="title" label="文章名称"></el-table-column>
        <el-table-column prop="category_name" label="归属栏目"></el-table-column>
        <el-table-column prop="sub_category_name" label="归属分类"></el-table-column>
        <el-table-column prop="modify_user_name" label="最后修改人"></el-table-column>
        <el-table-column prop="updated_at" label="最后修改时间" width="170"></el-table-column>
        <el-table-column label="推送企业微信" width="100">
          <template slot-scope="scope">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              active-color="#13ce66"
              inactive-color="#999"
              v-model="scope.row.is_push_qywx"
              @change="changePushQywx(scope.row)">
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="是否可见" width="100">
          <template slot-scope="scope">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              active-color="#13ce66"
              inactive-color="#999"
              v-model="scope.row.show"
              @change="changeShow(scope.row)">
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column fixed="right" label="操作" width="130" align="center">
          <template slot-scope="scope">
            <el-button @click.native="showEditDialog(scope.row.id)"  type="text" size="small">编辑</el-button>
            <el-button @click.native="showPreviewDialog(scope.row.id)"  type="text" size="small">查看</el-button>
            <el-button @click.native="delArticle(scope.row.id)"  type="text" size="small">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <!-- 添加文章 -->
    <el-dialog title="添加文章" :visible.sync ="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">
        <el-row>
          <el-form-item label="归属栏目" prop="category_code">
            <el-select v-model="addForm.category_code" placeholder="请选择" @change="getAddList()">
              <el-option v-for="item in searchCategoryList" :value-key="item.code" :key="item.code" :label="item.name" :value="item.code"></el-option>
            </el-select>
          </el-form-item>  
        </el-row> 
         <el-row>
          <el-form-item label="归属分类" prop="sub_category_code">
            <el-select v-model="addForm.sub_category_code" placeholder="请选择" :disabled="addForm.category_code === ''">
              <el-option v-for="item in addSubcategoryList" :value-key="item.code" :key="item.code" :label="item.name" :value="item.code"></el-option>
            </el-select>
          </el-form-item>  
        </el-row>
         <el-row>
          <el-form-item label="文章作者" prop="teacher_id">
            <el-select v-model="addForm.teacher_id" placeholder="请选择" :disabled="addForm.category_code === ''">
              <el-option v-for="item in addTeacherList" :value-key="item.id" :key="item.id" :label="item.name" :value="item.id"></el-option>
            </el-select>
          </el-form-item>  
        </el-row>
        <el-row>
          <el-form-item label="文章名称" prop="title">
            <el-input v-model="addForm.title" placeholder="请输入" :maxlength="64"></el-input>
          </el-form-item>
        </el-row> 
          <el-form-item label="文章封面" prop="cover_url">
            <el-upload ref="addCover" :action="imgUrl" :file-list="addImgFile" list-type="picture"
                        :on-success="addUploadSuccess" :on-error="uploadError" :data="imgObj"
                        :before-upload="uploadBefore" :limit="1" :on-remove="handleRemove"
                        :on-exceed="handleExceed" :with-credentials="true">
                <el-button size="small" type="primary">点击上传</el-button>
                <span slot="tip" class="el-upload__tip">(推荐图片尺寸357X198)</span>
            </el-upload>
          </el-form-item>
        <el-row>
          <el-form-item label="文章摘要" prop="summary">
            <el-input type="textarea" v-model="addForm.summary" placeholder="请输入" :maxlength="120"></el-input>
          </el-form-item>
        </el-row>       
        <!-- <el-row>
          <el-form-item label="音频地址" prop="audio_url">
            <el-input v-model="addForm.audio_url" placeholder="格式如: <iframe src=...></iframe>(来源：喜马拉雅FM音频分享)"></el-input>
          </el-form-item>
        </el-row> -->
        <el-form-item label="文章" prop="content">
          <editor ref="addEditor" editorId="addArticle" :content="addForm.content" ></editor>
        </el-form-item>
        <el-row>
          <el-form-item label="引导语" prop="ad_guide">
            <el-input type="textarea" v-model="addForm.ad_guide" placeholder="请输入" :maxlength="200" :rows="4"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
            <el-form-item label="定时发送" prop="published_at" class="data_picker">
                <el-date-picker
                        v-model="addForm.published_at"
                        type="datetime"
                        placeholder="选择日期"
                        value-format="yyyy-MM-dd HH:mm:ss"
                        format="yyyy-MM-dd HH:mm:ss"
                        style="width:200px">
                </el-date-picker>
            </el-form-item>
        </el-row>
        <el-row>
            <el-form-item label="推送微信" prop="is_push_qywx">
              <el-switch
                :active-value="1"
                :inactive-value="0"
                active-color="#13ce66"
                inactive-color="#999"
                v-model="addForm.is_push_qywx"
              >
              </el-switch>
            </el-form-item>
        </el-row>
        <el-form-item label="是否可见" prop="visible">
          <el-switch
            :active-value="1"
            :inactive-value="0"
            active-color="#13ce66"
            inactive-color="#999"
            v-model="addForm.show"
          >
          </el-switch>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="previewArticle" :loading="addLoading">发送到企业微信预览</el-button>
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确定</el-button>
        <el-button @click.native="addVisible = false">取消</el-button>        
      </div>
    </el-dialog>

    <!-- 编辑文章 -->
    <el-dialog title="编辑文章" :visible.sync ="editVisible" :close-on-click-modal="false" center  @close="closeEditDialog">
      <el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">
        <el-row>
          <el-form-item label="归属栏目" prop="category_code">
            <el-select v-model="editForm.category_code" placeholder="请选择" @change="getEditList()">
              <el-option v-for="item in searchCategoryList" :value-key="item.code" :key="item.code" :label="item.name" :value="item.code"></el-option>
            </el-select>
          </el-form-item>  
        </el-row> 
         <el-row>
          <el-form-item label="归属分类" prop="sub_category_code">
            <el-select v-model="editForm.sub_category_code" placeholder="请选择" :disabled="editForm.category_code === ''">
              <el-option v-for="item in editSubcategoryList" :value-key="item.code" :key="item.code" :label="item.name" :value="item.code"></el-option>
            </el-select>
          </el-form-item>  
        </el-row>
         <el-row>
          <el-form-item label="文章作者" prop="teacher_id">
            <el-select v-model="editForm.teacher_id" placeholder="请选择" :disabled="editForm.category_code === ''">
              <el-option v-for="item in editTeacherList" :value-key="item.id" :key="item.id" :label="item.name" :value="item.id"></el-option>
            </el-select>
          </el-form-item>  
        </el-row>
        <el-row>
          <el-form-item label="文章名称" prop="title">
            <el-input v-model="editForm.title" placeholder="请输入" :maxlength="64"></el-input>
          </el-form-item>
        </el-row> 
        <el-row>
          <el-form-item label="文章封面" prop="cover_url">
            <el-upload ref="editCover" :action="imgUrl" :file-list="editImgFile" list-type="picture"
                        :on-success="editUploadSuccess" :on-error="uploadError" :data="imgObj"
                        :before-upload="uploadBefore" :limit="1" :on-remove="handleRemove"
                        :on-exceed="handleExceed" :with-credentials="true">
                <el-button size="small" type="primary">点击上传</el-button>
                <span slot="tip" class="el-upload__tip">(推荐图片尺寸357X198)</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="文章摘要" prop="summary">
            <el-input type="textarea" v-model="editForm.summary" placeholder="请输入" :maxlength="120"></el-input>
          </el-form-item>
        </el-row>       
        <!-- <el-row>
          <el-form-item label="音频地址" prop="audio_url">
            <el-input v-model="editForm.audio_url" placeholder="格式如: <iframe src=...></iframe>(来源：喜马拉雅FM音频分享)"></el-input>
          </el-form-item>
        </el-row> -->
        <el-form-item label="文章" prop="content">
          <editor ref="editEditor" :editorId="'editArticle' + editForm.id"  :content="editForm.content"></editor>
        </el-form-item>
        <el-row>
          <el-form-item label="引导语" prop="ad_guide">
            <el-input type="textarea" v-model="editForm.ad_guide" placeholder="请输入" :maxlength="200" :rows="4"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
            <el-form-item label="定时发送" prop="published_at" class="data_picker">
                <el-date-picker
                        v-model="editForm.published_at"
                        type="datetime"
                        placeholder="选择日期"
                        value-format="yyyy-MM-dd HH:mm:ss"
                        format="yyyy-MM-dd HH:mm:ss"
                        style="width:200px">
                </el-date-picker>
            </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="editSubmit" :loading="editLoading">保存并预览</el-button>
        <el-button @click.native="editVisible = false">取消</el-button>        
      </div>
    </el-dialog>

    <!-- 预览文章 -->
    <el-dialog title="预览文章" :visible.sync ="previewVisible" :close-on-click-modal="false" center>
      <el-row> 
        <textarea id="copyer"></textarea>
        <div class="article-url">
          <div class="detail-url">文章url：<span id="detailUrl">{{preview.source_url}}</span></div>
          <div class="copy-btn" @click="copyText()">复制链接</div>
        </div>
      </el-row>
      <el-row>
        <div id="preview" class="preview">
          <div class="title">{{preview.title}}</div>
          <div class="data">{{preview.data}}</div>
          <div class="intro"><b>摘要：</b><span v-html="preview.summary"></span></div>
          <div class="content w-e-text-container" id="article" v-html="preview.content"></div>
        </div>
      </el-row>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="previewSubmit" :loading="previewLoading">编辑</el-button>
      </div>
    </el-dialog>

    <!-- 文章内容添加确认提示框 -->
    <el-dialog title="提示" :visible.sync="publishVisible" custom-class="del-contact" top="40vh">
      <i class="el-icon-warning el-message-box__status"></i><span style="display: inline-block; padding-left: 2.5em; height: 16px; line-height: 25px;">确认发布文章?</span>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="delContact(1)">是</el-button>
        <el-button @click="delContact(0)">否</el-button>
        <el-button @click="publishVisible = false">取消</el-button>
      </span>
    </el-dialog>
  </div> 
</template>

<script>
import Env from '../../http/env'
import API_CONTENT from '../../http/api_content'
import Pagination from '@/components/Pagination'
import Editor from '@/components/Editor' // 调用编辑器

export default {
  name: 'Article',
  data () {
    return {
      myCroppa: {},
      searchCategoryList: {},     // 归属栏目
      searchSubcategoryList: {},     // 文章分类
      show: 1,
      // 搜索区表单
      formInline: {title: '', category_code: '', sub_category_code: '', show: ''},

      // 缓存搜索数据
      searchParams: {title: '', category_code: '', sub_category_code: '', show: ''},

      // 分页初始化
      totalAll: 0,          // 列表总数目
      pageSize: 10,         // 分页显示数目
      pageNo: 1,            // 当前页码
      pageRefresh: true,    // 分页内容刷新

      tablePageData: [],    // 分页显示数据

      categoryListOfPushQywx: [],   // 获取推送企业微信栏目列表

      // ----新增文章----
      addVisible: false, // 是否显示
      addLoading: false,
      addFormRules: {
        published_at: [{required: true, message: '选择日期', trigger: 'blur'}],
        category_code: [{required: true, message: '请选择归属栏目', trigger: 'change'}],
        sub_category_code: [{required: true, message: '请选择归属栏目', trigger: 'change'}],
        teacher_id: [{required: true, message: '请选择文章作者', trigger: 'change'}],
        title: [{required: true, message: '请输入文章名称', trigger: 'change'}],
        summary: [{required: true, message: '请输入文章摘要', trigger: 'blur'}],
        content: [{required: true, message: '请输入文章内容', trigger: 'blur'}],
        cover_url: [{required: true, message: '请上传封面图片', trigger: 'blur'}]
      },
      addForm: {category_code: '', sub_category_code: '', teacher_id: '', title: '', cover_url: null, summary: '', audio_url: '', content: '', show: 0, ad_guide: '', is_push_qywx: 0, visible: 0, published_at: ''},
      addSubcategoryList: {},
      addTeacherList: {},

      // 上传图片 上传图片预览在addImgFile数组里面[{name: '', url: ''}]
      addImgFile: [],
      editImgFile: [],
      imgUrl: `${Env.baseURL}/resource/image`,
      imgObj: {'image': {}},

      // ----编辑文章----
      editVisible: false, // 是否显示
      editLoading: false,
      editFormRules: {
        publishedat: [{required: true, message: '选择日期', trigger: 'blur'}],
        category_code: [{required: true, message: '请选择归属栏目', trigger: 'change'}],
        sub_category_code: [{required: true, message: '请选择归属栏目', trigger: 'change'}],
        teacher_id: [{required: true, message: '请选择文章作者', trigger: 'change'}],
        title: [{required: true, message: '请输入文章名称', trigger: 'change'}],
        summary: [{required: true, message: '请输入文章摘要', trigger: 'blur'}],
        content: [{required: true, message: '请输入文章内容', trigger: 'blur'}],
        cover_url: [{required: true, message: '请上传封面图片', trigger: 'blur'}]
      },
      editForm: {category_code: '', sub_category_code: '', teacher_id: '', title: '', cover_url: null, summary: '', audio_url: '', content: '', ad_guide: '', published_at: ''},
      editSubcategoryList: {},
      editTeacherList: {},

       // ----预览文章----
      previewVisible: false, // 是否显示
      previewLoading: false,
      preview: {title: '', data: '', summary: '', content: '', source_url: ''},

      // ---确认弹框---
      publishVisible: false
    }
  },
  components: {
    Editor,  // 引入wangEditor富文本编辑器模块
    Pagination
  },
  created: function () {
  },
  mounted: function () {
    this.getArticleList()
    this.getCategoryList()
  },
  methods: {
    initPagination () {
      this.pageRefresh = false
      this.$nextTick(() => { this.pageRefresh = true })
    },

    // 跳转分页
    gotoPage (page) {
      this.pageNo = page
      this.getArticleList()
    },

    // 获取文章列表
    getArticleList () {
      var params = Object.assign({}, this.searchParams)
      params.page_no = this.pageNo
      params.page_size = this.pageSize
      API_CONTENT.getArticleList(params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.article_list
          this.totalAll = res.data.article_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '获取文章列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取推送企业微信栏目列表
    getPushQywxCategoryList () {
      API_CONTENT.getPushQywxCategoryList().then(res => {
        console.log(res.data)
        this.categoryListOfPushQywx = res.data
        console.log(this.categoryListOfPushQywx)
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取归属栏目
    getCategoryList () {
      API_CONTENT.getCategoryList().then(res => {
        this.searchCategoryList = res.data.category_list
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取归属分类
    getSubcategoryList () {
      this.searchSubcategoryList = {}
      if (this.formInline.category_code) {
        API_CONTENT.getSubcategoryList(this.formInline.category_code).then(res => {
          this.searchSubcategoryList = res.data.sub_category_list
        }).catch(err => {
          console.log(err)
        })
      }
    },

    // 修改文章可见状态
    changeShow (row) {
      let showStatus = row.show === 1 ? 1 : 0
      API_CONTENT.changeShow(row.id, showStatus).then(res => {
        if (res.code === 0) {
          console.log('可见状态改变')
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 修改文章是否推送企业微信
    changePushQywx (row) {
      let pushStatus = row.is_push_qywx === 1 ? 1 : 0
      API_CONTENT.changePushQywx(row.id, pushStatus).then(res => {
        if (res.code === 0) {
          console.log('推送状态改变')
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 修改添加弹出层，栏目分类关联文章分类及作者功能
    getAddList () {
      this.addSubcategoryList = {}
      this.addteacherList = {}
      if (this.addForm.category_code) {
        API_CONTENT.getSubcategoryList(this.addForm.category_code).then(res => {
          this.addSubcategoryList = res.data.sub_category_list
        }).catch(err => {
          console.log(err)
        })
        API_CONTENT.getTeacherList(this.addForm.category_code).then(res => {
          this.addTeacherList = res.data.teacher_list
        }).catch(err => {
          console.log(err)
        })
        console.log(this.categoryListOfPushQywx.includes(this.addForm.category_code))
        if (this.categoryListOfPushQywx.includes(this.addForm.category_code)) {
          setTimeout(() => {
            this.addForm.is_push_qywx = 1
            this.addForm.show = 1
          }, 500)
        } else {
          setTimeout(() => {
            this.addForm.is_push_qywx = 0
            this.addForm.show = 0
          }, 500)
        }
      }
    },

    // 修改编辑弹出层，栏目分类关联文章分类及作者功能
    getEditList () {
      this.editSubcategoryList = {}
      this.editteacherList = {}
      this.editForm.sub_category_code = ''
      this.editForm.teacher_id = ''
      if (this.editForm.category_code) {
        API_CONTENT.getSubcategoryList(this.editForm.category_code).then(res => {
          this.editSubcategoryList = res.data.sub_category_list
        }).catch(err => {
          console.log(err)
        })
        API_CONTENT.getTeacherList(this.editForm.category_code).then(res => {
          this.editTeacherList = res.data.teacher_list
        }).catch(err => {
          console.log(err)
        })
      }
    },

    // 更新表格
    updateList () {
      this.getArticleList()
    },

    // 询问是否发布
    showAddContactDialog () {
      this.publishVisible = true
      // this.delId = id
    },

    onSearch () {
      this.pageNo = 1
      this.searchParams = this.formInline
      let searchParams = this.filterParams(this.searchParams)
      API_CONTENT.searchArticleList(searchParams).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.article_list
          this.totalAll = res.data.article_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '查询失败' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    KeySearch (ev) {
      this.onSearch()
    },

    // 新增文章
    showAddDialog () {
      this.getPushQywxCategoryList()
      let _this = this
      _this.addImgFile = []
      setTimeout(() => {
        _this.addVisible = true
      }, 500)
      _this.addForm = {category_code: '', sub_category_code: '', teacher_id: '', title: '', cover_url: '', summary: '', content: '', show: 0, ad_guide: '', is_push_qywx: 0, visible: 0, published_at: ''}
      setTimeout(() => {
        _this.$refs.addForm.clearValidate()
        _this.$refs.addEditor.clear()
      }, 600)
    },

    addSubmit () {
      this.$confirm('确认后,该内容会被正式推送给客户,请确定是否推送?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        let _this = this
        this.addForm.content = _this.$refs.addEditor.setContent()
        this.$refs.addForm.validate((valid) => {
          if (valid) {
            this.addLoading = true
            API_CONTENT.addArticle(_this.addForm).then(res => {
              if (res.code === 0) {
                _this.$message.success({showClose: true, message: '新增成功', duration: 2000})
                _this.updateList()
                _this.addVisible = false
                setTimeout(() => {
                  _this.showPreviewDialog(res.data.article.id)
                }, 500)
              } else {
                _this.$message.error({showClose: true, message: '新增失败', duration: 2000})
                _this.addVisible = false
              }
              this.addLoading = false
            }).catch(err => {
              console.error(err)
              this.addLoading = false
            })
            _this.$refs.addCover.clearFiles()
          }
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消发布'
        })
      })
    },

    previewArticle () {
      this.$confirm('是否确定推送到企业微信预览该文章?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        let _this = this
        this.addForm.content = _this.$refs.addEditor.setContent()
        this.$refs.addForm.validate((valid) => {
          if (valid) {
            this.addLoading = true
            API_CONTENT.articlePreview(_this.addForm).then(res => {
              if (res.code === '1000') {
                _this.$message.success({showClose: true, message: '推送预览成功', duration: 2000})
              } else if (res.code === '5001') {
                _this.$message.error({showClose: true, message: '所选栏目不支持推送企业微信', duration: 2000})
              } else {
                _this.$message.error({showClose: true, message: '推送预览失败', duration: 2000})
              }
              this.addLoading = false
            }).catch(err => {
              console.error(err)
              this.addLoading = false
            })
          }
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消推送预览'
        })
      })
    },

    // 编辑文章
    showEditDialog (id) {
      let _this = this
      this.editImgFile = []
      setTimeout(() => {
        _this.editVisible = true
      }, 500)
      API_CONTENT.getArticle(id).then(res => {
        if (res.code === 0) {
          _this.editForm.category_code = res.data.article.category_code
          _this.getEditList()
          _this.editForm = {
            id: id,
            category_code: res.data.article.category_code,
            sub_category_code: res.data.article.sub_category_code,
            teacher_id: res.data.article.teacher_id,
            title: res.data.article.title,
            cover_url: res.data.article.cover_url,
            summary: res.data.article.summary,
            // audio_url: res.data.article.audio_url,
            content: res.data.article.content,
            ad_guide: res.data.article.ad_guide,
            published_at: res.data.article.published_at
          }
          if (res.data.article.cover_url) {
            _this.editImgFile = [{
              name: res.data.article.cover_url.substr(res.data.article.cover_url.lastIndexOf('/') + 1),
              url: res.data.article.cover_url
            }]
          }
        }
        setTimeout(() => {
          _this.$refs.editForm.clearValidate()
          _this.$refs.editEditor.getContent(_this.editForm.content)
        }, 600)
      })
    },

    editSubmit () {
      let _this = this
      this.editForm.content = _this.$refs.editEditor.setContent()
      this.$refs.editForm.validate((valid) => {
        if (valid) {
          this.editLoading = true
          API_CONTENT.editArticle(_this.editForm.id, _this.editForm).then(res => {
            if (res.code === 0) {
              _this.$message.success({showClose: true, message: '编辑成功', duration: 2000})
              this.updateList()
              _this.editVisible = false
              setTimeout(() => {
                _this.showPreviewDialog(res.data.article.id)
              }, 500)
            } else {
              _this.$message.error({showClose: true, message: '编辑失败', duration: 2000})
              _this.editVisible = false
            }
            this.editLoading = false
          }).catch(err => {
            console.error(err)
            this.editLoading = false
          })
          _this.$refs.editCover.clearFiles()
        }
      })
    },

    closeEditDialog () {
      this.$refs.editEditor.clear()
      this.editVisible = false
    },

    // 预览文章
    showPreviewDialog (id) {
      let _this = this
      this.previewVisible = true
      API_CONTENT.getArticle(id).then(res => {
        if (res.code === 0) {
          _this.preview = {
            id: id,
            title: res.data.article.title,
            data: res.data.article.published_at,
            summary: res.data.article.summary,
            content: res.data.article.content,
            source_url: res.data.article.source_url
          }
          setTimeout(() => {
            _this.fitIframe()
          }, 200)
        }
      })
    },
    // 跳转到编辑页面
    previewSubmit () {
      this.previewVisible = false
      setTimeout(() => {
        this.showEditDialog(this.preview.id)
      }, 500)
    },

    // 删除
    delArticle (id) {
      this.$confirm('是否确定删除该文章?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        API_CONTENT.delArticle(id).then(data => {
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

    // // 上传要截图的图片
    // uploadCroppedImage () {
    //   this.myCroppa.generateBlob((blob) => {
    //   }, 'image/jpeg', 0.8)
    // },

    // ---------------------上传图片模块------------------------------
    uploadBefore (file) {
      console.log(file)
      // post请求中image类型为文件对象
      let imgType = [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/bmp',
        'image/gif',
        'image/svg'
      ]
      let isJPG = false
      for (var i = 0; i < imgType.length; i++) {
        if (imgType[i] === file.type) {
          isJPG = true
        }
      }
      const isLt500K = 357 * 198 / 1024 <= 300
      if (!isJPG) {
        this.$message.error('上传图片只能是 JPG/PNG/GIF/SVG 格式!')
      }
      if (!isLt500K) {
        this.$message.error('上传图片大小不能超过 300k!')
      }
      if (isJPG && isLt500K) {
        this.imgObj.image = file
      }
      return isJPG && isLt500K
    },
    // 上传图片成功
    addUploadSuccess (response, file, addImgFile) {
      if (response.code === 0) {
        this.addForm.cover_url = response.data.path
        this.addImgFile = [{
          name: response.data.path.substr(response.data.path.lastIndexOf('/') + 1),
          url: response.data.path
        }]
      } else {
        console.error(response.msg)
      }
    },
    // 上传图片成功
    editUploadSuccess (response, file, editImgFile) {
      if (response.code === 0) {
        this.editForm.cover_url = response.data.path
        this.editImgFile = [{
          name: response.data.path.substr(response.data.path.lastIndexOf('/') + 1),
          url: response.data.path
        }]
      } else {
        console.error(response.msg)
      }
    },

    // 上传图片失败
    uploadError (response, file, ImgFile) {
      console.error('上传失败，请重试！')
    },

    handleRemove (file, fileList) {
      this.addForm.cover_url = ''
      this.editForm.cover_url = ''
    },

    handleExceed (file, ImgFile) {
      this.$alert('只能上传一张图片')
    },

    // 更新上传图片
    updateUploadSuccess (response, file, ImgFile) {
      if (response.code === 0) {
        this.addForm.cover_url = response.data.path
        this.addImgFile = [{
          name: response.data.path.substr(response.data.path.lastIndexOf('/') + 1),
          url: response.data.path
        }]
      } else {
        console.error(response.msg)
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
    },

    fitIframe () {
      let article = document.getElementById('article')
      let iframes = article.getElementsByTagName('iframe')
      if (iframes.length > 0) {
        for (let i = 0; i < iframes.length; i++) {
          iframes[i].style.width = '100%'
          iframes[i].style.height = '248px'
        }
      } else {
        return false
      }
    },

    // 复制文本
    copyText () {
      let detailUrl = document.getElementById('detailUrl')
      let copyer = document.getElementById('copyer')
      copyer.value = detailUrl.innerText
      copyer.select() // 选择对象
      document.execCommand('Copy') // 执行浏览器复制命令
      this.$message.success({showClose: true, message: '已复制url', duration: 2000})
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
.el-upload__tip{
  display: inline-block;
  margin-left: 12px;
}
.article-url{
  margin: 0 auto;
  margin-top: -34px;
  width: 400px;
  padding: 5px 0;
  word-break: break-all;
  .detail-url {
    width: 100%;
  }
  .copy-btn {
    color: #409eff;
    text-decoration: underline;
  }
  
}
#copyer {
  height: 1px;
  color: #fff;
  opacity: 0;
  border: none;
}  
.preview{
  width: 375px;
  min-height: 600px;
  margin: 0 auto;
  border: 1px solid #e2e2e2;
  padding: 20px 12px;
  .title{
    font-size: 20px;
    font-weight: bold;
    line-height: 28px;
  }
  .data{
    font-size: 12px;
    color: #999;
    line-height: 17px;
    margin-top: 8px;
  }
  .intro{
  	margin-top: 16px;
  }
  .content{
    margin-top: 14px;
    font-size: 15px;
    color: #333;
  }
}

</style>
