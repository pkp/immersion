<?php

import('controllers.grid.settings.sections.form.SectionForm');
import('lib.pkp.classes.linkAction.LinkAction');
import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');

class ImmersionSectionForm extends SectionForm {
	
	/**
	 * Constructor.
	 * @param $request Request
	 * @param $sectionId int optional
	 */
	
	function __construct($request, $sectionId = null)
	{
		parent::__construct($request, $sectionId);
	}
	
	/**
	 * Initialize form data from current settings.
	 */
	function initData() {
		
		$request = Application::getRequest();
		$journal = $request->getJournal();
		
		$sectionDao = DAORegistry::getDAO('ImmersionSectionDAO');
		$sectionId = $this->getSectionId();
		if ($sectionId) {
			$section = $sectionDao->getById($sectionId, $journal->getId());
		}
		
		if (isset($section) ) {
			$locale = AppLocale::getLocale();
			$this->setData(array(
				'coverImage' => $section->getCoverImage($locale),
				'coverImageAltText' => $section->getCoverImageAltText($locale),
				'colorPick' => $section->getColor()
			));
		}
		
		parent::initData();
	}
	
	/**
	 * Fetch form contents
	 * @param $request Request
	 * @see Form::fetch()
	 */
	function fetch($request) {
		
		$templateMgr = TemplateManager::getManager($request);
		
		$journal = $request->getJournal();
		
		$sectionDao = DAORegistry::getDAO('ImmersionSectionDAO');
		$sectionId = $this->getSectionId();
		$section = $sectionDao->getById($sectionId, $journal->getId());
		// Cover image delete link action
		if ($coverImage = $section->getCoverImage(AppLocale::getLocale())) $templateMgr->assign(
			'deleteCoverImageLinkAction',
			new LinkAction(
				'deleteCoverImage',
				new RemoteActionConfirmationModal(
					$request->getSession(),
					__('common.confirmDelete'), null,
					$request->getRouter()->url(
						$request, null, null, 'deleteCoverImage', null, array(
							'coverImage' => $coverImage,
							'sectionId' => $sectionId,
						)
					),
					'modal_delete'
				),
				__('common.delete'),
				null
			)
		);
		
		return parent::fetch($request);
		
	}
	
	function readInputData() {
		parent::readInputData();
		$this->readUserVars(array('coverImageAltText', 'colorPick', 'temporaryFileId'));
	}
	
	/**
	 * Get the names of fields for which localized data is allowed; add specific locale data from Immersion Theme DAO
	 * @return array
	 */
	function getLocaleFieldNames() {
		$sectionDao = DAORegistry::getDAO('ImmersionSectionDAO');
		return $sectionDao->getLocaleFieldNames();
	}
	
	/**
	 * @copydoc Form::validate()
	 */
	function validate() {
		if ($temporaryFileId = $this->getData('temporaryFileId')) {
			$request = Application::getRequest();
			$user = $request->getUser();
			$temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');
			$temporaryFile = $temporaryFileDao->getTemporaryFile($temporaryFileId, $user->getId());
			
			import('classes.file.PublicFileManager');
			$publicFileManager = new PublicFileManager();
			if (!$publicFileManager->getImageExtension($temporaryFile->getFileType())) {
				$this->addError('coverImage', __('editor.issues.invalidCoverImageFormat'));
			}
		}
		
		return parent::validate();
	}
	
	/**
	 * Save section.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return mixed
	 */
	function execute($args, $request) {
		$sectionDao = DAORegistry::getDAO('ImmersionSectionDAO');
		$journal = $request->getJournal();
		
		// Get or create the section object
		if ($this->getSectionId()) {
			$section = $sectionDao->getById($this->getSectionId(), $journal->getId());
		} else {
			import('classes.journal.Section');
			$section = $sectionDao->newDataObject();
			$section->setJournalId($journal->getId());
		}
		
		$locale = AppLocale::getLocale();
		// Copy an uploaded cover file for the section, if there is one.
		if ($temporaryFileId = $this->getData('temporaryFileId')) {
			$user = $request->getUser();
			$temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');
			$temporaryFile = $temporaryFileDao->getTemporaryFile($temporaryFileId, $user->getId());
			
			import('classes.file.PublicFileManager');
			$publicFileManager = new PublicFileManager();
			$newFileName = 'cover_section_' . $this->getSectionId() . '_' . $locale . $publicFileManager->getImageExtension($temporaryFile->getFileType());
			$journal = $request->getJournal();
			$publicFileManager->copyJournalFile($journal->getId(), $temporaryFile->getFilePath(), $newFileName);
			$section->setCoverImage($newFileName, $locale);
		}
		
		// TODO input validation (HTML hex colors)
		$section->setAbstractWordCount($this->getData('wordCount'));
		
		$section->setColor($this->getData('colorPick'), null);
		
		$section->setCoverImageAltText($this->getData('coverImageAltText'), $locale);
		
		// Update section editors
		$this->_saveSubEditors($journal->getId());
		
		// Insert or update the section in the DB
		if ($this->getSectionId()) {
			$sectionDao->updateObject($section);
		} else {
			$section->setSequence(REALLY_BIG_NUMBER);
			$this->setSectionId($sectionDao->insertObject($section));
			$sectionDao->resequenceSections($journal->getId());
		}
		
		return parent::execute($section, $request);
	}
	
}