<?php
namespace app\forms;

use php\gui\animatefx\AnimationFX;
use std, gui, framework, app;


class MainForm extends AbstractForm
{
    public $currentform = null;

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        $this->checks();
    }

    /**
     * @event profile_mini.click-Left 
     */
    function doProfile_miniClickLeft(UXMouseEvent $e = null)
    {    
        $this->loadFragmentForm("app\\forms\\" . Profile);
        $this->currentform->user_page('');
    }
    
    function loadFragmentForm($formname)
    {
        if ($this->currentform != null)
        {
            $this->currentform->free();
            $this->currentform = null;
        }
        $this->currentform = new $formname;
        $this->currentform->showInFragment($this->fragment);

    }

    /**
     * @event news.click-Left 
     */
    function doNewsClickLeft(UXMouseEvent $e = null)
    {    
        $this->loadFragmentForm("app\\forms\\" . News);

    }

    /**
     * @event settings.click-Left 
     */
    function doSettingsClickLeft(UXMouseEvent $e = null)
    {    
        $this->loadFragmentForm("app\\forms\\" . Settings);

    }

    /**
     * @event friends.click-Left 
     */
    function doFriendsClickLeft(UXMouseEvent $e = null)
    {    
        $this->loadFragmentForm("app\\forms\\" . Friends);
        $this->currentform->friends_render();
    }

    /**
     * @event close 
     */
    function doClose(UXWindowEvent $e = null)
    {    
        fs::clean('./cache/');
        LongPoll::stop();
    }

    /**
     * @event messages.click-Left 
     */
    function doMessagesClickLeft(UXMouseEvent $e = null)
    {
        $this->loadFragmentForm("app\\forms\\" . Dialogs);
        $this->currentform->load_dialogs();
    }


    
    function get_me()
    {
        $this->loadFragmentForm("app\\forms\\" . News);
        $GLOBALS['user'] = SimpleVK::Query('users.get', ['fields'=>'photo_50'])['response'][0];
        AdvancedVK::cache($GLOBALS['user']['photo_50'],$this->profile_mini,true);
    }

    
    function checks() 
    {
        if (SimpleVK::checkAuth() == false)
        {
            $this->news->enabled = false;
            $this->friends->enabled = false;
            $this->profile_mini->enabled = false;
            $this->settings->enabled = false;
            $this->loadFragmentForm("app\\forms\\" . Authentication);
        }
        else 
        {
            $this->news->enabled = true;
            $this->friends->enabled = true;
            $this->profile_mini->enabled = true;
            $this->settings->enabled = true;
            fs::clean('./cache/');
            $this->get_me();
            $this->loadFragmentForm("app\\forms\\" . News);
            
            LongPoll::init();
        }
        
    }

}
