<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>不可预料的错误</title>
	<meta name=viewport content="width=device-width,initial-scale=1,user-scalable=0">
	<style>
		body{
			font-size: 13px;
		}
		.unknow{
			margin: 0 auto;
			text-align: center;
		}	
		.unknow-img{
			margin-top: 170px;
			width: 120px;
			text-align: center;
		}
		.unknow-tip{
			color: #999;
			margin-top: 12px;
			text-align: center;
		}
		.refresh{
			margin: 16px auto;
			border: 1px solid #298CFC;
			border-radius: 5px;
			width: 100px;
			height: 30px;
			line-height: 30px;
			text-align: center;
			color: #298CFC;
		}
		.refresh:active{
			color: #fff;
			background: #298CFC;
		}
	</style>
</head>
<body>
	<div class="unknow">
		<img src="{{config('app.url_storage').'/assets/ugc/images/unkonwError.png'}}" class="unknow-img" alt="">
	</div>
	<div class="unknow-tip">{{ $exception->getMessage() }}，请联系您的专属服务助理</div>
	<div class="refresh" onclick="refresh()">刷新试试</div>
</body>
<script>
	function refresh(){
  		window.location.reload();
	}
</script>
</html>
