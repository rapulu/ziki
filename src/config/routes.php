<?php
use Ziki\Http\Router;

session_start();
Router::get('/', function ($request) {
    $user = new Ziki\Core\Auth();
    if ($user::isInstalled() == true) {
        return $user->redirect('/install');
    } else {
        $directory = "./storage/contents/";
        $ziki = new Ziki\Core\Document($directory);
        $feed = $ziki->fetchRss();
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $host = $user->hash($url);
        // Render our view
        //print_r($feed);
        $count = new Ziki\Core\Subscribe();
        $setting = new Ziki\Core\Setting();
        $settings = $setting->getSetting();
        $fcount = $count->fcount();
        $count = $count->count();
        return $this->template->render('index.html', ['posts' => $feed, 'host' => $host, 'count' => $count, 'fcount' => $fcount]);
    }
});
Router::get('blog-details/{id}', function ($request, $id) {

    $directory = "./storage/contents/";
    $ziki = new Ziki\Core\Document($directory);
    $setting = new Ziki\Core\Setting();
    $settings = $setting->getSetting();
    $result = $ziki->getEach($id);
    $count = new Ziki\Core\Subscribe();
    $fcount = $count->fcount();
    $count = $count->count();


    return $this->template->render('blog-details.html', $settings, ['result' => $result, 'count' => $count, 'fcount' => $fcount]);
});
Router::get('/timeline', function ($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    $directory = "./storage/contents/";
    $ziki = new Ziki\Core\Document($directory);
    $post = $ziki->fetchAllRss();
    $count = new Ziki\Core\Subscribe();
    $fcount = $count->fcount();
    $count = $count->count();
    return $this->template->render('timeline.html', ['posts' => $post, 'count' => $count, 'fcount' => $fcount]);
});

Router::get('/tags/{id}', function ($request, $id) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    $directory = "./storage/contents/";
    $ziki = new Ziki\Core\Document($directory);
    $result = $ziki->tagPosts($id);
    $twig_vars = ['posts' => $result, 'tag' => $id];
    return $this->template->render('tags.html', $twig_vars);
});
Router::post('/publish', function ($request) {
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
    $initial_images = array_filter($data, function ($key) {
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
    $result = $ziki->create($title, $body, $tags, $images, $extra);
    return $this->template->render('timeline.html', ['ziki' => $result]);
});

Router::get('/about', function ($request) {
    include ZIKI_BASE_PATH . "/src/core/SendMail.php";
    $checkifOwnersMailIsprovided = new  SendContactMail();
    $checkifOwnersMailIsprovided->getOwnerEmail();
    $aboutContent = $checkifOwnersMailIsprovided->getPage();
    $message = [];
    if (empty($checkifOwnersMailIsprovided->getOwnerEmail())) {
        $message['ownerEmailNotProvided'] = true;
    }
    if (isset($_SESSION['messages'])) {
        $message = $_SESSION['messages'];
        unset($_SESSION['messages']);
    }
    return $this->template->render('about.html', ['message' => $message, 'about' => $aboutContent]);
});
Router::post('/send', function ($request) {
    include ZIKI_BASE_PATH . "/src/core/SendMail.php";
    $request = $request->getBody();
    $SendMail = new SendContactMail();
    $SendMail->mailBody = $this->template->render('mail-template.html', ['guestName' => $request['guestName'], 'guestEmail' => $request['guestEmail'], 'guestMsg' => $request['guestMsg']]);
    $SendMail->sendMail($request);
    $SendMail->clientMessage();
    return $SendMail->redirect('/about');
});
Router::post('/setcontactemail', function ($request) {
    include ZIKI_BASE_PATH . "/src/core/SendMail.php";
    $request = $request->getBody();
    $SetContactEmail = new SendContactMail();
    $SetContactEmail->setContactEmail($request);
    $SetContactEmail->clientMessage();
    return $SetContactEmail->redirect('/profile');
});
Router::post('/updateabout', function ($request) {
    include ZIKI_BASE_PATH . "/src/core/SendMail.php";
    $request = $request->getBody();
    $updateabout = new SendContactMail();
    $updateabout->updateAbout($request);
    $updateabout->clientMessage();
    return $updateabout->redirect('/profile');
});
Router::get('delete/{id}', function ($request, $id) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return new RedirectResponse("/");
    }
    $directory = "./storage/contents/";
    $ziki = new Ziki\Core\Document($directory);
    $result = $ziki->delete($id);
    return $this->template->render('timeline.html', ['delete' => $result]);
});
Router::get('/published-posts', function ($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    return $this->template->render('published-posts.html');
});

