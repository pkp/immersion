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
		
		// Additional data to the templates
		HookRegistry::register ('TemplateManager::display', array($this, 'addIssueTemplateData'));
		HookRegistry::register ('TemplateManager::display', array($this, 'addSiteWideData'));
		HookRegistry::register ('issueform::display', array($this, 'addToIssueForm'));
		
		// Check if CSS embedded to the HTML galley
		HookRegistry::register('TemplateManager::display', array($this, 'hasEmbeddedCSS'));
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
		 * @var $publishedArticleDao PublishedArticleDAO
		 * @var $sectionDao SectionDAO
		 * @var $sections array
		 * @var $section Section
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
		
		$sectionDao = DAORegistry::getDAO('SectionDAO');
		$sections = $sectionDao->getByIssueId($issue->getId());
		
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
		
		if ($request->getContext()) {
			$templateMgr->assign('immersionHomepageImage', $journal->getLocalizedSetting('homepageImage'));
		}
		
		$templateMgr->assign(array(
			'loginUrl' => $loginUrl,
			'orcidImageUrl' => $orcidImageUrl
		));
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
		// assign predefined colors to the form
		$validColors = array(
			'#74A6B4' => 'cyan',
			'#8B9890' => 'grayish green',
			'#619144' => 'glade green',
			'#DFC6A4' => 'light grayish gamboge',
			'#CB6579' => 'pink',
			'#A0DFE0' => 'light cyan',
			'#F0D74C' => 'yellow',
			'#FF2C00' => 'scarlet',
			'#527543' => 'fern green',
			'#83A383' => 'envy green',
			'#AABBAA' => 'greenish gray'
		);
		
		$templateMgr->assign(array(
			'sections' => $sections,
			'validColors' => $validColors,
		));
	}
	
	/**
	 * @param $hookName string `TemplateManager::display`
	 * @param $args array [
	 *      @option TemplateManager
	 *      @option string relative path to the template
	 *  ]
	 * @return bool
	 */
	public function hasEmbeddedCSS($hookName, $args) {
		$templateMgr = $args[0];
		$template = $args[1];
		$request = $this->getRequest();
		
		// Retun false if not a galley page
		if ($template !== 'plugins/plugins/generic/htmlArticleGalley/generic/htmlArticleGalley:display.tpl') return false;
		
		$articleArrays = $templateMgr->get_template_vars('article');
		
		// Deafult styling for HTML galley
		$boolEmbeddedCss = false;
		foreach ($articleArrays->getGalleys() as $galley) {
			if ($galley->getFileType() === 'text/html') {
				$submissionFile = $galley->getFile();
				
				$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
				import('lib.pkp.classes.submission.SubmissionFile'); // Constants
				$embeddableFiles = array_merge(
					$submissionFileDao->getLatestRevisions($submissionFile->getSubmissionId(), SUBMISSION_FILE_PROOF),
					$submissionFileDao->getLatestRevisionsByAssocId(ASSOC_TYPE_SUBMISSION_FILE, $submissionFile->getFileId(), $submissionFile->getSubmissionId(), SUBMISSION_FILE_DEPENDENT)
				);
				
				foreach ($embeddableFiles as $embeddableFile) {
					if ($embeddableFile->getFileType() == 'text/css') {
						$boolEmbeddedCss = true;
					}
				}
			}
			
		}
		
		$templateMgr->assign(array(
			'boolEmbeddedCss' => $boolEmbeddedCss,
			'themePath' => $request->getBaseUrl() . "/" . $this->getPluginPath(),
		));
	}
	
	
}