<?php

use UncleCheese\Green\TemplateParser;
use UncleCheese\Green\Green;
use Symfony\Component\Filesystem\Filesystem;

class GreenTemplateParserTask extends BuildTask
{

	public function run($request)
	{
		$moduleName = $request->getVar('module');
		$format = $request->getVar('format') ?: 'yaml';
		$fs = new Filesystem();

		$green = Green::inst();

		if(!$moduleName) {
			throw new \RuntimeException("Please specify a design module.");
		}

		$module = $green->getDesignModule($moduleName);

		if(!$module) {
			throw new \RuntimeException("Module not found: $module");
		}

		$parser = TemplateParser::create();
		$templateFile = $module->getLayoutTemplateFile();

		$parser->parse($templateFile);
		$data = $parser->getResults($format);
		$filename = "content." . ($format === 'yaml' ? 'yml' : 'json');

		$fs->dumpFile(
			Controller::join_links($module->getPath(), $filename),
			$data
		);

		echo "Success! Loaded template contents into $filename\n";

	}
}