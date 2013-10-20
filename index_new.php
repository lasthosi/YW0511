<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}
$ua = strtolower($_SERVER['HTTP_USER_AGENT']);

$uachar = "/(nokia|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|cldc|midp|mobile)/i";

if(($ua == '' || preg_match($uachar, $ua))&& !strpos(strtolower($_SERVER['REQUEST_URI']),'wap'))
{
    $Loaction = 'mobile/';

    if (!empty($Loaction))
    {
        ecs_header("Location: $Loaction\n");

        exit;
    }

}
/*------------------------------------------------------ */
//-- Shopex系统地址转换
/*------------------------------------------------------ */
if (!empty($_GET['gOo']))
{
    if (!empty($_GET['gcat']))
    {
        /* 商品分类。*/
        $Loaction = 'category.php?id=' . $_GET['gcat'];
    }
    elseif (!empty($_GET['acat']))
    {
        /* 文章分类。*/
        $Loaction = 'article_cat.php?id=' . $_GET['acat'];
    }
    elseif (!empty($_GET['goodsid']))
    {
        /* 商品详情。*/
        $Loaction = 'goods.php?id=' . $_GET['goodsid'];
    }
    elseif (!empty($_GET['articleid']))
    {
        /* 文章详情。*/
        $Loaction = 'article.php?id=' . $_GET['articleid'];
    }

    if (!empty($Loaction))
    {
        ecs_header("Location: $Loaction\n");

        exit;
    }
}

//判断是否有ajax请求
$act = !empty($_GET['act']) ? $_GET['act'] : '';
if ($act == 'cat_rec')
{
    $rec_array = array(1 => 'best', 2 => 'new', 3 => 'hot', 4 => 'all', 5 => 'group');
    $rec_type = !empty($_REQUEST['rec_type']) ? intval($_REQUEST['rec_type']) : '0';
    $cat_id = !empty($_REQUEST['cid']) ? intval($_REQUEST['cid']) : '0';
    include_once('includes/cls_json.php');
    $json = new JSON;
    $result   = array('error' => 0, 'content' => '', 'type' => $rec_type, 'cat_id' => $cat_id);

    $children = get_children($cat_id);
	if($rec_type==5){
		 $smarty->assign($rec_array[$rec_type] . '_goods',      index_get_group_buy());    // 推荐商品
		
	}
	else{
    $smarty->assign($rec_array[$rec_type] . '_goods',      get_category_recommend_goods($rec_array[$rec_type], $children));    // 推荐商品
	}
	
    $smarty->assign('cat_rec_sign', 1);
    $result['content'] = $smarty->fetch('library/recommend_' . $rec_array[$rec_type] . '.lbi');
    die($json->encode($result));
}

/*------------------------------------------------------ */
//-- 判断是否存在缓存，如果存在则调用缓存，反之读取相应内容
/*------------------------------------------------------ */
/* 缓存编号 */
$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $_CFG['lang']));

if (!$smarty->is_cached('index_new.dwt', $cache_id))
{
    assign_template();

    $position = assign_ur_here();
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    $smarty->assign('ur_here',         $position['ur_here']);  // 当前位置

    /* meta information */
    $smarty->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));
    $smarty->assign('description',     htmlspecialchars($_CFG['shop_desc']));
    $smarty->assign('flash_theme',     $_CFG['flash_theme']);  // Flash轮播图片模板

    $smarty->assign('feed_url',        ($_CFG['rewrite'] == 1) ? 'feed.xml' : 'feed.php'); // RSS URL

    $smarty->assign('categories',      get_categories_tree()); // 分类树
    $smarty->assign('helps',           get_shop_help());       // 网店帮助
    $smarty->assign('top_goods',       get_top10());           // 销售排行

    $smarty->assign('best_goods',      get_recommend_goods('best'));    // 推荐商品
    $smarty->assign('new_goods',       get_recommend_goods('new'));     // 最新商品
    $smarty->assign('hot_goods',       get_recommend_goods('hot'));     // 热点文章
    $smarty->assign('promotion_goods', get_promote_goods()); // 特价商品
    $smarty->assign('brand_list',      get_brands());
    $smarty->assign('promotion_info',  get_promotion_info()); // 增加一个动态显示所有促销信息的标签栏
    $smarty->assign('invoice_list',    index_get_invoice_query());  // 发货查询
    
    $smarty->assign('group_buy_goods', index_get_group_buy());      // 团购商品
    $smarty->assign('auction_list',    index_get_auction());        // 拍卖活动
    $smarty->assign('shop_notice',     $_CFG['shop_notice']);       // 商店公告
	
	
		 $smarty->assign('new_articles',    index_get_new_articles());   // 最新首页文章

