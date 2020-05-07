<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>内容管理</el-breadcrumb-item>
        <el-breadcrumb-item>个股报告管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu"> 
      <el-row class="nav clearfix">
        <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr">添加报告</el-button>
      </el-row>    
      <el-form :inline="true" :model="formInline">
        <el-row>
          <el-form-item label="报告标题">
            <el-input v-model="formInline.report_title" placeholder="请输入"></el-input>
          </el-form-item>
          <el-form-item label="股票代码">
            <el-input v-model="formInline.stock_code" placeholder="请输入"></el-input>
          </el-form-item>
          <el-form-item label="报告类型">
            <el-select v-model="formInline.category_id" clearable placeholder="全部">
              <el-option v-for="item in reportCategoryList" :value-key="item.category_id" :key="item.category_id" :label="item.category_name" :value="item.category_id"></el-option>
            </el-select>
          </el-form-item>  
          <el-form-item label="发布状态">
            <el-select v-model="formInline.publish" clearable placeholder="全部">
              <el-option v-for="item in publishStatusList" :value-key="item.status" :key="item.status" :label="item.name" :value="item.status"></el-option>
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
      <!-- 报告列表 -->
      <el-table
        :data="tablePageData"
        stripe
        style="width: 100%">
        <el-table-column fixed prop="report_title" label="报告标题"></el-table-column>
        <el-table-column prop="report_date" label="报告日期"></el-table-column>
        <el-table-column prop="category_name" label="报告类型"></el-table-column>
        <el-table-column label="相关股票">
          <template slot-scope="scope">
            <div>{{scope.row.stock_name}} ({{scope.row.stock_code}})</div>
          </template>
        </el-table-column>
        <el-table-column prop="publish_status_name" label="发布状态"></el-table-column>
        <el-table-column prop="last_modify_user_name" label="最后修改人"></el-table-column>
        <el-table-column prop="updated_at" label="最后修改时间" width="130" :formatter="formatData"></el-table-column>
        <el-table-column fixed="right" label="操作" width="130" align="center">
          <template slot-scope="scope">
            <el-dropdown>
              <el-button type="primary">
                报告管理<i class="el-icon-arrow-down el-icon--right"></i>
              </el-button>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item @click.native="showEditDialog(scope.row.id)" v-if="!scope.row.publish || modifyPermission">编辑报告</el-dropdown-item>
                <el-dropdown-item @click.native="delReport(scope.row.id)" v-if="!scope.row.publish">删除报告</el-dropdown-item>
                <el-dropdown-item @click.native="showPreviewDialog(scope.row.id)">查看报告</el-dropdown-item>
                <el-dropdown-item @click.native="pushReport(scope.row.id)" v-if="!scope.row.publish">发布报告</el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <!-- 添加报告 -->
    <el-dialog title="添加报告" :visible.sync ="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">
        <el-row>
          <el-form-item label="股票代码" prop="stock_code">
            <el-autocomplete
              v-model="addForm.stock_code"
              :fetch-suggestions="querySearchAsync"
              placeholder=""
              @select="handleAddSelect">
            </el-autocomplete>
             <span> {{addForm.stock_name}}</span>
          </el-form-item>
          <div class="stock-tip">(只可选择个股代码)</div>
        </el-row> 
        <el-row class="mt15">
          <el-form-item label="报告标题" prop="report_title">
            <el-input v-model="addForm.report_title" placeholder="请输入" :maxlength="64"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="作者" prop="defaultAuthor">
            <el-input v-model="defaultAuthor" placeholder="请输入" style="width: 200px" disabled></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="报告类型" prop="category_id">
            <el-select v-model="addForm.category_id" placeholder="请选择" @change="changeCategoryType">
              <el-option v-for="item in reportCategoryList" :value-key="item.category_id" :key="item.category_id" :label="item.category_name" :value="item.category_id"></el-option>
            </el-select>
          </el-form-item>  
        </el-row> 
        <el-row v-if="addForm.category_id === 2">
          <el-form-item label="短标题" prop="report_short_title">
            <el-input v-model="addForm.report_short_title" placeholder="请输入" :maxlength="4" class="de-input"></el-input>
          </el-form-item>
        </el-row>
        <el-row v-if="addForm.category_id !== 2">
          <el-form-item label="报告日期" prop="report_date">
            <el-date-picker
              v-model="addForm.report_date"
              :clearable="false"
              align="right"
              type="date"
              value-format="yyyy-MM-dd"
              format="yyyy-MM-dd"
              :default-value="new Date()"
              placeholder="选择日期">
            </el-date-picker>
          </el-form-item>
        </el-row>
        <el-row v-if="addForm.category_id === 2">
          <el-form-item label="报告日期" prop="report_date">
            <el-date-picker
              v-model="addForm.report_date"
              :clearable="false"
              align="right"
              type="date"
              value-format="yyyy-MM-dd"
              format="yyyy-MM-dd"
              :default-value="new Date()"
              placeholder="选择日期"
              :picker-options="workDatePickerOptions">
            </el-date-picker>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="报告摘要" prop="report_summary">
            <el-input
              type="textarea"
              :rows="3"
              placeholder="最多输入120个字"
              v-model="addForm.report_summary"
              :maxlength="120"
              show-word-limit>
            </el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="内容类型" prop="report_format">
            <el-select v-model="addForm.report_format" placeholder="请选择" @change="changeReportFormat">
              <el-option v-for="item in contentCategory" :value-key="item.status" :key="item.status" :label="item.name" :value="item.status"></el-option>
            </el-select>
          </el-form-item>  
        </el-row> 
        <el-row v-if="addForm.report_format === '0'">
          <el-form-item label="报告内容" prop="report_content">
            <editor ref="addEditor" editorId="addReport" :content="addForm.report_content" ></editor>
          </el-form-item>
        </el-row>
        <el-row v-if="addForm.report_format === '1'">
          <el-form-item label="添加附件" prop="report_url">
            <el-upload ref="addCover" 
                      :action="uploadUrl" 
                      :file-list="addUploadFile"
                      :on-success="addUploadSuccess" 
                      :on-error="uploadError" 
                      :data="uploadObj"
                      :before-upload="uploadBefore" multiple
                      :limit="1" 
                      :on-remove="handleRemove"
                      :on-exceed="handleExceed" 
                      :with-credentials="true">
                <el-button size="small" type="primary">点击上传</el-button>
                <span slot="tip" class="el-upload__tip">(只能上传pdf格式的文件,且不超过500kb)</span>
            </el-upload>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确定</el-button>
        <el-button @click.native="addVisible = false">取消</el-button>        
      </div>
    </el-dialog>

    <!-- 编辑报告 -->
    <el-dialog title="编辑报告" :visible.sync ="editVisible" :close-on-click-modal="false" center  @close="closeEditDialog">
      <el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">
         <el-row>
          <el-form-item label="股票代码" prop="stock_code">
            <el-autocomplete
              v-model="editForm.stock_code"
              :fetch-suggestions="querySearchAsync"
              placeholder=""
              @select="handleEditSelect"
              :disabled="editFormPublish == 1">
            </el-autocomplete>
             <span> {{editForm.stock_name}}</span>
          </el-form-item>
          <div class="stock-tip">(只可选择个股代码)</div>
        </el-row> 
        <el-row class="mt15">
          <el-form-item label="报告标题" prop="report_title">
            <el-input v-model="editForm.report_title" placeholder="请输入" :maxlength="64" :disabled="editFormPublish == 1"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="作者" prop="defaultAuthor">
            <el-input v-model="defaultAuthor" placeholder="请输入" style="width: 200px" :disabled="editFormPublish == 1"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="报告类型" prop="category_id">
            <el-select v-model="editForm.category_id" placeholder="请选择" :disabled="editFormPublish == 1" @change="changeCategoryType">
              <el-option v-for="item in reportCategoryList" :value-key="item.category_id" :key="item.category_id" :label="item.category_name" :value="item.category_id"></el-option>
            </el-select>
          </el-form-item>  
        </el-row> 
        <el-row v-if="editForm.category_id === 2">
          <el-form-item label="短标题" prop="report_short_title">
            <el-input v-model="editForm.report_short_title" placeholder="请输入"  :disabled="editFormPublish == 1" :maxlength="4" class="de-input"></el-input>
          </el-form-item>
        </el-row>
        <el-row v-if="editForm.category_id !== 2">
          <el-form-item label="报告日期" prop="report_date">
            <el-date-picker
              v-model="editForm.report_date"
              :clearable="false"
              align="right"
              type="date"
              value-format="yyyy-MM-dd"
              format="yyyy-MM-dd"
              :default-value="new Date()"
              placeholder="选择日期"
              :disabled="editFormPublish == 1">
            </el-date-picker>
          </el-form-item>
        </el-row>
        <el-row v-if="editForm.category_id === 2">
          <el-form-item label="报告日期" prop="report_date">
            <el-date-picker
              v-model="editForm.report_date"
              :clearable="false"
              align="right"
              type="date"
              value-format="yyyy-MM-dd"
              format="yyyy-MM-dd"
              :default-value="new Date()"
              placeholder="选择日期"
              :disabled="editFormPublish == 1"
              :picker-options="workDatePickerOptions">
            </el-date-picker>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="报告摘要" prop="report_summary">
            <el-input
              type="textarea"
              :rows="3"
              placeholder="最多输入120个字"
              v-model="editForm.report_summary"
              :maxlength="120"
              show-word-limit :disabled="editFormPublish == 1">
            </el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="内容类型" prop="report_format">
            <el-select v-model="editForm.report_format" placeholder="请选择" @change="changeReportFormat">
              <el-option v-for="item in contentCategory" :value-key="item.status" :key="item.status" :label="item.name" :value="item.status"></el-option>
            </el-select>
          </el-form-item>  
        </el-row> 
        <el-row v-if="editForm.report_format === '0'">
          <el-form-item label="报告内容" prop="report_content">
            <editor ref="editEditor" editorId="editReport" :content="editForm.report_content" ></editor>
          </el-form-item>
        </el-row>
        <el-row v-if="editForm.report_format === '1'">
          <el-form-item label="添加附件" prop="report_url">
            <el-upload ref="editCover" 
                      :action="uploadUrl" 
                      :file-list="editUploadFile"
                      :on-success="editUploadSuccess" 
                      :on-error="uploadError" 
                      :data="uploadObj"
                      :before-upload="uploadBefore" multiple
                      :limit="1" 
                      :on-remove="handleRemove"
                      :on-exceed="handleExceed" 
                      :with-credentials="true">
                <el-button size="small" type="primary">点击上传</el-button>
                <span slot="tip" class="el-upload__tip">(只能上传pdf格式的文件,且不超过500kb)</span>
            </el-upload>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="editSubmit" :loading="editLoading">确定</el-button>
        <el-button @click.native="editVisible = false">取消</el-button>        
      </div>
    </el-dialog>

    <!-- 预览报告 -->
    <el-dialog title="预览报告" :visible.sync ="previewVisible" :close-on-click-modal="false" center>
      <el-row>
        <div id="preview" class="preview">
          <div class="title">{{preview.title}}</div>
          <span class="views">
            <img class="views-icon" :src="preview.author_teacher_icon" v-if="preview.author_teacher_icon">
            <img class="views-icon" :src="defaultAuthorIcon" v-if="!preview.author_teacher_icon">
            <span v-if="preview.author_teacher_name">{{preview.author_teacher_name}}</span>
            <span v-if="!preview.author_teacher_name">{{defaultAuthor}}</span>
          </span>
          <span class="date">{{preview.date}}</span>
          <div class="content w-e-text-container" id="report" v-html="preview.content" v-if="preview.report_format != 1"></div>
          <div><a :href="preview.report_url" v-if="preview.report_format == 1">{{preview.title}}.pdf</a></div>
        </div>
      </el-row>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="previewSubmit" :loading="previewLoading">编辑</el-button>
      </div>
    </el-dialog>
  </div> 
