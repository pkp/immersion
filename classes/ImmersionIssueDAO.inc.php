<?php

import ('plugins.themes.immersion.classes.ImmersionIssue');
import ('classes.issue.IssueDAO');

class ImmersionIssueDAO extends IssueDAO {
	
	/**
	 * Construct a new data object.
	 * @return Issue
	 */
	function newDataObject() {
		return new ImmersionIssue();
	}
	
	/**
	 * Get the list of additional fields.
	 * @return array
	 */
	
	function getAdditionalFieldNames() {
		return array_merge(
			parent::getAdditionalFieldNames(),
			array('immersionSectionColor')
		);
	}
}