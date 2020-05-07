<template>
    <div>
        <el-row class="position">
            <el-breadcrumb separator-class="el-icon-arrow-right">
                <el-breadcrumb-item>宣传管理</el-breadcrumb-item>
                <el-breadcrumb-item>论坛管理</el-breadcrumb-item>
            </el-breadcrumb>
        </el-row>

        <!-- 搜索区域 -->
        <el-row class="top-menu">
            <el-row class="nav clearfix">
                <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr">添加论坛</el-button>
            </el-row>
            <el-form :inline="true" :model="formInline">
                <el-row>
                  <el-form-item label="论坛主题" prop="theme">
                      <el-input v-model="formInline.theme" placeholder="输入主题" :maxlength="255"></el-input>
                  </el-form-item>
                  <el-form-item label="展示开始时间">
                      <el-date-picker
                              v-model="formInline.first_time"
                              align="right"
                              type="date"
                              value-format="yyyy-MM-dd"
                              format="yyyy-MM-dd"
                              placeholder="选择日期">
                      </el-date-picker>
                  </el-form-item>
                  <el-form-item label="展示结束时间">
                      <el-date-picker
                              v-model="formInline.last_time"
                              align="right"
                              type="date"
                              value-format="yyyy-MM-dd"
                              format="yyyy-MM-dd"
                              placeholder="选择日期">
                      </el-date-picker>
                  </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item>
                        <el-button type="primary" icon="el-icon-search" @click="onSearch"
                                    @keydown.13="KeySearch($event)" class="search" round>查询
                        </el-button>
                    </el-form-item>
                </el-row>
            </el-form>
        </el-row>

        <!-- 列表 -->
        <el-row class="table-menu">
            <!-- 论坛表格 -->
            <el-table :data="tablePageData" stripe style="width: 100%">
                <el-table-column fixed prop="theme" label="论坛主题"></el-table-column>
                <el-table-column prop="forum_at" label="直播开始日期" minWidth="140"></el-table-column>
                <el-table-column prop="visible_start_time" label="展示开始日期" minWidth="140"></el-table-column>
                <el-table-column prop="visible_end_time" label="展示结束日期" minWidth="140" ></el-table-column>
                <el-table-column prop="teacher" label="主讲嘉宾"></el-table-column>
                <el-table-column prop="updated_user_name" label="最后修改人"></el-table-column>
                <el-table-column prop="updated_at" label="最后修改时间" minWidth="140"></el-table-column>
                <el-table-column fixed="right" label="操作" align="center" width="120">
                    <template slot-scope="scope">
                        <el-dropdown>
                            <el-button type="primary">
                                论坛管理<i class="el-icon-arrow-down el-icon--right"></i>
                            </el-button>
                            <el-dropdown-menu slot="dropdown">
                                <el-dropdown-item @click.native="showEditDialog(scope.row.id)">编辑论坛</el-dropdown-item>
                                <el-dropdown-item @click.native="showDialog(scope.row.id)">查看论坛</el-dropdown-item>
                                <el-dropdown-item @click.native="delForum(scope.row.id)">删除论坛</el-dropdown-item>
                                <el-dropdown-item @click.native="publishAd(scope.row.id)">发布广告</el-dropdown-item>
                            </el-dropdown-menu>
                        </el-dropdown>
                    </template>
                </el-table-column>
            </el-table>

            <!-- 分页 -->
            <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
        </el-row>

        <!-- 添加论坛 -->
        <el-dialog title="添加论坛" :visible.sync="addVisible" :close-on-click-modal="false" center
                   :before-close="handleAddClose">
            <el-form :model="addForm" label-width="120px" :rules="addFormRules" ref="addForm">
                <el-row>
                    <el-form-item label="论坛主题" prop="theme">
                        <el-input v-model="addForm.theme" placeholder="请输入论坛主题" :maxlength="255"></el-input>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="论坛图片" prop="img_src">
                        <el-upload :action="imgUrl" :file-list="addImgFile" list-type="picture"
                                   :on-success="uploadSuccess" :on-error="uploadError" :data="imgObj"
                                   :before-upload="uploadBefore" :limit="1"  :on-remove="handleRemove"
                                   :on-excceed="handleExceed" :with-credentials="true">
                            <el-button size="small" type="primary">点击上传</el-button>
                            <span slot="tip" class="el-upload__tip">(要求：图片尺寸1035*240、大小不超过100k)</span>
                        </el-upload>
                    </el-form-item>
                </el-row>
                <el-row>
                  <el-form-item label="主讲嘉宾" prop="teacher">
                    <el-select v-model="addForm.teacher" placeholder="请选择">
                      <el-option v-for="item in teachers" :key="item.name" :value-key="item.name" :label="item.name" :value="item.name">
                      </el-option>
                    </el-select>
                  </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="直播开始日期" prop="forum_at" class="data_picker">
                        <el-date-picker
                                v-model="addForm.forum_at"
                                type="datetime"
                                placeholder="选择日期"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                format="yyyy-MM-dd HH:mm:ss">
                        </el-date-picker>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="展示开始日期" prop="visible_at" class="data_picker">
                        <el-date-picker
                                v-model="addForm.visible_at"
                                type="datetime"
                                placeholder="选择日期"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                format="yyyy-MM-dd HH:mm:ss"
                                width=250>
                        </el-date-picker>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="论坛时长" prop="duration">
                        <el-input v-model="addForm.duration" placeholder="输入时长" type="number" :min="0"
                                  class="short-input">
                            <template slot="append">分钟(min)</template>
                        </el-input>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="论坛简介" prop="abstract">
                        <el-input v-model="addForm.abstract" size="" placeholder="请输入论坛简介" type="textarea"
                                  :row="3"></el-input>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="展示互动ID" prop="url_key">
                        <el-input :disabled="true" v-model="addForm.url_key" placeholder="" :maxlength="50"></el-input>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="链接" prop="url_link">
                        <el-input v-model="addForm.url_link" placeholder="请输入链接" :maxlength="255" @blur="getUrlKeyOfAdd()"></el-input>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="可见人群" prop="permission_codes">
                        <div v-for="(item, i) in permission_array" :key="item.code">
                            <el-checkbox :indeterminate="checkedPackageCodes[i] && checkedPackageCodes[i].length > 0 && checkedPackageCodes[i].length < countArrItemNum(packageCodes[i])" v-model="item.granted" @change="handleCheckAllChange(item.granted, i)">{{item.name}}</el-checkbox>
                            <div v-for="(arr, j) in packageCodes[i]" :key="arr[0].name">
                              <el-checkbox-group v-model="checkedPackageCodes[i]" @change="handleCheckedPackageCodesChangeOfAdd(i)" style="padding-left: 60px;">
                                <el-checkbox v-for="packageCode in packageCodes[i][j]" :label="packageCode.code" :key="packageCode.code" border size="mini">{{packageCode.name}}</el-checkbox>
                              </el-checkbox-group>
                            </div>
                        </div>
                    </el-form-item>
                </el-row>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确定</el-button>
                <el-button @click.native="addCancel">取消</el-button>
            </div>
        </el-dialog>

        <!-- 编辑论坛 -->
        <el-dialog title="编辑论坛" :visible.sync="editVisible" :close-on-click-modal="false" center
                   :before-close="handleEditClose">
            <el-form :model="editForm" label-width="120px" :rules="editFormRules" ref="editForm">
                <el-row>
                    <el-form-item label="论坛主题" prop="theme">
                        <el-input v-model="editForm.theme" placeholder="请输入论坛主题" :maxlength="255"></el-input>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="论坛图片" prop="img_src">
                        <el-upload :action="imgUrl" :file-list="addImgFile" list-type="picture"
                                   :on-success="updateUploadSuccess" :on-error="uploadError" :data="imgObj"
                                   :before-upload="uploadBefore" :limit="1" :on-remove="handleRemove"
                                   :on-exceed="handleExceed" :with-credentials="true">
                            <el-button size="small" type="primary">点击上传</el-button>
                            <span slot="tip" class="el-upload__tip">(要求：图片尺寸1035*240、大小不超过100k)</span>
                        </el-upload>
                    </el-form-item>
                </el-row>
                <el-row>
                  <el-form-item label="主讲嘉宾" prop="teacher">
                    <el-select v-model="editForm.teacher" placeholder="请选择">
                      <el-option v-for="item in teachers" :key="item.name" :value-key="item.name" :label="item.name" :value="item.name">
                      </el-option>
                    </el-select>
                  </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="直播开始日期" prop="forum_at" class="data_picker">
                        <el-date-picker
                                v-model="editForm.forum_at"
                                type="datetime"
                                placeholder="选择日期"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                format="yyyy-MM-dd HH:mm:ss">
                        </el-date-picker>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="展示开始日期" prop="visible_at" class="data_picker">
                        <el-date-picker
                                v-model="editForm.visible_at"
                                type="datetime"
                                placeholder="选择日期"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                format="yyyy-MM-dd HH:mm:ss">
                        </el-date-picker>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="论坛时长" prop="duration">
                        <el-input v-model="editForm.duration" placeholder="输入时长" type="number" :min="0"
                                  class="short-input">
                            <template slot="append">分钟(min)</template>
                        </el-input>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="论坛简介" prop="abstract">
                        <el-input v-model="editForm.abstract" size="" placeholder="请输入论坛简介" type="textarea"
                                  :row="3"></el-input>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="展示互动ID" prop="url_key">
                        <el-input :disabled="true" v-model="editForm.url_key" placeholder="请输入展示互动ID" :maxlength="50"></el-input>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="链接" prop="url_link">
                        <el-input v-model="editForm.url_link" placeholder="请输入链接" :maxlength="255" @blur="getUrlKeyOfEdit()"></el-input>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="可见人群" prop="permission_codes">
                        <div v-for="(item, i) in permission_array" :key="item.code">
                           <el-checkbox :indeterminate="checkedPackageCodes[i] && checkedPackageCodes[i].length > 0 && checkedPackageCodes[i].length < countArrItemNum(packageCodes[i])" v-model="item.granted" @change="handleCheckAllChange(item.granted, i)">{{item.name}}</el-checkbox>
                            <div v-for="(arr, j) in packageCodes[i]" :key="arr[0].name">
                              <el-checkbox-group v-model="checkedPackageCodes[i]" @change="handleCheckedPackageCodesChangeOfEdit(i)" style="padding-left: 60px;">
                                <el-checkbox v-for="packageCode in packageCodes[i][j]" :label="packageCode.code" :key="packageCode.code" border size="mini">{{packageCode.name}}</el-checkbox>
                              </el-checkbox-group>
                            </div>
                        </div>
                    </el-form-item>
                </el-row>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click.native="editSubmit" :loading="editLoading">确定</el-button>
                <el-button @click.native="editCancel">取消</el-button>
            </div>
        </el-dialog>

        <el-dialog title="查看论坛" :visible.sync="showVisible" :close-on-click-modal="false" center>
            <el-form :model="showForm" label-width="100px">
                <el-row>
                    <el-form-item label="论坛主题">
                        {{showForm.theme}}
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="论坛图片">
                        <img :src="showForm.img_url" alt="努力生成中..." class="show_img">
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="主讲嘉宾">
                        {{showForm.teacher}}
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="直播开始日期">
                        {{showForm.forum_at}}
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="展示日期">
                        {{showForm.visible_start_time}} —— {{showForm.visible_end_time}}
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="论坛时长">
                        {{showForm.duration}} 分钟(min)
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="论坛简介">
                        {{showForm.abstract}}
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="展示互动ID">
                        {{showForm.url_key}}
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="链接">
                        {{showForm.url_link}}
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="可见人群">
                        {{showForm.permission_type}}
                    </el-form-item>
                </el-row>
            </el-form>
            <div slot="footer" class="dialog-foot">
                <el-button @click.native="showVisible = false">关闭</el-button>
            </div>
        </el-dialog>

        <!-- 添加广告 -->
        <el-dialog title="发布广告" :visible.sync="publishVisible" :close-on-click-modal="false" center
                   :before-close="handleAddAdClose">
            <el-form :model="publishForm" label-width="100px" :rules="publishFormRules" ref="publishForm">
                <el-row>
                    <el-form-item label="广告位类型" prop="location_code">
                        <el-select v-model="publishForm.location_code" placeholder="请选择" @change="changeLocation">
                            <el-option v-for="item in adLocations" :key="item.code" :value-key="item.code"
                                       :label="item.name" :value="item.code"></el-option>
                        </el-select>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="展示终端" prop="terminal_codes">
                      <el-checkbox-group v-model="publishForm.terminal_codes" >
                        <el-checkbox v-for="item in selectTerminals" :label="item.code" :key="item.code" border size="mini">{{item.name}}</el-checkbox>
                      </el-checkbox-group>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="广告名称" prop="title" v-if="publishForm.location_code !== FAST_ENTRANCE_LOCATION_CODE">
                        <el-input v-model="publishForm.title" placeholder="请输入广告名称" :maxlength="255"></el-input>
                    </el-form-item>
                </el-row>
                <el-row>
                  <el-form-item label="广告名称" prop="title" v-if="publishForm.location_code === FAST_ENTRANCE_LOCATION_CODE">
                    <el-input v-model="publishForm.title" placeholder="请输入广告名称，不超过6个字" :maxlength="6"></el-input>
                  </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="媒体类型" prop="media_code">
                        <el-select v-model="publishForm.media_code" placeholder="请选择">
                            <el-option v-for="item in mediaTypes" :key="item.code" :value-key="item.code"
                                       :label="item.name" :value="item.code"></el-option>
                        </el-select>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="图片" prop="img_src">
                        <el-upload :action="imgUrl" :file-list="addAdImgFile" list-type="picture"
                                   :on-success="addAdUploadSuccess" :on-error="uploadError" :data="imgObj"
                                   :before-upload="uploadBeforeOfPublish" :limit="1" :on-remove="handleRemove"
                                   :on-exceed="handleExceed" :with-credentials="true">
                            <el-button size="small" type="primary">点击上传</el-button>
                            <span slot="tip" class="el-upload__tip" v-if="imgSize && imgFileSize">(要求：图片尺寸{{imgSize}}、大小不超过{{imgFileSize}}k)</span>
                        </el-upload>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="是否自动弹出" v-if="popupState">
                        <el-switch
                                :active-value="1"
                                :inactive-value="0"
                                active-color="#13ce66"
                                inactive-color="#999"
                                v-model="publishForm.need_popup">
                        </el-switch>
                    </el-form-item>
                </el-row>
                <el-row v-if="popupState && publishForm.need_popup == 1">
                    <el-form-item label="弹出层海报" prop="popup_poster_url">
                        <el-upload :action="imgUrl" :file-list="addAdPopupImgFile" list-type="picture"
                                   :on-success="addAdUploadPopupImgSuccess" :on-error="uploadError" :data="imgObj"
                                   :before-upload="uploadPopupBeforeOfPublish" :limit="1" :on-remove="handleRemovePopupImg"
                                   :on-exceed="handleExceed" :with-credentials="true">
                            <el-button size="small" type="primary">点击上传</el-button>
                            <span slot="tip" class="el-upload__tip" v-if="popupImgSize && popupImgFileSize">(要求：图片尺寸{{popupImgSize}}、大小不超过{{popupImgFileSize}}k)</span>
                        </el-upload>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="链接" prop="url_link">
                        <el-input v-model="publishForm.url_link" placeholder="请输入链接"
                                  :maxlength="255"></el-input>
                    </el-form-item>
                </el-row>
                <el-row>
                  <el-form-item label="跳转类型">
                    <el-input v-model="publishForm.jump_type" placeholder="请输入跳转类型" :maxlength="255"></el-input>
                  </el-form-item>
                </el-row>
                <el-row>
                  <el-form-item label="跳转参数" prop="jump_params">
                    <el-input type="textarea" v-model="publishForm.jump_params" placeholder="请输入跳转参数" :maxlength="500"></el-input>
                  </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="展示时间" prop="show_time">
                        <el-date-picker
                                v-model="publishForm.show_time"
                                type="datetimerange"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期"
                                value-format="yyyy-MM-dd HH:mm:ss"
                                format="yyyy-MM-dd HH:mm:ss">
                        </el-date-picker>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="业务类型" prop="operation_code">
                        <el-select :disabled="true" v-model="publishForm.operation_code" placeholder="请选择">
                            <el-option v-for="item in operationTypes" :key="item.code" :value-key="item.code"
                                       :label="item.name" :value="item.code"></el-option>
                        </el-select>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="可见人群" prop="permission_codes">
                        <div v-for="(item,i) in permission_array" :key="item.code">
                            <el-checkbox :indeterminate="checkedPackageCodes[i] && checkedPackageCodes[i].length > 0 && checkedPackageCodes[i].length < countArrItemNum(packageCodes[i])" v-model="item.granted" @change="handleCheckAllChange(item.granted, i)">{{item.name}}</el-checkbox>
                            <div v-for="(arr, j) in packageCodes[i]" :key="arr[0].name">
                              <el-checkbox-group v-model="checkedPackageCodes[i]" @change="handleCheckedPackageCodesChangeOfPublish(i)" style="padding-left: 60px;">
                                <el-checkbox v-for="packageCode in packageCodes[i][j]" :label="packageCode.code" :key="packageCode.code" border size="mini">{{packageCode.name}}</el-checkbox>
                              </el-checkbox-group>
                            </div>
                        </div>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="排序序号" prop="sort_num">
                        <el-input v-model="publishForm.sort_num" placeholder="不填默认为“0”，按记录ID排序" type="number"
                                  :min="0"></el-input>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="是否禁用">
                        <el-switch
                                :active-value="1"
                                :inactive-value="0"
                                active-color="#13ce66"
                                inactive-color="#999"
                                v-model="publishForm.disabled">
                        </el-switch>
                    </el-form-item>
                </el-row>
            </el-form>
            <div slot="footer" class="dialog-foot">
                <el-button type="primary" @click.native="publishSubmit" :loading="publishLoading">保存</el-button>
                <el-button @click.native="addAdCancel">取消</el-button>
            </div>
        </el-dialog>
    </div>
