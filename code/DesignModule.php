<?php

namespace Unclecheese\Green;

use \Exception;
use \Injector;
use \SSViewer;
use \ArrayData;
use \DBField;
use \Requirements;

/**
 * Class DesignModule
 * Defines a collection of templates, css, javascript, and data that make up
 * a design module
 *
 * @package Unclecheese\Green
 * @author UncleCheese <unclecheese@leftandmain.com>
 */
class DesignModule
{


    /**
     * @var array
     */
    private static $data_file_names = [
        'content.yml',
        'content.yaml',
        'content.json'
    ];


    /**
     * @var string
     */
    protected $directory;


    /**
     * DesignModule constructor.
     * @param $path
     */
    public function __construct($path)
    {
        $this->directory = $path;

        if (!is_dir($this->getPath())) {
            throw new Exception("Invalid directory {$this->getPath()}");
        }
    }


    /**
     * @return string
     */
    public function getPath()
    {
        return $this->directory;
    }


    /**
     * Gets the path relative to the web root
     * @return string
     */
    public function getRelativePath()
    {
        return Green::inst()->getDesignFolder() . '/' . $this->getName();
    }


    /**
     * A simple identifier for the module
     * @return string
     */
    public function getName()
    {
        return basename($this->directory);
    }


    /**
     * Gets the absolute path to the layout template file
     * @return string
     */
    public function getLayoutTemplateFile()
    {
    	$layout = $this->getPath() . '/layout.ss';

    	return file_exists($layout) ? $layout : $this->getPath() . '/index.ss';
    }


    /**
     * Gets the absolute path to the main template file
     * @return string
     */
    public function getMainTemplateFile()
    {
    	$main = $this->getPath() . '/main.ss';

    	if(file_exists($main)) {
    		return $main;
    	}

    	$main = (string) $this->getConfiguration()->main_template;
    	if($main) {
    		return SSViewer::getTemplateFileByType($main, 'main');
    	}

    	return false;
    }

    /**
     * Gets the contents of the design template
     * @return string
     * @throws Exception
     */
    public function getTemplateContents()
    {
        $path = $this->getLayoutTemplateFile();
        if (!file_exists($path)) {
            throw new Exception("File index.ss does not exist in {$this->getName()}");
        }

        return file_get_contents($path);
    }

    
    /**
     * Gets the data source as an object
     * @return bool|DataSource
     */
    public function getDataSource()
    {
        foreach (self::$data_file_names as $name) {
            $path = $this->getPath() . '/' . $name;
            if (file_exists($path)) {
                return new DataSource($path);
            }
        }

        return false;
    }


    public function getConfiguration()
    {
    	$path = $this->getPath().'/config.yml';
    	if(file_exists($path)) {
    		return DBField::create_field('YAMLField', file_get_contents($path));
    	}

    	return ArrayData::create([]);
    }


    /**
     * Gets all of the stylesheets in the module
     * @return array
     */
    public function getStylesheets()
    {
        return $this->getFilesByPattern('*.css');
    }


    /**
     * Gets all of the javascripts in the module
     * @return array
     */
    public function getJavascripts()
    {
        return $this->getFilesByPattern('*.js');
    }


    /**
     * Gets all the images in the module
     * @return  array
     */
    public function getImages()
    {
    	return $this->getFilesByPattern('/.*\.(jpeg|jpg|gif|png)/');
    }


    /**
     * Loads all of the JS and CSS
     */
    public function loadRequirements()
    {
        foreach ($this->getStylesheets() as $css) {
            Requirements::css($this->getRelativePath().'/'.basename($css));
        }
        foreach ($this->getJavascripts() as $js) {
            Requirements::javascript($this->getRelativePath().'/'.basename($js));
        }    	
    }


    /**
     * A helper to get files of a certain pattern, e.g. extension
     * @param $ext
     * @return array
     */
    protected function getFilesByPattern($pattern)
    {
        $iterator = Injector::inst()->create('GreenFinder')
            ->in($this->getPath())
            ->name($pattern);
        $files = [];

        foreach ($iterator as $file) {
            $files[] = $this->getRelativePath() . '/' . $file->getRelativePathname();
        }

        return $files;
    }

}