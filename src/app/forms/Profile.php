<?php
namespace app\forms;

use facade\Json;
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
        $fields = 'verified, sex, bdate, city, photo_50, photo_100, online, status, counters, last_seen, is_friend, friend_status,can_send_friend_request';
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
        if ($user_data['can_send_friend_request'] == 1)
        {
            if ($user_data['id'] != $GLOBALS['user']['id'])
            {
                $add_friend_button = new UXFlatButton;
                $add_friend_button->classesString = 'vk-button';
                if ($user_data['is_friend'] == 1)
                {
                    $add_friend_button->text = 'Удалить из друзей';
                    $add_friend_button->on('click', function () use ($user_data,$add_friend_button) {
                        SimpleVK::Query('friends.delete', ['user_id'=>$user_data['id']]);
                        app()->getForm('MainForm')->loadFragmentForm("app\\forms\\" . Profile);
                        app()->getForm('MainForm')->currentform->user_page($user_data['id']);
                    });
                }
                else 
                {
                    $add_friend_button->text = 'Добавить в друзья';
                    $add_friend_button->on('click', function () use ($user_data,$add_friend_button) {
                        SimpleVK::Query('friends.add', ['user_id'=>$user_data['id']]);
                        $add_friend_button->text = 'Заявка отправлена';
                    });
                }
                
                $this->hbox->add($add_friend_button);
            }
        }
        $user_wall = SimpleVK::Query('wall.get', ['owner_id'=>$user_id,'extended'=>1]);
        AdvancedVK::generate_news($user_wall,$this->user_posts->items);
    }

}
