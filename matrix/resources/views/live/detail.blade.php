<!DOCTYPE html >
<html xmlns:gs="http://www.gensee.com/ec">
<head>
	<meta charset="UTF-8">
	<title>视频直播</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <link rel="stylesheet" href="{{config('app.url_storage').'/assets/ugc/css/live.css'}}?v=20200102">
</head>
<body>
	<div class="wrap">
		<div class="video-area">
			<div class="title" id="title">直播节目</div>
			<div class="video" id="video">
			<!-- <gs:video-vod id="videoComponent" site="fhcj.gensee.com" ctx="webcast" ownerid="638eaad8051c4111871f24b17f4851a2" py="1" bgimg="https://static.gensee.com/webcast/static/mobile2015/images/video_b.jpg" btnimg="https://static.gensee.com/webcast/static/mobile2015/images/triangle_img.png?v=4.6"></gs:video-vod> -->
				<div class="coffee-time">
					<div class="coffee">
						<img src="{{config('app.url_storage').'/assets/ugc/images/coffee.png'}}" alt="">
						<div>稍等片刻，直播即将开始...</div>
					</div>
				</div>
			</div>
		</div>
		<div class="discussion">
			<div class="tabs">
				<div class="tab active">节目表<div class="red-block"></div></div>
				<div class="tab">直播互动<div class="red-block"></div></div>
			</div>
			<div class="programme">
				<ul id="list" class="scroll-bar">
				</ul>
			</div>
			<div class="interaction scroll-bar">
				<div class="reply-list" style="height:100%">
				</div>
				<div class="reply-bottom">
					<div class="send-box">
						<textarea 
							id="textarea" 
							onpropertychange="setHeight(this);" 
							onpaste="setHeight(this);" 
							oninput="setHeight(this);"
							type="text" 
							placeholder="友善发言的人运气不会太差" 
							maxlength="500"
							></textarea>
					</div>
					<div class="actions">
						<div class="send-btn">发送</div>
						<span id="limitLength">0/500</span>
						<div class="emoji"><img src="{{config('app.url_storage').'/assets/ugc/images/live_emoji.png'}}" alt="">表情</div>
					</div>
					<div class="emoji-area scroll-bar w-emoji">
						<div class="emoji-column">
							<div class="wEmoji-item" data-emj="[微笑.png]">
								<div class="emoji-bg" data-w="[微笑.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[撇嘴.png]">
								<div class="emoji-bg" data-w="[撇嘴.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[色.png]">
								<div class="emoji-bg" data-w="[色.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[发呆.png]">
								<div class="emoji-bg" data-w="[发呆.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[得意.png]">
								<div class="emoji-bg" data-w="[得意.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[流泪.png]">
								<div class="emoji-bg" data-w="[流泪.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[害羞.png]">
								<div class="emoji-bg" data-w="[害羞.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[闭嘴.png]">
								<div class="emoji-bg" data-w="[闭嘴.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[睡.png]">
								<div class="emoji-bg" data-w="[睡.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[大哭.png]">
								<div class="emoji-bg" data-w="[大哭.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[尴尬.png]">
								<div class="emoji-bg" data-w="[尴尬.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[发怒.png]">
								<div class="emoji-bg" data-w="[发怒.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[调皮.png]">
								<div class="emoji-bg" data-w="[调皮.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[呲牙.png]">
								<div class="emoji-bg" data-w="[呲牙.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[惊讶.png]">
								<div class="emoji-bg" data-w="[惊讶.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[难过.png]">
								<div class="emoji-bg" data-w="[难过.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[囧.png]">
								<div class="emoji-bg" data-w="[囧.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[抓狂.png]">
								<div class="emoji-bg" data-w="[抓狂.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[吐.png]">
								<div class="emoji-bg" data-w="[吐.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[偷笑.png]">
								<div class="emoji-bg" data-w="[偷笑.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[愉快.png]">
								<div class="emoji-bg" data-w="[愉快.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[白眼.png]">
								<div class="emoji-bg" data-w="[白眼.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[傲慢.png]">
								<div class="emoji-bg" data-w="[傲慢.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[困.png]">
								<div class="emoji-bg" data-w="[困.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[惊恐.png]">
								<div class="emoji-bg" data-w="[惊恐.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[流汗.png]">
								<div class="emoji-bg" data-w="[流汗.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[憨笑.png]">
								<div class="emoji-bg" data-w="[憨笑.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[悠闲.png]">
								<div class="emoji-bg" data-w="[悠闲.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[奋斗.png]">
								<div class="emoji-bg" data-w="[奋斗.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[咒骂.png]">
								<div class="emoji-bg" data-w="[咒骂.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[疑问.png]">
								<div class="emoji-bg" data-w="[疑问.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[嘘.png]">
								<div class="emoji-bg" data-w="[嘘.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[晕.png]">
								<div class="emoji-bg" data-w="[晕.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[衰.png]">
								<div class="emoji-bg" data-w="[衰.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[骷髅.png]">
								<div class="emoji-bg" data-w="[骷髅.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[敲打.png]">
								<div class="emoji-bg" data-w="[敲打.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[再见.png]">
								<div class="emoji-bg" data-w="[再见.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[擦汗.png]">
								<div class="emoji-bg" data-w="[擦汗.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[抠鼻.png]">
								<div class="emoji-bg" data-w="[抠鼻.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[鼓掌.png]">
								<div class="emoji-bg" data-w="[鼓掌.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[坏笑.png]">
								<div class="emoji-bg" data-w="[坏笑.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[左哼哼.png]">
								<div class="emoji-bg" data-w="[左哼哼.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[右哼哼.png]">
								<div class="emoji-bg" data-w="[右哼哼.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[哈欠.png]">
								<div class="emoji-bg" data-w="[哈欠.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[鄙视.png]">
								<div class="emoji-bg" data-w="[鄙视.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[委屈.png]">
								<div class="emoji-bg" data-w="[委屈.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[快哭了.png]">
								<div class="emoji-bg" data-w="[快哭了.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[阴险.png]">
								<div class="emoji-bg" data-w="[阴险.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[亲亲.png]">
								<div class="emoji-bg" data-w="[亲亲.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[可怜.png]">
								<div class="emoji-bg" data-w="[可怜.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[菜刀.png]">
								<div class="emoji-bg" data-w="[菜刀.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[西瓜.png]">
								<div class="emoji-bg" data-w="[西瓜.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[啤酒.png]">
								<div class="emoji-bg" data-w="[啤酒.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[咖啡.png]">
								<div class="emoji-bg" data-w="[咖啡.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[猪头.png]">
								<div class="emoji-bg" data-w="[猪头.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[玫瑰.png]">
								<div class="emoji-bg" data-w="[玫瑰.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[凋谢.png]">
								<div class="emoji-bg" data-w="[凋谢.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[嘴唇.png]">
								<div class="emoji-bg" data-w="[嘴唇.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[爱心.png]">
								<div class="emoji-bg" data-w="[爱心.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[心碎.png]">
								<div class="emoji-bg" data-w="[心碎.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[蛋糕.png]">
								<div class="emoji-bg" data-w="[蛋糕.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[炸弹.png]">
								<div class="emoji-bg" data-w="[炸弹.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[便便.png]">
								<div class="emoji-bg" data-w="[便便.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[月亮.png]">
								<div class="emoji-bg" data-w="[月亮.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[太阳.png]">
								<div class="emoji-bg" data-w="[太阳.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[拥抱.png]">
								<div class="emoji-bg" data-w="[拥抱.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[强.png]">
								<div class="emoji-bg" data-w="[强.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[弱.png]">
								<div class="emoji-bg" data-w="[弱.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[握手.png]">
								<div class="emoji-bg" data-w="[握手.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[胜利.png]">
								<div class="emoji-bg" data-w="[胜利.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[抱拳.png]">
								<div class="emoji-bg" data-w="[抱拳.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[勾引.png]">
								<div class="emoji-bg" data-w="[勾引.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[拳头.png]">
								<div class="emoji-bg" data-w="[拳头.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[OK.png]">
								<div class="emoji-bg" data-w="[OK.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[跳跳.png]">
								<div class="emoji-bg" data-w="[跳跳.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[发抖.png]">
								<div class="emoji-bg" data-w="[发抖.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[怄火.png]">
								<div class="emoji-bg" data-w="[怄火.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[转圈.png]">
								<div class="emoji-bg" data-w="[转圈.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[高兴.png]">
								<div class="emoji-bg" data-w="[高兴.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[口罩.png]">
								<div class="emoji-bg" data-w="[口罩.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[笑哭.png]">
								<div class="emoji-bg" data-w="[笑哭.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[吐舌头.png]">
								<div class="emoji-bg" data-w="[吐舌头.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[傻呆.png]">
								<div class="emoji-bg" data-w="[傻呆.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[恐惧.png]">
								<div class="emoji-bg" data-w="[恐惧.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[悲伤.png]">
								<div class="emoji-bg" data-w="[悲伤.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[不屑.png]">
								<div class="emoji-bg" data-w="[不屑.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[嘿哈.png]">
								<div class="emoji-bg" data-w="[嘿哈.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[捂脸.png]">
								<div class="emoji-bg" data-w="[捂脸.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[奸笑.png]">
								<div class="emoji-bg" data-w="[奸笑.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[机智.png]">
								<div class="emoji-bg" data-w="[机智.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[皱眉.png]">
								<div class="emoji-bg" data-w="[皱眉.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[耶.png]">
								<div class="emoji-bg" data-w="[耶.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[鬼脸.png]">
								<div class="emoji-bg" data-w="[鬼脸.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[合十.png]">
								<div class="emoji-bg" data-w="[合十.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[加油.png]">
								<div class="emoji-bg" data-w="[加油.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[庆祝.png]">
								<div class="emoji-bg" data-w="[庆祝.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[礼物.png]">
								<div class="emoji-bg" data-w="[礼物.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[红包.png]">
								<div class="emoji-bg" data-w="[红包.png]"></div>
							</div>
							<div class="wEmoji-item" data-emj="[鸡.png]">
								<div class="emoji-bg" data-w="[鸡.png]"></div>
							</div>
						</div>
					</div>
					<div class="emoji-subscript w-emoji"></div>
				</div>
			</div>
		</div>
	</div>
