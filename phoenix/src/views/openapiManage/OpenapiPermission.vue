<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>接口管理</el-breadcrumb-item>
        <el-breadcrumb-item>接口权限管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu"> 
      <el-form :inline="true" :model="formInline">
        <el-row>
          <el-form-item label="用户姓名" prop="name"> 
            <el-input v-model="formInline.name" placeholder="请输入"></el-input>  
          </el-form-item>
          <!-- <el-form-item label="权限分类">
            <el-select v-model="formInline.code" clearable placeholder="请选择">
              <el-option v-for="item in codeType" :value-key="item.code" :key="item.code" :label="item.code" :value="item.code"></el-option>
            </el-select>
          </el-form-item>   -->
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
      <!-- 权限列表 -->
      <el-table
        :data="tablePageData"
        stripe
        style="width: 100%">
        <el-table-column fixed prop="name" label="名称"></el-table-column>
        <el-table-column prop="permission" label="权限分类"></el-table-column>
        <el-table-column prop="remark" label="简介"></el-table-column>
        <el-table-column prop="created_at" label="创建时间"></el-table-column>
        <el-table-column
          fixed="right"
          align="center"
          label="操作"
          width="100"
          >
          <template slot-scope="scope">
            <el-button  @click.native="editOpenApiPermission(scope.$index)" type="text" size="small">编辑</el-button>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <!-- 编辑权限 -->
    <el-dialog title="编辑权限" :visible.sync ="editVisible" :close-on-click-modal="false" center>
      <el-form :model="editForm" label-width="80px" ref="editForm">
        <el-row>
          <el-form-item label="名称" prop="name">
            {{editForm.name}}
          </el-form-item>
        </el-row>       
        <el-row>
          <el-form-item label="code值" prop="code">
            {{editForm.code}}
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="简介" prop="remark">
            {{editForm.remark}}
          </el-form-item>
        </el-row>  
        <el-row>
          <el-form-item label="绑定权限" prop="type">
            <div v-for="(item,i) in editForm.right" :key="item.code">
                <el-checkbox :indeterminate="checkedRights[i].length > 0 && checkedRights[i].length < rights[i].length" v-model="item.granted" @change="handleCheckAllChange(item.granted, i)">{{item.name}}</el-checkbox>
                <el-checkbox-group v-model="checkedRights[i]" @change="handleCheckedRightsChange(i)" style="padding-left: 60px;">
                  <el-checkbox v-for="name in rights[i]" :label="name" :key="name" border size="mini">{{name}}</el-checkbox>
                </el-checkbox-group>
            </div>
          </el-form-item>
        </el-row> 
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="editSubmit" :loading="editLoading">确定</el-button>
        <el-button @click.native="editVisible = false">取消</el-button>        
      </div>
    </el-dialog>
  </div> 
</template>

<script>
import API_OPENAPI from '../../http/api_openapi'
import Pagination from '@/components/Pagination'

export default {
  name: 'Permission',
  data () {
    return {
      // 搜索区表单
      formInline: {name: '', type: '', code: ''},

      // 缓存搜索数据
      searchParams: {name: '', type: '', code: ''},

      totalAll: 0,          // 列表总数目
      pageSize: 10,         // 分页显示数目
      pageNo: 1,            // 当前页码
      pageRefresh: true,    // 分页内容刷新

      tablePageData: [],    // 分页显示数据
      signType: [],         // 标记类型
      codeType: [],         // code类型
      // 编辑视频海报
      editVisible: false, // 是否显示
      editLoading: false,
      editForm: {name: '', type: '', right: [], info: ''},

      // 添加checkbox选择
      checkedRights: [],
      rights: []
    }
  },
  components: {
    Pagination
  },
  mounted: function () {
    this.getList()
    this.getPermission()
  },
  methods: {
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
      let params = Object.assign({}, this.searchParams)
      params.page_no = this.pageNo
      params.page_size = this.pageSize
      API_OPENAPI.getCodeList(params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.custom_app_list
          this.totalAll = res.data.custom_app_cnt
          this.initPagination()
        } else {
          this.$message.error({showClose: true, message: '获取接口权限列表失败：' + res.msg, duration: 2000})
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // getSignType () {
    //   let typeArr = []
    //   this.tableData.forEach(d => {
    //     if (typeArr.indexOf(d.type) < 0) {
    //       typeArr.push(d.type)
    //     }
    //   })
    //   this.signType = typeArr
    // },

    getPermission () {
      API_OPENAPI.getPermission().then(data => {
        data.data.forEach(d => {
          this.codeType.push({'code': d.code, 'name': d.name})
          if (d.child) {
            d.child.forEach(child => {
              this.codeType.push({'code': child.code, 'name': child.name})
            })
          }
        })
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
      API_OPENAPI.searchCodeList(searchParams).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.custom_app_list
          this.totalAll = res.data.custom_app_cnt
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

    // 编辑用户权限 dialog
    editOpenApiPermission (index) {
      this.rights = []
      this.checkedRights = []
      API_OPENAPI.getPermission(this.tablePageData[index].code).then(data => {
        console.log(data)
        this.editForm = {
          id: this.tablePageData[index].id,
          name: this.tablePageData[index].name,
          code: this.tablePageData[index].code,
          right: data.data,
          remark: this.tablePageData[index].remark
        }
        if (data.data) {
          data.data.forEach(d => {
            let right = []
            let checkedRight = []
            if (d.child) {
              d.child.forEach(child => {
                right.push(child.name)
                if (child.granted) {
                  checkedRight.push(child.name)
                }
              })
            }
            this.rights.push(right)
            this.checkedRights.push(checkedRight)
          })
        }
        console.log(this.rights)
        console.log(this.checkedRights)
        this.editVisible = true
      }).catch(err => {
        console.error(err)
      })
    },

    editSubmit () {
      let _this = this
      this.$refs.editForm.validate((valid) => {
        if (valid) {
          // 生成post的权限列表
          let codeList = []
          let nameList = []
          _this.checkedRights.forEach(d => {
            nameList = nameList.concat(d)
          })
          _this.codeType.forEach(d => {
            if (nameList.indexOf(d.name) !== -1) {
              codeList.push(d.code)
            }
          })
          let grant = {
            code: _this.editForm.code,
            guard_list: codeList
          }
          API_OPENAPI.guardPermission(grant).then(data => {
            _this.$message.success({showClose: true, message: '编辑成功', duration: 2000})
            _this.editVisible = false
            this.updateList()
          }).catch(err => {
            console.error(err)
          })
        }
      })
    },

    // 权限checkbox选择状态变化
    handleCheckAllChange (val, i) {
      this.checkedRights[i] = val ? this.rights[i] : []
      console.log(this.checkedRights)
      // this.isIndeterminates[i] = false
    },
    handleCheckedRightsChange (i) {
      let checkedCount = this.checkedRights[i].length
      this.editForm.right[i].granted = checkedCount === this.rights[i].length
      // this.isIndeterminates[i] = checkedCount > 0 && checkedCount < this.rights[i].length
      // console.log(this.isIndeterminates[i])
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

</style>
