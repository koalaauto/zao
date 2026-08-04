<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
        <meta name="renderer" content="webkit">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title>{{ $title or '早' }} - 飞鱼秀非官方回放 - 大熊的个人站</title>
        <meta name="keywords" content="早嗷嗷嗷嗷 飞鱼秀 回放 下载 小飞 喻舟">
        <meta name="description" content="{{ $description or '亲爱的飞鱼人，这里可以在线收听或下载自 2004 年飞鱼秀开播以来的几乎所有的节目回放喔~' }}">
        <link type="text/plain" rel="author" href="/humans.txt">
        <link rel="stylesheet" href="/static/module/tipsy/tipsy.css">
        <link rel="stylesheet" href="/static/module/AmaranJS/css/amaran.min.css">
        <link rel="stylesheet" href="/static/css/font-awesome.min.css">
        <link rel="stylesheet" href="/static/css/main.css">
        <link rel="stylesheet" href="/static/css/amaran.css">
        <script src="/static/module/jquery/jquery-2.1.4.min.js"></script>
        <script src="/static/module/jquery/jquery.cookie.js"></script>
        <script src="/static/module/tipsy/jquery.tipsy.js"></script>
        <script src="/static/module/js-emoji/js/emoji.min.js"></script>
        <script src="/static/module/AmaranJS/js/jquery.amaran.min.js"></script>
        <script src="/static/js/common.js"></script>
        <script src="/static/js/base.js"></script>
        <!--[if lt IE 9]>
            <script src="http://cdn.staticfile.org/html5shiv/r29/html5.min.js"></script>
            <script src="http://cdn.staticfile.org/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-BTP68EH1Q2"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
          gtag('config', 'G-BTP68EH1Q2');
        </script>
    </head>
    <body>
        <header>
            <div class="sidebar">
                <img class="avatar" src="/static/img/avatar.jpg">
                <h2 class="description">再见飞鱼秀，不散的飞鱼人</h2>
                <form action="/" method="get" class="search">
                    <i class="fa fa-search"></i>
                    <input type="search" name="s" value="{{ $_GET['s'] or '' }}" placeholder="" autocomplete="off">
                </form>
                <nav>
                    <a href="{{ URL('/') }}"><i class="fa fa-home" original-title="首页"></i></a>
                    <a href="{{ URL('music') }}"><i class="fa fa-music" original-title="歌曲"></i></a>
                    <!--a href="{{ URL('program/apptoday') }}"><i class="fa fa-calendar-check-o" original-title="APP 同期节目"></i></a-->
                    <a href="{{ URL('here') }}"><i class="fa fa-map-o" original-title="签到"></i></a>
                    <a href="https://pan.baidu.com/s/11ZcPOWpNRjvGCulOdCSQJg" rel="nofollow" target="_blank"><i class="fa fa-arrow-down" original-title="下载-提取码-ZAOO"></i></a>
                    <a href="{{ URL('about') }}"><i class="fa fa-about" original-title="关于"></i></a>
                </nav>

                @if ( ! Agent::isMobile())
                <a href="/{{ $cover['original'] }}" target="_blank" class="image-copyright">{{ $cover['copyright'] }}</a>
                @endif
            </div>
        </header>

        <div class="content" id="content" data-notification='{!! $notification or "{}" !!}'>

            @yield('content')

            <footer id="footer">
                © 2016 <a href="{{ URL('/') }}">Zao~ao~ao~ao~ao.com</a>
                <a href="https://koalaauto.com" rel="noopener" target="_blank" class="link">@KoalaAuto</a>
                @yield('footer_extra')
            </footer>
        </div>

        <script src="/static/js/cn-tw.js"></script>
    </body>
</html>
