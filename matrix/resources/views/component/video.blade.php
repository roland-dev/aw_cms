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
    .videoFrame{
        position:relative; 
        z-index:1;
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
        .videoFrame{
            height: 100vh;
        }
    }
</style>
<div class="videoFrame">
    <video id="videoPlayer" class="video-js vjs-default-skin" 
        controls="controls" 
        preload="yes" 
        width="100%" 
        controlslist="nodownload" 
        playsinline="true" 
        x-webkit-airplay="true" 
        webkit-playsinline="true" 
        oncontextmenu="return false;">
    </video>
    <img id="loading" style="display:none;" src="data:image/gif;base64,R0lGODlhIAAgALMMAJ6enpmZmbi4uHNzc7+/v9nZ2czMzIyMjFlZWdHR0bKysqampgAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh/wtYTVAgRGF0YVhNUDw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MDAwQjM4RDhEMzNDMTFFMzkyODZGMzNBQ0YxNkFCRjQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MDAwQjM4RDlEMzNDMTFFMzkyODZGMzNBQ0YxNkFCRjQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDowMDBCMzhENkQzM0MxMUUzOTI4NkYzM0FDRjE2QUJGNCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDowMDBCMzhEN0QzM0MxMUUzOTI4NkYzM0FDRjE2QUJGNCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgH//v38+/r5+Pf29fTz8vHw7+7t7Ovq6ejn5uXk4+Lh4N/e3dzb2tnY19bV1NPS0dDPzs3My8rJyMfGxcTDwsHAv769vLu6ubi3trW0s7KxsK+urayrqqmop6alpKOioaCfnp2cm5qZmJeWlZSTkpGQj46NjIuKiYiHhoWEg4KBgH9+fXx7enl4d3Z1dHNycXBvbm1sa2ppaGdmZWRjYmFgX15dXFtaWVhXVlVUU1JRUE9OTUxLSklIR0ZFRENCQUA/Pj08Ozo5ODc2NTQzMjEwLy4tLCsqKSgnJiUkIyIhIB8eHRwbGhkYFxYVFBMSERAPDg0MCwoJCAcGBQQDAgEAACH5BAUAAAwALAAAAAAgACAAAASnkMlJ6bg16y2v5+B0jJ03SEgaVmPLmGcqr2J7lCcjqzRj35kdoidxbWahgJJIKTgzyiiT4axWoktm9UnBBqbbAjQL5k7PgPS5kmhL0vC1pE1nwNVyuvsOkDP0CW94fnV+RAuIawaLGYiOU4uRFY6JTJGMFJQLkJeNlZwGKwqjHASmTKOpGqasRKmkDAKyEqynPa8KsbKztQSosLuzDK1+wQKGGbvIyxEAIfkEBQAADAAsAAAAAB8AHwAABKWQyUnpuTXrza7noBSMkocxQxpSY9uZaKquTEu+hyTPqx1ouwFNdAPyNoDksIJoVpLQpaRJpUCVUqpzcgVIGVrEE/utfr+L9LlSaDPS8LWkTYer5fS2fSFn5Atvd310fUMKh2sJihWHjV+KkBSNiFKQixOTCo+WjJSbCSECohwGpUuiqBqlq0OoowwEsRKrpjSuArCxsrQGp6+6sgyscsAEhRW6QxEAIfkEBQAADAAsAAAAAB8AHwAABKWQyUlpuDXrza7noASMkocxRxpSY9uZaKquTEu+gSTPqw1ouwNNdAPyNovksDJoVpLQpaRJpUCVUqpzcl1IGdrBE/utfr+K9LmCaDPS8LWkTYer5fS2XSFn5BFvd310fUMCh2sFihWHjV+KkBSNiFKQixOTAo+WjJSbBSEEohwJpUuiqBqlq0OoowwGsRKrpjSuBLCxsrQJp6+6sgyscsAGhRW6QxEAIfkEBQAADAAsAAAAAB8AHwAABKWQyUkpuDXrza7noLSMkocxQRpSY9uZaKquTEu+gCTPq71ouwBNdAPyNorksHJoVpLQpaRJpUCVUqpzclVIGdrDE/utfr+C9LkyaDPS8LWkTYer5fS2XSBn5Advd310fUMEh2sIihWHjV+KkBSNiFKQixOTBI+WjJSbCCEGohwFpUuiqBqlq0OoowwJsRKrpjSuBrCxsrQFp6+6sgyscsAJhRW6QxEAIfkEBQAADAAsAAAAAB8AHwAABKWQyUnpujXrza7noKSMkocxQBpSY9uZaKquTEu+iyTPq61oOwBNdAPyNoLksBJoVpLQpaRJpUCVUqpzchVIGdrAE/utfr+E9LlyaDPS8LWkTYer5fS2nSBn5A9vd310fUMGh2sDihWHjV+KkBSNiFKQixOTBo+WjJSbAyEJohwIpUuiqBqlq0OoowwFsRKrpjSuCbCxsrQIp6+6sgyscsAFhRW6QxEAIfkEBQAADAAsAAAAAB8AHwAABKWQyUmpujXrza7noCSMkocxSxpSY9uZaKquTEu+iiTPqy1ouwVNdAPyNoTksAJoVpLQpaRJpUCVUqpzciVIGVrAE/utfr+G9LkSaDPS8LWkTYer5fS23SBn5ANvd310fUMJh2sHihWHjV+KkBSNiFKQixOTCY+WjJSbByEFohwDpUuiqBqlq0OoowwIsRKrpjSuBbCxsrQDp6+6sgyscsAIhRW6QxEAIfkEBQAADAAsAAAAAB8AHwAABKWQyUmpuDXrza7noESMkocxShpSY9uZaKquTEu+giTPq01oOwVNdAPyNobksLJoVpLQpaRJpUCVUqpzcjVIGdrFE/utfr+J9LkCaDPS8LWkTYer5fS2PSFn5AFvd310fUMFh2sBihWHjV+KkBSNiFKQixOTBY+WjJSbASEIohwHpUuiqBqlq0OoowwDsRKrpjSuCLCxsrQHp6+6sgyscsADhRW6QxEAIfkEBQAADAAsAAAAAB8AHwAABKWQyUkpuTXrza7noGSMkocxQhpSY9uZaKquTEu+hCTPq21oOwFNdAPyNonksKJoVpLQpaRJpUCVUqpzck1IGVrFE/utfr+F9LmyaDPS8LWkTYer5fS2vSBn5Bdvd310fUMIh2sAihWHjV+KkBSNiFKQixOTCI+WjJSbACEDohwBpUuiqBqlq0OoowwHsRKrpjSuA7CxsrQBp6+6sgyscsAHhRW6QxEAIfkEBQAADAAsAAAAAB8AHwAABKWQyUmpuTXrza7noJSMkocxRBpSY9uZaKquTEu+hiTPq51oOwJNdAPyNoXksCJoVpLQpaRJpUCVUqpzci1IGVrBE/utfr+I9LmiaDPS8LWkTYer5fS2HSFn5BVvd310fUMDh2sLihWHjV+KkBSNiFKQixOTA4+WjJSbCyEHohwApUuiqBqlq0OoowwBsRKrpjSuB7CxsrQAp6+6sgyscsABhRW6QxEAIfkEBQAADAAsAAAAAB8AHwAABKWQyUlpujXrza7noFSMkocxRhpSY9uZaKquTEu+iSTPq11oOwNNdAPyNojksEJoVpLQpaRJpUCVUqpzckVIGVrCE/utfr+D9LkiaDPS8LWkTYer5fS2fSBn5AVvd310fUMHh2sKihWHjV+KkBSNiFKQixOTB4+WjJSbCiEBohwLpUuiqBqlq0OoowwAsRKrpjSuAbCxsrQLp6+6sgyscsAAhRW6QxEAIfkEBQAADAAsAAAAAB8AHwAABKWQyUlpuTXrza7noISMkocxSRpSY9uZaKquTEu+hSTPq41ouwRNdAPyNoPksGJoVpLQpaRJpUCVUqpzch1IGVrDE/utfr+H9LlCaDPS8LWkTYer5fS2/SBn5Alvd310fUMBh2sCihWHjV+KkBSNiFKQixOTAY+WjJSbAiEAohwKpUuiqBqlq0OoowwLsRKrpjSuALCxsrQKp6+6sgyscsALhRW6QxEAIfkEBQAADAAsAAAAAB8AHwAABKWQyUkpujXrza7noDSMkocxRRpSY9uZaKquTEu+iCTPqz1ouwJNdAPyNofksJJoVpLQpaRJpUCVUqpzcj1IGdrEE/utfr+B9LliaDPS8LWkTYer5fS2PSBn5A1vd310fUMAh2sEihWHjV+KkBSNiFKQixOTAI+WjJSbBCELohwCpUuiqBqlq0OoowwKsRKrpjSuC7CxsrQCp6+6sgyscsAKhRW6QxEAOw==">
    <img src="{{config('app.url_storage').'/assets/ugc/images/video_play.png'}}" alt="" class="play-status video-control">
    <img src="{{config('app.url_storage').'/assets/ugc/images/video_pause.png'}}" alt="" class="pause-status video-control">