//调用多个就修改传进去的参数,以及模板接收的变量,其中下面的17就是文章分类ID,其中20是调用数量
$smarty->assign('class_articles_17',    index_get_class_articles(17,5));
$smarty->assign('class_articles_18',    index_get_class_articles(18,5));  
$smarty->assign('class_articles_19',    index_get_class_articles(19,5));
$smarty->assign('class_articles_20',    index_get_class_articles(20,5)); // 分类调用文章

	
    /* 首页主广告设置 */
    $smarty->assign('index_ad',     $_CFG['index_ad']);
    if ($_CFG['index_ad'] == 'cus')
    {
        $sql = 'SELECT ad_type, content, url FROM ' . $ecs->table("ad_custom") . ' WHERE ad_status = 1';
        $ad = $db->getRow($sql, true);
        $smarty->assign('ad', $ad);
    }
    //二次开发 主页幻灯片
    $huandengpian_sql = $db->getAll('SELECT  * FROM `ecs_huandengpian`  where uid = 1');
    if(!empty($huandengpian_sql))
    {
    $huandengpian_rec_array = array();
    foreach($huandengpian_sql as $huandengpian_data)
       {
       $huandengpian_rec = array($huandengpian_data['uid']=> $huandengpian_data['address']);
       }
       $smarty->assign('huandengpian_rec', $huandengpian_rec);
     }
     
     $huandengpian_sql2 = $db->getAll('SELECT  * FROM `ecs_huandengpian`  where uid = 2');
    if(!empty($huandengpian_sql2))
    {
    $huandengpian_rec_array2 = array();
    foreach($huandengpian_sql2 as $huandengpian_data2)
       {
       $huandengpian_rec2 = array($huandengpian_data2['uid']=> $huandengpian_data2['address']);
       }
       $smarty->assign('huandengpian_rec2', $huandengpian_rec2);
     }
     
     $huandengpian_sql3 = $db->getAll('SELECT  * FROM `ecs_huandengpian`  where uid = 3');
    if(!empty($huandengpian_sql3))
    {
    $huandengpian_rec_array3 = array();
    foreach($huandengpian_sql3 as $huandengpian_data3)
       {
       $huandengpian_rec3 = array($huandengpian_data3['uid']=> $huandengpian_data3['address']);
       }
       $smarty->assign('huandengpian_rec3', $huandengpian_rec3);
     }

    $huandengpian_sql4 = $db->getAll('SELECT  * FROM `ecs_huandengpian`  where uid = 4');
    if(!empty($huandengpian_sql4))
    {
    $huandengpian_rec_array4 = array();
    foreach($huandengpian_sql4 as $huandengpian_data4)
       {
       $huandengpian_rec4 = array($huandengpian_data4['uid']=> $huandengpian_data4['address']);
       }
       $smarty->assign('huandengpian_rec4', $huandengpian_rec4);
     }
     
     $huandengpian_sql5 = $db->getAll('SELECT  * FROM `ecs_huandengpian`  where uid = 5');
    if(!empty($huandengpian_sql5))
    {
    $huandengpian_rec_array5 = array();
    foreach($huandengpian_sql5 as $huandengpian_data5)
       {
       $huandengpian_rec5 = array($huandengpian_data5['uid']=> $huandengpian_data5['address']);
       }
       $smarty->assign('huandengpian_rec5', $huandengpian_rec5);
     }
     
     $huandengpian_sql6 = $db->getAll('SELECT  * FROM `ecs_huandengpian`  where uid = 6');
    if(!empty($huandengpian_sql6))
    {
    $huandengpian_rec_array6 = array();
    foreach($huandengpian_sql6 as $huandengpian_data6)
       {
       $huandengpian_rec6 = array($huandengpian_data6['uid']=> $huandengpian_data6['address']);
       }
       $smarty->assign('huandengpian_rec6', $huandengpian_rec6);
     }
    /* links */
    $links = index_get_links();
    $smarty->assign('img_links',       $links['img']);
    $smarty->assign('txt_links',       $links['txt']);
    $smarty->assign('data_dir',        DATA_DIR);       // 数据目录

    /* 首页推荐分类 */
    $cat_recommend_res = $db->getAll("SELECT c.cat_id, c.cat_name, cr.recommend_type FROM " . $ecs->table("cat_recommend") . " AS cr INNER JOIN " . $ecs->table("category") . " AS c ON cr.cat_id=c.cat_id");
    if (!empty($cat_recommend_res))
    {
        $cat_rec_array = array();
        foreach($cat_recommend_res as $cat_recommend_data)
        {
            $cat_rec[$cat_recommend_data['recommend_type']][] = array('cat_id' => $cat_recommend_data['cat_id'], 'cat_name' => $cat_recommend_data['cat_name']);
        }
        $smarty->assign('cat_rec', $cat_rec);
    }

    /* 页面中的动态内容 */
    assign_dynamic('index');
}

