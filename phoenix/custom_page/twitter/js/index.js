var EXP = 1000 * 60 * 60 * 24 * 10;
var user;
var unClickedUrl = "img/icon_like_small_normal.png";
var clickedUrl = "img/icon_like_small-highlight.png";
var apply_category_code;
var is_loop = true;
var notice_num = 0;
var imageItems = [];
var imageSwipeOptions = {};
var gallery = null;   // 图片放大
var loading = layer.open({type: 2})    // 加载层

$(".wrap").hide()    // 隐藏界面
// 是否有上次登录地址
$(function () {
    var xjlj_last_url = get('xjlj_last_url', EXP)
    var loginPageUrl = "register.html";
    if (xjlj_last_url && (xjlj_last_url.data.indexOf("openAccount") > 0 || xjlj_last_url.data.indexOf("reviewing") > 0)) {
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
          if (res.code === 0) {  
              clear('user')
              clear('xjlj_last_url')
              if (res.data.action === 0) {
                  setUser(res.data);
              } else if (res.data.action === 1) {
                  window.location.href = loginPageUrl;
              } else if (res.data.action === 30) {
                  window.location.replace(openAccountUrl + '?actionUrl=' + res.data.actionUrl);    
              } else if (res.data.action === 31) {
                  window.location.replace(openAccountReviewingUrl);
              } else {
                  window.location.replace(xjlj_last_url.data);
              }  
          } else {
              window.location.href = loginPageUrl;
          }
        },
        error: function () {
          window.location.href = loginPageUrl;
        }
      })
    } else {
      enterLastUrl()

      if (get('user', EXP)) {
        user = get('user', EXP);     
        // 获取通知数
        $.ajax({
          type: 'get',
          url: baseURL + '/api/v2/system-notice/list',
          dataType: 'json',
          headers: {
            "X-SessionId": localStorage.getItem("uc_sessionId")
          },
          xhrFields: {
            withCredentials: true
          },
          success: function (res) {
            if (res.code === 0) {
                notice_num = res.data.unread_count > 9 ? '...' : res.data.unread_count
                if (notice_num !== 0) {
                    $('.message-num').html(notice_num)
                    $('.message-num').css("display", "block")
                } else {
                    $('.message-num').css("display", "none")
                }
            } else {
              window.location.href = loginPageUrl;
            }
          },
          error: function () {
            // goLoginPage();
            window.location.href = loginPageUrl;
            console.log("网络超时");
          }
        })
        var user_icon = user.data.icon_url ? user.data.icon_url : "img/img_avatar_30pt.png";
        var user_mobile = user.data.mobile.substring(0, 3) + "****" + user.data.mobile.substring(7, 11)
        var userMessageHtml = '';
        userMessageHtml += '<img class="user-img" src="' +
          user_icon + '"><span class="phone-num">' +
          user_mobile + '</span><div class="icon-message"><img src="img/icon_Message_Default.png"><div class="message-num"></div></div><img class="signout" src="img/icon_Quit_Default.png">';
        $("#user-message").html(userMessageHtml);
      } else {
        window.location.href = loginPageUrl;
        // goLoginPage();
      }
      // 每20秒刷新动态
      setInterval(function(){
        rollTwitterList()
      }, 20*1000)

      getTwitterList()
    }

    $.ajax({
        type: 'get',
        url: baseURL + '/api/v2/ad/xj_banner',
        dataType: 'json',
        headers: {
          "X-SessionId": localStorage.getItem("uc_sessionId")
        },
        success: function (res) {
            if(res.code === 0){
                var swiperWrapperHtml = '';
                if (res.data.length === 1) {
                  is_loop = false;
                }
                res.data.forEach(function (item) {
                    swiperWrapperHtml += '<div class="swiper-slide"><a href="'
                        + item.jump_url + '"><img src="'
                        + item.poster_url + '"></a></div>'
                });
                $("#swiper-wrapper").html(swiperWrapperHtml);
                setSwiper();
            }
        },
        error: function (err) {
          console.log("网络超时");
        }
    })

    getTeaList();
})


