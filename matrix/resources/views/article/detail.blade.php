<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
  <meta name="format-detection" content="telephone=no">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="white">
  <meta name="referrer" content="no-referrer" />
  <title>{{ $data['article']['title'] }}</title>
  <link rel="stylesheet" href="{{config('app.url_storage').'/assets/ugc/css/reset.css'}}?v=20190420">
  <link rel="stylesheet" href="{{config('app.url_storage').'/assets/ugc/css/reply.css'}}?v=20191127">
  <link rel="stylesheet" href="{{config('app.url_storage').'/assets/ugc/css/forward.css'}}?v=20190422">
  <style>
    .wrap {
      margin: 0 auto;
      max-width: 1000px;
      color: #333;
      word-break: break-all;
    }

    /*文章页面*/
    .article {
      font-size: 15px;
      line-height: 1.5;
      color: #333;
      margin: 20px 12px;
    }

    .article .title {
      font-size: 20px;
      font-weight: bold;
      line-height: 28px;
    }

    .article .actives {
      font-size: 12px;
      color: #999;
      line-height: 17px;
      margin-top: 8px;
    }

    .article .voice {
      margin-top: 8px;
      overflow: hidden;
    }

    .article .intro {
      font-size: 14px;
      margin-top: 8px;
    }

    .article .actives .views img {
      vertical-align: -3px;
    }

    .article .views-num {
      color: #333;
    }

    .article .actives .time {
      margin-left: 12px;
    }

    .article iframe {
      margin: 16px 0;
    }

    .article .content {
      min-height: 180px;
      margin-top: 16px;
    }

    .article .content ul,
    .article .content ol {
      margin: 10px 0 10px 20px;
    }

    .article .content ul li {
      list-style: disc;
    }

    .article .content ol li {
      list-style: decimal;
    }

    .article .content p {
      margin: 10px 0;
      line-height: 2;
      text-align: justify;
    }

    .article .content .iframe {
      margin: 16px 0;
    }

    .article .content img {
      max-width: 100%;
    }

    .article .tip {
      font-size: 13px;
      color: #666;
      padding: 10px 12px 12px 12px;
      margin: 30px 0;
      background: #fff5f5;
      border-radius: 2px;
    }

    .article .tip .title {
      text-align: center;
      font-size: 14px;
      font-weight: bold;
      color: #333;
      margin-bottom: 2px;
    }

    .article .tip .title img {
      width: 6px;
      margin: 0 4px;
      vertical-align: 1px;
    }

    .views img {
      border-radius: 50%;
    }

  </style>
  <script type="text/javascript" src="{{config('app.url_storage').'/assets/ugc/js/zepto.min.js'}}"></script>
  <script src="{{config('app.url_storage').'/assets/ugc/js/layer.js'}}"></script>
</head>

<body>
  <div class="wrap" id="wrap">
    <div class="article" id="article">
      <div class="title">{{ $data['article']['title'] }}</div>
      <div class="actives">
        <span class="views">
        @if($data['article']['teacher_icon_url'])
          <img src="{{ $data['article']['teacher_icon_url'] }}" alt="" width="16px">
          @else
          <img src="http://res.zhongyingtougu.com/cms/head_icon/default.png" alt="" width="16px">
          @endif
          <span class="views-num">{{ $data['article']['teacher_name'] }}</span>
        </span>
        <span class="time">{{ $data['article']['published_at'] }}</span>
      </div>
      <div class="content">{!! $data['article']['content'] !!}</div>
      @if($data['article']['is_like'])
      <div class="like clicked">
        <img src="{{config('app.url_storage').'/assets/ugc/images/like_HighlightBig.png'}}" alt="">
        <span class="num" style="top: -4px;">{{ $data['article']['like_sum'] }}</span>
      </div>
      @else
      <div class="like">
        <img src="{{config('app.url_storage').'/assets/ugc/images/like_NormalBig.png'}}" alt="">
        <span class="num" style="top: -4px;">{{ $data['article']['like_sum'] }}</span>
      </div>
      @endif
      <div class="tip">
        <div class="title">
          <img src="{{config('app.url_storage').'/assets/ugc/images/img_risk.png'}}" alt=""> 风险提示
          <img src="{{config('app.url_storage').'/assets/ugc/images/img_risk.png'}}" alt="">
        </div>
        <div>以上内容仅供参考，不构成任何投资建议。股市有风险，投资需谨慎！</div>
      </div>
    </div>
  </div>
  @component('component.reply', [ 'id' => $data['article']['id'], 'sessionId' => $data['article']['session_id'],
  'isTeacher' => $data['article']['is_forward_teacher'], 'type' => 'article', 'articleId' => $data['article']['id'],
  'articleTitle' => $data['article']['title'], 'articleAuthorUserId' => $data['article']['teacher_user_id'],
  'forwardTeacherId' => $data['article']['forward_open_id'], 'isReply' => $data['article']['is_reply'], 'isLike' => $data['article']['is_like'], 'likeSum' => $data['article']['like_sum'], 'forwardImg' => $data['article']['cover_url']])
  @endcomponent

  @component('component.wxshare', ['articleTitle' =>$data['article']['title'], 'desc' => ''])
  @endcomponent
</body>
<script type="text/javascript" src="{{config('app.url_storage').'/assets/ugc/js/previewImage.js'}}"></script>
<script src="{{config('app.url_storage').'/assets/ugc/js/md5.js'}}"></script>
<script type="text/javascript">
  var cryptonymKey;
  var likeNum = "{{ $data['article']['like_sum'] }}";
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
    $('.wrap').on("click", ".like", function () {
        clickLike()
    })
  })

  // 获取点赞数
  function getLikeNum() {
    $.ajax({
      type: "get",
      url: baseURL + "/api/v2/interaction/vote/statistic/article/{{ $data['article']['id'] }}/" + cryptonymKey,
      dataType: "json",
      xhrFields: {
        withCredentials: true
      },
      @if($data['article']['session_id'])
      headers: {
        "X-SessionId": "{{ $data['article']['session_id'] }}",
      },
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
      "type": "article",
      "article_id": "{{ $data['article']['id'] }}",
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
      @if($data['article']['session_id'])
      headers: {
        "X-SessionId": "{{ $data['article']['session_id'] }}",
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
</script>
<script>
  setTimeout(function () {
    var iframes = document.querySelectorAll("#article iframe")
    var iframeWidth = document.querySelector("#article").offsetWidth * 0.66
    if (iframes.length > 0) {
      for (var i = 0; i < iframes.length; i++) {
        iframes[i].style.width = "100%";
        iframes[i].style.height = iframeWidth + "px";
      }
    }
  }, 200);
</script>
</html>