// Start- Portfolio page
Router::get('/portfolio', function ($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    return $this->template->render('portfolio.html');
});
// End- Portfolio

// Start- Portfolio_expanded page
Router::get('/portfolio-expanded', function ($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    return $this->template->render('portfolio-expanded.html');
});
// End- Portfolio_expanded


// ahmzyjazzy add this (^_^) : setting page
Router::get('/settings', function ($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    $setting = new Ziki\Core\Setting();
    $settings = $setting->getSetting();
    return $this->template->render('settings.html');
});

// ahmzyjazzy add this (^_^) : setting api
Router::post('/appsetting', function ($request) {

    //create middleware to protect api from non auth user
    //$user = new Ziki\Core\Auth();
    //if (!$user->is_logged_in()) {
    //    return json_encode(array("msg" => "Authentication failed, pls login.", "status" => "error", "data" => null));
    //}

    $data = $request->getBody();
    $field = $data['field']; //field to update in  app.json
    $value = $data['value']; //value for setting field in app.json

    $setting = new Ziki\Core\Setting();

    try {
        $result = $setting->updateSetting($field, $value);
        if ($result) {
            echo json_encode(array("msg" => "Setting updated successfully", "status" => "success", "data" => $result));
        } else {
            echo json_encode(array("msg" => "Field does not exist", "status" => "error", "data" => null));
        }
    } catch (Exception $e) {
        echo json_encode(array("msg" => "Caught exception: ",  $e->getMessage(), "\n", "status" => "error", "data" => null));
    }

    return;
});

// profile page
Router::get('/profile', function ($request) {
    ///please don't remove or change the included path
    include ZIKI_BASE_PATH . "/src/core/SendMail.php";
    //please don't rename the variables 
    $userSiteDetails = new  SendContactMail();
    //this  gets the owners email address
    $userEmailAddr = $userSiteDetails->getOwnerEmail();
    //this gets the page content
    $getAboutPageContent = $userSiteDetails->getPage();
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    //this for error and successs messages
    $message = [];
    if (isset($_SESSION['messages'])) {
        $message = $_SESSION['messages'];
        unset($_SESSION['messages']);
    }

    return $this->template->render('profile.html', ['message' => $message, 'userEmailAddr' => $userEmailAddr, 'about' => $getAboutPageContent]);
});

// following page
Router::get('/following', function ($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    $directory = "./storage/contents/";
    $ziki = new Ziki\Core\Document($directory);
    $list = $ziki->subscription();
    $count = new Ziki\Core\Subscribe();
    $fcount = $count->fcount();
    $count = $count->count();

    return $this->template->render('following.html', ['sub' => $list, 'count' => $count, 'fcount' => $fcount]);
});

// followers page
Router::get('/followers', function ($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    $directory = "./storage/contents/";
    $ziki = new Ziki\Core\Document($directory);
    $list = $ziki->subscriber();
    $count = new Ziki\Core\Subscribe();
    $fcount = $count->fcount();
    $count = $count->count();

    return $this->template->render('followers.html', ['sub' => $list, 'count' => $count, 'fcount' => $fcount]);
});

// Subscription page
Router::post('/subscriptions', function ($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    $directory = "./storage/contents/";
    $ziki = new Ziki\Core\Document($directory);
    $list = $ziki->subscription();

    $count = new Ziki\Core\Subscribe();
    $fcount = $count->fcount();
    $count = $count->count();


    return $this->template->render('subscriptions.html', ['sub' => $list, 'count' => $count, 'fcount' => $fcount]);
});

// Subscribers page
Router::get('/subscribers', function ($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    $directory = "./storage/contents/";
    $ziki = new Ziki\Core\Document($directory);
    $list = $ziki->subscriber();
    $count = new Ziki\Core\Subscribe();
    $fcount = $count->fcount();
    $count = $count->count();

    return $this->template->render('subscriber.html', ['sub' => $list, 'count' => $count, 'fcount' => $fcount]);
});

// 404 page
Router::get('/editor', function ($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    return $this->template->render('editor.html');
});
Router::get('/404', function ($request) {
    return $this->template->render('404.html');
});
Router::get('/blog-details', function ($request) {

    $setting = new Ziki\Core\Setting();
    $settings = $setting->getSetting();
    return $this->template->render('blog-details.html', $settings);
});

