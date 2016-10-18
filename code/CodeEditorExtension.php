<?php

namespace UncleCheese\Green;

use \Director;
use \Controller;
use \SS_HTTPRequest;

class CodeEditorExtension extends \DataExtension
{
	private static $allowed_actions = [
		'parsegreentemplate'
	];

	public function parsegreentemplate(SS_HTTPRequest $r)
	{
		$page = Director::get_current_page();
		$controller = Controller::curr();
		$parser = TemplateParser::create();

		if(!$page || !$page->canEdit()) {
			return $controller->httpError(403, 'Cannot edit this page');
		}

		if(!$page->hasExtension('GreenExtension')) {
			return $controller->httpError(400, 'This page does not have the Green extension');
		}

		$module = $page->getModule();

		if(!$module) {
			return $controller->httpError(400, 'This page has no design module selected');
		}

		$dataType = strtolower(preg_replace(
			'/Field$/',
			'',
			$page->dbObject('TemplateData')->class
		));

		$parser->parse($module->getLayoutTemplateFile());
		$data = $parser->getResults($dataType);

		return (
			new SS_HTTPResponse(\Convert::array2json([
				'result' => $data
			]), 200)
		)
		->addHeader('Content-Type', 'application/json');

	}


	public function updateAttributes(&$attributes)
	{

		// Hack way to signal Green editor
		if($this->owner->getName() === 'TemplateData') {
			$button = sprintf(
				'<a class="template-parse-button" href="%s">%s</a>',
				$this->owner->Link('parsegreentemplate'),
				_t('Green.LOAD_TEMPLATE','Load from template')
			);
			$this->owner->setTitle(
				$this->owner->Title() . "({$button})"
			);
		}
	}
		
}