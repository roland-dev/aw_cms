<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
  <meta name="format-detection" content="telephone=no">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="white">
  <meta name="referrer" content="no-referrer" />
  <title>{{ $data['kit_report']['title'] }}</title>
  <link rel="stylesheet" href="{{config('app.url_storage').'/assets/ugc/css/reset.css'}}?v=20190420">
  <link rel="stylesheet" href="{{config('app.url_storage').'/assets/ugc/css/reply.css'}}?v=20191127">
  <link rel="stylesheet" href="{{config('app.url_storage').'/assets/ugc/css/forward.css'}}?v=20190422">
  <style>
    .wrap {
      margin: 0 auto;
      color: #333;
      word-break: break-all;
    }

    /*文章页面*/
    .kitReport {
      font-size: 15px;
      line-height: 1.5;
      color: #333;
      margin: 20px 12px;
    }

    .kitReport .title {
      font-size: 20px;
      font-weight: bold;
      line-height: 28px;
    }

    .kitReport .actives {
      font-size: 12px;
      color: #999;
      line-height: 17px;
      margin-top: 8px;
    }

    .kitReport .voice {
      margin-top: 8px;
      overflow: hidden;
    }

    .kitReport .intro {
      font-size: 14px;
      margin-top: 8px;
    }

    .kitReport .actives .views img {
      vertical-align: -3px;
    }

    .kitReport .views-num {
      color: #333;
    }
    
    .kitReport .views-icon {
      width: 17px;
      height: 17px;
    }

    .kitReport .actives .time {
      margin-left: 12px;
    }

    .kitReport iframe {
      margin: 16px 0;
    }

    .kitReport .summary {
      margin-top: 12px;
      background: rgba(248, 248, 248, 1);
      padding: 12px 15px;
      color: #666666;
      font-weight: 400;
    }

    .kitReport .content {
      min-height: 180px;
      margin-top: 16px;
    }

    .kitReport .content_pdf {
      text-decoration: none;
    }

    .kitReport .content .pdf {
      background: rgba(238, 246, 255, 1);
      border-radius: 4px;
      padding: 10px 0px;
      text-align: center;
    }

    .kitReport .pdf img {
      width: 22px;
      height: 22px;
      vertical-align: -3px;
    }

    .kitReport .pdf span {
      font-size: 16px;
      font-weight: 400;
      color: rgba(11, 157, 255, 1);
    }

    .kitReport .content ul,
    .kitReport .content ol {
      margin: 10px 0 10px 20px;
    }

    .kitReport .content ul li {
      list-style: disc;
    }

    .kitReport .content ol li {
      list-style: decimal;
    }

    .kitReport .content p {
      margin: 10px 0;
      line-height: 2;
      text-align: justify;
    }

    .kitReport .content .iframe {
      margin: 16px 0;
    }

    .kitReport .content img {
      max-width: 100%;
    }

    .kitReport .tip {
      font-size: 13px;
      color: #666;
      padding: 10px 12px 12px 12px;
      margin: 30px 0;
      background: #fff5f5;
      border-radius: 2px;
    }

    .kitReport .tip .title {
      text-align: center;
      font-size: 14px;
      font-weight: bold;
      color: #333;
      margin-bottom: 2px;
    }

    .kitReport .tip .title img {
      width: 6px;
      margin: 0 4px;
      vertical-align: 1px;
    }

    .views img {
      border-radius: 50%;
    }
    
    .download-bottom {
      max-width: 100%;
    }

    .wrap {
      max-width: 100%;
    }
  </style>
  <script type="text/javascript" src="{{config('app.url_storage').'/assets/ugc/js/zepto.min.js'}}"></script>
  <script src="{{config('app.url_storage').'/assets/ugc/js/layer.js'}}"></script>
