<?php
namespace app\modules;

use php\gui\UXImageViewWrapper;
use std, gui, framework, app;


class AdvancedVK extends AbstractModule
{
    public static function cache($url,$obj,$round = false) 
    {
        if (!is_dir('./cache/'))
        {
            mkdir('./cache/');
        }
        $th = new Thread(function () use ($url,$obj,$round) {
            $dir = './cache/'.md5($url).'.png';
            file_put_contents($dir,file_get_contents($url));
            uiLater(function () use ($dir,$obj,$round) {
                Element::loadContentAsync($obj,$dir, function () use ($obj,$round) {
                    if ($round == true)
                    {
                        $obj = self::setBorderRadius($obj,255);
                    }
                });
            });
        });
        $th->start();
    }
    
    public static function get_source_from_news($source_id, $news_obj)
    {
        if ($source_id < 0)
        {
            foreach ($news_obj['response']['groups'] as $group_obj)
            {
                if ($group_obj['id'] == $source_id*-1)
                {
                    return $group_obj;
                }
            }
        }
        else 
        {
            foreach ($news_obj['response']['profiles'] as $user_obj)
            {
                if ($user_obj['id'] == $source_id)
                {
                    $user_obj['name'] = $user_obj['first_name'].' '.$user_obj['last_name'];
                    return $user_obj;
                }
            }
        }
    }
    
    public static function bigValue($value)
    {
        if ($value > 1000)
        {
            return round($value/1000).'K';
        }
        elseif ($value > 1000000)
        {
            return round($value/1000).'M';
        }
        elseif ($value > 1000000000) 
        {
            return round($value/1000).'B';
        }
        else 
        {
            return $value;
        }
    }
    
    public static function showDate( $date ) // $date --> время в формате Unix time
    {
        $stf      = 0;
        $cur_time = Time::seconds();
        $diff     = $cur_time - $date;
     
        $seconds = ['секунда', 'секунды', 'секунд'];
        $minutes = ['минуту', 'минуты', 'минут'];
        $hours   = [ 'час', 'часа', 'часов' ];
        $days    = [ 'день', 'дня', 'дней' ];
        $weeks   = [ 'неделя', 'недели', 'недель'];
        $months  = [ 'месяц', 'месяца', 'месяцев'];
        $years   = [ 'год', 'года', 'лет'];
        $decades = [ 'десятилетие', 'десятилетия', 'десятилетий'];
     
        $phrase = [ $seconds, $minutes, $hours, $days, $weeks, $months, $years, $decades];
        $length = [ 1, 60, 3600, 86400, 604800, 2630880, 31570560, 315705600];
     
        for ( $i = sizeof( $length ) - 1; ( $i >= 0 ) && ( ( $no = $diff / $length[ $i ] ) <= 1 ); $i -- ) {
            ;
        }
        if ( $i < 0 ) {
            $i = 0;
        }
        $_time = $cur_time - ( $diff % $length[ $i ] );
        $no    = floor( $no );
        $value = sprintf( "%d %s", $no, self::getPhrase( $no, $phrase[ $i ] ) );
     
        if ( ( $stf == 1 ) && ( $i >= 1 ) && ( ( $cur_time - $_time ) > 0 ) ) {
            $value .= time_ago( $_time );
        }
     
        return $value . ' назад';
    }
     
    function getPhrase( $number, $titles ) {
        $cases = [2, 0, 1, 1, 1, 2];
     
        return $titles[ ( $number % 100 > 4 && $number % 100 < 20 ) ? 2 : $cases[ min( $number % 10, 5 ) ] ];
    }
    
    public static function share_modal()
    {
        
    }
    
    public static function msg_date($date)
    {
        $time = (new Time($date * 1000))->toString('HH:mm');
        return $time;
    }
    
