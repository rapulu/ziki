<?php
use Ziki\Http\Router;

session_start();

Router::get('/about/{id}', function($request,$id) {
     return $this->template->render('about-us.html');
});
Router::get('/', function($request) {
    $user = new Ziki\Core\Auth();
    if ($user::isInstalled() == true) {
        return $user->redirect('/install');
    }
    else{
        $directory = "./storage/contents/";
        $ziki = new Ziki\Core\Document($directory);
        $feed = $ziki->fetchRss();
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $host = $user->hash($url);
        // Render our view
        //print_r($feed);
        return $this->template->render('index.html',['posts' => $feed, 'host' => $host] );
    }
});
Router::get('blog-details/{id}', function($request, $id) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    $directory = "./storage/contents/";
    $ziki = new Ziki\Core\Document($directory);
   $result = $ziki->getEach($id);
   return $this->template->render('blog-details.html', ['result' => $result] );
});
Router::get('/timeline', function($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    $directory = "./storage/contents/";
    $ziki = new Ziki\Core\Document($directory);
    $post = $ziki->fetchAllRss();
    return $this->template->render('timeline.html', ['posts' => $post] );
});

Router::get('/tags/{id}', function($request,$id) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    $directory = "./storage/contents/";
    $ziki = new Ziki\Core\Document($directory);
    $result = $ziki->update($id);
    return $this->template->render('timeline.html', ['posts' => $result] );
});
Router::post('/publish', function($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    $directory = "./storage/contents/";
    $data = $request->getBody();
    $title = $data['title'];
    $body = $data['postVal'];
    $tags = $data['tags'];
    // filter out non-image data
    $initial_images = array_filter($data , function($key) {
        return preg_match('/^img-\w*$/', $key);
      }, ARRAY_FILTER_USE_KEY);
      // PHP automatically converts the '.' of the extension to an underscore
      // undo this
      $images = [];
      foreach ($initial_images as $key => $value) {
        $newKey = preg_replace('/_/', '.', $key);
        $images[$newKey] = $value;
      }
      //return json_encode([$images]);
      $ziki = new Ziki\Core\Document($directory);
      $result = $ziki->create($title, $body, $tags, $images);
    return $this->template->render('timeline.html', ['ziki' => $result]);
});

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
Router::post('/timeline', function($request) {
  $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    $data = $request->getBody();
    $url = $_POST['domain'];
    $ziki = new Ziki\Core\Subscribe();
    $result = $ziki->extract($url);
    $directory = "./storage/contents/";
    $ziki = new Ziki\Core\Document($directory);
    $feed = $ziki->fetchAllRss();
    return $this->template->render('index.html', ['posts' => $feed]);
});
}
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  Router::get('/timeline', function($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
      $directory = "./storage/contents/";
      $ziki = new Ziki\Core\Document($directory);
      $feed = $ziki->fetchAllRss();
     //  Render our view
      print_r($feed);
      return $this->template->render('timeline.html',['posts' => $feed] );
  });
}
Router::get('/contact-us', function($request) {
    include ZIKI_BASE_PATH."/src/core/SendMail.php";
    $checkifOwnersMailIsprovided = new  SendContactMail();
    $checkifOwnersMailIsprovided->getOwnerEmail();
    $message = [];
    if(empty($checkifOwnersMailIsprovided->getOwnerEmail()))
    {
        $message['ownerEmailNotProvided'] = true;
    }
    if(isset($_SESSION['messages']))
    {
        $message = $_SESSION['messages'];
        unset($_SESSION['messages']);
    }
    return $this->template->render('contact-us.html',['message'=>$message]);
});
Router::post('/send',function($request){
    include ZIKI_BASE_PATH."/src/core/SendMail.php";
    $request=$request->getBody();
    $SendMail = new SendContactMail();
    $SendMail->mailBody= $this->template->render('mail-template.html',['guestName'=>$request['guestName'],'guestEmail'=>$request['guestEmail'],'guestMsg'=>$request['guestMsg']]);
    $SendMail->sendMail($request);
    $SendMail->clientMessage();
    return $SendMail->redirect('/contact-us');
});
Router::get('delete/{id}', function($request, $id) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return new RedirectResponse("/");
    }
    $directory = "./storage/contents/";
    $ziki = new Ziki\Core\Document($directory);
    $result = $ziki->delete($id);
    return $this->template->render('timeline.html', ['delete' => $result] );
});
Router::get('/published-posts', function($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    return $this->template->render('published-posts.html');
});
Router::get('/settings', function($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    return $this->template->render('settings.html');
});
Router::get('/profile', function($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    return $this->template->render('profile.html');
});
Router::post('/subscriptions', function($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    return $this->template->render('subscriptions.html');
});
Router::get('/subscribers', function($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    return $this->template->render('subscribers.html');
});
Router::get('/editor', function($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
  return $this->template->render('editor.html');
});
Router::get('/404', function($request) {
    return $this->template->render('404.html');
});

