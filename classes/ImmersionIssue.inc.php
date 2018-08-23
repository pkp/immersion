<?php

import('classes.issue.Issue');

class ImmersionIssue extends Issue {
	/**
	 * get immersion color pick
	 * @return string
	 */
	function getImmersionSectionColor() {
		return $this->getData('immersionSectionColor');
	}
	
	/**
	 * set immersion color pick
	 * @param $immersionColorPick string
	 */
	function setImmersionSectionColor($immersionSectionColor) {
		return $this->setData('immersionSectionColor', $immersionSectionColor);
	}
}