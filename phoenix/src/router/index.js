import Vue from 'vue'
import Router from 'vue-router'
import Home from '@/views/Home'
import Login from '@/views/Login'
// import Welcome from '@/views/Welcome'
import Video from '@/views/resourceRegister/Video'
import User from '@/views/systemManage/User'
import Permission from '@/views/systemManage/Permission'
import UserGroup from '@/views/systemManage/UserGroup'
import UserGroupMember from '@/views/systemManage/UserGroupMember'
import CourseSystem from '@/views/teachingManage/CourseSystem'
import Course from '@/views/teachingManage/Course'
import CourseVideo from '@/views/teachingManage/Video'
import Article from '@/views/content/Article'
import Message from '@/views/content/Message'
import Twitter from '@/views/content/Twitter'
import Reply from '@/views/content/Reply'
import Report from '@/views/content/Report'
import Kit from '@/views/content/Kit'
import KitReport from '@/views/content/KitReport'
import Feed from '@/views/content/Feed'
import AuMessage from '@/views/audit/AuMessage'
import AuTwitter from '@/views/audit/AuTwitter'
import Ad from '@/views/propagandaManage/Ad'
import Forum from '@/views/propagandaManage/Forum'
import DynamicAd from '@/views/propagandaManage/DynamicAd'
import FeedElite from '@/views/operate/FeedElite'
import Category from '@/views/columnManage/Category'
import SubCategory from '@/views/columnManage/SubCategory'
import CategoryGroup from '@/views/columnManage/CategoryGroup'
import CategoryGroupMember from '@/views/columnManage/CategoryGroupMember'
import Teacher from '@/views/columnManage/Teacher'
import MoveQr from '@/views/operate/MoveQr'
import OpenapiPermission from '@/views/openapiManage/OpenapiPermission'
import OpenapiCode from '@/views/openapiManage/OpenapiCode'
import LiveRoom from '@/views/liveManage/LiveRoom'
import LiveStaticTalkshow from '@/views/liveManage/LiveStaticTalkshow'
import LiveTalkshow from '@/views/liveManage/LiveTalkshow'
import Discuss from '@/views/liveManage/Discuss'

Vue.use(Router)

