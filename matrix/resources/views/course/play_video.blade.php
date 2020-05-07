<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0" />
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <meta name="format-detection" content="telephone=no">
  <meta name="referrer" content="no-referrer" />
  <title>{{ $data['course']['title'] }}</title>
  <link rel="stylesheet" href="{{config('app.url_storage').'/assets/ugc/css/reset.css'}}?v=20190420">
  <link rel="stylesheet" href="{{config('app.url_storage').'/assets/ugc/css/reply.css'}}?v=20191127">
  <link rel="stylesheet" href="{{config('app.url_storage').'/assets/ugc/css/forward.css'}}?v=20190422">
  <style>
    .tabs {
      display: flex;
      height: 40px;
      width: 100%;
      line-height: 40px;
      text-align: center;
      font-size: 14px;
      color: #333;
      background: #fff;
      border-bottom: 1px solid #e2e2e2;
      z-index: 99;
    }

    .tabs div {
      flex: 1;
      position: relative;
    }

    .tabs div .rect {
      display: none;
      width: 20px;
      height: 3px;
      background: #FA3D41;
      position: absolute;
      left: calc(50% - 10px);
      bottom: 0;
    }

    .tabs div.select {
      font-size: 16px;
      color: #FA3D41;
    }

    .tabs div.select .rect {
      display: block;
    }

    .conts li {
      display: none;
      min-height: 200px;
      width: 100%;
      font-size: 15px;
    }

    .conts li.show {
      display: block;
    }

    .conts .title {
      padding: 16px 15px;
      font-size: 20px;
      line-height: 28px;
      color: #333;
    }

    .conts .title span {
      font-size: 13px;
      line-height: 18px;
      color: #999;
      margin-left: 8px;
    }

    .conts .cont-divider {
      height: 10px;
      background: #F8F8F8;
    }

    .conts .cont {
      padding: 16px 15px;
      font-size: 15px;
      line-height: 21px;
    }

    .conts .cont .info {
      margin-top: 4px;
    }

    .conts .cont .info img{
      max-width: 100%;
    }

    .conts .cont-empty {
      text-align: center;
    }

    .conts .cont-empty img {
      width: 120px;
      text-align: center;
      margin-top: 40px;
    }

    .conts .cont-empty .tip {
      margin-top: 17px;
      color: #999;
      background: #fff;
      font-size: 14px;
    }

    .conts .course-item {
      padding: 15px 15px 15px 15px;
      background-size: 18px 12px;
      line-height: 21px;
    }

    .conts .course-item.select {
      color: #fa3d41;
      background:#FFF5F5;
    }

    .conts .course-item img{
      width: 18px;
      margin-right: 8px;
    }

    .reply-title {
      display: none;
      margin-top: 30px;
    }

    .reply-empty {
      margin-top: 60px;
    }


    @media screen and (min-width: 1000px){ 
      .layout{
        display: flex;
        width: 100vw;
        height: 100vh;
        overflow: hidden;
      }
      .wrap{
        flex: 1;
        max-width: 1920px;
        width: calc(100% - 360px);
        margin: 0;
      }
      .right-area{
        width: 365px;
        height: 100%;
        overflow-y: auto;
        overflow-x: hidden;
        border-left: 5px solid #eee;
      }
      /* 自定义滚动条 */
      .right-area::-webkit-scrollbar {
        width: 6px;
      }

      .right-area::-webkit-scrollbar-track {
        /*滚动条里面轨道*/
        border-radius: 50px;
        background: #e6e6e6;
      }

      .right-area::-webkit-scrollbar-thumb {
        /*滚动条里面小方块*/
        border-radius: 50px;
        background: #d0d0d0;
      }
      #zytg_player{       
        position: relative;
        width: 100%;
        height: 100%;
      }
      #zytg_player video{
        height: 100%;
      }
      .reply-bottom{
        width: 360px;
        left: auto;
        right: 0;
      }

      .reply-bottom {
        left: auto;
        right: 0;
      }
    }

  </style>
  <script type="text/javascript" src="{{config('app.url_storage').'/assets/ugc/js/jquery-2.1.1.min.js'}}"></script>
  <script src="{{config('app.url_storage').'/assets/ugc/js/layer.js'}}"></script>
</head>

