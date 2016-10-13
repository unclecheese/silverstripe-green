<?php

use Symfony\Component\Filesystem\Filesystem;

use UncleCheese\Green\Green;
use UncleCheese\Green\DesignModule;
use UncleCheese\Green\DataSource;

class GreenTest extends SapphireTest
{
	protected $fs;

	protected $extraDataObjects = [
		'TestGreenPage'
	];

	private static $sample_files = [
		'config.yml',
		'content.yml',
		'index.ss',
		'sample.css',
		'another-sample.js',
		'someimage.jpeg'
	];

	public function setUp()
	{
		$this->fs = new Filesystem();
		foreach(self::$sample_files as $file) {
			$this->fs->copy(
				$this->getAssetPath($file),
				$this->getFileTestPath($file)
			);
		}

		Config::inst()->update(
			'UncleCheese\Green\Green',
			'design_folder',
			GREEN_DIR.'/tests/green-test'
		);
	}

	public function tearDown()
	{
		$this->fs->remove(
			$this->getFileTestPath()
		);
	}

	public function testDataSourceDBObject()
	{
		$dataSource = $this->createDataSource();
		$dbObj = $dataSource->toDBObject();
		$this->assertEquals('test title', $dbObj->Title);
		$this->assertTrue($dbObj->obj('Date') instanceof Date);
	}


	public function testDataSourceImages()
	{
		$dataSource = $this->createDataSource();
		$dataObj = $dataSource->toDBObject();
		$this->assertTrue(
			$dataObj->obj('SomeImage') instanceof Image_Cached
		);
	}

	public function testDataSourceGetters()
	{
		$dataSource = $this->createDataSource();
		$this->assertEquals('content.yml', $dataSource->getName());
		$this->assertEquals(__DIR__.'/green-test/test-design/content.yml', $dataSource->getPath());
	}


	public function testDesignModuleGetters()
	{
		$designModule = $this->createDesignModule();
		$this->assertEquals(
			$this->getFileTestPath(),
			$designModule->getPath()
		);
		$this->assertEquals(
			GREEN_DIR.'/tests/green-test/test-design',
			$designModule->getRelativePath()
		);

		$this->assertEquals(
			'test-design',
			$designModule->getName()
		);

		$this->assertEquals(
			$this->getFileTestPath('index.ss'),
			$designModule->getLayoutTemplateFile()
		);

		$this->assertEquals(
			file_get_contents($this->getFileTestPath('index.ss')),
			$designModule->getTemplateContents()
		);

		$this->fs->dumpFile(
			$this->getFileTestPath('main.ss'),
			'test'
		);

		$this->assertEquals(
			$this->getFileTestPath('main.ss'),
			$designModule->getMainTemplateFile()
		);

		$this->fs->remove(
			$this->getFileTestPath('main.ss')
		);

		$this->fs->remove(
			$this->getFileTestPath('index.ss')
		);

		$this->fs->dumpFile(
			$this->getFileTestPath('layout.ss'),
			'test'
		);

		$this->assertEquals(
			$this->getFileTestPath('layout.ss'),
			$designModule->getLayoutTemplateFile()
		);
	}

	public function testDesignModuleCreatesDataSource()
	{
		$designModule = $this->createDesignModule();
		$dataSource = $designModule->getDataSource();

		$this->assertTrue($dataSource instanceof DataSource);

		$this->fs->remove($this->getFileTestPath('content.yml'));

		$this->fs->touch($this->getFileTestPath('content.json'));

		$this->assertTrue($dataSource instanceof DataSource);
	}

	public function testDesignModuleReturnsFalseOnNoDataSource()
	{
		$designModule = $this->createDesignModule();
		$this->fs->remove($this->getFileTestPath('content.yml'));

		$this->assertFalse($designModule->getDataSource());
	}

	public function testDesignModuleGetsConfiguration()
	{
		$designModule = $this->createDesignModule();
		$config = $designModule->getConfiguration();
		$this->assertTrue($config instanceof YAMLField);

		$this->assertEquals('bar', (string) $config->foo);
	}

	public function testDesignModuleReturnsArrayDataOnNoConfiguration()
	{
		$designModule = $this->createDesignModule();
		$this->fs->remove($this->getFileTestPath('config.yml'));
		
		$config = $designModule->getConfiguration();
		$this->assertTrue($config instanceof ArrayData);
	}

	public function testDesignModuleAssets()
	{
		$designModule = $this->createDesignModule();
		$this->assertCount(1, $designModule->getStylesheets());
		$this->assertEquals(
			$this->getRelativeFileTestPath('sample.css'),
			$designModule->getStylesheets()[0]
		);

		$this->assertCount(1, $designModule->getJavascripts());
		$this->assertEquals(
			$this->getRelativeFileTestPath('another-sample.js'),
			$designModule->getJavascripts()[0]
		);

		$this->assertCount(1, $designModule->getImages());
		$this->assertEquals(
			$this->getRelativeFileTestPath('someimage.jpeg'),
			$designModule->getImages()[0]
		);

	}

