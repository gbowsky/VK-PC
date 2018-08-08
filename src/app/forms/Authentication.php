<?php
namespace app\forms;

use std, gui, framework, app;


class Authentication extends AbstractForm
{

    /**
     * @event button.action 
     */
    function doButtonAction(UXEvent $e = null)
    {
        if (SimpleVK::createAuth($this->edit->text, $this->passwordField->text) == true)
        {
            app()->form('MainForm')->loadFragmentForm("app\\forms\\".News);
            app()->form('MainForm')->checks();
            $this->free();
        }    
        else 
        {
            app()->form('MainForm')->toast('АШИПКА');
        }
    }

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        if (SimpleVK::checkAuth() == true)
        {
            $this->loadForm(MainForm);
        }
    }


}