function getTeaList() {
    $.ajax({
        type: 'get',
        url: baseURL + '/api/v2/category-group/xiangjianglunjian',
        dataType: 'json',
        xhrFields: {
            withCredentials: true
        },
        headers: {
          "X-SessionId": localStorage.getItem("uc_sessionId")
        },
        success: function (data) {
            if (data.code === 0) {
                var teaListHtml = '';
                data.data.category_list.forEach(function (item) {
                    var isActive;
                    var isFollow;
                    var isApplied = false;
                    var teacher_id;
                    var img_src = "img/img_avatar_30pt.png";

                    item.teacher_list.forEach(function (d) {
                      if (d.primary === 1) {
                        teacher_id = d.id;
                        img_src = d.icon_url ? d.icon_url : "img/img_avatar_30pt.png";
                        return;
                      }
                    })

                    if (item.follow === undefined) {
                        isActive = ' default';
                        isFollow = '<span class="plus">+</span>关注';
                    } else if (item.follow === 0) {
                        isApplied = true;
                        isActive = ' active';
                        isFollow = '审批中';
                    } else if (item.follow === 2) {
                        isActive = ' default';
                        isFollow = '<span class="plus">+</span>关注';
                        isApplied = true;
                    } else  if (item.follow === 1) {
                        isApplied = true;
                        isActive = ' active';
                        isFollow = '已关注';
                    }
                    teaListHtml += '<div class="tea"><input name="code" type="hidden" value="'
                        + item.code + '"><input name="isApplied" type="hidden" value="'
                        +isApplied + '"><div class="img-shadow"></div><a href="detail.html?category_code=' + item.code + '&teacher_id=' + teacher_id + '" class="teacher-img"><img src="'
                        + img_src + '"></a><div class="name">'
                        + item.name + '</div><div class="btn-follow'
                        + isActive + '">'
                        + isFollow + '</div></div>';
                });
                $("#tea-list").html(teaListHtml);
                $("#tea-list").css("width", data.data.category_list.length * 92 - 10)
                // 老师头像阴影效果
                $(".teacher-img").on("click", function(){
                  $(this).prev().css("display", "none")
                  setTimeout(function(){
                    $(this).prev().css("display", "block")
                  }, 300)
                })
            } else {
                console.log("请求错误");
            }
        },
        error: function () {
            console.log("网络超时");
            goLoginPage();
        }
    })
}

function getTwitterList() {
  $.ajax({
    type: 'get',
    url: baseURL + '/api/v2/twitter/list',
    dataType: 'json',
    xhrFields: {
      withCredentials: true
    },
    headers: {
      "X-SessionId": localStorage.getItem("uc_sessionId")
    },
    success: function (data) {
      if (data.code === 0) {
        layer.close(loading);
        if (data.data.twitter_list.length > 0) {
          $("#news-list").html();
          var newsListHtml = '<div class="no-read-twitter"></div><div class="new_message" id="new-message"><div class="message"><img src="img/img_Up.png"></div></div>';
          data.data.twitter_list.forEach(function (item) {
            var thumbs_up_state = false;
            var res_time = item.created_at.substr(0, 10);
            var now_time = getDate();
            var time = now_time === res_time ? "今天" + item.created_at.substr(10) : item.created_at;
            var clickedClass = item.like ? ' clicked' : ' unclicked';
            var clickedImg = item.like ? clickedUrl : unClickedUrl;
            var thumbs_up_num = item.like_count ? item.like_count : "点个赞吧";
            var img_src = "img/img_avatar_30pt.png";

            img_src = item.icon_url ? item.icon_url : "img/img_avatar_30pt.png";

            newsListHtml += '<div class="news"><div class="news-info"><a href="detail.html?category_code=' + item.category_code + '&teacher_id=' +
              item.teacher_id + '"><img src="' +
              img_src + '"></a><div class="name">' +
              item.category_name + '</div><div class="time">' +
              time + '</div></div><div class="news-content"><div class="image-text">' +
              ReplaceUrl(item.content) + '</td></tr></table></div><div class="thumbs-up' +
              clickedClass + '"><input type="hidden" value="' +
              item.like_count + '"><img src="' +
              clickedImg + '" onclick="twitterLike(' + item.id + ', this)"><div class="text">' +
              thumbs_up_num + '</div></div></div></div>';
          });
          $("#news-list").html(newsListHtml);
          // onImgLoad()
          $(".no-data").hide();
          $(".no-follow").hide();
          $(".news-list").show();
        } else {
          $(".no-follow").show();
          $(".no-data").hide();
          $(".news-list").hide();
        }
        $(".wrap").show()
        // 判断swiper是否为单张图片
        if ($(".swiper-slide").length === 1) {
          $(".swiper-container").addClass("swiper-no-swiping")
          $(".swiper-pagination").css("display", "none")
        }
      } else {
        goLoginPage();
        console.log("请求错误");
      }
    },
    error: function (data) {
      console.log("网络错误")
    }
  })
}

