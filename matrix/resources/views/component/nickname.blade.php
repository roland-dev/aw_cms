<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
  <title>修改昵称</title>
  <style>
    body{
      background: #fff;
    }
    *{
      margin: 0;
      padding: 0;
    }
    .set-nick{
        padding: 0 15px;
    }
    .set-nick-name{
        border-bottom: 1px solid #e2e2e2;
    }
    .set-nick-name input{ 
        padding: 15px 0;
        border: none;
        outline: none;
        width: 100%;
    }
    .set-nick-tip{
        font-size: 13px;
        line-height: 18px;
        margin-top: 12px;
        color: #e5b43e;
    }
    .set-nick-tip img{
        width: 16px;
        height: 16px;
        vertical-align: -4px;
    }
    .set-nick-btn{
        margin: 30px auto 0; 
        color: #fff;
        background: #FEC5C6;
        max-width: 345px;
        height: 44px;
        line-height: 44px;
        text-align: center;
        border-radius: 22px;
    }
    .set-nick-btn:active{
        background: #C83034;
    }
  </style>
</head>
<body>
  <div class="set-nick">
    <div class="set-nick-name">
        <input id="nickName" type="text" maxlength="12" placeholder="昵称起的好，财运少不了，先起名再评论吧~">
    </div>
    <div class="set-nick-tip">
        <img src="{{config('app.url_storage').'/assets/ugc/images/Notice.png'}}" alt="">
        昵称仅允许汉字、数字、英文字母，不超过12个字符
    </div>
    <div class="set-nick-btn">确定</div>
</div>
</body>
<script type="text/javascript" src="{{config('app.url_storage').'/assets/ugc/js/zepto.min.js'}}"></script>
<script src="{{config('app.url_storage').'/assets/ugc/js/layer.js'}}"></script>
<script>
    $(function(){
        $("#nickName").bind("input propertychange",function(event){
            if ($("#nickName").val() == "") {
                $('.set-nick-btn').css('background', "#FEC5C6")
            } else {
                $('.set-nick-btn').css('background', "#fa3d41")
            }
        });

        $(".set-nick-btn").click(function(){
            var nickName = $("#nickName").val()
            if ($.trim(nickName).length > 0) {
                $.ajax({
                        url: '{{ config('uc.url') }}/api/v2/uc/person/info',
                        type: 'post',
                        contentType: 'application/json',
                        dataType: 'json',
                        data: '{"nickName":"' + nickName + '"}',
                        crossDomain: true,
                        xhrFields: {
                            withCredentials: true
                        },
                        success: function (res) {
                            if (res.code == 0) {
                                layer.open({
                                    content: "昵称设置成功",
                                    skin: 'msg',
                                    time: 2 //2秒后自动关闭
                                });
                                // 添加回退刷新标识
                                sessionStorage.setItem('zytg_need_refresh', 'nickname');
                                setTimeout(function(){
                                window.history.go(-1);
                                }, 2000);
                            } else {
                                alert(res.msg)
                            }
                        },
                        error: function (err) {
                            if (err.status == 401) {
                            layer.open({
                                content: "用户未登录",
                                skin: 'msg',
                                time: 2 //2秒后自动关闭
                            });
                            } else {
                            layer.open({
                                content: "网络异常",
                                skin: 'msg',
                                time: 2 //2秒后自动关闭
                            });
                            }
                        }
                    });
            } else if($.trim(nickName).length > 12){
                layer.open({
                    content: "昵称不能超过12个字",
                    skin: 'msg',
                    time: 2 //2秒后自动关闭
                });
            } else {
                layer.open({
                    content: "昵称不能为空！",
                    skin: 'msg',
                    time: 2 //2秒后自动关闭
                });
            }
        })
    })
    
</script>
</html>



