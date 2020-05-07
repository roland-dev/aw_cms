<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>运营管理</el-breadcrumb-item>
        <el-breadcrumb-item>活码管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 添加区域 -->
    <el-row class="top-menu"> 
      <el-row class="nav clearfix">
        <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr">添加活码组</el-button>
      </el-row>
    </el-row>

    <!-- 内容区域 -->
    <el-row class="table-menu">
      <el-card class="box-card" v-for="group in moveqrGroupList" :key="group.code">
        <div slot="header" class="clearfix">
          <div>
            <span class="group-title">组名：{{group.title}}</span>
            <el-button type="text" class="group-btn" @click="clearTime(group.code)">清空计数</el-button>
            <el-button type="text" class="group-btn" @click="delMoveqrGroup(group.code)">删除</el-button>
            <el-button type="text" class="group-btn" @click="showEditDialog(group.code, group.title, group.max_fans)">编辑</el-button>
          </div>
          <div>code：{{group.code}}</div>
        </div>
        <div class="text item">
          <el-row>
            <div class="moveqr-box card" v-for="moveqr in group.move_qr_list" :key="moveqr.code">
              <div>
                <span>{{moveqr.title}}</span>
                <div class="clearfix">
                  <span class="time">排序:{{moveqr.sort}} 计数:{{moveqr.show_cnt}}</span>
                  <el-button type="danger" icon="el-icon-delete" size="mini" circle class="fr" @click="delMoveqr(moveqr.code)"></el-button>
                  <el-button type="primary" icon="el-icon-edit" size="mini" circle class="fr" @click="showEditMoveqr(moveqr.code, moveqr.title, moveqr.sort)"></el-button>
                </div>
              </div>
              <div class="image">
                <img :src="moveqr.url" >
              </div>
            </div>
            <div style="display: inline-block; float:left">
              <div tabindex="0" class="el-upload el-upload--picture-card" @click="showAddMoveqr(group.code)">
                <i data-v-998a4204="" class="el-icon-plus"></i>
              </div>
            </div>
          </el-row>
        </div>
      </el-card>
    </el-row>

    <!-- 添加 -->
    <el-dialog title="添加活码组" :visible.sync ="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="80px" :rules="addFormRules" ref="addForm">
        <el-row>
          <el-form-item label="活码组名" prop="title">
            <el-input v-model="addForm.title" placeholder="请输入" :maxlength="64"></el-input>
          </el-form-item>
        </el-row> 
        <el-row>
          <el-form-item label="活码次数" prop="max_fans">
            <el-input-number v-model="addForm.max_fans" :min="1" :max="500" label="请选择固定计数次数"></el-input-number>
          </el-form-item>
        </el-row> 
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确定</el-button>
        <el-button @click.native="addVisible = false">取消</el-button>        
      </div>
    </el-dialog>

     <!-- 编辑 -->
    <el-dialog title="编辑活码组" :visible.sync ="editVisible" :close-on-click-modal="false" center>
      <el-form :model="editForm" label-width="80px" :rules="editFormRules" ref="editForm">
        <el-row>
          <el-form-item label="活码组名" prop="title">
            <el-input v-model="editForm.title" placeholder="请输入" :maxlength="64"></el-input>
          </el-form-item>
        </el-row> 
        <el-row>
          <el-form-item label="活码次数" prop="max_fans">
            <el-input-number v-model="editForm.max_fans" :min="1" :max="500" label="请选择固定计数次数"></el-input-number>
          </el-form-item>
        </el-row> 
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="editSubmit" :loading="editLoading">确定</el-button>
        <el-button @click.native="editVisible = false">取消</el-button>        
      </div>
    </el-dialog>

    <!-- 添加活码 -->
    <el-dialog title="添加活码" :visible.sync ="addMoveqrVisible" :close-on-click-modal="false" center>
      <el-form :model="addMoveqrForm" label-width="80px" :rules="addMoveqrFormRules" ref="addMoveqrForm">
        <el-row>
          <el-form-item label="活码名" prop="title">
            <el-input v-model="addMoveqrForm.title" placeholder="请输入" :maxlength="64"></el-input>
          </el-form-item>
        </el-row> 
        <el-row>
          <el-form-item label="活码图片" prop="filename">
            <el-upload ref="addCover" :action="imgUrl" :file-list="addImgFile" list-type="picture"
                        :on-success="addUploadSuccess" :on-error="uploadError" :data="imgObj"
                        :before-upload="uploadBefore" :limit="1" :on-remove="handleRemove"
                        :on-exceed="handleExceed" :with-credentials="true">
                <el-button size="small" type="primary">点击上传</el-button>
                <span slot="tip" class="el-upload__tip">(上传图片不能超过300k)</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="排序序号" prop="sort">
            <el-input v-model="addMoveqrForm.sort" placeholder="不填默认为“0”，按记录展示开始排序" type="number" :min="0" class="short-input"></el-input>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addMoveqrSubmit" :loading="addMoveqrLoading">确定</el-button>
        <el-button @click.native="addMoveqrVisible = false">取消</el-button>        
      </div>
    </el-dialog>

     <!-- 编辑活码 -->
    <el-dialog title="编辑活码" :visible.sync ="editMoveqrVisible" :close-on-click-modal="false" center>
      <el-form :model="editMoveqrForm" label-width="80px" :rules="editMoveqrFormRules" ref="editMoveqrForm">
        <el-row>
          <el-form-item label="活码名" prop="title">
            <el-input v-model="editMoveqrForm.title" placeholder="请输入" :maxlength="64"></el-input>
          </el-form-item>
        </el-row> 
        <el-row>
           <el-form-item label="排序序号" prop="sort">
            <el-input v-model="editMoveqrForm.sort" placeholder="不填默认为“0”，按记录展示开始排序" type="number" :min="0" class="short-input"></el-input>
          </el-form-item>
        </el-row> 
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="editMoveqrSubmit" :loading="editMoveqrLoading">确定</el-button>
        <el-button @click.native="editMoveqrVisible = false">取消</el-button>        
      </div>
    </el-dialog>
  </div> 
