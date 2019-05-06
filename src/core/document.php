<?php
namespace Ziki\Core;

use Parsedown;
use Mni\FrontYAML\Parser;
use KzykHys\FrontMatter\FrontMatter;
use Symfony\Component\Finder\Finder;
use KzykHys\FrontMatter\Document as Doc;

/**
 *	The Document class holds all properties and methods of a single page document.
 *
 */

class Document
{
    //define an instance of the symfony clss
    //define an instance of the frontMatter class

    protected $file;

    public function __construct($file)
    {
        $this->file       = $file;
    }

    public function file()
    {
        return $this->file;
    }

    //for creating markdown files
    //kjarts code here
    public function create($title, $content, $tags, $image, $extra)
    {
        $time = date(DATE_RSS, time());
        $unix = strtotime($time);
        // Write md file
        $document = FrontMatter::parse($content);
        $md = new Parser();
        $markdown = $md->parse($document);

        $yaml = $markdown->getYAML();
        $html = $markdown->getContent();
        //$doc = FileSystem::write($this->file, $yaml . "\n" . $html);

        $yamlfile = new Doc();
        if ($title != "") {
            $yamlfile['title'] = $title;
        }
        if ($tags != "") {
            $tag = explode(",", $tags);
            $put = [];
            foreach ($tag as $value) {
                array_push($put, $value);
            }
            $yamlfile['tags'] = $put;
        }
        if (!empty($image)) {
            foreach ($image as $key => $value) {
                $decoded = base64_decode($image[$key]);
                $url = "./storage/images/" . $key;
                FileSystem::write($url, $decoded);
                $yamlfile['image'] = $url;
            }
        }

        if (!$extra) {
           $yamlfile['type'] = "published";
           $yamlfile['published_at'] = $time;
        } else {
            $yamlfile['type'] = "draft";
            $yamlfile['published_at'] = "";
        }

        // $yamlfile['post_dir'] = SITE_URL . "/storage/contents/{$unix}";
        // create slug by first removing spaces
        if($title != ""){
            $striped = str_replace(' ', '-', $title);
        }else{
            $striped = str_replace(' ', '-', $time);
        }
        
        $yamlfile['updated_at'] = "";
        $yamlfile['created_at'] = $time;
        // then removing encoded html chars
        $striped = preg_replace("/(&#[0-9]+;)/", "", $striped);
        $yamlfile['slug'] = $striped . "-{$unix}";
        $yamlfile->setContent($content);
        $yaml = FrontMatter::dump($yamlfile);
        $file = $this->file;
        $dir = $file . $unix . ".md";
        //return $dir; die();
        $doc = FileSystem::write($dir, $yaml);
        if (!$extra) {
            if ($doc) {
                $result = array("error" => false, "message" => "Post published successfully");
                $this->createRSS();
            } else {
                $result = array("error" => true, "message" => "Fail while publishing, please try again");
            }
        } else {
            if ($doc) {
                $result = array("error" => false, "message" => "Draft saved successfully");
            } else {
                $result = array("error" => true, "message" => "Fail while publishing, please try again");
            }
        }

        return $result;
    }

    // Start- create portfolio