</head>
<body>
  <div class="wrap" id="wrap">
    <div class="kitReport" id="kitReport">
      <div class="title">{{ $data['kit_report']['title'] }}</div>
      <div class="actives">
        <span class="views">
          <img class="views-icon" src="{{ $data['kit_report']['belong_user_icon'] }}">
          <span class="views-num">{{ $data['kit_report']['belong_user_name'] }}</span>
        </span>
        <span class="time">{{ $data['kit_report']['start_at'] }} — {{ $data['kit_report']['end_at'] }}</span>
      </div>

      @if($data['kit_report']['summary'])
      <div class="summary">{{ $data['kit_report']['summary'] }}</div>
      @endif

      @if($data['kit_report']['format'])
      <div class="content">
        <a class="content_pdf" href="{{ $data['kit_report']['url'] }}">
          <div class="pdf">
            <img src="{{config('app.url_storage').'/assets/ugc/images/pdf_icon.png'}}" >
            <span>点击查看完整报告</span>
          </div>
        </a>
      </div>
      @else
      <div class="content">{!! $data['kit_report']['content'] !!}</div>
      @endif

      @if($data['kit_report']['is_like'])
      <div class="like clicked">
        <img src="{{config('app.url_storage').'/assets/ugc/images/like_HighlightBig.png'}}" alt="">
        <span class="num" style="top: -4px;">{{ $data['kit_report']['like_sum'] }}</span>
      </div>
      @else
      <div class="like">
        <img src="{{config('app.url_storage').'/assets/ugc/images/like_NormalBig.png'}}" alt="">
        <span class="num" style="top: -4px;">{{ $data['kit_report']['like_sum'] }}</span>
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
  @component('component.reply', ['id' => $data['kit_report']['report_id'], 'sessionId' => $data['kit_report']['session_id'],
  'isTeacher' => $data['kit_report']['is_forward_teacher'], 'type' => 'kit_report', 'articleId' => $data['kit_report']['report_id'],
  'articleTitle' => $data['kit_report']['title'], 'articleAuthorUserId' => $data['kit_report']['belong_user_id'],
  'forwardTeacherId' => $data['kit_report']['forward_open_id'], 'isReply' => $data['kit_report']['is_reply'], 'isLike' => $data['kit_report']['is_like'], 'likeSum' => $data['kit_report']['like_sum'], 'forwardImg' => '' ])
  @endcomponent

  @component('component.wxshare', ['articleTitle' => $data['kit_report']['title'], 'desc' => ''])
  @endcomponent
</body>
<script type="text/javascript" src="{{config('app.url_storage').'/assets/ugc/js/previewImage.js'}}"></script>
<script src="{{config('app.url_storage').'/assets/ugc/js/md5.js'}}"></script>
<script type="text/javascript">
  var cryptonymKey;
  var likeNum = "{{ $data['kit_report']['like_sum'] }}";
  var baseURL = "{{ config('app.ajax_url') }}";

  $(function () {
    if (localStorage.getItem("zytg_cryptonym_key")) {
      cryptonymKey = localStorage.getItem("zytg_cryptonym_key")
    } else {
      getCryptonymKey()
    }
    getLikeNum()
    getReply()

    $('.wrap').on('click', '.like', function () {
      clickLike()
    })

    document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
      // 通过下面这个API隐藏右上角按钮
      WeixinJSBridge.call('hideOptionMenu');
    });
  })

  // 获取点赞数
  function getLikeNum() {
    $.ajax({
      type: "get",
      url: baseURL + "/api/v2/interaction/vote/statistic/kit_report/{{ $data['kit_report']['report_id'] }}/" + cryptonymKey,
      dataType: "json",
      xhrFields: {
        withCredentials: true
      },
      @if($data['kit_report']['session_id'])
      headers: {
        "X-SessionId": "{{ $data['kit_report']['session_id'] }}",
      },
      @endif
      success: function (res) {
        if (res.code == 0) {
          if (res.data[0].is_like) {
            $('.like').addClass('clicked');
            $('.like img').attr("src", "{{config('app.url_storage').'/assets/ugc/images/like_HighlightBig.png'}}")
            $('.praise-like').addClass("clicked")
            $('.praise-img img').attr("src", "{{config('app.url_storage').'/assets/ugc/images/like_Highlight.png'}}")
          }
          likeNum = res.data[0].like_sum
          if (likeNum > 999) {
            $('.num').text('999+')
          } else {
            $('.num').text(likeNum)
          }

          if ($('.num').text() != 0) {
            $('.praise-like').text($('.num').text())
          } else {
            $('.praise-like').text("")
          }
        }
      }
    })
  }

  // 点赞功能
  function clickLike() {
    var params = {
      "type": "kit_report",
      "article_id": "{{ $data['kit_report']['report_id'] }}",
      "udid": cryptonymKey,
    }
    $.ajax({
      type: "put",
      url: baseURL + '/api/v2/interaction/vote',
      dataType: "json",
      xhrFields: {
        withCredentials: true
      },
      data: params,
      @if($data['kit_report']['session_id'])
      headers: {
        "X-SessionId": "{{ $data['kit_report']['session_id'] }}",
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
          content: '点赞失败！',
          skin: 'msg',
          time: 2
        });
      }
    })
  }
</script>
</html>