import * as HTTP from './'

export default {
  // 获取广告列表
  getAdList: params => {
    return HTTP.GET('/propaganda/ad', params)
  },

  // 获取广告位类型
  getAdLocations: params => {
    return HTTP.GET('/propaganda/locations', params)
  },

  // 获取广告展示终端类型
  getAdTerminals: params => {
    return HTTP.GET('/propaganda/terminal/list', params)
  },

  getAdTerminalsOfLocationCode: locationCode => {
    return HTTP.GET(`/propaganda/${locationCode}/terminal/list`)
  },

  // 获取媒体类型
  getAdMediaTypes: params => {
    return HTTP.GET('/propaganda/media/list', params)
  },

  // 获取业务类型
  getOperationTypes: params => {
    return HTTP.GET('/propaganda/operation/list', params)
  },

  // 获取权限列表
  getPackages: params => {
    return HTTP.GET('/propaganda/packages', params)
  },

  // 搜索广告
  searchAds: params => {
    return HTTP.PATCH('/propaganda/ad', params)
  },

  // 添加广告
  createAd: params => {
    return HTTP.POST('/propaganda/ad/create', params)
  },

  // 更新广告
  updateAd: params => {
    return HTTP.PUT('/propaganda/ad', params)
  },

  // 删除广告
  deleteAd: adId => {
    return HTTP.DELETE(`/propaganda/ad/${adId}`)
  },

  // 获取广告详情
  findAdById: adId => {
    return HTTP.GET(`/propaganda/ad/${adId}`)
  },

  // 获取论坛列表
  getForumList: params => {
    return HTTP.GET('/propaganda/forum', params)
  },

  // 添加论坛
  createForum: params => {
    return HTTP.POST('/propaganda/forum', params)
  },

  getTeachers: params => {
    return HTTP.GET('/propaganda/teacher/list')
  },

  // 搜索论坛
  searchForums: params => {
    return HTTP.PATCH('/propaganda/forum', params)
  },

  // 更新论坛
  updateForum: params => {
    return HTTP.PUT('/propaganda/forum', params)
  },

  // 删除论坛
  deleteForum: forumId => {
    return HTTP.DELETE(`/propaganda/forum/${forumId}`)
  },
  // 获取 forumId 论坛发布的正在展示的数据
  getAdListDataOfForumId: forumId => {
    return HTTP.GET(`/propaganda/forum/ads/${forumId}`)
  },

  // 获取论坛详情
  findForumById: forumId => {
    return HTTP.GET(`/propaganda/forum/${forumId}`)
  },
  // --------------------------------------- 跑马灯管理 ----------------------------------------------
  // 获取跑马灯记录来源类型
  getSourceTypes: () => {
    return HTTP.GET(`/propaganda/dynamic/ad/source-type/list`)
  },

  // 获取跑马灯 展示终端列表
  getDynamicAdTerminals: () => {
    return HTTP.GET(`/propaganda/dynamic/ad/terminal/list`)
  },

  // 添加 跑马灯
  addDynamicAd: params => {
    return HTTP.POST(`/propaganda/dynamic/ad/create`, params)
  },

  // 获取 跑马灯列表
  getDynamicAdList: params => {
    return HTTP.GET(`/propaganda/dynamic/ad/list`, params)
  },

  searchDynamicAdList: params => {
    return HTTP.PATCH(`/propaganda/dynamic/ad`, params)
  },

  changeActive: (dynamicAdId, active) => {
    return HTTP.PUT(`/propaganda/dynamic/ad/${dynamicAdId}/active/${active}`)
  },

  changeSign: (dynamicAdId, sign) => {
    return HTTP.PUT(`/propaganda/dynamic/ad/${dynamicAdId}/sign/${sign}`)
  },

  getDynamicAd: (dynamicAdId) => {
    return HTTP.GET(`/propaganda/dynamic/ad/${dynamicAdId}`)
  },

  editDynamicAd: (dynamicAdId, params) => {
    return HTTP.PUT(`/propaganda/dynamic/ad/${dynamicAdId}`, params)
  },

  delDynamicAd: (dynamicAdId) => {
    return HTTP.DELETE(`/propaganda/dynamic/ad/${dynamicAdId}`)
  }
}
