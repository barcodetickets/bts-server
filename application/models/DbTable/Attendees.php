<?php

/**
 * BTS attendees table
 *
 * @author	Frederick Ding
 * @version $Id$
 * @package	Bts
 */
class Bts_Model_DbTable_Attendees extends Zend_Db_Table_Abstract
{

	/**
	 * The default table name
	 */
	protected $_name = 'bts_attendees';

	public function __construct ()
	{
		parent::__construct(array(
				'db' => 'db'
		));
	}
}