</template>

<script>
import Env from '../../http/env'
import API_CONTENT from '../../http/api_content'
import Pagination from '@/components/Pagination'
import Editor from '@/components/Editor' // 调用编辑器

export default {
  name: 'Report',
  data () {
    return {
      stockList: [],                     // 自动补全股票查询列表
      modifyPermission: false,          // 已发布报告编辑权限
      reportCategoryList: [],            // 报告分类
      contentCategory: [
        {status: '0', name: '图文'},
        {status: '1', name: 'PDF'}
      ],
      publishStatusList: [],             // 发布状态
      defaultAuthor: '产业资本研究中心',  // 默认报告作者
      defaultAuthorIcon: `${Env.baseURL}/storage/assets/ugc/images/logo_s.png`, // 默认报告作者icon

      // 搜索区表单
      formInline: {report_title: '', stock_code: '', category_id: '', publish: ''},

      // 缓存搜索数据
      searchParams: {report_title: '', stock_code: '', category_id: '', publish: ''},

      // 表格内容
      tablePageData: [],

      // 筛选掉六、日
      workDatePickerOptions: {
        disabledDate (time) {
          return time.getDay() === 0 || time.getDay() === 6
        }
      },

      // 工作日日历
      workCalendars: [],

      // 分页初始化
      totalAll: 0,               // 列表总数目
      pageSize: 10,              // 分页尺寸
      pageNo: 1,                 // 当前页
      pageRefresh: true,          // 分页内容刷新

      // ----新增报告----
      addVisible: false, // 是否显示
      addLoading: false,
      addFormRules: {
        stock_code: [{required: true, message: '请输入股票代码', trigger: 'blur'}, { max: 9, message: '股票代码格式不正确', trigger: 'change' },
          { validator: this.addCheckStockCode, trigger: 'blur' }],
        report_title: [{required: true, message: '请输入报告标题', trigger: 'blur'}],
        category_id: [{required: true, message: '请选择报告类型', trigger: 'change'}],
        report_short_title: [{required: true, message: '请输入短标题', trigger: 'blur'}, { max: 4, message: '短标题长度最多输入4个字', trigger: 'blur' }],
        report_date: [{required: true, message: '请选择日期', trigger: 'blur'}],
        report_summary: [{required: true, message: '请输入报告摘要', trigger: 'blur'}],
        report_format: [{required: true, message: '请选择内容类型', trigger: 'blur'}],
        report_content: [{required: true, message: '请输入报告内容', trigger: 'blur'}],
        report_url: [{required: true, message: '请上传pdf文件', trigger: 'blur'}]
      },
      addForm: {stock_code: '', stock_name: '', report_title: '', category_id: 1, report_short_title: '', report_date: '', report_summary: '', report_content: '', report_url: '', report_format: '0'},

      // 上传报告 上传报告预览在addUploadFile数组里面[{name: '', url: ''}]
      addUploadFile: [],
      editUploadFile: [],
      uploadUrl: `${Env.baseURL}/stock/report/upload`,
      uploadObj: {'file': {}},

      // ----编辑报告----
      editVisible: false, // 是否显示
      editLoading: false,
      editFormRules: {
        stock_code: [{required: true, message: '请输入股票代码', trigger: 'blur'}, { max: 9, message: '股票代码格式不正确', trigger: 'change' },
          { validator: this.editCheckStockCode, trigger: 'blur' }],
        report_title: [{required: true, message: '请输入报告标题', trigger: 'blur'}],
        category_id: [{required: true, message: '请选择报告类型', trigger: 'change'}],
        report_short_title: [{required: true, message: '请输入短标题', trigger: 'blur'}, { max: 4, message: '短标题长度最多输入4个字', trigger: 'blur' }],
        report_date: [{required: true, message: '请选择日期', trigger: 'blur'}],
        report_summary: [{required: true, message: '请输入报告摘要', trigger: 'blur'}],
        report_format: [{required: true, message: '请选择内容类型', trigger: 'blur'}],
        report_content: [{required: true, message: '请输入报告内容', trigger: 'blur'}],
        report_url: [{required: true, message: '请上传pdf文件', trigger: 'blur'}]
      },
      editFormPublish: false,
      editReportId: '',
      editForm: {stock_code: '', stock_name: '', report_title: '', category_id: 1, report_short_title: '', report_date: '', report_summary: '', report_content: '', report_url: '', report_format: '0'},

       // ----预览报告----
      previewVisible: false, // 是否显示
      previewLoading: false,
      preview: {title: '', date: '', summary: '', report_format: '', content: '', report_url: '', author_teacher_name: '', author_teacher_icon: ''}
    }
  },
  components: {
    Editor,  // 引入wangEditor富文本编辑器模块
    Pagination
  },
  created: function () {
  },
  mounted: function () {
    this.getList()
    this.getCategoryList()
    this.getReportPublishStatueList()
    this.getCalendars()
  },
  methods: {
    // 获取每页列表内容
    getList () {
      var params = Object.assign({}, this.searchParams)
      params.page_no = this.pageNo
      params.page_size = this.pageSize
      API_CONTENT.getStockReportList(params).then(res => {
        this.tablePageData = res.data.stock_report_list
        this.totalAll = res.data.stock_report_cnt
        this.modifyPermission = res.data.modify_permission
        this.initPagination()
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取归属栏目
    getCategoryList () {
      API_CONTENT.getReportCategoryList().then(res => {
        if (res.code === 0) {
          this.reportCategoryList = res.data.report_categories
        } else {
          this.reportCategoryList = []
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 异步列表
    querySearchAsync (code, cb) {
      if (code.length > 3) {
        API_CONTENT.getStock(code).then(res => {
          if (res.code === 0) {
            var results = []
            if (res.data.length > 0) {
              res.data.forEach(d => {
                if (d.kind === '1') {
                  var stock = {}
                  stock.value = d.symbol + ' ' + d.name
                  results.push(stock)
                }
              })
            }
            cb(results)
          } else {
            console.log('获取股票列表失败')
          }
        })
      }
      if (code.length !== 6) {
        this.addForm.stock_name = ''
        this.editForm.stock_name = ''
      }
    },

    getCalendars () {
      API_CONTENT.getCalendars().then(res => {
        if (res.code === 0) {
          this.workCalendars = res.data
        } else {
          console.log('获取日历失败:' + res.msg)
        }
      })
    },

    checkReportDateRange (reportDate) {
      let result = false
      reportDate = new Date(Date.parse(reportDate.replace(/-/g, '/')))

      let nowDate = new Date()
      nowDate.setHours(0, 0, 0)
      let startDate = this.getPreMonthDate(nowDate)
      let endDate = this.getNextMonthDate(nowDate)

      if (startDate.getTime() <= reportDate.getTime() && reportDate.getTime() <= endDate.getTime()) {
        result = true
      }

      console.log(result)
      return result
    },

    getPreMonthDate (date) {
      let daysInMonth = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]
      let strYear = date.getFullYear()
      let strDay = date.getDate()
      let strMonth = date.getMonth() + 1

      if (((strYear % 4) === 0 && (strYear % 100) !== 0) || (strYear % 400) === 0) {
        daysInMonth[2] = 29
      }

      if (strMonth - 1 === 0) {
        strYear -= 1
        strMonth = 12
      } else {
        strMonth -= 1
      }

      strDay = Math.min(strDay, daysInMonth[strMonth])

      return new Date(strYear, strMonth - 1, strDay)
    },

    getNextMonthDate (date) {
      let daysInMonth = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]
      let strYear = date.getFullYear()
      let strDay = date.getDate()
      let strMonth = date.getMonth() + 1

      if (((strYear % 4) === 0 && (strYear % 100) !== 0) || (strYear % 400) === 0) {
        daysInMonth[2] = 29
      }

      if (strMonth + 1 === 13) {
        strYear += 1
        strMonth = 1
      } else {
        strMonth += 1
      }

      strDay = Math.min(strDay, daysInMonth[strMonth])

      return new Date(strYear, strMonth - 1, strDay)
    },

    // 搜索区控制选中项
    handleSearchSelect (item) {
      this.formInline.stock = item.value.substring(0, 6)
    },

    // 添加区域控制选中项
    handleAddSelect (item) {
      this.addForm.stock_code = item.value.substring(0, 9)
      this.addForm.stock_name = item.value.substring(10)
    },

    // 编辑区域控制选中项
    handleEditSelect (item) {
      this.editForm.stock_code = item.value.substring(0, 9)
      this.editForm.stock_name = item.value.substring(10)
    },

    // 获取发布状态列表
    getReportPublishStatueList () {
      API_CONTENT.getReportPublishStatueList().then(res => {
        if (res.code === 0) {
          this.publishStatusList = res.data.publish_status
        } else {
          this.publishStatusList = []
        }
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
      let searchForm = this.filterParams(this.searchParams)
      API_CONTENT.getStockReportList(searchForm).then(res => {
        this.tablePageData = res.data.stock_report_list
        this.totalAll = res.data.stock_report_cnt
        this.modifyPermission = res.data.modify_permission
        this.initPagination()
      }).catch(err => {
        console.error(err)
      })
    },

    KeySearch (ev) {
      this.onSearch()
    },

    changeCategoryType (value) {
      this.addForm.report_short_title = ''
      this.addForm.report_date = ''
      this.editForm.report_short_title = ''
      this.editForm.report_date = ''
    },

    changeReportFormat (value) {
      this.addForm.report_content = ''
      this.addForm.report_url = ''
      this.addUploadFile = []
      this.editForm.report_content = ''
      this.editForm.report_url = ''
      this.editUploadFile = []
    },

    // 新增报告
    showAddDialog () {
      let _this = this
      _this.addUploadFile = []
      setTimeout(() => {
        _this.addVisible = true
      }, 500)
      let today = this.getNowFormatDate()
      _this.addForm = {stock_code: '', stock_name: '', report_title: '', category_id: 1, report_short_title: '', report_date: today, report_summary: '', report_content: '', report_url: '', report_format: '0'}
      setTimeout(() => {
        _this.$refs.addForm.clearValidate()
        _this.$refs.addEditor.clear()
      }, 600)
    },

    addSubmit () {
      let _this = this
      if (_this.$refs.addEditor) {
        this.addForm.report_content = _this.$refs.addEditor.setContent()
      }
      this.$refs.addForm.validate((valid) => {
        if (valid) {
          this.addLoading = true
          API_CONTENT.addStockReport(_this.addForm).then(res => {
            if (res.code === 0) {
              _this.$message.success({showClose: true, message: '新增成功', duration: 2000})
              _this.updateList()
              _this.addVisible = false
              // setTimeout(() => {
              //   _this.showPreviewDialog(res.data.article.id)
              // }, 500)
            } else {
              _this.$message.error({showClose: true, message: '新增失败: ' + res.msg, duration: 2000})
              // _this.addVisible = false
            }
            this.addLoading = false
          }).catch(err => {
            console.error(err)
            this.addLoading = false
          })
          if (_this.$refs.addCover) {
            _this.$refs.addCover.clearFiles()
          }
        }
      })
    },

    // 编辑报告
    showEditDialog (id) {
      let _this = this
      this.editUploadFile = []
      this.editReportId = id
      setTimeout(() => {
        _this.editVisible = true
      }, 500)
      API_CONTENT.getStockReport(id).then(res => {
        if (res.code === 0) {
          _this.editForm = {
            stock_code: res.data.stock_report.stock_code,
            stock_name: res.data.stock_report.stock_name,
            report_title: res.data.stock_report.report_title,
            category_id: res.data.stock_report.category_id,
            report_short_title: res.data.stock_report.report_short_title,
            report_date: res.data.stock_report.report_date,
            report_summary: res.data.stock_report.report_summary,
            report_content: res.data.stock_report.report_content,
            report_url: res.data.stock_report.report_url,
            report_format: res.data.stock_report.report_format.toString()
          }
          _this.editFormPublish = res.data.stock_report.publish
          if (res.data.stock_report.report_url) {
            _this.editUploadFile = [{
              name: res.data.stock_report.report_url.substr(res.data.stock_report.report_url.lastIndexOf('/') + 1),
              url: res.data.stock_report.report_url
            }]
          }
        }
        setTimeout(() => {
          _this.$refs.editForm.clearValidate()
          if (_this.$refs.editEditor) {
            _this.$refs.editEditor.getContent(_this.editForm.report_content)
          }
        }, 600)
      })
    },

    editSubmit () {
      let _this = this
      if (_this.$refs.editEditor) {
        this.editForm.report_content = _this.$refs.editEditor.setContent()
      }
      this.$refs.editForm.validate((valid) => {
        if (valid) {
          this.editLoading = true
          API_CONTENT.editStockReport(_this.editReportId, _this.editForm).then(res => {
            if (res.code === 0) {
              _this.$message.success({showClose: true, message: '编辑成功', duration: 2000})
              this.updateList()
              _this.editVisible = false
              // setTimeout(() => {
              //   _this.showPreviewDialog(res.data.article.id)
              // }, 500)
            } else {
              _this.$message.error({showClose: true, message: '编辑失败: ' + res.msg, duration: 2000})
              // _this.editVisible = false
            }
            this.editLoading = false
          }).catch(err => {
            console.error(err)
            this.editLoading = false
          })
          if (_this.$refs.editCover) {
            _this.$refs.editCover.clearFiles()
          }
        }
      })
    },

    closeEditDialog () {
      if (this.$refs.editEditor) {
        this.$refs.editEditor.clear()
      }
      this.editVisible = false
    },

    // 预览报告
    showPreviewDialog (id) {
      let _this = this
      this.previewVisible = true
      API_CONTENT.getStockReport(id).then(res => {
        if (res.code === 0) {
          _this.preview = {
            id: id,
            title: res.data.stock_report.report_title,
            date: res.data.stock_report.report_date,
            summary: res.data.stock_report.report_summary,
            report_format: res.data.stock_report.report_format,
            content: res.data.stock_report.report_content,
            report_url: res.data.stock_report.report_url,
            author_teacher_name: res.data.stock_report.author_teacher_name,
            author_teacher_icon: res.data.stock_report.author_teacher_icon
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
    delReport (id) {
      this.$confirm('是否确定删除该报告?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        API_CONTENT.delStockReport(id).then(data => {
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

    // 发布
    pushReport (id) {
      if (this.workCalendars.length === 0) {
        this.$message.success({showClose: true, message: '发布失败： 获取节假日日历失败', duration: 2000})
      }
      API_CONTENT.getStockReport(id).then(res => {
        if (res.code === 0) {
          let reportDate = res.data.stock_report.report_date.replace(/-/g, '')
          let categoryId = res.data.stock_report.category_id
          if (categoryId === 2 && !this.checkReportDateRange(res.data.stock_report.report_date)) {
            this.$message.warning({showClose: true, message: '只能发布当前日期前后一个月的跟踪报告', duration: 2000})
          } else if (categoryId === 2 && this.workCalendars.indexOf(reportDate) === -1) {
            this.$message.warning({showClose: true, message: '报告日期必须为股票交易日', duration: 2000})
          } else {
            this.$confirm('发布后数据不能修改，请确认是否发布?', '提示', {
              confirmButtonText: '确定',
              cancelButtonText: '取消',
              type: 'warning'
            }).then(() => {
              API_CONTENT.pushStockReport(id).then(data => {
                if (data.code === 0) {
                  this.$message.success({showClose: true, message: '发布成功', duration: 2000})
                  this.updateList()
                } else {
                  this.$message({
                    type: 'info',
                    message: data.msg
                  })
                }
              }).catch(() => {
                this.$message({
                  type: 'info',
                  message: '发布失败'
                })
              })
            }).catch(() => {
              this.$message({
                type: 'info',
                message: '已取消发布'
              })
            })
          }
        } else {
          this.$message.error({showClose: true, message: '查询个股报告信息失败', duration: 2000})
        }
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

    // 检测code值
    addCheckStockCode (rule, value, callback) {
      if (value) {
        value = Number(value)
        // if (isNaN(value)) {
        //   return callback(new Error('股票代码只允许输入数字'))
        // } else
        if (this.addForm.stock_name === '') {
          return callback(new Error('请在下拉列表里面选择股票'))
        } else {
          callback()
        }
      }
    },

    editCheckStockCode (rule, value, callback) {
      if (value) {
        value = Number(value)
        // if (isNaN(value)) {
        //   return callback(new Error('股票代码只允许输入数字'))
        // } else
        console.log(this.editForm)
        if (this.editForm.stock_name === '') {
          return callback(new Error('请在下拉列表里面选择股票'))
        } else {
          return callback()
        }
      }
    },

    // ---------------------上传文件模块------------------------------
    uploadBefore (file) {
      console.log(file)
      // post请求中image类型为文件对象
      let fileType = [
        'application/pdf'
      ]
      let isPDF = false
      for (var i = 0; i < fileType.length; i++) {
        if (fileType[i] === file.type.toLowerCase()) {
          isPDF = true
        }
      }
      const isLt500K = 357 * 198 / 1024 <= 500
      if (!isPDF) {
        this.$message.error('上传文件只能是 PDF 格式!')
      }
      if (!isLt500K) {
        this.$message.error('上传图片大小不能超过 500k!')
      }
      if (isPDF && isLt500K) {
        this.uploadObj.file = file
      }
      return isPDF && isLt500K
    },
    // 上传文件成功
    addUploadSuccess (response, file, addUploadFile) {
      if (response.code === 0) {
        this.addForm.report_url = response.data.path
        console.log(file)
        this.addUploadFile = [{
          // name: response.data.path.substr(response.data.path.lastIndexOf('/') + 1),
          name: file.name,
          url: response.data.path
        }]
      } else {
        console.error(response.msg)
      }
    },
    // 上传文件成功
    editUploadSuccess (response, file, editUploadFile) {
      if (response.code === 0) {
        this.editForm.report_url = response.data.path
        this.editUploadFile = [{
          // name: response.data.path.substr(response.data.path.lastIndexOf('/') + 1),
          name: file.name,
          url: response.data.path
        }]
      } else {
        console.error(response.msg)
      }
    },

    // 上传文件失败
    uploadError (response, file, ImgFile) {
      console.error('上传失败，请重试！')
    },

    handleRemove (file, fileList) {
      this.addForm.report_url = ''
      this.editForm.report_url = ''
    },

    handleExceed (file, ImgFile) {
      this.$alert('只能上传一个文件')
    },

    // 更新上传文件
    updateUploadSuccess (response, file, ImgFile) {
      if (response.code === 0) {
        this.addForm.report_url = response.data.path
        this.addUploadFile = [{
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
      let article = document.getElementById('report')
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

     // 日期时间
    formatData (row, column, value) {
      let newVal = value.substring(0, 16)
      return newVal
    },

    // 获取当天时间
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
    margin-bottom: 8px;
  }
  .views {
    font-size: 12px;
    color: #999;
    line-height: 17px;
    .views-icon {
      border-radius: 50%;
      vertical-align: -3px;
      width: 17px;
      height: 17px;
    }
    span {
      color: #333;
    }
  }
  .date {
    font-size: 12px;
    color: #999;
    margin-left: 12px;
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
.de-input{
  width: 200px;
}

.stock-tip{
  font-size: 12px;
  position: absolute;
  top: 42px;
  left: 80px;
}

.mt15 {
  margin-top: 25px;
}
</style>
