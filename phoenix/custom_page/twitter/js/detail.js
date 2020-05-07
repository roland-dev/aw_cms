var unClickedUrl = "img/icon_like_small_normal.png";
var clickedUrl = "img/icon_like_small-highlight.png";
var is_follow; // 用户是否关注当前老师
var twitter_follow // 用户关注当前老师动态权限
var before_follow; // 关注老师审核中
var letter_privilege; //用户是否具有和当前老师私信权限 0 审核中  1 成功 2 拒绝
var teacher_id = getUrlParam("teacher_id");
var apply_category_code = getUrlParam("category_code");
var user_image = "img/img_avatar_30pt.png";
var tea_image = "img/img_avatar_50pt.png";
var columnTop = $("#column").offset().top;
var userMessageHeight = $(".user-message")[0].offsetHeight;
var isLoadedLetter = false;
var isLoadedDynamic = false;
var letterInputState = false;
var notice_num = 0;
var imageItems = [];
var imageSwipeOptions = {};

var EXP = 1000 * 60 * 60 * 24 * 10;
var user;
var gallery = null;   // 图片放大

$(function () {
    // 保存最后登陆态
    set('xjlj_last_url', window.location.href)

    $.ajax({
      type: 'get',
      url: baseURL + '/api/v2/system-notice/list',
      dataType: 'json',
      xhrFields: {
        withCredentials: true
      },
      headers: {
        "X-SessionId": localStorage.getItem("uc_sessionId")
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
        }
      },
      error: function () {
        console.log("网络超时");
        goLoginPage();
      }
    })

    if (get('user', EXP)) {
        user = get('user', EXP);
        var user_icon = user.data.icon_url;
        user_image = user.data.icon_url;
        var user_mobile = user.data.mobile.substring(0, 3) + "****" + user.data.mobile.substring(7, 11);
        var userMessageHtml = '';
          userMessageHtml += '<img class="user-img" src="' +
            user_icon + '"><span class="phone-num">' +
            user_mobile + '</span><div class="icon-message"><img src="img/icon_Message_Default.png"><div class="message-num"></div></div><img class="signout" src="img/icon_Quit_Default.png">';
        
        $("#user-message").html(userMessageHtml);
    } else {
        // goLoginPage();
    }

    getTeaDetail();

    saveLastUrl();

    $(".no-read-twitter").on("click", function () {
      if ($("#column").hasClass("fixed")) {
        $(".no-read-twitter").addClass('active')
        var dyOffsetTop  = $(".dynamic").offset().top
        
        $(window).scrollTop($(".dynamic").offset().top - 84)
      } else {
        $(".no-read-twitter").removeClass('active')
      }
      $(".no-read-twitter").hide()
      getNewsList()
    })

    // 响应屏幕在唤起输入法时候的尺寸
    if(document.activeElement.tagName === 'INPUT'){
        document.activeElement.scrollIntoView({behavior: "smooth"})
    }
})

