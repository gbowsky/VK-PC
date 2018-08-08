<?php
namespace app\forms;

use std, gui, framework, app;


class News extends AbstractForm
{

    public $start_from,$loaded;

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        $this->load_news();
    }

    /**
     * @event listView.scroll-Down 
     */
    function doListViewScrollDown(UXScrollEvent $e = null)
    {    
        if ($this->loaded == 1)
        {
            $scrollTo = $this->listView->items->count();
            $func = (function () use ($scrollTo) {
                $this->listView->scrollTo($scrollTo-1);
            });
            $this->load_news($func);
        }
    }
    
    function load_news($func = null)
    {
        $this->loaded = 0;
        $th = new Thread(function () use ($func) {
           $news = SimpleVK::Query('newsfeed.get', ['filters'=>'post','count'=>20,'start_from'=>$this->start_from]);
           $this->start_from = $news['response']['next_from'];
           UXApplication::runLater(function () use ($news,$func) {
                AdvancedVK::generate_news($news,$this->listView->items);
                if ($func != null)
                {
                    call_user_func($func);
                }
                $this->loaded = 1;
           });
        });
        $th->run();
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

}
