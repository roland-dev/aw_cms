<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>宣传管理</el-breadcrumb-item>
        <el-breadcrumb-item>跑马灯管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu">
      <el-row class="nav clearfix">
        <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr" >添加跑马灯</el-button>
      </el-row>
      <el-form :inline="true" :model="formInline">
        <el-row>
          <el-form-item label="展示开始时间">
            <el-date-picker
              v-model="formInline.start_at"
              align="right"
              type="datetime"
              value-format="yyyy-MM-dd HH:mm:ss"
              format="yyyy-MM-dd HH:mm:ss"
              placeholder="选择日期">
            </el-date-picker>
          </el-form-item>
          <el-form-item label="展示结束时间">
            <el-date-picker
              v-model="formInline.end_at"
              align="right"
              type="datetime"
              value-format="yyyy-MM-dd HH:mm:ss"
              format="yyyy-MM-dd HH:mm:ss"
              placeholder="选择日期">
            </el-date-picker>
          </el-form-item>
          <el-form-item label="内容来源">
            <el-select v-model="formInline.source_type" clearable placeholder="请选择">
              <el-option v-for="item in sourceTypes" :value-key="item.code" :key="item.code" :label="item.name" :value="item.code"></el-option>
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
      <el-table
        :data="tablePageData"
        stripe
        style="width: 100%">
        <el-table-column fixed prop="title" label="文字内容"></el-table-column>
        <el-table-column prop="start_at" label="展示开始日期"></el-table-column>
        <el-table-column prop="end_at" label="展示结束日期"></el-table-column>
        <el-table-column prop="source_type_name" label="来源"></el-table-column>
        <el-table-column label="发布状态" width="100">
          <template slot-scope="scope">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              active-color="#13ce66"
              inactive-color="#999"
              v-model="scope.row.active"
              @change="changeActive(scope.row)">
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="标红状态" width="100">
          <template slot-scope="scope">
            <el-switch
              :active-value="1"
              :inactive_value="0"
              active-color="#13ce66"
              inactive-color="#999"
              v-model="scope.row.sign"
              @change="changeSign(scope.row)">
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column prop="last_modify_user_name" label="最后修改人"></el-table-column>
        <el-table-column prop="updated_at" label="最后修改时间"></el-table-column>
        <el-table-column fixed="right" align="center" label="操作" width="120">
          <template slot-scope="scope">
            <el-dropdown>
              <el-button type="primary">
                跑马灯管理<i class="el-icon-arrow-down el-icon--right"></i>
              </el-button>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item @click.native="showEditDialog(scope.row.id)" v-if="scope.row.source_type === 'added'">编辑跑马灯</el-dropdown-item>
                <el-dropdown-item @click.native="showDynamicAd(scope.row.id)">查看跑马灯</el-dropdown-item>
                <el-dropdown-item @click.native="delDynamicAd(scope.row.id)" v-if="scope.row.source_type === 'added'">删除跑马灯</el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <!-- 添加跑马灯 -->
    <el-dialog title="添加跑马灯" :visible.sync="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">
        <el-row>
          <el-form-item label="文字内容" prop="title">
            <el-input v-model="addForm.title" placeholder="最多输入30个字" :maxlength="30"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="链接" prop="content_url">
            <el-input v-model="addForm.content_url" placeholder="请输入" :maxlength="255"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="跳转类型">
            <el-input v-model="addForm.jump_type" placeholder="请输入跳转类型" :maxlength="50"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="跳转参数" prop="jump_params">
            <el-input type="textarea" v-model="addForm.jump_params" placeholder="请输入跳转参数" :maxlength="500"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="展示时间" prop="show_time">
            <el-date-picker
              v-model="addForm.show_time"
              type="datetimerange"
              start-placeholder="开始时间"
              end-placeholder="结束时间"
              value-format="yyyy-MM-dd HH:mm:ss"
              format="yyyy-MM-dd HH:mm:ss">
            </el-date-picker>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="展示终端" prop="terminal_codes">
            <el-checkbox-group v-model="addForm.terminal_codes">
              <el-checkbox v-for="item in terminals" :label="item.code" :key="item.code" border size="mini">{{item.name}}</el-checkbox>
            </el-checkbox-group>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="可见人群" prop="permission_codes">
            <div v-for="(item, i) in permission_array" :key="item.code">
              <el-checkbox :indeterminate="checkedPackageCodes[i] && checkedPackageCodes[i].length > 0 && checkedPackageCodes[i].length < countArrItemNum(permission_array[i]['child'])"
                v-model="item.granted" @change="handleCheckAllChange(item.granted, i)">
                {{item.name}}
              </el-checkbox>
              <div v-for="(arr, j) in permission_array[i]['child']" :key="arr[0].name">
                <el-checkbox-group v-model="checkedPackageCodes[i]" @change="handleCheckedPackageCodesChangeOfAdd(i)" style="padding-left:60px">
                  <el-checkbox v-for="packageCode in permission_array[i]['child'][j]" :label="packageCode.code" :key="packageCode.code" border size="mini">
                    {{packageCode.name}}
                  </el-checkbox>
                </el-checkbox-group>
              </div>
            </div>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="是否发布" prop="active">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              active-color="#13ce66"
              inactive-color="#999"
              v-model="addForm.active">
            </el-switch>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="是否标红" prop="sign">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              active-color="#13ce66"
              inactive-color="#999"
              v-model="addForm.sign">
            </el-switch>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确认</el-button>
        <el-button @click.native="addVisible = false">取消</el-button>
      </div>
    </el-dialog>

    <!-- 编辑跑马灯 -->
    <el-dialog title="编辑跑马灯" :visible.sync="editVisible" :close-on-click-modal="false" center>
      <el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">
        <el-row>
          <el-form-item label="文字内容" prop="title">
            <el-input v-model="editForm.title" placeholder="最多输入30个字" :maxlength="320"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="链接" prop="content_url">
            <el-input v-model="editForm.content_url" placeholder="请输入" :maxlength="255"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="跳转类型">
            <el-input v-model="editForm.jump_type" placeholder="请输入跳转类型" :maxlength="50"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="跳转参数" prop="jump_params">
            <el-input type="textarea" v-model="editForm.jump_params" placeholder="请输入跳转参数" :maxlength="500"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="展示时间" prop="show_time">
            <el-date-picker
              v-model="editForm.show_time"
              type="datetimerange"
              start-placeholder="开始时间"
              end-placeholder="结束时间"
              value-format="yyyy-MM-dd HH:mm:ss"
              format="yyyy-MM-dd HH:mm:ss">
            </el-date-picker>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="展示终端" prop="terminal_codes">
            <el-checkbox-group v-model="editForm.terminal_codes">
              <el-checkbox v-for="item in terminals" :label="item.code" :key="item.code" border size="mini">{{item.name}}</el-checkbox>
            </el-checkbox-group>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="可见人群" prop="permission_codes">
            <div v-for="(item, i) in permission_array" :key="item.code">
              <el-checkbox :indeterminate="checkedPackageCodes[i] && checkedPackageCodes[i].length > 0 && checkedPackageCodes[i].length < countArrItemNum(permission_array[i]['child'])"
                v-model="item.granted" @change="handleCheckAllChange(item.granted, i)">
                {{item.name}}
              </el-checkbox>
              <div v-for="(arr, j) in permission_array[i]['child']" :key="arr[0].name">
                <el-checkbox-group v-model="checkedPackageCodes[i]" @change="handleCheckedPackageCodesChangeOfEdit(i)" style="padding-left:60px">
                  <el-checkbox v-for="packageCode in permission_array[i]['child'][j]" :label="packageCode.code" :key="packageCode.code" border size="mini">
                    {{packageCode.name}}
                  </el-checkbox>
                </el-checkbox-group>
              </div>
            </div>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="是否发布" prop="active">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              active-color="#13ce66"
              inactive-color="#999"
              v-model="editForm.active">
            </el-switch>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="是否标红" prop="sign">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              active-color="#13ce66"
              inactive-color="#999"
              v-model="editForm.sign">
            </el-switch>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="editSubmit" :loading="editLoading">确定</el-button>
        <el-button @click.native="editVisible = false">取消</el-button>
      </div>
    </el-dialog>

    <!-- 查看跑马灯 -->
    <el-dialog title="查看跑马灯" :visible.sync="showVisible" :close-on-click-modal="false" center>
      <el-form :model="showForm" label-width="80px">
        <el-row>
          <el-form-item label="文字内容">
            {{showForm.title}}
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="链接">
            {{showForm.content_url}}
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="跳转类型">
            {{showForm.jump_type}}
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="跳转参数">
            {{showForm.jump_params}}
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="展示时间">
            {{showForm.start_at}} -- {{showForm.end_at}}
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="展示终端">
            {{showForm.terminal_codes}}
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="可见人群">
            {{showForm.permission_codes}}
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="是否发布">
            {{showForm.active}}
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="是否标红">
            {{showForm.sign}}
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click.native="showVisible = false">关闭</el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
import HTTP from '../../http/api_propaganda'
import Pagination from '@/components/Pagination'

