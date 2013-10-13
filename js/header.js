/// <reference path="jquery-1.10.2.min.js"/>
$(document).ready(function () {
    var search_type = $('#search_type').val();
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
});