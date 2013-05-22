<?php

/**
 *
 * @author	Frederick Ding
 * @version	$Id$
 * @package	Bts
 */
class Api_Bootstrap extends Zend_Application_Module_Bootstrap
{

	protected function _initHelpers ()
	{
		Zend_Controller_Action_HelperBroker::addPath(
				dirname(__FILE__) . '/controllers/helpers', 'Api_Action_Helper');
		Zend_Controller_Action_HelperBroker::getStaticHelper('formatResponse');
	}
}

