<?php
namespace Ziki\Core;
session_start();

use Ziki\Core\filesystem as FileSystem;
class Auth {
    /**
     * This function will get the auth details from specified url
     */
public function getAuth(){
    $result = json_decode(FileSystem::read($url));
  $_SESSION['name'] = $result->name;
  $_SESSION['email'] = $result->email;
  $_SESSION['bio'] = $result->bio;
  $_SESSION['image'] = $result->image;
    return $msg = "Data stored as success succesfully";
}
}