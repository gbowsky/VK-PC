<?php
namespace app\forms;

use std, gui, framework, app;


class Friends extends AbstractForm
{

    public $offset = 0,$user_id,$max_users;

    /**
     * @event listView.scroll-Down 
     */
    function doListViewScrollDown(UXScrollEvent $e = null)
    {    
        if ($this->listView->items->count < $this->max_users)
        {
            $this->offset =+ 20;
            $this->friends_render($this->user_id);
        }
    }
    
    function friends_render($user_id = '')
    {
        $this->user = $user_id;
        $friends = SimpleVK::Query('friends.get', ['user_id'=>$user_id,'order'=>'hints','fields'=>'photo_50,first_name,last_name','offset'=>$this->offset,'count'=>20])['response'];
        $this->max_users = $friends['count'];
        $this->render_users_list($friends['items'], $this->listView->items);
    }
    
    function render_users_list($users_objects, $to)
    {
        foreach ($users_objects as $obj)
        {
            $user_name = new UXLabel($obj['first_name'].' '.$obj['last_name']);
            $user_img = new UXImageView;
            $user_img->size = [35,35];
            AdvancedVK::cache($obj['photo_50'],$user_img,true);
            $main = new UXHBox([$user_img,$user_name]);
            $main->classesString = 'friends_list';
            $user_name->classesString = 'friends_list_text';
            $main->on('click', function () use ($obj) {
                app()->getForm('MainForm')->loadFragmentForm("app\\forms\\" . Profile);
                app()->getForm('MainForm')->currentform->user_page($obj['id']);
            });
            $to->add($main);
        }
    }

}
