var smsCountDown;
var voiceCountDown;
var isSmsCountDown;
var isVoiceCountDown;
var smsSecond = 0;
var voiceSecond = 0;
var telephone = $("#telephone");
var smsCode = $("#smsCode");
var smsToken = ''; // 验证码token
var voiceBtn = $(".voiceCode_btn");
var openAccountUrl = "openAccount.html"
var openAccountReviewingUrl = "reviewing.html"

// 发送短信验证码
$(".smsCode_btn").click(function () {
    if (smsSecond > 0 || isVoiceCountDown) {
        return false;
    }
    // 检验手机号码
    if (checkSubmitTelephone()) {
        // 请求手机短信验证码
        var telephoneData = '{"mobile":' + telephone.val() + ',"product": "rfzq","opCode": "signin"}'
        $.ajax({
            type: "post",
            url: UC_API_URL + "/sms/signin",
            xhrFields: {
                withCredentials: true // 跨域请求
            },
            dataType: "json",
            contentType: "application/json",
            data: telephoneData, //连同请求发送到服务器的数据。
            success: function (data) {
                //数据成功返回时执行次回调函数
                if (data.code === 0) {
                    saveMobile(telephone.val())
                    smsToken = data.data.smsToken
                    // sessionStorage中存放smsToken的值
                    sessionStorage.setItem("smsToken", data.data.smsToken)

                    if (data.data.smsCode !== "") {
                        smsCode.val(data.data.smsCode)
                    }
                    // 开启60s倒计时
                    setSmsCountDown();
                } else {
                    if (data.code === 20600) {
                        layer.open({
                            content: '该手机号已有众赢财富通账号',
                            btn: '确认'
                        });
                    } else {
                        layer.open({
                            content: data.msg,
                            btn: '确认'
                        });
                    }
                }
            },
            error: function (data) {
                console.log(data.msg)
            }
        });
    }
})

// 设置短信倒计时
function setSmsCountDown() {
    isSmsCountDown = true;
    smsSecond = 60;
    $(".smsCode_btn").html("<span>重新发送" + smsSecond + "s</span>");
    smsCountDown = setInterval(function () {
        if (smsSecond < 1) {
            clearInterval(smsCountDown);
            $(".smsCode_btn").html("获取验证码");
        } else {
            smsSecond--;
            $(".smsCode_btn").html("<span>重新发送" + smsSecond + "s</span>");
            isSmsCountDown = false;
        }
    }, 1000);
}

// 手机号码合法性检验
function checkSubmitTelephone() {
    if (telephone.val() === "") {
        layer.open({
            content: '手机号码不能为空！',
            skin: 'msg',
            time: 2 // 2秒后自动关闭
        });
        // telephone.focus();
        return false;
    }
    if (!telephone.val().match(/^1\d{10}$/)) {
        layer.open({
            content: '手机号码格式不正确！',
            skin: 'msg',
            time: 2 // 2秒后自动关闭
        });
        // telephone.focus();
        return false;
    }
    return true;
};

// 发送语音验证码
$(".voiceCode_btn").click(function () {
    if (voiceSecond > 0) {
        return false;
    }
    // 检验手机号码
    if (checkSubmitTelephone()) {
        if (smsToken) {
            $.ajax({
                type: "post",
                url: UC_API_URL + "/sms/voices/" + smsToken,
                xhrFields: {
                    withCredentials: true // 跨域请求
                },
                success: function (data) {
                    //数据成功返回时执行次回调函数
                    if (data.code === 0) {
                        saveMobile(telephone.val())
                        smsToken = data.data.smsToken
                        // sessionStorage中存放smsToken的值
                        sessionStorage.setItem("smsToken", data.data.smsToken)

                        if (data.data.smsCode !== "") {
                            smsCode.val(data.data.smsCode)
                        }
                        // 开启倒计时
                        setVoiceCountDown();
                    } else {
                        layer.open({
                            content: data.msg,
                            btn: '确认'
                        });
                    }
                },
                error: function (data) {
                    console.log(data.msg)
                }
            });
        } else {
            // 请求手机语音验证码
            var telephoneData = '{"mobile": "' + telephone.val() + '", "product": "rfzq", "opCode": "signin" }'
            $.ajax({
                type: "post",
                url: UC_API_URL + "/sms/voices",
                xhrFields: {
                    withCredentials: true // 跨域请求
                },
                dataType: "json",
                contentType: "application/json",
                data: telephoneData, //连同请求发送到服务器的数据。
                success: function (data) {
                    //数据成功返回时执行次回调函数
                    if (data.code === 0) {
                        saveMobile(telephone.val())
                        smsToken = data.data.smsToken
                        sessionStorage.setItem("smsToken", data.data.smsToken)
                        if (data.data.smsCode !== "") {
                            smsCode.val(data.data.smsCode)
                        }
                        // 开启倒计时
                        setVoiceCountDown();
                    } else {
                        layer.open({
                            content: data.msg,
                            btn: '确认'
                        });
                    }
                },
                error: function (data) {
                    console.log(data.msg)
                }
            });
        }
    }
})

