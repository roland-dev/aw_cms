<link rel="stylesheet" href="{{config('app.url_storage').'/assets/ugc/css/wEmoji.css'}}">
<script src="{{config('app.url_storage').'/assets/ugc/js/wEmoji.js'}}"></script>

<div class="reply">
  <div class="reply-title"><img src="{{config('app.url_storage').'/assets/ugc/images/reply_area.png'}}" alt=""></div>
  <div class="reply-list" id="replyList"></div>
  <div class="reply-empty">
    <div class="empty-img"><img src="{{config('app.url_storage').'/assets/ugc/images/pic_Comment.png'}}" alt=""></div>
    <div class="empty-tip">人生处处是战场，评论也要争第一！</div>
  </div>
</div>
@if ($isReply)
<div class="reply-bottom">
  <div class="focus">
    <div class="textarea-box">
      <textarea id="replyMessage" placeholder="友善发言的人运气不会太差..." style="font-size: 15px"></textarea>
    </div>
    @if ($isTeacher)
    <div class="send">
      <label class="alse-reply">
        <img src="{{config('app.url_storage').'/assets/ugc/images/UnSelect.png'}}" id="alseReplyimg"/>
        <label for="alseReply">同时转发到解盘</label>
      </label>
      <label class="for-all">
        <img src="{{config('app.url_storage').'/assets/ugc/images/Selected.png'}}" id="forAllImg"/>
        <label for="forAll">全员可见</label>
      </label>
      <img class="send-img" src="{{config('app.url_storage').'/assets/ugc/images/send_Disabled.png'}}" alt="" style="margin-left: 16px;">
      <img class="emoji-img" src="{{config('app.url_storage').'/assets/ugc/images/emoji_Default.png'}}" alt="" style="float: right; margin-right: 0">
    </div>
    @else    
    <div class="send">
      <img class="emoji-img" src="{{config('app.url_storage').'/assets/ugc/images/emoji_Default.png'}}" alt="">
      <img class="send-img" src="{{config('app.url_storage').'/assets/ugc/images/send_Disabled.png'}}" alt="">
      <span class="textarea-max" id="limitLength">500</span>
    </div>
    @endif
  </div>
  <div class="blur no-right">
    <div class="input-box">
      <div>友善发言的人运气不会太差...</div>
    </div>
    <div class="praise-img">
      @if (!$isLike && $likeSum == 0)
      <span class="praise-like"></span>
      <img src="{{config('app.url_storage').'/assets/ugc/images/like_Normal.png'}}" alt="">
      @elseif (!$isLike && $likeSum != 0)
      <span class="praise-like">{{ $likeSum }}</span>
      <img src="{{config('app.url_storage').'/assets/ugc/images/like_Normal.png'}}" alt="">
      @else
      <span class="praise-like clicked">{{ $likeSum }}</span>
      <img src="{{config('app.url_storage').'/assets/ugc/images/like_Highlight.png'}}" alt="">
      @endif
    </div>
  </div>
  <div class="reply-emoji-wrapper"></div>
</div>
@else
<div class="download-bottom" style="z-index: 99">
  <div class="logo-img"><img src="{{config('app.url_storage').'/assets/ugc/images/logo_zycft.png'}}" alt=""></div>
  <div class="logo-name">
    <div class="name-title">众赢财富通</div>
    <div class="name-abstract">众赢财富通，投资更轻松</div>
  </div>
  <div class="download-btn" onclick="downloadApp()">免费下载</div>
</div>
@endif
@if ($isTeacher)
<div class="reply-box__warpper">
  <div class="reply-box__dialog">
    <div class="reply-menu">
      <div class="teacher-reply">回复</div>
      <div class="divider"></div>
      <div class="sticky">评论置顶</div>
    </div>
    <div class="cancel">取消</div>
  </div>
</div>
@endif
@if(!$isReply &&  $type == "course")
<div class="reply-bottom">
  <div class="blur no-right">
    <div class="static-input-box">
      <div>下载app即可互动评论哦！</div>
    </div>
    <div class="praise-img">
      @if ($isLike)
      <span class="praise-like clicked">{{ $likeSum }}</span>
      <img src="{{config('app.url_storage').'/assets/ugc/images/like_Highlight.png'}}" alt="">
      @else
      <span class="praise-like">{{ $likeSum }}</span>
      <img src="{{config('app.url_storage').'/assets/ugc/images/like_Normal.png'}}" alt="">
      @endif
    </div>
  </div>
