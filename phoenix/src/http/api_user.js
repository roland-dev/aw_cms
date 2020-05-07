import * as HTTP from './'

export default {
  // 登录
  login: params => {
    return HTTP.GET('/user/auth/uc/enterprise', params)
  },

  // 登录状态
  user: params => {
    return HTTP.GET('/user', params)
  },

  // 获取用户权限列表
  getPermisson: id => {
    return HTTP.GET(`/user/permission`)
  },

  // 获取uc登录token
  getToken: params => {
    return HTTP.GET('/user/auth/uc', params)
  },

  // 退出
  logout: params => {
    return HTTP.GET('/user/logout', params)
  },
  // 用户中心退出（uc退出和cms退出同时，才可以保证完全退出）
  uclogout: params => {
    return HTTP.ucPOST('/session/out', params)
  }
}
