import * as HTTP from './'

export default {
  // 获取直播室列表
  getLiveRoomList: params => {
    return HTTP.GET('/live/room/list', params)
  },

  // 添加直播室
  addLiveRoom: params => {
    return HTTP.POST('/live/room', params)
  },

  getLiveRoomInfo: liveRoomCode => {
    return HTTP.GET(`/live/room/${liveRoomCode}`)
  },

  updateLiveRoom: (liveRoomCode, params) => {
    return HTTP.PUT(`/live/room/${liveRoomCode}`, params)
  },

  deleteLiveRoom: liveRoomCode => {
    return HTTP.DELETE(`/live/room/${liveRoomCode}`)
  },

  // 获取视频供应商列表
  getVideoVendorList: params => {
    return HTTP.GET('/video/vendor/list', params)
  },

  // 固定节目管理
  getLiveStaticTalkshowList: params => {
    return HTTP.GET('/live/static-talkshow/list', params)
  },

  addLiveStaticTalkshow: params => {
    return HTTP.POST('/live/static-talkshow', params)
  },

  getLiveStaticTalkshowInfo: id => {
    return HTTP.GET(`/live/static-talkshow/${id}`)
  },

  updateLiveStaticTalkshow: (id, params) => {
    return HTTP.PUT(`/live/static-talkshow/${id}`, params)
  },

  deleteLiveStaticTalkshow: id => {
    return HTTP.DELETE(`/live/static-talkshow/${id}`)
  },

  // 每日节目管理
  getLiveTalkshowList: params => {
    return HTTP.GET('/live/talkshow/list', params)
  },

  addLiveTalkshow: params => {
    return HTTP.POST('/live/talkshow', params)
  },

  getLiveTalkshowInfo: code => {
    return HTTP.GET(`/live/talkshow/${code}`)
  },

  updateLiveTalkshow: (code, params) => {
    return HTTP.PUT(`/live/talkshow/${code}`, params)
  },

  deleteLiveTalkshow: code => {
    return HTTP.DELETE(`/live/talkshow/${code}`)
  },

  pullLiveTalkshow: params => {
    return HTTP.POST('/live/talkshow/list', params)
  },

  // 直播开始与停止
  changeTalkshowStatus: (talkshowCode, params) => {
    return HTTP.PATCH(`/live/talkshow/${talkshowCode}`, params)
  },

  // 直播互动管理
  getLiveDiscussList: params => {
    return HTTP.GET('/live/discuss/list', params)
  },

  // 直播互动 审核
  putLiveDiscussRequest: (id, params) => {
    return HTTP.PUT(`/live/discuss/${id}/examine`, params)
  },

  // 直播互动 审核(批量)
  putLiveDiscussRequests: (params) => {
    return HTTP.PUT(`/live/discuss/batch-examine`, params)
  },

  replyLiveDiscuss: params => {
    return HTTP.POST(`/live/discuss`, params)
  }
}