</div>

@endif

<script>
  var UC_API_URL = "{{ config('uc.url') }}"
  var isTeacherReply = false;
  var isReplying = false;               // 评论中
  var isReplyVoteIng = false;           // 评论点赞中
  var teacherReplyId = ""
  var teacherReplyContent = ""
  var teacherReplyOpenId = ""
  var placedStatus = 0             // 置顶状态   0 未置顶  1 置顶
  var miniRefresh;                 // 下拉刷新列表
  var isLoadReply = false;
  var lastReplyId = 0;             // 最后回复id 
  var replyPageSize = 20;          // 评论分页
  var isAlseForward = false;         // 评论同时转发
  var isForAll = 1;               // 回复评论全员可见
  var isAuth = 0;                // 是否是当前文章页作者

  var articleTitle = $("title").html()
  var u = navigator.userAgent,
    app = navigator.appVersion;
  var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); // ios终端
  var isApp = !!u.match(/zytg/);
  var weForward;
  var selectEmojiing = false;
  var textareaLeftPos,textareaRightPos;


  $(function () {
    // 判断是否需要刷新
    var refresh = sessionStorage.getItem('zytg_need_refresh');
    if (refresh) {
      sessionStorage.removeItem('zytg_need_refresh')
      location.reload();
    } 
    // 判断ios设备
    if (isiOS) {
      $('textarea').focus(function () {
        window.setTimeout('scrollBottom()', 500);
      });
    }

    // 判断是否在app内 
    if (isApp || document.body.clientWidth >= 1000) {
      $(".download-bottom").hide()
    } else {
      @if (!$isReply)
      $('.wrap').css("marginTop", "60px")
      @endif
    }

    // 判断是否有评论内容
    if (getLocalStorage("ZYTG_UGC_REPLY_{{$type}}") && getLocalStorage("ZYTG_UGC_REPLY_{{$type}}").id == "{{$articleId}}") {
      $("#replyMessage").val(getLocalStorage("ZYTG_UGC_REPLY_{{$type}}").content)
      $('.reply-bottom .input-box div').text($('#replyMessage').val())
    } else {
      localStorage.removeItem("ZYTG_UGC_REPLY_{{$type}}")
    }


    // 初始化emoji表情库
    var we = new wantEmoji({
      wrapper: ".reply-emoji-wrapper",
      callback: function (emojiCode) {
        var message = $("#replyMessage").val()
        var totleLength = message.length + emojiCode.length                // 添加emoji后字符总长度
        // 输入emoji字符，更改发送状态
        $('.reply-bottom .send-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/send_Default.png'}}")
        if (!selectEmojiing) {
          textareaLeftPos = getCaretPosition($("#replyMessage")[0])
          textareaRightPos = message.length - textareaLeftPos 
          selectEmojiing = true
        }
        // 判断表情是否在中间部分插入
        if (message.length == textareaLeftPos) {
          $("#replyMessage").val(message + emojiCode)
        } else {
          $("#replyMessage").val(message.substring(0, message.length - textareaRightPos) + emojiCode + message.substring(message.length - textareaRightPos, message.length ))
        }
        $('.reply-bottom .input-box div').text($('#replyMessage').val())
        // 统计输入区限制字数
        $("#limitLength").text(500 - totleLength)
        if(500 < totleLength){
          $("#limitLength").css("color","red")
        } else {
          $("#limitLength").css("color","#999")
        }
        // 存储评论内容到本地
        var replyContent = {
          id : "{{$articleId}}",
          content: $("#replyMessage").val()
        }
        setLocalStorage("ZYTG_UGC_REPLY_{{$type}}", replyContent)
      },
      autoInit: true
    });

    // 点击点赞按钮
    $('.reply-bottom .praise-img img').click(function () {
        clickLike()
    })

    // 点击发送按钮
    $('.reply-bottom .send-img').click(function () {
      if ($.trim($("#replyMessage").val()) == "") {
        layer.open({
          content: "评论内容不可以为空",
          skin: 'msg',
          time: 2 //2秒后自动关闭
        });
      } else {
        $('.reply-bottom .send-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/send_Pressed.png'}}")
        setTimeout(function () {
          $('.reply-bottom .send-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/send_Default.png'}}")
        }, 300)
        if (isAlseForward) {
          forwardToList()
        } else {
          sendReply()
        }
      }
    })

    // 评论输入区聚焦事件
    $('#replyMessage').focus(function () {
      selectEmojiing = false
    });

    // 唤起键盘
    $('.reply-bottom .input-box').click(function () {
      isTeacherReply = false
      $(".alse-reply").show()
      $(".for-all").hide()
      if ($.trim($("#replyMessage").val()) == "") {
        $('.reply-bottom .send-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/send_Disabled.png'}}")
        // 统计输入区限制字数初始化
        $("#limitLength").text(500)
      } else {
        $('.reply-bottom .send-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/send_Default.png'}}")
        // 统计输入区限制字数初始化
        $("#limitLength").text(500 - $("#replyMessage").val().length)
        if(500 < $("#replyMessage").val().length){
          $("#limitLength").css("color","red")
        } else {
          $("#limitLength").css("color","#999")
        }
      }
      $('.reply-bottom').addClass('active')
      $('.reply-bottom').height("194px")
      $('#replyMessage').focus()
      if (!$('.reply-emoji-wrapper').css('display') == "block") {
        $('.reply-bottom .emoji-img').attr("src", "{{config('app.url_storage').'/assets/ugc/images/emoji_Default.png'}}")
      }
    })

    // 键盘收起
    $('#replyMessage').focus(function () {
      if ($('.reply-emoji-wrapper').css('display') == "block") {
        $('.reply-bottom .emoji-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/keyboard_Pressed.png'}}")
        $('.reply-bottom .emoji-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/emoji_Default.png'}}")
        var emojiH = $(".reply-bottom").height() - $(".reply-emoji-wrapper").height()
        $(".reply-bottom").height(emojiH)
        $(".reply-emoji-wrapper").hide()
      }
    })

    $("#replyMessage").bind("input propertychange", function (event) {
      if ($.trim($("#replyMessage").val()) == "") {
        setTimeout(function(){
          if ($("#replyMessage").val().length > 0) {
            $('.reply-bottom .send-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/send_Default.png'}}")
            $('.reply-bottom .input-box div').text($('#replyMessage').val())
            // 统计输入区限制字数初始化
            $("#limitLength").text(500 - $("#replyMessage").val().length)
          } else {
            $('.reply-bottom .send-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/send_Disabled.png'}}")
            $('.reply-bottom .input-box div').text("友善发言的人运气不会太差...")
            // 统计输入区限制字数初始化
            localStorage.removeItem("ZYTG_UGC_REPLY_{{$type}}")
            $("#limitLength").text(500)
          }
        },100)

      } else {
        $('.reply-bottom .send-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/send_Default.png'}}")
        $('.reply-bottom .input-box div').text($('#replyMessage').val())
        // 统计输入区限制字数初始化
        $("#limitLength").text(500 - $("#replyMessage").val().length)
        if(500 < $("#replyMessage").val().length){
          $("#limitLength").css("color","red")
        } else {
          $("#limitLength").css("color","#999")
        }
        // 存储评论内容到本地
        var replyContent = {
          id : "{{$articleId}}",
          content: $("#replyMessage").val()
        }
        setLocalStorage("ZYTG_UGC_REPLY_{{$type}}", replyContent)
      }
      
    });

    // 点击emoji
    $('.reply-bottom .emoji-img').click(function () {
      if ($(this).attr("src") == "{{config('app.url_storage').'/assets/ugc/images/emoji_Default.png'}}") {
        $('.reply-bottom .emoji-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/emoji_Pressed.png'}}")
        $('.reply-bottom .emoji-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/keyboard_Default.png'}}")
        $(".reply-emoji-wrapper").show()
        var emojiH = $(".reply-emoji-wrapper").height() + $(".reply-bottom").height()
        $(".reply-bottom").height(emojiH)
      } else {
        $('.reply-bottom .emoji-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/keyboard_Pressed.png'}}")
        $('.reply-bottom .emoji-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/emoji_Default.png'}}")
        var emojiH = $(".reply-bottom").height() - $(".reply-emoji-wrapper").height()
        $(".reply-emoji-wrapper").hide()
        $(".reply-bottom").height(emojiH)
        $("#replyMessage").focus()
      }

    })

    $(".wrap").click(function () {
      resetReplyMessage()
    })

    $(".reply").click(function () {
      if ($('.reply-bottom').height() > 60) {
        setTimeout(() => {
          resetReplyMessage()
        }, 300);
      } 
    })

      // 监听滚动刷新获取评论分页
    $(window).scroll(function(event){
      if ($(".reply").css("display") == "block" && !$(".reply-bottom").hasClass("active")) {
        if(checkScrollHeight() && isLoadReply){
          getReply(true)
          console.log("快到底了")
        }
      }
    });

    // 如果是老师，点击评论回复评论 
    $('.reply').on("click", ".reply-content", function () {
      if(!isAuth) {
        return false;
      }
      setTimeout(() => {
        if ($('.reply-bottom').height() < 60 && !isReplyVoteIng) {
          $(this).parent().addClass("active")
        }
      }, 300);
      
      setTimeout(() => {
        if ($('.reply-bottom').height() < 60 && !isReplyVoteIng) {
          $(this).parent().removeClass("active")
          teacherReplyId = $(this).attr("ref-id")
          var rContent = $(this).html()
          if(rContent.indexOf("<div") > -1) {
            rContent = rContent.substring(0, rContent.indexOf("<div"))
          }
          teacherReplyContent = emojiReverse(rContent)
          teacherReplyOpenId = $(this).attr("open-id")
          placedStatus = $(this).attr("placed-status")
          $(".sticky").html(placedStatus != "1" ? "评论置顶" : "取消置顶")   
          $('.reply-box__warpper').show()
        }
      }, 600);
      
    })

    // 评论及评论回复点赞
    $('.reply').on("click", ".reply-like", function () {
      isReplyVoteIng = true
      var recordId = $(this).attr("ref-id")
      $.ajax({
        type: "put",
        url: "{{ config('app.ajax_url') }}/api/v2/interaction/vote/ugc?record_id=" + recordId + "&type=article_reply&udid=" + cryptonymKey,
        dataType: "json",
        @if($sessionId)
        headers: {
          "X-SessionId": "{{ $sessionId }}",
        },
        @endif
        xhrFields: {
          withCredentials: true
        },
        success: res => {
          if (res.code == 0) {
            var likeSum = res.data[0].like_sum ? res.data[0].like_sum : "赞"
            if (res.data[0].is_like) {
              $(this).addClass("vote")
              if(res.data[0].like_sum) {

              }
              $(this).html("<img src=\"{{config('app.url_storage').'/assets/ugc/images/reply_like_high.png'}}\" alt=''>" + likeSum)
            } else {
              $(this).removeClass("vote")
              $(this).html("<img src=\"{{config('app.url_storage').'/assets/ugc/images/reply_like.png'}}\" alt=''>" + likeSum)
            }
          } else {
            layer.open({
              content: res.msg,
              skin: 'msg',
              time: 1 //2秒后自动关闭
            });
          }
                    setTimeout(() => {
            isReplyVoteIng = false
          }, 1000);
        },
        error: error => {
          setTimeout(() => {
            isReplyVoteIng = false
          }, 1000);
          console.log(error)
        }
      })
    })

    // 点击牛人回复评论 
    $('.reply-box__dialog .teacher-reply').on("click", function () {
      $('.reply-box__warpper').hide()
      var _this = this
      setTimeout(function () {
        isTeacherReply = true
        $(".alse-reply").hide()
        $(".for-all").show()
        isAlseForward = false;
        isForAll = 1
        $("#alseReplyimg").attr("src", "{{config('app.url_storage').'/assets/ugc/images/UnSelect.png'}}");
        $("#alseReplyimg").attr("alt", "未选");
        $("#replyMessage").val("")
        $('.reply-bottom').addClass('active')
        $('.reply-bottom').height("194px")
        $('#replyMessage').focus()
        if (!$('.reply-emoji-wrapper').css('display') == "block") {
          $('.reply-bottom .emoji-img').attr("src", "{{config('app.url_storage').'/assets/ugc/images/emoji_Default.png'}}")
        }
      }, 200);
    })

    // 评论置顶
    $('.reply-box__dialog .sticky').on("click", function () {
      $.ajax({
        type: "get",
        url: "{{ config('app.ajax_url') }}/api/v2/interaction/reply/place?reply_id=" + teacherReplyId,
        dataType: "json",
        @if($sessionId)
        headers: {
          "X-SessionId": "{{ $sessionId }}",
        },
        @endif
        xhrFields: {
          withCredentials: true
        },
        success: function (res) {
          if (res.code == 0) {
            $('.reply-box__warpper').hide()
            getReply()
            setTimeout(function () {
              clearReplyContent()
              window.scrollTo(0, $("#replyList").offset().top - 80)
            }, 1000);
          } else {
            layer.open({
              content: res.msg,
              skin: 'msg',
              time: 1 //2秒后自动关闭
            });
          }
        },
        error: function (error) {
          console.log(error)
        }
      })
    })

    // 取消回复浮层
    $('.reply-box__dialog .cancel').on("click", function () {
      $('.reply-box__warpper').hide()
      teacherReplyId = ""
      teacherReplyOpenId = ""
    })

    // 同时评论
    $('.reply-bottom').on("click", ".alse-reply", function () {
      if(isAlseForward){
          isAlseForward = false
          $("#alseReplyimg").attr("src", "{{config('app.url_storage').'/assets/ugc/images/UnSelect.png'}}");
          $("#alseReplyimg").attr("alt", "未选");
      } else{
          isAlseForward = true
          $("#alseReplyimg").attr("src", "{{config('app.url_storage').'/assets/ugc/images/Selected.png'}}");
          $("#alseReplyimg").attr("alt", "选中");
      }
    })

    // 全员可见
    $('.reply-bottom').on("click", ".for-all", function () {
      if(isForAll){
          isForAll = 0
          $("#forAllImg").attr("src", "{{config('app.url_storage').'/assets/ugc/images/UnSelect.png'}}");
          $("#forAllImg").attr("alt", "未选");
      } else{
          isForAll = 1
          $("#forAllImg").attr("src", "{{config('app.url_storage').'/assets/ugc/images/Selected.png'}}");
          $("#forAllImg").attr("alt", "选中");
      }
    })

    // 发送评论
    function sendReply() {
      if (isReplying) {
        return false;
      }
      // 评论字数判断
      if ($("#replyMessage").val().length > 500) {
        layer.open({
          content: "评论不能超过500字哦",
          skin: 'msg',
          time: 2 //2秒后自动关闭
        });
        return false;
      }
      
      isReplying = true
      if (isTeacherReply) {
        var params = {
          "type": "{{ $type }}",
          "article_id": "{{ $articleId }}",
          "article_title": "{{ $articleTitle }}",
          "article_author_user_id": "{{ $articleAuthorUserId }}",
          "content": $("#replyMessage").val(),
          "ref_id": teacherReplyId,
          "ref_content": teacherReplyContent,
          "ref_open_id": teacherReplyOpenId,
          "is_all_visible": isForAll
        }
      } else {
        var params = {
          "type": "{{ $type }}",
          "article_id": "{{ $articleId }}",
          "article_title": "{{ $articleTitle }}",
          "article_author_user_id": "{{ $articleAuthorUserId }}",
          "content": $("#replyMessage").val()
        }
      }

      $.ajax({
        type: "post",
        url: "{{ config('app.ajax_url') }}/api/v2/interaction/reply",
        dataType: "json",
        data: params,
        @if($sessionId)
        headers: {
          "X-SessionId": "{{ $sessionId }}",
        },
        @endif
        xhrFields: {
          withCredentials: true
        },
        success: function (res) {
          if (res.code == 0) {
            layer.open({
              content: "评论成功！",
              skin: 'msg',
              time: 2 //2秒后自动关闭
            });
            getReply()
            setTimeout(function () {
              clearReplyContent()
              window.scrollTo(0, $("#replyList").offset().top - 80)
            }, 1000);
          } else if(res.code == 230001) {
            layer.open({
              content: "发表评论请先设置昵称!",
              skin: 'msg',
              time: 2 //2秒后自动关闭
            });
            setTimeout(function(){
              window.location.href = "{{ config('app.url') }}/api/v2/client/rechristen"
            }, 1500);
          } else {
            layer.open({
              content: res.msg,
              skin: 'msg',
              time: 2 //2秒后自动关闭
            });
          }
          setTimeout(function () {
            isReplying = false
          }, 800);
        },
        error: function () {
          setTimeout(function () {
            isReplying = false
          }, 800);
          console.log("评论发送失败")
        }
      })
    }
  })

  function resetReplyMessage() {
    $('.reply-bottom').removeClass('active')
    $('.reply-bottom').height("52px")
    $(".reply-emoji-wrapper").hide()
    if ($('#replyMessage').val() == "") {
      $('.reply-bottom .input-box div').text("友善发言的人运气不会太差...")
    } else {
      $('.reply-bottom .input-box div').text($('#replyMessage').val())
    }
    $('.reply-bottom .emoji-img').attr("src", "{{config('app.url_storage').'/assets/ugc/images/emoji_Default.png'}}")
  }

  // 获取评论数
  function getReply(refresh) {
    isLoadReply = false
    if (!refresh) {
      $(".reply-list").html("")
      lastReplyId = 0
    }
    // app 内
    if (isApp || document.body.clientWidth >= 1000) {
      $(".download-bottom").hide()
    } else {
       $(".download-bottom").show()
    }
    $.ajax({
      type: "get",
      url: "{{ config('app.ajax_url') }}/api/v2/interaction/reply/newlist?type={{$type}}&article_id={{$articleId}}&last_reply_id="+ lastReplyId +"&page_size=" + replyPageSize + "&t=" + (new Date().getTime()),
      dataType: "json",
      xhrFields: {
        withCredentials: true
      },
      @if($sessionId)
      headers: {
        "X-SessionId": "{{ $sessionId }}",
      },
      @endif
      success: function (res) {
        if (res.code == 0) {
          $('.reply').show()
          $('.reply-empty').hide()
          
          var list = res.data.reply_list;
          if (lastReplyId > 0 || list.length > 0) {
            isAuth = list[0].is_auth
            for (var i = 0; i < list.length; i++) {
              var replyItem = '<div class="reply-item"><div class="reply-author">'

              if (!list[i].icon_url) {
                replyItem += '<img src="{{config('app.url_storage').'/assets/ugc/images/one.png'}}" class="reply-port" alt="">'
              } else {
                replyItem += '<img src="' + list[i].icon_url + '" class="reply-port" alt="">'
              }

              replyItem += '<span>' + list[i].nickname + '</span>'

              if (list[i].is_teacher) {
                replyItem += '<img src="{{config('app.url_storage').'/assets/ugc/images/label_Teacher.png'}}" class="teacher-port" alt="">'
              }
              // 置顶图标
              if (list[i].placed_status) {
                replyItem += '<img src="{{config('app.url_storage').'/assets/ugc/images/Topping.png'}}" class="placed-port" alt="">'
              }
              // 评论点赞区域
              var likeSum = list[i].like_sum ? list[i].like_sum : "赞"
              if (list[i].is_like) {
                replyItem += '<b class="reply-like vote" ref-id="' + list[i].id + '"><img src="{{config('app.url_storage').'/assets/ugc/images/reply_like_high.png'}}" alt="">' + likeSum + '</b>'
              } else {
                replyItem += '<b class="reply-like" ref-id="' + list[i].id + '"><img src="{{config('app.url_storage').'/assets/ugc/images/reply_like.png'}}" alt="">' + likeSum + '</b>'
              }
              

              // 评论内容区域
              replyItem += '</div><div class="reply-content" ref-id="' + list[i].id + '" open-id="' + list[i].open_id + '"placed-status="' + list[i].placed_status + '">' + emojiExplain(list[i].content)

              // 评论回复区域
              if (list[i].ref_content_list) {
                var refListContent = ""
                replyItem += '<div class="ref-area">'
                for (var j = 0; j < list[i].ref_content_list.length; j++) {
                  var refList = list[i].ref_content_list[j];
                  refListContent += '<div class="ref-name">' + refList.nickname
                 
                  if (refList.is_teacher) {
                    refListContent += '<img src="{{config('app.url_storage').'/assets/ugc/images/label_Teacher.png'}}" class="ref-teacher-port" alt="">'
                  }

                  // 评论回复点赞区域
                  if (refList.is_like) {
                    refListContent += '<b class="reply-like vote" ref-id="' + refList.id + '"><img src="{{config('app.url_storage').'/assets/ugc/images/reply_like_high.png'}}" alt="">' + refList.like_sum + '</b>'
                  } else {
                    refListContent += '<b class="reply-like" ref-id="' + refList.id + '"><img src="{{config('app.url_storage').'/assets/ugc/images/reply_like.png'}}" alt="">' + refList.like_sum + '</b>'
                  }

                  refListContent += '</div><div class="ref-content">' + emojiExplain(refList.content) + '</div>';
                }
                replyItem += refListContent + '</div>'
              }

              replyItem += '</div>'

              if (i == list.length - 1) {
                replyItem += '</div>';
              } else {
                replyItem += '<div class="divider"></div></div>';
              }

              $(".reply-list").append(replyItem)
            }
            @if($type == "course")
            $(".reply-title").hide()
            @endif
            $(".reply-list").show()
            if (list.length == replyPageSize) {
              lastReplyId = list[list.length-1].id
              isLoadReply = true
            } else {
              isLoadReply = false
            }
          } else {
            $(".reply-title").hide()
            $('.reply-empty').show()
          }
        } else {
          layer.open({
            content: res.msg,
            skin: 'msg',
            time: 2 //2秒后自动关闭
          });
        }
      },
      error: function () {
        console.log("获取评论错误")
      }
    })
  }


  // 转发到解盘
  function forwardToList() {
    if (isReplying) {
      return false;
    }
    isReplying = true
    var params = {
      "content": $("#replyMessage").val(),
      "ref_type": "{{ $type }}",
      "ref_id": "{{ $articleId }}",
      "ref_title": "{{ $articleTitle }}",
      "is_reply": 1,
      "author_user_id": "{{ $articleAuthorUserId }}" // 无作者user_id为0
    }
    @if($forwardImg)
    params.ref_thumb = "{{ $forwardImg }}"
    @else
    params.ref_thumb = ""
    @endif
    $.ajax({
      url: "{{ config('app.ajax_url') }}/api/v2/interaction/forward/twitter",
      type: 'post',
      @if($sessionId)
      headers: {
        "X-SessionId": "{{ $sessionId }}",
      },
      @endif
      data: params,
      success: function (res) {
        if (res.code === 0) {
          layer.open({
            content: res.msg,
            skin: 'msg',
            time: 1 //2秒后自动关闭
          });
          getReply()
          setTimeout(function () {
            clearReplyContent()
            isAlseForward = false
            $("#alseReplyimg").attr("src", "{{config('app.url_storage').'/assets/ugc/images/UnSelect.png'}}");
            $("#alseReplyimg").attr("alt", "未选");
            window.scrollTo(0, $("#replyList").offset().top - 80)
          }, 1000);
        } else {
          layer.open({
            content: res.msg,
            skin: 'msg',
            time: 2 //2秒后自动关闭
          });
        } 
        setTimeout(function () {
          isReplying = false
        }, 800);
      },
      error: function (err) {
        layer.open({
          content: "转发错误，未转发成功！",
          skin: 'msg',
          time: 2 //2秒后自动关闭
        });
        setTimeout(function () {
          isReplying = false
        }, 800);
      }
    });
  }

  // 获取页面高度
  function getScrollTop() {
    var scrollTop = 0;
    if(document.documentElement && document.documentElement.scrollTop) {
      scrollTop = document.documentElement.scrollTop;
    } else if(document.body) {
      scrollTop = document.body.scrollTop;
    }
    return scrollTop;
  }

  // 获取当前可视高度
  function getClientHeight() {
    return document.documentElement.clientHeight;
  }

  // 文档完整的高度
  function getScrollHeight() {
    return Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
  }

  function checkScrollHeight(){
      return getScrollHeight() - getClientHeight() -getScrollTop() <= 120;
  }

  // 下载app
  function downloadApp() {
    window.location.href =
      "http://www.zhongyingtougu.com/version/download/?ADTAG=631.841&v=2.4.267&from=singlemessage";
  }

  // 时间倒计时
  function recentDate(time, now) {
    if (now) {
      var b = now
    } else {
      b = new Date().getTime()
    }
    var a = new Date(time.replace(/-/g,'/')).getTime()
    var diffTime = (b - a) / 1000

    // 判断发布时间与当前时间差
    if (diffTime <= 1) {
      return "1秒前"
    } else if (diffTime > 1 && diffTime < 60) {
      return Math.ceil(diffTime) + "秒前"
    } else if (diffTime > 60 && diffTime <= 60 * 60) {
      return parseInt(diffTime / 60) + "分钟前"
    } else if (diffTime > 60 * 60 && diffTime <= 60 * 60 * 24) {
      return parseInt(diffTime / (60 * 60)) + "小时前"
      // } else if (diffTime > 60*60*24 && diffTime <= 60*60*24*30) {
      //     return parseInt(diffTime/(60*60*24)) + "天前"
      // } else if (diffTime > 60*60*24*30 && diffTime <= 60*60*24*30*12) {
      //     return parseInt(diffTime/(60*60*24*30)) + "个月前"
      // } else if (diffTime > 60*60*24*30*12) {
      //     return parseInt(diffTime/(60*60*24*30*12)) + "年前"
    } else {
      return time.substring(5, 16)
    }
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
  // 获取textarea光标位置
  function getCaretPosition(obj) {
    var result = 0;
    if(obj.selectionStart >= 0) { //IE以外 
      result = obj.selectionStart;
    } else { //IE 
      try {
        var rng;
        if(obj.tagName == "textarea") { //TEXTAREA 
          rng = event.srcElement.createTextRange();
          rng.moveToPoint(event.x, event.y);
        } else { //Text 
          rng = document.selection.createRange();
        }
        rng.moveStart("character", -event.srcElement.value.length);
        result = rng.text.length;
      } catch(e) {
        throw new Error(10, "asdasdasd");
      }
    }
    return result;
  }

  // 设置textarea光标位置
  function setCaretPosition(tObj, sPos) {
    if(tObj && sPos) {
      if(tObj.setSelectionRange) {
        setTimeout(function() {
          tObj.setSelectionRange(sPos, sPos);
          tObj.focus();
        }, 0);
      } else if(tObj.createTextRange) {
        var rng = tObj.createTextRange();
        rng.move('character', sPos);
        rng.select();
      }
    }
  }

  // 解释emoji表情地址
  function emojiExplain(str) {
    var emojiArr = [
      "微笑", "撇嘴", "色", "发呆", "得意", "流泪", "害羞", "闭嘴", "睡", "大哭", "尴尬", "发怒", "调皮", "呲牙", "惊讶", "难过", "囧", "抓狂", "吐",
      "偷笑",
      "愉快", "白眼", "傲慢", "困", "惊恐", "流汗", "憨笑", "悠闲", "奋斗", "咒骂", "疑问", "嘘", "晕", "衰", "骷髅", "敲打", "再见", "擦汗", "抠鼻",
      "鼓掌",
      "坏笑", "左哼哼", "右哼哼", "哈欠", "鄙视", "委屈", "快哭了", "阴险", "亲亲", "可怜", "菜刀", "西瓜", "啤酒", "咖啡", "猪头", "玫瑰", "凋谢", "嘴唇",
      "爱心", "心碎",
      "蛋糕", "炸弹", "便便", "月亮", "太阳", "拥抱", "强", "弱", "握手", "胜利", "抱拳", "勾引", "拳头", "OK", "跳跳", "发抖", "怄火", "转圈",
      "高兴", "口罩",
      "笑哭", "吐舌头", "傻呆", "恐惧", "悲伤", "不屑", "嘿哈", "捂脸", "奸笑", "机智", "皱眉", "耶", "鬼脸", "合十", "加油", "庆祝", "礼物", "红包",
      "鸡"
    ]
    var path = "http://qn-image.zhongyingtougu.com/emoji/"
    var reg = new RegExp('\\[(.+?)\\]', "g")
    return str.replace(reg, function (str, target) {
      if (str.length < 6) {
        var emojiname = str.substring(1, str.length - 1)
        var isEmoji = emojiArr.indexOf(emojiname)
        if (isEmoji > -1) {
          var iconUrl = '<img src="' + path + emojiname + '.png"/>'
          return iconUrl;
        } else {
          return str;
        }
      } else {
        return str;
      }
    });
  }

  // 逆向转保存的图片文件
  function emojiReverse(str){
    var reg = new RegExp('<img src="http://qn-image.zhongyingtougu.com/emoji/', "g")
    var reg2 = new RegExp('.png">', "g")
    var newStr = str.replace(reg, function (str, target) {
      return "["
    });
    return newStr.replace(reg2, function (str, target) {
      return "]"
    });
  }

  // 定位依附下边界
  function scrollBottom() {
    window.scrollTo(0, $('body').height());
  }

  // 获取url中的参数
  function getUrlParam(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
    var r = window.location.search.substr(1).match(reg); //匹配目标参数
    if (r != null) return unescape(r[2]);
    return null; //返回参数值
  };

  // 存取localStorage
  function setLocalStorage(name, arr) {
    localStorage.setItem(name, JSON.stringify(arr));
  }
  function getLocalStorage(name) {
    return JSON.parse(localStorage.getItem(name));
  }

  // 清除回复框内容
  function clearReplyContent() {
    localStorage.removeItem("ZYTG_UGC_REPLY_{{$type}}")
    $('#replyMessage').val("")
    resetReplyMessage()
  }

</script>
