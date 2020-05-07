import * as HTTP from './'

export default {
  // 获取用户列表
  getUserList: params => {
    return HTTP.GET('/user/list', params)
  },

  getUserListOfAll: params => {
    return HTTP.GET('/user/all/list', params)
  },

  // 搜索用户列表
  searchUser: params => {
    return HTTP.GET('/user/list', params)
  },

  // 获取所有权限列表
  getUserGrantList: params => {
    return HTTP.GET('/user/grant/list', params)
  },

  // 搜索用户权限列表
  searchUserGrant: params => {
    return HTTP.GET('/user/grant/list', params)
  },

  // 通过id获取用户的权限列表
  getUserPermission: id => {
    return HTTP.GET(`/user/permission/${id}`)
  },

  postGrant: params => {
    return HTTP.POST(`/user/grant`, params)
  },

  // 添加user
  addUser: params => {
    return HTTP.POST(`/user`, params)
  },

  // 获取指定id的user
  findUser: id => {
    return HTTP.GET(`/user/${id}`)
  },

  // 更新user
  updateUser: (id, params) => {
    return HTTP.PUT(`/user/${id}`, params)
  },

  // 更新user的active状态
  changeActive: (userId, active) => {
    return HTTP.PUT(`/user/${userId}/active/${active}`)
  },

  // 更新user的selected状态
  changeSelected: (userId, selected) => {
    return HTTP.PUT(`/user/${userId}/selected/${selected}`)
  },

  // 获取 teacher-tab 列表
  getTeacherTabs: params => {
    return HTTP.GET(`/user/teacher-tab/list`, params)
  },

  // 获取标记类型
  getSignTypes: () => {
    return HTTP.GET(`/user/sign-type/list`)
  },

  // 获取用户组列表
  getUserGroups: params => {
    return HTTP.GET(`/user-group/list`, params)
  },

  // 添加用户组
  createUserGroup: params => {
    return HTTP.POST(`/user-group`, params)
  },

  // 获取用户组详情信息
  getUserGroupDetail: (userGroupCode, params) => {
    return HTTP.GET(`/user-group/${userGroupCode}`, params)
  },

  // 编辑用户组信息
  updateUserGroup: params => {
    return HTTP.PUT(`/user-group`, params)
  },

  // 删除用户组
  deleteUserGroup: userGroupCode => {
    return HTTP.DELETE(`/user-group/${userGroupCode}`)
  },

  createUserGroupMember: params => {
    return HTTP.POST(`/user-group/member`, params)
  },

  getUserGroupMember: id => {
    return HTTP.GET(`/user-group/member/${id}`)
  },

  updateUserGroupMember: (id, params) => {
    return HTTP.PUT(`/user-group/member/${id}`, params)
  },

  deleteUserGroupMember: id => {
    return HTTP.DELETE(`/user-group/member/${id}`)
  },

  getUserIdOfUserGroupCode: userGroupCode => {
    return HTTP.GET(`/user-group/member/${userGroupCode}/user_ids`)
  }
}
