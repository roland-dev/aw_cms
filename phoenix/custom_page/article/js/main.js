
// 登录
function setUser (data) {
    var userData = {
        "session_id": data.sessionId,
        "channel": "hk"
    }
    
    // 存储sessionId到localStorage
    localStorage.setItem("hk_uc_sessionId", data.sessionId)

    $.ajax({
        type:"post",
        url: baseURL + "/api/v2/customer/login",
        xhrFields: {
            withCredentials: true   // 跨域请求
        },
        headers: {
          "X-SessionId": data.sessionId,
        },
        dataType: "json",
        data: userData,  //连同请求发送到服务器的数据。
        success: function(data){
            console.log("已登录")
            set('user', data.data.customer)
            window.history.go(0)
        }
    });
}

// 退出登录
function logout() {
    layer.open({
      content: '是否确认要退出登录？',
      btn: ['是的', '不要'],
      yes: function (index) {
        $.ajax({
          type: "post",
          url: baseURL + "/api/v2/customer/logout",
          xhrFields: {
            withCredentials: true // 跨域请求
          },
          dataType: "json",
          success: function (d) {
            $.ajax({
              type: "post",
              url: UC_API_URL + "/session/out",
              xhrFields: {
                withCredentials: true // 跨域请求
              },
              dataType: "json",
              contentType: "application/json",
              data: '{}',
              success: function (data) {
                if (data.code === 0) {
                  clear('user')
                  localStorage.removeItem("article_uc_sessionId")
                  $('.user').css("display", "none")
                  window.history.go(0)
                }
              },
              error: function (data) {
                console.log("网络超时");
                goLoginPage();
              }
            })
          }   
        });
      }
    })    
}

//封装过期控制代码
function set(key,value){
    var curTime = new Date().getTime();
    localStorage.setItem(key,JSON.stringify({data:value,time:curTime}));
}
function get(key,exp){
    var data = localStorage.getItem(key);
    if (!data) {
        return false;
    }
    var dataObj = JSON.parse(data);
    if (new Date().getTime() - dataObj.time>exp) {
        console.log('信息已过期');
    }else{
        return dataObj;
    }
}
// 清除user数据
function clear(key){
    localStorage.removeItem(key);
}

// 时间截取
function cutstr(str) {
    var newStr = str.substring(0, 10)
    if (new Date(newStr).toDateString() === new Date().toDateString()) {
      newStr = "今天" + str.substring(10, str.length - 3);
    } else {
      newStr = str.substring(0, str.length - 3);
    }
    return newStr;
}

//获取url中的参数
function getUrlParam(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
    var r = window.location.search.substr(1).match(reg);  //匹配目标参数
    if (r != null) return unescape(r[2]); return null; //返回参数值
};

function refresh() {
    window.history.go(0);
}

// 处理iframe引用视频尺寸问题
function fitIframe() {
    var iframes = $("#article iframe")
    if (iframes.length > 0) {
        var iframeWidth = $("#article").width()*0.66; 
        iframes.css({
            "width": "100%",
            "height": iframeWidth + "px"
        })
    } 
}

// 保存手机号
function saveMobile (mobile) {
    localStorage.setItem("rfzq_mobile", mobile);
}

// 写入手机号
function writeMobile () {
    var mobile = localStorage.getItem("rfzq_mobile");
    if (mobile) { 
        return mobile;
    } else {
      return false;
    }
}

// 保存列表分类
function saveActiveTab (index) {
  sessionStorage.setItem("active_tab", index)
}

// 写入列表分类
function writeActiveTab () {
  var tab = sessionStorage.getItem("active_tab")
  if (tab) {
    return tab;
  } else {
    return false;
  }
}

// 过滤阅读数
function formatReadNum (num) {
    if (num > 9999) {
      num = num / 10000
      num = num.toFixed(1)
      return num + "万";
    } else {
      return num
    }
}

// 过滤点赞数
function formatLikeNum (num) {
  if (num > 999) {
    num = "999+";
  }
  return num;
}

// 文章分类区别
function adaptTab() {
  var windowWidth = $(window).width()
  var minWidth = 120 // 最小宽度
  var tabTotalWidth = $('.tab').length * minWidth
  if (windowWidth >= tabTotalWidth) {
    $('.tab').removeClass('scroll').addClass('flex')
    $('.tabs').removeClass('scroll').addClass('flex')
  } else {
    // 平均分配
    $('.tab').removeClass('flex').addClass('scroll')
    $('.tabs').removeClass('flex').addClass('scroll')
    $('.tabs').css('width', tabTotalWidth + 'px')
  }
}

// 文章跳转
function toArticle(id) {
  window.location.href = "article.html?id=" + id;
}

// 保存当前页面，如果没有则跳转首页
function saveLastUrl() {
  // 设置lastVisit标识
  set('last_url', window.location.href)

  // 监测回退时间，如是回退到列表页，则清除lastVisit标识，避免循环跳转
  window.history.pushState(null, null, window.location.href)
  window.addEventListener('popstate', function () {
    clear('last_url')
    window.history.back()
  });
}

function enterLastUrl() {
  // 设置lastVisit标识
  var lastURL = get('last_url', 1800000)

  if (lastURL && lastURL.data) {
    // 跳转前设置详情页的返回目标页
    window.history.pushState(null, null, window.location.href);
    window.location.href = lastURL.data;
  }
}

// 获取cookie
function getCookie(c_name){
  if (document.cookie.length>0) {
    c_start=document.cookie.indexOf(c_name + "=")
    if (c_start != -1) { 
      c_start=c_start + c_name.length+1 
      c_end=document.cookie.indexOf(";",c_start)
      if (c_end==-1) c_end=document.cookie.length
      return unescape(document.cookie.substring(c_start,c_end))
    } 
  }
  return ""
}

function ReplaceUrl (text) {
  text = text.replace(/<a href[^>]*>/gi, '')
  text = text.replace(/<\/a>/gi, '')
  text = text.replace(/style=\"(.*?)\"/gi, '')
  // 兼容富文本多次复制
  text = text.replace(/<span href[^>]*>/gi, '<span>')
  text = text.replace(/&lt;/g, '')
  text = text.replace(/&gt;/g, '')
  text = text.replace(/<\//g, ' </')
  // 提前存储img标签再取出来用
  var imgStore = []
  text = text.replace(/<img [^>]*>/gi, function (a, i) {   // a表示正则匹配的内容， i表示正则匹配的位置
    imgStore.push({'site': i, 'img': a})
    return 'imgStore' + i
  })

  var re = /(http[s]?:\/\/)+([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w\.\?\-\=\&\;\%]*)*\/?/gi;
  var s = text.replace(re, function (a) {
    return '<a href="' + a + '">' + a + '</a>'
  })
  if (imgStore.length > 0) {
    for (var i = 0; i < imgStore.length; i++) {
      var site = 'imgStore' + imgStore[i].site
      var regExp =new RegExp(site,"gi")
      s = s.replace(regExp, imgStore[i].img)
    }
  }

  return s;
}
