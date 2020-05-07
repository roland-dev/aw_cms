<html lang="en" data-scale="true">
<head>
  <meta charset="utf-8">
  <meta name="format-detection" content="telephone=no">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="referrer" content="no-referrer" />
  <link rel="stylesheet" href="{{config('app.url_storage').'/assets/ugc/css/news.css'}}?v=20200113">
  <script>window.isDelayLoadForApp = true;</script>
  <title>新闻详情</title>
  <style>
    .stock_code{
      cursor: pointer
    }
  </style>
</head>
<body>
  <div id="loading">加载中...</div>
  <div id="app">
    <div class="container__2Ylna">
      <div class="main__BK_-w">
        <div class="title1__lNHpI" id="newsTitle"></div>
        <div class="head__1v7IL">
          <span id="newsTime"></span>
          <span id="newsSource"></span>
          <a href="http://new.qq.com/omn/20190321/20190321A0O2ZC.html" target="_self" id="newsLink"
            style="display: none;">查看原文</a>
        </div>
        <div class="body-sm__FTgoD body__4Yj8j">
          <div id="newsBody">
            <!--?xml version="1.0" encoding="UTF-8"?-->
            <div class="content-article">
              <div id="Status">
              </div>
            </div>
          </div>
        </div>
        <div class="link__1GQzj" style="border: 0.5px solid rgb(229, 229, 229);">
          <div class="linkSVG__1N6nx">
            <svg width="30px" height="30px" viewBox="0 0 30 30" version="1.1" xmlns="http://www.w3.org/2000/svg"
              xmlns:xlink="http://www.w3.org/1999/xlink">
              <desc>Created with Sketch.</desc>
              <defs></defs>
              <g id="众赢财富通" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                <g id="首页－新闻详情版权" transform="translate(-15.000000, -622.000000)">
                  <g id="link" transform="translate(15.000000, 622.000000)">
                    <polygon id="矩形-2" fill="#2F77B5" points="0 0 30 0 0 30"></polygon>
                    <g id="分组-3"
                      transform="translate(10.035534, 10.500000) rotate(-315.000000) translate(-10.035534, -10.500000) translate(7.035534, 3.500000)">
                      <path
                        d="M5.12132034,8.87867966 L5.12132034,11.3786797 C5.12132034,12.7593915 4.00203222,13.8786797 2.62132034,13.8786797 L2.62132034,13.8786797 C1.24060847,13.8786797 0.121320344,12.7593915 0.121320344,11.3786797 L0.121320344,8.87867966"
                        id="矩形-15" stroke="#FFFFFF" stroke-linecap="round"></path>
                      <path
                        d="M0.121320344,5.87867966 L0.121320344,3.37867966 C0.121320344,1.99796778 1.24060847,0.878679656 2.62132034,0.878679656 L2.62132034,0.878679656 C4.00203222,0.878679656 5.12132034,1.99796778 5.12132034,3.37867966 L5.12132034,5.87867966"
                        id="矩形-15-copy" stroke="#FFFFFF" stroke-linecap="round"></path>
                      <rect id="矩形-13" fill="#FFFFFF" x="2.12132034" y="3.87867966" width="1" height="6" rx="0.5">
                      </rect>
                    </g>
                  </g>
                </g>
              </g>
            </svg>
          </div>
          <div style="padding-left: 15px;" id="newsTitle2"></div>
          <div style="padding-left: 15px;" id="newsLink2"></div>
        </div>
        <div class="info1__3shKQ" id="copyright" style="">如有版权问题，请联系众赢财富通客服</div>
        <div class="separator1__3O7lQ"></div>
        <div class="tags__2Mxb-">
          <div id="newsTags"></div>
        </div>
        @if ($is_forward_teacher)
        <div class="forward-img"><img src="{{config('app.url_storage').'/assets/ugc/images/share2_default.png'}}" alt=""></div>
        @endif
        <div class="tip">
          <div class="title">
            <img src="{{config('app.url_storage').'/assets/ugc/images/img_risk.png'}}" alt=""> 风险提示
            <img src="{{config('app.url_storage').'/assets/ugc/images/img_risk.png'}}" alt="">
          </div>
          <div>本页面资讯内容和其他基于人工智能算法关联的信息服务，仅供投资参考，不构成投资建议。</div>
        </div>
      </div>
    </div>
  </div>
  @if ($is_forward_teacher)
  <div class="hz-forward">
    <div class="forward-cont">
      <textarea class="comment" placeholder="在这里输入您的点评内容" rows="6" maxlength="500" id="forwardMessage"></textarea>
      <div class="share">
        <div class="share-img"></div>
        <div class="share-title"></div>
      </div>
    </div>
  </div>
  <div class="forward-bottom">
    <div class="focus">
      <div class="send">
        <img class="emoji-img" src="{{config('app.url_storage').'/assets/ugc/images/emoji_Default.png'}}" alt="">
        <img class="send-img" src="{{config('app.url_storage').'/assets/ugc/images/send_Default.png'}}" alt="">
      </div>
    </div>
    <div class="emoji-wrapper"></div>
  </div>
  <link rel="stylesheet" href="{{config('app.url_storage').'/assets/ugc/css/wEmoji.css'}}">
  <script src="{{config('app.url_storage').'/assets/ugc/js/wEmoji.js'}}"></script>
  <script src="{{config('app.url_storage').'/assets/ugc/js/layer.js'}}"></script>
  @endif
  <script src="{{config('app.url_storage').'/assets/ugc/js/zepto.min.js'}}"></script>
  <script src="//res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
  <script type="text/javascript">
    $(document).ready(function () {
      var newsTitle;
      @if($is_forward_teacher)
      var weForward;
      // var u = navigator.userAgent,
      // app = navigator.appVersion;
      // var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
  
      // // 判断ios设备
      // if (isiOS) {
      //   $('textarea').focus(function () {
      //     window.setTimeout(scrollBottom(), 500);
      //   });
      // }

      // 点击键盘按钮,唤起键盘
      $('.forward-bottom .reply-img img').click(function () {
        $('.reply-img img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/keyboard_Pressed.png'}}")
        setTimeout(function () {
          $('.reply-img img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/keyboard_Default.png'}}")
        }, 300)
        $('.forward-bottom').addClass('active')
        $('#replyMessage').focus()
      })

      // 点击发送按钮
      $('.forward-bottom .send-img').click(function () {
        $('.send-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/send_Pressed.png'}}")
        setTimeout(function () {
          $('.send-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/send_Default.png'}}")
        }, 300)
        forwardToList()
      })

      // 唤起键盘
      $('.forward-bottom .input-box').click(function () {
        $('.forward-bottom').addClass('active')
        $('.forward-bottom').height("194px")
        if (!$('.emoji-wrapper').is(':visible')) {
          $('.forward-bottom .emoji-img').attr("src", "{{config('app.url_storage').'/assets/ugc/images/emoji_Default.png'}}")
        }
      })

      $("#forwardMessage").focus(function () {
        $(".emoji-wrapper").hide()
        $('.emoji-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/emoji_Default.png'}}")
        $('.forward-bottom').height("52px")
      })

      // 点击emoji
      $('.forward-bottom .emoji-img').click(function () {
        if ($(this).attr("src") == "{{config('app.url_storage').'/assets/ugc/images/emoji_Default.png'}}") {
          $('.emoji-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/emoji_Pressed.png'}}")
          $('.emoji-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/keyboard_Default.png'}}")
          $(".emoji-wrapper").show()
          var emojiH = $(".emoji-wrapper").height() + $(".forward-bottom").height()
          $(".forward-bottom").height(emojiH)
        } else {
          $('.emoji-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/keyboard_Pressed.png'}}")
          $('.emoji-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/emoji_Default.png'}}")
          $(".emoji-wrapper").hide()
          $('.forward-bottom').height("52px")
        }
      })

      $(".share").click(function () {
        $('title').text($("#newsTitle").text())
        $('.container__2Ylna').show()
        $('.hz-forward').hide()
        $('.forward-bottom').hide()
      })

      // 转发
      function forwardToList() {
        var params = {
          "content": $("#forwardMessage").val(),
          "ref_type": "news",
          "ref_id": getUrlParam("news_id"),
          "ref_title": newsTitle,
          "is_reply": 0,
          "author_user_id": 0 // 无作者user_id为0
        }
        params.ref_thumb = getUrlParam("pic_url") ? getUrlParam("pic_url") : "{{config('app.url_storage').'/assets/ugc/images/noPic.png'}}"
        $.ajax({
          url: "{{ config('app.ajax_url') }}/api/v2/interaction/forward/twitter",
          type: 'post',
          headers: {
            "X-SessionId": "{{ $session_id }}",
          },
          data: params,
          success: function (res) {
            if (res.code === 0) {
              layer.open({
                content: res.msg,
                skin: 'msg',
                time: 2 //2秒后自动关闭
              });
              setTimeout(function () {
                $('title').text($("#newsTitle").text())
                $('.container__2Ylna').show()
                $('.hz-forward').hide()
                $('.forward-bottom').hide()
              }, 1000);
            } else {
              layer.open({
                content: res.msg,
                skin: 'msg',
                time: 2 //2秒后自动关闭
              });
            }
          },
          error: function (err) {
            layer.open({
              content: "网络连接异常！",
              skin: 'msg',
              time: 2 //2秒后自动关闭
            });
            console.error(err)
          }
        });
      }
      @endif
      $("#newsLink").hide();
      $("#newsLink2").parent().hide();
      $("#copyright").hide();

      const STOCK_URL = '//stock.zhongyingtougu.com'

      $.ajax({
        type: 'GET',
        url: STOCK_URL + '/api/v2/sc/news/hots/' + getUrlParam("news_id"),
        dataType: 'json',
        xhrFields: {
          withCredentials: true
        },
        success: function (resp) {
          showResp(resp);
        },
        error: function (xhr, errorType, err) {
          layer.open({
            content: "'接口发生错误: ' + err",
            skin: 'msg',
            time: 2 //2秒后自动关闭
          });
        }
      });

      // 点击转发
      $('.forward-img').click(function () {
        $('.forward-img img').attr('src', '{{url("images/share2_pressed.png")}}')
        setTimeout(function () {
          $('.forward-img img').attr('src', '{{url("images/share2_default.png")}}')
        }, 300)
        $('.container__2Ylna').hide()
        $('.hz-forward').show()
        $('.forward-bottom').show()
        $('.share-title').text($("#newsTitle").text())
        $('title').text("转发到解盘")
        if (getUrlParam("pic_url")) {
          $('.share-img').html('<img src="' + getUrlParam("pic_url") + '">')
        } else {
          $('.share-img').html('<img src="{{config('app.url_storage').'/assets/ugc/images/noPic.png'}}">')
        }

        if (!weForward) {
          weForward = new wantEmoji({
            wrapper: ".emoji-wrapper",
            callback: function (emojiCode) {
              $("#forwardMessage").val($("#forwardMessage").val() + emojiCode)
            },
            autoInit: true
          });
        }
      })

      // 展示文章详情
      function showResp(resp) {
        var news = resp.data;
        if (news) {
          $.get(news.detailUrl, function (txt) {
            // 获取body内容
            var start = txt.indexOf('<body>');
            var end = txt.indexOf('</body>');
            var _realBody = txt.substring(start + 6, end);
            //
            document.title = news.title;
            newsTitle = news.title
            wxshare()
            $("#app").show()
            $('#loading').hide()
            $("#copyright").show();

            $("#newsTitle").html(news.title);
            $("#newsTitle2").html(news.title);
            $("#newsTime").html(news.publishTime.substring(5, 16));
            $("#newsSource").html(news.source);
            $("#newsLink").attr('href', news.originalUrl);
            $("#newsLink2").text(news.originalUrl).parent().show();
            if (news.body && news.body.length > 0) {
              $("#newsSummary").html(news.body).parent().show();
            }
            $("#newsBody").hide();

            $("#newsBody").html(_realBody).show();
            $("#newsBody a").attr('href', '#');

            $.each(news.industryTags, function (index, tag) {
              $("#newsTags").append("<span>" + tag + "</span>");
            });
            $.each(news.themeTags, function (index, tag) {
              $("#newsTags").append("<span>" + tag + "</span>");
            });
            $.each(news.tickers, function (index, tag) {
              $("#newsTags").append(
                "<span class='stock_code' symbol='" + tag
                .symbol.substring(0, 6) + "'>" +
                tag.symbol +
                "</span>");
            });
          });
        } else {
          $("#newsSummary").html(resp.msg).parent().show();
        }
      };

      // 定位依附下边界
      function scrollBottom() {
        window.scrollTo(0, $('body').height());
      }

      //获取url中的参数
      function getUrlParam(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
        var r = window.location.search.substr(1).match(reg); //匹配目标参数
        if (r != null) return unescape(r[2]);
        return null; //返回参数值
      };

      // 微信分享
      function wxshare() {
        $.ajax({
          url: '{{ config('app.ajax_url') }}/api/v1/qywxUser/wxshare',
          type: 'post',
          data: {"url": window.location.href},
          crossDomain: true,
          xhrFields: {
              withCredentials: true
          },
          success: function (res) {
            if (res.code == 0) {
              // 微信分享
              wx.config({
                  debug: false,
                  appId: res.data.appId,
                  timestamp: res.data.timestamp,
                  nonceStr: res.data.nonceStr,
                  signature: res.data.signature,
                  // 所有要调用的 API 都要加到这个列表中
                  jsApiList: [
                      "onMenuShareAppMessage",
                      "onMenuShareTimeline"
                  ]
              });
              wx.error(function (response) {
                  console.log(response);
              });
              wx.ready(function () {
                  wx.checkJsApi({
                      jsApiList: ['onMenuShareAppMessage', 'onMenuShareTimeline'] // 需要检测的JS接口列表，所有JS接口列表见附录2,
                  })
                  wx.onMenuShareAppMessage({
                      title: document.title,
                      desc:  "",
                      link: window.location.href,
                      imgUrl: "{{config('app.url_storage').'/assets/ugc/images/logo300x300.png'}}",
                      success: function (response) {
                          self.saveShareResult()
                      },
                      cancel: function (response) {
                          console.log(response)
                      }
                  })
                  wx.onMenuShareTimeline({
                    title: document.title,
                    desc:  "",
                    link: window.location.href,
                    imgUrl: "{{config('app.url_storage').'/assets/ugc/images/logo300x300.png'}}",
                    success: function (res) {
                        alert("分享成功：" +JSON.stringify(res));
                        // 用户确认分享后执行的回调函数
                    },
                    cancel: function (res) {
                        alert("取消分享：" +JSON.stringify(res));
                        // 用户取消分享后执行的回调函数
                    },
                    fail:function (res) {
                        alert("分享失败：" +JSON.stringify(res));
                    }
                  });
              })
              wx.error(function (response) {
                  self.showShareMask = false
                  console.log(response)
                  self.toast(response)
              })
            } else {
                console.log(response.msg)
            }
          },
          error: function (err) {
            console.log(err)
          }
        });
      }
    });

  </script>
  <script src="//cms.zhongyingtougu.com/lib/viewStock.js?v=191220" type="text/javascript"></script>
  <script src="{{config('app.url_storage').'/assets/ugc/js/previewImage.js'}}"></script>
</body>
</html>
