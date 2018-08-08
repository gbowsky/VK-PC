<?php
namespace app\forms;

use std, gui, framework, app;


class photoViewer extends AbstractForm
{

    public $scale = 1;

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
    function call_photo_view($photo_obj)
    {
        $this->show();
        $this->scale = 1;
        AdvancedVK::cache(arr::last($photo_obj['photo']['sizes'])['url'],$this->image);
    }
    
}
