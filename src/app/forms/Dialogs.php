<?php
namespace app\forms;

use std, gui, framework, app;


class Dialogs extends AbstractForm
{
    public $offset = 20,$max_users,$form;

    /**
     * @event listView.scroll-Down 
     */
    function doListViewScrollDown(UXScrollEvent $e = null)
    {    
        if ($this->offset < $this->max_users)
        {
            $a = $this->listView->items->count;
            $this->offset = $this->offset + 20;
            $this->load_dialogs();
            $this->listView->scrollTo($a-1);
        }
    }

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        $this->form = 'Dialog';
    }

    /**
     * @event button.action 
     */
    function doButtonAction(UXEvent $e = null)
    {    
        $this->load_dialogs();
    }

    function load_dialogs()
    {
        $dialogs_resp = SimpleVK::Query('messages.getConversations', ['filter'=>'all', 'extended'=>true,'fields'=>'photo_50','count'=>$this->offset]);
        $this->label->text = $dialogs_resp['response']['count'].' Диалогов';
        $this->cache_users_from_conversations($dialogs_resp);
        $this->max_users = $dialogs_resp['response']['count'];
        $this->render_dialog($dialogs_resp, 530);
    }
    
    
    function render_dialog($dialogs_resp, $pref_width)
    {
        $this->listView->items->clear();
        foreach ($dialogs_resp['response']['items'] as $dialog)
        {
            $source = $this->get_com_user_id($dialog['conversation']['peer']['id'], $dialogs_resp, $dialog);
            if ($source['photo_50'] != null)
            {
                $dialog_image = new UXImageView;
                $dialog_image->size = [40,40];
                AdvancedVK::cache($source['photo_50'], $dialog_image, true);
            }
            else 
            { 
                $colors=['#006064','#01579b','#4a148c','#1b5e20','#bf360c','#263238'];
                $dialog_name_photo = new UXLabel(substr($source['name'],0,1));
                $dialog_name_photo->font->size = 16;
                $dialog_name_photo->font->bold = true;
                $dialog_name_photo->textColor = 'white';
                $dialog_image = new UXVBox([$dialog_name_photo]);
                $dialog_image->style = '-fx-background-color: '.$colors[rand(0,count($colors)-1)].'; -fx-background-radius:255;';
                $dialog_image->minSize = [40,40];
                $dialog_image->alignment = "CENTER";
            }
            $dialog_name = new UXLabel($source['name']);
            $dialog_date = new UXLabel(AdvancedVK::msg_date($dialog['last_message']['date']));
            $dialog_data_name = new UXHBox([$dialog_name,$dialog_date]);
            $msg_source = $this->get_com_user_id($dialog['last_message']['from_id'], $dialogs_resp,$dialog);
            $dialog_last_message = new UXLabel(trim($msg_source['name'].' : '.$dialog['last_message']['text']));
            $dialog_last_message->id = 'msg'.$dialog['conversation']['peer']['id'];
            $dialog_last_message->maxWidth = $pref_width;
            $dialog_last_message->maxHeight = 14;
            $dialog_data = new UXVBox([$dialog_data_name,$dialog_last_message]);
            $dialog_box = new UXHBox([$dialog_image,$dialog_data]);
            $dialog_box->userData = json_encode(['info'=>$source,'id'=>$dialog['conversation']['peer']['id']]);
            $dialog_box->on('click', function () use ($dialog_box) {
                app()->getForm('MainForm')->loadFragmentForm("app\\forms\\" . messageDialog);
                app()->getForm('MainForm')->currentform->load_messages($dialog_box->userData);
            });
            $this->listView->items->add($dialog_box);
            
            $dialog_box->classesString= 'dialog-box';
            $dialog_data->classesString='dialog-data';
            $dialog_name->classesString='dialog-name';
            $dialog_date->classesString='dialog-date';
            $dialog_last_message->classesString='dialog-last-message';
        }
    }
    
    function cache_users_from_conversations($dialogs_resp)
    {
        $a = '';
        foreach ($dialogs_resp['response']['items'] as $dialog_obj)
        {
            if ($dialog_obj['conversation']['peer']['id'] > 2000000000)
            {
                $a = ($dialog_obj['conversation']['peer']['id']-2000000000).','.$a;
            }
        }
        $b = SimpleVK::Query('messages.getChat',['chat_ids'=>$a,'fields'=>'photo_50']);
        foreach ($b['response'] as $chat_obj)
        {
            foreach ($chat_obj['users'] as $user_obj)
            {
                if ($user_obj['type'] == 'profile')
                {
                    $user_obj['name'] = $user_obj['first_name'].' '.$user_obj['last_name'];
                }
                $GLOBALS['users'][$user_obj['id']] = $user_obj;
            }
        }
    }
    
    function get_com_user_id($id, $conv_obj, $dlg)
    {
        if ($id > 2000000000)
        {
            $GLOBALS['conv_settings'][$id] =  $dlg['conversation']['push_settings'];
            $dlg['conversation']['chat_settings']['name'] = $dlg['conversation']['chat_settings']['title'];
            $dlg['conversation']['chat_settings']['photo_50'] = $dlg['conversation']['chat_settings']['photo']['photo_50'];
            return $dlg['conversation']['chat_settings'];
        }
        else
        {
           if ($id < 0)
           {
               $GLOBALS['conv_settings'][$id] =  $dlg['conversation']['push_settings'];
               foreach ($conv_obj['response']['groups'] as $grp_obj)
               {
                   if ($id == $grp_obj['id']*-1)
                   {
                       $GLOBALS['users'][$id*-1] = $grp_obj;
                       return $grp_obj;
                   }
               }    
           } 
           else 
           {
               $GLOBALS['conv_settings'][$id*-1] =  $dlg['conversation']['push_settings'];
               foreach ($conv_obj['response']['profiles'] as $prof_obj)
               {
                   if ($prof_obj['id'] == $id) 
                   {
                       if ($GLOBALS['user']['id'] == $prof_obj['id'])
                       {
                           $prof_obj['name'] = 'Вы';
                       }
                       else
                       {
                           $prof_obj['name'] = $prof_obj['first_name'].' '.$prof_obj['last_name'];
                           $GLOBALS['users'][$id] = $prof_obj;
                       }
                       return $prof_obj;    
                   }    
               }   
           }
        }
    }
}
