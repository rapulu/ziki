<?php
namespace Ziki\Core;
use Ziki\Core\FileSystem;
class Auth {
    /**
     * This function will get the auth details from specified url
     */
    public static function isInstalled()
    {
        $dir = "./src/config/auth.json";
        $check_settings = FileSystem::read($dir);
        if(!$check_settings) {
            $install = true;
        }
        else{
            $install = false;
        }
        return $install;
    }
    public static function setup ($data)
    {
        $check_settings = self::isInstalled();
        if($check_settings == true) {
            $s_file = "./src/config";
            $data['name'] = $data['firstname']." ".$data['lastname'];
            $site_url = $data['domainName'].$data['domain'];
            $data['image'] = "https://img.icons8.com/color/96/000000/user.png";
            $save = json_encode($data);
            $doc = FileSystem::write("{$s_file}/auth.json", $save);
            $destination = $data['email'];
            $subject = "Welcome To Lucid";

            $message = "";

            // Always set content-type when sending HTML email
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            // More headers
            $headers .= 'From: <lucidowner.test@gmail.com>' . "\r\n";
            $variables = array();
            $variables['name'] = $data['name'];
            $variables['address'] = $data['domainName'].$data['domain'];
            $email_temp = "./src/config/email.php";
            $template = file_get_contents($email_temp);
            foreach($variables as $key => $value) {
                $template = str_replace('{{ '.$key.' }}', $value, $template);
            }
            $msg= $template;
            @mail($destination, $subject, $msg, $headers) ;
            /*$url = "https://auth.techteel.com/api/login/email?address={$data['email']}?domain={$site_url}";
            $ch = curl_init();
            //Set the URL that you want to GET by using the CURLOPT_URL option.
            curl_setopt($ch, CURLOPT_URL, $url);
            
            //Set CURLOPT_RETURNTRANSFER so that the content is returned as a variable.
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            //Set CURLOPT_FOLLOWLOCATION to true to follow redirects.
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            //Execute the request.
            $result = curl_exec($ch);
            
            //Close the cURL handle.
            curl_close($ch);*/
            $install = true;
        }
        else{
            $install = false;
        }
        return $install;
    }
    public static function getAuth($data, $role){
        $user['name'] = $data->name;
        $user['email'] = $data->email;
        $user['image'] = $data->image;
        $user['last_login'] = $data->updated_at;
        $user['role'] = $role;
        $user['login_token'] = md5($data->email);
        $_SESSION['login_user'] = $user;
        return true;
    }
    public function hash($data){
        $ch = curl_init();
        //Set the URL that you want to GET by using the CURLOPT_URL option.
        curl_setopt($ch, CURLOPT_URL, "https://auth.techteel.com/api/encrpt?host={$data}");
        
        //Set CURLOPT_RETURNTRANSFER so that the content is returned as a variable.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        //Set CURLOPT_FOLLOWLOCATION to true to follow redirects.
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        //Execute the request.
        $result = curl_exec($ch);
        
        //Close the cURL handle.
        curl_close($ch);
        return $result;
    }
    // Log in user check
    public function is_logged_in() {
        // Check if user session has been set
        if (isset($_SESSION['login_user']) && ($_SESSION['login_user']['login_token'] != '')) {
            return $_SESSION;
        }
    }
    // Log out user
    public function log_out() {
        // Destroy and unset active session
        session_destroy();
        unset($_SESSION);
        return true;
    }
    public function validateAuth($params) {
        $auth_response =  array();
        $data =  explode(",", $params);
        $provider = $data[0];
        $token = $data[1];
        $ch = curl_init();
        //Set the URL that you want to GET by using the CURLOPT_URL option.
        curl_setopt($ch, CURLOPT_URL, "https://auth.techteel.com/api/authcheck/{$provider}/{$token}");
        
        //Set CURLOPT_RETURNTRANSFER so that the content is returned as a variable.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        //Set CURLOPT_FOLLOWLOCATION to true to follow redirects.
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        //Execute the request.
        $result = curl_exec($ch);
        
        //Close the cURL handle.
        curl_close($ch);
        $res = json_decode($result);
        //Save User data to auth.json
        $dir = "./src/config/auth.json";
        $check_settings = FileSystem::read($dir);
        if(!$check_settings) {
            $json_user = FileSystem::write($dir, $result);
            if($json_user){
                $role = "admin";
                $auth =self::getAuth($res, $role);
                $auth_response = $auth;
            }
        }
        else{
            $check_prev = json_decode($check_settings);
            if($check_prev->email == $res->email){
                $role = "admin";
                $auth = self::getAuth($check_prev, $role);
                $auth_response = $auth;
            }
            else{
                $role = "guest";
                $auth =self::getAuth($res, $role);
                $auth_response = $auth;
            }
        }  
        return $auth_response;  
    }
    public function redirect($location)
    {
        header('Location:'.$location);
    }
}