</body>
<script type="text/javascript" src="{{config('app.url_storage').'/assets/ugc/js/jquery-2.1.1.min.js'}}"></script>
<script type="text/javascript" src="{{config('app.url_storage').'/assets/ugc/js/md5.js'}}"></script>
<script type="text/javascript" src="{{config('app.url_storage').'/assets/ugc/js/vhallSDK.js'}}"></script>
<!-- <script type="text/javascript" src="//static.gensee.com/webcast/static/sdk/js/gssdk-1.3.js?201806v477"></script> -->
<script type="text/javascript" src="//static.gensee.com/webcast/static/sdk/js/gssdk.js"></script>
<script>
    var baseURL = "{{ config('app.ajax_url') }}"
	var talkshowList = []       // 直播列表
	var messageList = []        // 直播互动列表
	var selectEmojiing = false  // 选择emoji表情中
	var showEmoji = false       // 显示emoji区域
	var isLive = false          // 直播中
	var selectRoom;              // 直播室数据
	var sending = false            // 发送中
	var openId;                     // 识别身份的openId
	var cryptonymKey;               // 匿名用户身份验证
	var isReplyVoteIng;             // 点赞进行中
	var isFirstEnter = true;        // 首次进入
	var isFirstDiscuss = true;      // 首次讨论区
	var liveEndTime;                // 已结束直播的时间

	var UCURL = "{{ config('uc.url') }}"
	const REPORT_WAITING_TIME = 60;     // 直播上报时间间隔
	var totalSeekTime = 0;
	var lastPlayHeadTime = 0;
	var lastReportTime = 0;
	var currentPlayedTime = 0;
	var disableReporting = false;

	window.onload=function(){
		// 获取当天直播列表
		getTodayList()         

		// 初始化状态
		$(".wrap").click(function () {
		// resetMessage()
		})	

		// 匿名用户身份验证
		if (localStorage.getItem("zytg_cryptonym_key")) {
			cryptonymKey = localStorage.getItem("zytg_cryptonym_key")
		} else {
			getCryptonymKey()
		}	
		
		// 切换直播室右侧功能tab
		$(".tab").click(function(){
			$(".tab").removeClass("active")
			$(this).addClass("active")
			var index = $(this).index()
			if(index == 0){
				if(isLive) {
					getTodayList()
				}
				$(".programme").show()
				$(".interaction").hide()
			} else {
				$(".programme").hide()
				$(".interaction").show()
			}
		})

		// 输入区事件响应
		$("#textarea").on("input", function(){
			var num = $(this).val().length
			$("#limitLength").text(num + "/500")
		})

		// 评论及评论回复点赞
		$('.reply-list').on("click", ".vote", function () {
			isReplyVoteIng = true
			var recordId = $(this).attr("ref-id")
			$.ajax({
				type: "put",
				url: "{{ config('app.ajax_url') }}/api/v2/interaction/vote/ugc?record_id=" + recordId + "&type=live_discuss&udid=" + cryptonymKey,
				dataType: "json",
				xhrFields: {
				withCredentials: true
				},
				success: res => {
					if (res.code == 0) {
						var likeSum = res.data[0].like_sum
						if (res.data[0].is_like) {
							$(this).addClass("active")
							$(this).html("<img src=\"{{config('app.url_storage').'/assets/ugc/images/live_liked.png'}}\" alt=''> " + formatLikeNum(likeSum))
						} else {
							$(this).removeClass("active")
							$(this).html("<img src=\"{{config('app.url_storage').'/assets/ugc/images/live_like.png'}}\" alt=''> " + formatLikeNum(likeSum))
						}
					} else {
						alert(res.msg)
					}
					setTimeout(() => {
						isReplyVoteIng = false
					}, 1000)
				},
				error: error => {
					setTimeout(() => {
						isReplyVoteIng = false
					}, 1000);
					console.log(error)
				}
			})
		})


		$('.reply-list').click(function () {
			$(".w-emoji").hide()
			showEmoji = false
		})

		// 显示表情区域
		$(".emoji").click(function(){
			if(!showEmoji){
				$(".w-emoji").show()
				showEmoji = true
			} else {
				$(".w-emoji").hide()
				showEmoji = false
			}
		})

		// 发送新消息
		$(".send-btn").click(function(){
			sendMessage()
		})

		// emoji存值
		$(".wEmoji-item").click(function(){
			var a = $(this).attr("data-emj")
			// 获取当前信息长度
			var message = $("#textarea").val()
			var emojiCode = "[" + a.substring(1, a.indexOf(".png")) + "]"
			var totleLength = message.length + emojiCode.length                // 添加emoji后字符总长度
			// 输入emoji字符，更改发送状态
			if (!selectEmojiing) {
				textareaLeftPos = getCaretPosition($("#textarea")[0])
				textareaRightPos = message.length - textareaLeftPos 
				selectEmojiing = true
			}
			// 判断表情是否在中间部分插入
			if (message.length == textareaLeftPos) {
				$("#textarea").val(message + emojiCode)
			} else {
				$("#textarea").val(message.substring(0, message.length - textareaRightPos) + emojiCode + message.substring(message.length - textareaRightPos, message.length ))
			}

			// 统计输入区限制字数
			$("#limitLength").text(totleLength + "/500")
			if(500 < totleLength){
				$("#limitLength").css("color","red")
			} else {
				$("#limitLength").css("color","#999")
			}
		})
	}
	
	
	// 获取今日节目表，若有节目直播，自动返回直播中界面
	function getTodayList() {
		// play_status播放状态， 10=没有预告，20=蓝字预告，30=即将开始，40=正在播出，50=播放结束，60=看回放
		$.ajax({
			type: "get",
			url: baseURL + "/api/v2/client/talkshow/today-list",
			dataType: "json",
			xhrFields: {
				withCredentials: true
			},
			success: function (res) {
				if(res.code === 0) {
					talkshowList = res.data.talkshow_list
					var listHtml = ""
					if(talkshowList.length > 0) {
						for (var i = 0; i < talkshowList.length; i++) {
							if (talkshowList[i].play_status == 50) {
								listHtml += '<li class="active"><div class="time"><img src="{{config("app.url_storage")."/assets/ugc/images/time_active.png"}}" alt="">'
							} else if(talkshowList[i].play_status == 40 || talkshowList[i].play_status == 60) {
								listHtml += '<li class="active"><div class="time"><img src="{{config("app.url_storage")."/assets/ugc/images/time_active.png"}}" alt="">'

							} else {
								listHtml += '<li><div class="time"><img src="{{config("app.url_storage")."/assets/ugc/images/time_default.png"}}" alt="">'
							}
							listHtml += talkshowList[i].start_time + '-' + talkshowList[i].end_time + '</div>'
							+ '<div class="cont">'
							+	'<div class="title">' + talkshowList[i].title + '</div>'
							+	'<div class="author">'	
							+		'<div class="teacher">主讲嘉宾：<span>' + talkshowList[i].teacher_name + '</span></div>'
							if(talkshowList[i].play_status == 40) {
								if(isFirstEnter) {
									isLive = true
									selectRoom = talkshowList[i]
								}
								
								listHtml +=	'<div class="status active" onclick="getSign(' + i + ')"><img src="{{config("app.url_storage")."/assets/ugc/images/playing.png"}}" alt="" class="on"><img src="{{config("app.url_storage")."/assets/ugc/images/play.png"}}" alt="" class="off">播放中</div>'
							} else if (talkshowList[i].play_status == 50) {
								listHtml += '<div class="status lookback"><img src="{{config("app.url_storage")."/assets/ugc/images/look_back.png"}}" alt="" class="off"><img src="{{config("app.url_storage")."/assets/ugc/images/look_back_active.png"}}" alt="" class="on">看回放</div>'
							} else if (talkshowList[i].play_status == 60) {
								listHtml += '<div class="status lookback" onclick="lookBack(' + i + ')"><img src="{{config("app.url_storage")."/assets/ugc/images/look_back.png"}}" alt="" class="off"><img src="{{config("app.url_storage")."/assets/ugc/images/look_back_active.png"}}" alt="" class="on">看回放</div>'
							} else {
								listHtml +=	'<div class="status"><img src="{{config("app.url_storage")."/assets/ugc/images/play.png"}}" alt="">准备中</div>'
							}
							
							listHtml +=	'</div>'
							+ '</div>'
							+ '</li>'
						}
					}
					$("#list").html(listHtml)
					if(isLive && isFirstEnter) {
						getSign("live")
						isFirstEnter = false
					}
				}
			},
			error: function (err) {
				console.log(err)
			}
		})
	}
	
	// ------------------------------------------------微吼直播区域------------------------------------------------	
	// 直播引入
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
		    // x5_video_type: 'h5',
		    x5_video_fullscreen: 'false', 
		    protocol:'https'
		});

		VHALL_SDK.on('playerReady', function() {
			// 跳转到直播互动
			$(".tab")[1].click()
			// 直播上报
			setTimeout(function(){
				reportAction(0)
				if(selectRoom.type == "play") {
					window.$Player.openControls(false)
					window.$Player.openUI(false)
					// 定位到播出时间
					var seektime = allowDiscuss(selectRoom.start_time)
					window.$Player.vhallplayer.setCurrentTime(seektime*60, () => {
			          console.log('设置当前播放时间失败-----------')
			        })
				}
			}, 500);
			
			$('#video video').on("waiting",function(){  
				$('#loading').show();
			}); 
			$('#video video').on("loadeddata",function(){  
				if($('#video video')[0].paused){
					$("#video").append('<img src="{{config('app.url_storage').'/assets/ugc/images/video_pause.png'}}" alt="" class="pause-status video-control">')
				}
				$('#loading').hide();
			}); 
			$('#video video').on("play",function(){  
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

		$("#video").click(function(){
			if($(".video-control").length > 0) {
				$(".video-control").hide()
				window.$Player.vhallplayer.play()
			}
		})
	}

	// 获取签名,获取用户信息
	function getSign(index) {
		if(index != "live"){
			selectRoom = talkshowList[index]
			$(".status").removeClass("active")
			$(".status").eq(index).addClass("active")
		} 
		if(selectRoom.end_time == liveEndTime) {
			var liveLoading = '<div class="coffee-time">'
					+ '<div class="coffee">'
					+ 	'<img src="' + '{{config("app.url_storage")."/assets/ugc/images/coffee.png"}}' + '" alt="">'
					+ 	'<div>稍等片刻，直播即将开始...</div>'
					+ '</div>'
				+ '</div>'
			$("#video").html(liveLoading)
			return 
		}

		isLive = true
		$("#video").html('<img id="loading" src="data:image/gif;base64,R0lGODlhIAAgALMMAJ6enpmZmbi4uHNzc7+/v9nZ2czMzIyMjFlZWdHR0bKysqampgAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh/wtYTVAgRGF0YVhNUDw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MDAwQjM4RDhEMzNDMTFFMzkyODZGMzNBQ0YxNkFCRjQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MDAwQjM4RDlEMzNDMTFFMzkyODZGMzNBQ0YxNkFCRjQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDowMDBCMzhENkQzM0MxMUUzOTI4NkYzM0FDRjE2QUJGNCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDowMDBCMzhEN0QzM0MxMUUzOTI4NkYzM0FDRjE2QUJGNCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgH//v38+/r5+Pf29fTz8vHw7+7t7Ovq6ejn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">')
		$("#title").text(selectRoom.title)
		messageListInit()
		$.ajax({
			type: "get",
			url: baseURL + "/api/v2/token/vhall/sign?room_id=" + selectRoom.sdk_video_vodid,
			dataType: "json",
			xhrFields: {
				withCredentials: true
			},
			success: function (res) {
				if(res.code == 0) {
					openId = res.data.account
					if(selectRoom.video_vendor_code == "video_vhall") {
						vHallBeginPlay(selectRoom.sdk_video_vodid, res.data.vhall_sign, res.data.signedat, res.data.account, res.data.username)
					} else {
						genseeLive(selectRoom.sdk_video_vodid, res.data.account)
					}
					
				} else {
					alert("获取直播签名错误！")
				}
			},
			error: function (err) {
				console.log(err)
			}
		})
	}

	// 看回放
	function lookBack(index) {
		$(".status").removeClass("active")
		$(".status").eq(index).addClass("active")
		var room = talkshowList[index]
		selectRoom = room
		isLive = false
		messageList = []
		$(".reply-list").html("")
		$("#title").text(room.title)
		$("#video").html('<iframe src="' + room.play_url + '" frameborder="0"></iframe>')
	}

	// 获取展示互动直播
	function genseeLive(roomid, uid) {
		messageListInit()
		$("#title").text(selectRoom.title)
		if(selectRoom.type == "live"){
			$("#video").html('<gs:video-live id="videoComponent" site="fhcj.gensee.com" ctx="webcast" ownerid="' + roomid + '" uid="' + uid + '" bar="false" gsver="2"/>')
		} else {
			$("#video").html('<gs:video-vod id="videoComponent" ' 
				+ 'site="fhcj.gensee.com" ' 
				+ 'ctx="webcast" ownerid="' + roomid 
				+ '" py="1" ' 
				+ 'bar="false" '
				+ 'bgimg="https://static.gensee.com/webcast/static/mobile2015/images/video_b.jpg" '
				+ 'btnimg="https://static.gensee.com/webcast/static/mobile2015/images/triangle_img.png?v=4.6"/>')
			
			// 初始化点播区域
			GS.loadTag('video-vod', document.getElementsByTagName("gs:video-vod")[0]);
		}
		
		//根据组获得通讯通道
		var channel = GS.createChannel();
		var genseeTimer = null
		var seeked = false
		if(selectRoom.type == "play") {
			// 定位到播出时间
			channel.bind("onPlay", function (event) {
				if(!seeked) {
					var seektime = allowDiscuss(selectRoom.start_time)
					channel.send("seek", {
						"timestamp": seektime*60*1000
					});
					seeked = true
				}
				
			});
		}
		
		channel.bind("onStart", function(event){
			// 开始播放
			console.log("开始播放")
			genseeTimer = setInterval(() => {
				if(selectRoom.sdk_video_vodid == roomid) {
					reportAction(60);
				} else {
					clearInterval(genseeTimer)
				}
			}, 60000);
		})

		channel.bind("onStop", function(){
			getTodayList()
			clearInterval(genseeTimer)
			liveEndTime = selectRoom.end_time
			var liveLoading = '<div class="coffee-time">'
					+ '<div class="coffee"'
					+ 	'<img src="' + '{{config("app.url_storage")."/assets/ugc/images/coffee.png"}}' + '" alt="">'
					+ 	'<div>稍等片刻，直播即将开始...</div>'
					+ '</div>'
				+ '</div>'
			$("#video").html(liveLoading)
			alert("直播已结束！")
		})
	}

	// 直播互动初始化
	function textareaInit () {
		showEmoji = false
		$("#textarea").val("")
		$("#limitLength").text("0/500")
		$(".reply-bottom").height(60)
		$("#textarea").height(20)
		$(".w-emoji").hide()
	}

	// 获取直播互动内容
	function getMessageList () {
		var lastId = 0
		if(messageList.length > 0) {
			lastId = messageList[0].id
		}
		
		var discussData = {
			"talkshow_code": selectRoom.talkshow_code,
			"live_room_code": selectRoom.live_room_code,
			"last_discuss_id": lastId,
			"page_size": 100
		}
		$.ajax({
			type: "get",
			url: baseURL + "/api/v2/interaction/live/discuss/list",
			dataType: "json",
			data: discussData,
			xhrFields: {
				withCredentials: true
			},
			success: function (res) {
				// $(".reply-list").html("")
				var messageListHtml = ""
				var newMessageList = res.data.live_discuss_list
				if(res.code == 0) {
					if(lastId != 0) {
						messageList = [...res.data.live_discuss_list, ...messageList]
					} else {
						messageList = res.data.live_discuss_list
					}

					// if(res.data.live_discuss_list.length == 100) {
					// 	$(".reply-list").append('<div class="loading" onclick="loadMoreMessage()">点击加载更多...</div>')	
					// }

					for (let i = 0; i < newMessageList.length; i++) {
						// 20 通过审核  30 拒绝审核
						if(newMessageList[i].status == 20 || newMessageList[i].open_id == openId) {
							if (newMessageList[i].open_id == openId) {
								messageListHtml +=  '<div class="item"><div class="to-right">'
							} else {
								messageListHtml +=  '<div class="item"><div class="to-left">'
							}
							if(newMessageList[i].icon_url){
								messageListHtml += '<div class="avatar"><img src="' + newMessageList[i].icon_url + '" alt=""></div>'
							} else {
								messageListHtml += '<div class="avatar"><img src="' + '{{config("app.url_storage")."/assets/ugc/images/one.png"}}' + '" alt=""></div>'
							}
							messageListHtml += '<div class="message">'
								+		'<div class="intro"><span class="name">' + newMessageList[i].customer_name + '</span><span class="time">' + newMessageList[i].send_time + '</span></div>'
								+		'<div style="display: inline-block"><div class="richtext">' + explainEmoji(newMessageList[i].content) + '</div>'
								if(newMessageList[i].is_like){
									messageListHtml += '<div class="vote active">'
										+ '<img src="{{config("app.url_storage")."/assets/ugc/images/live_liked.png"}}" alt=""> '
								} else {
									messageListHtml += '<div class="vote" ref-id="' + newMessageList[i].id + '">'
										+ '<img src="{{config("app.url_storage")."/assets/ugc/images/live_like.png"}}" alt=""> '
								}								
								messageListHtml += formatLikeNum(newMessageList[i].like_sum) + '</div></div>'
								+	'</div>'
								+ '</div></div>'
						}
					}
					$(".reply-list").append(messageListHtml)

					// if(messageList.length <= 20) {
						setTimeout(() => {
							goMessageListBottom()
						}, 700);
					// }
				} else {
					alert(res.msg)
				}
			},
			error: function (err) {
				console.log(err)
			}
		})
	}

	// 加载更多message
	function loadMoreMessage() {
		$(".reply-list .loading").remove()
		getMessageList()
	}

	function messageListInit() {
		messageList = []
		$(".reply-list").html("")
		getMessageList()
	}

	// 发送互动内容
	function sendMessage () {
		if(sending) {
			return;
		}
		if(selectRoom.play_status == 60){
			alert("当前视频不支持直播互动！")
		} else {
			var message = $("#textarea").val()
			if(!message){
				alert("不能发送空消息！")
			} else {
				sending = true
				var discussData = {
					"talkshow_code": selectRoom.talkshow_code,
					"live_room_code": selectRoom.live_room_code,
					"content": $("#textarea").val()
				}
				$.ajax({
					type: "post",
					url: baseURL + "/api/v2/interaction/live/discuss",
					dataType: "json",
					data: discussData,
					xhrFields: {
						withCredentials: true
					},
					success: function (res) {
						if(res.code == 0) {
							getMessageList()
							textareaInit()							
						} else {
							alert(res.msg)
						}
						sending = false
					},
					error: function (err) {
						console.log(err)
						sending = false
					}
				})
			}
		}
	}

	// 发送上报信息
	function reportAction(during) {
		var now = new Date().getTime()
		now = Math.floor(now/1000)
		var reportObj = {
			columnId: "talkshow",
			itemId: selectRoom.talkshow_code,
			itemKind: "live",
			title: selectRoom.title,
			pageName: "pc_live",
			detailUrl: selectRoom.live_room_code,
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

    // 初始化表情区域
    function resetMessage() {
    	$('.reply-bottom').height(80)
    }

	// 评论列表滚动到底
	function goMessageListBottom () {
		setTimeout(() => {
			// 滚动到最底部
			var msgList = $(".interaction")[0]
			if (msgList) {
				msgList.scrollTop = msgList.scrollHeight;
			}
		}, 500);
	}

	// 解释表情代码
    function explainEmoji(str){
		// 使用emoji雪碧图表情图片
		var reg = new RegExp('\\[(.+?)\\]',"g")
		return str.replace(reg,function(str){
			if (str.length < 6) {
				var emojiname = str.substring(0, str.length - 1)
				var iconUrl = '<div class="emoji-bg" data-w="' + emojiname + '.png]"></div>'
				return iconUrl;
			} else {
				return str
			}
		});
    }

	// 格式化点赞数
	function formatLikeNum(num) {
		if(num == 0){
			return "赞"
		} else if(num > 999 || num == "999+") {
			return "999+"
		} else {
			return num
		}
	}

	  // 获取匿名用户身份码
	function getCryptonymKey() {
		var date = new Date().getTime()
		cryptonymKey = random(4) + '-' + hex_md5(date)
		localStorage.setItem("zytg_cryptonym_key", cryptonymKey)
	}

	// 返回随机数
	function random(length) {
		var str = Math.random().toString(36).substr(2);
		if (str.length >= length) {
			return str.substr(0, length);
		}
		str += random(length - str.length);
		return str;
    }
	
	function setHeight (obj) {
	  	var textareaH = obj.style.height
		if($("#textarea").val().length < 15) {
			obj.style.height = '20px';
	  		$(".reply-bottom").height(80 - 40 + 20)
		}
	  	if(obj.scrollHeight > 60) {
	  		obj.style.height = '60px';
	  		$(".reply-bottom").height(80 - 40 + 60)
	  	} else {
	  		obj.style.height = obj.scrollHeight + 'px';
	  		$(".reply-bottom").height(80 - 40 + obj.scrollHeight)
	  	}
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
	        throw new Error(10, "error");
	      }
	    }
	    return result;
	}
	  
	// 检查是否为直播
    function allowDiscuss(startTime) {
        var date = new Date()
        var h = date.getHours()
        var m = date.getMinutes()
        var now = h*60 + m
        var startH = Number(startTime.substring(0,2))
        var startM = Number(startTime.substring(3,5))
        var start = startH*60 + startM
        return now - start
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
</script>
</html>