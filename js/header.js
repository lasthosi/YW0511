/// <reference path="jquery-1.10.2.min.js"/>
$(document).ready(function () {
    var user_id = parseInt($('#user_id').val());
    var search_type = $('#search_type').val();
    function clear_space() {
        $('[data-clear-space="true"]').contents().each(function () {
            if (this.nodeType === 3) {
                var str = $.trim(this.data);
                if (str) {
                } else {
                    $(this).remove();
                }
            }
        });
    }
    clear_space();
    if (user_id) {
        $('div.head-body>div.tool>div.right>div.info>div.tab').addClass('login');
    }
    function select_hide(em, time) {
        em.stop();
        var ch = em.height();
        em.css('height', '');
        var h = em.height();
        em.height(ch);
        em.animate({ height: h }, time || 300, function () {
            $(this).css('height', '').removeClass('hover');
        });
    }
    $('div.head-body>div.tool div.select').mouseenter(function (e) {
        var em = $(this).stop().addClass('hover');
        var ch = em.height();
        em.css('height', 'auto');
        var mh = em.height();
        em.height(ch);
        em.animate({ height: mh }, 300, function () {
            $(this).css('height', 'auto');
        });
    }).mouseleave(function (e) {
        select_hide($(this));
    }).delegate('a', 'mousedown', function (e) {
        var tgt = $(e.currentTarget), sel = $(e.delegateTarget);
        sel.children('a.current').removeClass('current');
        tgt.addClass('current');
        $('#search_type').val(tgt.text());
        sel.prepend(tgt);
        select_hide(sel, 50);
    }).children('a').each(function () {
        var em = $(this);
        if (search_type == em.text()) {
            em.addClass('current');
        }
    });
    $('div.head-body:first>div.tool>div.right').delegate('div.info', 'mouseenter', function (e) {
        var tgt = $(e.currentTarget);
        tgt.children('div.tab').addClass('active');
        tgt.children('div.content').stop().slideDown(200);
    }).delegate('div.info', 'mouseleave', function (e) {
        var tgt = $(e.currentTarget);
        tgt.children('div.content').stop().slideUp(200, function () {
            tgt.children('div.tab').removeClass('active');
        });
    });

    // 分类菜单
    $('div.head-body>div.nav>div.navc').mouseenter(function () {
        var tgt = $(this);
        tgt.stop();
        var h = tgt.height();
        tgt.css('height', 'auto');
        var toh = tgt.height();
        tgt.css('height', h);
        tgt.stop().animate({ height: toh }, 450, function () {
            $(this).css('overflow', 'visible');
        });
    });
    $('div.head-body>div.nav>div.navc').mouseleave(function () {
        var tgt = $(this);
        tgt.stop();
        var h = tgt.height();
        tgt.css('height', '');
        var toh = tgt.height();
        tgt.css('height', h);
        $(this).css('overflow', 'hidden');
        tgt.stop().animate({ height: toh }, 450, function () {
            $(this).css('height', '');
        });
    });
    $('div.head-body>div.nav>div.navc').delegate('div.i', 'mouseenter', function (e) {
        var tgt = $(e.currentTarget);
        var p = tgt.children('.panel');
        if (p.children().size() == 0) return;
        tgt.children('.link').addClass('open');
        p.stop();
        p.css('top', 0);
        p.css('display', 'block');
        var w = p.width(), pos = p.offset(), doc = $(document);
        var win = $(window), win_h = win.height();
        var top = pos.top - doc.scrollTop(), bottom = top + p.outerHeight();
        var dy = 0;
        var itop = tgt.offset().top - doc.scrollTop(), ibottom = itop + tgt.outerHeight();
        bottom = Math.min(bottom, ibottom);
        if (bottom > win_h) {
            dy += win_h - bottom;
            top += dy;
        }
        top = Math.max(top, itop);
        if (top < 0) {
            dy -= top;
        }
        var ani = {
        };
        if (dy > 0) {
            ani.top = '+=' + dy;
        } else if (dy < 0) {
            ani.top = '-=' + (-dy);
        }
        p.css('opacity', 0);
        p.animate({ opacity: 1 }, 500);
        p.animate(ani, 150);
    }).delegate('div.i', 'mouseleave', function (e) {
        var tgt = $(e.currentTarget);
        var p = tgt.children('.panel');
        if (p.children().size() == 0) return;
        p.stop();
        tgt.children('.link').removeClass('open');
        p.css('display', 'none');
        p.css('top', 0);
    });
});