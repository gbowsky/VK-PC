<?php
namespace app\forms;

use std, gui, framework, app;


class Profile extends AbstractForm
{

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        $GLOBALS['content'] = 'Profile';    
    }

    /**
     * @event friends_counters.action 
     */
    function doFriends_countersAction(UXEvent $e = null)
    {    
        app()->form($GLOBALS['content'])->free();
        app()->getForm('Friends')->showInFragment(app()->getForm('MainForm')->fragment);
        app()->getForm('Friends')->friends_render($this->friends_counters->userData);
    }

    
    function user_page($user_id = '')
    {
        $fields = 'verified, sex, bdate, city, photo_50, photo_100, online, status, counters, last_seen';
        if ($user_id == '')
        {
            $user_data = SimpleVK::Query('users.get', ['fields'=>$fields])['response'][0];
        }
        else 
        {
            $user_data = SimpleVK::Query('users.get', ['fields'=>$fields,'user_id'=>$user_id])['response'][0];
        }
        AdvancedVK::cache($user_data['photo_100'], $this->user_image, true);
        $this->user_names->text = $user_data['first_name'].' '.$user_data['last_name'];
        if ($user_data['status'] != '')
        {
            $this->user_status->text = $user_data['status'];   
        }
        else
        {
            $this->user_status->text = 'Статус отсутствует';
        }
        $this->friends_counters->text = $user_data['counters']['friends'].' друзей ';
        if ($user_data['counters']['mutual_friends'] != '')
        {
            $this->friends_counters->text = $this->friends_counters->text.$user_data['counters']['mutual_friends'].' общих';   
        }
        $this->friends_counters->userData = $user_data['id'];
        if ($user_data['online'] == 1)
        {
            $this->online_status->text = 'В сети';
        }
        else 
        {
            $this->online_status->text = 'Был(а) в сети '.AdvancedVK::showDate($user_data['last_seen']['date']);
        }
        if ($user_data['counter']['followers'] != '')
        {
            $this->followers->text = $user_data['counter']['followers'].' подписчиков';
        }
        $user_wall = SimpleVK::Query('wall.get', ['owner_id'=>$user_id,'extended'=>1]);
        AdvancedVK::generate_news($user_wall,$this->user_posts->items);
    }

}
