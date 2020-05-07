<script src="//res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
$(function(){
  if (isWeiXin()) {
    $.ajax({
      url: '{{ config('app.ajax_url') }}/api/v1/qywxUser/wxshare',
      type: 'post',
      data: {"url": window.location.href},
      crossDomain: true,
      xhrFields: {
          withCredentials: true
      },
      success: function (res) {
        console.log(res)
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
                  title: "{{ $articleTitle }}",
                  @if ($desc)
                  desc:  '{{ str_replace(array("\r\n", "\r", "\n"), "", $desc) }}',
                  @else
                  desc:  "",
                  @endif
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

  function isWeiXin(){
    var ua = window.navigator.userAgent.toLowerCase();
    return ua.indexOf('micromessenger') > -1 ? true : false
  }
})
</script>
