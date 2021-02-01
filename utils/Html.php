<?php
/**
 * HTML Page Model
 * @package Sevida
 * @subpackage Utilities
 */
class Html {
    /**
     * @var string
     */
    public string $title;
    /**
     * @var string
     */
    public string $path;
    /**
     * @var string
     */
    public array $metaTags = [];
    /**
     * @var array
     */
    public array $cssFiles = [];
    /**
     * @var array
     */
    public array $cssStyles = [];
    /**
     * @var array
     */
    public array $jsCodes = [];
    /**
     * @var array
     */
    public array $jsFiles = [];
    /**
     * @param string $title
     * @param string $path;
     */
    public function __construct( string $title, string $path ) {
        $this->title = $title;
        $this->path = $path;
    }
}