	public function testDesignModuleRequirements()
	{
		$mock = $this->getMockBuilder('Requirements_Backend')
					->setMethods(['javascript','css'])
					->getMock();
		$mock->expects($this->once())
			->method('css')
			->with($this->getRelativeFileTestPath('sample.css'));

		$mock->expects($this->once())
			->method('javascript')
			->with($this->getRelativeFileTestPath('another-sample.js'));

		Requirements::set_backend($mock);

		$this->createDesignModule()->loadRequirements();
	}


	public function testGreenGetters()
	{
		$green = Green::inst();

		$this->assertEquals(
			GREEN_DIR.'/tests/green-test',
			$green->getDesignFolder()
		);

		$this->assertEquals(
			BASE_PATH.'/'.GREEN_DIR.'/tests/green-test',
			$green->getAbsoluteDesignFolder()
		);

	}

	public function testGreenGetsDesignModules()
	{
		$green = Green::inst();
		$modules = $green->getDesignModules();
		$this->assertCount(1, $modules);
		$this->assertTrue($modules[0] instanceof DesignModule);

		$this->fs->mkdir(__DIR__.'/green-test/dummy');

		$modules = $green->getDesignModules();
		$this->assertCount(1, $modules);

		$module = $green->getDesignModule('test-design');
		$this->assertTrue($module instanceof DesignModule);

		$this->assertFalse($green->getDesignModule('dummy'));
	}

	public function testGetCMSFieldsAddsCodeEditorOnlyWhenNeeded()
	{
		$page = TestGreenPage::create([
			'DesignModule' => 'test-design'
		]);

		$page->write();
		$fields = $page->getCMSFields();

		$this->assertNull($fields->dataFieldByName('TemplateData'));

		$this->fs->remove($this->getFileTestPath('content.yml'));

		$fields = $page->getCMSFields();

		$editor = Injector::inst()->get('YAMLEditor');
		$this->assertTrue(
			$fields->dataFieldByName('TemplateData') instanceof $editor->class
		);

	}

	public function testGreenPageRenderingFromCMSWithFile()
	{
		$page = TestGreenPage::create([
			'DesignModule' => 'test-design',
			'URLSegment' => 'tester',
			'ParentID' => 0
		]);

		$page->write();
		$page->publish("Stage", "Live");

		$response = Director::test('tester');
		$body = $response->getBody();
		
		$this->assertRegExp(
			'/Title\: test title/',
			$body
		);

		$this->assertRegExp(
			'/Date\: 01\/01\/2015/',
			$body
		);


	}

	public function testGreenPageRenderingFromCMSWithInlineData()
	{
		$page = TestGreenPage::create([
			'DesignModule' => 'test-design',
			'URLSegment' => 'tester-2',
			'ParentID' => 0
		]);

		$this->fs->remove(
			$this->getFileTestPath('content.yml')
		);

		$page->TemplateData = <<<YAML
Title: title changed
Date: Date|2015-02-02
YAML;
		$page->write();
		$page->publish("Stage", "Live");

		$response = Director::test('tester-2');
		$body = $response->getBody();
		
		$this->assertRegExp(
			'/Title\: title changed/',
			$body
		);

		$this->assertRegExp(
			'/Date\: 02\/02\/2015/',
			$body
		);

	}

	public function testGreenPageRenderingFromPublicURL()
	{
		$response = Director::test('test-url');
		$this->assertEquals(404, $response->getStatusCode());

		$this->fs->dumpFile(
			$this->getFileTestPath('config.yml'),
			'public_url: test-url'
		);

		Green::install();

		$response = Director::test('test-url');
		$this->assertEquals(200, $response->getStatusCode());

		$body = $response->getBody();		
		$this->assertRegExp(
			'/Title\: test title/',
			$body
		);

		$this->assertRegExp(
			'/Date\: 01\/01\/2015/',
			$body
		);

		$this->fs->dumpFile(
			$this->getFileTestPath('main.ss'),
			"___MAIN___\n\$Layout"
		);

		$response = Director::test('test-url');
		$this->assertEquals(200, $response->getStatusCode());

		$body = $response->getBody();		

		$this->assertEquals(
			'___MAIN___',
			substr($body, 0, 10)
		);

		$this->assertRegExp(
			'/Title\: test title/',
			$body
		);

		$this->assertRegExp(
			'/Date\: 01\/01\/2015/',
			$body
		);

	}

	protected function getFileTestPath($file = null)
	{
		return __DIR__.'/green-test/test-design' . ($file ? "/$file" : '');
	}

	protected function getRelativeFileTestPath($file = null)
	{
		return GREEN_DIR.'/tests/green-test/test-design' . ($file ? "/$file" : '');
	}

	protected function getAssetPath($file = null)
	{
		return __DIR__ . '/_assets/' . ($file ? "/$file" : '');
	}

	protected function createDesignModule()
	{
		return new DesignModule(__DIR__.'/green-test/test-design');
	}

	protected function createDataSource()
	{
		return new DataSource(__DIR__.'/green-test/test-design/content.yml');
	}

}