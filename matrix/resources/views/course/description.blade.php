<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0" />
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <meta name="format-detection" content="telephone=no">
  <meta name="referrer" content="no-referrer" />
  <title>课程介绍</title>
  <link rel="stylesheet" href="{{config('app.url_storage').'/assets/ugc/css/reset.css'}}?v=20190420">
  <link rel="stylesheet" href="{{config('app.url_storage').'/assets/ugc/css/reply.css'}}?v=20191127">
  <link rel="stylesheet" href="{{config('app.url_storage').'/assets/ugc/css/forward.css'}}?v=20190422">
  <style>
    .cont {
      padding: 16px 15px;
      font-size: 15px;
      line-height: 21px;
      margin：0 auto;
      max-width: 1000px;
    }

    .cont .info {
      margin-top: 4px;
    }

    .cont .info img{
      max-width: 100%;
    }
  </style>
</head>
<body>
  <div class="wrap">
    <!-- 课程详情及评论区域 -->
    <div class="section-top slider-box">
      <div class="cont">
        @if($data['course']['full_text_description'])
        <b>课程介绍</b>
        <div class="info" id="courseInfo">{!! $data['course']['full_text_description'] !!}</div>
        @endif
      </div>
    </div>
  </div>
</body>
</html>
