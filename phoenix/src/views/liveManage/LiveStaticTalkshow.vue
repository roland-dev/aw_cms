<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>直播管理</el-breadcrumb-item>
        <el-breadcrumb-item>固定节目管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu">
      <el-row class="nav clearfix">
        <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr">添加节目</el-button>
      </el-row>
    </el-row>

    <!-- 列表 -->
    <el-row class="table-menu">
      <!-- 固定节目列表 -->
      <el-table :data="tablePageData" stripe style="width: 100%">
        <el-table-column prop="category_name" label="关联栏目"></el-table-column>
        <el-table-column prop="teacher_user_name" label="栏目老师"></el-table-column>
        <el-table-column prop="start_time" label="开始时间"  :formatter="timeFormat"></el-table-column>
        <el-table-column prop="end_time" label="结束时间"  :formatter="timeFormat"></el-table-column>
        <el-table-column prop="last_modify_user_name" label="最后修改人"></el-table-column>
        <el-table-column prop="updated_at" label="最后修改时间"></el-table-column>
        <el-table-column fixed="right" align="center" label="操作" width="200">
          <template slot-scope="scope">
            <el-button @click.native="showEditDialog(scope.row.id)" type="text" size="small">编辑</el-button>
            <el-button @click.native="delStaticTalkshow(scope.row.id)" type="text" size="samll">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <!-- 添加固定节目 -->
    <el-dialog width="825px" title="添加节目" :visible.sync="addVisible" :close-on-click-modal="false" center>
      <el-form :model="addForm" label-width="100px" :rules="addFormRules" ref="addForm">
        <el-row>
          <el-form-item label="关联栏目" prop="category_code">
            <el-select v-model="addForm.category_code" placeholder="请选择" @change="getTeacherListByAdd">
              <el-option v-for="category in categoryList" :value-key="category.code" :key="category.code" :label="category.name" :value="category.code"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="栏目老师" prop="teacher_id">
            <el-select v-model="addForm.teacher_id" placeholder="请选择">
              <el-option v-for="teacher in teacherList" :value-key="teacher.teacher_id" :key="teacher.teacher_id" :label="teacher.name" :value="teacher.teacher_id"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="节目名称" prop="title">
            <el-input v-model="addForm.title" placeholder="请输入" :maxlength="64"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="播放时间" required>
            <el-col :span="7" style="height: 32px;">
              <el-form-item prop="start_time">
                <el-time-picker placeholder="开始时间" v-model="addForm.start_time" @change="changeStartTime" :picker-options="{selectableRange: startTime }" value-format="yyyy-MM-dd HH:mm:ss">
                </el-time-picker>
              </el-form-item>
            </el-col>
            <el-col :span="7" style="height: 32px;">
              <el-form-item prop="end_time">
                <el-time-picker placeholder="结束时间" v-model="addForm.end_time" @change="changeEndTime" :picker-options="{selectableRange: endTime }" value-format="yyyy-MM-dd HH:mm:ss">
                </el-time-picker>
              </el-form-item>
            </el-col>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="banner图片" prop="banner_url">
            <el-upload :action="imgUrl" :file-list="bannerImgFile" list-type="picture"
                       :on-success="uploadSuccessOfAdd" :on-error="uploadError" :data="imgObj"
                       :before-upload="uploadBefore" :limit="1" :on-remove="handleRemove"
                       :on-exceed="handleExceed" :with-credentials="true">
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="el-upload__tip">(要求：图片尺寸 1053*240 px、大小不超过 100 K)</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="类型" prop="type">
            <el-select v-model="addForm.type" disabled placeholder="请选择">
              <el-option v-for="type in liveTypes" :vlaue-key="type.code" :key="type.code" :label="type.name" :value="type.code"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="关联直播室" prop="live_room_code">
            <el-select v-model="addForm.live_room_code" placeholder="请选择">
              <el-option v-for="liveRoom in liveRoomList" :value-key="liveRoom.code" :key="liveRoom.code" :label="liveRoom.name" :value="liveRoom.code"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="关联供应商" prop="video_vendor_code">
            <el-select v-model="addForm.video_vendor_code" placeholder="请选择">
              <el-option v-for="videoVendor in videoVendorList" :value-key="videoVendor.code" :key="videoVendor.code" :label="videoVendor.name" :value="videoVendor.code"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="播报内容" prop="boardcast_content">
            <el-input type="textarea" v-model="addForm.boardcast_content" placeholder="请输入播报内容" :maxlength="255"></el-input>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确定</el-button>
        <el-button @click.native="addVisible = false">取消</el-button>
      </div>
    </el-dialog>

    <!-- 编辑固定节目 -->
    <el-dialog width="825px" title="编辑节目" :visible.sync="editVisible" :close-on-click-modal="false" center>
      <el-form :model="editForm" label-width="100px" :rules="editFormRules" ref="editForm">
        <el-row>
          <el-form-item label="关联栏目" prop="category_code">
            <el-select v-model="editForm.category_code" placeholder="请选择" @change="getTeacherListByEdit">
              <el-option v-for="category in categoryList" :value-key="category.code" :key="category.code" :label="category.name" :value="category.code"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="栏目老师" prop="teacher_id">
            <el-select v-model="editForm.teacher_id" placeholder="请选择">
              <el-option v-for="teacher in teacherList" :value-key="teacher.teacher_id" :key="teacher.teacher_id" :label="teacher.name" :value="teacher.teacher_id"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="节目名称" prop="title">
            <el-input v-model="editForm.title" placeholder="请输入" :maxlength="64"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="播放时间" required>
            <el-col :span="7" style="height: 32px;">
              <el-form-item prop="start_time">
                <el-time-picker placeholder="开始时间" v-model="editForm.start_time" @change="changeStartTime" :picker-options="{selectableRange: startTime}" value-format="yyyy-MM-dd HH:mm:ss">
                </el-time-picker>
              </el-form-item>
            </el-col>
            <el-col :span="7" style="height: 32px;">
              <el-form-item prop="end_time">
                <el-time-picker placeholder="结束时间" v-model="editForm.end_time" @change="changeEndTime" :picker-options="{selectableRange: endTime}" value-format="yyyy-MM-dd HH:mm:ss">
                </el-time-picker>
              </el-form-item>
            </el-col>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="banner图片" prop="banner_url">
            <el-upload :action="imgUrl" :file-list="bannerImgFile" list-type="picture"
                       :on-success="uploadSuccessOfEdit" :on-error="uploadError" :data="imgObj"
                       :before-upload="uploadBefore" :limit="1" :on-remove="handleRemove"
                       :on-exceed="handleExceed" :with-credentials="true">
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="el-upload__tip">(要求：图片尺寸 1053*240 px、大小不超过 100 K)</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="类型" prop="type">
            <el-select v-model="editForm.type" disabled placeholder="请选择">
              <el-option v-for="type in liveTypes" :vlaue-key="type.code" :key="type.code" :label="type.name" :value="type.code"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="关联直播室" prop="live_room_code">
            <el-select v-model="editForm.live_room_code" placeholder="请选择">
              <el-option v-for="liveRoom in liveRoomList" :value-key="liveRoom.code" :key="liveRoom.code" :label="liveRoom.name" :value="liveRoom.code"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="关联供应商" prop="video_vendor_code">
            <el-select v-model="editForm.video_vendor_code" placeholder="请选择">
              <el-option v-for="videoVendor in videoVendorList" :value-key="videoVendor.code" :key="videoVendor.code" :label="videoVendor.name" :value="videoVendor.code"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="播报内容" prop="boardcast_content">
            <el-input type="textarea" v-model="editForm.boardcast_content" placeholder="请输入播报内容" :maxlength="255"></el-input>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="editSubmit" :loading="addLoading">确定</el-button>
        <el-button @click.native="editVisible = false">取消</el-button>
      </div>
    </el-dialog>

  </div>
