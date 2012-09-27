<?
class Slide extends CommonSlide
{
	public $slideLayouts
	public function __construct()
	{
		$this->slideLayouts = array();
		parent->__construct();
	}
}