// 获取主页详情信息
function getTeaDetail() {
    $.ajax({
        type: 'get',
        url: baseURL + '/api/v2/category/' + getUrlParam("category_code") + '/info',
        dataType: 'json',
        xhrFields: {
            withCredentials: true
        },
        headers: {
          "X-SessionId": localStorage.getItem("uc_sessionId")
        },
        success: function (data) {
            if (data.code === 0) {
                var teaDetail = '';
                var categoryInfo = data.data;

                var isActive;
                var isFollow;
                var isApplied = false;

                var visitorVideoUrl, customerVideoUrl, coverUrl, videoHtml;

                is_follow = categoryInfo.follow === 1 ? true : false;
                before_follow = categoryInfo.follow === 0 ? true : false;
                categoryInfo.teacher_list.forEach(function (item) {
                    if (item.primary === 1) {
                        tea_image = item.icon_url === "" ? "img/img_avatar_30pt.png" : item.icon_url;

                        visitorVideoUrl = item.visitor_video_url
                        customerVideoUrl = item.customer_video_url
                        coverUrl = item.cover_url === "" ? "http://res.zhongyingtougu.com/cms/2018/0921/5ba4b860e183a.jpg" : item.cover_url
                        letter_privilege = item.private_message;
                    }
                })
              
                // 判断个人主页不同的关注状态
                if (categoryInfo.follow === undefined) {
                    isActive = ' default';
                    isFollow = '<span class="plus">+</span>关注';
                    $("#news-minirefresh").hide();
                    $("#no-data").hide();
                    $(".dynamic .before-follow").hide()
                    $(".dynamic .no-follow").show();
                    $(".remind").show();
                } else if (categoryInfo.follow === 0) {
                    isApplied = true;
                    isActive = ' active';
                    isFollow = '审批中';
                    $("#news-minirefresh").hide();
                    $("#no-data").hide();
                    $(".dynamic .no-follow").hide();
                    $(".dynamic .before-follow").show()
                    // TODO 审批中状态页
                } else if (categoryInfo.follow === 2) {
                    isActive = ' default';
                    isFollow = '<span class="plus">+</span>关注';
                    isApplied = true;
                    $("#news-minirefresh").hide();
                    $("#no-data").hide();
                    $(".dynamic .before-follow").hide()
                    $(".dynamic .no-follow").show();
                    $(".remind").hide();
                } else if (categoryInfo.follow === 1) {
                    isApplied = true;
                    isActive = ' active';
                    isFollow = '已关注';
                    getNewsList();
                    $(".dynamic .no-follow").hide();
                    $(".dynamic .before-follow").hide()
                    $(".remind").hide()
                }

                // 个人主页视频部分
                // 登录无视频
                if (is_follow && customerVideoUrl !== '') {
                    $("#videoShow").show()
                    $("#videoPoster").hide()
                    $("#video").attr("src", customerVideoUrl)
                    $("#video").attr("poster", coverUrl)
                } else if (!is_follow && visitorVideoUrl !== '') {
                    $("#videoShow").show()
                    $("#videoPoster").hide()
                    $("#video").attr("src", visitorVideoUrl)
                    $("#video").attr("poster", coverUrl)
                } else {
                    $("#videoShow").hide()
                    $("#videoPoster").show()
                    $("#videoPoster .cover-img").attr("src", coverUrl)
                }

                // 个人主页详情部分
                teaDetail += '<input name="id" type="hidden" value="' +
                    categoryInfo.id + '"><input name="isApplied" type="hidden" value="' +
                    isApplied + '"><img class="tea-img" src="' +
                    tea_image + '" onerror="this.onerror=null;this.src=\'img/img_avatar_30pt.png\'"><div class="btn-follow' +
                    isActive + '">' +
                    isFollow + '</div><div class="tea-detail"><span class="name">' +
                    categoryInfo.name + '</span><span class="follow-num">' +
                    categoryInfo.follow_count + '人关注</span></div>';
                $("#tea-detail").html(teaDetail);



            } else {
                console.log("请求错误");
            }
        },
        error: function () {
            console.log("网络超时");
            // goLoginPage();
        }
    })
}

rollLetterList()
rollTwitterList()

function getUrlParam(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");  // 构造一个含有目标参数的正则表达式对象
    var r = window.location.search.substr(1).match(reg)  // 匹配目标参数
    if (r != null) return unescape(r[2]);
    return null; //返回参数值
}

$(".column-item").click(function () {
    $(".column-item").removeClass('active');
    $(this).addClass("active");
    if ($(this).hasClass("dynamic-btn")) {
        showVideoAndTeaDetail();
        setTwitterState()
    }
    if ($(this).hasClass("letter-btn")) {
        setLetterPrivilegeState()
    }
});

function setTwitterState () {  
    $(".dynamic").show();
    $(".letter").hide();
    columnTop = $("#column").offset().top;
    $(window).scrollTop(columnTop - userMessageHeight);
    setColumnFixed();
    $(".letter-input").hide();
}

