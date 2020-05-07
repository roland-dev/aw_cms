<template>
  <el-container>
    <el-header>
      <h1 class="top-title">和众汇富CMS系统</h1>  
      <el-dropdown trigger="click">
        <span class="el-dropdown-link">
          <i class="iconfont icon-user"></i> {{nickname}}<i class="iconfont icon-down"></i>
        </span>
        <el-dropdown-menu slot="dropdown">
          <el-dropdown-item divided @click.native="logout" style="border: none">退出登录</el-dropdown-item>
        </el-dropdown-menu>
      </el-dropdown>
    </el-header>

    <div class="container">
      <aside :class="{showSidebar:!collapsed}">
        <!--展开折叠开关-->
        <div class="menu-toggle" @click.prevent="collapse">
          <i class="iconfont icon-menufold" v-show="!collapsed"></i>
          <i class="iconfont icon-menuunfold" v-show="collapsed"></i>
        </div>
        <!--导航菜单-->
        <el-menu 
          router 
          :unique-opened="true"
          :default-active="$route.path"      
          :collapse="collapsed">
          <template v-for="item in routes">
            <el-submenu  
              v-if="item.menuShow"
              :index="$route.path.indexOf(item.path) != -1 ?　item.path　:　item.path" 
              :key="item.name"> 
              <template slot="title">
                <i :class="item.iconCls"></i>
                <span slot="title">{{item.name}}</span>
              </template>
              <div v-for="term in item.children" :key="term.name" >
                <el-menu-item 
                  v-if="term.menuShow"
                  :index="term.path" 
                  :class="$route.path==term.path?'is-active':''">
                  <span slot="title">{{term.name}}</span>
                </el-menu-item>
              </div>
              
            </el-submenu>
          </template>
        </el-menu>  
      </aside>
      <el-main ref="main">
        <!-- <h2 class="welcome" v-if="welcome">欢迎来到CMS系统</h2>  -->
        <transition name="fade" mode="out-in">
          <router-view></router-view>
        </transition>
      </el-main>
    </div>
  </el-container>
</template>

<script>
import HTTP from '../http/api_user'
import API_CONTENT from '../http/api_content'