/* Devmohy working on draft */
/* Save draft*/
Router::post('/saveDraft', function($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    $directory = "./storage/drafts/";
    $data = $request->getBody();
    $title = $data['title'];
    $body = $data['postVal'];
    $tags = $data['tags'];

    $initial_images = array_filter($data , function($key) {
        return preg_match('/^img-\w*$/', $key);
      }, ARRAY_FILTER_USE_KEY);
      // PHP automatically converts the '.' of the extension to an underscore
      // undo this
      $images = [];
      foreach ($initial_images as $key => $value) {
        $newKey = preg_replace('/_/', '.', $key);
        $images[$newKey] = $value;
      }

    $ziki = new Ziki\Core\Document($directory);
    $result = $ziki->createDraft($title, $body,$tags, $images, true);
    return $this->template->render('drafts.html', ['ziki' => $result]);
});
/* Save draft */

/* Get all saved draft */
Router::get('/drafts', function($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    $directory = "./storage/drafts/";
    $ziki = new Ziki\Core\Document($directory);
    $draft = $ziki->getDrafts();
    return $this->template->render('drafts.html', ['drafts' => $draft]);
});

Router::get('/videos', function($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }

    return $this->template->render('videos.html');

});

/* Get all saved draft */

/* Delete draft */
Router::get('deleteDraft/{id}', function($request, $id) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return new RedirectResponse("/");
    }
    $directory = "./storage/drafts/";
    $ziki = new Ziki\Core\Document($directory);
    $result = $ziki->delete($id);
    return $this->template->render('drafts.html', ['delete' => $result] );
});
/* Delete draft */

/* Edit draft */
/* Edit draft */

/* Devmohy working on draft */


Router::get('/about', function($request) {
    return $this->template->render('about-us.html');
});
Router::get('/microblog', function($request) {
    return $this->template->render('microblog.html');
});
Router::get('/download', function($request) {
    return $this->template->render('download.html');
});
Router::get('/auth/{provider}/{token}', function($request, $token){
    $param = $request->getBody();
    $user = new Ziki\Core\Auth();
    $check = $user->validateAuth($token);
    if($_SESSION['login_user']['role'] == 'guest'){
        return $user->redirect('/');
    }
    else{
        return $user->redirect('/timeline');
    }
});
Router::get('/setup/{provider}/{token}', function($request, $token){
    $user = new Ziki\Core\Auth();
    $check = $user->validateAuth($token);
    if($_SESSION['login_user']['role'] == 'guest'){
        return $user->redirect('/');
    }
    else{
        return $user->redirect('/profile');
    }
});
Router::get('/logout', function($request) {
    $user = new Ziki\Core\Auth();
    $user->log_out();
    return $user->redirect('/');
});
Router::get('/api/images', function() {
    return (new Ziki\Core\UploadImage)->getAllImages();
});
Router::post('/api/upload-image', function() {
    return (new Ziki\Core\UploadImage)->upload();
});

Router::get('/install', function($request) {
    $user = new Ziki\Core\Auth();
    if ($user::isInstalled() == false) {
        return $user->redirect('/');
    }
    else{
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $host = $user->hash($url);
        return $this->installer->render('install.html', ['host' => $host]);
    }
});

Router::post('/setup', function($request) {
    $data = $request->getBody();
    $user = new Ziki\Core\Auth();
    die(json_encode($data));
});
Router::get('/{id}', function($request, $id) {
 try {
    // echo 'this page is for the /{id} route';
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    $directory = './storage/contents/';
    $path = ZIKI_BASE_PATH.'/src/core/document.php';
    require_once $path;
    $doc = new Document($directory);
    $result = $doc->getSinglePost($id);
    // var_dump($result);
 } catch (\Throwable $th) {
     echo $th->getMessage();
 }
   return $this->template->render('blog-details.html', ['result' => $result] );
});