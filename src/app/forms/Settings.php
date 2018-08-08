<?php
namespace app\forms;

use std, gui, framework, app;


class Settings extends AbstractForm
{

    /**
     * @event button.action 
     */
    function doButtonAction(UXEvent $e = null)
    {    
        fs::clean('./cache/');
    }

    /**
     * @event buttonAlt.action 
     */
    function doButtonAltAction(UXEvent $e = null)
    {    
        SimpleVK::clearAuth();
        app()->shutdown();
    }

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        $GLOBALS['content'] = 'Settings';
    }

    /**
     * @event button3.action 
     */
    function doButton3Action(UXEvent $e = null)
    {    
        for ($x = 0; $x <= 100; $x++) {
            SimpleVK::Query('users.get');
        } 
    }


}
