<?
require_once 'SmartDOMDocument.php';
class PresentationML2HTML
{
	private static $methods;
	private static $xpath;
	private static $htmlxpath;
	private static $html;
	public static function TextBody2HTML($textBody)
	{
		if(empty(self::$methods))
		{
			self::$methods = get_class_methods('PresentationML2HTML');
		}
		$textBodyDom = new DOMDocument();
		$textBodyDom->loadXML($textBody);
		self::$xpath = new DOMXpath($textBodyDom);
		self::$html = new SmartDOMDocument(); 
		self::$htmlxpath = new DOMXpath(self::$html);
		self::Traverse($textBodyDom->documentElement);
		self::$xpath = null;
		return self::$html->saveHTMLExact();
	}
	private static function Traverse($element)
	{
		$name = isset($element->tagName)?$element->tagName:$element->nodeName;
		$method_name = str_replace(':',"_",$name);
		if(in_array($method_name,self::$methods))	
		{
			call_user_func_array(array('PresentationML2HTML',$method_name),array($element));
		}
		if(!empty($element->nextSibling))
		{
			self::Traverse($element->nextSibling);
		}
	}
	private static function p_sld($element)
	{
		$node_list = self::$xpath->query("//p:txBody",$element);
		foreach($node_list as $node)
		{
			self::p_txBody($node);
		}
	}
	private static function p_txBody($element)
	{
		self::Traverse($element->firstChild);
	}
	private static function a_p($element)
	{
		$paragraph = new DOMElement('p');
		self::$html->appendChild($paragraph);
		self::Traverse($element->firstChild);
	}
	private static function a_pPr($element)
	{
		$html_node_list = self::$htmlxpath->query('/p[last()]',self::$html);
		$paragraph = $html_node_list->item(0);
		$style = "";
		foreach($element->attributes as $attribute)
		{
			switch($attribute->name)
			{
				case "marL":
					$style .= "margin-left: ".(double)($attribute->value/12700)." pt; ";
				break;
				case "marR":
					$style .= "margin-right: ".(double)($attribute->value/12700)." pt; ";
				case "algn":
					switch($attribute->value)
					{
						case "ctr":
							$align = "center";
						break;
						case "r":
							$align = "right";
						break;
						case "just":
							$align = "justify";
						break;
						default:
							$align = "left";
					}
					$style .= "text-align: ".$align."; ";
				break;
			}
		}
		//TODO handle Lists.
		self::Traverse($element->firstChild);
	}
	private static function a_endParaRPr($element)
	{
		self::a_r($element);
		self::a_rPr($element);
	}
	private static function a_r($element)
	{
		$html_node_list = self::$htmlxpath->query('/p[last()]',self::$html);
		$paragraph = $html_node_list->item(0);
		$span = new DOMElement('span');
		$paragraph->appendChild($span);
		self::Traverse($element->firstChild);
	}
	private static function a_br($element)
	{
		$html_node_list = self::$htmlxpath->query('/p[last()]',self::$html);
		$paragraph = $html_node_list->item(0);
		$paragraph->appendChild(new DOMElement('br'));
	}
	private static function a_rPr($element)
	{
		$html_node_list = self::$htmlxpath->query('/p[last()]/span[last()]',self::$html);
		$span = $html_node_list->item(0);
		$style = "";
		foreach($element->attributes as $attribute)
		{
			switch($attribute->name)
			{
				case "b":
					$style .= $attribute->value?"font-weight: bold; ":"";
				break;
				case "i":
					$style .= $attribute->value?"font-style: italic; ":"";
				break;
				case "u":
					$style .= $attribute->value?"text-decoration: underline; ":"";
				break;
				case "strike":
					$style .= $attribute->value?"text-decoration: line-through; ":"";
				break;
				case "sz":
					$style .= "font-size: ".(double)($attribute->value/100)." pt; ";
				break;
				
			}
		}
		$span->appendChild(new DomAttr('style',$style));
		self::Traverse($element->firstChild);
	}
	private static function a_solidFill($element)
	{
		switch($element->parentNode->nodeName)
		{
			case 'a:rPr':
				$html_node_list = self::$htmlxpath->query('/p[last()]/span[last()]',self::$html);
				$span = $html_node_list->item(0);
				foreach($element->childNodes as $child_node)
				{
					$style = "color: #".$child_node->attributes->getNamedItem('val')->nodeValue." ";
				}
				$span->appendChild(new DomAttr('style',$style));
			break;
		}
	}
	private static function a_t($element)
	{
		$html_node_list = self::$htmlxpath->query('/p[last()]/span[last()]',self::$html);
		$span = $html_node_list->item(0);
		$span->appendChild(new DOMText($element->textContent));
	}
}
