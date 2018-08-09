<?php
namespace app\modules;

use std, gui, framework, app;


class LongPoll extends AbstractModule
{

    public static $access_token, $lp_thread;

    public static function init()
    {
        self::$access_token=SimpleVK::$accessToken;
        $data=SimpleVK::Query('messages.getLongPollServer', ['use_ssl'=>1]);
        $server = $data['response']['server'];
        $key = $data['response']['key'];
        $ts = $data['response']['ts'];

        self::$lp_thread=new Thread(function() use($data, $server, $key, $ts)
        {
            while(true){
            $ch = new jURL("https://{$server}?act=a_check&key={$key}&ts={$ts}&wait=25&mode=234&version=2");
            $resp = $ch->exec();
            $resp=json_decode($resp,1);
            if(array_key_exists('failed', $resp)){
                switch($resp['failed']){
                case 1:
                    $ts = $resp['ts'];
                continue;
                case 2:
                    $data = SimpleVK::Query('messages.getLongPollServer', ['use_ssl'=>1]);
                    $key = $data['response']['key'];
                continue;
                case 3:
                    $data = SimpleVK::Query('messages.getLongPollServer', ['use_ssl'=>1]);
                    $key = $data['response']['key'];
                    $ts = $data['response']['ts'];
                continue;
                }
            }
            $ts = $resp['ts'];
            if(count($resp['updates'])>0){
                        break;
                }    
            }
            UXApplication::runLater(function () use ($data,$resp) {
                foreach ($resp['updates'] as $id => $upd) {
                    if($upd[0]==4){
                        #dialog form
                        if (app()->form('MainForm')->currentform->form == 'Dialog')
                        {
                            $id = 'msg'.$upd[3];
                            if (app()->form('MainForm')->currentform->{$id} != null)
                            {
                                if (isset($upd[6]['from']))
                                {
                                    app()->form('MainForm')->currentform->{$id}->text = $GLOBALS['users'][$upd[6]['from']]['first_name'].' '.$GLOBALS['users'][$upd[6]['from']]['last_name'].' : '.$upd[5];
                                }
                                else 
                                {
                                    $c = (boolval($upd[2] & 2) ? 1 : 0);
                                    if ($c == 1)
                                    {
                                        app()->form('MainForm')->currentform->{$id}->text = 'Вы : '.$upd[5];   
                                    }
                                    else 
                                    {
                                        app()->form('MainForm')->currentform->{$id}->text = $upd[5];
                                    }
                                }
                            }
                        }
                        #messages form
                        elseif (app()->form('MainForm')->currentform->form == 'messageDialog')
                        {    
                            if (app()->form('MainForm')->currentform->peer_id == $upd[3])
                            {
                                app()->form('MainForm')->currentform->lpoll_load_one($upd[1]);
                            }
                        }
                    }   
                }
                self::init();
            });
        });
        self::$lp_thread->start();
    }
    
    public static function stop()
    {
        self::$lp_thread->stop();
    }
}