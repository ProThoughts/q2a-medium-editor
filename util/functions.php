<?php

require_once(MEDIUM_EDITOR_DIR.'/vendor/phpQuery-onefile.php');

/*
 * プログレスバーを削除する
 */
function qme_remove_progressbar($content)
{
    $pq = phpQuery::newDocument($content);
    
    $elements = $pq['div.mdl-progress'];
    
    foreach ($elements as $elem) {
        pq($elem)->remove();
    }
    
    $html = $pq->html();
    $pq = null;
    return $html;
}

/*
 * 指定したタグに style 属性ある場合にそのタグを削除する
 * タグ内のコンテンツは残す
 */
function qme_remove_style($tag, $content)
{
    $pq = phpQuery::newDocument($content);

    $elements = $pq[$tag];

    foreach ($elements as $elem) {
        if (!empty(pq($elem)->attr('style'))) {
            pq($elem)->contentsUnwrap();
        }
    }

    return $pq->html();
}

/*
 * 本文末尾の改行を削除
 */
function qme_remove_br_tags($content)
{
    $pq = phpQuery::newDocument($content);

    $elements = $pq['p'];

    // 処理しやすくするためpタグ内余計なスペース削除
    foreach ($elements as $elem) {
        $text = pq($elem)->html();
        pq($elem)->html( qme_mb_trim($text) );
    }
    $tmp = $pq->html();
    $regex = "/<p class=\"medium-insert-active\">(<br>|\s|　)*<\/p>/Us";
    $regex2 = "/(<p class=\"\">(<br>|\s|　)*<\/p>|\s)*$/Us";
    $tmp = preg_replace($regex, "", $tmp);
    return preg_replace($regex2, "", $tmp);
}

/*
 * 文字列の前後の空白を取り除く
 */
 function qme_mb_trim($string)
 {
     $string = preg_replace( "/^[\s　]*(.*?)[\s　]*$/u", "$1", $string);
     return $string;
 }

/*
 * 文字列末尾の改行を削除、div で囲まれたバージョン
 * 表示する場合 <div class="entry-content"></di> で囲まれている
 */
function qme_remove_br_tags_in_div($content)
{
    $pq = phpQuery::newDocument($content);

    $elements = $pq['div.entry-content'];

    foreach ($elements as $elem) {
        $text = pq($elem)->html();
        pq($elem)->html( qme_remove_br_tags($text) );
    }

    return $pq->html();
}

/*
 * 指定したタグを削除する
 * $tag: 'img', 'div.medium-insert-buttons' など
 */
function qme_remove_tag($tag, $content)
{
    $pq = phpQuery::newDocument($content);

    $elements = $pq[$tag];

    foreach ($elements as $elem) {
        pq($elem)->remove();
    }

    $html = $pq->html();
    $pq = null;
    return $html;
}

/*
 * div.image-url が div.medium-insert-images で囲まれていない場合があるので対処
 */
function qme_wrapping_images($content)
{
    $pq = phpQuery::newDocument($content);

    $elements = $pq['div.image-url'];

    foreach ($elements as $elem) {
        if (strpos(pq($elem)->parent()->attr('class'), 'medium-insert-images') === false) {
            $element = pq($elem);
            $element->replaceWith('<div class="medium-insert-images">'.$element.'</div>');
        }
    }

    $html = $pq->html();
    $pq = null;
    return $html;
}

/*
 * [image="{src}"] の {src}に a タグが付くので削除する
 */
function qme_remove_anchor($content)
{
  $pq = phpQuery::newDocument($content);
  
  $elements = $pq['div.image-url a'];
  
  foreach ($elements as $elem) {
    pq($elem)->contentsUnwrap();
  }
  $html = $pq->html();
  $pq=null;
  return $html;
}

/*
 * div.insert-images を消す
 */
function qme_unwrapping_images($content)
{
    $pq = phpQuery::newDocument($content);

    $elements = $pq['div.insert-images'];

    foreach ($elements as $elem) {
        pq($elem)->contentsUnwrap();
    }

    $html = $pq->html();
    $html = qme_remove_images_class($html);
    $pq = null;
    return $html;
}

/*
 * div.medium-insert-images 直下に div.image-url がない場合は class を削除
 */
function qme_remove_images_class($content)
{
  $pq = phpQuery::newDocument($content);
  
  $elements = $pq['div.medium-insert-images'];
  
  foreach ($elements as $elem) {
    $element = pq($elem);
    $imgtag = $element['>div.image-url'];
    if ($imgtag == '') {
      $element->attr('class', '');
    }
  }
  
  $html = $pq->html();
  $pq = null;
  return $html;
}