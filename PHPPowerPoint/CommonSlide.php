<?
class CommonSlide
{
	public $shapeTree;
	public $background;
	public function __construct()
	{
		$this->shapeTree = array();
	}
	public function addShape($shape)
	{
		$this->shapeTree[] = $shape;
	}
}