let router = new Router({
  routes: [
    {
      path: '/',
      name: 'Login',
      component: Login
    },
    {
      path: '/home',
      name: 'Home',
      component: Home
    },
    {
      path: '/admin',
      component: Home,
      name: '系统管理',
      code: 'admin',
      menuShow: false,
      iconCls: 'font_meun icon-xitongguanli', // 图标样式class
      children: [
        { path: '/admin/user', component: User, name: '用户管理', code: 'user', menuShow: false },
        { path: '/admin/permission', component: Permission, name: '权限管理', code: 'permission', menuShow: false },
        { path: '/admin/user-group', component: UserGroup, name: '用户组管理', code: 'user_group', menuShow: false },
        // 路由守卫特殊处理该类页面
        { path: '/admin/user-group/member/:userGroupCode', component: UserGroupMember, name: '用户组成员管理', code: 'user_group_member', menuShow: false }
      ]
    },
    {
      path: '/resource',
      component: Home,
      name: '资源管理',
      code: 'resource',
      menuShow: false,
      iconCls: 'font_meun icon-ziyuanguanli', // 图标样式class
      children: [
        { path: '/resource/video', component: Video, name: '视频登记', code: 'video', menuShow: false }
      ]
    },
    {
      path: '/teaching',
      component: Home,
      name: '教学管理',
      code: 'teaching',
      menuShow: false,
      iconCls: 'font_meun icon-jiaoxueguanli', // 图标样式class
      children: [
        { path: '/teaching/coursesystem', component: CourseSystem, name: '课程体系管理', code: 'coursesystem', menuShow: false },
        { path: '/teaching/course', component: Course, name: '课程管理', code: 'course', menuShow: false },
        { path: '/teaching/course/video/:id', component: CourseVideo, name: '视频管理', code: 'coursevideo', menuShow: false }
      ]
    },
    {
      path: '/content',
      component: Home,
      name: '内容管理',
      code: 'content',
      menuShow: false,
      iconCls: 'font_meun icon-neirongguanli', // 图标样式class
      children: [
        { path: '/content/article', component: Article, name: '文章管理', code: 'article', menuShow: false },
        { path: '/content/twitter', component: Twitter, name: '动态管理', code: 'twitter', menuShow: false },
        { path: '/content/message', component: Message, name: '私信管理', code: 'private_message', menuShow: false },
        { path: '/content/reply', component: Reply, name: '评论管理', code: 'reply', menuShow: false },
        { path: '/content/report', component: Report, name: '个股报告管理', code: 'stock_report', menuShow: false },
        { path: '/content/kit', component: Kit, name: '锦囊管理', code: 'kit', menuShow: false },
        { path: '/content/KitReport', component: KitReport, name: '锦囊报告管理', code: 'kit_report', menuShow: false },
        { path: '/content/feed', component: Feed, name: '推送记录管理', code: 'feed', menuShow: false }
      ]
    },
    {
      path: '/audit',
      component: Home,
      name: '审批管理',
      code: 'examine',
      menuShow: false,
      iconCls: 'font_meun icon-shenpiguanli', // 图标样式class
      children: [
        { path: '/audit/twitter', component: AuTwitter, name: '动态关注申请', code: 'twitter_follow', menuShow: false },
        { path: '/audit/private-message', component: AuMessage, name: '私信聊天申请', code: 'private_message_follow', menuShow: false }
      ]
    },
    {
      path: '/propaganda',
      component: Home,
      name: '宣传管理',
      code: 'propaganda',
      menuShow: false,
      iconCls: 'font_meun icon-xuanchuanguanli',
      children: [
        {path: '/propaganda/ad', component: Ad, name: '广告管理', code: 'ad', menuShow: false},
        {path: '/propaganda/forum', component: Forum, name: '论坛管理', code: 'forum', menuShow: false},
        {path: '/propaganda/dynamic/ad', component: DynamicAd, name: '跑马灯管理', code: 'dynamic_ad', menuSHow: false}
      ]
    },
    {
      path: '/operate',
      component: Home,
      name: '运营管理',
      code: 'operate',
      menuShow: false,
      iconCls: 'font_meun icon-yunyingguanli',
      children: [
        {path: '/operate/handpick', component: FeedElite, name: '内容精选', code: 'feed_elite', menuShow: false},
        {path: '/operate/moveqr', component: MoveQr, name: '活码管理', code: 'moveqr', menuShow: false}
      ]
    },
    {
      path: '/column',
      component: Home,
      name: '栏目管理',
      code: 'column',
      menuShow: false,
      iconCls: 'font_meun icon-lanmuguanli',
      children: [
        {path: '/column/category', component: Category, name: '栏目分类管理', code: 'category', menuShow: false},
        {path: '/column/category/:categoryCode', component: SubCategory, name: '子栏目分类管理', code: 'sub_category', menuShow: false},
        {path: '/column/category_group', component: CategoryGroup, name: '栏目分组管理', code: 'category_group', menuShow: false},
        {path: '/column/category_group/member/:columnGroupCode', component: CategoryGroupMember, name: '栏目组成员管理', code: 'category_group_member', menuShow: false},
        {path: '/column/teacher', component: Teacher, name: '栏目老师管理', code: 'teacher', menuShow: false}
      ]
    },
    {
      path: '/openapi',
      component: Home,
      name: '接口管理',
      code: 'openapi',
      menuShow: false,
      iconCls: 'font_meun icon-jiekouguanli', // 图标样式class
      children: [
        { path: '/openapi/openapicode', component: OpenapiCode, name: '密钥管理', code: 'openapicode', menuShow: false },
        { path: '/openapi/openapipermission', component: OpenapiPermission, name: '接口权限管理', code: 'openapipermission', menuShow: false }
      ]
    },
    {
      path: '/live',
      component: Home,
      name: '直播管理',
      code: 'live',
      menuShow: false,
      iconCls: 'font_meun icon-zhiboguanli',
      children: [
        { path: '/live/room', component: LiveRoom, name: '直播室管理', code: 'liveroom', menuShow: false },
        { path: '/live/static-talkshow', component: LiveStaticTalkshow, name: '固定节目管理', code: 'static_talkshow', menuShow: false },
        { path: '/live/talkshow', component: LiveTalkshow, name: '每日节目管理', code: 'talkshow', menuShow: false },
        { path: '/live/discuss', component: Discuss, name: '直播互动管理', code: 'discuss', menuShow: false }
      ]
    }
  ]
})

router.beforeEach((to, from, next) => {
  var permission = sessionStorage.getItem('zytg_update_permissions')
  var showPage = false
  // 有权限隐藏目录需要特殊处理
  var permissionArr = [ '子栏目分类管理',
    '用户组成员管理',
    '栏目组成员管理',
    '视频管理']

  // 确认是否展示隐藏目录页面
  permissionArr.forEach(d => {
    if (to.name.indexOf(d) > -1) {
      showPage = true
    }
  })

  if (showPage) {
    next()
  } else if (from.fullPath === '/home') {
    if (permission && permission.indexOf(to.path + ' ') === -1) {
      next('/')
    } else {
      next()
    }
  } else {
    if (permission && permission.indexOf(to.path + ' ') === -1) {
      next('/home')
    } else {
      next()
    }
  }
})

export default router
