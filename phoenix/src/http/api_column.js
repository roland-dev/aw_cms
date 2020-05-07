import * as HTTP from './'

export default {
  // 获取栏目列表
  getCategoryList: params => {
    return HTTP.GET('/column/category/list', params)
  },

  getCategoryListOfPaging: params => {
    return HTTP.GET('/column/category/paging-list', params)
  },

  // 获取 service_list
  getServiceList: params => {
    return HTTP.GET('/column/category/service/list', params)
  },

  // 搜索 栏目分类 列表
  searchCategory: params => {
    return HTTP.PATCH(`/column/category`, params)
  },

  // 检查 code 唯一性
  checkCategoryCodeUnique: (categoryCode) => {
    return HTTP.GET(`/column/category/checkcode/${categoryCode}`)
  },

  // 添加 栏目
  addCategory: params => {
    return HTTP.POST('/column/category/create', params)
  },

  // 获取 栏目分类信息
  getCategoryInfo: (categoryId) => {
    return HTTP.GET(`/column/category/${categoryId}`)
  },

  // 编辑 栏目分类
  updateCategory: (categoryId, params) => {
    return HTTP.PUT(`/column/category/update/${categoryId}`, params)
  },

  // 获取 老师列表
  getTeacherListOfCategoryCode: (categoryCode) => {
    return HTTP.GET(`/column/category/teacher/list/${categoryCode}`)
  },

  // 获取 子栏目分类列表
  getSubCategoryList: (categoryCode, params) => {
    return HTTP.GET(`/column/subcategory/list/${categoryCode}`, params)
  },

  // 检查 subCategoryCode 唯一性
  checkSubCategoryCodeUnique: (categoryCode, subCategoryCode) => {
    return HTTP.GET(`/column/subcategory/checkcode/${categoryCode}/${subCategoryCode}`)
  },

  // 更新subCategory的active状态
  changeActiveOfSubCategory: (subCategoryId, active) => {
    return HTTP.PUT(`/column/subcategory/${subCategoryId}/active/${active}`)
  },

  // 添加 子栏目
  addSubCategory: params => {
    return HTTP.POST('/column/subcategory/create', params)
  },

  // 获取 子栏目分类
  getSubCategoryInfo: (subCategoryId) => {
    return HTTP.GET(`/column/subcategory/${subCategoryId}`)
  },

  // 编辑 子栏目分类
  updateSubCategory: (subCategoryId, params) => {
    return HTTP.PUT(`/column/subcategory/update/${subCategoryId}`, params)
  },

  // 删除 子栏目分类
  deleteSubCategory: (subCategoryId) => {
    return HTTP.DELETE(`/column/subcategory/delete/${subCategoryId}`)
  },

  // 获取 栏目分类 （栏目分组）
  getCategoryListByGroup: params => {
    return HTTP.GET('/column/category-group/category/list', params)
  },

  // 获取栏目分组列表
  getCategoryGroupList: params => {
    return HTTP.GET('/column/category-group/list', params)
  },

  // 检查 category_group_code 唯一性
  checkCategoryGroupCodeUnique: (categoryGroupCode) => {
    return HTTP.GET(`/column/category-group/checkcode/${categoryGroupCode}`)
  },

  // 添加 栏目分组
  createCategoryGroup: params => {
    return HTTP.POST('/column/category-group/create', params)
  },

  // 获取 栏目分组详情
  getCategoryGroupDetail: (categoryGroupCode) => {
    return HTTP.GET(`/column/category-group/${categoryGroupCode}`)
  },

  // 编辑 栏目分组
  updateCategoryGroup: params => {
    return HTTP.PUT('/column/category-group/update', params)
  },

  // 删除 栏目分组
  deleteCategoryGroup: categoryGroupCode => {
    return HTTP.DELETE(`/column/category-group/delete/${categoryGroupCode}`)
  },

  // 获取 栏目组成员
  getCategoryGroupMemberList: params => {
    return HTTP.GET(`/column/category-group/member/list`, params)
  },

  // 创建 栏目组成员
  createCategoryGroupMember: params => {
    return HTTP.POST(`/column/category-group/member`, params)
  },

  getCategoryGroupMember: id => {
    return HTTP.GET(`/column/category-group/member/${id}`)
  },

  updateCategoryGroupMember: (id, params) => {
    return HTTP.PUT(`/column/category-group/member/${id}`, params)
  },

  deleteCategoryGroupMember: id => {
    return HTTP.DELETE(`/column/category-group/member/${id}`)
  },

  // 获取 栏目老师列表
  getTeacherList: params => {
    return HTTP.GET(`/column/teacher/list`, params)
  },

  // 获取 栏目老师列表 分页
  getTeacherListOfPaging: params => {
    return HTTP.GET(`/column/teacher/paging-list`, params)
  },

  // 搜索 栏目老师列表
  searchTeacher: params => {
    return HTTP.PATCH('/column/teacher', params)
  },

  // 更新teacher的active状态
  changeActive: (teacherId, active) => {
    return HTTP.PUT(`/column/teacher/${teacherId}/active/${active}`)
  },

  // 添加 栏目老师
  addTeacher: params => {
    return HTTP.POST('/column/teacher/create', params)
  },

  updateTeacher: (teacherId, params) => {
    return HTTP.PUT(`/column/teacher/update/${teacherId}`, params)
  },

  getUserList: params => {
    return HTTP.PATCH('/column/teacher/user/list', params)
  },

  getTeacherInfo: (teacherId) => {
    return HTTP.GET(`/column/teacher/${teacherId}`)
  }
}
