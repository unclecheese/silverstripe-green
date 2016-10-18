<?php

namespace UncleCheese\Green;

use \SS_HTTPRequest;
use \SS_HTTPResponse;
use \Controller;
use \Permission;
use \CMSMain;
use \Member;
use \DataList;

/**
 * A controller that stores endpoints needed for UI features in the CMS
 *
 * @package  silverstripe-green
 * @author  Uncle Cheese <unclecheese@leftandmain.com>
 */
class GreenCMSController extends CMSMain
{

	private static $url_segment = 'green';	

	private static $required_permission_codes = 'CMS_ACCESS_CMSMain';

	public function index($r)
	{
		if(!$r->getVar('class') || !$r->getVar('id')) {
			return $this->httpError(400);
		}

		$obj = DataList::create(
			$r->getVar('class'),
			$r->getVar('id')
		)->first();
			
		$controller = Controller::curr();
		$parser = TemplateParser::create();

		if(!$obj || !$obj->canEdit(Member::currentUser())) {
			return $controller->httpError(403, 'Cannot edit this page');
		}

		if(!$obj->hasExtension('GreenExtension')) {
			return $controller->httpError(400, 'This page does not have the Green extension');
		}

		$module = $obj->getModule();

		if(!$module) {
			return $controller->httpError(400, 'This page has no design module selected');
		}

		$dataType = strtolower(preg_replace(
			'/Field$/',
			'',
			$obj->dbObject('TemplateData')->class
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