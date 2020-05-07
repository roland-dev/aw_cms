<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>股票池管理</el-breadcrumb-item>
        <el-breadcrumb-item>量化股票池</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu"> 
      <el-row class="nav">
        <h3 class="inline">归属模式</h3>
        <ul>
          <li v-for="(mode,index) in stockPoolMode" :key="mode.value" 
            :class="{active: mode.isActive}" @click="changeMode(index)">{{mode.name}}</li>
        </ul>
        <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr">添加股票</el-button>
      </el-row>    
      <el-form :inline="true" :model="formInline">
        <el-row>
          <el-form-item label="股票代码">
            <el-input v-model="formInline.id" placeholder="股票代码"></el-input>
          </el-form-item>
          <el-form-item label="股票名称">
            <el-input v-model="formInline.name" placeholder="股票名称"></el-input>
          </el-form-item>
          <el-form-item label="股票名称">
            <el-date-picker
              v-model="formInline.date"
              type="daterange"
              range-separator="至"
              start-placeholder="开始日期"
              end-placeholder="结束日期">
            </el-date-picker>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item>
            <el-button type="primary" icon="el-icon-search" @click="onSearch" class="search" round>查询</el-button>
          </el-form-item>
        </el-row>
      </el-form>       
    </el-row>

    <!-- 列表 -->
    <el-row class="table-menu">
      <el-row class="mb5">
        <span class="ml10">入池时间：2017-12-16</span>
        <el-button type="primary" @click="exportTodayPool" round class="ml10">导出当天股票池</el-button>
      </el-row>
      <!-- 日股票池表格 -->
      <el-table
        :data="tableData"
        stripe
        style="width: 100%">
        <el-table-column fixed prop="code" label="股票代码"></el-table-column>
        <el-table-column prop="name" label="股票名称"></el-table-column>
        <el-table-column prop="nowPrice" label="最新价"></el-table-column>
        <el-table-column fixed="right" label="操作" width="130" align="center">
           <template slot-scope="scope">
            <el-dropdown>
              <el-button type="primary">
                股票池管理<i class="el-icon-arrow-down el-icon--right"></i>
              </el-button>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item @click.native="showEditDialog">编辑股票</el-dropdown-item>
                <el-dropdown-item @click.native="showStrategyDialog">查看策略</el-dropdown-item>
                <el-dropdown-item @click.native="delStock(scope.$index)">删除股票</el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
          </template>
        </el-table-column>
      </el-table>
    </el-row>

    <!-- 添加股票 -->
    <el-dialog title="添加股票" :visible.sync ="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm"  :inline="true">

        <h3>股票信息</h3>
        <el-form-item label="股票代码" prop="code">
          <el-input v-model="addForm.code" auto-complete="off"></el-input>
        </el-form-item>

        <h3>入池信息</h3>
        <el-row>   
          <el-form-item label="归属模式" prop="mode">
            <el-select v-model="addForm.mode" placeholder="请选择归属模式">
              <el-option v-for="mode in stockPoolMode" :key="mode.value" :label="mode.name" :value="mode.value"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-form-item label="入池时间">
          <el-radio-group v-model="addForm.enterWay" @change="changeEnterWay">
            <el-radio :label="1">立即入池</el-radio>
            <el-radio :label="2">定时入池</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="选择日期">
          <el-date-picker type="date" placeholder="选择日期" v-model="addForm.date" :disabled="addDateDisable"></el-date-picker>
        </el-form-item>

        <h3>策略信息</h3>
        <el-row>
          <el-form-item label="发布作者" prop="author">
            <el-input v-model="addForm.author"></el-input>
          </el-form-item>
        </el-row>
        <el-form-item label="策略正文" prop="article">
          <editor editorId="add-article" v-model="addForm.article"></editor>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确定</el-button>
        <el-button @click.native="addVisible = false">取消</el-button>        
      </div>
    </el-dialog>

    <!-- 编辑股票 -->
    <el-dialog title="编辑股票" :visible.sync ="editVisible" :close-on-click-modal="false" center>
      <el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm" :inline="true">

        <h3 class="mb5">股票信息</h3>
        <el-form-item label="股票代码" prop="code">
          <el-input v-model="editForm.code" auto-complete="off"></el-input>
        </el-form-item>

        <h3>入池信息</h3>
        <el-row>
          <el-form-item label="归属模式" prop="mode">
            <el-select v-model="editForm.mode" placeholder="请选择归属模式">
              <el-option v-for="mode in stockPoolMode" :key="mode.value" :label="mode.name" :value="mode.value"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-form-item label="入池时间">
          <el-radio-group v-model="editForm.enterWay">
            <el-radio label="立即入池"></el-radio>
            <el-radio label="定时入池"></el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="出版日期">
          <el-date-picker type="date" placeholder="选择日期" v-model="editForm.date"></el-date-picker>
        </el-form-item>
        

        <h3>策略信息</h3>
        <el-row>
          <el-form-item label="发布作者" prop="author">
            <el-input v-model="editForm.author"></el-input>
          </el-form-item>
        </el-row>
        <el-form-item label="策略正文" prop="article">
          <div>
            <editor editorId="edit-article" v-model="editForm.article"></editor>
          </div>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="editSubmit" :loading="editLoading">确定</el-button>
        <el-button @click.native="editVisible = false">取消</el-button>        
      </div>
    </el-dialog>

    <!-- 查看策略 -->
    <el-dialog title="查看策略" :visible.sync ="strategyVisible" :close-on-click-modal="false" center>
      <el-form :model="strategyForm" label-width="80px" :rules="strategyFormRules" ref="strategyForm" :inline="true">

        <h3>股票信息</h3>
        <el-row>
          <el-form-item label="股票代码" prop="code">
            <el-input v-model="strategyForm.code" auto-complete="off" :disabled="true"></el-input>
          </el-form-item>
        </el-row>
        <el-form-item label="归属模式" prop="mode">
          <el-select v-model="strategyForm.mode" placeholder="请选择归属模式" :disabled="true">
            <el-option v-for="mode in stockPoolMode" :key="mode.value" :label="mode.name" :value="mode.value"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="入池时间">
          <el-date-picker type="date" placeholder="选择日期" v-model="strategyForm.date" :disabled="true"></el-date-picker>
        </el-form-item>

        <h3>策略信息</h3>
        <el-row>
          <el-form-item label="发布作者" prop="author">
            <el-input v-model="strategyForm.author" :disabled="true"></el-input>
          </el-form-item>
        </el-row>
        <el-form-item label="策略正文" prop="article">
          <editor editorId="strategy-article" v-model="strategyForm.article"></editor>  
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click.native="strategyVisible = false">关闭</el-button>        
      </div>
    </el-dialog>
  </div> 
