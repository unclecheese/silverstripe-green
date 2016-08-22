<?php

namespace UncleCheese\Green;

use \Object;
use \Injector;
use \Config;

/**
 * Class Green
 * A utility class for dealing with global concerns of the Green module
 *
 * @package UncleCheese\Green
 * @author UncleCheese <unclecheese@leftandmain.com>
 */
class Green extends Object
{


    /**
     * Gets the default data type, e.g. "json"
     * @return string
     */
    public static function default_data_type()
    {
        return self::config()->default_data_type;
    }


    /**
     * @return Green
     */
    public static function inst()
    {
        return Injector::inst()->get(__CLASS__);
    }


    public static function install()
    {
		if(!self::config()->public_access) return;

		$currentRoutes = Config::inst()->get('Director','rules');
		$newRoutes = [];

		foreach(self::inst()->getDesignModules() as $module) {
			$route = (string) $module->getConfiguration()->public_url;
			if($route) {
				$newRoutes[$route] = 'UncleCheese\Green\Controller';
			}		
		}
		$routes = array_merge($newRoutes, $currentRoutes);
		
		Config::inst()->update('Director', 'rules', $routes);
		
    }


    /**
     * Gets the location where design modules are stored.
     * Use $theme to interpolate the current theme directory.
     *
     * @return string
     */
    public function getDesignFolder()
    {
        return str_replace(
            '$ThemeDir/',
            THEMES_DIR . '/' . Config::inst()->get('SSViewer', 'theme') . '/',
            $this->config()->design_folder
        );
    }


    /**
     * Gets the absolute path to the design folder
     * @return string
     */
    public function getAbsoluteDesignFolder()
    {
        return BASE_PATH . '/' . $this->getDesignFolder();
    }


    /**
     * Gets all of the design modules as objects
     * @return array
     */
    public function getDesignModules()
    {
        $iterator = $this->createFinder()
            ->in($this->getAbsoluteDesignFolder())
            ->directories();

        $modules = [];
        foreach ($iterator as $file) {
        	if(file_exists($file->getRealPath().'/index.ss')) {
            	$modules[] = new DesignModule($file->getRealPath());
        	}
        }

        return $modules;
    }


    /**
     * Gets a specific design module as an object by its name
     * @param string $name
     * @return bool|mixed
     */
    public function getDesignModule($name)
    {
        foreach ($this->getDesignModules() as $module) {
            if ($module->getName() == $name) {
                return $module;
            }
        }

        return false;
    }


    /**
     * Creates a Finder service
     * @return object
     */
    protected function createFinder()
    {
        return Injector::inst()->create('GreenFinder');
    }
}
