<?php
namespace app\forms;

use std, gui, framework, app;


class Authentication extends AbstractForm
{

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

    /**
     * @event edit.step 
     */
    function doEditStep(UXEvent $e = null)
    {    
        $this->center($this->edit);
        $this->center($this->passwordField);
        $this->center($this->button);
    }

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
     * @event passwordField.keyDown-Enter 
     */
    function doPasswordFieldKeyDownEnter(UXKeyEvent $e = null)
    {    
        $this->doButtonAction();
    }
    
    function center($obj)
    {
        $obj->x = (app()->form(MainForm)->fragment->width/2 - $obj->width/2);
    }



}