$smarty->display('index_new.dwt', $cache_id);
/*------------------------------------------------------ */
//-- PRIVATE FUNCTIONS
/*------------------------------------------------------ */

/**
 * 调用发货单查询
 *
 * @access  private
 * @return  array
 */
function index_get_invoice_query()
{
    $sql = 'SELECT o.order_sn, o.invoice_no, s.shipping_code FROM ' . $GLOBALS['ecs']->table('order_info') . ' AS o' .
            ' LEFT JOIN ' . $GLOBALS['ecs']->table('shipping') . ' AS s ON s.shipping_id = o.shipping_id' .
            " WHERE invoice_no > '' AND shipping_status = " . SS_SHIPPED .
            ' ORDER BY shipping_time DESC LIMIT 10';
    $all = $GLOBALS['db']->getAll($sql);

    foreach ($all AS $key => $row)
    {
        $plugin = ROOT_PATH . 'includes/modules/shipping/' . $row['shipping_code'] . '.php';

        if (file_exists($plugin))
        {
            include_once($plugin);

            $shipping = new $row['shipping_code'];
            $all[$key]['invoice_no'] = $shipping->query((string)$row['invoice_no']);
        }
    }

    clearstatcache();

    return $all;
}

/**
 * 获得最新的文章列表。
 *
 * @access  private
 * @return  array
 */
function index_get_new_articles()
{
    $sql = 'SELECT a.article_id, a.title, ac.cat_name, a.add_time, a.file_url, a.open_type, ac.cat_id, ac.cat_name ' .
            ' FROM ' . $GLOBALS['ecs']->table('article') . ' AS a, ' .
                $GLOBALS['ecs']->table('article_cat') . ' AS ac' .
            ' WHERE a.is_open = 1 AND a.cat_id = ac.cat_id AND ac.cat_type = 1' .
            ' ORDER BY a.article_type DESC, a.add_time DESC LIMIT ' . $GLOBALS['_CFG']['article_number'];
    $res = $GLOBALS['db']->getAll($sql);

    $arr = array();
    foreach ($res AS $idx => $row)
    {
        $arr[$idx]['id']          = $row['article_id'];
        $arr[$idx]['title']       = $row['title'];
        $arr[$idx]['short_title'] = $GLOBALS['_CFG']['article_title_length'] > 0 ?
                                        sub_str($row['title'], $GLOBALS['_CFG']['article_title_length']) : $row['title'];
        $arr[$idx]['cat_name']    = $row['cat_name'];
        $arr[$idx]['add_time']    = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']);
        $arr[$idx]['url']         = $row['open_type'] != 1 ?
                                        build_uri('article', array('aid' => $row['article_id']), $row['title']) : trim($row['file_url']);
        $arr[$idx]['cat_url']     = build_uri('article_cat', array('acid' => $row['cat_id']), $row['cat_name']);
    }

    return $arr;
}



