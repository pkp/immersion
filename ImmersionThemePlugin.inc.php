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
		
		$this->addStyle(
			'fonts',
			'https://fonts.googleapis.com/css?family=Roboto:300,400,400i,700,700i|Spectral:400,400i,700,700i',
			array('baseUrl' => ''));
		
		// Add navigation menu areas for this theme
		$this->addMenuArea(array('primary', 'user'));
		
		// Register new DAO class for Sections and Issues
		$this->import('classes.ImmersionIssueDAO');
		$immersionIssueDao = new ImmersionIssueDAO();
		DAORegistry::registerDAO('ImmersionIssueDAO', $immersionIssueDao);
		
		$this->import('classes.ImmersionSectionDAO');
		$immersionSectionDao = new ImmersionSectionDAO();
		DAORegistry::registerDAO('ImmersionSectionDAO', $immersionSectionDao);
		
		// Additional data to the templates
		HookRegistry::register ('TemplateManager::display', array($this, 'addIssueTemplateData'));
		HookRegistry::register ('TemplateManager::display', array($this, 'addSiteWideData'));
		HookRegistry::register ('issueform::display', array($this, 'addToIssueForm'));
		
		// Initiate new Grid Handler for SectionForm
		HookRegistry::register('LoadComponentHandler', array($this, 'setupGridHandler'));
		HookRegistry::register('issuedao::getAdditionalFieldNames', array($this, 'addIssueDAOFieldNames'));
		HookRegistry::register('issueform::initdata', array($this, 'initDataIssueFormFields'));
		HookRegistry::register('issueform::readuservars', array($this, 'readIssueFormFields'));
		HookRegistry::register('issueform::execute', array($this, 'executeIssueFormFields'));
		
		// TODO styles and scripts should be compiled, concatenated and minified before the release
		// Adding styles
		$this->addStyle('jquery-ui', 'node_modules/jquery-ui-dist/jquery-ui.min.css');
		$this->addStyle('bootstrap', 'node_modules/bootstrap/dist/css/bootstrap.min.css');
		$this->addStyle('tag-it', 'node_modules/tag-it/css/jquery.tagit.css');
		$this->addStyle('less', 'resources/less/import.less');
		
		// Adding scripts
		$this->addScript('jquery', 'node_modules/jquery/dist/jquery.min.js');
		$this->addScript('popper', 'node_modules/popper.js/dist/umd/popper.min.js');
		$this->addScript('bootstrap', 'node_modules/bootstrap/dist/js/bootstrap.min.js');
		$this->addScript('jquery-ui', 'node_modules/jquery-ui-dist/jquery-ui.min.js');
		$this->addScript('tag-it', 'resources/js/tag-it.min.js');
		$this->addScript('main', 'resources/js/main.js');
	}
	
	/**
	 * @copydoc PKPPlugin::getTemplatePath
	 */
	public function getTemplatePath($inCore = false) {
		return $this->getTemplateResourceName() . ':templates/';
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
		 * @var $issueDao IssueDAO
		 * @var $issue Issue
		 * @var $publishedArticleDao ImmersionPublishedArticleDAO
		 * @var $sectionDao ImmersionSectionDAO
		 * @var $sections array
		 * @var $section ImmersionSection
		 */
		
		$templateMgr = $args[0];
		$template = $args[1];
		$request = $this->getRequest();
		
		if ($template !== 'frontend/pages/issue.tpl' && $template !== 'frontend/pages/indexJournal.tpl') return false;
		
		
		$journal = $request->getJournal();
		
		$issueDao = DAORegistry::getDAO('IssueDAO');
		
		if ($template === 'frontend/pages/indexJournal.tpl') {
			$issue = $issueDao->getCurrent($journal->getId(), true);
		} else {
			$issue = $templateMgr->get_template_vars('issue');
		}
		
		$publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
		$publishedArticlesBySections = $publishedArticleDao->getPublishedArticlesInSections($issue->getId(), true);
		
		$immersionSectionColors = $issue->getData('immersionSectionColor');
		
		$sectionDao = DAORegistry::getDAO('ImmersionSectionDAO');
		$sections = $sectionDao->getByIssueId($issue->getId());
		
		import('classes.file.PublicFileManager');
		$publicFileManager = new PublicFileManager();
		$sectionCoverBasePath = $request->getBaseUrl() . '/' . $publicFileManager->getJournalFilesPath($journal->getId()) . '/';
		
		$lastSectionColor = null;
		foreach ($publishedArticlesBySections as $sectionId => $publishedArticlesBySection) {
			foreach ($sections as $section) {
				if ($section->getId() == $sectionId) {
					$publishedArticlesBySections[$sectionId]['section'] = $section;
					$publishedArticlesBySections[$sectionId]['sectionColor'] = $immersionSectionColors[$sectionId];
					
					// Need only the color of the last section that contains articles
					if ($publishedArticlesBySections[$sectionId]['articles'] && $immersionSectionColors[$sectionId]) {
						$lastSectionColor = $immersionSectionColors[$sectionId];
					}
				}
			}
		}
		
		$templateMgr->assign(array(
			'publishedArticlesBySections' => $publishedArticlesBySections,
			'sectionCoverBasePath' => $sectionCoverBasePath,
			'lastSectionColor' => $lastSectionColor,
		));
		
		return false;
	}
	
	public function addSiteWideData($hookname, $args) {
		$templateMgr = $args[0];
		
		$request = $this->getRequest();
		$journal = $request->getJournal();
		
		// Load login form
		$loginUrl = $request->url(null, 'login', 'signIn');
		if (Config::getVar('security', 'force_login_ssl')) {
			$loginUrl = PKPString::regexp_replace('/^http:/', 'https:', $loginUrl);
		}
		
		$orcidImageUrl = $this->getPluginPath() . '/templates/images/orcid.png';
		
		$templateMgr->assign(array(
			'immersionHomepageImage' => $journal->getLocalizedSetting('homepageImage'),
			'loginUrl' => $loginUrl,
			'orcidImageUrl' => $orcidImageUrl
		));
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
	
	/**
	 * Add section settings to IssueDAO
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option SectionDAO
	 *		@option array List of additional fields
	 * ]
	 */
	public function addIssueDAOFieldNames($hookName, $args) {
		$fields =& $args[1];
		$fields[] = 'immersionSectionColor';
	}
	
	
	/**
	 * Initialize data when form is first loaded
	 *
	 * @param $hookName string `issueform::initData`
	 * @parram $args array [
	 *		@option IssueForm
	 * ]
	 */
	public function initDataIssueFormFields($hookName, $args) {
		$issueForm = $args[0];
		$request = Application::getRequest();
		$context = $request->getContext();
		$issueDao = DAORegistry::getDAO('IssueDAO');
		
		$issueForm->setData('immersionSectionColor', $issueForm->issue->getData('immersionSectionColor'));
	}
	
	/**$$
	 * Read user input from additional fields in the issue editing form
	 *
	 * @param $hookName string `issueform::readUserVars`
	 * @parram $args array [
	 *		@option IssueForm
	 *		@option array User vars
	 * ]
	 */
	public function readIssueFormFields($hookName, $args) {
		$issueForm =& $args[0];
		$request = Application::getRequest();
		
		$issueForm->setData('immersionSectionColor', $request->getUserVar('immersionSectionColor'));
	}
	
	/**
	 * Save additional fields in the issue editing form
	 *
	 * @param $hookName string `issueform::execute`
	 * @param $args array [
	 *		@option IssueForm
	 *		@option Issue
	 *		@option Request
	 * ]
	 */
	public function executeIssueFormFields($hookName, $args) {
		$issueForm = $args[0];
		$issue = $args[1];
		$request = $args[2];
		
		$issue->setData('immersionSectionColor', $issueForm->getData('immersionSectionColor'));
		
		$issueDao = DAORegistry::getDAO('IssueDAO');
		$issueDao->updateObject($issue);
	}
	
	/**
	 * Add variables to the issue editing form
	 *
	 * @param $hookName string `issueform::execute`
	 * @param $args array [
	 *		@option IssueForm
	 * ]
	 */
	
	public function addToIssueForm($hookName, $args) {
		$issueForm = $args[0];
		$request = $this->getRequest();
		
		$sectionDao = DAORegistry::getDAO('SectionDAO');
		$sections = $sectionDao->getByIssueId($issueForm->issue->getId());
		
		$templateMgr = TemplateManager::getManager($request);
		
		$templateMgr->assign(array(
			'sections' => $sections
		));
	}
	
}