// Start- Portfolio page

Router::get('/portfolio', function ($request) {

    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    return $this->template->render('portfolio.html');
});
// End- Portfolio page


// Start- followers page

Router::get('/followers', function ($request) {

    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    return $this->template->render('followers.html');
});
// End- followers page

// Start- following page

Router::get('/following', function ($request) {

    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    $directory = "./storage/contents/";
    $ziki = new Ziki\Core\Document($directory);
    $list = $ziki->subscription();
    $count = new Ziki\Core\Subscribe();
    $count = $count->count();

    return $this->template->render('following.html', ['sub' => $list, 'count' => $count]);
});
// End- following page


/* Devmohy working on draft */
/* Save draft*/
Router::post('/saveDraft', function ($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    $directory = "./storage/drafts/";
    $data = $request->getBody();
    $title = $data['title'];
    $body = $data['postVal'];
    $tags = $data['tags'];
    $initial_images = array_filter($data, function ($key) {
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
    $result = $ziki->create($title, $body, $tags, $images, true);

    return $this->template->render('drafts.html', ['ziki' => $result]);
});

/* Save draft */
/* Get all saved draft */
Router::get('/drafts', function ($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    $directory = "./storage/drafts/";
    $ziki = new Ziki\Core\Document($directory);
    $draft = $ziki->get();
    return $this->template->render('drafts.html', ['drafts' => $draft]);
});

//videos page
Router::get('/videos', function ($request) {
    $user = new Ziki\Core\Auth();

    $directory = "./storage/videos/";
    $ziki = new Ziki\Core\Document($directory);
    $Videos = $ziki->getVideo();
    //print_r($Videos);
    return $this->template->render('videos.html', ['videos' => $Videos, 'user' => $user]);
});
Router::get('/microblog', function ($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    //print_r($Videos);
    return $this->template->render('microblog.html');
});



// Router::get('/about', function ($request) {
//     return $this->template->render('about-us.html');
// });

//download page
Router::get('/download', function ($request) {
    return $this->template->render('download.html');
});

Router::get('/auth/{provider}/{token}', function ($request, $token) {
    $user = new Ziki\Core\Auth();
    $check = $user->validateAuth($token);
    if ($_SESSION['login_user']['role'] == 'guest') {
        return $user->redirect('/');
    } else {
        return $user->redirect('/timeline');
    }
});

Router::get('/setup/{provider}/{token}', function ($request, $token) {
    $user = new Ziki\Core\Auth();
    $check = $user->validateAuth($token);
    if ($_SESSION['login_user']['role'] == 'guest') {
        return $user->redirect('/');
    } else {
        return $user->redirect('/profile');
    }
});

Router::get('/logout', function ($request) {
    $user = new Ziki\Core\Auth();
    $user->log_out();
    return $user->redirect('/');
});
Router::get('/api/images', function () {
    return (new Ziki\Core\UploadImage)->getAllImages();
});
Router::post('/api/upload-image', function () {
    return (new Ziki\Core\UploadImage)->upload();
});

Router::post('/setup', function ($request) {
    $data = $request->getBody();
    $user = new Ziki\Core\Auth();
    $setup = $user->setup($data);
    if ($setup == true) {
        return $user->redirect('/timeline');
    } else {
        return $user->redirect('/install');
    }
});

Router::get('/install', function ($request) {
    $user = new Ziki\Core\Auth();
    if ($user::isInstalled() == false) {
        return $user->redirect('/');
    } else {
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $host = $user->hash($url);
        return $this->installer->render('install.html', ['host' => $host]);
    }
});

/* Add Video*/
Router::post('/addvideo', function ($request) {
    $user = new Ziki\Core\Auth();
    if (!$user->is_logged_in()) {
        return $user->redirect('/');
    }
    $directory = "./storage/videos/";
    $data = $request->getBody();

    //Get youtube url id for embed
    parse_str(parse_url($data['domain'], PHP_URL_QUERY), $YouTubeId);
    $video_url = "https://www.youtube.com/embed/" . $YouTubeId['v'];
    $video_title = $data['title'];
    $video_about = $data['description'];
    $ziki = new Ziki\Core\Document($directory);
    $ziki->addVideo($video_url, $video_title, $video_about);
    return $user->redirect('/videos');
});
