<?php

/**
 * @file plugins/themes/immersion/ImmersionThemePlugin.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ImmersionThemePlugin
 * @ingroup plugins_themes_immersion
 *
 * @brief Immersion theme
 */

import('lib.pkp.classes.plugins.ThemePlugin');
class ImmersionThemePlugin extends ThemePlugin {
	
	public function init() {
		
		// Add navigation menu areas for this theme
		$this->addMenuArea(array('primary', 'user'));
		
		// Get extra data for templates
		HookRegistry::register ('Templates::Manager::Sections::SectionForm::AdditionalMetadata', array($this, 'sectionDataForm'));
	}
	
	/**
	 * Get the display name of this theme
	 * @return string
	 */
	public function getDisplayName() {
		return __('plugins.themes.immersion.name');
	}
	
	/**
	 * Get the description of this plugin
	 * @return string
	 */
	public function getDescription() {
		return __('plugins.themes.immersion.description');
	}
	
	public function sectionDataForm($hookName, $args) {
		$templateMgr = $args[1];
		
		/* @var $templateMgr TemplateManager */
		$templateMgr->display($this->getTemplatePath() . "/templates/sectionForm.tpl");
	}
	
	public function manage($args, $request) {
	
	}
}