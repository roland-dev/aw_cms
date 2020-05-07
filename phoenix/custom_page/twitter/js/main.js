var loginPageUrl = "register.html";
var openAccountUrl = "openAccount.html"
var openAccountReviewingUrl = "reviewing.html"

// 登录
function setUser(data) {
  var userData = {
    "session_id": data.sessionId,
    "channel": "hk"
  }

  // 存储sessionId到localStorage
  localStorage.setItem("uc_sessionId", data.sessionId)
  $.ajax({
    type: "post",
    url: baseURL + "/api/v2/customer/login",
    headers: {
      "X-SessionId": data.sessionId ,
    },
    xhrFields: {
      withCredentials: true // 跨域请求
    },
    dataType: "json",
    data: userData, //连同请求发送到服务器的数据。
    success: function (res) {
      console.log("已登录")
      set('user', res.data.customer)
      // 跳转开户页面      
      if (data.action === 30) {
        clear('user')
        window.location.replace(openAccountUrl + '?actionUrl=' + data.actionUrl)    
      // 开户审核中        
      } else if (data.action === 31) {
        clear('user')
        window.location.replace(openAccountReviewingUrl)
      } else { 
        goLoginCallBackUrl();
      }     
    }
  });
}

// 退出登录
function logout() {
  $.ajax({
    type: "post",
    url: baseURL + "/api/v2/customer/logout",
    xhrFields: {
      withCredentials: true // 跨域请求
    },
    dataType: "json",
    success: function (data) {
      if (data.code === 0) {
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
              clear('user');
              localStorage.removeItem("uc_sessionId")
              set('login_callBackUrl', window.location.href);
              setTimeout(function(){
                window.location.replace(loginPageUrl);
              }, 300);
            }
          },
          error: function (data) {
            console.log("网络超时");
            goLoginPage();
          }
        })
      }
    },
    error: function (data) {
      console.log("网络超时");
      goLoginPage();
    }
  });
}


// 封装过期控制代码
function set(key, value) {
  var curTime = new Date().getTime();
  localStorage.setItem(key, JSON.stringify({
    data: value,
    time: curTime
  }));
}

function get(key, exp) {
  var data = localStorage.getItem(key);
  if (!data) {
    return false;
  }
  var dataObj = JSON.parse(data);
  if (new Date().getTime() - dataObj.time > exp) {
    console.log('信息已过期');
  } else {
    return dataObj;
  }
}
// 清除user数据
function clear(key) {
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
  var r = window.location.search.substr(1).match(reg); //匹配目标参数
  if (r != null) return unescape(r[2]);
  return null; //返回参数值
};

function refresh() {
  window.history.go(0);
}

// 处理iframe引用视频尺寸问题
function fitIframe() {
  var iframes = $("#article iframe")
  if (iframes.length > 0) {
    var iframeWidth = $("#article").width() * 0.66;
    iframes.css({
      "width": "100%",
      "height": iframeWidth + "px"
    })
  }
}

// 保存手机号
function saveMobile(mobile) {
  localStorage.setItem("rfzq_mobile", mobile);
}

// 写入手机号
function writeMobile() {
  var mobile = localStorage.getItem("rfzq_mobile");
  if (mobile) {
    return mobile;
  } else {
    return false;
  }
}

// 过滤阅读数
function formatReadNum(num) {
  if (num > 9999) {
    num = num / 10000
    num = num.toFixed(1)
    return num + "万";
  } else {
    return num
  }
}

// 过滤点赞数
function formatLikeNum(num) {
  if (num > 999) {
    num = "999+";
  }
  return num;
}

// 文章分类区别
function adaptTab(tabs, screenWidth) {
  var tabsWidth = $(".tabs").width()
  var minWidth = 120 // 最小宽度
  console.log(tabs)
  var tabTotalWidth = tabs.length * minWidth
  if (tabsWidth >= tabTotalWidth) {
    $('.tabs').width(tabTotalWidth + 'px')
  } else {
    // 平均分配
    $('.tabs').addClass('flex')
  }
}

