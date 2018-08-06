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
		
		// Register new DAO class for Sections
		$this->import('classes.ImmersionSectionDAO');
		$immersionSectionDao = new ImmersionSectionDAO();
		DAORegistry::registerDAO('ImmersionSectionDAO', $immersionSectionDao);
		
		$this->import('classes.ImmersionPublishedArticleDAO');
		$immersionPublishedArticleDAO = new ImmersionPublishedArticleDAO();
		DAORegistry::registerDAO('ImmersionPublishedArticleDAO', $immersionPublishedArticleDAO);
		
		// Additional data to the templates
		HookRegistry::register ('TemplateManager::display', array($this, 'addIssueTemplateData'));
		
		// Initiate new Grid Handler for SectionForm
		HookRegistry::register('LoadComponentHandler', array($this, 'setupGridHandler'));
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
	
	// Add data to the templates
	
	public function addIssueTemplateData($hookname, $args) {
		
		/* @var $request Request
		 * @var $context Context
		 * @var $templateMgr TemplateManager
		 * @var $publishedArticleDao ImmersionPublishedArticleDAO
		 */
		
		
		$templateMgr = $args[0];
		$template = $args[1];
		
		if ($template !== 'frontend/pages/issue.tpl' && $template !== 'frontend/pages/indexJournal.tpl') return false;
		$request = $this->getRequest();
		$journal = $request->getJournal();
		$issueDao = DAORegistry::getDAO('IssueDAO');
		$issue = $issueDao->getCurrent($journal->getId(), true);
		$publishedArticleDao = DAORegistry::getDAO('ImmersionPublishedArticleDAO');
		
		$templateMgr->assign('publishedArticlesBySections', $publishedArticleDao->getImmersionPublishedArticlesInSections($issue->getId(), true));
		
		
	}
	
	// Allow requests for ImmersionSectionHandler
	
	function setupGridHandler($hookName, $args) {
		$component =& $args[0];
		if ($component == 'plugins.themes.immersion.controllers.grid.ImmersionSectionGridHandler') {
			define('IMMERSION_PLUGIN_NAME', $this->getName());
			return true;
		}
		return false;
	}
	
}