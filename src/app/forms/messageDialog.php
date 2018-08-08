<?php
namespace app\forms;

use std, gui, framework, app;


class messageDialog extends AbstractForm
{

    /**
     * @event button.action 
     */
    function doButtonAction(UXEvent $e = null)
    {    
        $text = $this->textArea->text;
        $this->textArea->clear();
        SimpleVK::Query('messages.send', ['message'=>$text, 'peer_id'=>$this->peer_id]);
    }

    /**
     * @event textArea.keyDown-Shift+Enter 
     */
    function doTextAreaKeyDownShiftEnter(UXKeyEvent $e = null)
    {    
        $this->doButtonAction();
    }

    public $offset,$form = 'messageDialog',$peer_id;

    function load_messages($id,$count = 10)
    {
        $info = json_decode($id,1);
        $this->peer_id = $info['id'];
        $messages = SimpleVK::Query('messages.getHistory', ['peer_id'=>$info['id'],'count'=>10]);
        $messages['response']['items'] = arr::reverse($messages['response']['items']);
        foreach ($messages['response']['items'] as $msg_object)
        {
           $this->listView->items->add($this->render_message($msg_object));
        }
        if ($this->toggleButton->selected == true)
        {
            $this->listView->scrollTo($this->listView->items->count);
        }
    }
    
    function lpoll_load_one()
    {
        $messages = SimpleVK::Query('messages.getHistory', ['peer_id'=>$this->peer_id,'count'=>1]);
        foreach ($messages['response']['items'] as $msg)
        {
            $this->listView->items->add($this->render_message($msg));
        }
        if ($this->toggleButton->selected == true)
        {
            $this->listView->scrollTo($this->listView->items->count);
        }
    }
    
    function render_message($msg_object)
    {
        $source = $GLOBALS['users'][$msg_object['from_id']];
        if ($source['name'] == null)
        {
            $new_user = SimpleVK::Query('users.get',['user_id'=>$msg_object['from_id'],'fields'=>'photo_50'])['response']['items'][0];
            $GLOBALS['users'][$new_user['id']] = $new_user;
        }
        $message_main = new UXHBox;
        $message_photo = new UXImageView;
        $message_photo->size = [40,40];
        AdvancedVK::cache($source['photo_50'], $message_photo,true);
        $message_main->add($message_photo);
        $message_box = new UXVBox;
        $peer_name = new UXLabel($source['name']);
        $peer_name->on('click', function () use ($source) {
            if ($source['id'] > 0)
            {
                app()->getForm('MainForm')->loadFragmentForm("app\\forms\\" . Profile);
                app()->getForm('MainForm')->currentform->user_page($source['id']);
            }
        });
        $message_box->add($peer_name);
        $message_main->add($message_box);
        if (isset($msg_object['text']))
        {
            $message_text = new UXLabel($msg_object['text']);
            $message_box->add($message_text);
        }
        if (isset($msg_object['attachments']))
        {
            $message_box->add(AdvancedVK::news_attachments($msg_object['attachments'], 530));
        }
        if (isset($msg_object['fwd_messages'][0]))
        {
            $fwd_sep = new UXSeparator;
            $fwd_sep->orientation = 'VERTICAL';
            $fwd_box = new UXVBox;
            foreach ($msg_object['fwd_messages'] as $fwd_message)
            {
                $fwd_box->add($this->render_message($fwd_message));
            }
            $fwd_sep_box = new UXHBox([$fwd_sep,$fwd_box]);
            $message_box->add($fwd_sep_box);
        }
        
        $message_main->classesString = 'message_main';
        $message_box->classesString = 'message_box';
        $message_text->classesString = 'message_text';
        $peer_name->classesString = 'message_peer_text';
        return $message_main;
    }

}
