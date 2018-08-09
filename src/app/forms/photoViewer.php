<?php
namespace app\forms;

use std, gui, framework, app;


class photoViewer extends AbstractForm
{

    public $scale = 1,$photos,$selpic,$maxpic;

    /**
     * @event close 
     */
    function doClose(UXWindowEvent $e = null)
    {    
        $this->scale = 1;
        app()->form('photoViewer')->free();
    }

    /**
     * @event image.scroll-Up 
     */
    function doImageScrollUp(UXScrollEvent $e = null)
    {    
        $this->scale += 0.2;
        $this->image->scale = $this->scale;
    }

    /**
     * @event image.scroll-Down 
     */
    function doImageScrollDown(UXScrollEvent $e = null)
    {    
        if (($this->scale - 0.2) > 0.2)
        {
            $this->scale -= 0.2;
            $this->image->scale = $this->scale;
        }
        else 
        {
            
            $this->image->scale = $this->scale;
        }
    }

    /**
     * @event hbox.step 
     */
    function doHboxStep(UXEvent $e = null)
    {    
        $this->center($this->hbox);
    }

    /**
     * @event prev.action 
     */
    function doPrevAction(UXEvent $e = null)
    {    
        $this->selpic -=1;
        if ($this->selpic < 0)
        {
            $this->selpic = $this->maxpic-1;
        }
        $this->label->text = 'Фотография '.($this->selpic+1).' из '.($this->maxpic);
        AdvancedVK::cache(arr::last($this->photos[$this->selpic]['sizes'])['url'], $this->image);
   }

    /**
     * @event next.action 
     */
    function doNextAction(UXEvent $e = null)
    {    
        $this->selpic +=1;
        if ($this->selpic == $this->maxpic)
        {
            $this->selpic = 0;
        }
        AdvancedVK::cache(arr::last($this->photos[$this->selpic]['sizes'])['url'], $this->image);
        $this->label->text = 'Фотография '.($this->selpic+1).' из '.($this->maxpic);
    }

    /**
     * @event keyDown-Left 
     */
    function doKeyDownLeft(UXKeyEvent $e = null)
    {    
        $this->doPrevAction();
        
    }

    /**
     * @event keyDown-Right 
     */
    function doKeyDownRight(UXKeyEvent $e = null)
    {    
        $this->doNextAction();
    }
    
    function loadAllAttachmentsPhotos($attach, $photo_obj)
    {
        $this->show();
        $this->scale = 1;
        AdvancedVK::cache(arr::last($photo_obj['photo']['sizes'])['url'],$this->image);
        $a = 0;
        $this->selpic = 0;
        foreach ($attach as $att_obj)
        {
            if ($att_obj['type'] == 'photo')
            {
                $att_obj['photo']['count'] = $a;
                $this->photos[$a] = $att_obj['photo'];
                $a ++;
                $this->maxpic = $a;
            }
        }
        foreach ($this->photos as $pic)
        {
            $post_photo_pic = new UXImageView;
            $post_photo_pic->fitHeight = 40;
            $post_photo_pic->preserveRatio = true;
            $post_photo_pic->id = $pic['count'];
            $post_photo_pic->cursor = 'HAND';
            $this->label->text = 'Фотография '.($this->selpic+1).' из '.($this->maxpic);
            AdvancedVK::cache($pic['sizes'][0]['url'],$post_photo_pic);
            $post_photo_pic->on('click', function () use ($post_photo_pic,$this,$pic) {
                AdvancedVK::cache(arr::last($this->photos[$post_photo_pic->id]['sizes'])['url'], $this->image);                       
                $this->selpic = $pic['count'];
                $this->label->text = 'Фотография '.($this->selpic+1).' из '.($this->maxpic);
            });            
            $this->hbox->add($post_photo_pic);
        }
    }
    
    function center($obj)
    {
        $obj->x = ($this->image->width/2 - $obj->width/2);
    }
    
}
