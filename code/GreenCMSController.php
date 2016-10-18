<?php

namespace UncleCheese\Green;

use \SS_HTTPRequest;
use \Controller;
use \Director;
use \Permission;
use \CMSMain;

class GreenCMSController extends CMSMain
{

	private static $url_segment = 'green';	

	private static $required_permission_codes = 'CMS_ACCESS_CMSMain';

	public function index(SS_HTTPRequest $r)
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
}