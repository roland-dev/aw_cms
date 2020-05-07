import * as HTTP from '.'

export default {
  // ------------------------------- 文章管理 ---------------------------------------------
  // 获取内容精选列表
  getFeedList: params => {
    return HTTP.GET('/feed/list', params)
  },

  // 标记精选feed
  putFeed: (feedId, params) => {
    return HTTP.PUT(`/feed/elite/${feedId}`, params)
  },

  // 删除feed
  delFeed: (feedId, isContact) => {
    return HTTP.DELETE(`/feed/${feedId}/${isContact}`)
  },

  // 可免费查看状态改变（解盘）
  putByPass: (feedId, params) => {
    return HTTP.PUT(`/feed/bypass/${feedId}`, params)
  },

  // ---------------- 活码管理 ------------------
  // 获取活码分组列表
  getMoveqrGroupList: () => {
    return HTTP.GET(`/operate/moveqrgroup`)
  },

  // 增加活码分组
  addMoveqrGroup: (params) => {
    return HTTP.POST(`/operate/moveqrgroup`, params)
  },

  // 修改活码分组
  editMoveqrGroup: (groupId, params) => {
    return HTTP.PUT(`/operate/moveqrgroup/${groupId}`, params)
  },

  // 删除活码分组
  delMoveqrGroup: (groupId) => {
    return HTTP.DELETE(`/operate/moveqrgroup/${groupId}`)
  },

  // 清除活码计数缓存
  clearMoveqrTime: (groupId) => {
    return HTTP.DELETE(`/operate/moveqrgroup/cache/${groupId}`)
  },

  // 活码上传图片
  addMoveqrImg: (params) => {
    return HTTP.POST(`/operate/moveqr/image`, params)
  },

  // 增加活码
  addMoveqr: (params) => {
    return HTTP.POST(`/operate/moveqr`, params)
  },

  // 修改活码
  editMoveqr: (groupId, params) => {
    return HTTP.PUT(`/operate/moveqr/${groupId}`, params)
  },

  // 删除活码
  delMoveqr: (moveqrId) => {
    return HTTP.DELETE(`/operate/moveqr/${moveqrId}`)
  }
}
