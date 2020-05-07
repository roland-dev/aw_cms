<!-- <link href="https://cms.zhongyingtougu.com/gensee/css/mobile_vod.css?v=1.1" rel="stylesheet" type="text/css"> -->
<style type="text/css">
#loading {
  position:absolute;
  top:50%;
  left:50%;
  width: 32px;
  height: 32px;
  margin:-22px 0 0 -16px;
  text-align: center;
}     
.top-screen {
  position:fixed;
  top:0; 
  bottom:0; 
  left:0; 
  right:0; 
  background: #fff; 
  z-index: 10000;
}
.top-screen img{
  position:absolute;
  top:50%;
  left:50%;
  width: 32px;
  height: 32px;
  transform: translate(-16px, -16px);
  text-align: center;
}
#webPlayer {
    height:180px; 
    position: relative;
}
#webPlayer.full{
    position: fixed;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    z-index: 9999;
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
    #webPlayer{
        height: 100vh;
    }
}
.video-container {position:relative; width:100%; height:100%; -webkit-transition:all 0.5s ease-in-out; transition:all 0.5s ease-in-out; overflow:hidden;}
.video-box {width:100%; height:100%;}
.videotop {-webkit-transform: translateY(-150%); transform: translateY(-150%);}   
</style>
</head>
<body>
<div class="top-screen" style="position:fixed; top:0; bottom:0; left:0; right:0; background: #fff; z-index: 10000">
  <img src="data:image/gif;base64,R0lGODlhIAAgALMMAJ6enpmZmbi4uHNzc7+/v9nZ2czMzIyMjFlZWdHR0bKysqampgAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh/wtYTVAgRGF0YVhNUDw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MDAwQjM4RDhEMzNDMTFFMzkyODZGMzNBQ0YxNkFCRjQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MDAwQjM4RDlEMzNDMTFFMzkyODZGMzNBQ0YxNkFCRjQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDowMDBCMzhENkQzM0MxMUUzOTI4NkYzM0FDRjE2QUJGNCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDowMDBCMzhEN0QzM0MxMUUzOTI4NkYzM0FDRjE2QUJGNCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgH//v38+/r5+Pf29fTz8vHw7+7t7Ovq6ejn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">
</div>
<div id="webPlayer" tag="ZYTG_PLAYER">
    <div id="video-box" class="video-container">
        @if($posterUrl)
        <gs:video-vod id="videoComponent" site="fhcj.gensee.com" ctx="webcast" ownerid="{{ $id }}" py="1" bgimg="{{ $$posterUrl }}" btnimg="https://static.gensee.com/webcast/static/mobile2015/images/triangle_img.png?v=4.6" />
        @else
        <gs:video-vod id="videoComponent" site="fhcj.gensee.com" ctx="webcast" ownerid="{{ $id }}" py="1" bgimg="https://static.gensee.com/webcast/static/mobile2015/images/video_b.jpg" btnimg="https://static.gensee.com/webcast/static/mobile2015/images/triangle_img.png?v=4.6" />
        @endif
        <img id="loading" style="display:none;" src="data:image/gif;base64,R0lGODlhIAAgALMMAJ6enpmZmbi4uHNzc7+/v9nZ2czMzIyMjFlZWdHR0bKysqampgAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh/wtYTVAgRGF0YVhNUDw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MDAwQjM4RDhEMzNDMTFFMzkyODZGMzNBQ0YxNkFCRjQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MDAwQjM4RDlEMzNDMTFFMzkyODZGMzNBQ0YxNkFCRjQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDowMDBCMzhENkQzM0MxMUUzOTI4NkYzM0FDRjE2QUJGNCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDowMDBCMzhEN0QzM0MxMUUzOTI4NkYzM0FDRjE2QUJGNCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgH//v38+/r5+Pf29fTz8vHw7+7t7Ovq6ejn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">
    </div>
    <img src="{{config('app.url_storage').'/assets/ugc/images/video_play.png'}}" alt="" class="play-status video-control">
    <img src="{{config('app.url_storage').'/assets/ugc/images/video_pause.png'}}" alt="" class="pause-status video-control">
