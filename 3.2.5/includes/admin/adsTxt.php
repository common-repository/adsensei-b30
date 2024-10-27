<?php

namespace wpadsensei;

class adsTxt {

    /**
     * Content to add
     * @var string 
     */
    private $content;

    /**
     * Pattern to search and replace
     * @var string 
     */
    private $pattern;

    /**
     * 
     * @param array $content to add
     * @param string $pattern for content to remove
     */
    public function __construct($content = array(), $pattern = '') {
        $this->content = $content;

        $this->pattern = $pattern;

        $this->filename = ABSPATH . 'ads.txt';
    }
    
    /**
     * Check if we need to create an ads.txt
     * @return boolean
     */
    public function needsAdsTxt(){
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        if (!is_file($this->filename)){
            return true;
        }
        // Initialize the WordPress filesystem
        $filesystem = WP_Filesystem();

        // Check if the filesystem is initialized successfully
        if ($filesystem) {
            // Path to the file
            // get everything from ads.txt and convert to array
            $contentText = $filesystem->get_contents($this->filename);
            
            // Pattern not find 
            if ($contentText !== false && strpos($contentText, $this->pattern) === false) {
                return true;
            } else {
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * Write ads.txt
     * @return bool
     */
    public function writeAdsTxt() {
        // Initialize the WordPress filesystem
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        
        $filesystem = WP_Filesystem();
        
        // Check if the filesystem is initialized successfully
        if ( $filesystem ) {
            // Write the content to the file
            if ( $filesystem->put_contents( $this->filename, $this->content ) ) {
                // Show notice that ads.txt has been created
                set_transient( 'adsensei_vi_ads_txt_notice', true, 300 );
                return true;
            } else {
                // Handle error when failed to write content to the file
                return false;
            }
        } else {
            // Handle filesystem initialization error
            return false;
        }
        // show error admin notice
        set_transient('adsensei_vi_ads_txt_error', true, 300);
        return false;
    }
    
    

    /**
     * Create and return the content
     * @return string
     */
    public function getContent() {
        // ads.txt does not exists
        if (!is_file($this->filename)) {
            return $this->content . "\r\n";
        }

        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        $filesystem = WP_Filesystem();
        
        // Check if the filesystem is initialized successfully
        if ( $filesystem ) {
            // get everything from ads.txt and convert to array
            $contentText = $filesystem->get_contents($this->filename);
             // Change all \r\n to \n
            //$contentText = str_replace(array("\r\n", "\n"), '', $contentText);

            //$content = array_filter(explode("\n", trim($contentText)), 'trim');
            $content = explode("\n", $contentText);
            
            // Pattern not find so append new content to ads.txt existing content  
            if (strpos($contentText, $this->pattern) === false) {
                return $contentText . "\r\n" . $this->content;
            }

            // Pattern found, so remove everything first and add new stuff from api response
            $newContent = '';
            foreach ($content as $entry) {
                if (strpos($entry, $this->pattern) !== false) {
                continue; 
                }
                    $newContent .= str_replace(array("\r", "\n"), '', $entry) . "\r\n";
                
            }
            return $newContent . $this->content;
        }
        return '';
        
       
    }

}
