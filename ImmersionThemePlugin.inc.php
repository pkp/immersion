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
		
		// New settings for the sections (Dashboard -> Settings -> Journal -> Sections -> Edit): TODO should be deleted
		/*
		HookRegistry::register('sectiondao::getAdditionalFieldNames', array($this, 'addSectionDAOFieldNames'));
		HookRegistry::register('sectionform::initdata', array($this, 'initDataSectionFormFields'));
		HookRegistry::register('sectionform::readuservars', array($this, 'readSectionFormFields'));
		HookRegistry::register('sectionform::execute', array($this, 'executeSectionFormFields'));
		*/
		
		// Additional data to the templates
		HookRegistry::register ('TemplateManager::display', array($this, 'addTemplateData'));
		
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
	
	
	// Add section settings to SectionDAO
	
	public function addSectionDAOFieldNames($hookName, $args) {
		
		/* @var $fields array */
		
		$fields =& $args[1];
		$fields[] = 'colorPick';
	}
	
	// Initialize data when form is first loaded

	public function initDataSectionFormFields($hookName, $args) {
		
		/* @var $sectionForm SectionForm
		 * @var $sectionDao SectionDAO
		 * @var $section Section
		 */
		
		$sectionForm = $args[0];
		$request = $this->getRequest();
		
		$context = $request->getContext();
		$contextId = $context ? $context->getId() : CONTEXT_ID_NONE;
		
		$sectionDao = DAORegistry::getDAO('ImmersionSectionDAO');
		
		$section = $sectionDao->getById($sectionForm->getSectionId(), $contextId);
		
		$sectionForm->setData('colorPick', $section->getData('colorPick'));
		
	}
	
	// Read user input from additional fields in the section editing form
	
	public function readSectionFormFields($hookName, $args) {
		
		/* @var $sectionForm SectionForm */
		
		$sectionForm =& $args[0];
		$request = Application::getRequest();
		$sectionForm->setData('colorPick', $request->getUserVar('colorPick'));
	}
	
	// Save additional fields in the section editing form

	public function executeSectionFormFields($hookName, $args) {
		
		/* @var $sectionForm SectionForm
		 * @var $sectionDao SectionDAO
		 * @var $section Section
		 */
		
		$sectionForm = $args[0];
		$section = $args[1];
		$colorPick = $sectionForm->getData('colorPick') ? $sectionForm->getData('colorPick') : '';
		if (empty($colorPick)) {
			$colorPick = '#ffffff';
		}
		$section->setData('colorPick', $colorPick);
		
		$sectionDao = DAORegistry::getDAO('SectionDAO');
		$sectionDao->updateObject($section);
	}
	
	// Add data to the templates
	
	public function addTemplateData($hookname, $args) {
		
		/* @var $request Request
		 * @var $context Context
		 * @var $templateMgr TemplateManager
		 * @var $sectionDao SectionDAO
		 * @var $section Section
		 * @var $result DAOResultFactory (contains a list of Section objects)
		 */
		
		$request = $this->getRequest();
		$context = $request->getContext();
		$contextId = $context ? $context->getId() : CONTEXT_ID_NONE;
		
		$templateMgr = $args[0];
		$template = $args[1];
		
		$sectionDao = DAORegistry::getDAO('ImmersionSectionDAO');
		$resultFactory = $sectionDao->getByContextId($contextId);
		$immersionSections = array();
		while ($section = $resultFactory->next()) {
			$immersionSections[] = $section;
		}
		$templateMgr->assign('immersionSections', $immersionSections);
		
		$templateMgr->register_function('plugin_url', array($this, 'smartyPluginUrl'));
		
	}
	
	// Allow requests for ImmersionSectionHandler
	
	function setupGridHandler($hookName, $params) {
		$component =& $params[0];
		if ($component == 'plugins.themes.immersion.controllers.grid.ImmersionSectionGridHandler') {
			define('IMMERSION_PLUGIN_NAME', $this->getName());
			return true;
		}
		return false;
	}
	
	function addHandlerData($hookName, $params) {
		var_dump($params);
	}
	
}