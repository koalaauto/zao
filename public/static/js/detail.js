'use strict';
$(function() {
    // init program player
    if ( ! utils.isMobileClient()) {
        $('.post-content audio').mediaelementplayer({
            isVideo: false,
            enableKeyboard: false,
            alwaysShowControls: true,
            alwaysShowHours: true,
            videoVolume: 'horizontal',
            startVolume: 1,
            flashName: '/static/module/mediaelement-4.2.14/build/mediaelement-flash-video-hls.swf',
            hls: {
                path: '/static/module/hls.js-1.7.3/hls.min.js',
                debug: false
            },
            features: ['playpause','progress','current','duration','tracks','volume'],
            defaultSeekBackwardInterval: function(media) {
                return (media.duration * 0.02);
            },
            defaultSeekForwardInterval: function(media) {
                return (media.duration * 0.02);
            }
        });
    }

    // 手机端用原生 <audio> 直接放 m3u8 时（安卓尤其如此），播放器请求不带 Referer，
    // 被七牛防盗链 403。浏览器支持 MSE 时改用 hls.js 加载（会带 Referer），点播放才开始拉分片
    if (utils.isMobileClient()) {
        var $programAudios = $('.post-content audio');
        if ($programAudios.length) {
            $.ajax({
                url: '/static/module/hls.js-1.7.3/hls.min.js',
                dataType: 'script',
                cache: true
            }).done(function() {
                if ( ! window.Hls || ! Hls.isSupported()) {
                    return;
                }
                // iPhone/iPad 原生播放会带 Referer，一直能放，保持原样；安卓虽然也报能放 m3u8，但会 403
                var probe = $programAudios[0];
                if ( ! /Android/i.test(navigator.userAgent) && probe.canPlayType('application/vnd.apple.mpegurl')) {
                    return;
                }
                $programAudios.each(function() {
                    var audio = this;
                    var $source = $(audio).find('source');
                    var src = $source.attr('src');
                    if ( ! src || src.indexOf('.m3u8') === -1) {
                        return;
                    }
                    var hls = new Hls({autoStartLoad: false});
                    var started = false;
                    $source.remove();
                    hls.loadSource(src);
                    hls.attachMedia(audio);
                    $(audio).on('play', function() {
                        if ( ! started) {
                            started = true;
                            hls.startLoad(audio.currentTime);
                        }
                    });
                });
            });
        }
    }

    // init music player
    if ( ! utils.isMobileClient()) {
        $('.post-music .row').hover(function() {
            var $that = $(this);
            if ($that.find('.pause').is(':hidden')) {
                $that.find('.mask').removeClass('hide');
                $that.find('.play').removeClass('hide');
            }
        }, function() {
            var $that = $(this);
            $that.find('.mask').addClass('hide');
            $that.find('.play').addClass('hide');
        }); 
    }
    $('.post-music .cover').click(function() {
        var $cover = $(this);
        var $row = $cover.parent().parent().parent();
        var $play = $cover.children('.play');
        var $pause = $cover.children('.pause');
        var $audio = $cover.parent().siblings('audio');
        var audio = $audio[0];

        if ($pause.hasClass('hide')) {
            $('.post-music audio').each(function() {
                this.pause();
            });
            $('.post-music .pause').addClass('hide');
            $('.post-music .row').removeClass('row-bg');
            $play.addClass('hide');
            $pause.removeClass('hide');
            $row.addClass('row-bg');

            audio.src = $audio.attr('data-src');
            audio.play();
        } else {
            $pause.addClass('hide');
            $play.removeClass('hide');
            $row.removeClass('row-bg');
            $row.find('.mask').addClass('hide');
            $row.find('.play').addClass('hide');

            audio.pause();
        }
    });

    // tips
    if ( ! utils.isMobileClient()) {
        // bind tips
        $('.post-title, .post-meta').tipsy({
            gravity: 'e',
            html: true,
            delayOut: 5000,
            opacity: 0.5,
            offset: 10
        });

        // replace emoji to image in tips
        if (utils.isWinOs()) {
            var emoji = utils.emoji();
            $('.post-title, .post-meta').on('mouseover', function() {
                var tips = document.getElementsByClassName('tipsy-inner');
                for (var i = 0, n = tips.length; i < n; i++) {
                    tips[i].innerHTML = emoji.replace_unified(tips[i].innerHTML);
                }
            });
        }

        // trigger it
        $('.post-title, .post-meta li').each(function() {
            $(this).trigger('mouseover').trigger('mouseout');
        });
    }

    // set current program date
    var date = $('article').attr('data-date');
    $.cookie('program_date', date, {expires: 365, path: '/'});

    // load page view counts
    var url = '/program/' + date + '/pv';
    $.getJSON(url, function(data) {
        $('#post-view-counts').text(data.total);
    });

    // replace emoji to image in duoshuo comments
    $('.ds-comments').wait(function() {
        var emoji = utils.emoji();
        $(this).find('p').each(function() {
            this.innerHTML = emoji.replace_colons(this.innerHTML);
        });
    }, 5, 1000);
});
