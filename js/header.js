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
});