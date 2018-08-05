<?php

/**
 * @file plugins/generic/themes/classes/ImmersionSectionDAO.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ImmersionSectionDAO
 * @ingroup journal
 *
 * @brief Operations for retrieving and modifying Section objects.
 */

import ('classes.journal.SectionDAO');
import('plugins.themes.immersion.classes.ImmersionSection');

class ImmersionSectionDAO extends SectionDAO {
	
	/**
	 * Get the list of fields for which data can be localized.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array_merge(
			parent::getLocaleFieldNames(),
			array('coverImageAltText', 'coverImage')
		);
	}
	
	/**
	 * Return a new data object.
	 */
	function newDataObject() {
		return new ImmersionSection();
	}
}