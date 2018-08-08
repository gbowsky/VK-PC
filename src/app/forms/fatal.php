<?php
namespace app\forms;

use std, gui, framework, app;


class fatal extends AbstractForm
{
    public $error;

    /**
     * @event buttonAlt.action 
     */
    function doButtonAltAction(UXEvent $e = null)
    {    
        UXClipboard::setText(json_encode($this->error));
        browse('http://vk.com/gbowsky');
        app()->shutdown();
    }

}