    public function createportfolio($title, $content, $image)
    {
        $time = date(DATE_RSS, time());
        $unix = strtotime($time);
        // Write md file
        $document = FrontMatter::parse($content);
        $md = new Parser();
        $markdown = $md->parse($document);

        $yaml = $markdown->getYAML();
        $html = $markdown->getContent();
        //$doc = FileSystem::write($this->file, $yaml . "\n" . $html);

        $yamlfile = new Doc();
        $yamlfile['title'] = $title;
        if ($tags != "") {
            $tag = explode(",", $tags);
            $put = [];
            foreach ($tag as $value) {
                array_push($put, $value);
            }
            $yamlfile['tags'] = $put;
        }
        if (!empty($image)) {
            foreach ($image as $key => $value) {
                $decoded = base64_decode($image[$key]);
                $url = "./storage/images/portfolio/" . $key;
                FileSystem::write($url, $decoded);
            }
        }

        if (!$extra) {
            $yamlfile['post_dir'] = SITE_URL . "/storage/portfolio/{$unix}";
        } else {
            $yamlfile['post_dir'] = SITE_URL . "/storage/portfolio/{$unix}";
            $yamlfile['image'] = "./storage/images/portfolio/" . $key;
        }

        // create slug by first removing spaces
        $striped = str_replace(' ', '-', $title);
        // then removing encoded html chars
        $striped = preg_replace("/(&#[0-9]+;)/", "", $striped);
        $yamlfile['slug'] = $striped . "-{$unix}";
        $yamlfile['timestamp'] = $time;
        $yamlfile->setContent($content);
        $yaml = FrontMatter::dump($yamlfile);
        $file = $this->file;
        $dir = $file . $unix . ".md";
        //return $dir; die();
        $doc = FileSystem::write($dir, $yaml);
        if (!$extra) {
            if ($doc) {
                $result = array("error" => false, "message" => "Post published successfully");
                $this->createRSS();
            } else {
                $result = array("error" => true, "message" => "Fail while publishing, please try again");
            }
        } else {
            if ($doc) {
                $result = array("error" => false, "message" => "Draft saved successfully");
            } else {
                $result = array("error" => true, "message" => "Fail while publishing, please try again");
            }
        }

        return $result;
    }

    //End- create  portfolio

