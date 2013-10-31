/// <reference path="../jquery-1.10.2.min.js"/>
$(document).ready(function () {
    // 购物车功能
    $('html').append('<div id="temp_cart"></div>');
    var cart = {
        getFormAttrs: function (formBuy) {
            var spec_arr = new Array();
            var j = 0;

            for (i = 0; i < formBuy.elements.length; i++) {
                var prefix = formBuy.elements[i].name.substr(0, 5);

                if (prefix == 'spec_' && (
                  ((formBuy.elements[i].type == 'radio' || formBuy.elements[i].type == 'checkbox') && formBuy.elements[i].checked) ||
                  formBuy.elements[i].tagName.toLowerCase() == 'select')) {
                    spec_arr[j] = formBuy.elements[i].value;
                    j++;
                }
            }

            return spec_arr;
        },
        load: function () {
            $.ajax({
                url: 'flow.php?step=ajax_cart',
                success: function (result) {
                    if (result.error > 0) {
                        alert(result.message);
                    } else {
                        var html = result.content.replace(/<script>[^>]+<\/script>/ig, '');
                        var count = parseInt(result.message.substring(1)) || 0, i = result.message.indexOf('总计'), money = parseFloat(result.message.substring(i + 2)) || 0;
                        $('#buy-cart-count').text(count);
                        $('#temp_cart').html(html);

                    }
                },
                dataType: 'json',
                type: 'POST'
            });

        },
        add_callback: function (result) {
            if (result.error > 0) {
                // 如果需要缺货登记，跳转
                if (result.error == 2) {
                    if (confirm(result.message)) {
                        location.href = 'user.php?act=add_booking&id=' + result.goods_id + '&spec=' + result.product_spec;
                    }
                }
                    // 没选规格，弹出属性选择框
                else if (result.error == 6) {
                    alert('未选择选项，但是暂未完成相关代码，哦？？？？？？？？');
                    //openSpeDiv(result.message, result.goods_id, result.parent);
                }
                else {
                    alert(result.message);
                }
            }
            else {
                var cart_url = 'flow.php?step=cart';
                if (result.one_step_buy == '1') {
                    location.href = cart_url;
                }
                else {
                    switch (result.confirm_type) {
                        case '1':
                            if (confirm(result.message)) location.href = cart_url;
                            break;
                        case '2':
                            if (!confirm(result.message)) location.href = cart_url;
                            break;
                        case '3':
                            location.href = cart_url;
                            break;
                        default:
                            break;
                    }
                    cart.load();
                }
            }
        },
        add: function (goodsId, parentId) {
            var goods = {}, spec_arr = [], fittings_arr = [], number = 1, formBuy = document.forms['ECS_FORMBUY'], quick = 0;
            if (formBuy) {
                spec_arr = this.getFormAttrs(formBuy);
                if (formBuy.elements['number']) {
                    number = parseInt(formBuy.elements['number'].value) || 1;
                }
                quick = 1;
            }
            goods.quick = quick;
            goods.spec = spec_arr;
            goods.goods_id = goodsId;
            goods.number = number;
            goods.parent = parseInt(parentId) || 0;
            if (goodsId >= 48 && goodsId <= 55 && number > 1) {
                alert("套餐购买每次只能购买一份！");
                return;
            }
            $.ajax({
                url: 'flow.php?step=add_to_cart',
                data: { goods: JSON.stringify(goods) },
                success: this.add_callback,
                dataType: 'json',
                type: 'POST'
            });
        }
    };
    cart.load();
    $(document)
        .delegate('*', 'mouseenter', function (e) {
            $(e.currentTarget).addClass('hover');
        })
        .delegate('*.hover', 'mouseleave', function (e) {
            $(e.currentTarget).removeClass('hover');
        })
        .delegate('[data-fun="add2cart"]', 'mouseup', function (e) {
            var tgt = $(e.currentTarget);
            cart.add(tgt.attr('data-goods-id'), tgt.attr('data-goods-parent'));
        });
});