<body>
  <div class="layout">
    <div class="wrap" style="">
      <!-- video_type 0 腾讯视频 1 展示互动 2 微吼 3 其他视频-->
      <div id="zytg_player">
        @if ( $data['course']['video_type'] === 0)
        @component('component.video', [ 'id' => $data['course']['video_id'], 'vkey' => $data['course']['video_key']])
        @endcomponent
        @elseif ( $data['course']['video_type'] === 1 )
        @component('component.gensee', [ 'id' => $data['course']['video_id'], 'posterUrl' => $data['course']['poster_url'], 'vkey' => $data['course']['video_key']])
        @endcomponent
        @elseif ( $data['course']['video_type'] === 2 )
        @component('component.vhall', [ 'id' => $data['course']['video_id'], 'title' =>$data['course']['title'], 'type' => 'course', 'vkey' => $data['course']['video_key']])
        @endcomponent
        @else
        <p class="video" style="position: relative; padding-bottom: 62.5%; height: 0; overflow: hidden;">
          <iframe src="{{ $data['course']['url'] }}" frameborder="0" frameborder="0" width="100%"
            allowfullscreen="allowfullscreen" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"
            id="zytg_player"></iframe>
        </p>
        @endif
      </div>
    </div>
    <div class="right-area">
      <!-- 课程详情及评论区域 -->
      <div class="section-top slider-box">
        <div class="tabs" id="courseTabs">
          <div class="tab">课程介绍<div class="rect"></div>
          </div>
          <div class="tab">课程目录<div class="rect"></div>
          </div>
          <div class="tab select">精彩评论<div class="rect"></div>
          </div>
        </div>
        <ul class="conts">
          <li class="cont-li">
            <div class="title">{{ $data['course']['course_name'] }}<span>{{ $data['course']['title'] }}</span></div>
            <div class="cont-divider"></div>
            <div class="cont">
              @if($data['course']['course_description'])
              <b>课程介绍</b>
              <div class="info" id="courseInfo">{{ $data['course']['course_description'] }}</div>
              @else
              <div class="cont-empty">
                <div><img src="{{config('app.url_storage').'/assets/ugc/images/Empty.png'}}" alt=""></div>
                <div class="tip">这里空空如也，先去别处逛逛吧</div>
              </div>
              @endif
            </div>
          </li>
          <li class="cont-li">
            <div class="title">{{ $data['course']['course_name'] }}<span>{{ $data['course']['title'] }}</span></div>
            <div class="cont-divider"></div>
            <div class="course-list">
            </div>
            <div class="cont-empty">
            </div>
          </li>
          <li class="cont-li show" style="min-height: 0"></li>
        </ul>
      </div>
      @component('component.reply', [ 'id' => $data['course']['video_id'], 'sessionId' => $data['course']['session_id'], 'type'
      => 'course', 'articleId' => $data['course']['video_key'], 'isTeacher' =>
      $data['course']['is_forward_teacher'],'articleTitle' => $data['course']['title'], 'articleAuthorUserId' =>
      $data['course']['author'], 'forwardTeacherId' => $data['course']['forward_open_id'], 'isReply' =>
      $data['course']['is_reply'], 'isLike' => $data['course']['is_like'], 'likeSum' => $data['course']['like_sum'], 'forwardImg' => $data['course']['poster_url']])
      @endcomponent
    </div>
  @component('component.wxshare', ['articleTitle' =>$data['course']['title'], 'desc' => $data['course']['course_description']])
  @endcomponent
  </div>
  <script type="text/javascript" src="{{config('app.url_storage').'/assets/ugc/js/previewImage.js'}}"></script>
  <script src="//res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
  <script src="{{config('app.url_storage').'/assets/ugc/js/md5.js'}}"></script>
  <script type="text/javascript">
    //获取要定位元素距离浏览器顶部的距离 
    var sliderBoxH = $(".conts").offset().top;  
    var cryptonymKey;
    var likeNum = "{{ $data['course']['like_sum'] }}";
    var baseURL = "{{ config('app.ajax_url') }}";

    $(function () {
      $("#courseInfo").html($("#courseInfo").text())
      if (localStorage.getItem("zytg_cryptonym_key")) {
        cryptonymKey = localStorage.getItem("zytg_cryptonym_key")
      } else {
        getCryptonymKey()
      }

      if (getUrlParam("tab") == 2) {
        $(".reply").hide()
        $('.tab').eq(1).addClass("select").siblings().removeClass('select');
        $('.cont-li').eq(1).show().siblings().hide();
        $(".reply-bottom").hide()
      } else {
        getReply()
        $('.tab').eq(2).addClass("select").siblings().removeClass('select');
        $('.cont-li').eq(2).show().siblings().hide();
      }

      getLikeNum()
      getCourseList()

      // 用户点赞功能
      $('.layout').on("click", ".like", function () {
        clickLike()
      })

      //滚动条事件  
      $(window).scroll(function(){  
        //获取滚动条的滑动距离  
        var scroH = $(this).scrollTop();  
        //滚动条的滑动距离大于等于定位元素距离浏览器顶部的距离，就固定，反之就不固定  
        if(scroH >= sliderBoxH){  
          $(".tabs").css({"position":"fixed","top":0});  
        }else if(scroH<sliderBoxH){  
          $(".tabs").css({"position":"static"});    
        }
      }) 

      $('.tabs div').click(function () {
        var i = $(this).index();
        $(this).addClass('select').siblings().removeClass('select');
        $('.conts li').eq(i).show().siblings().hide();
        if (i == 2) {
          // $(".reply").show()
          $(".reply-bottom").show()
          getReply()
        } else {
          $(".reply").hide()
          $(".reply-bottom").hide()
        }
      });

      $('.course-list .course-item').click(function () {
        var i = $(this).index();
        $(this).addClass('select').siblings().removeClass('select');
      })
    })

    // 获取课程列表
    function getCourseList() {
      $.ajax({
        type: "get",
        url: baseURL + "/api/v2/client/course/list/{{ $data['course']['course_code'] }}",
        dataType: "json",
        xhrFields: {
          withCredentials: true
        },
        @if($data['course']['session_id'])
        headers: {
          "X-SessionId": "{{ $data['course']['session_id'] }}",
        },
        @endif
        success: function (res) {
          if (res.code == 0) {
            var courseListHtml = "";
            for (var i = 0; i < res.data.length; i++) {
              if (res.data[i].detail_id == "{{ $data['course']['video_key'] }}") {
                courseListHtml += '<div class="course-item select" onclick="goCourseUrl(\'' + res.data[i]
                  .detail_id + '\')"><img src="{{config('app.url_storage').'/assets/ugc/images/Video.png'}}">' + res.data[i].title + '</div>'
              } else {
                courseListHtml += '<div class="course-item" onclick="goCourseUrl(\'' + res.data[i].detail_id +
                  '\')"><img src="{{config('app.url_storage').'/assets/ugc/images/Video.png'}}">' + res.data[i].title + '</div>'
              }
            }
            $(".course-list").html(courseListHtml)
          }
        },
      })
    }

    // 获取点赞数
    function getLikeNum() {
      $.ajax({
        type: "get",
        url: baseURL + "/api/v2/interaction/vote/statistic/course/{{ $data['course']['video_key'] }}/" + cryptonymKey,
        dataType: "json",
        xhrFields: {
          withCredentials: true
        },
        @if($data['course']['session_id'])
        headers: {
          "X-SessionId": "{{ $data['course']['session_id'] }}",
        },
        @else
        @endif
        success: function (res) {
          if (res.code == 0) {
            if (res.data[0].is_like) {
              $('.like').addClass("clicked");
              $('.like img').attr("src", "{{config('app.url_storage').'/assets/ugc/images/like_HighlightBig.png'}}")
              $('.praise-like').addClass("clicked")
              $('.praise-img img').attr("src", "{{config('app.url_storage').'/assets/ugc/images/like_Highlight.png'}}")
            }
            likeNum = res.data[0].like_sum
            if (likeNum > 999) {
              $('.praise-like').text() === "999+"
            } else {
              if (likeNum != 0) {
                $('.praise-like').text(likeNum) 
              }
            }
          }
        },
      })
    }

    // 点赞功能
    function clickLike() {
      var params = {
        "type": "course",
        "article_id": "{{ $data['course']['video_key'] }}",
        "udid": cryptonymKey,
      }
      $.ajax({
        type: "put",
        url: baseURL + "/api/v2/interaction/vote",
        dataType: "json",
        xhrFields: {
          withCredentials: true
        },
        data: params,
        @if($data['course']['session_id'])
        headers: {
          "X-SessionId": "{{ $data['course']['session_id'] }}",
        },
        @endif
        success: function (res) {
          if (res.code == 0) {
            if (res.data[0].is_like) {
              $('.reply-bottom .praise-like').addClass("clicked")
              $('.reply-bottom .praise-img img').attr("src", "{{config('app.url_storage').'/assets/ugc/images/like_Highlight.png'}}")
   
              if (res.data[0].like_sum > 999) {
                $('.praise-like').text('999+')
              } else {
                $('.praise-like').text(res.data[0].like_sum)
              }
            } else {
              $('.reply-bottom .praise-img img').attr("src", "{{config('app.url_storage').'/assets/ugc/images/like_Normal.png'}}")
              $('.reply-bottom .praise-like').removeClass("clicked")
              if (res.data[0].like_sum > 999) {
                $('.praise-like').text('999+')
              } else {
                if (res.data[0].like_sum != 0) {
                  $('.praise-like').text(res.data[0].like_sum)
                } else {
                  $('.praise-like').text("")
                }
              }
            }
          }
        },
        error: function () {
          layer.open({
            content: "点赞失败！",
            skin: 'msg',
            time: 2 //2秒后自动关闭
          });
        }
      })
    }

    function goCourseUrl(id) {
      window.location.href = baseURL + "/api/v2/client/course/detail/" + id + "?tab=2"
    }

    // 获取匿名用户身份码
    function getCryptonymKey() {
      var date = new Date().getTime()
      cryptonymKey = random(4) + '-' + hex_md5(date)
      localStorage.setItem("zytg_cryptonym_key", cryptonymKey)
    }

    function random(length) {
      var str = Math.random().toString(36).substr(2);
      if (str.length >= length) {
        return str.substr(0, length);
      }
      str += random(length - str.length);
      return str;
    }

    //获取url中的参数
    function getUrlParam(name) {
      var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
      var r = window.location.search.substr(1).match(reg); //匹配目标参数
      if (r != null) return unescape(r[2]);
      return null; //返回参数值
    };

  </script>
</body>
</html>
