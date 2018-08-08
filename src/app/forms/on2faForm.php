<?php
namespace app\forms;

use std, gui, framework, app;


class on2faForm extends AbstractForm
{

    /**
     * @event button.action 
     */
    function doButtonAction(UXEvent $e = null)
    {    
        if (SimpleVK::createAuth($GLOBALS['2fa_login'], $GLOBALS['2fa_pass'],$GLOBALS['2fa_save'],$GLOBALS['2fa_ext'],$this->passwordField->text) == true)
        {
            app()->form('MainForm')->loadFragmentForm("app\\forms\\".News);
            app()->form('MainForm')->checks();
            $this->free();
        }    
        else 
        {
            $this->toast('Error');
        }
    }
    
    public function on2faStart($login, $password, $arr, $save, $extapi)
    {
        $GLOBALS['2fa_login'] = $login;
        $GLOBALS['2fa_pass'] = $password;
        $GLOBALS['2fa_save'] = $save;
        $GLOBALS['2fa_ext'] = $extapi;
        $GLOBALS['2fa_key'] = $arr['validation_sid'];
        if ($arr['validation_type'] == '2fa_app')
        {
            $this->labelAlt->text = 'Код отправлен в личные сообщения';
        }
        else 
        {
            $this->labelAlt->text = 'Код отправлен по СМС : '.$arr['phone_mask'];
        }
    }

}