export default {
  name: 'home',
  data () {
    return {
      nickname: '未登录',
      collapsed: false,
      welcome: true,
      routesArr: [],
      routes: this.$router.options.routes,
      auTwitterNum: 0,
      auMessageNum: 0,
      permisson: '/home ',
      isTwitterFollow: false,
      isPMessageFollow: false
    }
  },
  created () {
    this.login()
  },
  mounted () {
    setTimeout(() => {
      this.applyAudit()
    }, 5000)
    this.checkScreen()
  },
  methods: {
    // 折叠导航栏
    collapse () {
      this.collapsed = !this.collapsed
      if (this.collapsed) {
        this.$refs.main.$el.style.marginLeft = 60 + 'px'
      } else {
        this.$refs.main.$el.style.marginLeft = 190 + 'px'
      }
    },
    // showMenu (i, status) {
    //   this.$refs.menuCollapsed.getElementsByClassName('submenu-hook-' + i)[0].style.display = status ? 'block' : 'none'
    // },

    checkScreen () {
      if (window.screen.width < 700) {
        this.collapsed = true
        this.$refs.main.$el.style.marginLeft = 60 + 'px'
      } else {
        this.$refs.main.$el.style.marginLeft = 190 + 'px'
      }
    },

    login () {
      HTTP.user().then(res => {
        if (res.code === 0) {
          this.nickname = res.data.user_info.name
          // 获取权限路由
          HTTP.getPermisson().then(data => {
            this.permisson = '/home '
            data.data.menu.forEach(d => {
              if (d.granted) {
                this.routesArr.push(d.code)
              }
              if (d.child) {
                d.child.forEach(child => {
                  // 判断使用是否有动态审核和私信审核权限
                  if (child.id === 20) {
                    this.isTwitterFollow = child.granted
                  }
                  if (child.id === 21) {
                    this.isPMessageFollow = child.granted
                  }
                  if (child.granted) {
                    this.routesArr.push(child.code)
                  }
                })
              }
            })
            this.routes.forEach(d => {
              if (this.routesArr.indexOf(d.code) !== -1) {
                d.menuShow = true
                this.permisson += d.path + ' '
                if (d.children) {
                  d.children.forEach(child => {
                    if (this.routesArr.indexOf(child.code) !== -1) {
                      child.menuShow = true
                      this.permisson += child.path + ' '
                    }
                  })
                }
              }
            })
            sessionStorage.setItem('zytg_update_permissions', this.permisson)
            var menuItems = document.querySelectorAll('.el-menu-item')
            this.removeClass(menuItems, 'is-active')
          }).catch(err => {
            console.error(err)
          })
        } else {
          HTTP.login().then(data => {
            if (data.code === 0) {
              window.location = data.data.loginUrl
            }
          }).catch(err => {
            console.log(err)
            alert('链接UC失败')
          })
        }
      }).catch(err => {
        console.error(err)
        HTTP.login().then(data => {
          if (data.code === 0) {
            window.location = data.data.loginUrl
          }
        }).catch(error => {
          console.log(error)
          alert('链接UC失败')
        })
      })
    },
    logout () {
      // let that = this
      this.$confirm('确认退出吗?', '提示', {
        confirmButtonClass: 'el-button--warning'
      }).then(() => {
        // 确认
        console.log('退出UC')
        HTTP.uclogout({}).then(uc => {
          HTTP.logout().then(data => {
            if (data.code === 0) {
              console.log('退出成功')
              HTTP.login().then(data => {
                if (data.code === 0) {
                  window.location = data.data.loginUrl
                }
              }).catch(err => {
                console.log(err)
                alert('链接UC失败')
              })
              // this.$router.push({path: '/'})
            }
          }).catch(err => {
            console.error(err)
          })
        }).catch(err => {
          console.log(err)
        })
      }).catch(() => {})
    },

    // 审核申请通知(五分钟循环调用))
    applyAudit () {
      if (this.isTwitterFollow) {
        API_CONTENT.getTwitterRequest({'status': 0}).then(res => {
          let twitterReqNum = res.data.twitter_request_list.length
          if (twitterReqNum > this.auTwitterNum) {
            let twitterNotice = this.$notify({
              title: '提示',
              message: `收到${twitterReqNum - this.auTwitterNum}条动态审批新申请，<span style="color: #409EFF; cursor: pointer">去查看</span>`,
              duration: 0,
              dangerouslyUseHTMLString: true,
              showClose: false,
              onClick: () => {
                this.$router.push('/audit/twitter')
                twitterNotice.close()
              }
            })
          }
          this.auTwitterNum = twitterReqNum
        }).catch(err => {
          console.error(err)
        })
      }

      if (this.isPMessageFollow) {
        API_CONTENT.getMessageRequest({'status': 0}).then(res => {
          if (res.data && res.data.private_message_request_list) {
            let messageReqNum = res.data.private_message_request_list.length
            if (messageReqNum > this.auMessageNum) {
              let messageNotice = this.$notify({
                title: '提示',
                message: `收到${messageReqNum - this.auMessageNum}条私信审批新申请，<span style="color: #409EFF; cursor: pointer">去查看</span>`,
                duration: 0,
                dangerouslyUseHTMLString: true,
                showClose: false,
                onClick: () => {
                  this.$router.push('/audit/private-message')
                  messageNotice.close()
                }
              })
            }
            this.auMessageNum = messageReqNum
          }
        }).catch(err => {
          console.error(err)
        })
      }
      if (this.isPMessageFollow || this.isTwitterFollow) {
        this.loopApplyAudit()
      }
    },

    // 循环审核申请
    loopApplyAudit () {
      setInterval(() => {
        if (this.isTwitterFollow) {
          API_CONTENT.getTwitterRequest({'status': 0}).then(res => {
            let twitterReqNum = res.data.twitter_request_list.length
            if (twitterReqNum > this.auTwitterNum) {
              let twitterNotice = this.$notify({
                title: '提示',
                message: `收到${twitterReqNum - this.auTwitterNum}条动态审批新申请，<span style="color: #409EFF; cursor: pointer">去查看</span>`,
                duration: 0,
                dangerouslyUseHTMLString: true,
                showClose: false,
                onClick: () => {
                  this.$router.push('/audit/twitter')
                  twitterNotice.close()
                }
              })
            }
            this.auTwitterNum = twitterReqNum
          }).catch(err => {
            console.error(err)
          })
        }
        if (this.isPMessageFollow) {
          API_CONTENT.getMessageRequest({'status': 0}).then(res => {
            if (res.data && res.data.private_message_request_list) {
              let messageReqNum = res.data.private_message_request_list.length
              if (messageReqNum > this.auMessageNum) {
                let messageNotice = this.$notify({
                  title: '提示',
                  message: `收到${messageReqNum - this.auMessageNum}条私信审批新申请，<span style="color: #409EFF; cursor: pointer">去查看</span>`,
                  duration: 0,
                  dangerouslyUseHTMLString: true,
                  showClose: false,
                  onClick: () => {
                    this.$router.push('/audit/private-message')
                    messageNotice.close()
                  }
                })
              }
              this.auMessageNum = messageReqNum
            }
          }).catch(err => {
            console.error(err)
          })
        }
      }, 5 * 60 * 1000)
    },

    removeClass (obj, cls) {
      let objClass = ' ' + obj.className + ' '    // 获取 class 内容, 并在首尾各加一个空格. ex) 'abc    bcd' -> ' abc    bcd '
      objClass = objClass.replace(/(\s+)/gi, ' ')  // 将多余的空字符替换成一个空格. ex) ' abc    bcd ' -> ' abc bcd '
      let removed = objClass.replace(' ' + cls + ' ', ' ')  // 在原来的 class 替换掉首尾加了空格的 class. ex) ' abc bcd ' -> 'bcd '
      removed = removed.replace(/(^\s+)|(\s+$)/g, '')  // 去掉首尾空格. ex) 'bcd ' -> 'bcd'
      obj.className = removed // 替换原来的 class.
    }
  }
}
</script>
import '@/assets/css/common.less'


