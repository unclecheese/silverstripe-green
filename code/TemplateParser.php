<?php

namespace UncleCheese\Green;

use Symfony\Component\Yaml\Yaml;
use \ReflectionTemplate;
use \ViewableData;
use \Injector;
use \i18n;

/**
 * Parses a template using ReflectionTemplate and returns mock data
 * in serialised JSON or YAML
 *
 * @package  UncleCheese\Green
 * @author  Uncle Cheese <unclecheese@leftandmain.com>
 */
class TemplateParser extends \Object
{

	/**
	 * The template data
	 * @var array
	 */
	protected $data = [];

	/**	 
	 * @var ReflectionTemplate
	 */
	protected $parser;

	/**
	 * Creates the mock data
	 * @var Faker
	 */
	protected $faker;

	/**
	 * Constructor
	 * @param RelctionTemplate $parser
	 */
	public function __construct($parser)
	{
		$this->parser = $parser;
		$this->faker = \Faker\Factory::create(i18n::get_locale());

		parent::__construct();
	}

	/**
	 * Parse the template
	 * @param  string $path Path to the template
	 */
	public function parse($path)
	{
		$data = [];

		$this->parser->process(file_get_contents($path));	
		
		$this->processVars(
			$this->parser->getTopLevelVars(),
			$data
		);
		
		$this->processBlocks(
			$this->parser->getTopLevelBlocks(), 
			$data
		);

		$this->data = $data;		
	}

	/**
	 * Gets the results in JSON or YAML format
	 * @param  string $format 
	 * @return string
	 */
	public function getResults($format = 'yaml')
	{
		$format = strtolower($format);
		if($format === 'yaml') {
			return Yaml::dump($this->data, 4, 2);
		}

		if($format === 'json') {
			return json_encode($data, JSON_PRETTY_PRINT);
		}

		throw new \Exception("Invalid data format: $format");
	}

	/**
	 * Processes template vars
	 * @param  array $vars 
	 * @param  The total collection of template data &$data
	 */
	protected function processVars($vars, &$data)
	{	
		$defaultDataType = ViewableData::config()->default_cast;

		foreach((array) $vars as $varName => $type) {
			$key = ($type === $defaultDataType) ? '' : "{$type}|";
			$sng = Injector::inst()->get($type);
			$value = '';
			if($sng->hasMethod('getFakeData')) {
				$value = $sng->getFakeData($this->faker);	
			}

			$data[$varName] = "{$key}$value";
		}		
	}

	/**
	 * Processes template blocks
	 * @param  array $blocks 
	 * @param  The total collection of template data &$data
	 */
	protected function processBlocks($blocks, &$data)
	{
		foreach($blocks as $block) {						
			$data[$block->getName()] = [];
			if($block->isLoop()) {
				// force the array to be enumerated
				$data[$block->getName()][] = [];				
				$this->processVars($block->getVars(), $data[$block->getName()][0]);
			}
			else {
				// force the array to be associative
				$this->processVars($block->getVars(), $data[$block->getName()]);	
			}
			
			$children = $block->getChildren();
			if(!empty($children)) {
				$this->processBlocks($children, $data[$block->getName()]);
			}
		}
	}
}