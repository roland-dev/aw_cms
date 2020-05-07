<html>
<head>
	<meta charset="UTF-8">
	<title>没有权限</title>
	<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no,minimal-ui">
    <meta name="format-detection" content="telephone=no">
	<style>
    .wrap {
      max-width: 768px;
      margin: 0 auto;
      padding: 6px 22px;
      color: #333;
    }
    .wrap.selection {
      padding: 16px;
    }
    .no-key {
      display: none;
      font-size: 12px;
      color: #999;
      text-align: center;
    }
    .no-key .error-img {
      width: 100px;
      margin-top: 140px;
    }
    .no-key .msg {
      margin-top: 14px;
    }
    .no-key .refresh {
      font-size: 18px;
      width: 200px;
      height: 48px;
      line-height: 48px;
      background: #fa3d41;
      color: #fff;
      border-radius: 4px;
      text-align: center;
      margin: 30px auto; 
    }
  </style>
</head>
<body style="">
	<div class="no-key" style="display: block;">
		<img src="{{config('app.url_storage').'/assets/ugc/images/openservice-icon.png'}}" alt="" class="error-img">
		<div class="msg">您尚未开通此服务，如有需要请联系您的客户经理<br>或拨打客服电话4006-9876-77咨询</div>
	</div>
</body>
</html>