    public static function generate_news($news_obj,$to)
    {
        file_put_contents('news.log.txt', json_encode($news_obj));
        foreach ($news_obj['response']['items'] as $obj)
        {
            $mm = new UXVBox;
            $mm->alignment = 'CENTER';
            $main = new UXVBox;
            $main->maxWidth = 540;
            if (isset($obj['owner_id']))
            {
                $source = self::get_source_from_news($obj['owner_id'], $news_obj);
            }
            else 
            {
                $source = self::get_source_from_news($obj['source_id'], $news_obj);
            }
            $post_source_img = new UXImageView;
            $post_source_img->size = [40,40];
            AdvancedVK::cache($source['photo_50'], $post_source_img, true);
            $post_source_name = new UXLabel($source['name']);    
            $post_source_date = new UXLabel(AdvancedVK::showDate($obj['date']));
            $post_source_texts_box = new UXVBox([$post_source_name,$post_source_date]);
            $post_source_img_texts_box = new UXHBox([$post_source_img,$post_source_texts_box]);
            $post_source_img_texts_box->alignment = 'CENTER_LEFT';
            $main->add($post_source_img_texts_box);
            $post_content = new UXVBox();
            $post_content->alignment = 'CENTER';
            if ($obj['text'] != '')
            {
                $post_text = new UXLabel();
                $post_content->add($post_text);
                if (count(explode("\n",$obj['text'])) > 7)
                {
                    $post_text->maxHeight = 84;
                    $post_text_expand = new UXHyperlink;
                    $post_text_expand->text = 'Показать полностью...';
                    $post_text_expand->on('click', function () use ($post_text,$post_text_expand) {
                    if ($post_text->maxHeight == 84)
                    {
                        $post_text->maxHeight = -1;
                        $post_text_expand->text = 'Свернуть';
                    }
                    else 
                    {
                        $post_text->maxHeight = 84;
                        $post_text_expand->text = 'Показать полностью...';
                    }
                    });
                    $post_content->add($post_text_expand);
                }
                $post_text->classesString = 'post_content_text';
                $post_text->text = $obj['text'];
                $post_text->wrapText = true;
                $post_text->maxWidth = 530;
                $post_text->textColor = 'white';
                
            }
            if (isset($obj['copy_history']))
            {
                $post_content->add(AdvancedVK::wall_post($news_obj,$obj['copy_history']));
            }
            if (isset($obj['attachments']))
            {
                $post_content->add(self::news_attachments($obj['attachments']));
            }
            $post_counters = new UXHBox;
            $post_counters->padding = 4;
            $post_counters_like = new UXFlatButton;
            $post_counters_like->text = AdvancedVK::bigValue($obj['likes']['count']).' ';
            $post_counters_like->on('click',function () use ($obj, $post_counters_like) {
                if ($obj['likes']['user_likes'] == 0)
                {
                    if (isset($obj['source_id']) || isset($obj['post_id']))
                    {
                        SimpleVK::Query('likes.add', ['type'=>'post','owner_id'=>$obj['source_id'],'item_id'=>$obj['post_id']]);
                    }
                    else 
                    {
                        SimpleVK::Query('likes.add', ['type'=>'post','owner_id'=>$obj['owner_id'],'item_id'=>$obj['id']]);
                    }
                    $post_counters_like->text = AdvancedVK::bigValue($obj['likes']['count']+1).' ';
                    $obj['likes']['user_likes'] = 1;
                }
                else 
                {
                    if (isset($obj['source_id']) || isset($obj['post_id']))
                    {
                        SimpleVK::Query('likes.delete', ['type'=>'post','owner_id'=>$obj['source_id'],'item_id'=>$obj['post_id']]);
                    }
                    else 
                    {
                        SimpleVK::Query('likes.delete', ['type'=>'post','owner_id'=>$obj['owner_id'],'item_id'=>$obj['id']]);
                    }
                    $post_counters_like->text = AdvancedVK::bigValue($obj['likes']['count']).' ';
                    $obj['likes']['user_likes'] = 0;
                }
            })
            $post_counters->add($post_counters_like);
            
            $main->classesString = 'post_box';
            $post_source_texts_box->classesString = 'post_source_texts_box';
            $post_source_name->classesString = 'post_source_name';
            $post_source_date->classesString = 'post_source_date';
            $post_content->classesString = 'post_content_box';
            $post_source_img_texts_box->classesString = 'main_info';
            $post_counters->classesString = 'post_counters_box';
            $post_counters_like->classesString = 'post_counters_like';
            $main->add($post_content);
            $main->add($post_counters);
            $mm->add($main);
            $to->add($mm);
        }
    }
    