</div>
<script type="text/javascript" src="https://static.gensee.com/webcast/static/sdk/js/gssdk-1.3.js?201806v477"></script>
<!-- <script type="text/javascript" src="https://cms.zhongyingtougu.com/gensee/js/utils.js"></script> -->
<!-- <script type="text/javascript" src="https://cms.zhongyingtougu.com/gensee/js/TouchSlide.js"></script> -->
<script type="text/javascript">
  var reportInterval;
  var beginInterval;
  var playStatus;

  //根据组获得通讯通道
  var channel = GS.createChannel();
  
  var winWidth = (window.innerWidth > 0) ? window.innerWidth : screen.width;
  var winHeight = (window.innerHeight > 0) ? window.innerHeight : screen.height;
  var video_height = parseInt((9 * winWidth)/16);
  var tabsHeight=$('.tabs').height();
  setTimeout(function () {
      $(".top-screen").hide()
  }, 2000);
  
  $(function(){
    //设置区块高度
    //设置区块高度 modified by eric @20190317
    resizePlayer();
    $(window).resize(function () {
        resizePlayer();
    });

    // 未开始正式播放前的定时器，检查何时完成缓冲开始播放（展示互动api没有找到更合适的通知事件）
    beginInterval = setInterval(function(){
        channel.send("playheadTime", {});
        // console.log("setInterval 0");
    }, 1000);   

  });
  channel.bind("onStatus", function(event){
    console.log(event)
    if(event.data.type != 8) {
      $(".top-screen").hide()
    }
  })

  //监听播放时间
  channel.bind("onPlayheadTime", function (event) {
      if (!event.data || !event.data.playheadTime || (lastPlayHeadTime == event.data.playheadTime)) return;
      //刚开始播放时隐藏菊花
      if ((currentPlayedTime == 0) && (event.data.playheadTime > 0)) {
          $('#loading').hide();
          clearInterval(beginInterval);

          //发起定时播放时长检查任务
          if (!reportInterval) {
              reportInterval = setInterval(function(){
                  channel.send("playheadTime", {});
              }, REPORT_WAITING_TIME * 1000);        
          }
      }

      currentPlayedTime = event.data.playheadTime - totalSeekTime;
      // console.log("report: ", (lastPlayHeadTime), (currentPlayedTime), (lastReportTime), (currentPlayedTime - lastReportTime));
      reportAction("play_video", "{{ $vkey }}", getSeconds(currentPlayedTime - lastReportTime));
      lastReportTime = currentPlayedTime;
      lastPlayHeadTime = event.data.playheadTime;
  });

  // 拖拽快进完成
  channel.bind("onSeekCompleted", function (event) {
      totalSeekTime = event.data.timestamp - lastPlayHeadTime + totalSeekTime;
      lastPlayHeadTime = event.data.timestamp;
      // console.log(' > seek: ', totalSeekTime); 
  });
  
          
  channel.bind("loadStart", function (event) {
      $('#loading').show();
  });

  channel.bind("onChapter", function (event) {
      $('#loading').hide();
  });

  channel.bind("onPlay", function (event) {
      console.log("开始播放")
      playStatus = true
  });

  channel.bind("onPause", function (event) {
      console.log("暂停")
      playStatus = false
  });
  
  channel.bind("onDoubleClick", function (event) {
    if($("#loading").is(":visible") || window.innerWidth < 1000){
        return;
    }
    if(!playStatus) {
        channel.send("play", {
            // console.log("开始播放")
        });
        $(".pause-status").show()
        setTimeout(function(){
            $(".pause-status").hide()
        },3500)
    } else {
        channel.send("pause", {
            // console.log("开始播放")
        });
        $(".play-status").show()
        setTimeout(function(){
            $(".play-status").hide()
        },3500)
    }
  });

  
  
  function sdkgo(){
      //触摸切换功能
      
      TouchSlide({
          slideCell: "#chatQaBox",
          titCell: ".tabs li",
          mainCell: ".slider-container",
          defaultIndex: window.tabDefaultIndex,
          startFun:function(i,c){
              
          },
          endFun:function(i,c){
          }
      });
  }
  //API错误通知
  channel.bind("onAPIError", function (event) {
      // console.log(event);
  });
  
  
  function isPortrait(){
      if(window.orientation==0 && window.innerWidth>window.innerHeight){
          return false;
      }else{
          return window.orientation==180||window.orientation==0||window.orientation==undefined;
      }
  }
  function bodyOrientationChange() {
      setTimeout(function () {
          winWidth = (window.innerWidth > 0) ? window.innerWidth : screen.width;
          winHeight = (window.innerHeight > 0) ? window.innerHeight : screen.height;
          video_height = parseInt((9 * winWidth)/16);
          
          // console.log(winWidth);
          if(isPortrait()){//竖屏使用16：9比例高度
              $("#topHalf").height(video_height);
              $('#doc-box').height(winHeight-video_height-tabsHeight);
              $('#doc-box').find('iframe').width(winWidth).height(winHeight-video_height-tabsHeight);
              //setTimeout(function(){$('#doc-box').find('iframe').css('clear','both');},1000);
              $('.msg-content,.qa_txt').width(winWidth-20);
              $('.chat-bd,.qa_list_content').height(winHeight-video_height-tabsHeight);
              $('.chapter-list-container').height(winHeight-video_height-tabsHeight-$('.chapter-hd').height());
          }else{//横屏直接铺满视频
              $("#topHalf").height(winHeight);
          }
      }, 100);
  }


  function createOrientationChangeProxy(fn, scope) {
      return function () {
          clearTimeout(scope.orientationChangedTimeout);
          var args = Array.prototype.slice.call(arguments, 0);
          scope.orientationChangedTimeout = setTimeout($.proxy(function () {

              var ori = window.orientation;
              if (ori != scope.lastOrientation) {
                  fn.apply(scope, args); // 这里才是真正执行回调函数
              }
              scope.lastOrientation = ori;
          }, scope), 500);
      };
  }
  //横竖屏翻转监听
  window.addEventListener("onorientationchange" in window ? "orientationchange" : "resize", createOrientationChangeProxy(function () {
      bodyOrientationChange();
  }, window), false);

  function resizePlayer() {
      //设置区块高度
      winWidth = (window.innerWidth > 0) ? window.innerWidth : screen.width;
      winHeight = (window.innerHeight > 0) ? window.innerHeight : screen.height;
      video_height = parseInt((9 * winWidth)/16);
      if(window.innerWidth >= 1000) {
        $("#webPlayer").height("100vh");
        $("#webPlayer").removeClass("full")
        $("body").css("overflow", "auto")
      } else if(window.innerWidth < 800){
        $("#webPlayer").height(video_height);
        $("#webPlayer").removeClass("full")
        $("body").css("overflow", "auto")
      } else {
        $("#webPlayer").height("100vh");
        $("#webPlayer").addClass("full")
        $("body").css("overflow", "hidden")
      }
      
  }
</script>
<script type="text/javascript">
  window.onload = function(){
      setTimeout(reportAction("get_video", "{{ $vkey }}"), 500);
  }
</script>

<script type="text/javascript">
  const REPORT_WAITING_TIME = 60;
  var totalSeekTime = 0;
  var lastPlayHeadTime = 0;
  var lastReportTime = 0;
  var currentPlayedTime = 0;
  var disableReporting = false;

  function reportAction(key, id, duration) {
      duration = duration ? duration : 0;
      $.post('https://cms.zhongyingtougu.com/pofolio.php?app=tj&action=add_action', { act_key: key, act_id: id, act_duration: duration }, function(response){
          console.log(response);
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
