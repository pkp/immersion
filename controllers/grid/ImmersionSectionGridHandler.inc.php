<?php

import('controllers.grid.settings.sections.SectionGridHandler');

class ImmersionSectionGridHandler extends SectionGridHandler {
	
	function __construct() {
		parent::__construct();
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER),
			array('deleteCoverImage')
		);
		$this->plugin = PluginRegistry::getPlugin('themes', IMMERSION_PLUGIN_NAME);
	}
	
	/**
	 * An action to edit a section
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 * @return JSONMessage JSON object
	 */
	function editSection($args, $request) {
		parent::editSection($args, $request);
		$sectionId = isset($args['sectionId']) ? $args['sectionId'] : null;
		$this->setupTemplate($request);
		
		import('plugins.themes.immersion.controllers.grid.ImmersionSectionForm');
		$sectionForm = new ImmersionSectionForm($request, $sectionId);
		$sectionForm->initData();
		return new JSONMessage(true, $sectionForm->fetch($request));
	}
	
	/**
	 * Update a section
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function updateSection($args, $request) {
		parent::updateSection($args, $request);
		$sectionId = $request->getUserVar('sectionId');
		
		import('plugins.themes.immersion.controllers.grid.ImmersionSectionForm');
		$sectionForm = new ImmersionSectionForm($request, $sectionId);
		$sectionForm->readInputData();
		
		if ($sectionForm->validate()) {
			$sectionForm->execute($args, $request);
			return DAO::getDataChangedEvent($sectionForm->getSectionId());
		}
		return new JSONMessage(false);
	}
	
	/**
	 * Delete an uploaded cover image.
	 * @param $args array
	 *   `coverImage` string Filename of the cover image to be deleted.I
	 *   `issueId` int Id of the issue this cover image is attached to
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function deleteCoverImage($args, $request) {
		assert(!empty($args['immersionCoverImage']) && !empty($args['sectionId']));
		
		// Check if the passed filename matches the filename for this issue's
		// cover page.
		$sectionDao = DAORegistry::getDAO('ImmersionSectionDAO');
		$section = $sectionDao->getById((int) $args['sectionId']);
		$locale = AppLocale::getLocale();
		if ($args['immersionCoverImage'] != $section->getImmersionCoverImage($locale)) {
			return new JSONMessage(false, __('editor.issues.removeCoverImageFileNameMismatch'));
		}
		
		$file = $args['immersionCoverImage'];
		
		// Remove cover image and alt text from issue settings
		$section->setImmersionCoverImage('', $locale);
		$section->setImmersionCoverImageAltText('', $locale);
		$sectionDao->updateObject($section);
		
		// Remove the file
		$publicFileManager = new PublicFileManager();
		if ($publicFileManager->removeJournalFile($section->getJournalId(), $file)) {
			$json = new JSONMessage(true);
			$json->setEvent('fileDeleted');
			return $json;
		} else {
			return new JSONMessage(false, __('editor.issues.removeCoverImageFileNotFound'));
		}
	}
}