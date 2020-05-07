import * as HTTP from './'

export default {
  // ------------------------------- 文章管理 ---------------------------------------------
  // 获取文章列表
  getArticleList: params => {
    return HTTP.GET('/article/list', params)
  },

  // 搜索文章列表
  searchArticleList: params => {
    return HTTP.GET('/article/list', params)
  },

  // 获取归属列表
  getCategoryList: () => {
    return HTTP.GET('/category/mylist')
  },

  // 获取所有归属列表
  getAllCategoryList: () => {
    return HTTP.GET('/category/list')
  },

  // 获取香江论剑列表
  getCategoryMyList: params => {
    return HTTP.GET('/category-group/xiangjianglunjian', params)
  },

  // 获取文章分类列表
  getSubcategoryList: (categoryCode) => {
    return HTTP.GET(`/category/${categoryCode}/subcategory/list`)
  },

  // 获取栏目老师列表
  getTeacherList: (categoryCode) => {
    return HTTP.GET(`/category/${categoryCode}/teacherlist`)
  },

  // 新增文章
  addArticle: params => {
    return HTTP.POST(`/article`, params)
  },

  // 新增文章
  articlePreview: params => {
    return HTTP.POST(`/article/preview`, params)
  },

  // 删除文章
  delArticle: (id) => {
    return HTTP.DELETE(`/article/${id}`)
  },

  // 查看文章
  getArticle: (id) => {
    return HTTP.GET(`/article/${id}`)
  },

  // 编辑文章
  editArticle: (id, params) => {
    return HTTP.PUT(`/article/${id}`, params)
  },

  // 修改文章是否可见状态
  changeShow: (id) => {
    return HTTP.PUT(`/article/${id}/show`)
  },

  // 修改文章是否可推送状态
  changePushQywx: (id) => {
    return HTTP.PUT(`/article/${id}/push`)
  },

  // 上传图片
  updateImg: (params) => {
    return HTTP.POST(`/resource/image`, params)
  },

  // 获取推送企业微信栏目列表
  getPushQywxCategoryList: () => {
    return HTTP.GET(`/category/qywxlist`)
  },

  // ------------------------------- 动态管理 ---------------------------------------------
  // 获取动态列表
  getTwitterList: params => {
    return HTTP.GET(`/twitter/list`, params)
  },

  // 发送动态
  addTwitter: params => {
    return HTTP.POST('/twitter', params)
  },

  // 动态审批
  putTwitterRequest: (twitterGuardId, params) => {
    return HTTP.PUT(`/twitter/request/${twitterGuardId}`, params)
  },

  // 动态审批列表
  getTwitterRequest: params => {
    return HTTP.GET('/twitter/request/list', params)
  },

  // 动态审批列表
  getTwitterRequestOfPaging: params => {
    return HTTP.GET('/twitter/request/paging-list', params)
  },

  getCustomerInfo: openId => {
    return HTTP.GET(`/customer/${openId}`)
  },

  getCustomerCardInfo: openId => {
    return HTTP.GET(`/customer/card/${openId}`)
  },

  getCustomerInfoByMobile: mobile => {
    return HTTP.GET(`/customer/mobile/${mobile}`)
  },

  addTwitterApproval: params => {
    return HTTP.POST('/twitter/request/add', params)
  },

  // ------------------------------- 私信管理 ---------------------------------------------
  // 获取私信列表
  getSessionMessageList: params => {
    return HTTP.GET(`/private-message/session/list`, params)
  },

  // 获取私信对话列表
  getMessage: params => {
    return HTTP.GET(`/private-message/list`, params)
  },

  // 搜索私信列表
  searchMessageList: params => {
    return HTTP.GET(`/private-message/session/list`, params)
  },

  // 发送私信
  addMessage: (params) => {
    return HTTP.POST('/private-message', params)
  },

  // 私信阅读记录
  readMessage: id => {
    return HTTP.PUT(`/private-message/${id}/read`)
  },

  // 私信审批
  putMessageRequest: (twitterGuardId, params) => {
    return HTTP.PUT(`/private-message/request/${twitterGuardId}`, params)
  },

  // 动态审批列表
  getMessageRequest: params => {
    return HTTP.GET('/private-message/request/list', params)
  },

  // 私信审批列表（分页）
  getMessageRequestOfPaging: params => {
    return HTTP.GET('/private-message/request/paging-list', params)
  },

  // 获取私信老师
  getTeacherId: code => {
    return HTTP.GET(`/category/${code}/teacherlist`)
  },

  // ------------------------------- 评论管理 ---------------------------------------------
  // 获取牛人列表
  getReplyTeacherList: () => {
    return HTTP.GET('/interaction/teacher/list')
  },

  // 获取评论所属类型列表
  getContentTypeList: () => {
    return HTTP.GET('/interaction/content-type/list')
  },

  // 获取评论列表
  getReplyList: params => {
    return HTTP.GET('/interaction/reply/list', params)
  },

  // 审批评论
  // reply_id=8 // 评论id
  // operate=20 // 审批意见，10 = new, 20 = approve, 30 = deny
  putReplyRequest: params => {
    return HTTP.PUT(`/interaction/reply/examine`, params)
  },

  // ------------------------------- 个股报告管理 ---------------------------------------------
  // 获取个股参数
  getStock: code => {
    return HTTP.stockGET(`/stocks/search?keyword=${code}`)
  },

  getCalendars: () => {
    return HTTP.stockGET(`/market/calendars`)
  },

  // 获取报告分类类型
  getReportCategoryList: () => {
    return HTTP.GET(`/stock/report/category/list`)
  },

  // 获取报告推送状态类型
  getReportPublishStatueList: params => {
    return HTTP.GET(`/stock/report/publish-status/list`, params)
  },

  // 新增个股报告
  addStockReport: params => {
    return HTTP.POST(`/stock/report/create`, params)
  },

  // 获取个股报告列表
  getStockReportList: params => {
    return HTTP.GET(`/stock/report/list`, params)
  },

  // 搜索个股报告列表
  searchStockReportList: params => {
    return HTTP.PATCH(`/stock/report/list`, params)
  },

  // 获取个股报告
  getStockReport: id => {
    return HTTP.GET(`/stock/report/${id}`)
  },

  // 编辑个股报告
  editStockReport: (id, params) => {
    return HTTP.PUT(`/stock/report/${id}`, params)
  },

  // 删除个股报告
  delStockReport: id => {
    return HTTP.DELETE(`/stock/report/${id}`)
  },

  // 发布个股报告
  pushStockReport: id => {
    return HTTP.GET(`/stock/report/push/${id}`)
  },

  // 批量审批评论
  putReplyRequests: params => {
    return HTTP.PUT(`/interaction/reply/batch-examine`, params)
  },

  // 评论回复
  reply: params => {
    return HTTP.POST(`/interaction/reply`, params)
  },
  // ----------------------------------------- 锦囊管理 -----------------------------------------------
  getTeacherListOfKit: () => {
    return HTTP.GET(`/kit/teacher/list`)
  },

  getBuyTypes: () => {
    return HTTP.GET(`/kit/buy-type/list`)
  },

  getBuyStates: () => {
    return HTTP.GET(`/kit/buy-states/list`)
  },

  getKitList: params => {
    return HTTP.GET(`/kit/list`, params)
  },

  searchKitList: params => {
    return HTTP.PATCH(`/kit`, params)
  },

  addKit: params => {
    return HTTP.POST(`/kit/create`, params)
  },

  getKit: id => {
    return HTTP.GET(`/kit/${id}`)
  },

  editKit: (id, params) => {
    return HTTP.PUT(`/kit/${id}`, params)
  },

  delKit: id => {
    return HTTP.DELETE(`/kit/${id}`)
  },
  // ----------------------------------------- 锦囊报告管理 ------------------------------------------------
  getKits: () => {
    return HTTP.GET(`/kit/report/kit/list`)
  },

  getValidStatus: () => {
    return HTTP.GET(`/kit/report/valid-status/list`)
  },

  getPublishStatus: () => {
    return HTTP.GET(`/kit/report/publish-status/list`)
  },

  getKitReportList: params => {
    return HTTP.GET(`/kit/report/list`, params)
  },

  searchKitReportList: params => {
    return HTTP.PATCH(`/kit/report`, params)
  },

  addKitReport: params => {
    return HTTP.POST(`/kit/report/create`, params)
  },

  getKitReport: id => {
    return HTTP.GET(`/kit/report/${id}`)
  },

  editKitReport: (id, params) => {
    return HTTP.PUT(`/kit/report/${id}`, params)
  },

  delKitReport: id => {
    return HTTP.DELETE(`/kit/report/${id}`)
  },

  pushKitReport: id => {
    return HTTP.GET(`/kit/report/push/${id}`)
  },
  // ----------------------------------------- 推送记录管理 -------------------------------------------------
  searchFeedList: params => {
    return HTTP.PATCH(`/content/feed`, params)
  },

  // 获取推送内容类型列表
  getFeedTypeList: () => {
    return HTTP.GET(`/content/feed/type/list`)
  }
}