</template>
<script>
  import HTTP from '../../http/api_propaganda'
  import Env from '../../http/env'
  import Pagination from '@/components/Pagination'

  export default {
    name: 'Forum',
    data () {
      return {
        formInline: {
          theme: '',
          first_time: '',
          last_time: ''
        },

        // 缓存搜索数据
        searchParams: {
          theme: '',
          first_time: '',
          last_time: ''
        },
        // 分页初始化
        totalAll: 0,
        pageSize: 10,
        pageNo: 1,
        pageRefresh: true,

        tablePageData: [],
        terminals: [],
        selectTerminals: [],
        adLocations: [],
        mediaTypes: [],
        operationTypes: [],
        packages: [],
        teachers: [],
        imgSize: '',
        imgFileSize: '',
        popupImgSize: '',
        popupImgFileSize: '',
        // 是否弹出层
        popupState: false,
        checkedPackageCodes: [],
        packageCodes: [],
        codeType: [],
        // 可见人群
        permission_array: [],
        // 限制点击
        clickFlag: true, // 是否可以点击提交

        // 快速功能入口 广告位Code
        FAST_ENTRANCE_LOCATION_CODE: 'fast_entrance',

        // 新增论坛
        addVisible: false,
        addLoading: false,
        addFormRules: {
          theme: [
            {required: true, message: '请输入论坛主题', trigger: 'blur'}
          ],
          img_src: [
            {required: true, message: '请上传图片', trigger: 'blur'}
          ],
          teacher: [
            {required: true, message: '请输入主讲嘉宾', trigger: 'blur'}
          ],
          forum_at: [
            {required: true, message: '选择日期', trigger: 'blur'},
            {validator: this.checkAddForumTime, trigger: 'blur'}
          ],
          visible_at: [
            {required: true, message: '选择日期', trigger: 'blur'},
            {validator: this.checkAddVisibleTime, trigger: 'blur'}
          ],
          duration: [
            {required: true, message: '输入时长', trigger: 'blur'},
            {validator: this.checkDuration, trigger: 'blur'}
          ],
          abstract: [
            {required: true, message: '输入论坛简介', trigger: 'blur'}
          ],
          url_link: [
            {type: 'url', required: true, message: '请输入展示互动链接', trigger: 'blur'},
            {validator: this.checkeUrlKeyOfAdd, trigger: 'blur'}
          ],
          permission_codes: [
            {required: true, validator: this.checkPermissionCode, trigger: 'blur'}
          ]
        },
        addForm: {
          theme: '',
          img_src: '',
          url_key: '',
          url_link: '',
          forum_at: '',
          visible_at: '',
          duration: '',
          teacher: '',
          abstract: '',
          permission_codes: []
        },

        // 上传图片 上传图片预览在addImgFile数组里面 [{name:'', url: ''}]
        addImgFile: [],
        addAdImgFile: [],
        addAdPopupImgFile: [],
        imgUrl: `${Env.baseURL}/propaganda/img/upload`,
        imgObj: {'image': {}},

        // 编辑论坛
        editVisible: false,
        editLoading: false,
        editFormRules: {
          theme: [
            {required: true, message: '请输入论坛主题', trigger: 'blur'}
          ],
          img_src: [
            {required: true, message: '请上传图片', trigger: 'blur'}
          ],
          teacher: [
            {required: true, message: '请输入主讲嘉宾', trigger: 'blur'}
          ],
          forum_at: [
            {required: true, message: '选择日期', trigger: 'blur'},
            {validator: this.checkEditForumTime, trigger: 'blur'}
          ],
          visible_at: [
            {required: true, message: '选择日期', trigger: 'blur'},
            {validator: this.checkEditVisibleTime, trigger: 'blur'}
          ],
          duration: [
            {required: true, message: '输入时长', trigger: 'blur'},
            {validator: this.checkDuration, trigger: 'blur'}
          ],
          abstract: [
            {required: true, message: '输入论坛简介', trigger: 'blur'}
          ],
          url_link: [
            {type: 'url', required: true, message: '请输入展示互动链接', trigger: 'blur'},
            {validate: this.checkeUrlKeyOfEdit, trigger: 'blur'}
          ],
          permission_codes: [
            {required: true, validator: this.checkPermissionCode, trigger: 'blur'}
          ]
        },
        editForm: {
          forum_id: '',
          theme: '',
          img_src: '',
          url_key: '',
          url_link: '',
          forum_at: '',
          visible_at: '',
          duration: '',
          teacher: '',
          abstract: '',
          permission_codes: []
        },
        // 查看论坛
        showVisible: false,
        showForm: {
          theme: '',
          img_src: '',
          url_key: '',
          url_link: '',
          forum_at: '',
          visible_start_time: '',
          visible_end_time: '',
          duration: '',
          teacher: '',
          abstract: '',
          permission_type: []
        },

        // 发布广告的默认url
        publishAdOfDefaultUrl: Env.forumH5URL + '/forum/forum.html?id=',

        // 新增广告
        publishVisible: false, // 是否显示
        publishLoading: false,
        publishFormRules: {
          media_code: [
            {required: true, message: '请选择媒体类型', trigger: 'blur'}
          ],
          location_code: [
            {required: true, message: '请选择广告位', trigger: 'blur'}
          ],
          operation_code: [
            {required: true, message: '请选择业务类型', trigger: 'blur'}
          ],
          terminal_codes: [
            {type: 'array', required: true, message: '请至少选择一个', trigger: 'change'}
          ],
          img_src: [
            {required: true, message: '请上传图片', trigger: 'blur'}
          ],
          popup_poster_url: [
            {required: true, validator: this.checkPopupImgOfAdd, trigger: 'blur'}
          ],
          title: [
            {required: true, message: '请输入广告名称', trigger: 'blur'}
          ],
          url_link: [
            {type: 'url', required: true, message: '请输入链接', trigger: 'blur'}
          ],
          jump_params: [
            {validator: this.checkJSONType, trigger: 'blur'}
          ],
          show_time: [
            {type: 'array', required: true, message: '请选择时间', trigger: 'blur'}
          ],
          sort_num: [
            {validator: this.checkSortNum, trigger: 'blur'}
          ],
          permission_codes: [
            {required: true, validator: this.checkPermissionCode, trigger: 'blur'}
          ]
        },
        publishForm: {
          operation_id: '',
          media_code: 'image',
          location_code: '',
          operation_code: '',
          title: '',
          img_src: '',
          url_link: '',
          jump_type: '',
          jump_params: '',
          show_time: [],
          terminal_codes: [],
          sort_num: 0,
          permission_codes: [],
          disabled: 0,
          need_popup: 0,
          popup_poster_url: ''
        }
      }
    },
    components: {
      Pagination
    },
    created: function () {
    },
    mounted: function () {
      this.getAdTerminals()
      this.getAdLocations()
      this.getAdMediaTypes()
      this.getOperationTypes()
      this.getPackages()
      this.getTeachers()
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
        this.getList()
      },

      getList () {
        var params = Object.assign({}, this.searchParams)
        params.page_no = this.pageNo
        params.page_size = this.pageSize
        HTTP.getForumList(params).then(res => {
          if (res.code === 0) {
            this.tablePageData = res.data.forum_list
            this.totalAll = res.data.forum_cnt
            this.initPagination()
          } else {
            this.$message.error({showClose: true, message: '获取论坛列表失败：' + res.msg, duration: 2000})
          }
        }).catch(err => {
          console.error(err)
        })
      },

      updateList () {
        this.getList()
      },

      onSearch () {
        this.pageNo = 1
        this.searchParams = this.formInline
        let searchParams = this.filterParams(this.searchParams)
        HTTP.searchForums(searchParams).then(res => {
          if (res.code === 0) {
            this.tablePageData = res.data.forum_list
            this.totalAll = res.data.forum_cnt
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

      // 获取广告展示终端类型
      getAdTerminals () {
        HTTP.getAdTerminals().then(data => {
          if (data.code === 0) {
            this.terminals = data.data
          } else {
            console.log(data.msg)
          }
        }).catch(err => {
          console.error(err)
        })
      },

      getAdTerminalsOfLocationCode (locationCode) {
        HTTP.getAdTerminalsOfLocationCode(locationCode).then(data => {
          if (data.code === 0) {
            this.selectTerminals = data.data
          } else {
            console.log(data.msg)
          }
        }).catch(err => {
          console.error(err)
        })
      },
      // 获取广告广告位
      getAdLocations () {
        HTTP.getAdLocations().then(data => {
          if (data.code === 0) {
            this.adLocations = this.removeInvalidLocation(data.data)
          } else {
            console.log(data.msg)
          }
        }).catch(err => {
          console.error(err)
        })
      },

      // 去除无效的location
      removeInvalidLocation (arr) {
        let result = []
        for (let i = 0; i < arr.length; i++) {
          result.push(arr[i])
        }
        return result
      },
      // 获取广告的媒体类型
      getAdMediaTypes () {
        HTTP.getAdMediaTypes().then(data => {
          if (data.code === 0) {
            this.mediaTypes = data.data
          } else {
            console.log(data.msg)
          }
        }).catch(err => {
          console.error(err)
        })
      },
      // 获取广告的业务类型
      getOperationTypes () {
        HTTP.getOperationTypes().then(data => {
          if (data.code === 0) {
            this.operationTypes = data.data
          } else {
            console.log(data.msg)
          }
        }).catch(err => {
          console.error(err)
        })
      },
      // 获取套餐列表
      getPackages () {
        let _this = this
        HTTP.getPackages().then(data => {
          if (data.code === 0) {
            this.packages = data.data
            data.data.forEach(d => {
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

      // 获取老师列表
      getTeachers () {
        HTTP.getTeachers().then(data => {
          if (data.code === 0) {
            this.teachers = data.data
          } else {
            console.log('服务器错误!')
          }
        }).catch(err => {
          console.log(err)
        })
      },

      changeLocation (value) {
        this.publishForm.terminal_codes = []
        this.getAdTerminalsOfLocationCode(value)
        this.setImgUrl(value)
      },

      setImgUrl (value) {
        this.addAdImgFile = []
        this.addAdPopupImgFile = []
        this.handleRemove()
        this.handleRemovePopupImg()
        this.adLocations.forEach(d => {
          if (d.code === value) {
            this.imgSize = d.size
            this.imgFileSize = d.file_size
            this.popupImgSize = d.popup_img_size
            this.popupImgFileSize = d.popup_img_file_size
            if (d.popup_img_size === null) {
              this.popupState = false
            } else {
              this.popupState = true
            }
          }
        })
      },

      handleCheckAllChange (val, i) {
        this.checkedPackageCodes[i] = val ? this.getAllArrayItem(this.packageCodes[i]) : []
        // this.isIndeterminates[i] = false
      },
      handleCheckedPackageCodesChangeOfEdit (i) {
        let checkedCount = this.checkedPackageCodes[i].length
        this.permission_array[i].granted = checkedCount === this.countArrItemNum(this.packageCodes[i])
        // this.isIndeterminates[i] = checkedCount > 0 && checkedCount < this.rights[i].length
        // console.log(this.isIndeterminates[i])
      },
      handleCheckedPackageCodesChangeOfAdd (i) {
        let checkedCount = this.checkedPackageCodes[i].length
        this.permission_array[i].granted = checkedCount === this.countArrItemNum(this.packageCodes[i])
      },
      handleCheckedPackageCodesChangeOfPublish (i) {
        let checkedCount = this.checkedPackageCodes[i].length
        this.permission_array[i].granted = checkedCount === this.countArrItemNum(this.packageCodes[i])
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
      getUrlKeyOfAdd () {
        if (this.addForm.url_link.indexOf('-') !== -1) {
          this.addForm.url_key = this.addForm.url_link.slice(this.addForm.url_link.lastIndexOf('-') + 1)
        } else {
          this.addForm.url_key = ''
        }
      },
      getUrlKeyOfEdit () {
        if (this.editForm.url_link.indexOf('-') !== -1) {
          this.editForm.url_key = this.editForm.url_link.slice(this.editForm.url_link.lastIndexOf('-') + 1)
        } else {
          this.editForm.url_key = ''
        }
      },

      checkSortNum (rule, value, callback) {
        if (value < 0) {
          callback(new Error('最小值为0！！'))
        } else if (!this.isInteger(value)) {
          callback(new Error('请输入正整数'))
        } else {
          callback()
        }
      },

      isInteger (obj) {
        return obj % 1 === 0
      },

      checkAddForumTime (rule, value, callback) {
        let visibleTime
        let forumTime
        if (this.checkForumOrVisibleTime(this.addForm.visible_at, this.addForm.forum_at)) {
          visibleTime = new Date(this.addForm.visible_at)
          forumTime = new Date(this.addForm.forum_at)
          if (visibleTime > forumTime) {
            callback(new Error('论坛直播时间需要在论坛展示时间之后'))
          } else {
            callback()
          }
        } else {
          callback()
        }
      },

      checkAddVisibleTime (rule, value, callback) {
        let visibleTime
        let forumTime
        if (this.checkForumOrVisibleTime(this.addForm.visible_at, this.addForm.forum_at)) {
          visibleTime = new Date(this.addForm.visible_at)
          forumTime = new Date(this.addForm.forum_at)
          if (visibleTime > forumTime) {
            callback(new Error('论坛展示时间需要在论坛直播时间之前'))
          } else {
            callback()
          }
        } else {
          callback()
        }
      },

      checkEditForumTime (rule, value, callback) {
        let visibleTime
        let forumTime
        if (this.checkForumOrVisibleTime(this.editForm.visible_at, this.editForm.forum_at)) {
          visibleTime = new Date(this.editForm.visible_at)
          forumTime = new Date(this.editForm.forum_at)
          if (visibleTime > forumTime) {
            callback(new Error('论坛直播时间需要在论坛展示时间之后'))
          } else {
            callback()
          }
        } else {
          callback()
        }
      },

      checkEditVisibleTime (rule, value, callback) {
        let visibleTime
        let forumTime
        if (this.checkForumOrVisibleTime(this.editForm.visible_at, this.editForm.forum_at)) {
          visibleTime = new Date(this.editForm.visible_at)
          forumTime = new Date(this.editForm.forum_at)
          if (visibleTime > forumTime) {
            callback(new Error('论坛展示时间需要在论坛直播时间之前'))
          } else {
            callback()
          }
        } else {
          callback()
        }
      },

      checkForumOrVisibleTime (visibleTime, forumTime) {
        let result = false
        if (visibleTime !== '' && visibleTime !== null && forumTime !== '' && forumTime !== null) {
          result = true
        }
        return result
      },

      checkDuration (rule, value, callback) {
        if (value < 0) {
          callback(new Error('最小值为0！！'))
        } else if (!this.isInteger(value)) {
          callback(new Error('请输入正整数'))
        } else {
          callback()
        }
      },

      checkPermissionCode (rule, value, callback) {
        let codeList = []
        this.checkedPackageCodes.forEach(d => {
          codeList = codeList.concat(d)
        })
        if (codeList.length <= 0) {
          callback(new Error('至少选择一个选项'))
        } else {
          callback()
        }
      },

      checkeUrlKeyOfAdd (rule, value, callback) {
        if (this.addForm.url_key === null || this.addForm.url_key.length === 0) {
          callback(new Error('请输入正确的展示互动链接'))
        } else {
          callback()
        }
      },
      checkeUrlKeyOfEdit (rule, value, callback) {
        if (this.editForm.url_key === null || this.editForm.url_key.length === 0) {
          callback(new Error('请输入正确的展示互动链接'))
        } else {
          callback()
        }
      },
      checkPopupImgOfAdd (rule, value, callback) {
        if (this.publishForm.need_popup === 0) {
          callback()
        }
        if (this.publishForm.need_popup === 1 && this.publishForm.popup_poster_url && this.publishForm.popup_poster_url.length > 0) {
          callback()
        } else {
          callback(new Error('请上传图片'))
        }
      },
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
              callback(new Error('格式错误, 请输入正确的JSON格式'))
            }
          } else {
            callback(new Error('格式错误, 请输入正确的JSON格式'))
          }
        }
      },

      // 添加论坛
      showAddDialog () {
        let _this = this
        this.packageCodes = []
        this.checkedPackageCodes = []
        this.addVisible = true
        this.addImgFile = []
        this.addForm = {
          theme: '',
          img_src: '',
          url_key: '',
          url_link: '',
          forum_at: '',
          visible_at: '',
          duration: '',
          teacher: '',
          abstract: '',
          permission_codes: []
        }

        if (this.packages.length > 0) {
          this.permission_array = JSON.parse(JSON.stringify(this.packages))
          this.permission_array.forEach(d => {
            let packagesCode = []
            let checkedPackageCode = []
            if (d.child) {
              d.child.forEach(child => {
                let packageCodeRow = []
                child.forEach(item => {
                  let packageCode = []
                  packageCode.name = item.name
                  packageCode.code = item.code
                  packageCodeRow.push(packageCode)
                })
                packagesCode.push(packageCodeRow)
              })
            }
            this.packageCodes.push(packagesCode)
            this.checkedPackageCodes.push(checkedPackageCode)
          })
        }

        setTimeout(() => {
          _this.$refs.addForm.clearValidate()
        }, 100)
      },
      addSubmit () {
        if (this.clickFlag) {
          let _this = this
          this.$refs.addForm.validate((valid) => {
            if (valid) {
              this.clickFlag = false
              let codeList = []
              this.checkedPackageCodes.forEach(d => {
                codeList = codeList.concat(d)
              })
              this.addForm.permission_codes = codeList
              HTTP.createForum(_this.addForm).then(data => {
                if (data.code === 0) {
                  _this.$message.success({showClose: true, message: data.msg, duration: 2000})
                  _this.addVisible = false
                  _this.updateList()
                  setTimeout(() => {
                    _this.clickFlag = true
                  }, 500)
                } else {
                  _this.$message.error(data.msg)
                  this.clickFlag = true
                }
              }).catch(err => {
                console.error(err)
                this.clickFlag = true
              })
            }
          })
        }
      },

      addCancel () {
        this.addVisible = false
      },
      handleAddClose () {
        this.addVisible = false
      },

      // 编辑论坛
      showEditDialog (id) {
        let _this = this
        this.checkedPackageCodes = []
        this.packageCodes = []
        this.editVisible = true
        this.addImgFile = []
        HTTP.findForumById(id).then(data => {
          if (data.code === 0) {
            this.editForm = {
              forum_id: id,
              theme: data.data.theme,
              img_src: data.data.img_src,
              url_key: data.data.url_key,
              url_link: data.data.url_link,
              forum_at: data.data.forum_at,
              visible_at: data.data.visible_at,
              duration: data.data.duration,
              teacher: data.data.teacher,
              abstract: data.data.abstract,
              permission_codes: ''
            }
            this.permission_array = data.data.permission_array
            if (data.data.permission_array) {
              let i = 0
              data.data.permission_array.forEach(d => {
                let packagesCode = []
                let checkedPackageCode = []
                if (d.child) {
                  d.child.forEach(child => {
                    let packageCodeRow = []
                    child.forEach(item => {
                      let packageCode = []
                      packageCode.name = item.name
                      packageCode.code = item.code
                      packageCodeRow.push(packageCode)
                      if (item.granted) {
                        checkedPackageCode.push(item.code)
                      }
                    })
                    packagesCode.push(packageCodeRow)
                  })
                }
                this.packageCodes.push(packagesCode)
                this.checkedPackageCodes.push(checkedPackageCode)
                this.permission_array[i].granted = checkedPackageCode.length === this.countArrItemNum(this.packageCodes[i])
                i++
              })
            }

            let name = data.data.img_url.substr(data.data.img_url.lastIndexOf('/') + 1)
            this.addImgFile = [{
              name: name,
              url: data.data.img_url
            }]
            setTimeout(() => {
              _this.$refs.editForm.clearValidate()
            }, 100)
          } else {
            this.$message.error(data.msg)
          }
        }).catch(err => {
          console.error(err)
        })
      },

      editSubmit () {
        let _this = this
        this.$refs.editForm.validate((valid) => {
          if (valid) {
            let codeList = []
            this.checkedPackageCodes.forEach(d => {
              codeList = codeList.concat(d)
            })
            _this.editForm.permission_codes = codeList
            HTTP.updateForum(_this.editForm).then(data => {
              if (data.code === 0) {
                _this.$message.success({showClose: true, message: data.msg, duration: 2000})
                _this.editVisible = false
                _this.updateList()
              } else {
                _this.$message.error(data.msg)
              }
            }).catch(err => {
              console.error(err)
            })
          }
        })
      },

      editCancel () {
        this.editVisible = false
      },
      handleEditClose () {
        this.editVisible = false
      },

      // 查看论坛
      showDialog (id) {
        HTTP.findForumById(id).then(data => {
          if (data.code === 0) {
            this.showForm = {
              theme: data.data.theme,
              img_url: data.data.img_url,
              url_key: data.data.url_key,
              url_link: data.data.url_link,
              forum_at: data.data.forum_at,
              visible_start_time: data.data.visible_at,
              visible_end_time: this.addDate(data.data.forum_at, 2),
              duration: data.data.duration,
              teacher: data.data.teacher,
              abstract: data.data.abstract,
              permission_type: this.getTypesByCode(this.codeType, data.data.permission_codes)
            }
          } else {
            this.$message.error(data.msg)
          }
        }).catch(err => {
          console.error(err)
        })

        this.showVisible = true
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

      delForum (id) {
        HTTP.getAdListDataOfForumId(id).then(data => {
          if (data.code === 0) {
            let h = this.$createElement
            let adNames = this.getParamOfArray(data.data, 'title')
            let messageArr = []
            if (adNames.length > 0) {
              adNames = adNames.join('、')
              messageArr.push(h('span', null, '该论坛有' + data.data.length + '条广告'))
              messageArr.push(h('span', {style: 'color: red'}, '(' + adNames + ')'))
              messageArr.push(h('span', null, '正在展示或即将展示，删除论坛会同时删除该论坛发布的广告，'))
            }
            messageArr.push(h('span', null, '确认删除该论坛？'))
            this.$msgbox({
              title: '提示',
              message: h('p', null, messageArr),
              showCancelButton: true,
              confirmButtonText: '确认',
              cancelButtonText: '取消'
            }).then(() => {
              HTTP.deleteForum(id).then(data => {
                if (data.code === 0) {
                  this.$message.success({showClose: true, message: '删除成功', duration: 2000})
                  this.updateList()
                } else {
                  this.$message.error(data.msg)
                }
              }).catch(err => {
                console.error(err)
              })
            }).catch(() => {
              this.$message({
                type: 'info',
                message: '已取消删除'
              })
            })
          } else {
            this.$message.error(data.msg)
          }
        }).catch(err => {
          console.error(err)
        })
      },

      getParamOfArray (arr, str) {
        let result = []
        arr.forEach(d => {
          result.push(d[str])
        })
        return result
      },

      publishAd (id) {
        let _this = this
        this.packageCode = []
        this.checkedPackageCodes = []
        this.publishVisible = true
        this.addAdImgFile = []
        this.addAdPopupImgFile = []
        this.imgSize = ''
        this.imgFileSize = ''
        this.popupImgSize = ''
        this.popupImgFileSize = ''
        this.popupState = false
        HTTP.findForumById(id).then(data => {
          if (data.code === 0) {
            this.publishForm = {
              operation_id: id,
              media_code: 'image',
              location_code: '',
              operation_code: `${Env.FORUM_OPERATION_CODE}`,
              title: data.data.theme,
              img_src: '',
              url_link: this.publishAdOfDefaultUrl + data.data.id,
              jump_type: '',
              jump_params: '',
              show_time: [
                data.data.visible_at,
                // data.data.forum_at
                this.addDate(data.data.forum_at, 1)
              ],
              terminal_codes: [],
              sort_num: 0,
              disabled: 0,
              permission_codes: [],
              need_popup: 0,
              popup_poster_url: ''
            }

            this.permission_array = data.data.permission_array
            if (data.data.permission_array) {
              let i = 0
              data.data.permission_array.forEach(d => {
                let packagesCode = []
                let checkedPackageCode = []
                if (d.child) {
                  d.child.forEach(child => {
                    let packageCodeRow = []
                    child.forEach(item => {
                      let packageCode = []
                      packageCode.name = item.name
                      packageCode.code = item.code
                      packageCodeRow.push(packageCode)
                      if (item.granted) {
                        checkedPackageCode.push(item.code)
                      }
                    })
                    packagesCode.push(packageCodeRow)
                  })
                }
                this.packageCodes.push(packagesCode)
                this.checkedPackageCodes.push(checkedPackageCode)
                this.permission_array[i].granted = checkedPackageCode.length === this.countArrItemNum(this.packageCodes[i])
                i++
              })
            }

            this.selectTerminals = []

            setTimeout(() => {
              _this.$refs.publishForm.clearValidate()
            }, 100)
          } else {
            this.$message.error(data.msg)
          }
        }).catch(err => {
          console.error(err)
        })
      },

      addDate (dateArg, days) {
        if (days === undefined || days === '') {
          days = 1
        }
        let date = new Date(dateArg)
        date.setDate(date.getDate() + days)
        let month = date.getMonth() + 1
        let day = date.getDate()
        let h = date.getHours()
        let m = date.getMinutes()
        let s = date.getSeconds()
        return date.getFullYear() + '-' + this.getFormatDate(month) + '-' + this.getFormatDate(day) + ' ' + this.getFormatDate(h) + ':' + this.getFormatDate(m) + ':' + this.getFormatDate(s)
      },
      getFormatDate (arg) {
        if (arg === undefined || arg === '') {
          return ''
        }

        let result = arg + ''
        if (result.length < 2) {
          result = '0' + result
        }
        return result
      },

      publishSubmit () {
        if (this.clickFlag) {
          this.$refs.publishForm.validate((valid) => {
            if (valid) {
              this.clickFlag = false
              let codeList = []
              this.checkedPackageCodes.forEach(d => {
                codeList = codeList.concat(d)
              })
              this.publishForm.permission_codes = codeList
              this.addAdForm()
            }
          })
        }
      },
      addAdForm () {
        let _this = this
        this.$refs.publishForm.validate((valid) => {
          if (valid) {
            if (!_this.popupState || parseInt(_this.publishForm.need_popup) === 0) {
              _this.publishForm.need_popup = 0
              _this.publishForm.popup_poster_url = ''
            }
            HTTP.createAd(_this.publishForm).then(data => {
              if (data.code === 0) {
                _this.$message.success({showClose: true, message: data.msg, duration: 2000})
                _this.publishVisible = false
                _this.updateList()
                setTimeout(function () {
                  _this.clickFlag = true
                }, 500)
              } else {
                _this.$message.error(data.msg)
                this.clickFlag = true
              }
            }).catch(err => {
              console.error(err)
              this.clickFlag = true
            })
          }
        })
      },

      addAdCancel () {
        this.publishVisible = false
      },
      handleAddAdClose () {
        this.publishVisible = false
      },

      // 上传图片模块
      uploadBefore (file) {
        console.log(file)

        let imgType = [
          'image/jpeg',
          'image/png',
          'image/jpg',
          'image/gif',
          'image/svg'
        ]
        let isJPG = false

        for (var i = 0; i < imgType.length; i++) {
          if (imgType[i] === file.type) {
            isJPG = true
          }
        }

        const isLt100K = file.size / 1024 < 100

        if (!isJPG) {
          this.$message.error('上传图片只能是 JPG/PNG/GIF/SVG 格式!')
        }
        if (!isLt100K) {
          this.$message.error('上传图片大小不能超过 100k!')
        }
        if (isJPG && isLt100K) {
          this.imgObj.image = file
        }

        return isJPG && isLt100K
      },

      // 发布广告上传图片模块
      uploadBeforeOfPublish (file) {
        if (!this.imgFileSize) {
          this.$message.error('请选择广告位类型')
          return false
        }

        let imgType = [
          'image/jpeg',
          'image/png',
          'image/jpg',
          'image/gif',
          'image/svg'
        ]
        let isJPG = false

        for (var i = 0; i < imgType.length; i++) {
          if (imgType[i] === file.type) {
            isJPG = true
          }
        }

        const isLtImgFileSize = file.size / 1024 < this.imgFileSize

        if (!isJPG) {
          this.$message.error('上传图片只能是 JPG/PNG/GIF/SVG 格式!')
        }
        if (!isLtImgFileSize) {
          this.$message.error('上传图片大小不能超过 ' + this.imgFileSize + 'k!')
        }
        if (isJPG && isLtImgFileSize) {
          this.imgObj.image = file
        }

        return isJPG && isLtImgFileSize
      },

      // 发布广告上传弹出层图片图片模块
      uploadPopupBeforeOfPublish (file) {
        if (!this.popupImgFileSize) {
          this.$message.error('请选择广告位类型')
          return false
        }

        let imgType = [
          'image/jpeg',
          'image/png',
          'image/jpg',
          'image/gif',
          'image/svg'
        ]
        let isJPG = false

        for (var i = 0; i < imgType.length; i++) {
          if (imgType[i] === file.type) {
            isJPG = true
          }
        }

        const isLtPopupImgFileSize = file.size / 1024 < this.popupImgFileSize

        if (!isJPG) {
          this.$message.error('上传图片只能是 JPG/PNG/GIF/SVG 格式!')
        }
        if (!isLtPopupImgFileSize) {
          this.$message.error('上传图片大小不能超过 ' + this.popupImgFileSize + 'k!')
        }
        if (isJPG && isLtPopupImgFileSize) {
          this.imgObj.image = file
        }

        return isJPG && isLtPopupImgFileSize
      },
      // 上传图片成功
      uploadSuccess (response, file, addImgFile) {
        if (response.code === 0) {
          this.addForm.img_src = response.data.cnd_relatively_file_path
          this.addImgFile = [{
            name: response.data.file_path.substr(response.data.file_path.lastIndexOf('/') + 1),
            url: response.data.file_path
          }]
        } else {
          console.error(response.msg)
        }
      },

      uploadError (response, file, addImgFile) {
        console.log('上传失败，请重试')
      },

      handleRemove (file, fileList) {
        this.addForm.img_src = ''
        this.editForm.img_src = ''
        this.publishForm.img_src = ''
      },
      handleRemovePopupImg () {
        this.publishForm.popup_poster_url = ''
      },

      handleExceed (file, addImgFile) {
        this.$alert('只能上传一张图片')
      },

      updateUploadSuccess (response, file, addImgFile) {
        if (response.code === 0) {
          this.editForm.img_src = response.data.cnd_relatively_file_path
          this.addImgFile = [{
            name: response.data.file_path.substr(response.data.file_path.lastIndexOf('/') + 1),
            url: response.data.file_path
          }]
        }
      },
      addAdUploadSuccess (response, file, addImgFile) {
        if (response.code === 0) {
          this.publishForm.img_src = response.data.cnd_relatively_file_path
          this.addAdImgFile = [{
            name: response.data.file_path.substr(response.data.file_path.lastIndexOf('/') + 1),
            url: response.data.file_path
          }]
        } else {
          console.log(response.msg)
        }
      },
      addAdUploadPopupImgSuccess (response, file, addImgFile) {
        if (response.code === 0) {
          this.publishForm.popup_poster_url = response.data.cnd_relatively_file_path
          this.addAdPopupImgFile = [{
            name: response.data.file_path.substr(response.data.file_path.lastIndexOf('/') + 1),
            url: response.data.file_path
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
      }
    }
  }
</script>
<style scoped lang="less">
    .show_img {
        max-width: 500px;
        width: 90%;
    }

    .checkbox-right-margin {
        margin-left: 0px;
        margin-right: 30px;
    }

    .short-input {
        width: 200px;
    }

    .data_picker .el-input {
      width: 185px;
    }
</style>