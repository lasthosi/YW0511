/// <reference path="jquery-1.10.2.min.js"/>
jQuery.slide = function (eltQuery) {
    var sets = {
        ver: { key_left: 'top' },
        hor: { key_left: 'left' }
    };
    function g_elt() {
        return $(eltQuery)
    }
    function play() {
        var elt = g_elt();
        if (elt.data('loopId')) return;
        var elt = $(eltQuery);
        elt.data('loopId', window.setInterval(next, elt.data('speed') + elt.data('waitTime')));
    }
    function pause() {
        var elt = g_elt();
        if (!elt.data('loopId')) return;
        window.clearInterval(elt.data('loopId'));
        elt.data('loopId', false);
    }
    function go(i, p) {
        if (!p) p = 1;
        var elt = g_elt(), inpanel = elt.children('.in').first(), indiv = inpanel.children().first(), nav = elt.find('.nav:first');
        var cur = elt.data('current'), next = i, curElt, curLink, nextElt, nextLink;
        if (cur == i) return;
        curElt = indiv.children('[data-i="' + cur + '"]:first');
        curLink = nav.children('[data-i="' + cur + '"]:first');
        nextElt = indiv.children('[data-i="' + next + '"]:first');
        nextLink = nav.children('[data-i="' + next + '"]:first');
        var curPos = curElt.offsetBy(inpanel), pos = nextElt.offsetBy(inpanel);
        var set = sets[elt.data('type')];
        if (p > 0) {
            if (pos[set.key_left] < curPos[set.key_left]) {
                var j = nextElt.index();
                indiv.children().each(function (i) {
                    if (i <= j) {
                        indiv.append(this);
                    } else {
                        return false;
                    }
                });
                var x = curPos[set.key_left];
                curPos = curElt.offsetBy(inpanel);
                x -= curPos[set.key_left];
                x += parseFloat(indiv.css(set.key_left)) || 0;
                indiv.css(set.key_left, x);
            }
        } else {
            if (pos[set.key_left] > curPos[set.key_left]) {
                var j = curElt.index();
                indiv.children().each(function (i) {
                    if (i <= j) {
                        indiv.append(this);
                    } else {
                        return false;
                    }
                });
                var x = curPos[set.key_left];
                curPos = curElt.offsetBy(inpanel);
                x -= curPos[set.key_left];
                x += parseFloat(indiv.css(set.key_left)) || 0;
                indiv.css(set.key_left, x);
            }
        }
        pos = nextElt.offsetBy(inpanel);
        curElt.removeClass('current');
        curLink.removeClass('current');
        nextElt.addClass('current');
        nextLink.addClass('current');
        elt.data('current', i);
        var ani = {}
        ani[set.key_left] = pos[set.key_left] > 0 ? '-=' + pos[set.key_left] : '+=' + (-pos[set.key_left]);
        indiv.stop().animate(ani, elt.data('speed'));
    }
    function next() {
        var elt = g_elt(), cur = elt.data('current'), indiv = elt.children('.in').children('div').first(), cs = indiv.children();
        cur++;
        if (cur >= cs.size()) {
            cur = 0;
        }
        go(cur, 1);
    }
    function pre() {
        var elt = g_elt(), cur = elt.data('current'), indiv = elt.children('.in').children('div').first(), cs = indiv.children();
        cur--;
        if (cur < 0) {
            cur = cs.size() - 1;
        }
        go(cur, -1);
    }
    function init() {
        var elt = g_elt(), indiv = elt.children('.in').children('div').first(), nav = elt.find('.nav:first');
        elt.data('type', elt.attr('data-type') == 'ver' ? 'ver' : 'hor');
        elt.data('speed', parseInt(elt.attr('data-speed') || 1000));
        elt.data('waitTime', parseInt(elt.attr('data-wait-time') || 2000));
        nav.html('');
        indiv.children().each(function (i, elt) {
            var t = $(this), item = t.children('[data-type="item"]'), link = t.children('[data-type="link"]');
            item.attr('data-i', i);
            link.attr('data-i', i);
            if (i == 0) {
                item.addClass('current');
                link.addClass('current');
            }
            nav.append(link);
            indiv.append(item);
            t.remove();
        });
        elt.data('current', 0);
        elt.mouseenter(pause).mouseenter(function () { $(this).addClass('hover'); });
        elt.mouseleave(play).mouseleave(function () { $(this).removeClass('hover'); });
        nav.delegate('a:not(.current)', 'mouseenter', function (e) {
            var i = parseInt($(e.currentTarget).attr('data-i'));
            go(i, g_elt().data('current') > i ? -1 : 1);
        });
        elt.find('.nav-btn:first').delegate('a', 'mouseenter', function (e) {
            $(e.currentTarget).addClass('hover');
        }).delegate('a', 'mouseleave', function (e) {
            $(e.currentTarget).removeClass('hover');
        }).delegate('a.pre', 'click', pre).delegate('a.next', 'click', next);
    }
    init();
    play();
};