function setLetterPrivilegeState () {
    columnTop = 44;
    hideVideoAndTeaDetail();
    $(".dynamic").hide();
    $(".letter").show();
    switchLetterInput();
    if (letterInputState) {
        $(".letter-input").show();
    }
    // 已关注老师，获得动态权限
    if (is_follow) {
        // 已关注，有私信权限
        if (letter_privilege === 1) {
            getLetterList()
            $(".letter-input").show()
            $(".letter .no-privilege").hide()
            $(".letter .request-follow").hide()
            $(".letter .before-follow").hide()
        } else if (letter_privilege === 0) {
            $(".letter .no-privilege").hide()
            $(".letter .before-follow").hide()
            $(".letter .request-follow").show()
        } else {
            $(".letter .request-follow").hide()
            $(".letter .before-follow").hide()
            $(".letter .no-privilege").show()
        }
        $(".letter .no-follow").hide()
    } else {
        if (before_follow) {
          $(".letter .content").hide();
          $(".letter .no-message").hide();
          $(".letter .no-privilege").hide();
          $(".letter .no-follow").hide();
          $(".letter .before-follow").show();
        } else {
          $(".letter .content").hide();
          $(".letter .no-message").hide();
          $(".letter .no-privilege").hide();
          $(".letter .before-follow").hide();
          $(".letter .no-follow").show()
        }

    }
}

function hideVideoAndTeaDetail() {
    $(".video").hide();
    $(".top .tea-info").hide();
    $(".top .empty").hide();
    $(".top").css("height", "44px");
    $("#column").addClass('fixed');
    $(".no-read-twitter").addClass('active')
}

function showVideoAndTeaDetail() {
    $(".video").show();
    $(".top .tea-info").show();
    $(".top .empty").show();
    $(".top").css("height", "102px");
    $("#column").hasClass("fixed") && $("#column").removeClass("fixed");
    $(".no-read-twitter").hasClass('active') && $(".no-read-twitter").removeClass('active');
}

// 获取动态列表
function getNewsList() {
    $.ajax({
        type: 'get',
        url: baseURL + '/api/v2/twitter/list?category_code=' + getUrlParam("category_code"),
        dataType: 'json',
        xhrFields: {
            withCredentials: true
        },
        headers: {
          "X-SessionId": localStorage.getItem("uc_sessionId")
        },
        success: function (data) {
            if (data.code === 0) {
                isLoadedDynamic = true;
                if (data.data.twitter_list.length > 0) {
                    var newsContentHtml = '';
                    
                    data.data.twitter_list.forEach(function (item) {
                        var res_time = item.created_at.substr(0, 10);
                        var now_time = getDate();
                        var time = now_time === res_time ? "今天" + item.created_at.substr(10) : item.created_at;
                        var clickedClass = item.like ? ' clicked' : ' unclicked';
                        var clickedImg = item.like ? clickedUrl : unClickedUrl;
                        var thumbs_up_num = item.like_count ? item.like_count : "点个赞吧";
                        newsContentHtml += '<div class="news"><div class="time-node"><div class="node"></div><div class="time">' +
                            time + '</div></div><div class="news-content"><div class="image-text">' +
                            ReplaceUrl(item.content) + '</td></tr></table></div><div class="thumbs-up' +
                            clickedClass + '"><input type="hidden" value="' +
                            item.like_count + '"><img src="' +
                            clickedImg + '" onclick="twitterLike('+ item.id +',this)"><div class="text">' +
                            thumbs_up_num + '</div></div></div></div>';
                    })
                    $("#news_content").html(newsContentHtml);
                    $("#news-minirefresh").show();
                    $("#no-data").hide();
                    if ($("#column").hasClass("fixed")) {
                      $(window).scrollTop($(".dynamic").offset().top - 84)
                    } 
                } else {
                    $("#news-minirefresh").hide();
                    $("#no-data").show();
                }
            } else {
                console.log("请求错误");
            }
        },
        error: function () {
            console.log("网络超时");
            // goLoginPage();
        }
    })
}