</template>

<script>
import Env from '../../http/env'
import API_OPERATE from '../../http/api_operate'

export default {
  name: 'MoveQr',
  data () {
    return {
      moveqrGroupList: [],   // 活码组列表

      // ----新增活码组----
      addVisible: false, // 是否显示
      addLoading: false,
      addFormRules: {
        title: [{required: true, message: '请输入', trigger: 'blur'}]
      },
      addForm: {title: '', max_fans: 1},

      // ----编辑活码组----
      editVisible: false, // 是否显示
      editLoading: false,
      editFormRules: {
        title: [{required: true, message: '请输入', trigger: 'blur'}]
      },
      editForm: {title: '', max_fans: 1},

      // ----新增活码----
      addMoveqrVisible: false, // 是否显示
      addMoveqrLoading: false,
      addMoveqrFormRules: {
        title: [{required: true, message: '请输入', trigger: 'blur'}]
      },
      addMoveqrForm: {title: '', sort: 0, fileName: ''},
      addMoveqrGroupCode: '',

      // ----编辑活码----
      editMoveqrVisible: false, // 是否显示
      editMoveqrLoading: false,
      editMoveqrFormRules: {
        title: [{required: true, message: '请输入', trigger: 'blur'}]
      },
      editMoveqrForm: {title: '', sort: 0},
      editMoveqrCode: '',

      // 上传图片 上传图片预览在addImgFile数组里面[{name: '', url: ''}]
      addImgFile: [],
      editImgFile: [],
      imgUrl: `${Env.baseURL}/operate/moveqr/image`,
      imgObj: {'image': {}}
    }
  },
  mounted: function () {
    this.getList()
  },
  methods: {
    // 获取活码组列表
    getList () {
      API_OPERATE.getMoveqrGroupList().then(res => {
        this.moveqrGroupList = res.data.move_qr_group_list
      }).catch(err => {
        console.error(err)
      })
    },

    // 添加活码组
    showAddDialog () {
      this.addVisible = true
      this.addForm = {title: '', max_fans: 1}
      let _this = this
      setTimeout(() => {
        _this.$refs.addForm.clearValidate()
      }, 300)
    },

    addSubmit () {
      let _this = this
      this.$refs.addForm.validate((valid) => {
        if (valid) {
          this.addLoading = true
          API_OPERATE.addMoveqrGroup(_this.addForm).then(res => {
            if (res.code === 0) {
              _this.$message.success({showClose: true, message: '新增成功', duration: 2000})
              this.getList()
              _this.addVisible = false
            } else {
              _this.$message.error({showClose: true, message: '新增失败', duration: 2000})
              _this.addVisible = false
            }
            this.addLoading = false
            this.$refs.addForm.clearValidate()
          }).catch(err => {
            console.error(err)
            this.addLoading = false
          })
        }
      })
    },

    // 编辑活码组
    showEditDialog (code, title, maxFans) {
      this.editVisible = true
      this.editForm = {
        'code': code,
        'title': title,
        'max_fans': maxFans
      }
      this.$refs.editForm.clearValidate()
    },

    editSubmit () {
      let _this = this
      this.$refs.editForm.validate((valid) => {
        if (valid) {
          this.editLoading = true
          API_OPERATE.editMoveqrGroup(_this.editForm.code, {'title': _this.editForm.title, 'max_fans': _this.editForm.max_fans}).then(res => {
            if (res.code === 0) {
              _this.$message.success({showClose: true, message: '编辑成功', duration: 2000})
              _this.getList()
              _this.editVisible = false
            } else {
              _this.$message.error({showClose: true, message: '编辑失败', duration: 2000})
              _this.editVisible = false
            }
            _this.editLoading = false
          }).catch(err => {
            console.error(err)
            _this.editLoading = false
          })
        }
      })
    },

    // 删除活码组
    delMoveqrGroup (code) {
      this.$confirm('是否确定删除该活码组?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        API_OPERATE.delMoveqrGroup(code).then(data => {
          this.$message.success({showClose: true, message: '删除成功', duration: 2000})
          this.getList()
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消删除',
          duration: 2000
        })
      })
    },

    // 清空计数
    clearTime (code) {
      this.$confirm('是否确定清空计算?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        API_OPERATE.clearMoveqrTime(code).then(data => {
          this.$message.success({showClose: true, message: '清空成功', duration: 2000})
          this.getList()
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消删除',
          duration: 2000
        })
      })
    },

    // 添加活码
    showAddMoveqr (code) {
      this.addMoveqrGroupCode = code
      this.addMoveqrVisible = true
      this.addMoveqrForm = {'title': '', 'sort': 0}
      let _this = this
      setTimeout(() => {
        _this.$refs.addMoveqrForm.clearValidate()
      }, 300)
    },

    addMoveqrSubmit (code) {
      let _this = this
      this.$refs.addMoveqrForm.validate((valid) => {
        if (valid) {
          this.addMoveqrForm.move_qr_group_code = _this.addMoveqrGroupCode
          this.addMoveqrLoading = true
          API_OPERATE.addMoveqr(_this.addMoveqrForm).then(res => {
            if (res.code === 0) {
              _this.$message.success({showClose: true, message: '新增成功', duration: 2000})
              this.getList()
              _this.addMoveqrVisible = false
            } else {
              _this.$message.error({showClose: true, message: '新增失败', duration: 2000})
              _this.addMoveqrVisible = false
            }
            this.addMoveqrGroupCode = ''
            this.addMoveqrLoading = false
            this.$refs.addMoveqrForm.clearValidate()
          }).catch(err => {
            console.error(err)
            this.addMoveqrLoading = false
          })
        }
      })
      _this.$refs.addCover.clearFiles()
    },

    // 编辑活码
    showEditMoveqr (code, title, sort) {
      this.editMoveqrCode = code
      this.editMoveqrVisible = true
      this.editMoveqrForm = {
        'title': title,
        'sort': sort
      }
    },

    editMoveqrSubmit () {
      let _this = this
      this.$refs.editMoveqrForm.validate((valid) => {
        if (valid) {
          this.editMoveqrLoading = true
          API_OPERATE.editMoveqr(_this.editMoveqrCode, _this.editMoveqrForm).then(res => {
            if (res.code === 0) {
              _this.$message.success({showClose: true, message: '编辑成功', duration: 2000})
              _this.getList()
              _this.editMoveqrVisible = false
            } else {
              _this.$message.error({showClose: true, message: '编辑失败', duration: 2000})
              _this.editMoveqrVisible = false
            }
            _this.editMoveqrLoading = false
            _this.editMoveqrGroupCode = ''
          }).catch(err => {
            console.error(err)
            _this.editMoveqrLoading = false
          })
        }
      })
    },

    // 删除活码
    delMoveqr (code) {
      this.$confirm('是否确定删除该活码?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        API_OPERATE.delMoveqr(code).then(data => {
          this.$message.success({showClose: true, message: '删除成功', duration: 2000})
          this.getList()
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消删除',
          duration: 2000
        })
      })
    },

    // ---------------------上传图片模块------------------------------
    uploadBefore (file) {
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
        this.addMoveqrForm.filename = response.data.filename
        this.addImgFile = [{
          name: response.data.filename,
          url: response.data.url
        }]
      } else {
        console.error(response.msg)
      }
    },
    // 上传图片成功
    editUploadSuccess (response, file, editImgFile) {
      if (response.code === 0) {
        this.editForm.filename = response.data.path
        this.editImgFile = [{
          name: response.data.filename,
          url: response.data.url
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
      this.addMoveqrForm.filename = ''
    },

    handleExceed (file, ImgFile) {
      this.$alert('只能上传一张图片')
    },

    // 更新上传图片
    updateUploadSuccess (response, file, ImgFile) {
      if (response.code === 0) {
        this.addMoveqrForm.filename = response.data.filename
        console.log()
        this.addImgFile = [{
          name: response.data.filename,
          url: response.data.url
        }]
      } else {
        console.error(response.msg)
      }
    }
  }
}
</script>

<style scoped>
.table-menu{
  margin-bottom: 24px;
}
.box-card{
  margin-bottom: 20px;
  padding-right: 0;
  width: 100%;
}

.card{
  width: 200px;
  min-height: 200px;
  border: 1px solid #ebeef5;
  background-color: #fff;
  -webkit-box-shadow: 0 2px 12px 0 rgba(0,0,0,.1);
  box-shadow: 0 2px 12px 0 rgba(0,0,0,.1);
  color: #303133;
  border-radius: 4px 4px 0 0;
  float:left; 
  margin-right: 20px;
  overflow: hidden;
  padding: 0 4px;
}
.el-card__body{
  padding: 0;
}
.card .image{
  margin-top: 6px;
}
.card .image img{
  width: 100%;
}
.box-card:last-child{
  margin-bottom: 0;
}
.table-menu{
  min-height: 300px;
}
.group-btn{
  float: right;
  padding: 3px 2px;
}
.moveqr-btn{

}
.group-title{
  font-size: 16px;
}
.el-upload--picture-card{
  width: 200px;
  height: 200px;
  line-height: 200px;
  margin-right: 20px;
}
.el-button--mini{
  padding: 3px 8px;
  margin-left: 6px;
}

</style>
