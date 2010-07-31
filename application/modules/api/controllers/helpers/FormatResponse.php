<?php
class Api_Action_Helper_FormatResponse extends Zend_Controller_Action_Helper_Abstract
{
	/**
	 * The response type (JSON or XML); by default, uses JSON
	 * @var string
	 */
	private $responseType = 'json';
	private $contextSwitch = null;
	/**
	 * Upon load of this helper, customize the Context Switch helper for our needs.
	 */
	public function init ()
	{
		$this->contextSwitch = Zend_Controller_Action_HelperBroker::getStaticHelper('contextSwitch');
		$this->contextSwitch->setContexts(array(
			'json' => array(
				'suffix' => 'json' ,
				'headers' => array(
					'Content-Type' => 'application/json' ,
					'Cache-Control' => 'private,no-cache' ,
					'Expires' => 'Sat, 26 Jul 1997 05:00:00 GMT') ,
				'callbacks' => array(
					'init' => 'initJsonContext' ,
					'post' => array(
						$this ,
						'jsonContext'))) ,
			'xml' => array(
				'suffix' => 'xml' ,
				'headers' => array(
					'Content-Type' => 'application/xml' ,
					'Cache-Control' => 'private,no-cache' ,
					'Expires' => 'Sat, 26 Jul 1997 05:00:00 GMT') ,
				'callbacks' => array(
					'post' => array(
						$this ,
						'xmlContext')))));
	}
	/**
	 * Upon predispatch, determines whether we will be sending in XML or
	 * JSON format.
	 */
	public function preDispatch ()
	{
		if (! is_null($this->contextSwitch->getCurrentContext())) {
			$this->responseType = $this->contextSwitch->getCurrentContext();
		} else {
			$requestedFormat = $this->getRequest()->getParam('format');
			switch ($requestedFormat) {
				case 'xml':
					$this->responseType = 'xml';
					break;
				case 'json':
				default:
					$this->responseType = 'json';
			}
			$headers = $this->contextSwitch->getHeaders($this->responseType);
			foreach ($headers as $key => $val) {
				$this->getResponse()->setHeader($key, $val, true);
			}
		}
	}
	/**
	 * Takes a PHP array and converts it to an XML document in a <response>
	 * root element.
	 * @param array $data
	 */
	public function arrayToXml (array $data)
	{
		$dom = new Api_Action_Helper_FormatResponse_XML();
		$dom->fromPHP($data);
		$dom->normalizeDocument();
		return $dom->saveXML();
	}
	/**
	 * Converts a PHP array into a JSON string.
	 * @param array $data
	 */
	public function arrayToJson (array $data)
	{
		return Zend_Json::prettyPrint(Zend_Json::encode($data));
	}
	/**
	 * Allows invocation in controller as $this->_helper->formatResponse($data);
	 * sends the appropriate response using the given data.
	 * @param array $data
	 */
	public function direct (array $data)
	{
		if ($this->responseType == 'xml') {
			$response = $this->arrayToXml($data);
		} else {
			$response = $this->arrayToJson($data);
		}
		$this->getResponse()->setBody($response);
	}
	public function xmlContext ()
	{
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		$view = $viewRenderer->view;
		if ($view instanceof Zend_View_Interface) {
			$vars = $this->arrayToXml($view->getVars());
			$this->getResponse()->setBody($vars);
		}
	}
	public function jsonContext ()
	{
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		$view = $viewRenderer->view;
		if ($view instanceof Zend_View_Interface) {
			$vars = $this->arrayToJson($view->response);
			$this->getResponse()->setBody($vars);
		}
	}
}
/**
 * Lightly adapted DOMDocument subclass which builds an XML document from a
 * PHP array. Used with permission by author.
 *
 * @author Toni Van de Voorde
 * @license Apache License 2.0
 */
class Api_Action_Helper_FormatResponse_XML extends DOMDocument
{
	/**
	 * Recursively builds a DOMDocument from PHP source data.
	 * @param mixed $data
	 * @param DOMElement $domElement
	 */
	public function fromPHP ($data, DOMElement $domElement = null)
	{
		$domElement = is_null($domElement) ? $this : $domElement;
		if (is_array($data)) {
			foreach ($data as $index => $element) {
				if (is_int($index)) {
					if ($index == 0) {
						$node = $domElement;
					} else {
						$node = $this->createElement($domElement->tagName);
						$domElement->parentNode->appendChild($node);
					}
				} else {
					$node = $this->createElement($index);
					$domElement->appendChild($node);
				}
				$this->fromPHP($element, $node);
			}
		} else {
			$domElement->appendChild($this->createTextNode($data));
		}
	}
}