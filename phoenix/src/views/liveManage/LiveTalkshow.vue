<template>
  <div>
    <el-row class="position">
      <el-breadcrumb separator-class="el-icon-arrow-right">
        <el-breadcrumb-item>直播管理</el-breadcrumb-item>
        <el-breadcrumb-item>每日节目管理</el-breadcrumb-item>
      </el-breadcrumb>
    </el-row>

    <!-- 搜索区域 -->
    <el-row class="top-menu">
      <el-row class="nav clearfix">
        <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr">添加节目</el-button>
      </el-row>
      <el-form :inline="true" :model="searchParams">
        <el-row>
          <el-form-item label="日期">
            <el-date-picker v-model="searchParams.date"
                            align="right"
                            type="date"
                            value-format="yyyy-MM-dd"
                            format="yyyy-MM-dd"
                            placeholder="选择日期"
                            :clearable="false">
            </el-date-picker>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item>
            <el-button type="primary" icon="el-icon-search" @click="onSearch"
                        @keydown.13="keySearch($event)" class="search" round>查询
            </el-button>
          </el-form-item>
        </el-row>
      </el-form>
    </el-row>

    <!-- 列表 -->
    <el-row class="table-menu" v-if="tablePageData.length && isShowList">
      <!-- 节目列表 -->
      <el-table :data="tablePageData" stripe style="width: 100%">
        <el-table-column prop="title" label="节目名称"></el-table-column>
        <el-table-column prop="category_name" label="关联栏目"></el-table-column>
        <el-table-column prop="teacher_user_name" label="栏目老师"></el-table-column>
        <el-table-column prop="start_time" label="开始时间" :formatter="timeFormat"></el-table-column>
        <el-table-column prop="end_time" label="结束时间" :formatter="timeFormat"></el-table-column>
        <el-table-column prop="last_modify_user_name" label="最后修改人"></el-table-column>
        <el-table-column prop="updated_at" label="最后修改时间"></el-table-column>
        <el-table-column prop="status_title" label="状态"></el-table-column>
        <el-table-column fixed="right" align="center" label="操作" width="120">
          <template slot-scope="scope">  
            <el-dropdown>
              <el-button type="primary">
                节目管理<i class="el-icon-arrow-down el-icon--right"></i>
              </el-button>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item @click.native="beginTalkshow(scope.row.code)" v-if="isToday && scope.row.type == 'live' && scope.row.status < 40">开始直播</el-dropdown-item>
                <el-dropdown-item @click.native="endTalkshow(scope.row.code)" v-if="isToday">结束直播</el-dropdown-item>
                <el-dropdown-item @click.native="showPreviewDialog(scope.row.code)" v-if="!isToday || scope.row.status >= 40">编辑节目</el-dropdown-item>
                <el-dropdown-item @click.native="showEditDialog(scope.row.code)" v-if="scope.row.status < 40 && isToday">编辑节目</el-dropdown-item>
                <el-dropdown-item @click.native="delTalkshow(scope.row.code)" v-if="scope.row.status < 40 && isToday">删除节目</el-dropdown-item>
                <el-dropdown-item @click.native="gotoReply(scope.row.teacher_user_id, scope.row.title)" v-if="scope.row.type == 'play' && scope.row.status != 40">录播评论</el-dropdown-item>
                <el-dropdown-item @click.native="gotoDiscuss(scope.row.live_room_code, scope.row.category_code, scope.row.title)" v-if="scope.row.type == 'live' && scope.row.status != 40">直播互动</el-dropdown-item>
                <el-dropdown-item @click.native="gotoDiscuss(scope.row.live_room_code, scope.row.category_code, scope.row.title)" v-if="scope.row.status == 40">直播互动</el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
          </template>
        </el-table-column>
      </el-table>
      <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
    </el-row>

    <!-- 复制固定节目表 -->
    <el-row class="content" v-if="tablePageData.length === 0 && isShowList" >
      <span>当前日期下还没有设定的节目表  </span>
      <el-button type="primary" @click.native="pullLiveTalkshow" v-if="isToday">一键复制固定节目表</el-button>
    </el-row>

    <!-- 添加节目 -->
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
          <el-form-item label="播放时间" prop="play_time">
            <el-date-picker
                    v-model="addForm.play_time"
                    type="datetimerange"
                    start-placeholder="开始日期"
                    end-placeholder="结束日期"
                    value-format="yyyy-MM-dd HH:mm:ss"
                    format="yyyy-MM-dd HH:mm:ss">
            </el-date-picker>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="banner图片" prop="banner_url">
            <el-upload :action="imgUrl" :file-list="bannerImgFile" list-type="picture"
                       :on-success="uploadSuccessOfAdd" :on-error="uploadError" :data="imgObj"
                       :before-upload="uploadBefore" :limit="1" :on-remove="handleRemove"
                       :on-exceed="handleExceed" :with-credentials="true">
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="el-upload__tip">(要求：图片尺寸 1035*240 px、大小不超过 100 K)</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="类型" prop="type">
            <el-select v-model="addForm.type" placeholder="请选择" @change="changeLiveType">
              <el-option v-for="type in liveTypes" :value-key="type.code" :key="type.code" :label="type.name" :value="type.code"></el-option>
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
          <el-form-item label="录播链接" prop="play_url">
            <el-input v-model="addForm.play_url" placeholder="请输入"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="播报内容" prop="boardcast_content">
            <el-input type="textarea" v-model="addForm.boardcast_content" placeholder="请输入播报内容" :maxlength="255"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="海报描述" prop="description">
            <el-input type="textarea" :rows="4" v-model="addForm.description" placeholder="最多输入130字"></el-input>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确定</el-button>
        <el-button @click.native="addVisible = false">取消</el-button> 
      </div>
    </el-dialog>

    <!-- 编辑节目表 -->
    <el-dialog width="825px" title="编辑节目 " :visible.sync="editVisible" :close-on-click-modal="false" center>
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
            <el-select v-model="editForm.teacher_id" placholder="请选择">
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
          <el-form-item label="播放时间" prop="play_time">
            <el-date-picker v-model="editForm.play_time"
                            type="datetimerange"
                            start-placeholder="开始时间"
                            end-placeholder="结束时间"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            format="yyyy-MM-dd HH:mm:ss">
            </el-date-picker>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="banner图片" prop="banner_url">
            <el-upload :action="imgUrl" :file-list="bannerImgFile" list-type="picture"
                       :on-success="uploadSuccessOfEdit" :on-error="uploadError" :data="imgObj"
                       :before-upload="uploadBefore" :limit="1" :on-remove="handleRemove"
                       :on-exceed="handleExceed" :with-credentials="true">
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="el-upload__tip">(要求：图片尺寸 1035*240 px、大小不超过 100 K)</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="类型" prop="type">
            <el-select v-model="editForm.type" placeholder="请选择" @change="changeLiveType">
              <el-option v-for="type in liveTypes" :value-key="type.code" :key="type.code" :label="type.name" :value="type.code"></el-option>
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
          <el-form-item label="录播链接" prop="play_url">
            <el-input v-model="editForm.play_url" placeholder="请输入"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="播报内容" prop="boardcast_content">
            <el-input type="textarea" v-model="editForm.boardcast_content" placeholder="请输入播报内容" :maxlength="255"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="海报描述" prop="description">
            <el-input type="textarea" :rows="4" v-model="editForm.description" placeholder="最多输入130字"></el-input>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click.native="editSubmit" :loading="editLoading">确认</el-button>
        <el-button @click.native="editVisible = false">取消</el-button>
      </div>
    </el-dialog>

    <!-- 查看节目表 -->
    <el-dialog width="825px" title="编辑节目 " :visible.sync="previewVisible" :close-on-click-modal="false" center class="preview-dialog">
        <el-form :model="previewForm" label-width="100px" :rules="previewFormRules" ref="previewForm">
        <el-row>
          <el-form-item label="关联栏目" prop="category_code">
            <el-select v-model="previewForm.category_code" disabled placeholder="请选择" @change="getTeacherList">
              <el-option v-for="category in categoryList" :value-key="category.code" :key="category.code" :label="category.name" :value="category.code"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="栏目老师" prop="teacher_id">
            <el-select v-model="previewForm.teacher_id" disabled placholder="请选择">
              <el-option v-for="teacher in teacherList" :value-key="teacher.teacher_id" :key="teacher.teacher_id" :label="teacher.name" :value="teacher.teacher_id"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="节目名称" prop="title">
            <el-input v-model="previewForm.title" disabled placeholder="请输入"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="播放时间" prop="play_time">
            <el-date-picker v-model="previewForm.play_time"
                            type="datetimerange"
                            start-placeholder="开始时间"
                            end-placeholder="结束时间"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            format="yyyy-MM-dd HH:mm:ss">
            </el-date-picker>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="banner图片" prop="banner_url">
            <el-upload :action="imgUrl" :file-list="bannerImgFile" list-type="picture"
                       :on-success="uploadSuccessOfEdit" :on-error="uploadError" :data="imgObj"
                       :before-upload="uploadBefore" :limit="1" :on-remove="handleRemove"
                       :on-exceed="handleExceed" :with-credentials="true" disabled>
              <el-button size="small" type="primary">点击上传</el-button>
              <span slot="tip" class="el-upload__tip">(要求：图片尺寸 1035*240 px、大小不超过 100 K)</span>
            </el-upload>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="类型" prop="type">
            <el-select v-model="previewForm.type" placeholder="请选择" @change="changeLiveType">
              <el-option v-for="type in liveTypes" :value-key="type.code" :key="type.code" :label="type.name" :value="type.code"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="关联直播室" prop="live_room_code">
            <el-select v-model="previewForm.live_room_code" disabled placeholder="请选择">
              <el-option v-for="liveRoom in liveRoomList" :value-key="liveRoom.code" :key="liveRoom.code" :label="liveRoom.name" :value="liveRoom.code"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="关联供应商" prop="video_vendor_code">
            <el-select v-model="previewForm.video_vendor_code" disabled placeholder="请选择">
              <el-option v-for="videoVendor in videoVendorList" :value-key="videoVendor.code" :key="videoVendor.code" :label="videoVendor.name" :value="videoVendor.code"></el-option>
            </el-select>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="录播链接" prop="play_url">
            <el-input v-model="previewForm.play_url" placeholder="请输入"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="播报内容" prop="boardcast_content">
            <el-input type="textarea" v-model="previewForm.boardcast_content" placeholder="请输入播报内容" :maxlength="255"></el-input>
          </el-form-item>
        </el-row>
        <el-row>
          <el-form-item label="海报描述" prop="description">
            <el-input type="textarea" :rows="4" v-model="previewForm.description" placeholder="最多输入130字"></el-input>
          </el-form-item>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <div slot="footer" class="dialog-footer">
          <el-button type="primary" @click.native="previewSubmit" :loading="editLoading">确认</el-button>
          <el-button @click.native="previewVisible = false">取消</el-button>
        </div>
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
  name: 'LiveTalkshow',
  data () {
    return {
      totalAll: 0,          // 列表总数目
      pageNo: 1,            // 当前页
      pageSize: 10,         // 分页显示数目
      tablePageData: [],    // 分页显示数据
      pageRefresh: true,    // 分页内容刷新

      categoryList: [],     // 栏目列表
      teacherList: [],      // 栏目老师列表
      liveRoomList: [],     // 直播室列表
      videoVendorList: [],  // 视频供应商列表
      isShowList: false,    // 显示列表区域，防止列表区内容闪屏
      isToday: true,        // 显示当天的以后节目

      liveTypes: [
        {
          code: 'live',
          name: '直播'
        },
        {
          code: 'play',
          name: '录播'
        }
      ],

      // time-picker
      startTime: '',
      endTime: '',

      // 上传图片
      imgUrl: `${Env.baseURL}/live/talkshow/upload/banner`,
      bannerImgFile: [],
      imgObj: {'image': {}},

      // 搜索框
      searchParams: {
        date: this.getNowFormatDate()
      },

      // 新增节目
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
        play_time: [
          {type: 'array', required: true, message: '请选择时间', trigger: 'blur'}
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
        play_url: [
          // {required: true, message: '请输入录播链接', trigger: 'blur'},
          {type: 'url', message: '请输入正确格式录播链接', trigger: 'blur'}
        ],
        boardcast_content: [
          {required: true, message: '请输入播报内容', trigger: 'blur'}
        ],
        description: [
          {required: true, message: '请输入海报描述', trigger: 'blur'},
          {max: 130, message: '请输入内容最大长度不超过130个字', trigger: 'blur'}
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
        play_url: '',
        boardcast_content: '',
        description: ''
      },

      // 编辑节目
      editVisible: false,
      editLoading: false,
      editFormRules: {
        category_code: [
          {required: true, message: '请选择关联栏目', trigger: 'blur'}
        ],
        teacher_id: [
          {required: true, message: '请选择栏目老师', trigger: 'blur'}
        ],
        title: [
          {required: true, message: '请输入节目名称', trigger: 'blur'}
        ],
        play_time: [
          {type: 'array', required: true, message: '请选择时间', trigger: 'blur'}
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
        play_url: [
          // {required: true, message: '请输入录播链接', trigger: 'blur'},
          {type: 'url', message: '请输入正确格式录播链接', trigger: 'blur'}
        ],
        boardcast_content: [
          {required: true, message: '请输入播报内容', trigger: 'blur'}
        ],
        description: [
          {required: true, message: '请输入海报描述', trigger: 'blur'},
          {max: 130, message: '请输入内容最大长度不超过130个字', trigger: 'blur'}
        ]
      },
      editForm: {
        code: '',
        teacher_id: '',
        title: '',
        start_time: '',
        end_time: '',
        banner_url: '',
        type: '',
        live_room_code: '',
        video_vendor_code: '',
        play_url: '',
        boardcast_content: '',
        description: ''
      },

      // 查看节目
      previewVisible: false,
      previewLoading: false,
      previewFormRules: {
        play_url: [
          // {required: true, message: '请输入录播链接', trigger: 'blur'},
          {type: 'url', message: '请输入正确格式录播链接', trigger: 'blur'}
        ],
        description: [
          {required: true, message: '请输入海报描述', trigger: 'blur'},
          {max: 130, message: '请输入内容最大长度不超过130个字', trigger: 'blur'}
        ]
      },
      previewForm: {
        code: '',
        teacher_id: '',
        title: '',
        start_time: '',
        end_time: '',
        banner_url: '',
        type: '',
        live_room_code: '',
        video_vendor_code: '',
        play_url: '',
        boardcast_content: '',
        description: ''
      }
    }
  },
  components: {
    Pagination
  },
  created: function () {
  },
  mounted: function () {
    this.getLiveTalkshowList()
  },
  methods: {
    // 搜索
    getLiveTalkshowList () {
      let params = Object.assign({}, this.searchParams)
      let now = this.getNowFormatDate()
      params.page_no = this.pageNo
      params.page_size = this.pageSize
      this.isToday = this.getTimeStamp(this.searchParams.date) >= this.getTimeStamp(now)
      HTTP.getLiveTalkshowList(params).then(res => {
        if (res.code === 0) {
          this.tablePageData = res.data.talkshow_list
          if (this.tablePageData.length > 0) {
            this.tablePageData.forEach(d => {
              d.isStart = (new Date(d.start_time).getTime()) < (new Date().getTime())
            })
          }
          this.totalAll = res.data.talkshow_cnt
          this.initPagination()
        } else {
          console.error(res.msg)
        }
        this.isShowList = true
      }).catch(err => {
        console.error(err)
        this.isShowList = true
      })
    },

    onSearch () {
      this.pageNo = 1
      this.getLiveTalkshowList()
    },
    KeySearch (ev) {
      this.onSearch()
    },

    // 从固定节目列表导入节目
    pullLiveTalkshow () {
      let params = Object.assign({}, this.searchParams)
      HTTP.pullLiveTalkshow(params).then(res => {
        if (res.code === 0) {
          this.$message.success({showClose: true, message: '复制固定节目表成功', duration: 2000})
          this.onSearch()
        } else {
          this.$message.error({showClose: true, message: '复制固定节目表失败：' + res.msg, duration: 2000})
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

    gotoPage (page) {
      this.pageNo = page
      this.getLiveTalkshowList()
    },

    // ------------------------ DateFormat ------------------
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
    },

    // -------------------------- timeFormat -------------------
    timeFormat (row, column) {
      let date = row[column.property]
      if (date === undefined) {
        return ''
      }
      return date.substr(11)
    },

    getTimeStamp (time) {
      var date = time ? new Date(time) : new Date()
      return date.getTime()
    },

    // -------------------------------- 上传图片 ------------------------------
    uploadBefore (file) {
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
          name: response.data.path.substr(response.data.path.lastIndexOf('/') + 1),
          url: response.data.path
        }]
      } else {
        console.error(response.msg)
      }
    },

    uploadError (response, file, ImgFile) {
      console.error('上传失败，请重试!')
    },

    handleRemove (file, fileList) {
      this.addForm.banner_url = ''
      this.editForm.banner_url = ''
    },

    handleExceed (file, fileList) {
      this.$alert('只能上传一张图片')
    },

    // -------------------------- 参数列表 ---------------------------------
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

    // 根据 categorycode 获取当前栏目
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

    changeLiveType () {
      this.addForm.play_url = ''
      this.editForm.play_url = ''
      this.previewForm.play_url = ''
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
    // ----------------------------- 添加节目 ------------------------------------
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
        type: '',
        live_room_code: '',
        video_vendor_code: '',
        play_url: '',
        boardcast_content: '',
        description: '',
        category_code: '',
        play_time: []
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
          _this.addForm.start_time = _this.addForm.play_time[0]
          _this.addForm.end_time = _this.addForm.play_time[1]
          HTTP.addLiveTalkshow(_this.addForm).then(res => {
            if (res.code === 0) {
              _this.$message.success({showClose: true, message: '新增成功', duration: 2000})
              _this.onSearch()
              _this.addVisible = false
            } else if (res.code === 373003) {
              _this.$message.warning({showClose: true, message: '新增成功：' + res.msg, duration: 2000})
              _this.onSearch()
              _this.addVisible = false
            } else {
              _this.$message.error({showClose: true, message: '新增失败:' + res.msg, duration: 2000})
            }
            _this.addLoading = false
          }).catch(err => {
            console.error(err)
            _this.addLoading = false
          })
        }
      })
    },

    // 查看节目
    showPreviewDialog (code) {
      this.previewVisible = true
      this.bannerImgFile = []
      // 获取节目详情
      HTTP.getLiveTalkshowInfo(code).then(res => {
        if (res.code === 0) {
          let talkshow = res.data.talkshow
          this.previewForm = {
            code: code,
            teacher_id: talkshow.teacher_id,
            title: talkshow.title,
            start_time: talkshow.start_time,
            end_time: talkshow.end_time,
            banner_url: talkshow.banner_url,
            type: talkshow.type,
            live_room_code: talkshow.live_room_code,
            video_vendor_code: talkshow.video_vendor_code,
            play_url: talkshow.play_url,
            boardcast_content: talkshow.boardcast_content,
            description: talkshow.description,
            category_code: talkshow.category_code,
            play_time: [
              talkshow.start_time,
              talkshow.end_time
            ]
          }

          let name = talkshow.banner_url.substr(talkshow.banner_url.lastIndexOf('/') + 1)
          this.bannerImgFile = [{
            name: name,
            url: talkshow.banner_url
          }]
          this.getColumnList()
          this.getTeacherList(talkshow.category_code)
          this.getLiveRoomList()
          this.getVideoVendorList()

          setTimeout(() => {
            this.$refs.previewForm.clearValidate()
          }, 100)
        } else {
          this.$message.error(res.msg)
        }
      }).catch(err => {
        console.error(err)
      })
    },

    // 编辑节目
    showEditDialog (code) {
      this.editVisible = true
      this.bannerImgFile = []
      // 获取节目详情
      HTTP.getLiveTalkshowInfo(code).then(res => {
        if (res.code === 0) {
          let talkshow = res.data.talkshow
          this.editForm = {
            code: code,
            teacher_id: talkshow.teacher_id,
            title: talkshow.title,
            start_time: talkshow.start_time,
            end_time: talkshow.end_time,
            banner_url: talkshow.banner_url,
            type: talkshow.type,
            live_room_code: talkshow.live_room_code,
            video_vendor_code: talkshow.video_vendor_code,
            play_url: talkshow.play_url,
            boardcast_content: talkshow.boardcast_content,
            description: talkshow.description,
            category_code: talkshow.category_code,
            play_time: [
              talkshow.start_time,
              talkshow.end_time
            ]
          }

          let name = talkshow.banner_url.substr(talkshow.banner_url.lastIndexOf('/') + 1)
          this.bannerImgFile = [{
            name: name,
            url: talkshow.banner_url
          }]

          this.getColumnList()
          this.getTeacherList(talkshow.category_code)
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
          _this.editForm.start_time = _this.editForm.play_time[0]
          _this.editForm.end_time = _this.editForm.play_time[1]
          HTTP.updateLiveTalkshow(_this.editForm.code, _this.editForm).then(res => {
            if (res.code === 0) {
              _this.$message.success({showClose: true, message: '编辑成功', duration: 2000})
              _this.getLiveTalkshowList()
              _this.editVisible = false
            } else if (res.code === 373003) {
              _this.$message.warning({showClose: true, message: '编辑成功：' + res.msg, duration: 2000})
              _this.getLiveTalkshowList()
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

    previewSubmit () {
      let _this = this
      this.$refs.previewForm.validate((valid) => {
        if (valid) {
          _this.previewLoading = true
          _this.previewForm.start_time = _this.previewForm.play_time[0]
          _this.previewForm.end_time = _this.previewForm.play_time[1]
          HTTP.updateLiveTalkshow(_this.previewForm.code, _this.previewForm).then(res => {
            if (res.code === 373003) {
              _this.$message.success({showClose: true, message: '编辑成功', duration: 2000})
              _this.getLiveTalkshowList()
              _this.previewVisible = false
            } else if (res.code === 0) {
              _this.$message.success({showClose: true, message: '编辑成功' + res.msg, duration: 2000})
              _this.getLiveTalkshowList()
              _this.previewVisible = false
            } else {
              _this.$message.error({showClose: true, message: '编辑失败:' + res.msg, duration: 2000})
            }
            _this.previewLoading = false
          }).catch(err => {
            console.error(err)
            _this.previewLoading = false
          })
        }
      })
    },

    // 删除节目
    delTalkshow (code) {
      let _this = this
      _this.$confirm('是否确认删除该节目？', '提示', {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        HTTP.deleteLiveTalkshow(code).then(res => {
          if (res.code === 0) {
            _this.$message.success({showClose: true, message: '删除成功', duration: 2000})
            _this.getLiveTalkshowList()
          } else {
            _this.$message.error({showClose: true, message: '删除失败:' + res.msg, duration: 2000})
          }
        }).catch(err => {
          console.error(err)
        })
      }).catch(() => {
        _this.$message({type: 'info', message: '已取消删除'})
      })
    },

    // changeTalkshowStatus开始直播室开始
    beginTalkshow (code) {
      let _this = this
      _this.$confirm('是否确认开始该直播室直播？', '提示', {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        HTTP.changeTalkshowStatus(code, {'operate': 40}).then(data => {
          if (data.code === 0) {
            _this.$message.success({showClose: true, message: '该直播室已开始直播', duration: 2000})
            _this.getLiveTalkshowList()
          } else {
            _this.$message.error({showClose: true, message: data.msg, duration: 2000})
          }
        }).catch(err => {
          console.error(err)
        })
      }).catch(() => {
        _this.$message({type: 'info', message: '已取消'})
      })
    },

    // 结束直播室直播
    endTalkshow (code) {
      let _this = this
      _this.$confirm('是否确认结束该直播室直播？', '提示', {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        HTTP.changeTalkshowStatus(code, {'operate': 50}).then(data => {
          if (data.code === 0) {
            _this.$message.success({showClose: true, message: '该直播室已结束直播', duration: 2000})
            _this.getLiveTalkshowList()
          } else {
            _this.$message.error({showClose: true, message: data.msg, duration: 2000})
          }
        }).catch(err => {
          console.error(err)
        })
      }).catch(() => {
        _this.$message({type: 'info', message: '已取消'})
      })
    },

    // 跳转录播评论
    gotoReply (id, title) {
      this.$router.push({
        name: '评论管理',
        params: {
          'type': 'talkshow',
          'article_author_user_id': id,
          'title': title
        }
      })
    },

    // 跳转直播互动
    gotoDiscuss (liveRoomCode, code, title) {
      this.$router.push({
        name: '直播互动管理',
        params: {
          'live_room_code': liveRoomCode,
          'category_code': code,
          'title': title
        }
      })
    }
  }
}
</script>
<style scoped>
.content {
  margin-top: 10px;
  text-align: center;
  line-height: 20px;
  padding: 30px 0px;
  background: #fff;
}
.preview-dialog .el-upload .el-button{
  display: none;
}
.preview-dialog .el-upload .el-upload__tip{
  display: none;
}
</style>
