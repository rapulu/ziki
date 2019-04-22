<?php
namespace Ziki\Core;
/**
 *	A Collection object represents a set of Document objects (matching certain criterias).
 *
 */

class Collection
{
    protected $dir;
    
    protected $entries;

    public function __construct($dir, $entries)
    {
        $this->dir     = $dir;
        $this->entries = $entries;
    }
    
    public function slug()
    {
        return $this->dir->getBasename();
    }
    
    public function entries()
    {
        return $this->entries;
    }
    
    public function title()
    {
        $title = ltrim($this->slug(), '/');
        $title = str_replace(['-', '_'], ' ', $title);
        $title = ucwords($title);
        return $title;
    }
    
    public function meta()
    {
        return [
            'title' => $this->title(),
        ];
    }
}