    public static function wall_post($news_obj,$wall_post_obj,$pref_width = 530)
    {
        foreach ($wall_post_obj as $wall_obj)
        {
            $post_attachment_wall_post_main = new UXHBox;
            $post_attachment_wall_post_main->maxWidth = $pref_width;
            $post_attachment_wall_post_sep = new UXSeparator;
            $post_attachment_wall_post_sep->orientation = 'VERTICAL';
            $post_attachment_wall_post_main->add($post_attachment_wall_post_sep);
            $post_attachment_wall_post_box = new UXVBox;
            $post_attachment_wall_post_box->maxWidth = ($pref_width-10);
            $post_attachment_wall_post_main->add($post_attachment_wall_post_box);
            $source = self::get_source_from_news($wall_obj['from_id'], $news_obj);
            $post_attachment_wall_post_source_photo = new UXImageView;
            $post_attachment_wall_post_source_photo->size = [35,35];
            AdvancedVK::cache($source['photo_50'],$post_attachment_wall_post_source_photo,true);
            $post_attachment_wall_post_name = new UXLabel($source['name']);
            $post_attachment_wall_post_date = new UXLabel(self::showDate($wall_obj['date']));
            $post_attachment_wall_post_info_box = new UXVBox([$post_attachment_wall_post_name,$post_attachment_wall_post_date]);
            $post_attachment_wall_post_info_box->paddingLeft = 4;
            $post_attachment_wall_post_info_box->alignment = "CENTER_LEFT";
            $post_attachment_wall_post_info = new UXHBox([$post_attachment_wall_post_source_photo,$post_attachment_wall_post_info_box]);
            $post_attachment_wall_post_box->add($post_attachment_wall_post_info);
            if ($wall_obj['text'] != '')
            {
                $post_text = new UXLabel();
                $post_attachment_wall_post_box->add($post_text);
                if (count(explode("\n", $wall_obj['text'])) > 7)
                {
                    $post_text->maxHeight = 84;
                    $post_text_expand = new UXHyperlink;
                    $post_text_expand->text = 'Показать полностью...';
                    $post_text_expand->on('click', function () use ($post_text,$post_text_expand) {
                    if ($post_text->maxHeight == 84)
                    {
                        $post_text->maxHeight = -1;
                        $post_text_expand->text = 'Свернуть';
                    }
                    else 
                    {
                        $post_text->maxHeight = 84;
                        $post_text_expand->text = 'Показать полностью...';
                    }
                    });
                    $post_attachment_wall_post_box->add($post_text_expand);
                }
                $post_text->classesString = 'post_content_text';
                $post_text->text = $wall_obj['text'];
                $post_text->wrapText = true;
                $post_text->maxWidth = 530;
                $post_text->textColor = 'white';
                
            }
            if (isset($wall_obj['attachments']))
            {
                $post_attachment_wall_post_box->add(self::news_attachments($wall_obj['attachments']));
            }
            
            return $post_attachment_wall_post_main;
        }
    }
    
    public static function news_attachments($attachment_obj,$pref_width = 530)
    {
        $attach_content = new UXVBox;
        foreach ($attachment_obj as $attach)
        {
            if (isset($attach['photo']))
            {
                $photos = true;
                break;
            }
            if (isset($attach['doc']))
            {
                if ($attach['doc']['type'] == 3)
                {
                    $photos = true;
                    break;
                }
            }
        }   
        
        if ($photos == true)
        {
            $post_attachment_photo_carousel = new UXFlowPane;
            $post_attachment_photo_carousel->maxWidth = $pref_width;  
            $attach_content->add($post_attachment_photo_carousel); 
        }
        
        
        foreach ($attachment_obj as $attach)
        {
            if ($attach['type'] == 'photo')
            {
                $post_attachment_photo = new UXImageView();
                $post_attachment_photo->preserveRatio = true;
                AdvancedVK::cache(arr::last($attach['photo']['sizes'])['url'], $post_attachment_photo);
                $post_attachment_photo->fitWidth = (($pref_width-10)/3);  
                $post_attachment_photo->on('click', function () use ($attach) {
                    app()->form('photoViewer')->call_photo_view($attach);
                });
                $post_attachment_photo_carousel->add($post_attachment_photo);
            }
            elseif ($attach['type'] == 'doc')
            {
                if ($attach['doc']['type'] == 3)
                {
                    $post_attachment_gif = new UXImageView();
                    $post_attachment_gif_text = new UXLabel('GIF');
                    $post_attachment_gif->preserveRatio = true;
                    AdvancedVK::cache(arr::last($attach['doc']['preview']['photo']['sizes'])['src'], $post_attachment_gif);
                    $post_attachment_gif->fitWidth = (($pref_width-10)/3);  
                    $post_attachment_gif->on('click', function () use ($attach) {
                        app()->form('gifViewer')->call_gif_view($attach['doc']['preview']['video']['src']);
                    });
                    $post_gif_box = new UXVBox([$post_attachment_gif_text,$post_attachment_gif]);
                    $post_attachment_photo_carousel->add($post_gif_box);
                }
            }
            elseif ($attach['type'] == 'sticker')
            {
                $post_attachment_sticker = new UXImageView;
                $post_attachment_sticker->size = [128,128];
                $post_attachment_sticker->preserveRatio = true;
                AdvancedVK::cache(arr::last($attach['sticker']['images_with_background'])['url'], $post_attachment_sticker);
                $attach_content->add($post_attachment_sticker);
            }
        }
        
        return $attach_content;
    }
    
    function setBorderRadius($image, $radius) 
    {
        $rect = new UXRectangle;
        $rect->width = $image->width;
        $rect->height = $image->height;
            
        $rect->arcWidth = $radius*2;
        $rect->arcHeight = $radius*2;
    
        $image->clip = $rect;
        $circledImage = $image->snapshot();
    
        $image->clip = NULL;
        $rect->free();
    
        $image->image = $circledImage;
        return $image;
    }
    
    public static function regex($pattern, $string) {
        $pattern=substr($pattern,1);
        $pattern=explode('/', $pattern);
        $regex=Regex::of($pattern[0], $pattern[1])->with($string);
        if($regex->find()){
            $params = $regex->groups();
            return $params;
        }
    }
}