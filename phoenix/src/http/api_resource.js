import * as HTTP from './'

export default {
  // 获取老师分类
  getCategory: params => {
    return HTTP.GET('/resource/video/category', params)
  },

  // 获取对应老师
  postAuthor: params => {
    return HTTP.POST('/resource/video/category', params)
  },

  // 获取搜索区域对应老师
  getAuthor: params => {
    return HTTP.GET('/user/teacher/list', params)
  },

  // 显示列表
  getList: params => {
    return HTTP.GET('/resource/video', params)
  },

  // 搜索显示列表
  searchVideo: params => {
    return HTTP.PATCH('/resource/video', params)
  },

  // 查询获取一条video信息
  findById: code => {
    return HTTP.GET(`/resource/video/${code}`)
  },
  // 添加一个video信息
  add: params => {
    return HTTP.POST(`/resource/video`, params)
  },

  // 更新一个video信息
  update: (params) => {
    return HTTP.PUT(`/resource/video`, params)
  },

  // 单个删除poster
  remove: id => {
    return HTTP.DELETE(`/resource/video/${id}`)
  },

  // 查看详情页面
  postImg: params => {
    return HTTP.POST(`/resource/img`, params)
  }

  // // 批量删除，传ids数组
  // search: id => {
  //   return HTTP.GET(`/user/video/${id}`)
  // }
}
