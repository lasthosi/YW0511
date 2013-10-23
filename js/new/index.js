/// <reference path="../jquery-1.10.2.min.js"/>
$(document).ready(function () {
    $('div.index-cat>div.panel').each(function (e) {
        var p = $(this), cs = p.children();
        p.append('<div class="b"></div>');
        p.children('div').last().append(cs).children().addClass('b');
        p.prepend(cs.first().removeClass('b').addClass('a'));
    });
});