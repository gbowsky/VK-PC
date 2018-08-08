<?php
namespace app\forms;

use std, gui, framework, app;


class gifViewer extends AbstractForm
{

    /**
     * @event close 
     */
    function doClose(UXWindowEvent $e = null)
    {    
        $this->browser->engine->cancel();
        $this->browser->free();
        $this->free();
    }
    function call_gif_view($photo_url)
    {
        $this->show();
        $this->scale = 1;
        $this->browser->engine->load($photo_url);
    }
}