<style lang="less">
@blue:#39a0ed;
// 标题栏
.el-header{
  position: fixed;
  top: 0;
  width: 100%;
  height: 60px;
  background: @blue;
  color: #fff;
  line-height: 60px;
  z-index: 1002;
  h1{
    display: inline-block;
  }
  .el-dropdown{
    float: right;
    color: #fff;
    cursor: pointer;
    .el-dropdown-menu{
      .link-item{
        color: #555;
      }
    }
  }
}
.container{
  position: relative;
  top: 60px;
  width: 100%;
  // 侧导航 
  aside {
    position: fixed;
    top: 60px;
    z-index: 888;
    height: calc(~"100% - 80px");
    background: #fff;
    color: #fff;
    &::-webkit-scrollbar {
      display: none;
    }

    &.showSidebar {
      overflow-x: hidden;
      overflow-y: auto;
    }
    .menu-toggle {
      background: lighten(@blue, 10%);
      text-align: center;
      color: white;
      height: 26px;
      line-height: 30px;
    }
    .el-submenu .el-menu-item {
      min-width: 60px;
    }
    .el-menu {
      width: 180px;
      .is-opened{
        .el-submenu__title{
          color: #fff;
          background: @blue;
          i{
            color: #fff;
          }
        }
      }
      .el-submenu__title{
        border-bottom: 1px solid #e2e2e2;
      }
      .el-menu-item{
        border-bottom: 1px solid #e2e2e2;      
      }
    }
    .el-menu--collapse {
      width: 60px;
    }
  }
}
// 内容区
.el-main {
  padding: 10px;
  margin-left: 60px;
}


</style>
