<div class="hz-forward">
  <div class="forward-cont">
    <textarea class="comment" placeholder="在这里输入您的点评内容" rows="6" maxlength="500" id="forwardMessage"></textarea>
    <div class="share">
      <div class="share-img"></div>
      <div class="share-title"></div>
    </div>
  </div>
  <div class="forward-bottom">
    <div class="focus">
      <div class="send">
        <img class="emoji-img" src="{{config('app.url_storage').'/assets/ugc/images/emoji_Default.png'}}" alt="">
        <img class="send-img" src="{{config('app.url_storage').'/assets/ugc/images/send_Default.png'}}" alt="">
        <label class="alse-reply">
          <img src="{{config('app.url_storage').'/assets/ugc/images/UnSelect.png'}}" id="alseReplyimg"/>
          <label for="alseReply">同时评论</label>
        </label>
        <input type="checkbox" id="alseReply" hidden>
      </div>
    </div>
    <div class="fw-emoji-wrapper"></div>
  </div>
</div>

<script>

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

  // 同时评论
  $("#alseReplyimg").click(function(){
    if($("#alseReply").prop('checked') ){
        $("#alseReply").prop("checked",false);
        $("#alseReplyimg").attr("src", "{{config('app.url_storage').'/assets/ugc/images/UnSelect.png'}}");
        $("#alseReplyimg").attr("alt", "未选");
    } else{
        $("#alseReply").prop("checked",true);
        $("#alseReplyimg").attr("src", "{{config('app.url_storage').'/assets/ugc/images/Selected.png'}}");
        $("#alseReplyimg").attr("alt", "选中");
    }
  })

  // 唤起键盘
  $('.forward-bottom .input-box').click(function () {
    $('.forward-bottom').addClass('active')
    $('.forward-bottom').height("194px")
    if (!$('.fw-emoji-wrapper').is(':visible')) {
      $('.forward-bottom .emoji-img').attr("src", "{{config('app.url_storage').'/assets/ugc/images/emoji_Default.png'}}")
    }
  })

  $("#forwardMessage").focus(function () {
    $(".fw-emoji-wrapper").hide()
    $('.emoji-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/emoji_Default.png'}}")
    $('.forward-bottom').height("52px")
  })

  // 点击emoji
  $('.forward-bottom .emoji-img').click(function () {
    if ($(this).attr("src") == "{{config('app.url_storage').'/assets/ugc/images/emoji_Default.png'}}") {
      $('.emoji-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/emoji_Pressed.png'}}")
      $('.emoji-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/keyboard_Default.png'}}")
      $(".fw-emoji-wrapper").show()
      var emojiH = $(".fw-emoji-wrapper").height() + $(".forward-bottom").height()
      $(".forward-bottom").height(emojiH)
    } else {
      $('.emoji-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/keyboard_Pressed.png'}}")
      $('.emoji-img').attr('src', "{{config('app.url_storage').'/assets/ugc/images/emoji_Default.png'}}")
      $(".fw-emoji-wrapper").hide()
      $('.forward-bottom').height("52px")
    }
  })

  $(".share").click(function () {
    $('.reply-bottom').show()
    $('.reply').show()
    $('.wrap').show()
    $('.hz-forward').hide()
    $('title').text(articleTitle)
    history.go(-1)
  })

  // 转发
  function forwardToList() {
    if (!$("#forwardMessage").val()) {
      layer.open({
        content: "转发内容不能为空！",
        skin: 'msg',
        time: 2 //2秒后自动关闭
      });
      return false;
    }
    var params = {
      "content": $("#forwardMessage").val(),
      "ref_type": "{{ $type }}",
      "ref_id": "{{ $articleId }}",
      "ref_title": "{{ $articleTitle }}",
      "is_reply": $("#alseReply").is(':checked') ? 1 : 0,
      "author_user_id": "{{ $articleAuthorUserId }}" // 无作者user_id为0
    }
    params.ref_thumb = getUrlParam("pic_url") ? getUrlParam("pic_url") : "{{config('app.url_storage').'/assets/ugc/images/noPic.png'}}"

    $.ajax({
      url: "{{ config('app.url') }}/api/v2/interaction/forward/twitter",
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
          
          setTimeout(function () {
            $('.reply-bottom').show()
            $('.reply').show()
            $('.wrap').show()
            $('.hz-forward').hide()
            $('title').text(articleTitle)
            getReply()
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
          content: "转发错误，未转发成功！",
          skin: 'msg',
          time: 2 //2秒后自动关闭
        });
      }
    });
  }

</script>