</template>

<script>
import Editor from '@/components/Editor' // 调用编辑器

export default {
  name: 'QuantityStockPool',
  data () {
    return {
      msg: 'Welcome to Your Vue.js App',
      stockPoolMode: [
        {
          name: '强势调整',
          value: 1,
          isActive: true
        }, {
          name: '超跌反弹',
          value: 2,
          isActive: false
        }, {
          name: '主力梅花桩',
          value: 3,
          isActive: false
        }, {
          name: '夹板图片',
          value: 4,
          isActive: false
        }
      ],
      formInline: {
        id: '',
        name: '',
        date: ''
      },
      tableData: [
        {
          code: '000001',
          name: '上证指数',
          nowPrice: '3322'
        }, {
          code: '000001',
          name: '上证指数',
          nowPrice: '3322'
        }, {
          code: '000001',
          name: '上证指数',
          nowPrice: '3322'
        }, {
          code: '000001',
          name: '上证指数',
          nowPrice: '3322'
        }
      ],
      // 新增股票初始化
      addVisible: false, // 是否显示
      addLoading: false,
      addDateDisable: true, // 是否给定时时间添加disable效果
      editorOption: {},
      addFormRules: {
        name: [
          {required: true, message: '请输入书名', trigger: 'blur'}
        ],
        author: [
          {required: true, message: '请输入作者', trigger: 'blur'}
        ],
        description: [
          {required: true, message: '请输入简介', trigger: 'blur'}
        ]
      },
      addForm: {
        code: '',
        mode: '',
        isNow: '',
        enterWay: 1,
        date: '',
        author: '',
        article: '这是添加文章内的内容'
      },
      // 编辑股票初始化
      editVisible: false, // 是否显示
      editLoading: false,
      editFormRules: {
        name: [
          {required: true, message: '请输入书名', trigger: 'blur'}
        ],
        author: [
          {required: true, message: '请输入作者', trigger: 'blur'}
        ],
        description: [
          {required: true, message: '请输入简介', trigger: 'blur'}
        ]
      },
      editForm: {
        code: '',
        mode: '',
        isNow: '',
        enterWay: 1,
        date: '',
        author: '',
        article: ''
      },
      // 编辑股票初始化
      strategyVisible: false, // 是否显示
      strategyLoading: false,
      strategyFormRules: {
        name: [
          {required: true, message: '请输入书名', trigger: 'blur'}
        ],
        author: [
          {required: true, message: '请输入作者', trigger: 'blur'}
        ],
        description: [
          {required: true, message: '请输入简介', trigger: 'blur'}
        ]
      },
      strategyForm: {
        code: '',
        mode: '',
        isNow: '',
        enterWay: 1,
        date: '',
        author: '',
        article: ''
      }
    }
  },
  components: {
    Editor  // 引入wangEditor富文本编辑器模块
  },
  methods: {
    onSearch () {
      console.log('search!')
      let top = document.getElementsByClassName('top-title')
      top[0].classList.add('aaaa')
      // let top = this.$el.querySelect('.top-menu')
      console.log(top)
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
    exportTodayPool () {
      console.log('Today!')
    },

    // 添加股票 dialog
    showAddDialog () {
      this.addVisible = true
    },
    changeEnterWay () {
      if (this.addForm.enterWay === 2) {
        this.addDateDisable = false
      } else {
        this.addForm.date = ''
        this.addDateDisable = true
      }
    },
    onEditorReady (editor) {
      console.log('添加编辑器成功')
    },
    addSubmit () {
      let _this = this
      this.$refs.addForm.validate((valid) => {
        if (valid) {
          _this.$message.success({showClose: true, message: '新增成功', duration: 2000})
          _this.addVisible = false
        }
      })
    },

    // 编辑股票 dialog
    showEditDialog () {
      this.editVisible = true
    },
    editSubmit () {
      let _this = this
      this.$refs.editForm.validate((valid) => {
        if (valid) {
          _this.$message.success({showClose: true, message: '编辑成功', duration: 2000})
          _this.editVisible = false
        }
      })
    },

    // 查看策略 dialog
    showStrategyDialog () {
      this.strategyVisible = true
    },
    delStock (index) {
      console.log(index)
    }
  }
}
</script>

<style scoped lang="less">

</style>
