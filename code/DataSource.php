<?php

namespace UncleCheese\Green;

use \Exception;
use \DBField;

/**
 * Class DataSource
 * 
 * Defines a file of serialised data (e.g. JSON, YAML) that can be used
 * to populate a template
 *
 * @package UncleCheese\Green
 * @author UncleCheese <unclecheese@leftandmain.com>
 */
class DataSource
{

    /**
     * @var string
     */
    protected $path;


    /**
     * DataSource constructor.
     * @param $path
     */
    public function __construct($path)
    {
        if (!file_exists($path)) {
            throw new Exception("$path doesn't exist.");
        }
        $this->path = $path;

    }


    /**
     * Converts the file content into a SerialisedDBField
     *
     * @return SerialisedDBField|null
     */
    public function toDBObject()
    {
        $info = pathinfo($this->path);

        switch ($info['extension']) {
            case 'yaml':
            case 'yml':
                return DBField::create_field('YAMLField', file_get_contents($this->path));
            case 'json':
                return DBField::create_field('JSONField', file_get_contents($this->path));

            default:
                return null;
        }
    }


    /**
     * A simple name for the data source
     * 
     * @return string
     */
    public function getName()
    {
        return basename($this->path);
    }


    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}