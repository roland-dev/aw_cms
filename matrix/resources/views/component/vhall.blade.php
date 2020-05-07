<style>
    #loading {
        position:absolute;
        top:50%;
        left:50%;
        width: 32px;
        height: 32px;
        margin:-22px 0 0 -16px;
        text-align: center;
    }   
    .videoFrame {
        position: relative; 
        padding-bottom: 56.25%; 
        height: 0; 
        overflow: hidden;
    }
    .video-control{
        display: none;
        width: 50px;
        height: 50px;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-25px, -50px);
    }
    .play-status{
        animation:videoAction 4s;
    }
    .pause-status{
        animation:videoAction 4s;
    }
    @keyframes videoAction{
        from {
            opacity: 1;
            transform: scale(1.2); 
        }
        to {
            opacity: 0;
            transform: scale(1); 
        }
    }
    
    @media screen and (min-width: 1000px){ 
        .videoFrame {
            height: 100vh;
            padding-bottom: 0; 
        }
    }
</style>
<div>
    <div class="videoFrame" id="video">
        <img id="loading" src="data:image/gif;base64,R0lGODlhIAAgALMMAJ6enpmZmbi4uHNzc7+/v9nZ2czMzIyMjFlZWdHR0bKysqampgAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh/wtYTVAgRGF0YVhNUDw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MDAwQjM4RDhEMzNDMTFFMzkyODZGMzNBQ0YxNkFCRjQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MDAwQjM4RDlEMzNDMTFFMzkyODZGMzNBQ0YxNkFCRjQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDowMDBCMzhENkQzM0MxMUUzOTI4NkYzM0FDRjE2QUJGNCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDowMDBCMzhEN0QzM0MxMUUzOTI4NkYzM0FDRjE2QUJGNCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgH//v38+/r5+Pf29fTz8vHw7+7t7Ovq6ejn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">
    </div>
    <img src="{{config('app.url_storage').'/assets/ugc/images/video_play.png'}}" alt="" class="play-status video-control">
    <img src="{{config('app.url_storage').'/assets/ugc/images/video_pause.png'}}" alt="" class="pause-status video-control">
</div>

