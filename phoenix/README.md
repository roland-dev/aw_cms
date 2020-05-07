# phoenix

> CMS前端

### vue-cli    使用vue-cli搭建项目  （https://www.npmjs.com/package/vue-cli）
### elementUI  前端UI框架  (http://element-cn.eleme.io/#/zh-CN/component/installation)
### less       css样式预处理  (http://lesscss.cn/)
### axios      基于Promise 用于浏览器和 nodejs 的 HTTP 客户端  (https://www.kancloud.cn/yunye/axios/234845)
### vue-router vue路由  (https://router.vuejs.org/zh-cn/installation.html)
### wangeditor 富文本编辑器  (http://www.wangeditor.com/)  
  
  
***
## 新建一个前端项目 

**进入到 `/phoenix` 之后进行如下操作:（启动vue项目要本地安装node环境及npm）**

    `更改文件`：     # 进入/phoenix/src/http 复制文件env.js.dev到当前文件夹 名字改为env.js 用于开发环境

    `npm install`:  # install dependencies

    `npm run dev`:  # serve with hot reload at localhost:8010


    `npm run build`: # build for production with minification

**其他常用命令**    

    `npm run build --report`: # build for production and view the bundle analyzer report

***
## Group Tips 提示周知
  [Git 使用规范流程](http://www.ruanyifeng.com/blog/2015/08/git-use-process.html) 合理、清晰的Git使用流程，让我们的生活更美好 : ) 

  [Git 常用命令清单](http://www.ruanyifeng.com/blog/2015/12/git-cheat-sheet.html)

  [Font-Awesome](http://fontawesome.io/) 

  [团队开发规范(beta)](http://code.daohehui.com/w/projects/cms/share/codingstyle/)  

  [ECMAScript 6 入门](http://es6.ruanyifeng.com/) es6 新特性

***
## 一些样式的开发约定

```html
  1. 颜色值统一大写，能3位的不用6位，如#FFF

  2. 尽量少用!important 和 z-index样式

  3. 只使用在对应模块的class样式，添加样式时候要加上scope，避免全局污染

  4. css排序规则: 显示 > 浮动 > 定位 > 尺寸 > 字体样式 > 颜色背景 > 边框相关属性  > 其他样式

  5. class一律采用小写加中划线的方式，不允许使用大写字母或 _ 

  6. 命名尽量避免使用中文拼音，应该采用更简明有语义的英文单词进行组合 

  7. 命名注意缩写，但是不能盲目缩写，具体请参见常用的CSS命名规则 

  8. 不允许通过1、2、3等序号进行命名 

  9. 避免class与id重名 

  10. id要唯一，不允许一个页面出现两个相同的id名字 

  11. class用于标识某一个类型的对象，命名必须言简意赅。 

  12. 尽可能提高代码模块的复用，样式尽量用组合的方式 

...

(to be continued)
```


