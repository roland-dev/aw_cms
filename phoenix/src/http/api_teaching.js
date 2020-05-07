import * as HTTP from './'

export default {
  /**
    * 获取课程体系列表
    */
  getCourseSystemList: params => {
    return HTTP.GET('/resource/coursesystem', params)
  },

  getCourseSystemListOfAll: params => {
    return HTTP.GET('/resource/coursesystem/all/list', params)
  },

  /**
    * 获取课程体系
    * @param couserSystemId 课程体系id
    */
  findCourseSystem: couserSystemId => {
    return HTTP.GET(`/resource/coursesystem/${couserSystemId}`)
  },

  /**
    * 新增课程体系
    * @param name 课程体系名称
    * @param code 课程key值
    */
  addCourseSystem: params => {
    return HTTP.POST('/resource/coursesystem', params)
  },

  /**
   * 编辑课程体系
   * @param name 课程体系名称
   * @param code 课程key值
   * @param course_system_id 课程体系id
   */
  updateCourseSystem: params => {
    return HTTP.PUT('/resource/coursesystem', params)
  },

  /**
   * 删除课程体系
   * @param course_system_id 课程体系id
   */
  removeCourseSystem: (courseSystemId, courseSystemCode) => {
    return HTTP.DELETE(`/resource/coursesystem/${courseSystemId}/${courseSystemCode}`)
  },

  /**
   * 获取课程列表 */
  getCourseList: params => {
    return HTTP.GET('/resource/coursesystem/course/list', params)
  },

  /**
   * 获取课程
   * @param id 课程id
   */
  getCourse: id => {
    return HTTP.GET(`/resource/coursesystem/course/${id}`)
  },

  /**
   * 搜索课程
   * @param course_name 课程名
   * @param course_system_code 内容权限控制表id
   */
  searchCourse: params => {
    return HTTP.PATCH(`/resource/coursesystem/course`, params)
  },

  /**
   * 新增课程
   * @param name 课程名
   * @param description 课程描述
   * @param course_system_code 课程体系key值
   * @param service_key 服务key值
   */
  addCourse: params => {
    return HTTP.POST(`/resource/coursesystem/course`, params)
  },

  /**
   * 获取课程
   * @param courseId 课程名
   * @param courseSystemId 课程体系名称id
   */
  findCourse: (courseId, courseSystemId, courseCode) => {
    return HTTP.GET(`/resource/coursesystem/course/${courseId}/${courseSystemId}/${courseCode}`)
  },

  /**
   * 编辑课程
   * @param course_id 课程id
   * @param name 课程名字
   * @param description 课程描述
   * @param course_system 课程体系key值
   * @param service_key 服务key值
   * @param content_access_control_id 内容权限控制表id
   */
  updateCourse: params => {
    return HTTP.PUT('/resource/coursesystem/course', params)
  },

  /**
   * 删除课程
   * @param courseId 课程id
   * @param contentAccessControlId 内容权限控制表id
   */
  removeCourse: (courseId, courseCode) => {
    return HTTP.DELETE(`/resource/coursesystem/course/${courseId}/${courseCode}`)
  },

  /**
   * 视频上传封面图片
   * @param image 上传图片
   * @param image_path 原图路径
   * @param thumbnail_path 缩略图路径
   */
  uploadVideoImage: params => {
    return HTTP.POST(`/resource/coursesystem/course/image`, params)
  },
  /**
  *添加视频
  * @param name 视频名称
  * @param image_path 原始图片路径
  * @param thumbnail_path 缩略图片路径
  * @param url 上传url
  * @param is_display 是否显示
  * @param author_id 视频作者id
  * @param course_id 课程id
  */
  addVideo: params => {
    return HTTP.POST(`/resource/coursesystem/course/video`, params)
  },

  /**
  *获取视频作者列表
  */
  getVideoAuthorList: params => {
    return HTTP.GET(`/user/videoauthor/list`, params)
  },

  /**
  *获取视频列表
  */
  getVideoList: (code, params) => {
    return HTTP.GET(`/resource/coursesystem/course/video/list/onecourse/${code}`, params)
  },

  /**
  *删除视频
  */
  removeVideo: (videoId, courseVideoId) => {
    return HTTP.DELETE(`/resource/coursesystem/course/video/${videoId}/${courseVideoId}`)
  },

  /**
  *获取视频详情
  */
  getOneVideoInfo: (videoId, courseVideoId) => {
    return HTTP.GET(`/resource/coursesystem/course/video/onecourse/${videoId}/${courseVideoId}`)
  },
  /**
  *编辑视频
  */
  editVideo: params => {
    return HTTP.PUT(`/resource/coursesystem/course/video`, params)
  },
  /**
  *删除图片
  */
  removeImage: params => {
    return HTTP.POST(`/resource/coursesystem/course/image/imagepath`, params)
  },
  /**
  *判定code唯一性
  */
  checkCourseCodeUnique: (courseCode) => {
    return HTTP.GET(`/resource/coursesystem/course/${courseCode}`)
  },
  /**
  *判定code唯一性
  */
  checkCourseSystemCodeUnique: (courseSystemCode) => {
    return HTTP.GET(`/resource/coursesystem/checkcode/${courseSystemCode}`)
  },
  /**
  *获取服务列表
  */
  getServiceList: params => {
    return HTTP.GET(`/resource/coursesystem/course/servicelist`, params)
  },
  /**
  *更新课程体系排序
  */
  courseSystemOrder: params => {
    return HTTP.POST(`/resource/coursesystem/sequence`, params)
  },
  /**
  *更新课程排序
  */
  courseOrder: params => {
    return HTTP.POST(`/resource/coursesystem/course/sequence`, params)
  },
  /**
  *更新课程视频排序
  */
  courseVideoOrder: params => {
    return HTTP.POST(`/resource/coursesystem/course/video/sequence`, params)
  },
  /**
   * 获取栏目列表
   */
  getCategoryList: params => {
    return HTTP.GET(`/resource/coursesystem/categorylist`, params)
  }
}