function rollTwitterList() {
  $.ajax({
    type: 'get',
    url: baseURL + '/api/v2/twitter/list',
    dataType: 'json',
    xhrFields: {
      withCredentials: true
    },
    headers: {
      "X-SessionId": localStorage.getItem("uc_sessionId")
    },
    success: function (data) {
      if (data.code === 0) {
        // 修改动态列表
        if (data.data.twitter_list.length > 0 && data.data.twitter_list.length > $(".news").length) {
            var noReadNews = data.data.twitter_list.length - $(".news").length
            $(".no-read-twitter").html('<img src="img/img_Up.png" alt="图片加载中。。。" width="10"> ' + noReadNews +'条新消息')
            $(".before-follow").hide();
            $(".no-read-twitter").show()
            if ($("#column").hasClass("fixed")) {
              $(".no-read-twitter").addClass('active')
            } else {
              $(".no-read-twitter").removeClass('active')
            }
        } else {
            console.log("无新动态发布")
            $(".dynamic .before-follow").hide();
            if ($("#column").hasClass("fixed")) {
              $(".no-read-twitter").addClass('active')
            } else {
              $(".no-read-twitter").removeClass('active')
            }
            $(".no-read-twitter").hide()
        }
        layer.close(loading);
      } else {
        goLoginPage();
        console.log("请求错误");
      }
    },
    error: function (data) {
      console.log("网络错误")
    }
  })
}

function setSwiper() {
    var mySwiper = new Swiper(".swiper-container", {
        loop: is_loop,
        autoplay:true,
        pagination: {
            el: '.swiper-pagination',
        },
        observer:true,//修改swiper自己或子元素时，自动初始化swiper
        observeParents:true,//修改swiper的父元素时，自动初始化swiper
    });
}

function getDate() {
    var date = new Date();//Tue Jul 16 01:07:00 CST 2013的时间对象
    var year = date.getFullYear();//年
    var month = date.getMonth() + 1;//月份（月份是从0~11，所以显示时要加1）
    month = month < 10 ? "0" + month : month;
    var day = date.getDate();//日期
    day = day < 10 ? "0" + day : day;
    return year + '-' + month + '-' + day;
}

$("#tea-list").on("click", ".btn-follow.default", function () {
    var modalTitleHtml = $(this).parent().find('input[name=isApplied]').val() === "true" ? "您的申请曾被拒绝过，确认要再次申请么？" : "确认申请关注这位大牛么？";
    apply_category_code = $(this).parent().find('input[name=code]').val();
    layer.open({
      content: modalTitleHtml,
      btn: ['确定', '取消'],
      yes: function (index) {
        var reqData = {
          "category_code": apply_category_code
        }
        $.ajax({
          type: 'post',
          url: baseURL + '/api/v2/twitter/request',
          dataType: 'json',
          data: reqData,
          headers: {
            "X-SessionId": localStorage.getItem("uc_sessionId"),
          },
          xhrFields: {
            withCredentials: true
          },
          success: function (data) {
            if (data.code === 0) {
              getTeaDetail();
            } else {
              console.log("请求错误");
            }
          },
          error: function (xhr) {
           
            if (xhr.status === 401) {
              goLoginPage();
            } else {
              console.log("网络超时");
            }
          }
        })
        layer.close(index)
      }
    });
});

function twitterLike(twitterId, that) {
  var parent = $(that).parent();
  if ($(parent).hasClass("unclicked")) {
    $.ajax({
      type: 'post',
      url: baseURL + '/api/v2/twitter/' + twitterId + '/like',
      dataType: 'json',
      xhrFields: {
        withCredentials: true
      },
      headers: {
        "X-SessionId": localStorage.getItem("uc_sessionId")
      },
      success: function (data) {
        if (data.code === 0 && data.data.like === 1) {
          $(parent).removeClass("unclicked");
          $(parent).addClass("clicked");
          $(that)[0].src = clickedUrl;
          var text = $(parent).find('.text')[0];
          $(text).html(parseInt($(parent).find("input").val()) + 1);
        } else {
          console.log('不可取消点赞')
        }
      },
      error: function () {
        console.log("网络超时");
        // goLoginPage();
      }
    })

  } else {
    console.log('不可取消点赞')
  }
}

// 消息
$("#user-message").on('click', '.icon-message', function () {
    $('.message-num').css("display", "none")
    window.location.href='message.html';
})

$("#user-message").on('click', '.signout', function () {
    layer.open({
      content: '是否确认要退出登录？',
      btn: ['确定', '取消'],
      yes: function (index) {
        logout()
      }
    })
})

$(".wrap").on("click", ".no-read-twitter", function () {
  $(".no-read-twitter").removeClass('active')
  $(".no-read-twitter").hide()
  getTwitterList()
})
