<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="X-UA-Compatible" content="chrome=1,IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
  <meta name="renderer" content="webkit">
  <meta name="referrer" content="no-referrer" />
  <meta name="format-detection" content="telephone=no">
  <title>{{ $data['talkshow']['title'] }}</title>
  <link rel="stylesheet" href="{{config('app.url_storage').'/assets/ugc/css/reset.css'}}?v=20190420">
  <link rel="stylesheet" href="{{config('app.url_storage').'/assets/ugc/css/reply.css'}}?v=20200316">
  <link rel="stylesheet" href="{{config('app.url_storage').'/assets/ugc/css/forward.css'}}?v=20190422">
  <style>
    @media screen and (min-width: 1000px){ 
      .layout{
        display: flex;
        width: 100vw;
        height: 100vh;
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
</head>
<body>
  <div class="layout">
    <div class="wrap">
      <!-- video_type 0 腾讯视频 1 展示互动 2 微吼 3 其他视频-->
      <div id="zytg_player">
        @if ( $data['talkshow']['video_type'] === 0)
        @component('component.video', [ 'id' => $data['talkshow']['detail_id'], 'vkey' => $data['talkshow']['content_id']])
        @endcomponent
        @elseif ( $data['talkshow']['video_type'] === 1 )
        @component('component.gensee', [ 'id' => $data['talkshow']['original_key'], 'posterUrl' => '', 'vkey' => $data['talkshow']['content_id']])
        @endcomponent
        @elseif ( $data['talkshow']['video_type'] === 2 )
        @component('component.vhall', [ 'id' => $data['talkshow']['original_key'], 'title' =>$data['talkshow']['title'], 'type' => 'talkshow', 'vkey' => $data['talkshow']['content_id']])
        @endcomponent
        @else
        <p class="video" style="position: relative; padding-bottom: 62.5%; height: 0; overflow: hidden;">
          <iframe src="{{ $data['talkshow']['source_url'] }}" frameborder="0" frameborder="0" width="100%"
            allowfullscreen="allowfullscreen" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></iframe>
        </p>
        @endif
      </div>

    </div>
    <div class="right-area">
      <div class="section-top">
        <div class="header">
          <div class="header-l">
            <div class="author-img"><img src="{{ $data['talkshow']['owner_avatar'] }}" alt=""></div>
          </div>
          <div class="header-r">
            <div class="author">{{ $data['talkshow']['owner_name'] }}</div>
            <div class="date">{{ $data['talkshow']['add_time'] }} <span>{{ $data['talkshow']['category_name'] }}</span></div>
          </div>
        </div>
        <div class="content">
          <div class="title">{{ $data['talkshow']['title'] }}</div>
          @if($data['talkshow']['summary'])
          <div class="des" style="min-height: 60px;"><pre>{{ $data['talkshow']['summary'] }}</pre></div>
          @endif
          @if($data['talkshow']['is_like'])
          <div class="like clicked">
            <img src="{{config('app.url_storage').'/assets/ugc/images/like_HighlightBig.png'}}" alt="">
            <span class="num" style="top: -4px;">{{ $data['talkshow']['like_sum'] }}</span>
          </div>
          @else
          <div class="like">
            <img src="{{config('app.url_storage').'/assets/ugc/images/like_NormalBig.png'}}" alt="">
            <span class="num" style="top: -4px;">{{ $data['talkshow']['like_sum'] }}</span>
          </div>
          @endif
          <div class="tip">
            <div class="tip-title">
              <img src="{{config('app.url_storage').'/assets/ugc/images/img_risk.png'}}" alt=""> 风险提示
              <img src="{{config('app.url_storage').'/assets/ugc/images/img_risk.png'}}" alt="">
            </div>
            <div>以上内容仅供参考，不构成任何投资建议。股市有风险，投资需谨慎！</div>
          </div>
        </div>
      </div>
    <script src="{{config('app.url_storage').'/assets/ugc/js/layer.js'}}"></script>

    @component('component.reply', [ 'id' => $data['talkshow']['original_key'], 'sessionId' => $data['talkshow']['session_id'], 'type' =>
    'talkshow', 'articleId' => $data['talkshow']['content_id'], 'isTeacher' => $data['talkshow']['is_forward_teacher'], 'articleTitle' =>
    $data['talkshow']['title'], 'articleAuthorUserId' => $data['talkshow']['author_id'], 'forwardTeacherId' => $data['talkshow']['forward_open_id'], 'isReply'
    => $data['talkshow']['is_reply'], 'isLike' => $data['talkshow']['is_like'], 'likeSum' => $data['talkshow']['like_sum'], 'forwardImg' => $data['talkshow']['thumb_cdn_url']])
    @endcomponent
    </div>
    @component('component.wxshare', ['articleTitle' =>$data['talkshow']['title'], 'desc' =>$data['talkshow']['summary']])
    @endcomponent
  </div>
  <script type="text/javascript" src="{{config('app.url_storage').'/assets/ugc/js/previewImage.js'}}"></script>
  <script src="//res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
  <script src="{{config('app.url_storage').'/assets/ugc/js/md5.js'}}"></script>
  <script type="text/javascript">
    var cryptonymKey;
    var detailId = "{{ $data['talkshow']['detail_id'] }}";
    var likeNum = "{{ $data['talkshow']['like_sum'] }}";
    var baseURL = "{{ config('app.ajax_url') }}";
    $(function () {
      if (localStorage.getItem("zytg_cryptonym_key")) {
        cryptonymKey = localStorage.getItem("zytg_cryptonym_key")
      } else {
        getCryptonymKey()
      }
      getLikeNum()
      getReply()

      // 用户点赞功能
      $('.layout').on("click", ".like", function () {
          clickLike()
      })
    })

    // 获取点赞数
    function getLikeNum() {
      $.ajax({
        type: "get",
        url: baseURL + "/api/v2/interaction/vote/statistic/talkshow/{{ $data['talkshow']['content_id'] }}/" + cryptonymKey,
        dataType: "json",
        xhrFields: {
          withCredentials: true
        },
        @if($data['talkshow']['session_id'])
        headers: {
          "X-SessionId": "{{ $data['talkshow']['session_id'] }}",
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
              $('.num').text() === "999+"
            } else {
              $('.num').text(likeNum)
            }
            if ($('.num').text() != 0) {
              $('.praise-like').text($('.num').text())
            } else {
              $('.praise-like').text("")
            }
          }
        },
      })
    }

    // 点赞功能
    function clickLike() {
      var params = {
        "type": "talkshow",
        "article_id": "{{ $data['talkshow']['content_id'] }}",
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
        @if($data['talkshow']['session_id'])
        headers: {
          "X-SessionId": "{{ $data['talkshow']['session_id'] }}",
        },
        @endif
        success: function (res) {
          if (res.code == 0) {
            if (res.data[0].is_like) {
              $('.like').addClass("clicked");
              $('.reply-bottom .praise-like').addClass("clicked")
              $('.like img').attr("src", "{{config('app.url_storage').'/assets/ugc/images/like_HighlightBig.png'}}")
              $('.reply-bottom .praise-img img').attr("src", "{{config('app.url_storage').'/assets/ugc/images/like_Highlight.png'}}")
              if (res.data[0].like_sum > 999) {
                $('.num').text('999+')
              } else {
                $('.num').text(res.data[0].like_sum)
              }
              $('.praise-like').text($('.num').text())
            } else {
              $('.like').removeClass("clicked")
              $('.like img').attr("src", "{{config('app.url_storage').'/assets/ugc/images/like_NormalBig.png'}}")
              $('.reply-bottom .praise-img img').attr("src", "{{config('app.url_storage').'/assets/ugc/images/like_Normal.png'}}")
              $('.reply-bottom .praise-like').removeClass("clicked")
              if (res.data[0].like_sum > 999) {
                $('.num').text('999+')
              } else {
                $('.num').text(res.data[0].like_sum)
              }
              if ($('.num').text() != 0) {
                $('.praise-like').text($('.num').text())
              } else {
                $('.praise-like').text("")
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

    // 获取匿名用户身份码udid
    function getCryptonymKey() {
      var date = new Date().getTime()
      cryptonymKey = random(4) + '-' + hex_md5(date)
      localStorage.setItem("zytg_cryptonym_key", cryptonymKey)
    }

    // 返回随机数
    function random(length) {
      var str = Math.random().toString(36).substr(2);
      if (str.length >= length) {
        return str.substr(0, length);
      }
      str += random(length - str.length);
      return str;
    }

  </script>
</body>
</html>
