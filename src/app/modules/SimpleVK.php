<?php
namespace app\modules;

use httpclient;
use std, gui, framework, app;


class SimpleVK extends AbstractModule
{

    public static 
    $ver = '0.1',
    $accessToken = '',
    $vkapiversion = '5.80',
    $tokenFile = './cache.ini',
    $client_id = '2274003',
    $client_secret = 'hHbZxrka2uZ6jB1inYsH',
    $user_agent = 'VKAndroidApp/5.15-2533';
    
    public static function setToken($token)
    {
        Logger::info('setToken');
        self::$accessToken = $token;
        return $token;
    }
    
    /**
     *Возвращает true если токен существует
    */
    public static function checkAuth()
    {
        if (app()->module(SimpleVK)->ini->get('token') == null)
        {
            return false;
        }
        else 
        {
            self::$accessToken = app()->module(SimpleVK)->ini->get('token');
            return true;
        }
    }
    
    /**
     *Удаляет сохранённый токен
    */
    public static function clearAuth()
    {
        app()->module(SimpleVK)->ini->remove('token');
        self::$accessToken = null;
        return true;
    }
    
    /**
     *Делает запрос
     *@param string $method - метод (чт. док. VK), 
     *@param array $params - массив с параметрами 
    */
    public static function Query($method, $params = [])
    {
        $client = new HttpClient;
        $client->responseType = 'JSON';
        $client->userAgent = self::$user_agent;
        $params['v'] = self::$vkapiversion;
        $params['access_token'] = self::$accessToken;
        $a = $client->get('https://api.vk.com/method/'.$method.'?'.http_build_query($params));
        if (self::result_check($a->body()))
        {
            return $a->body();
        }
    }
    
    public static function createAuth($login, $password, $save = true, $extapi = true, $code = false, $sid = false, $ccode = false)
    {
        $client = new HttpClient;
        $client->responseType = 'JSON';
        $client->userAgent = self::$user_agent;
        if ($code == false)
        {
            if (!$sid == false)
            {
                $authtry = $client->get('https://oauth.vk.com/token?grant_type=password&client_id='.self::$client_id.'&client_secret='.self::$client_secret.'&username='.$login.'&password='.$password.'&v='.self::$vkapiversion.'&2fa_supported=1'.'&captcha_sid='.$sid.'&captcha_key='.$ccode);
                $auth = $authtry->body();   
            }
            else 
            {
                $authtry = $client->get('https://oauth.vk.com/token?grant_type=password&client_id='.self::$client_id.'&client_secret='.self::$client_secret.'&username='.$login.'&password='.$password.'&v='.self::$vkapiversion.'&2fa_supported=1');
                $auth = $authtry->body();
                var_dump($auth);
            }
        }
        else 
        {
                $authtry = $client->get('https://oauth.vk.com/token?grant_type=password&client_id='.self::$client_id.'&client_secret='.self::$client_secret.'&username='.$login.'&password='.$password.'&v='.self::$vkapiversion.'&2fa_supported=1'.'&code='.$code);
                $auth = $authtry->body();
        }
        if (isset($auth['error']))
        {
            if ($auth['error'] == 'need_validation')
            {
                app()->form('MainForm')->loadFragmentForm("app\\forms\\".on2faForm);
                app()->form('on2faForm')->on2faStart($login, $password, $auth, $save);
            }
            if ($auth['error'] == 'need_captcha')
            {
                var_dump($auth);
                app()->form('vkCaptcha')->setUrl($auth['captcha_img']);
                $ssid = $auth['captcha_sid'];
                app()->showFormAndWait(vkCaptcha);
                SimpleVK::createAuth($login,$password,$save,$extapi,$code,$ssid,$GLOBALS['ccode']);
            }
        }
        else 
        {
            if ($auth['access_token'] != null)
            {
                Logger::info('valid_token');
                self::$accessToken = $auth['access_token'];
                if ($save == true)
                {
                    app()->module(SimpleVK)->ini->set('token', self::$accessToken);
                }
                return true;
            }
            else 
            {
                return false;
            }
        }
    }

    
    public static function result_check($response)
    {
        if (isset($response['error']))
        {
            if ($response['error']['error_code'] == 5)
            {
                app()->module(SimpleVK)->ini->set('token', null);
                self::$accessToken = null;
                app()->hideForm(MainForm);
                app()->form(MainForm)->free();
                app()->showFormAndWait(Authentication);
                app()->showForm(MainForm);
            }
            elseif ($response['error']['error_code'] == 6)
            {
                app()->module(SimpleVK)->ini->set('token', null);
                self::$accessToken = null;
                app()->form(fatal)->error = $response;
                app()->showFormAndWait(fatal);
            }
            else 
            {
                Logger::warn(print_r($response));
            }
        }
        else 
        {
            return true;
        }
    }
    

}

if(!function_exists('http_build_query')){
    function http_build_query($a,$b='',$c=0)
     {
            if (!is_array($a)) return false;
            foreach ((array)$a as $k=>$v)
            {
                if ($c)
                {
                    if( is_numeric($k) )
                        $k=$b."[]";
                    else
                        $k=$b."[$k]";
                }
                else
                {   if (is_int($k))
                        $k=$b.$k;
                }
                if (is_array($v)||is_object($v))
                {
                    $r[]=http_build_query($v,$k,1);
                        continue;
                }
                $r[]=urlencode($k)."=".urlencode($v);
            }
            return implode("&",$r);
            }
}