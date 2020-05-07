import Env from './env'
import axios from 'axios'
import { Message } from 'element-ui'
// 跨域安全策略
axios.defaults.withCredentials = true
// axios.defaults.timeout = 5000
axios.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=UTF-8'
axios.defaults.headers.get['Content-Type'] = 'application/x-www-form-urlencoded;charset=UTF-8' // 配置请求头

// 添加一个请求拦截器
// axios.interceptors.request.use(function (config) {
//   console.dir(config);
//   return config;
// }, function (error) {
//   // Do something with request error
//   return Promise.reject(error);
// });

// 添加一个响应拦截器
axios.interceptors.response.use(function (response) {
  if (response.status === 401 || response.data.code === 401) {
    window.location = response.data.data.front_url
  } else {
    return response
  }
}, function (err) {
  let error = JSON.parse(JSON.stringify(err))

  if (error.response.status === 404) {
    if (document.querySelectorAll('.el-message--warning').length > 0) {
      console.log('404: 请求错误或服务器异常! ')
    } else {
      Message.warning({
        message: '404: 请求错误或服务器异常! '
      })
    }
  } else if (error.response.status === 500) {
    if (document.querySelectorAll('.el-message--warning').length > 0) {
      console.log('500: 网络连接错误, 请检查网络！')
    } else {
      Message.warning({
        message: '500: 网络连接错误, 请检查网络！'
      })
    }
  } else if (error.response.status === 401) {
    if (document.querySelectorAll('.el-message--warning').length > 0) {
      console.log('401: 登录已失效，请刷新页面！')
    } else {
      Message.warning({
        message: '401: 登录已失效, 请刷新页面！'
      })
    }
  }
  return Promise.reject(err)
})

// 基地址
let base = Env.baseURL

// UC地址
let ucApi = Env.UC_API_URL

// STOCK地址
let stockApi = Env.STOCK_API_URL

// 测试使用
export const ISDEV = Env.isDev

// 通用方法
export const POST = (url, params) => {
  return axios.post(`${base}${url}`, params).then(res => res.data)
}

export const ucPOST = (url, params) => {
  return axios.post(`${ucApi}${url}`, params).then(res => res.data)
}

export const GET = (url, params) => {
  return axios.get(`${base}${url}`, { params: params }).then(res => res.data)
}

export const stockGET = (url, params) => {
  return axios.get(`${stockApi}${url}`, { params: params }).then(res => res.data)
}

export const PUT = (url, params) => {
  return axios.put(`${base}${url}`, params).then(res => res.data)
}

export const DELETE = (url, params) => {
  return axios.delete(`${base}${url}`, { params: params }).then(res => res.data)
}

export const PATCH = (url, params) => {
  return axios.patch(`${base}${url}`, params).then(res => res.data)
}