</template>
<script>
import Pagination from '@/components/Pagination'
import HTTP from '../../http/api_live'
import API_COLUMN from '../../http/api_column'
import Env from '../../http/env'

export default {
  name: 'LiveStaticTalkshow',
  data () {
    return {
      totalAll: 0,        // 列表总数目
      pageNo: 1,          // 当前页
      pageSize: 10,       // 分页显示数目
      tablePageData: [],  // 分页显示数据
      pageRefresh: true,  // 分页内容刷新

      categoryList: [],   // 栏目列表
      teacherList: [],    // 栏目老师列表

      liveTypes: [
        {
          code: 'live',
          name: '直播'
        },
        {
          code: 'play',
          name: '录播'
        }
      ],  // 节目类型

      liveRoomList: [],   // 直播室列表
      videoVendorList: [],  // 视频供应商列表

      // time-picker
      startTime: '',
      endTime: '',

      // 上传图片
      imgUrl: `${Env.baseURL}/live/static-talkshow/upload/banner`,
      bannerImgFile: [],
      imgObj: {'image': {}},

      // 新增固定节目
      addVisible: false,
      addLoading: false,
      addFormRules: {
        category_code: [
          {required: true, message: '请选择关联栏目', trigger: 'blur'}
        ],
        teacher_id: [
          {required: true, message: '请选择栏目老师', trigger: 'blur'}
        ],
        title: [
          {required: true, message: '请输入节目名称', trigger: 'blur'}
        ],
        start_time: [
          {required: true, message: '请选择时间', trigger: 'blur'}
        ],
        end_time: [
          {required: true, message: '请选择时间', trigger: 'blure'}
        ],
        banner_url: [
          {required: true, message: '请上传banner图片', trigger: 'blur'}
        ],
        type: [
          {required: true, message: '请选择节目类型', trigger: 'blur'}
        ],
        live_room_code: [
          {required: true, message: '请选择直播室', trigger: 'blur'}
        ],
        video_vendor_code: [
          {required: true, message: '请选择供应商', trigger: 'blur'}
        ],
        boardcast_content: [
          {required: true, message: '请输入播报内容', trigger: 'blur'}
        ]
      },
      addForm: {
        teacher_id: '',
        title: '',
        start_time: '',
        end_time: '',
        banner_url: '',
        type: '',
        live_room_code: '',
        video_vendor_code: '',
        boardcast_content: ''
      },

      // 编辑固定节目
      editVisible: false,
      editLoading: false,
      editFormRules: {
        catgeory_code: [
          {required: true, message: '请选择关联栏目', trigger: 'blur'}
        ],
        teacher_id: [
          {required: true, message: '请选择栏目老师', trigger: 'blur'}
        ],
        title: [
          {required: true, message: '请输入节目名称', trigger: 'blur'}
        ],
        start_time: [
          {required: true, message: '请选择时间', trigger: 'blur'}
        ],
        end_time: [
          {required: true, message: '请选择时间', trigger: 'blur'}
        ],
        banner_url: [
          {required: true, message: '请上传banner图片', trigger: 'blur'}
        ],
        type: [
          {required: true, message: '请选择节目类型', trigger: 'blur'}
        ],
        live_room_code: [
          {required: true, message: '请选择直播室', trigger: 'blur'}
        ],
        video_vendor_code: [
          {required: true, message: '请选择供应商', trigger: 'blur'}
        ],
        boardcast_content: [
          {required: true, message: '请输入播报内容', trigger: 'blur'}
        ]
      },
      editForm: {
        id: '',
        teacher_id: '',
        title: '',
        start_time: '',
        end_time: '',
        banner_url: '',
        type: '',
        live_room_code: '',
        video_vendor_code: '',
        boardcast_content: ''
      }
    }
  },
  components: {
    Pagination
  },
  created: function () {
  },
  mounted: function () {
    this.getLiveStaticTalkshowList()
  },
  methods: {
    // 获取固定节目列表
    getLiveStaticTalkshowList () {
      var params = {
        page_no: this.pageNo,
        page_size: this.pageSize
      }
      HTTP.getLiveStaticTalkshowList(params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.static_talkshow_list
          this.totalAll = res.data.static_talkshow_cnt
          this.initPagination()
        } else {
          console.log(res.msg)
        }
      }).catch(err => {
        console.error(err)
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
      this.getLiveStaticTalkshowList()
    },
    // --------------------------- timeFormat -----------------------------
    timeFormat (row, column) {
      let date = row[column.property]
      if (date === undefined) {
        return ''
      }
      return date.substr(11)
    },

    // --------------------------------  change function ------------------------------------

    changeStartTime (item) {
      this.endTime = item.substr(11) + ' - 23:59:59'
    },

    changeEndTime (item) {
      this.startTime = '00:00:00 - ' + item.substr(11)
    },

    // ------------------------------上传图片-------------------------------------------
    uploadBefore (file) {
      console.log(file)
      let imgType = [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/bmg',
        'image/gif',
        'image/svg'
      ]

      let isJPG = false
      for (let i = 0; i < imgType.length; i++) {
        if (imgType[i] === file.type) {
          isJPG = true
        }
      }

      const isLtFileSize = file.size / 1024 <= 100

      if (!isJPG) {
        this.$message.error('上传图片只能是 JPG/PNG/GIF/SVG 格式!')
      }
      if (!isLtFileSize) {
        this.$message.error('上传图片大小不能超过 100 K!')
      }

      if (isJPG && isLtFileSize) {
        this.imgObj.image = file
      }

      return isJPG && isLtFileSize
    },

    uploadSuccessOfAdd (response, file, bannerImgFile) {
      if (response.code === 0) {
        this.addForm.banner_url = response.data.path
        this.bannerImgFile = [{
          name: response.data.path.substr(response.data.path.lastIndexOf('/') + 1),
          url: response.data.path
        }]
      } else {
        console.error(response.msg)
      }
    },

    uploadSuccessOfEdit (response, file, bannerImgFile) {
      if (response.code === 0) {
        this.editForm.banner_url = response.data.path
        this.bannerImgFile = [{
          name: response.data.path.substr(response.date.path.lastIndexOf('/') + 1),
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
      this.addForm.banner_url = ''
      this.editForm.banner_url = ''
    },

    handleExceed (file, fileList) {
      this.$alert('只能上传一张图片')
    },

    // 获取栏目列表
    getColumnList () {
      API_COLUMN.getCategoryList().then(res => {
        if (res.code === 0) {
          this.categoryList = res.data.category_list
        } else {
          console.log(res.msg)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 根据categorycode 获取当前栏目
    getTeacherListByAdd (value) {
      this.addForm.teacher_id = ''
      this.categoryList.forEach(v => {
        if (value === v.code) {
          if (this.addForm.title.length === 0) {
            this.addForm.title = v.name
          }
        }
      })
      this.getTeacherList(value)
    },

    getTeacherListByEdit (value) {
      this.editForm.teacher_id = ''
      this.categoryList.forEach(v => {
        if (value === v.code) {
          if (this.editForm.title.length === 0) {
            this.editForm.title = v.name
          }
        }
      })
      this.getTeacherList(value)
    },

    getTeacherList (value) {
      API_COLUMN.getTeacherListOfCategoryCode(value).then(res => {
        if (res.code === 0) {
          this.teacherList = res.data.teacher_list
        } else {
          console.log(res.msg)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取 直播室列表
    getLiveRoomList () {
      let params = {
        page_no: 1,
        page_size: 1000
      }
      HTTP.getLiveRoomList(params).then(res => {
        if (res.code === 0) {
          this.liveRoomList = res.data.live_room_list
        } else {
          console.log(res.msg)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 获取视频供应商列表
    getVideoVendorList () {
      let params = {
        page_no: 1,
        page_size: 1000
      }
      HTTP.getVideoVendorList(params).then(res => {
        if (res.code === 0) {
          this.videoVendorList = res.data.video_vendor_list
        } else {
          console.log(res.msg)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 添加固定节目
    showAddDialog () {
      this.addVisible = true
      this.bannerImgFile = []
      this.teacherList = []
      this.startTime = ''
      this.endTime = ''
      this.addForm = {
        teacher_id: '',
        title: '',
        start_time: '',
        end_time: '',
        banner_url: '',
        type: 'live',
        live_room_code: '',
        video_vendor_code: '',
        boardcast_content: '',
        category_code: ''
      }
      this.getColumnList()
      this.getLiveRoomList()
      this.getVideoVendorList()

      // 清空input框验证状态
      setTimeout(() => {
        this.$refs.addForm.clearValidate()
      }, 100)
    },

    addSubmit () {
      let _this = this
      this.$refs.addForm.validate((valid) => {
        if (valid) {
          _this.addLoading = true
          HTTP.addLiveStaticTalkshow(_this.addForm).then(data => {
            if (data.code === 0) {
              _this.$message.success({showClose: true, message: '新增成功！此修改将会同步到明天的节目表中，今天的节目表不会被同步修改', duration: 2000})
              _this.getLiveStaticTalkshowList()
              _this.addVisible = false
            } else {
              _this.$message.error({showClose: true, message: '新增失败:' + data.msg, duration: 2000})
            }
            _this.addLoading = false
          }).catch(err => {
            console.error(err)
            _this.addLoading = false
          })
        }
      })
    },

    // 编辑固定节目
    showEditDialog (id) {
      this.editVisible = true
      this.bannerImgFile = []
      this.teacherList = []
      this.startTime = ''
      this.endTime = ''
      // 获取固定节目详情
      HTTP.getLiveStaticTalkshowInfo(id).then(res => {
        if (res.code === 0) {
          let staticTalkshow = res.data.static_talkshow
          this.editForm = {
            id: id,
            teacher_id: staticTalkshow.teacher_id,
            title: staticTalkshow.title,
            start_time: staticTalkshow.start_time,
            end_time: staticTalkshow.end_time,
            banner_url: staticTalkshow.banner_url,
            type: staticTalkshow.type,
            live_room_code: staticTalkshow.live_room_code,
            video_vendor_code: staticTalkshow.video_vendor_code,
            boardcast_content: staticTalkshow.boardcast_content,
            category_code: staticTalkshow.category_code
          }

          let name = staticTalkshow.banner_url.substr(staticTalkshow.banner_url.lastIndexOf('/') + 1)
          this.bannerImgFile = [{
            name: name,
            url: staticTalkshow.banner_url
          }]

          this.getColumnList()
          this.getTeacherList(staticTalkshow.category_code)
          this.getLiveRoomList()
          this.getVideoVendorList()

          setTimeout(() => {
            this.$refs.editForm.clearValidate()
          }, 100)
        } else {
          this.$message.error(res.msg)
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
          HTTP.updateLiveStaticTalkshow(_this.editForm.id, _this.editForm).then(res => {
            if (res.code === 0) {
              _this.$message.success({showClose: true, message: '编辑成功！此修改将会同步到明天的节目表中，今天的节目表不会被同步修改', duration: 2000})
              _this.getLiveStaticTalkshowList()
              _this.editVisible = false
            } else {
              _this.$message.error({showClose: true, message: '编辑失败:' + res.msg, duration: 2000})
            }
            _this.editLoading = false
          }).catch(err => {
            console.error(err)
            _this.editLoading = false
          })
        }
      })
    },

    // 删除固定节目
    delStaticTalkshow (id) {
      let _this = this
      _this.$confirm('是否确认删除该节目？', '提示', {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        HTTP.deleteLiveStaticTalkshow(id).then(res => {
          if (res.code === 0) {
            _this.$message.success({showClose: true, message: '删除成功！此修改将会同步到明天的节目表中，今天的节目表不会被同步修改', duration: 2000})
            _this.getLiveStaticTalkshowList()
          } else {
            _this.$message.error({showClose: true, message: '删除失败:' + res.msg, duration: 2000})
          }
        }).catch(err => {
          console.error(err)
        })
      }).catch(() => {
        _this.$message({type: 'info', message: '已取消删除'})
      })
    }
  }
}
</script>
