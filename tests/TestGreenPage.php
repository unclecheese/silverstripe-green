<?php

class TestGreenPage extends Page implements TestOnly
{
	private static $extensions = [
		'GreenExtension'
	];
}

class TestGreenPage_Controller extends UncleCheese\Green\Controller implements TestOnly
{
	
}