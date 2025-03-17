<?php

/**
 * @file plugins/themes/immersion/ImmersionThemePlugin.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
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

		// Adding styles (JQuery UI, Bootstrap, Tag-it)
		$this->addStyle('app-css', 'resources/dist/app.min.css');
		$this->addStyle('less', 'resources/less/import.less');

		// Styles for HTML galleys
		$this->addStyle('htmlGalley', 'templates/plugins/generic/htmlArticleGalley/css/default.css', array('contexts' => 'htmlGalley'));
		
		// Adding scripts (JQuery, Popper, Bootstrap, JQuery UI, Tag-it, Theme's JS)
		$this->addScript('app-js', 'resources/dist/app.min.js');

		// Add navigation menu areas for this theme
		$this->addMenuArea(array('primary', 'user'));

		// Option to show section description on the journal's homepage; turned off by default
		$this->addOption('sectionDescriptionSetting', 'FieldOptions', array(
			'label' => __('plugins.themes.immersion.options.sectionDescription.label'),
			'description' => __('plugins.themes.immersion.options.sectionDescription.description'),
			'type' => 'radio',
			'options' => array(
				[
					'value' => 'disable',
					'label'  => __('plugins.themes.immersion.options.sectionDescription.disable'),
				],
				[
					'value' => 'enable',
					'label'  => __('plugins.themes.immersion.options.sectionDescription.enable'),
				],
			)
		));

		$this->addOption('journalDescription', 'FieldOptions', array(
			'label' => __('plugins.themes.immersion.options.journalDescription.label'),
			'description' => __('plugins.themes.immersion.options.journalDescription.description'),
			'type' => 'radio',
			'options' => array(
				[
					'value' => 0,
					'label' => __('plugins.themes.immersion.options.journalDescription.disable'),
				],
				[
					'value' => 1,
					'label' => __('plugins.themes.immersion.options.journalDescription.enable'),
				],
			)
		));

		$this->addOption('journalDescriptionColour', 'FieldColor', array(
			'label' => __('plugins.themes.immersion.options.journalDescriptionColour.label'),
			'description' => __('plugins.themes.immersion.options.journalDescriptionColour.description'),
			'default' => '#000',
		));

		$this->addOption('immersionAnnouncementsColor', 'FieldColor', array(
			'label' => __('plugins.themes.immersion.announcements.colorPick'),
			'default' => '#000',
		));

		$this->addOption('abstractsOnIssuePage', 'FieldOptions', [
			'type' => 'radio',
			'label' => __('plugins.themes.immersion.option.abstractsOnIssuePage.label'),
			'description' => __('plugins.themes.immersion.option.abstractsOnIssuePage.description'),
			'tooltip' => __('plugins.themes.immersion.option.abstractsOnIssuePage.tooltip'),
			'options' => [
				[
					'value' => 'noAbstracts',
					'label' => __('plugins.themes.immersion.option.abstractsOnIssuePage.noAbstracts'),
				],
				[
					'value' => 'fadeoutAbstracts',
					'label' => __('plugins.themes.immersion.option.abstractsOnIssuePage.fadeoutAbstracts'),
				],
				[
					'value' => 'fullAbstracts',
					'label' => __('plugins.themes.immersion.option.abstractsOnIssuePage.fullAbstracts'),
				],
			],
			'default' => 'noAbstracts',
		]);

		// Additional data to the templates
		HookRegistry::register ('TemplateManager::display', array($this, 'addIssueTemplateData'));
		HookRegistry::register ('TemplateManager::display', array($this, 'addSiteWideData'));
		HookRegistry::register ('TemplateManager::display', array($this, 'homepageAnnouncements'));
		HookRegistry::register ('TemplateManager::display', array($this, 'homepageJournalDescription'));
		HookRegistry::register ('issueform::display', array($this, 'addToIssueForm'));

		// Check if CSS embedded to the HTML galley
		HookRegistry::register('TemplateManager::display', array($this, 'hasEmbeddedCSS'));
		// add abstract fade-out styles
		HookRegistry::register ('TemplateManager::display', array($this, 'addStyles'));

		// Additional variable for the issue form
		HookRegistry::register('issuedao::getAdditionalFieldNames', array($this, 'addIssueDAOFieldNames'));
		HookRegistry::register('issueform::initdata', array($this, 'initDataIssueFormFields'));
		HookRegistry::register('issueform::readuservars', array($this, 'readIssueFormFields'));
		HookRegistry::register('issueform::execute', array($this, 'executeIssueFormFields'));

		// Load colorpicker on issue management page
		$this->addStyle('spectrum', '/resources/dist/spectrum-1.8.0.css', [
			'contexts' => 'backend-manageIssues',
		]);
		$this->addScript('spectrum', '/resources/dist/spectrum-1.8.0.js', [
			'contexts' => 'backend-manageIssues',
		]);
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

	/**
	 * @param $hookname string
	 * @param $args array [
	 *      @option TemplateManager
	 *      @option string relative path to the template
	 * ]
	 * @brief Add section-specific data to the indexJournal and issue templates
	 */

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
			$issue = $templateMgr->getTemplateVars('issue');
		}

		if (!$issue) return false;

		$publishedSubmissionsInSection = $templateMgr->getTemplateVars('publishedSubmissions');

		// we need to set this even if no section colors are set
		$templateMgr->assign(array(
			'showAbstractsOnIssuePage' => $this->getOption('abstractsOnIssuePage')
		));

		// Section color
		$immersionSectionColors = $issue->getData('immersionSectionColor');
		if (empty($immersionSectionColors)) return false; // Section background colors aren't set

		$sectionDao = DAORegistry::getDAO('SectionDAO');
		$sections = $sectionDao->getByIssueId($issue->getId());
		$lastSectionColor = null;

		// Section description; check if this option and BrowseBySection plugin is enabled
		$sectionDescriptionSetting = $this->getOption('sectionDescriptionSetting');
		$pluginSettingsDAO = DAORegistry::getDAO('PluginSettingsDAO');
		$request = $this->getRequest();
		$context = $request->getContext();
		$contextId = $context ? $context->getId() : 0;
		$browseBySectionSettings = $pluginSettingsDAO->getPluginSettings($contextId, 'browsebysectionplugin');
		$isBrowseBySectionEnabled = false;
		if (!empty($browseBySectionSettings) && array_key_exists('enabled', $browseBySectionSettings) && $browseBySectionSettings['enabled']) {
			$isBrowseBySectionEnabled = true;
		}
		$locale = AppLocale::getLocale();

		foreach ($publishedSubmissionsInSection as $sectionId => $publishedArticlesBySection) {
			foreach ($sections as $section) {
				if ($section->getId() == $sectionId) {
					// Set section and its background color
					$publishedSubmissionsInSection[$sectionId]['section'] = $section;
					$publishedSubmissionsInSection[$sectionId]['sectionColor'] = $immersionSectionColors[$sectionId];

					// Check if section background color is dark
					$isSectionDark = false;
					if ($immersionSectionColors[$sectionId] && $this->isColourDark($immersionSectionColors[$sectionId])) {
						$isSectionDark = true;
					}
					$publishedSubmissionsInSection[$sectionId]['isSectionDark'] = $isSectionDark;

					// Section description
					if ($sectionDescriptionSetting == 'enable' && $isBrowseBySectionEnabled && $section->getData('browseByDescription', $locale)) {
						$publishedSubmissionsInSection[$sectionId]['sectionDescription'] = $section->getData('browseByDescription', $locale);
					}

					// Need only the color of the last section that contains articles
					if ($publishedSubmissionsInSection[$sectionId]['articles'] && $immersionSectionColors[$sectionId]) {
						$lastSectionColor = $immersionSectionColors[$sectionId];
					}
				}
			}
		}
		$templateMgr->assign(array(
			'publishedSubmissions' => $publishedSubmissionsInSection,
			'lastSectionColor' => $lastSectionColor,
		));
	}

	/**
	 * @param $hookname string
	 * @param $args array [
	 *      @option TemplateManager
	 *      @option string relative path to the template
	 * ]
	 * @return boolean|void
	 * @brief background color for announcements section on the journal index page
	 */
	public function homepageAnnouncements($hookname, $args) {

		$templateMgr = $args[0];
		$template = $args[1];

		if ($template !== 'frontend/pages/indexJournal.tpl') return false;

		$request = $this->getRequest();
		$journal = $request->getJournal();

		// Announcements on index journal page
		$announcementsIntro = $journal->getLocalizedData('announcementsIntroduction');
		$immersionAnnouncementsColor = $this->getOption('immersionAnnouncementsColor');

		$isAnnouncementDark = false;
		if ($immersionAnnouncementsColor && $this->isColourDark($immersionAnnouncementsColor)) {
			$isAnnouncementDark = true;
		}

		$templateMgr->assign(array(
			'announcementsIntroduction'=> $announcementsIntro,
			'isAnnouncementDark' => $isAnnouncementDark,
			'immersionAnnouncementsColor' => $immersionAnnouncementsColor
		));
	}

	/**
	 * @param $hookname string
	 * @param $args array [
	 *      @option TemplateManager
	 *      @option string relative path to the template
	 * ]
	 * @return void
	 * @brief Assign additional data to Smarty templates
	 */
	public function addSiteWideData($hookname, $args) {
		$templateMgr = $args[0];

		$request = $this->getRequest();
		$journal = $request->getJournal();

		if (!defined('SESSION_DISABLE_INIT')) {

			// Check locales
			if ($journal) {
				$locales = $journal->getSupportedLocaleNames();
			} else {
				$locales = $request->getSite()->getSupportedLocaleNames();
			}

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
				'languageToggleLocales' => $locales,
				'loginUrl' => $loginUrl,
				'orcidImageUrl' => $orcidImageUrl
			));
		}
	}

	/**
	 * @param $hookname string
	 * @param $args array [
	 *      @option TemplateManager
	 *      @option string relative path to the template
	 * ]
	 * @return boolean|void
	 * @brief Show Journal Description on the journal landing page depending on theme settings
	 */
	public function homepageJournalDescription($hookName, $args) {
		$templateMgr = $args[0];
		$template = $args[1];

		if ($template != "frontend/pages/indexJournal.tpl") return false;

		$journalDescriptionColour = $this->getOption('journalDescriptionColour');
		$isJournalDescriptionDark = false;
		if ($journalDescriptionColour && $this->isColourDark($journalDescriptionColour)) {
			$isJournalDescriptionDark = true;
		}

		$templateMgr->assign(array(
			'showJournalDescription' => $this->getOption('journalDescription'),
			'journalDescriptionColour' => $journalDescriptionColour,
			'isJournalDescriptionDark' => $isJournalDescriptionDark
		));
	}

	/**
	 * Add section settings to IssueDAO
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option IssueDAO
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
		$request = $this->getRequest();

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

		// The issueform::execute hook fires twice, once at the start of the
		// method when no issue exists. Only update the object during the
		// second request
		if (!$issue) {
			return;
		}

		$issue->setData('immersionSectionColor', $issueForm->getData('immersionSectionColor'));

		$issueDao = DAORegistry::getDAO('IssueDAO');
		$issueDao->updateObject($issue);
	}

	/**
	 * Add variables to the issue editing form
	 *
	 * @param $hookName string `issueform::display`; see fetch()
	 * @param $args array [
	 *		@option IssueForm
	 * ]
	 */

	public function addToIssueForm($hookName, $args) {
		$issueForm = $args[0];

		// Display only if available as per IssueForm::fetch()
		if ($issueForm->issue) {
			$request = $this->getRequest();

			$sectionDao = DAORegistry::getDAO('SectionDAO');
			$sections = $sectionDao->getByIssueId($issueForm->issue->getId());

			$templateMgr = TemplateManager::getManager($request);

			$templateMgr->assign('sections', $sections);
		}
	}

	/**
	 * @param $hookName string `TemplateManager::display`
	 * @param $args array [
	 *      @option TemplateManager
	 *      @option string relative path to the template
	 *  ]
	 */
	public function hasEmbeddedCSS($hookName, $args) {
		$templateMgr = $args[0]; /* @var $templateMgr TemplateManager */
		$template = $args[1];
		$request = $this->getRequest();

		// Return false if not a galley page
		if ($template !== 'plugins/plugins/generic/htmlArticleGalley/generic/htmlArticleGalley:display.tpl') return false;

		$articleArrays = $templateMgr->getTemplateVars('article');

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

	/**
	 * Get all defined sectin colors indexed by 'issueId_sectionId'
	 */
	public function addStyles($hookname, $args) {

		$templateMgr = $args[0];
		$template = $args[1];

		$templates = ["frontend/pages/issue.tpl", "frontend/pages/indexJournal.tpl"];
		if (!in_array($template, $templates)) return false;

		// For the abstract fade-out effect we require a css class for each section color
		$cssOutput = '';
		$issue = $templateMgr->getTemplateVars('issue');
		if ($issue) {
			foreach ($issue->getData('immersionSectionColor') as $sectionIndex => $sectionColor) {
					$cssOutput .= ".article__abstract-fadeout-{$sectionIndex}::after {\n";
					$cssOutput .= "  background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, {$sectionColor} 100%);\n";
					$cssOutput .= "}\n\n";
			}

			$templateMgr->addStyleSheet(
				'fadeout',
				$cssOutput,
				[
					'contexts' => 'frontend',
					'inline' => true,
					'priority' => STYLE_SEQUENCE_LAST,
				]
			);
		}
	}
}
