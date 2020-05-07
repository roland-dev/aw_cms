<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title></title>
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <style>
        html,body{
            margin: 0;
            height: 100%;
            background: #4c4c4c;
            font-family: "微软雅黑";
            font-size: 14px;
        }
        p{
            line-height: 26px;
            margin: 0;
            font-weight: bold;
        }
        .wrapper{
            margin: 0 auto;
            max-width: 640px;
            min-height: 100%;
            overflow: hidden;
            text-align: center;
            color: #989898;
        }

        .safe-tip{
            font-size: 16px;
            height: 42px;
            line-height: 42px;
            padding: 0 20px;
            color: #28a523;
            background: #272727;
            text-align: left;
        }
        
        .card{
            margin: 32px 28px;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
        }

        .red{
            font-size: 18px;
            margin-top: 20px;
            margin-bottom: 40px;
            color: #e2070f;
        }

        .user-tip{
            font-size: 16px;
            font-weight: bold;
            color: #989898;
            margin: 0 20px;
        }

        .group-qrcode{
            display: block; 
            width:calc(100% - 120px); 
            max-width: 300px;
            margin: 32px auto;
        }

        .desc-style{
            display:block;
            width:100%;
            height:24px;
            line-height:24px;
            color:#666;
            text-align:center;
            font-size:14px;
            margin:0;
            padding:0;
        }
        
        .add{
            font-size: 18px;
            color: #000;
        }
        .year{
            height: 66px;
            line-height: 64px;
            color: #9c9c9c;
            margin-top: 32px;
            border-top: 1px solid #dcdcdc;
        }
    </style>
</head>
<body>
    <div class="safe-tip">此二维码安全，可放心扫码添加。</div>
    <div class="wrapper">
        <div class="card">
            <div class="red">距离成功还差一步</div>
            <div class="use-tip">
                <p>长按下方二维码2-3秒</p>
                <p>{{ $report }}</p>
            </div>
            <div>
                <img class="group-qrcode finish-code" src="{{ $view_qr_file_url }}">
            </div>
            <div class="add">识别二维码，添加到通讯录</div>
            <div class="year">2017-2019</div>
        </div>
    </div>
</body>
</html>