</div>
<script type="text/javascript">
    var xmlHttp;
    var currentVideoKey;
    var urlBase = "https://h5vv.video.qq.com/getinfo?callback=txPlay&&charge=0&defaultfmt=auto&otype=json&platform=11001&defnpayver=0&host=m.v.qq.com&newplatform=11001&defsrc=1&defn=shd&vid=";


    // if (wx.getNetworkType) {
    //     wx.getNetworkType({
    //         success: function (res) {
    //             var url;
    //             var networkType = res.networkType; // 返回网络类型2g，3g，4g，wifi
    //             if (networkType == 'wifi') {
    //                 url = "https://vv.video.qq.com/getinfo?callback=txPlay&&charge=0&defaultfmt=auto&otype=json&guid=93c663bf015782f95de38b5e7a64c63d&platform=10901&defnpayver=1&appVer=3.2.160&sdtfrom=v1010&host=v.qq.com&ehost=https%3A%2F%2Fv.qq.com%2Fx%2Fpage%2Fd0532979jmw.html&sphttps=1&spwm=2&defn=shd&fhdswitch=1&show1080p=1&isHLS=0&newplatform=10901&defsrc=2&vid="
    //             } else {
    //                 url = urlBase;
    //             }
    //             playVideo('', "", url);
    //         }
    //     });
    // } else {
    playVideo('{{ $id }}', "https://shp.qpic.cn/qqvideo_ori/0/m0849dydlcb_496_280/0", urlBase);
    // }
    currentVideoKey = '{{ $vkey }}';
    
    // 点击video控制播放暂停按钮动画
    $('.videoFrame video').on("click", function(){
        if($("#loading").is(":visible") || window.innerWidth < 1000){
            return;
        }
        if($('.videoFrame video')[0].paused) {
            $('.videoFrame video').trigger('play');       
            $(".pause-status").show()
            setTimeout(function(){
                $(".pause-status").hide()
            },3500)
        } else {
            $('.videoFrame video').trigger('pause');
            $(".play-status").show()
            setTimeout(function(){
                $(".play-status").hide()
            },3500)
        }
    })

    function playVideo( video_id,  video_poster, url_base) {
        if( video_id ){
        try {
        $('.videoFrame video').attr('poster', video_poster);

        var jp = document.createElement("script");  
        jp.type = "text/javascript";  
        jp.src = url_base + video_id;  
        document.getElementsByTagName("head")[0].appendChild(jp);  
        } catch (err) {
        alert("视频地址解释失败！");
        console.log(err);
        }
        }
    }

    function txPlay(qqvideo) {
        if( qqvideo && typeof(qqvideo.vl)  != "undefined" && qqvideo.vl.vi[0].fn ){
            mp4source = qqvideo.vl.vi[0].ul.ui[0].url + qqvideo.vl.vi[0].fn + '?vkey=' + qqvideo.vl.vi[0].fvkey ;

            $('.videoFrame video').attr('src', mp4source);

            setTimeout(reportAction("get_video", currentVideoKey), 500);

            $('.videoFrame video').on("waiting",function(){  
                $('#loading').show();
            }); 
            $('.videoFrame video').on("loadeddata",function(){  
                $('#loading').hide();
            }); 
            $('.videoFrame video').on("play",function(){  
                $('.videoFrame video'); 
                $('#loading').hide();
            }); 

            $('.videoFrame video').on("timeupdate",function(){ 
                if (disableReporting) return;
                var player = $('.videoFrame video')[0];
                if (!player || !player.currentTime || (lastPlayHeadTime + REPORT_WAITING_TIME > player.currentTime)) return;

                currentPlayedTime = player.currentTime - totalSeekTime;

                reportAction("play_video", currentVideoKey, (currentPlayedTime - lastReportTime));
                lastReportTime = currentPlayedTime;
                lastPlayHeadTime = player.currentTime;
            }); 
            // 拖拽（快进或快退）
            $('.videoFrame video').on("seeking",function(){ 
                disableReporting = true;
                $('#loading').show();
            }); 
            // 拖拽（快进或快退）结束
            $('.videoFrame video').on("seeked",function(){ 
                var player = $('.videoFrame video')[0];
                totalSeekTime = player.currentTime - lastPlayHeadTime + totalSeekTime;
                lastPlayHeadTime = player.currentTime;
                disableReporting = false;
                $('#loading').hide();
            }); 
            // $('.videoFrame video').on("ended",function(){  
            //     setTimeout(reportAction("finish_video", currentVideoKey), 100);
            // }); 
        }
    }


    function resetPlayerHeight(){
        if (!window.parent) return;

        var main = window.parent.document.getElementById("zytg_player");
        var thisheight = document.body.scrollHeight;
        if (!main) return false;
        console.log(thisheight)
        if(document.body.clientWidth >= 1000) {
            // var zp = document.querySelector("#zytg_player")
            main.style.height = thisheight;
        }
        main.height = thisheight;
        main.width = "100%";
    }

    window.onload = function(){
        if (!window.parent) return;
        setTimeout(function(){
            // resetPlayerHeight();
        }, 100);
    }

    window.onresize = function(){
        if (!window.parent) return;
        // resetPlayerHeight();
    }
</script>
<script type="text/javascript">
    const REPORT_WAITING_TIME = 60;
    var totalSeekTime = 0;
    var lastPlayHeadTime = 0;
    var lastReportTime = 0;
    var currentPlayedTime = 0;
    var disableReporting = false;
    window.onload = function(){
        setTimeout(reportAction("get_video", "{{ $vkey }}"), 500);
    }
    function reportAction(key, id, duration) {
        duration = duration ? duration : 0;
        $.post('https://cms.zhongyingtougu.com/pofolio.php?app=tj&action=add_action', { act_key: key, act_id: id, act_duration: duration }, function(response){
            // console.log(response);
        });
    }
    function getSeconds(msec){
        return Math.floor(msec / 1000);
    }


    //tx statsitic
    var _mtac = {};
    (function() {
        var mta = document.createElement("script");
        mta.src = "https://pingjs.qq.com/h5/stats.js?v2.0.2";
        mta.setAttribute("name", "MTAH5");
        mta.setAttribute("sid", "500471073");
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(mta, s);
    })();
</script>
