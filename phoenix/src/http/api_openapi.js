import * as HTTP from './'

export default {
  // 创建密钥
  createCode: params => {
    return HTTP.POST(`/openapi/customapp`, params)
  },

  // 更新基本信息
  updateBasicInfo: params => {
    return HTTP.PUT(`/openapi/customapp/basic`, params)
  },

  // 更新密钥
  updateSecret: params => {
    return HTTP.PUT(`/openapi/customapp/secret`, params)
  },

  // code加锁
  codeLock: params => {
    return HTTP.PUT(`/openapi/customapp/lock`, params)
  },

  // code解锁
  codeUnlock: params => {
    return HTTP.PUT(`/openapi/customapp/unlock`, params)
  },

  // 获取code列表
  getCodeList: params => {
    return HTTP.GET(`/openapi/customapp/paging-list`, params)
  },

  // 查询code列表
  searchCodeList: params => {
    return HTTP.GET(`/openapi/customapp/paging-list`, params)
  },

  // 获取code详情
  getDetail: code => {
    return HTTP.GET(`/openapi/customapp/detail/${code}`)
  },

  // 获取权限列表
  getPermissionList: params => {
    return HTTP.GET(`/openapi/permission`, params)
  },

  // 获取权限
  getPermission: code => {
    return HTTP.GET(`/openapi/permission/${code}`)
  },

  // 授予权限
  guardPermission: params => {
    return HTTP.POST(`/openapi/permission`, params)
  }
}