<script type="text/javascript" src="{{config('app.url_storage').'/assets/ugc/js/vhallSDK.js'}}"></script>
<script type="text/javascript">
    var baseURL = "{{ config('app.ajax_url') }}"
    var UCURL = "{{ config('uc.url') }}"
    var currentVideoKey = '{{ $vkey }}'
    var roomId = '{{ $id }}'

    const REPORT_WAITING_TIME = 60;     // 直播上报时间间隔
    var totalSeekTime = 0;
    var lastPlayHeadTime = 0;
    var lastReportTime = 0;
    var currentPlayedTime = 0;
    var disableReporting = false;

    window.onload=function(){
        getVhallsign()
        resizeWidth()
        $(window).resize(function () {
            resizeWidth()
        });
    }
    // ------------------------------------------------微吼------------------------------------------------	
    // sdk引入
    function vHallBeginPlay(roomid, sign, signedat, account, username) {
        var app_key = "{{ config('token.vhall.app_key') }}"

        VHALL_SDK.init({
            account : account,
            username : username,
            roomid : roomid,
            app_key : app_key,
            signedat : signedat,
            sign : sign,
            videoContent : '#video',
            docContent: '#docId',  //可选，当需要使用文档模块时，插入视频dom属性 如（'#id','.class'等）
            x5_video_type: 'h5',
            x5_video_fullscreen: 'false', 
            protocol:'https'
        });

        VHALL_SDK.on('playerReady', function() {
            // 跳转到直播互动
            console.log("playerReady")
            $('#loading').hide();
            $("#video video").attr("height", "auto")
            $("#video video").attr("poster", "{{config('app.url_storage').'/assets/ugc/images/live_loading.jpg'}}")

            // 点击video控制播放暂停按钮动画
            $('#video video').on("click", function(){
                if($("#loading").is(":visible") || window.innerWidth < 1000){
                    return;
                }
                if($('#video video')[0].paused) {
                    $('#video video').trigger('play');       
                    $(".pause-status").show()
                    setTimeout(function(){
                        $(".pause-status").hide()
                    },3500)
                } else {
                    $('#video video').trigger('pause');
                    $(".play-status").show()
                    setTimeout(function(){
                        $(".play-status").hide()
                    },3500)
                }
            })

            // 直播上报
            setTimeout(reportAction(0), 500);

            $('#video video').on("waiting",function(){  
                $('#loading').show();
            }); 
            $('#video video').on("loadeddata",function(){  
                $('#loading').hide();
            }); 
            $('#video video').on("play",function(){  
                // $('#video video').show(); 
                $('#loading').hide();
            }); 

            $('#video video').on("timeupdate",function(){ 
                if (disableReporting) return;
                var player = $('#video video')[0];
                if (!player || !player.currentTime || (lastPlayHeadTime + REPORT_WAITING_TIME > player.currentTime)) return;

                currentPlayedTime = player.currentTime - totalSeekTime;

                reportAction(currentPlayedTime - lastReportTime);
                lastReportTime = currentPlayedTime;
                lastPlayHeadTime = player.currentTime;
            }); 
            // 拖拽（快进或快退）
            $('#video video').on("seeking",function(){ 
                disableReporting = true;
                $('#loading').show();
            }); 
            // 拖拽（快进或快退）结束
            $('#video video').on("seeked",function(){ 
                var player = $('#video video')[0];
                totalSeekTime = player.currentTime - lastPlayHeadTime + totalSeekTime;
                lastPlayHeadTime = player.currentTime;
                disableReporting = false;
                $('#loading').hide();
            }); 
            // $('#video video').on("ended",function(){  
            //     setTimeout(reportAction("finish_video", currentVideoKey), 100);
            // }); 
        });
        // 直播结束
        VHALL_SDK.on('streamOver', function() {
            var userInfo = VHALL_SDK.getUserinfo();

        });
    }

    // 获取微吼签名
    function getVhallsign() {
        $("#video").html('<img id="loading" style="display:none;" src="data:image/gif;base64,R0lGODlhIAAgALMMAJ6enpmZmbi4uHNzc7+/v9nZ2czMzIyMjFlZWdHR0bKysqampgAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh/wtYTVAgRGF0YVhNUDw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MDAwQjM4RDhEMzNDMTFFMzkyODZGMzNBQ0YxNkFCRjQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MDAwQjM4RDlEMzNDMTFFMzkyODZGMzNBQ0YxNkFCRjQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDowMDBCMzhENkQzM0MxMUUzOTI4NkYzM0FDRjE2QUJGNCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDowMDBCMzhEN0QzM0MxMUUzOTI4NkYzM0FDRjE2QUJGNCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgH//v38+/r5+Pf29fTz8vHw7+7t7Ovq6ejn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">')
        $.ajax({
            type: "get",
            url: baseURL + "/api/v2/token/vhall/sign?room_id=" + roomId,
            dataType: "json",
            xhrFields: {
                withCredentials: true
            },
            success: function (res) {
                if(res.code == 0) {
                    openId = res.data.account
                    vHallBeginPlay(roomId, res.data.vhall_sign, res.data.signedat, res.data.account, res.data.username)
                } else {
                    alert("获取微吼签名错误！")
                }
            },
            error: function (err) {
                console.log(err)
            }
        })
    }

    // 发送上报信息
    function reportAction(during) {
        var now = new Date().getTime()
        now = Math.floor(now/1000)
        var reportObj = {
            columnId: '{{ $type }}',    
            itemId: currentVideoKey,     
            itemKind: "webcast",           
            title: '{{ $title }}',
            pageName: "webcast",         
            detailUrl: roomId,
            addTs : now,
            remark: during
        }
        $.ajax({
            type: 'post',
            url: UCURL + '/api/v2/uc/views',
            contentType: "application/json",
            dataType: 'json',
            xhrFields: {
                withCredentials: true
            },
            data: JSON.stringify(reportObj),
            crossDomain: true,
            success: function (res) {
                if(res.code == 0) {
                    console.log("上报成功！")
                } else {
                    console.log(res.msg)
                }
            },
            error: function (err) {
                console.log(err)
            }
        })
    }

    function resizeWidth() {
        if(window.innerWidth > 1000) {
            $("body").css("overflow", "hidden")
        } else {
            $("body").css("overflow", "auto")
        }
    }
</script>