export default {
  name: 'DynamicAd',
  data () {
    return {
      sourceTypes: [],    // 来源列表

      // 搜索区表单
      formInline: {start_at: this.getNowFormatDate('00:00:00'), end_at: this.getNowFormatDate('23:59:59'), source_type: ''},

      // 缓存搜索数据
      searchParams: {start_at: this.getNowFormatDate('00:00:00'), end_at: this.getNowFormatDate('23:59:59'), source_type: ''},

      // 表格内容
      tablePageData: [],

      // 分页初始化
      totalAll: 0,        // 列表总数目
      pageSize: 10,       // 分页尺寸
      pageNo: 1,          // 当前页
      pageRefresh: true,  // 分页内容刷新

      // 可见人群
      packages: [],
      permission_array: [],
      checkedPackageCodes: [],

      // 展示需要
      codeType: [],

      // 展示终端列表
      terminals: [],

      // 新建跑马灯
      addVisible: false,
      addLoading: false,
      addFormRules: {
        title: [
          {required: true, message: '请输入文字内容', trigger: 'blur'},
          {max: 30, message: '请输入内容最大长度不超过30个字', trigger: 'blur'}
        ],
        content_url: [
          {required: true, type: 'url', message: '请输入链接地址', trigger: 'blur'}
        ],
        jump_params: [
          {validator: this.checkJSONType, trigger: 'blur'}
        ],
        show_time: [
          {type: 'array', required: true, message: '请选择展示时间', trigger: 'blur'}
        ],
        terminal_codes: [
          {type: 'array', required: true, message: '请选择展示终端', trigger: 'blur'}
        ],
        permission_codes: [
          {required: true, validator: this.checkPermissionCode, trigger: 'blur'}
        ],
        active: [
          {required: true}
        ],
        sign: [
          {required: true}
        ]
      },
      addForm: {
        title: '',
        content_url: '',
        jump_type: '',
        jump_params: '',
        start_at: '',
        end_at: '',
        terminal_codes: [],
        permission_codes: [],
        active: 1,
        sign: 0
      },

      // 编辑跑马灯
      editVisible: false,
      editLoading: false,
      editFormRules: {
        title: [
          {required: true, message: '请输入文本内容', trigger: 'blur'},
          {max: 30, message: '请输入内容最大长度不超过30个字', trigger: 'blur'}
        ],
        content_url: [
          {required: true, type: 'url', message: '请输入链接地址', trigger: 'blur'}
        ],
        jump_params: [
          {validator: this.checkJSONType, trigger: 'blur'}
        ],
        show_time: [
          {type: 'array', required: true, message: '请选择展示时间', trigger: 'blur'}
        ],
        terminal_codes: [
          {type: 'array', required: true, message: '请选择展示终端', trigger: 'blur'}
        ],
        permission_codes: [
          {required: true, validator: this.checkPermissionCode, trigger: 'blur'}
        ],
        active: [
          {required: true}
        ],
        sign: [
          {required: true}
        ]
      },
      editForm: {
        title: '',
        content_url: '',
        jump_type: '',
        jump_param: '',
        start_at: '',
        end_at: '',
        terminal_codes: [],
        permission_codes: [],
        active: 1,
        sign: 0
      },

      // 查看跑马灯
      showVisible: false,
      showForm: {
        title: '',
        content_url: '',
        jump_type: '',
        jump_params: '',
        start_at: '',
        end_at: '',
        terminal_codes: '',
        permission_codes: '',
        active: 1,
        sign: 0
      }
    }
  },
  components: {
    Pagination
  },
  created: function () {
  },
  mounted: function () {
    this.getSourceTypes()
    this.getPackages()
    this.getTerminals()
    this.getList()
  },
  methods: {
    // 获取内容来源列表
    getSourceTypes () {
      HTTP.getSourceTypes().then(res => {
        if (res.code === 0) {
          this.sourceTypes = res.data.source_types
        } else {
          this.$message.error({showClose: true, message: '获取内容来源列表数据错误：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取套餐列表
    getPackages () {
      let _this = this
      HTTP.getPackages().then(res => {
        if (res.code === 0) {
          _this.packages = res.data
          res.data.forEach(d => {
            if (d.child) {
              d.child.forEach(child => {
                child.forEach(item => {
                  _this.codeType.push({'code': item.code, 'name': item.name})
                })
              })
            }
          })
        } else {
          console.log('服务器错误！')
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取展示终端
    getTerminals () {
      HTTP.getDynamicAdTerminals().then(res => {
        if (res.code === 0) {
          this.terminals = res.data.terminals
        } else {
          console.log(res.msg)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    initPagination () {
      this.pageRefresh = false
      this.$nextTick(() => { this.pageRefresh = true })
    },

    gotoPage (page) {
      this.pageNo = page
      this.getList()
    },

    updateList () {
      this.getList()
    },

    getList () {
      var params = Object.assign({}, this.searchParams)
      params.page_no = this.pageNo
      params.page_size = this.pageSize
      HTTP.getDynamicAdList(this.filterParams(params)).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.dynamic_ad_list
          this.totalAll = res.data.dynamic_ad_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '获取跑马灯列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    onSearch () {
      this.pageNo = 1
      this.searchParams = this.formInline
      let searchParams = this.filterParams(this.searchParams)
      HTTP.searchDynamicAdList(searchParams).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.dynamic_ad_list
          this.totalAll = res.data.dynamic_ad_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '查询失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    KeySearch (env) {
      this.pageNo = 1
      this.searchParams = this.formInline
      let searchParams = this.filterParams(this.searchParams)
      HTTP.searchDynamicAdList(searchParams).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.dynamic_ad_list
          this.totalAll = res.data.dynamic_ad_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '查询失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    changeActive (row) {
      let activeStatus = row.active === 1 ? 1 : 0
      HTTP.changeActive(row.id, activeStatus).then(res => {
        if (res.code === 0) {
          console.log('发布状态改变')
        }
      }).catch(err => {
        console.error(err)
      })
    },

    changeSign (row) {
      let signStatus = row.sign === 1 ? 1 : 0
      HTTP.changeSign(row.id, signStatus).then(res => {
        if (res.code === 0) {
          console.log('标红状态改变')
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // validator
    checkJSONType (rule, value, callback) {
      if (!value || value.length === 0) {
        callback()
      } else {
        if (typeof value === 'string') {
          try {
            JSON.parse(value)
            callback()
          } catch (e) {
            console.log(e)
            callback(new Error('格式错误，请输入正确的JSON格式'))
          }
        } else {
          callback(new Error('格式错误，请输入正确的JSON格式'))
        }
      }
    },

    checkPermissionCode (rule, value, callback) {
      let permissionCodes = []
      this.checkedPackageCodes.forEach(d => {
        permissionCodes = permissionCodes.concat(d)
      })
      if (permissionCodes.length <= 0) {
        callback(new Error('至少选择一个选项'))
      } else {
        callback()
      }
    },

    handleCheckAllChange (val, i) {
      this.checkedPackageCodes[i] = val ? this.getAllArrayItem(this.permission_array[i]['child']) : []
    },

    handleCheckedPackageCodesChangeOfAdd (i) {
      this.permission_array[i].granted = this.checkedPackageCodes[i].length === this.countArrItemNum(this.permission_array[i]['child'])
    },

    handleCheckedPackageCodesChangeOfEdit (i) {
      this.permission_array[i].granted = this.checkedPackageCodes[i].length === this.countArrItemNum(this.permission_array[i]['child'])
    },

    getAllArrayItem (arr) {
      let resultArr = []
      arr.forEach(item => {
        item.forEach(packageItem => {
          resultArr.push(packageItem.code)
        })
      })
      return resultArr
    },

    countArrItemNum (arr) {
      let num = 0
      arr.forEach(item => {
        num = num + item.length
      })
      return num
    },

    // 新建跑马灯
    showAddDialog () {
      let _this = this
      setTimeout(() => {
        _this.addVisible = true
      }, 500)
      this.checkedPackageCodes = []
      _this.addForm = {
        title: '',
        content_url: '',
        jump_type: '',
        jump_params: '',
        start_at: '',
        end_at: '',
        terminal_codes: [],
        permission_codes: [],
        active: 1,
        sign: 0
      }

      // 处理 可见人群 列表
      if (this.packages.length > 0) {
        this.permission_array = JSON.parse(JSON.stringify(this.packages))
        this.permission_array.forEach(d => {
          this.checkedPackageCodes.push([])
        })
      }

      setTimeout(() => {
        _this.$refs.addForm.clearValidate()
      }, 600)
    },

    addSubmit () {
      let _this = this
      this.$refs.addForm.validate((valid) => {
        if (valid) {
          _this.addLoading = true
          _this.addForm.start_at = _this.addForm.show_time[0]
          _this.addForm.end_at = _this.addForm.show_time[1]
          let permissionCodes = []
          _this.checkedPackageCodes.forEach(d => {
            permissionCodes = permissionCodes.concat(d)
          })
          _this.addForm.permission_codes = permissionCodes
          let addFormData = Object.assign({}, _this.addForm)
          delete addFormData.show_time

          console.log(addFormData)
          HTTP.addDynamicAd(addFormData).then(res => {
            if (res.code === 0) {
              _this.$message.success({showClose: true, message: '新增成功', duration: 2000})
              _this.updateList()
              _this.addVisible = false
            } else {
              this.$message.error({showClose: true, message: '新增失败：' + res.msg, duration: 2000})
            }
            _this.addLoading = false
          }).catch(err => {
            console.error(err)
            _this.addLoading = false
          })
        }
      })
    },

    showEditDialog (id) {
      let _this = this
      this.checkedPackageCodes = []
      setTimeout(() => {
        _this.editVisible = true
      })
      HTTP.getDynamicAd(id).then(res => {
        if (res.code === 0) {
          let dynamicAd = res.data.dynamic_ad
          _this.editForm = {
            id: dynamicAd.id,
            title: dynamicAd.title,
            content_url: dynamicAd.content_url,
            jump_type: dynamicAd.jump_type,
            jump_params: dynamicAd.jump_params,
            start_at: dynamicAd.start_at,
            end_at: dynamicAd.end_at,
            show_time: [dynamicAd.start_at, dynamicAd.end_at],
            terminal_codes: dynamicAd.terminal_codes,
            permission_codes: dynamicAd.permission_codes,
            active: dynamicAd.active,
            sign: dynamicAd.sign
          }

          this.permission_array = dynamicAd.permission_array
          if (dynamicAd.permission_array) {
            let i = 0
            this.permission_array.forEach(d => {
              let checkedPackageCode = []
              if (d.child) {
                d.child.forEach(child => {
                  child.forEach(item => {
                    if (item.granted) {
                      checkedPackageCode.push(item.code)
                    }
                  })
                })
              }
              this.checkedPackageCodes.push(checkedPackageCode)
              this.permission_array[i].granted = checkedPackageCode.length === this.countArrItemNum(this.permission_array[i]['child'])
              i++
            })
          }

          setTimeout(() => {
            _this.$refs.editForm.clearValidate()
          })
        } else {
          this.$message.error({showClose: true, message: '查询失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    editSubmit () {
      let _this = this
      this.$refs.editForm.validate((valid) => {
        if (valid) {
          _this.editLoading = true
          _this.editForm.start_at = _this.editForm.show_time[0]
          _this.editForm.end_at = _this.editForm.show_time[1]
          let permissionCodes = []
          _this.checkedPackageCodes.forEach(d => {
            permissionCodes = permissionCodes.concat(d)
          })
          _this.editForm.permission_codes = permissionCodes
          let editFormData = Object.assign({}, _this.editForm)
          delete editFormData.show_time

          HTTP.editDynamicAd(editFormData.id, editFormData).then(res => {
            if (res.code === 0) {
              _this.$message.success({showClose: true, message: '新增成功', duration: 2000})
              _this.updateList()
              _this.editVisible = false
            } else {
              this.$message.error({showClose: true, message: '新增失败：' + res.msg, duration: 2000})
            }
            _this.editLoading = false
          }).catch(err => {
            console.error(err)
            _this.addLoading = false
          })
        }
      })
    },

    delDynamicAd (dynamicAdId) {
      this.$confirm('是否确认删除该锦囊报告?', '提示', {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        HTTP.delDynamicAd(dynamicAdId).then(res => {
          if (res.code === 0) {
            this.$message.success({showClose: true, message: '删除成功', duration: 2000})
            this.updateList()
          } else {
            this.$message.error({showClose: true, message: '删除失败：' + res.msg, duration: 2000})
          }
        }).catch(err => {
          console.error(err)
        })
      }).catch(() => {
        this.$message({type: 'info', message: '已取消删除'})
      })
    },

    showDynamicAd (dynamicAdId) {
      let _this = this
      _this.showVisible = true
      HTTP.getDynamicAd(dynamicAdId).then(res => {
        if (res.code === 0) {
          let dynamicAd = res.data.dynamic_ad
          _this.showForm = {
            id: dynamicAd.id,
            title: dynamicAd.title,
            content_url: dynamicAd.content_url,
            jump_type: dynamicAd.jump_type ? dynamicAd.jump_type : '无',
            jump_params: dynamicAd.jump_params ? dynamicAd.jump_params : '无',
            start_at: dynamicAd.start_at,
            end_at: dynamicAd.end_at,
            terminal_codes: this.getTypesByCode(this.terminals, dynamicAd.terminal_codes),
            permission_codes: this.getTypesByCode(this.codeType, dynamicAd.permission_codes),
            active: dynamicAd.active === 1 ? '已发布' : '未发布',
            sign: dynamicAd.sign === 1 ? '已标红' : '未标红'
          }
        } else {
          _this.$message.error({showClose: true, message: '查询失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取数组
    getTypesByCode (arr, args) {
      let result = []
      args.forEach(d => {
        result.push(this.getTypeByCode(arr, d))
      })
      return result.join(',')
    },

    // 获取类型type
    getTypeByCode (arr, code) {
      let result
      arr.forEach(d => {
        if (d.code === code) {
          result = d.name
        }
      })
      return result
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

    getNowFormatDate (time) {
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
      return currentdate + ' ' + time
    }
  }
}
</script>