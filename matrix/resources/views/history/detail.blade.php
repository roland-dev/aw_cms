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
      line-height: 1.5;
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
      <div class="content"></div>
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
  'isTeacher' => $data['article']['is_forward_teacher'], 'type' => $data['article']['type'], 'articleId' => $data['article']['id'],
  'articleTitle' => $data['article']['title'], 'articleAuthorUserId' => $data['article']['teacher_user_id'],
  'forwardTeacherId' => $data['article']['forward_open_id'], 'isReply' => $data['article']['is_reply'], 'isLike' => $data['article']['is_like'], 'likeSum' => $data['article']['like_sum'], 'forwardImg' => ''])
  @endcomponent

  @component('component.wxshare', ['articleTitle' =>$data['article']['title'], 'desc' => ''])
  @endcomponent
</body>
<script type="text/javascript" src="{{config('app.url_storage').'/assets/ugc/js/previewImage.js'}}"></script>
<script src="{{config('app.url_storage').'/assets/ugc/js/md5.js'}}"></script>
<script>
(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined'
        ? module.exports = factory(global)
        : typeof define === 'function' && define.amd
        ? define(factory) : factory(global)
}((
    typeof self !== 'undefined' ? self
        : typeof window !== 'undefined' ? window
        : typeof global !== 'undefined' ? global
: this
), function(global) {
    'use strict';
    // existing version for noConflict()
    global = global || {};
    var _Base64 = global.Base64;
    var version = "2.5.1";
    // if node.js and NOT React Native, we use Buffer
    var buffer;
    if (typeof module !== 'undefined' && module.exports) {
        try {
            buffer = eval("require('buffer').Buffer");
        } catch (err) {
            buffer = undefined;
        }
    }
    // constants
    var b64chars
        = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
    var b64tab = function(bin) {
        var t = {};
        for (var i = 0, l = bin.length; i < l; i++) t[bin.charAt(i)] = i;
        return t;
    }(b64chars);
    var fromCharCode = String.fromCharCode;
    // encoder stuff
    var cb_utob = function(c) {
        if (c.length < 2) {
            var cc = c.charCodeAt(0);
            return cc < 0x80 ? c
                : cc < 0x800 ? (fromCharCode(0xc0 | (cc >>> 6))
                                + fromCharCode(0x80 | (cc & 0x3f)))
                : (fromCharCode(0xe0 | ((cc >>> 12) & 0x0f))
                   + fromCharCode(0x80 | ((cc >>>  6) & 0x3f))
                   + fromCharCode(0x80 | ( cc         & 0x3f)));
        } else {
            var cc = 0x10000
                + (c.charCodeAt(0) - 0xD800) * 0x400
                + (c.charCodeAt(1) - 0xDC00);
            return (fromCharCode(0xf0 | ((cc >>> 18) & 0x07))
                    + fromCharCode(0x80 | ((cc >>> 12) & 0x3f))
                    + fromCharCode(0x80 | ((cc >>>  6) & 0x3f))
                    + fromCharCode(0x80 | ( cc         & 0x3f)));
        }
    };
    var re_utob = /[\uD800-\uDBFF][\uDC00-\uDFFFF]|[^\x00-\x7F]/g;
    var utob = function(u) {
        return u.replace(re_utob, cb_utob);
    };
    var cb_encode = function(ccc) {
        var padlen = [0, 2, 1][ccc.length % 3],
        ord = ccc.charCodeAt(0) << 16
            | ((ccc.length > 1 ? ccc.charCodeAt(1) : 0) << 8)
            | ((ccc.length > 2 ? ccc.charCodeAt(2) : 0)),
        chars = [
            b64chars.charAt( ord >>> 18),
            b64chars.charAt((ord >>> 12) & 63),
            padlen >= 2 ? '=' : b64chars.charAt((ord >>> 6) & 63),
            padlen >= 1 ? '=' : b64chars.charAt(ord & 63)
        ];
        return chars.join('');
    };
    var btoa = global.btoa ? function(b) {
        return global.btoa(b);
    } : function(b) {
        return b.replace(/[\s\S]{1,3}/g, cb_encode);
    };
    var _encode = buffer ?
        buffer.from && Uint8Array && buffer.from !== Uint8Array.from
        ? function (u) {
            return (u.constructor === buffer.constructor ? u : buffer.from(u))
                .toString('base64')
        }
        :  function (u) {
            return (u.constructor === buffer.constructor ? u : new  buffer(u))
                .toString('base64')
        }
        : function (u) { return btoa(utob(u)) }
    ;
    var encode = function(u, urisafe) {
        return !urisafe
            ? _encode(String(u))
            : _encode(String(u)).replace(/[+\/]/g, function(m0) {
                return m0 == '+' ? '-' : '_';
            }).replace(/=/g, '');
    };
    var encodeURI = function(u) { return encode(u, true) };
    // decoder stuff
    var re_btou = new RegExp([
        '[\xC0-\xDF][\x80-\xBF]',
        '[\xE0-\xEF][\x80-\xBF]{2}',
        '[\xF0-\xF7][\x80-\xBF]{3}'
    ].join('|'), 'g');
    var cb_btou = function(cccc) {
        switch(cccc.length) {
        case 4:
            var cp = ((0x07 & cccc.charCodeAt(0)) << 18)
                |    ((0x3f & cccc.charCodeAt(1)) << 12)
                |    ((0x3f & cccc.charCodeAt(2)) <<  6)
                |     (0x3f & cccc.charCodeAt(3)),
            offset = cp - 0x10000;
            return (fromCharCode((offset  >>> 10) + 0xD800)
                    + fromCharCode((offset & 0x3FF) + 0xDC00));
        case 3:
            return fromCharCode(
                ((0x0f & cccc.charCodeAt(0)) << 12)
                    | ((0x3f & cccc.charCodeAt(1)) << 6)
                    |  (0x3f & cccc.charCodeAt(2))
            );
        default:
            return  fromCharCode(
                ((0x1f & cccc.charCodeAt(0)) << 6)
                    |  (0x3f & cccc.charCodeAt(1))
            );
        }
    };
    var btou = function(b) {
        return b.replace(re_btou, cb_btou);
    };
    var cb_decode = function(cccc) {
        var len = cccc.length,
        padlen = len % 4,
        n = (len > 0 ? b64tab[cccc.charAt(0)] << 18 : 0)
            | (len > 1 ? b64tab[cccc.charAt(1)] << 12 : 0)
            | (len > 2 ? b64tab[cccc.charAt(2)] <<  6 : 0)
            | (len > 3 ? b64tab[cccc.charAt(3)]       : 0),
        chars = [
            fromCharCode( n >>> 16),
            fromCharCode((n >>>  8) & 0xff),
            fromCharCode( n         & 0xff)
        ];
        chars.length -= [0, 0, 2, 1][padlen];
        return chars.join('');
    };
    var _atob = global.atob ? function(a) {
        return global.atob(a);
    } : function(a){
        return a.replace(/\S{1,4}/g, cb_decode);
    };
    var atob = function(a) {
        return _atob(String(a).replace(/[^A-Za-z0-9\+\/]/g, ''));
    };
    var _decode = buffer ?
        buffer.from && Uint8Array && buffer.from !== Uint8Array.from
        ? function(a) {
            return (a.constructor === buffer.constructor
                    ? a : buffer.from(a, 'base64')).toString();
        }
        : function(a) {
            return (a.constructor === buffer.constructor
                    ? a : new buffer(a, 'base64')).toString();
        }
        : function(a) { return btou(_atob(a)) };
    var decode = function(a){
        return _decode(
            String(a).replace(/[-_]/g, function(m0) { return m0 == '-' ? '+' : '/' })
                .replace(/[^A-Za-z0-9\+\/]/g, '')
        );
    };
    var noConflict = function() {
        var Base64 = global.Base64;
        global.Base64 = _Base64;
        return Base64;
    };
    // export Base64
    global.Base64 = {
        VERSION: version,
        atob: atob,
        btoa: btoa,
        fromBase64: decode,
        toBase64: encode,
        utob: utob,
        encode: encode,
        encodeURI: encodeURI,
        btou: btou,
        decode: decode,
        noConflict: noConflict,
        __buffer__: buffer
    };
    // if ES5 is available, make Base64.extendString() available
    if (typeof Object.defineProperty === 'function') {
        var noEnum = function(v){
            return {value:v,enumerable:false,writable:true,configurable:true};
        };
        global.Base64.extendString = function () {
            Object.defineProperty(
                String.prototype, 'fromBase64', noEnum(function () {
                    return decode(this)
                }));
            Object.defineProperty(
                String.prototype, 'toBase64', noEnum(function (urisafe) {
                    return encode(this, urisafe)
                }));
            Object.defineProperty(
                String.prototype, 'toBase64URI', noEnum(function () {
                    return encode(this, true)
                }));
        };
    }
    //
    // export Base64 to the namespace
    //
    if (global['Meteor']) { // Meteor.js
        Base64 = global.Base64;
    }
    // module.exports and AMD are mutually exclusive.
    // module.exports has precedence.
    if (typeof module !== 'undefined' && module.exports) {
        module.exports.Base64 = global.Base64;
    }
    else if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define([], function(){ return global.Base64 });
    }
    // that's it!
    return {Base64: global.Base64}
}));
</script>
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
    var conentHTML = Base64.decode('{!! $data['article']['content'] !!}');
    $('.content').html(conentHTML)
  })

  // 获取点赞数
  function getLikeNum() {
    $.ajax({
      type: "get",
      url: baseURL + "/api/v2/interaction/vote/statistic/talkshow/{{ $data['article']['id'] }}/" + cryptonymKey,
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
          } else {
            $('.like').removeClass("clicked");
            $('.like img').attr("src", "{{config('app.url_storage').'/assets/ugc/images/like_NormalBig.png'}}")
            $('.praise-like').removeClass("clicked")
            $('.praise-img img').attr("src", "{{config('app.url_storage').'/assets/ugc/images/like_Normal.png'}}")
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
