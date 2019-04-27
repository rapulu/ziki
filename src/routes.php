<?php

$router->get('/', function($request) {
    $directory = "./storage/contents/";
    $ziki = new Ziki\Core\Document($directory);
    $post = $ziki->get();
    // Render our view
    return $this->template->render('index.html', ['posts' => $post] );
});

$router->get('/blog-details', function($request) {
    $directory = "./storage/contents/";
    $ziki = new Ziki\Core\Document($directory);
   $result = $ziki->getEach('post-detail-1555682341');
    echo var_dump($result); die();
   /*
   $ziki = [
        [ 'name'          => 'Adroit' ],
        [ 'name'          => 'Olu' ],
        [ 'name'          => 'Amuwo' ],
    ];
    return $this->template->render('blog-details.html', ['ziki' => $ziki] );*/
});

$router->get('/timeline', function($request) {
    $directory = "./storage/contents/";
    $ziki = new Ziki\Core\Document($directory);
    $post = $ziki->get();
    return $this->template->render('timeline.html', ['posts' => $post] );
});

$router->post('/timeline', function($request) {
    $directory = "./storage/contents/";
    $body = $_POST['postVal'];
    var_dump($body); die();
    $ziki = new Ziki\Core\Document($directory);
    $result = $ziki->create($body);
    var_dump($result); die();
    return $this->template->render('timeline.html', ['ziki' => $result]);
});

$router->get('/contact-us', function($request) {
    $ziki = [
        [ 'name'          => 'Adroit' ],
        [ 'name'          => 'Twig' ],
    ];

    return $this->template->render('contact-us.html', ['ziki' => $ziki] );
});

$router->get('/published-posts', function($request) {
    return $this->template->render('published-posts.html');
});

$router->get('/themes', function($request) {
    return $this->template->render('themes.html');
});

$router->get('/profile', function($request) {
    return $this->template->render('profile.html');
});

$router->get('/subscriptions', function($request) {
    return $this->template->render('subscriptions.html');
});

$router->get('/subscribers', function($request) {
    return $this->template->render('subscribers.html');
});

$router->get('/editor', function($request) {
    return $this->template->render('editor.html');
});

$router->get('/404', function($request) {
    return $this->template->render('404.html');
});

$router->get('/drafts', function($request) {
    return $this->template->render('drafts.html');
});

$router->get('/about', function($request) {
    return $this->template->render('about-us.html');
});

$router->get('/download', function($request) {
    return $this->template->render('download.html');
});