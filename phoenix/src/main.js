// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'
import ElementUI from 'element-ui'
import Croppa from 'vue-croppa'
import 'element-ui/lib/theme-chalk/index.css'
import 'vue-croppa/dist/vue-croppa.css'
import '@/assets/css/reset.css'   // 初始化样式
import '@/assets/css/iconfont.css'
import '@/assets/css/main.css'

Vue.use(ElementUI, { size: 'small' })  // 引入element-ui
Vue.use(Croppa)  // 引入图片裁切插件

Vue.config.productionTip = false

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  template: '<App/>',
  components: { App }
})