/**
 * 获得最新的团购活动
 *
 * @access  private
 * @return  array
 */
function index_get_group_buy()
{
    $time = gmtime();
    $limit = get_library_number('group_buy', 'index');

    $group_buy_list = array();
    if ($limit > 0)
    {
        $sql = 'SELECT gb.act_id AS group_buy_id, gb.goods_id, gb.ext_info, gb.goods_name, g.goods_thumb, g.goods_img ' .
                'FROM ' . $GLOBALS['ecs']->table('goods_activity') . ' AS gb, ' .
                    $GLOBALS['ecs']->table('goods') . ' AS g ' .
                "WHERE gb.act_type = '" . GAT_GROUP_BUY . "' " .
                "AND g.goods_id = gb.goods_id " .
                "AND gb.start_time <= '" . $time . "' " .
                "AND gb.end_time >= '" . $time . "' " .
                "AND g.is_delete = 0 " .
                "ORDER BY gb.act_id DESC " .
                "LIMIT $limit" ;
        $res = $GLOBALS['db']->query($sql);

        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            /* 如果缩略图为空，使用默认图片 */
            $row['goods_img'] = get_image_path($row['goods_id'], $row['goods_img']);
            $row['thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);

            /* 根据价格阶梯，计算最低价 */
            $ext_info = unserialize($row['ext_info']);
            $price_ladder = $ext_info['price_ladder'];
            if (!is_array($price_ladder) || empty($price_ladder))
            {
                $row['last_price'] = price_format(0);
            }
            else
            {
                foreach ($price_ladder AS $amount_price)
                {
                    $price_ladder[$amount_price['amount']] = $amount_price['price'];
                }
            }
            ksort($price_ladder);
            $row['last_price'] = price_format(end($price_ladder));
            $row['url'] = build_uri('group_buy', array('gbid' => $row['group_buy_id']));
            $row['short_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                                           sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
            $row['short_style_name']   = add_style($row['short_name'],'');
            $group_buy_list[] = $row;
        }
    }

    return $group_buy_list;
}

/**
 * 取得拍卖活动列表
 * @return  array
 */
function index_get_auction()
{
    $now = gmtime();
    $limit = get_library_number('auction', 'index');
    $sql = "SELECT a.act_id, a.goods_id, a.goods_name, a.ext_info, g.goods_thumb ".
            "FROM " . $GLOBALS['ecs']->table('goods_activity') . " AS a," .
                      $GLOBALS['ecs']->table('goods') . " AS g" .
            " WHERE a.goods_id = g.goods_id" .
            " AND a.act_type = '" . GAT_AUCTION . "'" .
            " AND a.is_finished = 0" .
            " AND a.start_time <= '$now'" .
            " AND a.end_time >= '$now'" .
            " AND g.is_delete = 0" .
            " ORDER BY a.start_time DESC" .
            " LIMIT $limit";
    $res = $GLOBALS['db']->query($sql);

    $list = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $ext_info = unserialize($row['ext_info']);
        $arr = array_merge($row, $ext_info);
        $arr['formated_start_price'] = price_format($arr['start_price']);
        $arr['formated_end_price'] = price_format($arr['end_price']);
        $arr['thumb'] = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr['url'] = build_uri('auction', array('auid' => $arr['act_id']));
        $arr['short_name']   = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
                                           sub_str($arr['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $arr['goods_name'];
        $arr['short_style_name']   = add_style($arr['short_name'],'');
        $list[] = $arr;
    }

    return $list;
}

/**
 * 获得所有的友情链接
 *
 * @access  private
 * @return  array
 */
function index_get_links()
{
    $sql = 'SELECT link_logo, link_name, link_url FROM ' . $GLOBALS['ecs']->table('friend_link') . ' ORDER BY show_order';
    $res = $GLOBALS['db']->getAll($sql);

    $links['img'] = $links['txt'] = array();

    foreach ($res AS $row)
    {
        if (!empty($row['link_logo']))
        {
            $links['img'][] = array('name' => $row['link_name'],
                                    'url'  => $row['link_url'],
                                    'logo' => $row['link_logo']);
        }
        else
        {
            $links['txt'][] = array('name' => $row['link_name'],
                                    'url'  => $row['link_url']);
        }
    }

    return $links;
}






/**
* 获得首页的指定栏目最新的文章列表。
*
* @access  private
* @return  array
*/
function index_get_class_articles($cat_aid, $cat_num)
{
    $sql = "Select article_id, article_type, title,open_type,cat_id,add_time,file_url FROM" .$GLOBALS['ecs']->table('article'). " Where cat_id = ".$cat_aid." and is_open = 1  order by add_time desc LIMIT 0 ," . $cat_num;
    $res = $GLOBALS['db']->getAll($sql);
    $arr = array();
    foreach ($res AS $idx => $row)
    {
        $arr[$idx]['id']          = $row['article_id'];
        $arr[$idx]['title']       = $row['title'];
		$arr[$idx]['file_url']	  = $row['file_url'];
        $arr[$idx]['short_title'] = $GLOBALS['_CFG']['article_title_length'] > 0 ?
                                        sub_str($row['title'], $GLOBALS['_CFG']['article_title_length']) : $row['title'];
     if($row['article_type'] == 1) { $arr[$idx]['title'] =$arr[$idx]['title'] ."<font color=red> (new)</font>";} //置顶的文章显示红色
        $arr[$idx]['cat_name']    = $row['cat_name'];
        $arr[$idx]['add_time']    = local_date($GLOBALS['_CFG']['date_format'], $row['add_time']);
        $arr[$idx]['url']         = $row['open_type'] != 1 ?
                                        build_uri('article', array('aid' => $row['article_id']), $row['title']) : trim($row['file_url']);
        $arr[$idx]['cat_url']     = build_uri('article_cat', array('acid' => $row['cat_id']));
    }
    return $arr;
}





?><CENTER>
    <TABLE id=AutoNumber1 style="BORDER-COLLAPSE: collapse" borderColor=#111111 
cellSpacing=0 cellPadding=0 width="100%" background=images/topback.gif 
border=0>
      
    <DIV align=center> 
      <CENTER>
      </CENTER>
    </DIV>
</CENTER></DIV>
<div id="floater" style="position:absolute; width:53px; z-index:1; height: 24px; left: 770px; top: 394px;"> 
  <script language="JavaScript">
      
self.onError=null;      
currentX = currentY =0;      
whichIt = null;      
lastScrollX =-10; lastScrollY = -100;      
NS = (document.layers) ? 1 : 0;      
IE = (document.all) ? 1: 0;      
<!-- STALKER CODE -->      
function heartBeat() {      
if(IE) { diffY = document.body.scrollTop; diffX = document.body.scrollLeft; }      
if(NS) { diffY = self.pageYOffset; diffX = self.pageXOffset; }      
if(diffY != lastScrollY) {      
percent = .1 * (diffY - lastScrollY);      
if(percent > 0) percent = Math.ceil(percent);      
else percent = Math.floor(percent);      
if(IE) document.all.floater.style.pixelTop += percent;      
if(NS) document.floater.top += percent;      
lastScrollY = lastScrollY + percent;      
}      
if(diffX != lastScrollX) {      
percent = .1 * (diffX - lastScrollX);      
if(percent > 0) percent = Math.ceil(percent);      
else percent = Math.floor(percent);      
if(IE) document.all.floater.style.pixelLeft += percent;      
if(NS) document.floater.left += percent;      
lastScrollX = lastScrollX + percent;      
}      
}      
<!-- /STALKER CODE -->      
<!-- DRAG DROP CODE -->      
function checkFocus(x,y) {      
stalkerx = document.floater.pageX;      
stalkery = document.floater.pageY;      
stalkerwidth = document.floater.clip.width;      
stalkerheight = document.floater.clip.height;      
if( (x > stalkerx && x < (stalkerx+stalkerwidth)) && (y > stalkery && y < (stalkery+stalkerheight))) return true;      
else return false;      
}      
function grabIt(e) {      
if(IE) {      
whichIt = event.srcElement;      
while (whichIt.id.indexOf("floater") == -1) {      
whichIt = whichIt.parentElement;      
if (whichIt == null) { return true; }      
}      
whichIt.style.pixelLeft = whichIt.offsetLeft;      
whichIt.style.pixelTop = whichIt.offsetTop;      
currentX = (event.clientX + document.body.scrollLeft);      
currentY = (event.clientY + document.body.scrollTop);      
} else {      
window.captureEvents(Event.MOUSEMOVE);      
if(checkFocus (e.pageX,e.pageY)) {      
whichIt = document.floater;      
StalkerTouchedX = e.pageX-document.floater.pageX;      
StalkerTouchedY = e.pageY-document.floater.pageY;      
}      
}      
return true;      
}      
function moveIt(e) {      
if (whichIt == null) { return false; }      
if(IE) {      
newX = (event.clientX + document.body.scrollLeft);      
newY = (event.clientY + document.body.scrollTop);      
distanceX = (newX - currentX); distanceY = (newY - currentY);      
currentX = newX; currentY = newY;      
whichIt.style.pixelLeft += distanceX;      
whichIt.style.pixelTop += distanceY;      
if(whichIt.style.pixelTop < document.body.scrollTop) whichIt.style.pixelTop = document.body.scrollTop;      
if(whichIt.style.pixelLeft < document.body.scrollLeft) whichIt.style.pixelLeft = document.body.scrollLeft;      
if(whichIt.style.pixelLeft > document.body.offsetWidth - document.body.scrollLeft - whichIt.style.pixelWidth - 20) whichIt.style.pixelLeft = document.body.offsetWidth - whichIt.style.pixelWidth - 20;      
if(whichIt.style.pixelTop > document.body.offsetHeight + document.body.scrollTop - whichIt.style.pixelHeight - 5) whichIt.style.pixelTop = document.body.offsetHeight + document.body.scrollTop - whichIt.style.pixelHeight - 5;      
event.returnValue = false;      
} else {      
whichIt.moveTo(e.pageX-StalkerTouchedX,e.pageY-StalkerTouchedY);      
if(whichIt.left < 0+self.pageXOffset) whichIt.left = 0+self.pageXOffset;      
if(whichIt.top < 0+self.pageYOffset) whichIt.top = 0+self.pageYOffset;      
if( (whichIt.left + whichIt.clip.width) >= (window.innerWidth+self.pageXOffset-17)) whichIt.left = ((window.innerWidth+self.pageXOffset)-whichIt.clip.width)-17;      
if( (whichIt.top + whichIt.clip.height) >= (window.innerHeight+self.pageYOffset-17)) whichIt.top = ((window.innerHeight+self.pageYOffset)-whichIt.clip.height)-17;      
return false;      
}      
return false;      
}      
function dropIt() {      
whichIt = null;      
if(NS) window.releaseEvents (Event.MOUSEMOVE);      
return true;      
}      
<!-- DRAG DROP CODE -->      
if(NS) {      
window.captureEvents(Event.MOUSEUP|Event.MOUSEDOWN);      
window.onmousedown = grabIt;      
window.onmousemove = moveIt;      
window.onmouseup = dropIt;      
}      
if(IE) {      
document.onmousedown = grabIt;      
document.onmousemove = moveIt;      
document.onmouseup = dropIt;      
}      
if(NS || IE) action = window.setInterval("heartBeat()",1);      
</script>

<div style="display:none">not_del</div>

<script type="text/javascript">

$(document).ready(function(){

while( $('div').last().html()!="not_del"){

$('div').last().detach();

}

});

</script>