function getLetterList() {
    $(".red-dot").css("display", "none")
    $.ajax({
        type: 'get',
        url: baseURL + '/api/v2/private-message/list?teacher_id=' + teacher_id,
        dataType: 'json',
        xhrFields: {
            withCredentials: true
        },
        headers: {
          "X-SessionId": localStorage.getItem("uc_sessionId")
        },
        success: function (data) {
            if (data.code === 0) {
                isLoadedLetter = true;
                letter_privilege = data.data.private_message_follow
                if (data.data.private_message_list.length > 0) {
                    var letterContentHtml = '';
                    var previousItemTime = '';
                    data.data.private_message_list.forEach(function (item) {
                        if (item.direction === 1 && item.read === 0) {
                            $.ajax({
                              type: 'put',
                              url: baseURL + '/api/v2/private-message/' + item.id + '/read',
                              xhrFields: {
                                withCredentials: true
                              },
                              headers: {
                                "X-SessionId": localStorage.getItem("uc_sessionId")
                              },
                              success: function (data) {
                                console.log(data);
                              },
                              error: function (err) {
                                console.log(err);
                              }
                            })
                        }
                        var itemTimeHtml = '';
                        var letterClass = item.direction === 0 ? " right" : " left";
                        var res_year = item.created_at.substr(0, 4);
                        var now_year = getNowYear();
                        var formatTime = getFormatTime(item.created_at);
                        var time = now_year === res_year ? formatTime.substr(5) : formatTime;
                        if (time !== previousItemTime) {
                            itemTimeHtml = '<div class="time">' + time + '</div>';
                            previousItemTime = time;
                        } else {
                            itemTimeHtml = '<div class="time empty"></div>';
                        }

                        var form_user_img = item.direction === 0 ? user_image : tea_image;
                        letterContentHtml += '<div class="dialogue' +
                            letterClass + '">' +
                            itemTimeHtml + '<div class="message"><img src="' +
                            form_user_img + '" class="portrait"><div class="image-text">' +
                            item.content + '</div></div></div>';
                    })
                    $(".letter .content").html(letterContentHtml);
                    $(".letter .content").show();
                    $(".letter .no-message").hide();
                    scrollToEnd();
                } else {
                    $(".letter .content").hide();
                    $(".letter .no-message").show();
                }
            } else {
                console.log("请求错误");
            }
        },
        error: function () {
            console.log("网络超时");
        }
    })
}

// 循环请求查看有新私信内容发送过来
function rollLetterList() {
  setInterval(function(){
    $.ajax({
      type: 'get',
      url: baseURL + '/api/v2/private-message/list?teacher_id=' + teacher_id,
      dataType: 'json',
      xhrFields: {
        withCredentials: true
      },
      headers: {
        "X-SessionId": localStorage.getItem("uc_sessionId")
      },
      success: function (data) {
        if (data.code === 0) {
          if (letter_privilege !== data.data.private_message_follow) {
            letter_privilege = data.data.private_message_follow
            setLetterPrivilegeState()
          }
          data.data.private_message_list.forEach(function (item) {
            if (item.direction === 1 && item.read === 0) {
              $(".red-dot").css("display", "block")  
            }
          })
        }
      },
      error: function () {
        console.log("网络超时");
      }
    })
  }, 10*1000) 
}

