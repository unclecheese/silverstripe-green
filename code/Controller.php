<?php

namespace UncleCheese\Green;

use \ContentController;
use \Page;

/**
 * A controller used for rendering Green page types, either through
 * direct URL access, or from the database.
 * 
 */
class Controller extends ContentController
{

	/**
	 * Finds the DesignModule object, whether through the attached
	 * data record or through the public_url match
	 * 
	 * @return DesignModule|bool
	 */
	protected function findModule()
	{
		if($this->isGreenPage()) {
			$moduleName = $this->DesignModule;
			if($moduleName) {
				return Green::inst()->getDesignModule($moduleName);
			}
		}

		$url = $this->request->getURL();
		foreach(Green::inst()->getDesignModules() as $module) {
			if((string) $module->getConfiguration()->public_url == $url) {
				return $module;
			}
		}

		return false;
	}


	/**
	 * Intercepts the handleAction method to force a customised viewer
	 * 
	 * @param  SS_HTTPRequest $request
	 * @param  string $action
	 * @return string
	 */
	protected function handleAction($request, $action)
	{
		$module = $this->findModule();
		
		if(!$module) {
			return parent::handleAction($request, $action);
		}
		
		$data = [];
		
		if($this->isGreenPage()) {
			$data = $this->data()->toViewableData();
		}
		elseif($module->getDataSource()) {
			$data = $module->getDataSource()->toDBObject();
		}

		$module->loadRequirements();
		$viewer = $this->getViewer($action);					
		$viewer->setTemplateFile('Layout', $module->getLayoutTemplateFile());

		$main = $module->getMainTemplateFile();
		if($main) {
			$viewer->setTemplateFile('main', $main);
		}
		
		return $viewer->process($this->customise($data));
	}


	/**
	 * Returns true if this controller is bound to a "real" page,
	 * as opposed to a dummy page used for direct access
	 * @return boolean
	 */
	protected function isGreenPage()
	{
		return $this->ID && $this->data()->hasExtension('GreenExtension');
	}

}