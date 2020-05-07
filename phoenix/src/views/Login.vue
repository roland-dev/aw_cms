<template>
  <div>
      <div class="title">CMS系统</div>      
  </div>
</template>

<script>
import HTTP from '../http/api_user'

export default {
  name: 'login',
  data () {
    return {
      islogin: false,
      routesArr: []
    }
  },
  mounted: function () {
    this.login()
  },
  methods: {
    login () {
      HTTP.user().then(data => {
        if (data.code === 0) {
          // 获取权限路由
          HTTP.getPermisson().then(data => {
            data.data.menu.forEach(d => {
              if (d.granted) {
                this.routesArr.push(d.code)
              }
              if (d.child) {
                d.child.forEach(child => {
                  if (child.granted) {
                    this.routesArr.push(child.code)
                  }
                })
              }
            })
            this.$router.options.routes.forEach(d => {
              // console.log(d)
              if (this.routesArr.indexOf(d.code) !== -1) {
                d.menuShow = true
                if (d.children) {
                  d.children.forEach(child => {
                    // console.log(child)
                    if (this.routesArr.indexOf(child.code) !== -1) {
                      child.menuShow = true
                    }
                  })
                }
              }
            })
            this.$router.push({path: '/home'})
          }).catch(err => {
            console.error(err)
          })
        } else {
          HTTP.login().then(data => {
            if (data.code === 0) {
              window.location = data.data.loginUrl
            }
          })
        }
      }).catch(err => {
        HTTP.login().then(data => {
          if (data.code === 0) {
            window.location = data.data.loginUrl
          }
          console.error(err)
        }).catch(error => {
          alert('UC连接错误！')
          console.error(error)
        })
      })
    }
  }
}
</script>

<style scoped lang="less">
  .title{
    font-family: '微软雅黑';
    margin: 40px;
    text-align: center;
    font-size: 28px;
    font-weight: 700;
    letter-spacing: 4px;
    color: #0082EF;
  }
  #wx_login{
    height: 100%;
    width: 100%;
    text-align: center;
    iframe{
      height: 100vh;
    }
  }
</style>
