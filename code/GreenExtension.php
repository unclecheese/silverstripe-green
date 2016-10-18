<?php

use UncleCheese\Green\Green;

/**
 * Class GreenExtension
 * Add this extension to any DataObject to allow it to map to a design module
 *
 * @package UncleCheese\Green;
 * @author UncleCheese <unclecheese@leftandmain.com>
 */
class GreenExtension extends DataExtension
{


    /**
     * GreenExtension constructor.
     */
    public function __construct()
    {
        $dir = Green::inst()->getDesignFolder();
        if (!$dir) {
            throw new Exception("You must set a design_folder path in the config (Green.design_folder)");
        }
        if (!is_dir(Director::baseFolder() . '/' . $dir)) {
            throw new Exception("Green.design_folder $dir does not exist");
        }

        parent::__construct();
    }


    /**
     * @param $class
     * @param $extension
     * @param $args
     * @return array
     */
    public static function get_extra_config($class, $extension, $args)
    {
        $dataType = isset($args[0]) ? $args[0] : null;

        if (!$dataType) {
            $dataType = Green::default_data_type();
        }

        return [
            'db' => [
                'DesignModule' => 'Varchar',
                'TemplateData' => "{$dataType}Field"
            ],
            'casting' => [
                'Design' => 'HTMLText'
            ]
        ];
    }


    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $dataField = $this->owner->db('TemplateData');
        $dataType = preg_replace('/Field$/', '', $dataField);
        $tab = $fields->findOrMakeTab("Root.Content & Design");
        $module = $this->getModule();

        $allModules = array_map(function ($module) {
            return $module->getName();
        }, Green::inst()->getDesignModules());

        $tab->push(
            DropdownField::create(
                'DesignModule',
                'Design module',
                ArrayLib::valuekey($allModules)
            )
                ->setEmptyString('--- Please select ---')
        );

        if (!$module) {
            return;
        }

        if ($source = $module->getDataSource()) {
            $tab->push(
                HeaderField::create(
                    "This module has its own data source file. ({$source->getName()})",
                    4
                )
            );
        } else {
        	$button = "";
        	if(!$this->owner->TemplateData) {
				$button = "(".sprintf(
					'<a class="template-parse-button" href="admin/green?id=%s&class=%s">%s</a>',
					$this->owner->ID,
					$this->owner->class,
					_t('Green.LOAD_TEMPLATE','Load from template')
				).")";
			}

            $tab->push(
                Injector::inst()->create("{$dataType}Editor")
                    ->setName('TemplateData')
                    ->setTitle("This module does not have its own data source file. You can create <strong>$dataType</strong> data for the design below. $button")
                    ->setRows(30)
            );
        }

        $c = $fields->dataFieldByName('TemplateData');
    }


    /**
     * Renders the design module
     *
     * @return HTMLText
     */
    public function DesignModule()
    {
        $module = $this->getModule();

        if (!$module) {
            return;
        }

        $module->loadRequirements();

        $viewer = SSViewer::fromString($module->getTemplateContents());

        return $viewer->process($this->toViewableData());
    }


    /**
     * Converts the module into {@link ViewableData} using its data source
     * @return ViewableData
     */
    public function toViewableData()
    {
        $module = $this->getModule();

        if ($source = $module->getDataSource()) {
            return $source->toDBObject();
        }
        
        return $this->owner->dbObject('TemplateData');
    }


    /**
     * Gets the design module that the DataObject is mapped to
     *
     * @return DesignModule|null
     */
    public function getModule()
    {
        if ($this->owner->DesignModule) {
            return Green::inst()->getDesignModule($this->owner->DesignModule);
        }

        return null;
    }
}