    //get post
    public function get()
    {
        $finder = new Finder();

        // find all files in the current directory
        $finder->files()->in($this->file);
        $posts = [];
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $document = $file->getContents();
                $parser = new Parser();
                $document = $parser->parse($document);
                $yaml = $document->getYAML();
                $body = $document->getContent();
                //$document = FileSystem::read($this->file);
                $parsedown  = new Parsedown();
                $tags = isset($yaml['tags']) ? $yaml['tags'] : '';
                $title = isset($yaml['title']) ? $parsedown->text($yaml['title']) : '';
                $slug = $parsedown->text($yaml['slug']);
                $image = isset($yaml['image']) ? $parsedown->text($yaml['image']) : '';
                $slug = preg_replace("/<[^>]+>/", '', $slug);
                $image = preg_replace("/<[^>]+>/", '', $image);
                $bd = $parsedown->text($body);
                ////
                preg_match('/<img[^>]+src="((\/|\w|-)+\.[a-z]+)"[^>]*\>/i', $bd, $matches);
                $first_img = false;
                if (isset($matches[1])) {
                    // there are images
                    $first_img = $matches[1];
                    // strip all images from the text
                    $bd = preg_replace("/<img[^>]+\>/i", " (image) ", $bd);
                }
                $time = $parsedown->text($yaml['timestamp']);
                $url = $parsedown->text($yaml['post_dir']);
                $content['title'] = $title;
                $content['body'] = $this->trim_words($bd, 200);
                $content['url'] = $url;
                $content['timestamp'] = $time;
                $content['tags'] = $tags;
                $content['slug'] = $slug;
                $content['preview_img'] = $first_img;
                //content['slug'] = $slug;
                $file = explode("-", $slug);
                $filename = $file[count($file) - 1];
                $content['filename'] = $filename;
                //content['timestamp'] = $time;
                $content['image'] = $image;
                $content['date'] = date('d M Y ', $filename);

                array_push($posts, $content);
            }
            krsort($posts);
            return $posts;
        } else {
            return false;
        }
    }
    //kjarts code for getting and creating markdown files end here

    public function getDrafts()
    {
        $finder = new Finder();

        // find all files in the current directory
        $finder->files()->in($this->file);
        $drafts = [];
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $document = $file->getContents();
                $parser = new Parser();
                $document = $parser->parse($document);
                $yaml = $document->getYAML();
                $body = $document->getContent();
                //$document = FileSystem::read($this->file);
                $parsedown  = new Parsedown();
                if(isset($yaml['title'])){
                    $title = $parsedown->text($yaml['title']);
                }else{
                    $title = "";
                }
                if(isset($yaml['image'])){
                    $image = $parsedown->text($yaml['image']);
                }else{
                    $image = "";
                }
                $slug = $parsedown->text($yaml['slug']);
                $type = $parsedown->text($yaml['type']); 
                $slug = preg_replace("/<[^>]+>/", '', $slug);
                $image = preg_replace("/<[^>]+>/", '', $image);
                $bd = $parsedown->text($body);
                $time = $parsedown->text($yaml['created_at']);
                //$url = $parsedown->text($yaml['post_dir']);
                if($type == "<p>draft</p>"){
                    $content['title'] = $title;
                    $content['body'] = $bd;
                    //$content['url'] = $url;
                    $content['slug'] = $slug;
                    $file = explode("-", $slug);
                    $filename = $file[count($file) - 1];
                    $content['filename'] = $filename;
                    $content['timestamp'] = $time;
                    $content['image'] = $image;
                    $content['type'] = $type;
                    array_push($drafts, $content);
                }
                
                
            }
            return $drafts;
        } else {
            return false;
        }
    }

    public function editDraft($id)
    {
        $finder = new Finder();
        // find all files in the current directory
        $finder->files()->in($this->file)->name($id.'.md');
        $posts = [];
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $document = $file->getContents();
                $parser = new Parser();
                $document = $parser->parse($document);
                $yaml = $document->getYAML();
                $body = $document->getContent();
                //$document = FileSystem::read($this->file);
                $parsedown  = new Parsedown();
                $slug = $parsedown->text($yaml['slug']);
                $slug = preg_replace("/<[^>]+>/", '', $slug);
                if ($slug == $id) {
                    $title = $parsedown->text($yaml['title']);
                    $bd = $parsedown->text($body);
                    $time = $parsedown->text($yaml['timestamp']);
                    //$url = $parsedown->text($yaml['post_dir']);
                    $content['title'] = $title;
                    $content['body'] = $bd;
                    //$content['url'] = $url;
                    $content['timestamp'] = $time;
                    array_push($posts, $content);
                }
            }
            return $posts;
        }
    }


    //trim_words used in triming strings by words
    function trim_words($string, $limit, $break = ".", $pad = "...")
    {
        if (strlen($string) <= $limit) return $string;

        if (false !== ($breakpoint = strpos($string, $break, $limit))) {
            if ($breakpoint < strlen($string) - 1) {
                $string = substr($string, 0, $breakpoint) . $pad;
            }
        }

        return $string;
    }

    public function fetchAllRss()
    {
        $rss = new \DOMDocument();
        $feed = [];
        $user = file_get_contents("src/config/auth.json");
        $user = json_decode($user, true);
        $data = file_get_contents("storage/rss/subscription.json");
        $urlArray = json_decode($data, true);

        $urlArray2 = array(
            array('name' => $user['name'], 'rss' => 'storage/rss/rss.xml', 'desc' => '', 'link' => '', 'img' => $user['image'], 'time' => ''),
            //                array('name' => 'Sample',  'url' => 'rss/rss.xml')
        );

        $result = array_merge($urlArray, $urlArray2);
        //  print_r($result);
        foreach ($result as $url) {
            $rss->load($url['rss']);

            foreach ($rss->getElementsByTagName('item') as $node) {
                if (!isset($node->getElementsByTagName('image')->item(0)->nodeValue)) {

                    $item = array(
                        'site'  => $url['name'],
                        'img'  => $url['img'],
                        'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
                        'desc'  => $node->getElementsByTagName('description')->item(0)->nodeValue,
                        'link'  => $node->getElementsByTagName('link')->item(0)->nodeValue . "?d=" . base64_encode(SITE_URL),
                        'date'  => date("F j, Y, g:i a", strtotime($node->getElementsByTagName('pubDate')->item(0)->nodeValue)),

                    );
                } else {
                    $item = array(
                        'site'  => $url['name'],
                        'img'  => $url['img'],
                        'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
                        'desc'  => $node->getElementsByTagName('description')->item(0)->nodeValue,
                        'link'  => $node->getElementsByTagName('link')->item(0)->nodeValue . "?d=" . base64_encode(SITE_URL),
                        'date'  => date("F j, Y, g:i a", strtotime($node->getElementsByTagName('pubDate')->item(0)->nodeValue)),
                        'image'  => $node->getElementsByTagName('image')->item(0)->nodeValue,
                    );
                }
                array_push($feed, $item);
            }
        }
        usort($feed, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        return $feed;
    }
    //RSS designed By DMAtrix;
    public function fetchRss()
    {
        $rss = new \DOMDocument();
        $feed = [];
        $user = file_get_contents("src/config/auth.json");
        $user = json_decode($user, true);
        $urlArray = array(
            array('name' => $user['name'], 'url' => 'storage/rss/rss.xml', 'img' => $user['image']),
        );

        foreach ($urlArray as $url) {
            $rss->load($url['url']);

            foreach ($rss->getElementsByTagName('item') as $node) {
                $item = array(
                    'site'  => $url['name'],
                    'img'  => $url['img'],
                    'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
                    'desc'  => $node->getElementsByTagName('description')->item(0)->nodeValue,
                    'link'  => $node->getElementsByTagName('link')->item(0)->nodeValue,
                    'date'  => date("F j, Y, g:i a", strtotime($node->getElementsByTagName('pubDate')->item(0)->nodeValue)),
                    'image'  => $node->getElementsByTagName('image')->item(0)->nodeValue,
                );
                array_push($feed, $item);
            }
        }
        usort($feed, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        return $feed;
    }
    //store rss By DMAtrix
    public function createRSS()
    {
        $user = file_get_contents("src/config/auth.json");
        $user = json_decode($user, true);

        date_default_timezone_set('UTC');
        $Feed = new RSS2;
        // Setting some basic channel elements. These three elements are mandatory.
        $Feed->setTitle($user['name']);
        $Feed->setLink(SITE_URL);
        $Feed->setDescription("");

        // Image title and link must match with the 'title' and 'link' channel elements for RSS 2.0,
        // which were set above.
        $Feed->setImage($user['name'], '', $user['image']);

        $Feed->setChannelElement('language', 'en-US');
        $Feed->setDate(date(DATE_RSS, time()));
        $Feed->setChannelElement('pubDate', date(\DATE_RSS, strtotime('2013-04-06')));


        $Feed->setSelfLink(SITE_URL . 'storage/rss/rss.xml');
        $Feed->setAtomLink('http://pubsubhubbub.appspot.com', 'hub');

        $Feed->addNamespace('creativeCommons', 'http://backend.userland.com/creativeCommonsRssModule');
        $Feed->setChannelElement('creativeCommons:license', 'http://www.creativecommons.org/licenses/by/1.0');

        $Feed->addGenerator();

        $finder = new Finder();
        $finder->files()->in($this->file);
        //print_r($finder->hasResults());
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $document = $file->getContents();
                $parser = new Parser();
                $document = $parser->parse($document);
                $yaml = $document->getYAML();
                $body = $document->getContent();

                $parsedown  = new Parsedown();

                $title = $parsedown->text($yaml['title']);
                $slug = $parsedown->text($yaml['slug']);
                $image = isset($yaml['image']) ? $parsedown->text($yaml['image']) : '';
                $slug = preg_replace("/<[^>]+>/", '', $slug);
                $image = preg_replace("/<[^>]+>/", '', $image);
                $bd = $parsedown->text($body);

                preg_match('/<img[^>]+src="((\/|\w|-)+\.[a-z]+)"[^>]*\>/i', $bd, $matches);
                $first_img = false;
                if (isset($matches[1])) {
                    // there are images
                    $first_img = $matches[1];
                    // strip all images from the text
                    $bd = preg_replace("/<img[^>]+\>/i", "", $bd);
                }
                $time = $parsedown->text(time());
                $url = $parsedown->text($yaml['post_dir']);

                $newItem = $Feed->createNewItem();
                $newItem->setTitle(strip_tags($title));
                $newItem->setLink("/post/" . strtolower($slug));
                $newItem->setDescription(substr(strip_tags($bd), 0, 100));
                $newItem->setDate(date(DATE_RSS, time()));

                $newItem->setAuthor($user['name'], $user['email']);
                $newItem->setId($url, true);
                $newItem->addElement('source', $user['name'] . '\'s page', array('url' => SITE_URL));

                $newItem->addElement('image', $first_img);

                $Feed->addItem($newItem);
            }
            $myFeed = $Feed->generateFeed();
            $handle = "storage/rss/rss.xml";
            $doc = FileSystem::write($handle, $myFeed);
            //        fwrite($handle, $myFeed);
            //      fclose($handle);
            $strxml = $Feed->printFeed();
        } else {
            return false;
        }
    }

    //RSS designed By DMAtrix;
    public function getRss()
    {
        $user = file_get_contents("src/config/auth.json");
        $user = json_decode($user, true);

        date_default_timezone_set('UTC');
        $Feed = new RSS2;
        // Setting some basic channel elements. These three elements are mandatory.
        $Feed->setTitle($user['name']);
        $Feed->setLink(SITE_URL);
        $Feed->setDescription("");

        // Image title and link must match with the 'title' and 'link' channel elements for RSS 2.0,
        // which were set above.
        $Feed->setImage($user['name'], '', $user['image']);

        $Feed->setChannelElement('language', 'en-US');
        $Feed->setDate(date(DATE_RSS, time()));
        $Feed->setChannelElement('pubDate', date(\DATE_RSS, strtotime('2013-04-06')));


        $Feed->setSelfLink(SITE_URL . 'storage/rss/rss.xml');
        $Feed->setAtomLink('http://pubsubhubbub.appspot.com', 'hub');

        $Feed->addNamespace('creativeCommons', 'http://backend.userland.com/creativeCommonsRssModule');
        $Feed->setChannelElement('creativeCommons:license', 'http://www.creativecommons.org/licenses/by/1.0');

        $Feed->addGenerator();

        $finder = new Finder();
        $finder->files()->in($this->file);

        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $document = $file->getContents();
                $parser = new Parser();
                $document = $parser->parse($document);
                $yaml = $document->getYAML();
                $body = $document->getContent();

                $parsedown  = new Parsedown();

                $title = $parsedown->text($yaml['title']);
                $slug = $parsedown->text($yaml['slug']);
                $slug = preg_replace("/<[^>]+>/", '', $slug);
                $bd = $parsedown->text($body);
                $time = $parsedown->text(time());
                $url = $parsedown->text($yaml['post_dir']);

                $newItem = $Feed->createNewItem();
                $newItem->setTitle(strip_tags($title));
                $newItem->setLink($slug);
                $newItem->setDescription(substr(strip_tags($bd), 0, 100));
                $newItem->setDate("2013-04-07 00:50:30");

                $newItem->setAuthor($user['name'], $user['email']);
                $newItem->setId($url, true);
                $newItem->addElement('source', $user['name'] . '\'s page', array('url' => SITE_URL));
                $Feed->addItem($newItem);
            }
            $myFeed = $Feed->generateFeed();

            $strxml = $Feed->printFeed();
        } else {
            return false;
        }
    }
    public function subscriber()
    {
        $db = "storage/rss/subscriber.json";
        $file = FileSystem::read($db);
        $data = json_decode($file, true);
        if (count($data) >= 1) {
            unset($file);
            $posts = [];
            foreach ($data as $key => $value) {

                $content['name'] = $value['name'];
                $content['img'] = $value['img'];
                $content['time'] = $value['time'];
                $content['desc'] = $value['desc'];
                $content['link'] = $value['link'];
                array_push($posts, $content);
            }
            return $posts;
        }
    }
    public function subscription()
    {
        $db = "storage/rss/subscription.json";
        $file = FileSystem::read($db);
        $data = json_decode($file, true);
        unset($file);
        $posts = [];
        foreach ($data as $key => $value) {

            $content['name'] = $value['name'];
            $content['img'] = $value['img'];
            $content['time'] = $value['time'];
            $content['desc'] = $value['desc'];
            $content['link'] = $value['link'];
            array_push($posts, $content);
        }
        return $posts;
    }
    //code for returnng details of each codes
    public function getEach($id)
    {
        $finder = new Finder();
        // find all files in the current directory
        $finder->files()->in($this->file);
        $posts = [];
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $document = $file->getContents();
                $parser = new Parser();
                $document = $parser->parse($document);
                $yaml = $document->getYAML();
                $body = $document->getContent();
                //$document = FileSystem::read($this->file);
                $parsedown  = new Parsedown();
                $slug = $parsedown->text($yaml['slug']);
                $slug = preg_replace("/<[^>]+>/", '', $slug);
                if ($slug == $id) {
                    $title = isset($yaml['title']) ? $parsedown->text($yaml['title']) : '';;
                    $bd = $parsedown->text($body);
                    $time = $parsedown->text($yaml['timestamp']);
                    $url = $parsedown->text($yaml['post_dir']);
                    $content['title'] = $title;
                    $content['body'] = $bd;
                    $content['url'] = $url;
                    $content['timestamp'] = $time;
                    array_push($posts, $content);
                }
            }
            return $posts;
        }
    }
    //end of get a post function

    // post
    public function tagPosts($id)
    {
        $finder = new Finder();
        // find all files in the current directory
        $finder->files()->in($this->file);
        $posts = [];
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $document = $file->getContents();
                $parser = new Parser();
                $document = $parser->parse($document);
                $yaml = $document->getYAML();
                $body = $document->getContent();
                //$document = FileSystem::read($this->file);
                $parsedown  = new Parsedown();
                // skip this document if it has no tags
                if (!isset($yaml['tags'])) {
                    continue;
                }
                $tags = $yaml['tags'];
                for ($i = 0; $i < count($tags); $i++) {
                    // strip away the leading "#" of the tag name
                    if (substr($tags[$i], 1) == $id) {
                        $slug = $parsedown->text($yaml['slug']);
                        $title = $parsedown->text($yaml['title']);
                        $bd = $parsedown->text($body);

                        // get the first image in the post body
                        // it will serve as the preview image
                        preg_match('/<img[^>]+src="((\/|\w|-)+\.[a-z]+)"[^>]*\>/i', $bd, $matches);
                        $first_img = false;
                        if (isset($matches[1])) {
                            // there are images
                            $first_img = $matches[1];
                            // strip all images from the text
                            $bd = preg_replace("/<img[^>]+\>/i", " (image) ", $bd);
                        }
                        $time = $parsedown->text($yaml['timestamp']);
                        $url = $parsedown->text($yaml['post_dir']);
                        $content['title'] = $title;
                        $content['body'] = $bd;
                        $content['url'] = $url;
                        $content['timestamp'] = $time;
                        $content['tags'] = $tags;
                        $content['slug'] = $yaml['slug'];
                        $content['preview_img'] = $first_img;
                        array_push($posts, $content);
                    }
                }
            }
        }
        return $posts;
    }

    //kjarts code for deleting post
    public function delete($id, $extra)
    {
        $finder = new Finder();
        // find all files in the current directory
        $finder->files()->in($this->file);
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $document = $file->getContents();
                $parser = new Parser();
                $document = $parser->parse($document);
                $yaml = $document->getYAML();
                $body = $document->getContent();
                $parsedown  = new Parsedown();
                $slug = $parsedown->text($yaml['slug']);
                $slug = preg_replace("/<[^>]+>/", '', $slug);
                if ($slug == $id) {
                    unlink($file);
                    $delete = "File deleted successfully";
                }
            }
            if (!$extra) {
                $this->createRSS();
            }
            return $delete;
        }
    }
    //deleteapOST by ProblemSolved;
    public function deletePost($post)
    {
        $finder = new Finder();
        // find post in the current directory
        $finder->files()->in($this->file)->name($post . '.md');
        if (!$finder->hasResults()) {
            return $this->redirect('/404');
        } else {
            ///coming back for some modifications
            unlink($this->file . $post . '.md');
            return $this->redirect('/published-posts');
        }
    }

    //get single post

    public function getPost($post)
    {
        $finder = new Finder();
        // find post in the current directory
        $finder->files()->in($this->file)->name($post . '.md');
        $content = [];
        if (!$finder->hasResults()) {
            return $this->redirect('/404');
        } else {
            foreach ($finder as $file) {
                $document = $file->getContents();
                $parser = new Parser();
                $document = $parser->parse($document);
                $yaml = $document->getYAML();
                $body = $document->getContent();
                $parsedown  = new Parsedown();
                $yamlTag = isset($yaml['tags']) ? $yaml['tags'] : [];
                $tags = [];
                foreach ($yamlTag as $tag) {
                    $removeHashTag = explode('#', $tag);
                    $tags[] = trim(end($removeHashTag));
                }
                $slug = $parsedown->text($yaml['slug']);
                $slug = preg_replace("/<[^>]+>/", '', $slug);
                $title = isset($yaml['title']) ? $parsedown->text($yaml['title']) : '';
                $bd = $parsedown->text($body);
                $time = $parsedown->text($yaml['timestamp']);
                //$url = $parsedown->text($yaml['post_dir']);
                $content['tags'] = $tags;
                $content['title'] = $title;
                $content['body'] = $bd;
                $content['url'] = $url;
                $content['timestamp'] = $time;
                $content['date'] = date('d M Y ', $post);
            }
            return $content;
        }
    }


    public function redirect($location)
    {
        header('Location:' . $location);
    }

    public function getRelatedPost($limit = 4, $tags, $skip_post)
    {

        $finder = new Finder();
        // find post in the current directory
        $finder->files()->in($this->file)->notName($skip_post . '.md')->contains($tags);
        $posts = [];
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $document = $file->getContents();
                $parser = new Parser();
                $document = $parser->parse($document);
                $yaml = $document->getYAML();
                $body = $document->getContent();
                //$document = FileSystem::read($this->file);
                $parsedown  = new Parsedown();
                if (!isset($yaml['tags'])) {
                    continue;
                }
                $tags = $yaml['tags'];
                $title = $parsedown->text($yaml['title']);
                $slug = $parsedown->text($yaml['slug']);
                $image = isset($yaml['image']) ? $parsedown->text($yaml['image']) : '';
                $slug = preg_replace("/<[^>]+>/", '', $slug);
                $image = preg_replace("/<[^>]+>/", '', $image);
                $bd = $parsedown->text($body);
                preg_match('/<img[^>]+src="((\/|\w|-)+\.[a-z]+)"[^>]*\>/i', $bd, $matches);
                $first_img = false;
                if (isset($matches[1])) {
                    // there are images
                    $first_img = $matches[1];
                    // strip all images from the text
                    $bd = preg_replace("/<img[^>]+\>/i", " (image) ", $bd);
                }
                $time = $parsedown->text($yaml['timestamp']);
                $url = $parsedown->text($yaml['post_dir']);
                $content['title'] = $title;
                $content['url'] = $url;
                $content['timestamp'] = $time;
                $content['tags'] = str_replace('#', '', implode(',', $tags));
                $content['slug'] = $slug;
                $content['preview_img'] = $first_img;
                //content['slug'] = $slug;
                $file = explode("-", $slug);
                $filename = $file[count($file) - 1];
                $content['filename'] = $filename;
                //content['timestamp'] = $time;
                $content['image'] = $image;
                $content['date'] = date('d M Y ', $filename);

                array_push($posts, $content);
            }
            krsort($posts);
            $countPosts = count($posts);
            if ($countPosts > $limit)
                array_shift($posts);
            return $posts;
        } else {
            return false;
        }
    }
    //stupid code by problemSolved ends here

    /**
     * updates a post stored in an md file
     * and echos a json object;
     *
     * @param [type] $mdfile
     * @param [type] $title
     * @param [type] $content
     * @param [type] $tags
     * @param [type] $image
     * @return void
     */
    public function updatePost($mdfile, $title, $content, $tags, $image)
    {
        $text = file_get_contents($mdfile);
        $document = FrontMatter::parse($text);
        $date = date("F j, Y, g:i a");
        // var_dump($document);
        // var_dump($document->getConfig());
        // var_dump($document->getContent());
        // var_dump($document['tags']);
        $document = new Doc();
        $tmp_title = explode(' ', $title);
        $slug = implode('-', $tmp_title);
        $document['title'] = $title;
        $document['slug'] = $slug;
        $document['timestamp'] = $date;
        $document['tags'] = explode(',', $tags);
        $hashedTags = [];
        // adding hash to the tags before storage
        foreach ($document['tags'] as $tag) {
            $hashedTags[] = '#' . $tag;
        }
        $document['tags'] = $hashedTags;
        $document['image'] = $image;
        $document->setContent($content);
        $yamlText = FrontMatter::dump($document);
        // var_dump($yamlText);
        $doc = FileSystem::write($mdfile, $yamlText);
        if ($doc) {
            $result = array("error" => false, "message" => "Post published successfully");
        } else {
            $result = array("error" => true, "message" => "Fail while publishing, please try again");
        }
        echo json_encode($result);
    }

    public function getSinglePost($id)
    {
        $directory = "./storage/contents/${id}.md";
        // var_dump($directory);
        $document = FrontMatter::parse(file_get_contents($directory));
        // var_dump($document);
        $content['title'] = $document['title'];
        $content['body'] = $document->getContent();
        // $content['url'] = $url;
        $content['timestamp'] = $document['timestamp'];

        return $content;
    }

    public function addVideo($url, $title, $content)
    {
        $time = date("F j, Y, g:i a");
        $unix = strtotime($time);
        // Write md file
        $document = FrontMatter::parse($content);
        $md = new Parser();
        $markdown = $md->parse($document);

        $yaml = $markdown->getYAML();
        $html = $markdown->getContent();
        //$doc = FileSystem::write($this->file, $yaml . "\n" . $html);

        $yamlfile = new Doc();
        $yamlfile['title'] = $title;
        $yamlfile['url'] = $url;

        $striped = str_replace(' ', '-', $title);
        $yamlfile['slug'] = $striped . "-{$unix}";
        $yamlfile['timestamp'] = $time;
        $yamlfile->setContent($content);
        $yaml = FrontMatter::dump($yamlfile);
        $file = $this->file;
        $dir = $file . $unix . ".md";
        //return $dir; die();
        $doc = FileSystem::write($dir, $yaml);
        if ($doc) {
            return true;
        }
        return false;
    }

    //get video
    public function getVideo()
    {
        $finder = new Finder();

        // find all files in the current directory
        $finder->files()->in($this->file);
        $videos = [];
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $document = $file->getContents();
                $parser = new Parser();
                $document = $parser->parse($document);
                $yaml = $document->getYAML();
                $body = $document->getContent();
                //$document = FileSystem::read($this->file);
                $parsedown  = new Parsedown();
                $title = $parsedown->text($yaml['title']);
                $bd = $parsedown->text($body);
                $time = $parsedown->text($yaml['timestamp']);
                $url = $parsedown->text($yaml['url']);
                $content['title'] = $title;
                $content['description'] = $bd;
                $content['domain'] = $url;
                $content['timestamp'] = $time;
                array_push($videos, $content);
            }
            return $videos;
        } else {
            return $videos;
        }
    }
}