// 保存当前页面，如果没有则跳转首页
var disalbePushStateMonitor = false;
function saveLastUrl() {
  //设置香江论剑lastVisit标识
  set('xjlj_last_url', window.location.href)

  // 监测回退时间，如是回退到列表页，则清除lastVisit标识，避免循环跳转
  window.history.pushState(null, null, window.location.href);
  window.addEventListener('popstate', function (e) {
    if (disalbePushStateMonitor) return false;
    clear('xjlj_last_url')
    window.history.back();
  });
}

function enterLastUrl() {
  //设置香江论剑lastVisit标识
  var lastURL = get('xjlj_last_url', 1800000)

  if (lastURL && lastURL.data) {
    //跳转前设置详情页的返回目标页
    window.history.pushState(null, null, window.location.href);
    window.location.href = lastURL.data;
  } else {
    clear('xjlj_last_url')
  }
}

function goLoginPage() {
  // 设置登录前url
  set('login_callBackUrl', window.location.href);
  // // 自动登录
  var sessionMeData = '{"bizName": "rfzq"}';
  $.ajax({
    type: 'post',
    url: UC_API_URL + '/rfzq/session/me',
    contentType: "application/json",
    dataType: 'json',
    async: false,
    data: sessionMeData,
    xhrFields: {
      withCredentials: true
    },
    success: function (res) {
      if (res.code === 0 && res.data.action === 0) {    
          setUser(res.data);      
      } else {
          window.location.href = loginPageUrl;
      }
    },
    error: function () {
      window.location.href = loginPageUrl;
    }
  })
}

// 登录页返回地址
function goLoginCallBackUrl() {
  // window.history.back(-1);
  var loginCallBackUrl = get('login_callBackUrl', 1800000); 
  clear('login_callBackUrl')
  if (loginCallBackUrl.data && (loginCallBackUrl.data.indexOf("openAccount") < 0 || loginCallBackUrl.data.indexOf("reviewing") < 0)) {
    window.location.replace(loginCallBackUrl.data)
  } else {
    // 返回首页
    window.location.replace("index.html");
  }
}

$.fn.ImgZoomIn = function () {
  // 获取屏幕高度
  var window_h = $(window).height();
  var scroll_h = $(window).scrollTop();

  bgstr = '<div id="ImgZoomInBG" style="position: fixed;filter:Alpha(Opacity=70); opacity:0.7;z-index: 10000;background-color: #000;display: none;"></div>';
  imgstr = '<img id="ImgZoomInImage" src="' + $(this).attr('src') + '" style="cursor:pointer; display:none; position:fixed; z-index:10001;" />';
  if ($('#ImgZoomInBG').length < 1) {
    $('body').append(bgstr);
  }
  if ($('#ImgZoomInImage').length < 1) {
    $('body').append(imgstr);
  } else {
    $('#ImgZoomInImage').attr('src', $(this).attr('src'));
  }

  $('#ImgZoomInBG').css({'top': 0, 'bottom': 0, 'left': 0, 'right': 0});

  $('#ImgZoomInBG').css('width', '100%');
  $('#ImgZoomInBG').css('height', window_h + 'px');
  $('#ImgZoomInBG').bind("touchmove", function (e) {
    e.preventDefault();
  })

  $('#ImgZoomInImage').css('width', '100%');
  $('#ImgZoomInImage').css('height', (window_h / 2) + 'px');
  $('#ImgZoomInImage').css('top', window_h / 4 + 'px');
  console.log(window_h, scroll_h)

  $('#ImgZoomInBG').bind("touchmove", function (e) {
    e.preventDefault();
  })

  $('#ImgZoomInBG').show();
  $('#ImgZoomInImage').show();
};

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
    if (a.indexOf("http") < 0) {
      return false
    } else {
      return '<a href="' + a + '" target=_blank>' + a + '</a>'
    }
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