// 设置语音倒计时
function setVoiceCountDown() {
    isVoiceCountDown = true;
    voiceSecond = 60;
    voiceBtn.html(" 重新发送" + voiceSecond + "s");
    voiceBtn.addClass("sending")
    voiceCountDown = setInterval(function () {
        if (voiceSecond < 1) {
            clearInterval(voiceCountDown);
            voiceBtn.removeClass("sending")
            voiceBtn.html("获取验证码");
            isVoiceCountDown = false;
        } else {
            voiceSecond--;
            voiceBtn.html(" 重新发送" + voiceSecond + "s");
        }
    }, 1000);
}

// 登录提交
$(".submit-btn").click(function () {
    // 获取验证码存储于sessionStorage中的smsToken
    smsToken = sessionStorage.getItem("smsToken");

    // 领取体验资格进行
    var sbData = {
        "mobile": telephone.val(),
        "smsCode": smsCode.val(),
        "smsToken": smsToken,
        "bizName": "rfzq"
    }
    var submitData = JSON.stringify(sbData)

    // 检验手机号码和验证码
    if (checkVerificationCode()) {
        if (smsToken === "" || smsToken === null) {
            layer.open({
                content: '请重新获取验证码!',
                skin: 'msg',
                time: 2 //2秒后自动关闭
            });
        } else {
            $.ajax({
                type: "post",
                url: UC_API_URL + "/rfzq/session/mobile",
                xhrFields: {
                    withCredentials: true // 跨域请求
                },
                dataType: "json",
                contentType: "application/json",
                data: submitData, //连同请求发送到服务器的数据。
                success: function (res) {
                    // 测试阶段UC返回均为未开户状态
                    if (res.code === 0) {
                          clear("user")
                          clear('xjlj_last_url')
                          setUser(res.data)                  
                    } else {
                        layer.open({
                            content: "手机验证码错误！",
                            btn: '确认'
                        });
                    }
                },
                error: function (data) {
                    console.log(data.msg)
                }
            })
        }

    }
});

// 手机号和验证码检验
function checkVerificationCode() {
    if (telephone.val() === "") {
        layer.open({
            content: '手机号码不能为空！',
            skin: 'msg',
            time: 2 //2秒后自动关闭
        });
        return false;
    }

    if (!telephone.val().match(/^1\d{10}$/)) {
        layer.open({
            content: '手机号码格式不正确！',
            skin: 'msg',
            time: 2 //2秒后自动关闭
        });
        return false;
    }

    if (smsCode.val() === "") {
        layer.open({
            content: '验证码不能为空！',
            skin: 'msg',
            time: 2 //2秒后自动关闭
        });
        return false;
    }
    if (!smsCode.val().match(/^\d{6}$/)) {
        layer.open({
            content: '验证码少于6位！',
            skin: 'msg',
            time: 2 //2秒后自动关闭
        });
        return false;
    }
    return true;
}

// $('.default-btn').click(function () {
//   if (!$(this).hasClass('active')) {
//     $(this).toggleClass('active')
//     setTimeout(function () {
//       $(this).toggleClass('active')
//     }, 600)
//   }
// })
