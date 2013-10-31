/// <reference path="../jquery-1.10.2.min.js"/>
$(document).ready(function () {
    var $index_cat = $('div.index-cat>div.panel');
    $index_cat.each(function (e) {
        var p = $(this), cs = p.children();
        p.append('<div class="b"></div>');
        p.children('div').last().append(cs).children().addClass('b');
        p.prepend(cs.first().removeClass('b').addClass('a'));
    });
    $index_cat
        .delegate('button', 'click', function (e) {
            e.stopPropagation();
            e.preventDefault();
            return false;
        })
        .delegate('a.a,a.b', 'mouseenter', function (e) {
            var t = $(e.currentTarget), img = t.children('img'), p = t.children('p.i');
            var rb = p.css('bottom');
            p.css('bottom', 0);
            var pos1 = p.offset();
            p.css('bottom', '');
            var pos2 = p.offset();
            p.css('bottom', rb);
            img.stop().animate({ top: -(pos2.top - pos1.top) / 2 }, 200);
            p.stop().animate({ bottom: 0 }, 200);
        })
        .delegate('a.a,a.b', 'mouseleave', function (e) {
            var t = $(e.currentTarget), img = t.children('img'), p = t.children('p.i');
            var rb = p.css('bottom');
            p.css('bottom', 0);
            var pos1 = p.offset();
            p.css('bottom', '');
            var pos2 = p.offset();
            p.css('bottom', rb);
            img.stop().animate({ top: 0 }, 200);
            p.stop().animate({ bottom: pos1.top - pos2.top }, 200, function () {
                $(this).css('bottom', '');
            });
        });
});