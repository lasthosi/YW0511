/// <reference path="../jquery-1.10.2.min.js"/>
$(document).ready(function () {
    $(document).delegate('*', 'mouseenter', function (e) {
        $(e.currentTarget).addClass('hover');
    }).delegate('*.hover', 'mouseleave', function (e) {
        $(e.currentTarget).removeClass('hover');
    });
});