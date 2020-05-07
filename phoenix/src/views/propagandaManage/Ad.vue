<template>
    <div>
        <el-row class="position">
            <el-breadcrumb separator-class="el-icon-arrow-right">
                <el-breadcrumb-item>宣传管理</el-breadcrumb-item>
                <el-breadcrumb-item>广告管理</el-breadcrumb-item>
            </el-breadcrumb>
        </el-row>

        <!-- 搜索区域 -->
        <el-row class="top-menu">
            <el-row class="nav clearfix">
                <el-button type="primary" icon="el-icon-plus" round @click="showAddDialog" class="fr">添加广告</el-button>
            </el-row>
            <el-form :inline="true" :model="formInline">
                <el-row>
                    <el-form-item label="展示开始时间">
                        <el-date-picker
                                v-model="formInline.start_time"
                                align="right"
                                type="date"
                                value-format="yyyy-MM-dd"
                                format="yyyy-MM-dd"
                                placeholder="选择日期">
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item label="展示结束时间">
                        <el-date-picker
                                v-model="formInline.end_time"
                                align="right"
                                type="date"
                                value-format="yyyy-MM-dd"
                                format="yyyy-MM-dd "
                                placeholder="选择日期">
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item label="广告位类型">
                      <el-select v-model="formInline.location_code" clearable placeholder="请选择">
                        <el-option v-for="item in adLocations" :value-key="item.code" :key="item.code" :label="item.name" :value="item.code"></el-option>
                      </el-select>
                    </el-form-item>
                    <el-form-item label="终端类型">
                      <el-select v-model="formInline.terminal_code" clearable placeholder="请选择">
                        <el-option v-for="item in terminals" :value-key="item.code" :key="item.code" :label="item.name" :value="item.code"></el-option>
                      </el-select>
                    </el-form-item>
                </el-row>
                <el-row>
                  <el-form-item label="业务类型">
                    <el-select v-model="formInline.operation_code" clearable placeholder="请选择">
                      <el-option v-for="item in operationTypes" :value-key="item.code" :key="item.code" :label="item.name" :value="item.code"></el-option>
                    </el-select>
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
            <!-- 广告表格 -->
            <el-table
                    :data="tablePageData"
                    stripe
                    style="width: 100%">
                <el-table-column fixed prop="title" label="广告名称" minWidth="100"></el-table-column>
                <el-table-column prop="media_type" label="媒体类型"></el-table-column>
                <el-table-column prop="operation_type" label="业务类型" minWidth="107"></el-table-column>
                <el-table-column prop="start_at" label="展示开始日期" minWidth="140"></el-table-column>
                <el-table-column prop="end_at" label="展示结束日期" minWidth="140"></el-table-column>
                <el-table-column prop="location_type" label="广告类型" minWidth="112"></el-table-column>
                <el-table-column prop="disabled" label="是否禁用" :formatter="formatterDisabled"></el-table-column>
                <el-table-column prop="sort_num" label="排序"></el-table-column>
                <el-table-column prop="updated_user_name" label="最后修改人"></el-table-column>
                <el-table-column prop="updated_at" label="最后修改时间" minWidth="140"></el-table-column>
                <el-table-column fixed="right" label="操作" align="center" minWidth="140">
                    <template slot-scope="scope">
                        <el-button @click.native="showEditDialog(scope.row.id)" type="text" size="small">编辑</el-button>
                        <el-button @click.native="showDialog(scope.row.id)" type="text" size="small">查看</el-button>
                        <el-button @click.native="delAd(scope.row.id)" type="text" size="small">删除</el-button>
                    </template>
                </el-table-column>
            </el-table>

            <!-- 分页 -->
            <pagination v-if="pageRefresh" ref="pagination" :size="pageSize" :total="totalAll" :page="pageNo" @setPage="gotoPage"></pagination>
        </el-row>

        <!-- 添加广告 -->
        <el-dialog title="添加广告" :visible.sync="addVisible" :close-on-click-modal="false" center
                   :before-close="handleAddClose">
            <el-form :model="addForm" label-width="100px" :rules="addFormRules" ref="addForm">
                <el-row>
                    <el-form-item label="广告位类型" prop="location_code">
                        <el-select v-model="addForm.location_code" placeholder="请选择" @change="changeLocation">
                            <el-option v-for="item in adLocations" :key="item.code" :value-key="item.code"
                                       :label="item.name" :value="item.code"></el-option>
                        </el-select>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="展示终端" prop="terminal_codes">
                      <el-checkbox-group v-model="addForm.terminal_codes">
                        <el-checkbox v-for="item in selectTerminals" :label="item.code" :key="item.code" border size="mini">{{item.name}}</el-checkbox>
                      </el-checkbox-group>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="广告名称" prop="title" v-if="addForm.location_code !== FAST_ENTRANCE_LOCATION_CODE">
                        <el-input v-model="addForm.title" placeholder="请输入广告名称" :maxlength="255"></el-input>
                    </el-form-item>
                </el-row>
                <el-row>
                  <el-form-item label="广告名称" prop="title" v-if="addForm.location_code === FAST_ENTRANCE_LOCATION_CODE">
                    <el-input v-model="addForm.title" placeholder="请输入广告名称，不超过6个字" :maxlength="6"></el-input>
                  </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="媒体类型" prop="media_code">
                        <el-select v-model="addForm.media_code" placeholder="请选择">
                            <el-option v-for="item in mediaTypes" :key="item.code" :value-key="item.code"
                                       :label="item.name" :value="item.code"></el-option>
                        </el-select>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="图片" prop="img_src">
                        <el-upload :action="imgUrl" :file-list="addImgFile" list-type="picture"
                                   :on-success="uploadSuccess" :on-error="uploadError" :data="imgObj"
                                   :before-upload="uploadBefore" :limit="1" :on-remove="handleRemove"
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
                                v-model="addForm.need_popup">
                        </el-switch>
                    </el-form-item>
                </el-row>
                <el-row v-if="popupState && addForm.need_popup == 1 ">
                    <el-form-item label="弹出层海报" prop="popup_poster_url">
                        <el-upload :action="imgUrl" :file-list="addPopupFile" list-type="picture"
                                   :on-success="uploadPopupImgSuccess" :on-error="uploadError" :data="imgObj"
                                   :before-upload="uploadPopupBefore" :limit="1" :on-remove="handleRemovePopupImg"
                                   :on-exceed="handleExceed" :with-credentials="true">
                            <el-button size="small" type="primary">点击上传</el-button>
                            <span slot="tip" class="el-upload__tip" v-if="popupImgSize && popupImgFileSize">(要求：图片尺寸{{popupImgSize}}、大小不超过{{popupImgFileSize}}k)</span>
                        </el-upload>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="链接" prop="url_link" v-if="addForm.location_code !== FAST_ENTRANCE_LOCATION_CODE && addForm.location_code !== AD_SPLASH_SCREEN_LOCATIONC_CODE && hideParams === false">
                        <el-input v-model="addForm.url_link" placeholder="请输入链接" :maxlength="255"></el-input>
                    </el-form-item>
                </el-row>
                <el-row>
                  <el-form-item label="链接" v-if="addForm.location_code === FAST_ENTRANCE_LOCATION_CODE || addForm.location_code === AD_SPLASH_SCREEN_LOCATIONC_CODE">
                    <el-input v-model="addForm.url_link" placeholder="请输入链接" :maxlength="255"></el-input>
                  </el-form-item>
                </el-row>
                <el-row>
                  <el-form-item label="跳转类型" v-if="hideParams === false">
                    <el-input v-model="addForm.jump_type" placeholder="请输入跳转类型" :maxlength="255">
                    </el-input>
                  </el-form-item>
                </el-row>
                <el-row>
                  <el-form-item label="跳转参数" prop="jump_params" v-if="hideParams === false">
                    <el-input type="textarea" v-model="addForm.jump_params" placeholder="请输入跳转参数" :maxlength="500">
                    </el-input>
                  </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="展示时间" prop="show_time">
                        <el-date-picker
                                v-model="addForm.show_time"
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
                        <el-select v-model="addForm.operation_code" placeholder="请选择">
                            <el-option v-for="item in operationTypes" :key="item.code" :value-key="item.code"
                                       :label="item.name" :value="item.code"></el-option>
                        </el-select>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="可见人群" prop="permission_codes"  v-if="hideParams === false">
                        <div v-for="(item, i) in permission_array" :key="item.code">
                            <!-- <el-checkbox :indeterminate="isIndeterminates[i]" v-model="item.granted" @change="handleCheckAllChange(item.granted, i)">{{item.name}}</el-checkbox> -->
                            <el-checkbox :indeterminate="checkedPackageCodes[i] && checkedPackageCodes[i].length > 0 && checkedPackageCodes[i].length < countArrItemNum(packageCodes[i])" v-model="item.granted" @change="handleCheckAllChange(item.granted, i)">{{item.name}}</el-checkbox>
                            <div v-for="(arr, j) in packageCodes[i]" :key="arr[0].name">
                              <el-checkbox-group v-model="checkedPackageCodes[i]" @change="handleCheckedPackageCodesChangeOfAdd(i)" style="padding-left: 60px;">
                                <el-checkbox v-for="packageCode in packageCodes[i][j]" :label="packageCode.code" :key="packageCode.code" border size="mini">{{packageCode.name}}</el-checkbox>
                              </el-checkbox-group>
                            </div>
                        </div>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="排序序号" prop="sort_num">
                        <el-input v-model="addForm.sort_num" placeholder="不填默认为“0”，按记录展示开始排序" type="number"
                                  :min="0" class="short-input"></el-input>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="是否禁用">
                        <el-switch
                                :active-value="1"
                                :inactive-value="0"
                                active-color="#13ce66"
                                inactive-color="#999"
                                v-model="addForm.disabled">
                        </el-switch>
                    </el-form-item>
                </el-row>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click.native="addSubmit" :loading="addLoading">确认</el-button>
                <el-button @click.native="addCancel">取消</el-button>
            </div>
        </el-dialog>

        <!-- 编辑广告 -->
        <el-dialog title="编辑广告" :visible.sync="editVisible" :close-on-click-modal="false" center
                   :before-close="handleEditClose">
            <el-form :model="editForm" label-width="100px" :rules="editFormRules" ref="editForm">
                <el-row>
                    <el-form-item label="广告位类型" prop="location_code">
                        <el-select v-model="editForm.location_code" placeholder="请选择" @change="changeLocation">
                            <el-option v-for="item in adLocations" :key="item.code" :value-key="item.code"
                                       :label="item.name" :value="item.code"></el-option>
                        </el-select>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="展示终端" prop="terminal_codes">
                      <el-checkbox-group v-model="editForm.terminal_codes" >
                        <el-checkbox v-for="item in selectTerminals" :label="item.code" :key="item.code" border size="mini">{{item.name}}</el-checkbox>
                      </el-checkbox-group>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="广告名称" prop="title" v-if="editForm.location_code !== FAST_ENTRANCE_LOCATION_CODE">
                        <el-input v-model="editForm.title" placeholder="请输入广告名称" :maxlength="255"></el-input>
                    </el-form-item>
                </el-row>
                <el-row>
                  <el-form-item label="广告名称" prop="title" v-if="editForm.location_code === FAST_ENTRANCE_LOCATION_CODE">
                    <el-input v-model="editForm.title" placeholder="请输入广告名称，不超过6个字" :maxlength="6"></el-input>
                  </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="媒体类型" prop="media_code">
                        <el-select v-model="editForm.media_code" placeholder="请选择">
                            <el-option v-for="item in mediaTypes" :key="item.code" :value-key="item.code"
                                       :label="item.name" :value="item.code"></el-option>
                        </el-select>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="图片" prop="img_src">
                        <el-upload :action="imgUrl" :file-list="addImgFile" list-type="picture"
                                   :on-success="updateUploadSuccess" :on-error="uploadError" :data="imgObj"
                                   :before-upload="uploadBefore" :limit="1" :on-remove="handleRemove"
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
                                v-model="editForm.need_popup">
                        </el-switch>
                    </el-form-item>
                </el-row>
                <el-row v-if="popupState && editForm.need_popup == 1">
                    <el-form-item label="弹出层海报" prop="popup_poster_url">
                        <el-upload :action="imgUrl" :file-list="addPopupFile" list-type="picture"
                                   :on-success="updateUploadPopupImgSuccess" :on-error="uploadError" :data="imgObj"
                                   :before-upload="uploadPopupBefore" :limit="1" :on-remove="handleRemovePopupImg"
                                   :on-exceed="handleExceed" :with-credentials="true">
                            <el-button size="small" type="primary">点击上传</el-button>
                            <span slot="tip" class="el-upload__tip" v-if="popupImgSize && popupImgFileSize">(要求：图片尺寸{{popupImgSize}}、大小不超过{{popupImgFileSize}}k)</span>
                        </el-upload>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="链接" prop="url_link" v-if="editForm.location_code !== FAST_ENTRANCE_LOCATION_CODE && editForm.location_code !== AD_SPLASH_SCREEN_LOCATIONC_CODE && hideParams === false">
                        <el-input v-model="editForm.url_link" placeholder="请输入链接"
                                  :maxlength="255"></el-input>
                    </el-form-item>
                </el-row>
                <el-row>
                  <el-form-item label="链接" v-if="editForm.location_code === FAST_ENTRANCE_LOCATION_CODE || editForm.location_code === AD_SPLASH_SCREEN_LOCATIONC_CODE">
                    <el-input v-model="editForm.url_link" placeholder="请输入链接" :maxlength="255">
                    </el-input>
                  </el-form-item>
                </el-row>
                <el-row>
                  <el-form-item label="跳转类型" v-if="hideParams === false">
                    <el-input v-model="editForm.jump_type" placeholder="请输入跳转类型" :maxlength="255"></el-input>
                  </el-form-item>
                </el-row>
                <el-row>
                  <el-form-item label="跳转参数" prop="jump_params" v-if="hideParams === false">
                    <el-input type="textarea" v-model="editForm.jump_params" placeholder="请输入跳转参数" :maxlength="500"></el-input>
                  </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="展示时间" prop="show_time">
                        <el-date-picker
                                v-model="editForm.show_time"
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
                        <el-select :disabled="forbidden" v-model="editForm.operation_code" placeholder="请选择">
                            <el-option v-for="item in operationTypes" :key="item.code" :value-key="item.code"
                                       :label="item.name" :value="item.code"></el-option>
                        </el-select>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="可见人群" prop="permission_codes" v-if="hideParams === false">
                        <div v-for="(item,i) in permission_array" :key="item.code">
                            <!-- <el-checkbox :indeterminate="isIndeterminates[i]" v-model="item.granted" @change="handleCheckAllChange(item.granted, i)">{{item.name}}</el-checkbox> -->
                            <el-checkbox :indeterminate="checkedPackageCodes[i] && checkedPackageCodes[i].length > 0 && checkedPackageCodes[i].length < countArrItemNum(packageCodes[i])" v-model="item.granted" @change="handleCheckAllChange(item.granted, i)">{{item.name}}</el-checkbox>
                            <div v-for="(arr, j) in packageCodes[i]" :key="arr[0].name">
                              <el-checkbox-group v-model="checkedPackageCodes[i]" @change="handleCheckedPackageCodesChangeOfEdit(i)" style="padding-left: 60px;">
                                <el-checkbox v-for="packageCode in packageCodes[i][j]" :label="packageCode.code" :key="packageCode.code" border size="mini">{{packageCode.name}}</el-checkbox>
                              </el-checkbox-group>
                            </div>
                        </div>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="排序序号" prop="sort_num">
                        <el-input v-model="editForm.sort_num" placeholder="不填默认为“0”，按记录ID排序" type="number"
                                  :min="0" class="short-input"></el-input>
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="是否禁用">
                        <el-switch
                                :active-value="1"
                                :inactive-value="0"
                                active-color="#13ce66"
                                inactive-color="#999"
                                v-model="editForm.disabled">
                        </el-switch>
                    </el-form-item>
                </el-row>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click.native="editSubmit" :loading="editLoading">确认</el-button>
                <el-button @click.native="editCancel">取消</el-button>
            </div>
        </el-dialog>

        <!-- 查看广告 -->
        <el-dialog title="查看广告" :visible.sync="showVisible" :close-on-click-modal="false" center>
            <el-form :model="showForm" label-width="100px">
                <el-row>
                    <el-form-item label="展示终端">
                        {{showForm.terminal_codes}}
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="广告位类型">
                        {{showForm.location_type}}
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="广告名称">
                        {{showForm.title}}
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="图片">
                        <img :src="showForm.img_url" alt="努力生成中..." class="show_img">
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="是否自动弹出">
                        {{showForm.need_popup == 1 ? '是' : '否'}}
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="弹出层图片" v-if="showForm.need_popup == 1">
                        <img :src="showForm.popup_poster_link" alt="努力生成中..." class="show_img">
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="链接">
                        {{showForm.url_link}}
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
                        {{showForm.start_at}} —— {{showForm.end_at}}
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="媒体类型">
                        {{showForm.media_type}}
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="业务类型">
                        {{showForm.operation_type}}
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="可见人群">
                        {{showForm.permission_type}}
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="排序序号" prop="sort_num">
                        {{showForm.sort_num}}
                    </el-form-item>
                </el-row>
                <el-row>
                    <el-form-item label="是否禁用">
                        {{showForm.disabled}}
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
  import Env from '../../http/env'
  import HTTP from '../../http/api_propaganda'
  import Pagination from '@/components/Pagination'

  export default {
    name: 'Ad',
    data () {
      return {
        formInline: {
          start_time: '',
          end_time: '',
          location_code: '',
          terminal_codes: '',
          operation_code: ''
        },
        // 缓存搜索数据
        searchParams: {
          start_time: '',
          end_time: '',
          location_code: '',
          terminal_codes: '',
          operation_code: ''
        },

        // 分页初始化
        totalAll: 0,
        pageSize: 10,
        pageNo: 1,
        pageRefresh: true,

        tablePageData: [],
        adLocations: [],
        terminals: [],
        selectTerminals: [],
        mediaTypes: [],
        operationTypes: [],
        packages: [],
        forbidden: false, // 从其他模块发布的广告，其中一部分字段无法修改
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

        // 快速入口 广告位Code
        FAST_ENTRANCE_LOCATION_CODE: 'fast_entrance',
        // 广告闪屏 广告位Code
        AD_SPLASH_SCREEN_LOCATIONC_CODE: 'ad_splash_screen',

        // 默认套餐权限
        DEFAULT_PACKAGE_CODE: 'basic_free_service',

        // 隐藏 链接 跳转类型 以及 跳转参数 状态
        hideParams: true,

        // 新增广告
        addVisible: false, // 是否显示
        addLoading: false,
        addFormRules: {
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
            {type: 'array', required: true, message: '请选择显示终端', trigger: 'blur'}
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
        addForm: {
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
        },

        // 上传图片 上传图片预览在addImgFile数组里面[{name: '', url: ''}]
        addImgFile: [],
        imgUrl: `${Env.baseURL}/propaganda/img/upload`,
        imgObj: {'image': {}},

        addPopupFile: [],
        // 编辑广告
        editVisible: false, // 是否显示
        editLoading: false,
        editFormRules: {
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
            {type: 'array', required: true, message: '请选择显示终端', trigger: 'blur'}
          ],
          img_src: [
            {required: true, message: '请上传图片', trigger: 'blur'}
          ],
          popup_poster_url: [
            {required: true, validator: this.checkPopupImgOfEdit, trigger: 'blur'}
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
        editForm: {
          ad_id: '',
          media_code: '',
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
        },
        // 查看广告
        showVisible: false,
        showForm: {
          media_type: '',
          location_type: '',
          operation_type: '',
          title: '',
          img_src: '',
          url_link: '',
          jump_type: '',
          jump_params: '',
          start_at: '',
          end_at: '',
          terminal_codes: [],
          sort_num: 0,
          disabled: 0,
          need_popup: 0,
          popup_poster_url: '',
          permission_type: ''
        }
      }
    },
    components: {
      Pagination
    },
    created: function () {
    },
    mounted: function () {
      this.getAdLocations()
      this.getAdTerminals()
      this.getAdMediaTypes()
      this.getOperationTypes()
      this.getPackages()
      this.getList()
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

      // 获取表格
      getList () {
        var params = Object.assign({}, this.searchParams)
        params.page_no = this.pageNo
        params.page_size = this.pageSize
        HTTP.getAdList(params).then(res => {
          if (res.code === 0) {
            this.tablePageData = res.data.ad_list
            this.totalAll = res.data.ad_cnt
            this.initPagination()
          } else {
            this.$message.error({showClose: true, message: '获取广告列表失败：' + res.msg, duration: 2000})
          }
        }).catch(err => {
          console.error(err)
        })
      },

      updateList () {
        this.getList()
      },

      // 搜索
      onSearch () {
        this.pageNo = 1
        this.searchParams = this.formInline
        let searchParams = this.filterParams(this.searchParams)
        HTTP.searchAds(searchParams).then(res => {
          if (res.code === 0) {
            this.tablePageData = res.data.ad_list
            this.totalAll = res.data.ad_cnt
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

      // 获取广告位类型
      getAdLocations () {
        HTTP.getAdLocations().then(data => {
          if (data.code === 0) {
            this.adLocations = data.data
          } else {
            console.log(data.msg)
          }
        }).catch(err => {
          console.error(err)
        })
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
            _this.packages = data.data
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

      formatterDisabled (row, column, value) {
        return value === 0 ? '未禁用' : '已禁用'
      },

      changeLocation (value) {
        this.addForm.terminal_codes = []
        this.editForm.terminal_codes = []
        this.getAdTerminalsOfLocationCode(value)
        this.setImgUrl(value)
        this.setParamsStatus(value)
      },

      setImgUrl (value) {
        this.addImgFile = []
        this.addPopupFile = []
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

      setParamsStatus (value) {
        this.hideParams && this.clearPackageParams()
        this.hideParams = false
        this.addForm.permission_codes = []
        this.editForm.permission_codes = []
      },

      clearPackageParams () {
        this.checkedPackageCodes = []
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
      },
      // 权限checkbox选择状态变化
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

      checkPopupImgOfEdit (rule, value, callback) {
        if (this.editForm.need_popup === 0) {
          callback()
        }
        if (this.editForm.need_popup === 1 && this.editForm.popup_poster_url && this.editForm.popup_poster_url.length > 0) {
          callback()
        } else {
          callback(new Error('请上传图片'))
        }
      },

      checkPopupImgOfAdd (rule, value, callback) {
        if (this.addForm.need_popup === 0) {
          callback()
        }
        if (this.addForm.need_popup === 1 && this.addForm.popup_poster_url && this.addForm.popup_poster_url.length > 0) {
          callback()
        } else {
          callback(new Error('请上传图片'))
        }
      },

      // 添加广告
      showAddDialog () {
        let _this = this
        this.packageCodes = []
        this.checkedPackageCodes = []
        this.addVisible = true
        this.addImgFile = []
        this.addPopupFile = []
        this.imgSize = ''
        this.imgFileSize = ''
        this.popupImgSize = ''
        this.popupImgFileSize = ''
        this.popupState = false
        this.addForm = {
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
          disabled: 0,
          permission_codes: [],
          need_popup: 0,
          popup_poster_url: ''
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
        this.selectTerminals = []
        setTimeout(() => {
          _this.$refs.addForm.clearValidate()
        }, 100)
      },
      addSubmit () {
        if (this.addForm.location_code === this.FAST_ENTRANCE_LOCATION_CODE) {
          if (!this.addForm.url_link && !(this.addForm.jump_type && this.addForm.jump_params)) {
            this.$message({showClose: true, message: '【链接】与【跳转类型及参数】至少填写一类', type: 'warning', duration: 5000})
            return false
          }
        }

        if (this.clickFlag) {
          this.$refs.addForm.validate((valid) => {
            if (valid) {
              this.clickFlag = false
              let codeList = []
              this.checkedPackageCodes.forEach(d => {
                codeList = codeList.concat(d)
              })
              this.addForm.permission_codes = codeList
              this.addAdForm()
            }
          })
        }
      },
      addAdForm () {
        let _this = this
        this.$refs.addForm.validate((valid) => {
          if (valid) {
            if (!_this.popupState || parseInt(_this.addForm.need_popup) === 0) {
              _this.addForm.need_popup = 0
              _this.addForm.popup_poster_url = ''
            }

            HTTP.createAd(_this.addForm).then(data => {
              if (data.code === 0) {
                _this.$message.success({showClose: true, message: data.msg, duration: 2000})
                _this.addVisible = false
                _this.updateList()
                setTimeout(function () {
                  _this.clickFlag = true
                }, 500)
              } else {
                _this.$message.error(data.msg)
                _this.clickFlag = true
              }
            }).catch(err => {
              console.error(err)
              _this.clickFlag = true
            })
          }
        })
      },
      addCancel () {
        this.addVisible = false
      },
      handleAddClose () {
        this.addVisible = false
      },

      // 编辑广告
      showEditDialog (id) {
        let _this = this
        this.checkedPackageCodes = []
        this.packageCodes = []
        this.editVisible = true
        this.forbidden = false
        this.addImgFile = []
        this.addPopupFile = []
        this.imgSize = ''
        this.imgFileSize = ''
        this.popupImgSize = ''
        this.popupImgFileSize = ''
        this.popupState = false
        HTTP.findAdById(id).then(data => {
          if (data.code === 0) {
            this.changeLocation(data.data.location_code)
            this.editForm = {
              ad_id: id,
              media_code: data.data.media_code,
              location_code: data.data.location_code,
              operation_code: data.data.operation_code,
              title: data.data.title,
              img_src: data.data.img_src,
              url_link: data.data.url_link,
              jump_type: data.data.jump_type,
              jump_params: data.data.jump_params,
              show_time: [data.data.start_at, data.data.end_at],
              terminal_codes: data.data.terminal_codes,
              sort_num: data.data.sort_num,
              disabled: data.data.disabled,
              permission_codes: '',
              need_popup: data.data.need_popup,
              popup_poster_url: data.data.popup_poster_url
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
            if (data.data.need_popup === 1) {
              let popupName = data.data.popup_poster_link.substr(data.data.popup_poster_link.lastIndexOf('/') + 1)
              this.addPopupFile = [{
                name: popupName,
                url: data.data.popup_poster_link
              }]
            }
            if (data.data.operation_id > 0) {
              this.forbidden = true
            }
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
        if (this.editForm.location_code === this.FAST_ENTRANCE_LOCATION_CODE) {
          if (!this.editForm.url_link && !(this.editForm.jump_type && this.editForm.jump_params)) {
            this.$message({showClose: true, message: '【链接】与【跳转类型及参数】至少填写一类', type: 'warning', duration: 5000})
            return false
          }
        }

        let _this = this
        this.$refs.editForm.validate((valid) => {
          if (valid) {
            let codeList = []
            this.checkedPackageCodes.forEach(d => {
              codeList = codeList.concat(d)
            })
            _this.editForm.permission_codes = codeList
            _this.editAdForm()
          }
        })
      },
      editAdForm () {
        let _this = this
        this.$refs.editForm.validate((valid) => {
          if (valid) {
            if (!_this.popupState || parseInt(_this.editForm.need_popup) === 0) {
              _this.editForm.need_popup = 0
              _this.editForm.popup_poster_url = ''
            }
            HTTP.updateAd(_this.editForm).then(data => {
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

      // 查看广告
      showDialog (id) {
        HTTP.findAdById(id).then(data => {
          if (data.code === 0) {
            this.showForm = {
              media_type: this.getTypeByCode(this.mediaTypes, data.data.media_code),
              location_type: this.getTypeByCode(this.adLocations, data.data.location_code),
              operation_type: this.getTypeByCode(this.operationTypes, data.data.operation_code),
              title: data.data.title,
              img_url: data.data.img_url,
              url_link: data.data.url_link,
              jump_type: data.data.jump_type,
              jump_params: data.data.jump_params,
              start_at: data.data.start_at,
              end_at: data.data.end_at,
              terminal_codes: this.getTypesByCode(this.terminals, data.data.terminal_codes),
              sort_num: data.data.sort_num,
              disabled: data.data.disabled === 1 ? '禁用' : '未禁用',
              permission_type: this.getTypesByCode(this.codeType, data.data.permission_codes),
              need_popup: data.data.need_popup,
              popup_poster_link: data.data.popup_poster_link
            }
          } else {
            this.$message.error(data.msg)
          }
        }).catch(err => {
          console.error(err)
        })

        this.showVisible = true
      },

      // 删除广告
      delAd (id) {
        this.$confirm('是否确认删除该广告？', '提示', {
          confirmButtonText: '确认',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          HTTP.deleteAd(id).then(data => {
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

      // 上传图片模块
      uploadBefore (file) {
        if (!this.imgFileSize) {
          this.$message.error('请选择广告位类型')
          return false
        }

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

        const isLtImgFileSize = file.size / 1024 <= this.imgFileSize

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

      // 上传弹出层图片模块
      uploadPopupBefore (file) {
        if (!this.popupImgFileSize) {
          this.$message.error('请选择广告位类型')
          return false
        }

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

        const isLtPopupImgFileSize = file.size / 1024 <= this.popupImgFileSize

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
      uploadPopupImgSuccess (response, file, addImgFile) {
        if (response.code === 0) {
          this.addForm.popup_poster_url = response.data.cnd_relatively_file_path
          this.addPopupFile = [{
            name: response.data.file_path.substr(response.data.file_path.lastIndexOf('/') + 1),
            url: response.data.file_path
          }]
        } else {
          console.error(response.msg)
        }
      },

      // 上传图片失败
      uploadError (response, file, addImgFile) {
        console.error('上传失败，请重试！')
      },

      handleRemove (file, fileList) {
        this.addForm.img_src = ''
        this.editForm.img_src = ''
      },

      handleRemovePopupImg (file, fileList) {
        this.addForm.popup_poster_url = ''
        this.editForm.popup_poster_url = ''
      },

      handleExceed (file, addImgFile) {
        this.$alert('只能上传一张图片')
      },

      // 更新上传图片
      updateUploadSuccess (response, file, addImgFile) {
        if (response.code === 0) {
          this.editForm.img_src = response.data.cnd_relatively_file_path
          this.addImgFile = [{
            name: response.data.file_path.substr(response.data.file_path.lastIndexOf('/') + 1),
            url: response.data.file_path
          }]
        } else {
          console.error(response.msg)
        }
      },

      // 更新popup图片
      updateUploadPopupImgSuccess (response, file, addImgFile) {
        if (response.code === 0) {
          this.editForm.popup_poster_url = response.data.cnd_relatively_file_path
          this.addPopupFile = [{
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
        width: 275px;
    }
</style>