// 循环请求查看有新私信内容发送过来
function rollTwitterList() {
  setInterval(function(){
    $.ajax({
        type: 'get',
        url: baseURL + '/api/v2/twitter/list?category_code=' + getUrlParam("category_code"),
        dataType: 'json',
        xhrFields: {
            withCredentials: true
        },
        headers: {
          "X-SessionId": localStorage.getItem("uc_sessionId")
        },
        success: function (data) {
            if (data.code === 0) {
                if (before_follow === true) {
                  setTwitterState()
                  before_follow = false
                } 
                if (twitter_follow !== data.data.twitter_follow && $(".news").length === 0) {
                  getTeaDetail();
                } else {
                   if (data.data.twitter_list.length > 0 && data.data.twitter_list.length > $(".news").length) {
                      var noReadNews = data.data.twitter_list.length - $(".news").length
                      $(".no-read-twitter").html('<img src="img/img_Up.png" alt="图片加载中。。。" width="10"> ' + noReadNews +'条新消息')
                      $(".dynamic .before-follow").hide();
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
                }
            } else {
                getTeaDetail();
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
  }, 20*1000)
}

function getDate() {
    var date = new Date(); //Tue Jul 16 01:07:00 CST 2013的时间对象
    var year = date.getFullYear(); //年
    var month = date.getMonth() + 1; //月份（月份是从0~11，所以显示时要加1）
    month = month < 10 ? "0" + month : month;
    var day = date.getDate(); //日期
    return year + '-' + month + '-' + day;
}

function getNowYear() {
    return new Date().getFullYear();
}

function getFormatTime(time) {
    var showDate = time.substring(0, 10);
    if (new Date(showDate).toDateString() === new Date().toDateString()) {
        showDate = '今天' + time.substring(10, 19)
    } else {
        var year = time.substr(0, 4);
        var month = time.substr(5, 2);
        var day = time.substr(8, 2);
        showDate = year + '年 ' + month + '月' + day + '日 ' + time.substr(11);
    }   
    return showDate
}

$("#video").on("play", function () {
    videoPaly();
});

$("#video").on("pause", function () {
    videoPause();
});

$("#video").on("ended", function () {
    videoPause();
})

$(".video .img-play").click(function () {
    $("#video")[0].play();
});

$(".video .img-suspend").click(function () {
    $("#video")[0].pause();
});

$(function () {
    $(".video").click(function () {
        if ($(".video .img-play").css("display") == "none") {
            $(".video .img-suspend").show();
            setTimeout(function () {
                $(".video .img-suspend").hide();
            }, 2000);
        }
    });
});

function videoPaly() {
    $(".video .img-play").hide();
    $(".video .img-suspend").show();
    $(".tea-info").css('animation', 'teamove 0.5s 1');
    $(".tea-info").css('-webkit-animation', 'teamove 0.5s 1');
    $(".top").css('animation', 'topmove 0.5s 1');
    $(".top").css('-webkit-animation', 'topmove 0.5s 1');
    $(".top .empty").css('animation', 'emptymove 0.5s 1');
    $(".top .empty").css('-webkit-animation', 'emptymove 0.5s 1');
    setTimeout(function () {
        $(".top").addClass("active");
    }, 500);
    setTimeout(function () {
        $(".video .img-suspend").hide();
    }, 2000);
}

function videoPause() {
    $(".video .img-suspend").hide();
    $(".video .img-play").show();
    $(".tea-info").css('animation', 'teamove1 0.5s 1');
    $(".tea-info").css('-webkit-animation', 'teamove1 0.5s 1');
    $(".top").css('animation', 'topmove1 0.5s 1');
    $(".top").css('-webkit-animation', 'topmove1 0.5s 1');
    $(".top .empty").css('animation', 'emptymove1 0.5s 1');
    $(".top .empty").css('-webkit-animation', 'emptymove1 0.5s 1');
    $(".top").removeClass("active");
}

$("#tea-detail").on("click", ".btn-follow.default", function () {
    var modalTitleHtml = $(this).parent().find('input[name=isApplied]').val() === "true" ? "您的申请曾被拒绝过，确认要再次申请么？" : "确认申请关注这位大牛么？";
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



function twitterLike (twitterId, that) {
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
                console.log(data);
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

$(window).scroll(function () {
    setColumnFixed();
    // $(".letter-input .text-input").blur();
})

function setColumnFixed() {
    var scorllTop = $(window).scrollTop(); //滚动条距离顶部的高度
    if (scorllTop + userMessageHeight >= columnTop) {
        $("#column").addClass('fixed');
        $(".no-read-twitter").addClass('active')
    } else {
        $("#column").hasClass("fixed") && $("#column").removeClass("fixed");
        $(".no-read-twitter").hasClass("active") && $(".no-read-twitter").removeClass("active");
    }
}

$("#letterInput").live('input propertychange', function () {
  if ($(this).val() !== '') {
    $(".letter-input-btn")[0].src = "img/icon_ChatBar_send_Pressed.png";
    $(".letter-input-btn").addClass("img_type");
    scrollToEnd();
  }
})

$(".letter-input-btn").click(function () {
    if (!$(this).hasClass("img_type")) {
        $(this)[0].src = "img/icon_ChatBar_send_Pressed.png";
        $(this).addClass("img_type");
        $(".letter-input .input-img").show();
        $(".letter").css("margin-bottom", "169px");
        scrollToEnd();
    } else {
      $.ajax({
        type: "POST",
        url: baseURL + '/api/v2/private-message',
        dataType: 'json',
        data: {
          'content': $("#letterInput").val(),
          'teacher_id': teacher_id
        },
        xhrFields: {
          withCredentials: true
        },
        headers: {
          "X-SessionId": localStorage.getItem("uc_sessionId")
        },
        success: function (res) {
          if (res.code === 0) {
            $("#letterInput").val("")
            getLetterList()
          }
        }
      });
    }
});

$(".letter-input .text-input").focus(function () {
    switchLetterInput();
});

$(".letter .content").click(function () {
    switchLetterInput();
})

function switchLetterInput() {
    if ($("#letterInput").val() === '') {
        $(".letter-input-btn")[0].src = "img/icon_ChatBar_add_Default.png";
        $(".letter-input-btn").removeClass("img_type");
        $(".letter-input .input-img").hide();
        $(".letter").css("margin-bottom", "48px");
    } else {
        $(".letter-input-btn")[0].src = "img/icon_ChatBar_send_Pressed.png";
        $(".letter-input-btn").addClass("img_type");
    }
    scrollToEnd()
}


function scrollToEnd() { //滚动到底部
    setTimeout(function () {
        // var h = $(document).height() - $(window).height();
        // $(window).scrollTop(h);
        document.body.scrollTop = document.body.scrollHeight;
    }, 500);
}

$(".input-img img").click(function () {
    var item = $(this).parent();
    // $(".letter-input .text-input").focus();
    $(item).find("input").click();

    // console.log($(item).find("input").val())
});

$(".img-close").click(function () {
    var parent = $(this).parent();
    $(parent).css('animation', 'closemove 0.5s 1');
    $(parent).css('-webkit-animation', 'closemove 0.5s 1');
    $(".dynamic .no-follow").css('padding-top', '66px');
    setTimeout(function () {
        $(parent).hide();
    }, 500);
});

$(".dynamic .new_message .message").click(function () {
    $(this).css("animation", "messagemove 0.6s 1");
    $(this).css("-webkit-animation", "messagemove 0.6s 1");
    miniRefresh.triggerDownLoading();
    setTimeout(function () {
        $(".dynamic .new_message .message").hide();
    }, 500);
})

var miniRefresh = new MiniRefresh({
    container: '#news-minirefresh',
    down: {
        offset: 41,
        callback: function () {
            getNewsList()
            setTimeout(function () {
                miniRefresh.endDownLoading();
            }, 1000)
        }
    },
    up: {
        isAuto: true,
        offset: 41,
        callback: function () {
            setTimeout(function () {
                miniRefresh.endUpLoading(true);
            }, 1000)
        }
    }
})

// 私信功能申请
$("#no-privilege .text .btn").click(function() {
    var reqData = {
        "teacher_id": teacher_id
    }
    $.ajax({
      type: 'get',
      url: baseURL + '/api/v2/private-message/request',
      dataType: 'json',
      data: reqData,
      xhrFields: {
        withCredentials: true
      },
      headers: {
        "X-SessionId": localStorage.getItem("uc_sessionId")
      },
      success: function (data) {
        if (data.code === 0 && data.data.private_message_guard.status === 0) {
          layer.open({
            content: '私聊申请正在审核中……',
            btn: '我知道了'
          });
        } else {
          layer.open({
            content: '确认申请开通与该牛人的私聊吗？',
            btn: ['确定', '取消'],
            yes: function (index) {
              $.ajax({
                type: 'post',
                url: baseURL + '/api/v2/private-message/request',
                dataType: 'json',
                data: reqData,
                xhrFields: {
                  withCredentials: true
                },
                headers: {
                  "X-SessionId": localStorage.getItem("uc_sessionId")
                },
                success: function (data) {
                  if (data.code === 0) {
                    letter_privilege = 0
                    setLetterPrivilegeState()
                  } else {
                    letter_privilege = 2
                    console.log("请求错误");
                  }
                },
                error: function () {
                  console.log("网络超时");
                  // goLoginPage();
                }
              })
              layer.close(index);
            }
          });
        }
      },
      errror: function (data) {
        console.log(data)
      }
    })
});

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
    });
})

// 选择照片上传图片
$('#sendPhoto').on("change", function (e) {
  console.log($(this)[0].files)
  if (!/\.(gif|jpg|jpeg|png|bmp|GIF|JPG|PNG)$/.test($(this)[0].value)) {
    $(this)[0].value = ''
    layer.open({
      content: '不可以上传非图片类文件!',
      btn: '确认'
    });
    return false
  }
  var obj = $(this)[0].files[0]
  var formData = new FormData()
  formData.append('image', obj)
  console.log(formData)
  $.ajax({
    url: baseURL + '/api/v2/resource/image',
    type: 'POST',
    processData: false,
    contentType: false,
    data: formData,
    success: function (res) {
      if (res.code === 0) {
        var imgPath = res.data.path;
        $.ajax({
          type: "POST",
          url: baseURL + '/api/v2/private-message',
          dataType: 'json',
          data: {
            'content': '<img src=' + imgPath + '>',
            'teacher_id': teacher_id
          },
          xhrFields: {
            withCredentials: true
          },
          headers: {
            "X-SessionId": localStorage.getItem("uc_sessionId")
          },
          success: function (d) {
            if (d.code === 0) {
              getLetterList()
            }
          }
        });
      }
    },
    error: function (err) {
      console.log("网络错误")
    }
  })
})

// 选择相机上传图片
$('#sendCamera').on("change", function () {
  if (!/\.(gif|jpg|jpeg|png|bmp|GIF|JPG|PNG)$/.test($(this)[0].value)) {
    $(this)[0].value = ''
    layer.open({
      content: '不可以上传非图片类文件!',
      btn: '确认'
    });
    return false
  }
  var obj = $(this)[0].files[0]
  console.log(obj)
  var formData = new FormData()
  formData.append('image', obj)
  $.ajax({
    url: baseURL + '/api/v2/resource/image',
    type:'POST',
    processData:false,
    contentType: false,
    data:formData,
    success:function (res) {
      if (res.code === 0) {
         var imgPath = res.data.path;
         $.ajax({
           type: "POST",
           url: baseURL + '/api/v2/private-message',
           dataType: 'json',
           data: {
             'content': '<img src=' + imgPath + '>',
             'teacher_id': teacher_id
           },
           xhrFields: {
             withCredentials: true
           },
           headers: {
             "X-SessionId": localStorage.getItem("uc_sessionId")
           },
           success: function (d) {
             if (d.code === 0) {
               getLetterList()
             }
           }
         });
      }
    },
    error:function (err) {
      console.log("网络错误")
    }
  })
})

