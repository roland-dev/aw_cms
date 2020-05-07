<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="white">
    <meta name="referrer" content="no-referrer" />
    <title>{{ $data['name'] }}</title>
    <style>
      body {
        margin: 0;
      }
      .warp {
        margin: 0 auto;
        color: #333;
        word-break: break-all;
        max-width: 1000px;
      }

      .kit {
        color: #333;
      }

      .kit .cover {
        width: 100%;
      }

      .kit .cover img {
        width: 100%;
      }

      .kit .cover .name {
        position: absolute;
        top: 0;
        margin: 22px 20px;
        font-size: 28px;
        color: #FFEFEA;
        max-width: 760px;
        display:-webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow:hidden;
      }

      .kit iframe {
        margin: 16px 0;
      }
      .kit .content {
        min-height: 180px;
        margin: 16px 15px 0px;
        font-size: 15px;
        line-height: 21px;
        color: #333333;
      }
      .kit .content ul,
      .kit .content ol {
        margin: 10px 0 10px 20px;
      }
      .kit .content ul li {
        list-style: disc;
      }
      .kit .content ol li {
        list-style: decimal;
      }
      .kit .content p {
        margin: 10px 0;
        line-height: 2;
        text-align: justify;
      }
      .kit .content .iframe {
        margin: 16px 0;
      }
      .kit .content img {
        max-width: 100%;
      }
    </style>
  </head>

  <body>
    <div class="warp">
      <div class="kit">
        <div class="cover">
          <img src="{{ $data['cover_url'] }}">
          <div class="name">{{ $data['name'] }}</div>
        </div>
        <div class="content">{!! $data['descript'] !!}</div>
      </div>
    </div>
  </body>
</html>