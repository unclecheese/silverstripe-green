<?php

namespace UncleCheese\Green;

use Symfony\Component\Yaml\Yaml;
use \ReflectionTemplate;
use \ViewableData;
use \Injector;
use \i18n;

class TemplateParser extends \Object
{

	protected $data = [];

	protected $parser;

	protected $faker;

	public function __construct($parser)
	{
		$this->parser = $parser;
		$this->faker = \Faker\Factory::create(i18n::get_locale());

		parent::__construct();
	}

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

	public function getResults($format = 'yaml')
	{
		$format = strtolower($format);
		if($format === 'yaml') {
			return Yaml::dump($this->data, 4);
		}

		if($format === 'json') {
			return json_encode($data, JSON_PRETTY_PRINT);
		}

		throw new \Exception("Invalid data format: $format");
	}

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