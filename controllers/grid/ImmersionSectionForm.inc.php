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
		
		/* @var $section ImmersionSection */
		
		if (isset($section) ) {
			$locale = AppLocale::getLocale();
			$this->setData(array(
				'title' => $section->getTitle(null), // Localized
				'abbrev' => $section->getAbbrev(null), // Localized
				'reviewFormId' => $section->getReviewFormId(),
				'metaIndexed' => !$section->getMetaIndexed(), // #2066: Inverted
				'metaReviewed' => !$section->getMetaReviewed(), // #2066: Inverted
				'abstractsNotRequired' => $section->getAbstractsNotRequired(),
				'identifyType' => $section->getIdentifyType(null), // Localized
				'editorRestriction' => $section->getEditorRestricted(),
				'hideTitle' => $section->getHideTitle(),
				'hideAuthor' => $section->getHideAuthor(),
				'policy' => $section->getPolicy(null), // Localized
				'wordCount' => $section->getAbstractWordCount(),
				'subEditors' => $this->_getAssignedSubEditorIds($sectionId, $journal->getId()),
				'immersionCoverImage' => $section->getImmersionCoverImage($locale),
				'immersionCoverImageAltText' => $section->getImmersionCoverImageAltText($locale),
				'immersionColorPick' => $section->getImmersionColor()
			));
		}
		
		PKPSectionForm::initData();
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
		if ($coverImage = $section->getImmersionCoverImage(AppLocale::getLocale())) $templateMgr->assign(
			'deleteCoverImageLinkAction',
			new LinkAction(
				'deleteCoverImage',
				new RemoteActionConfirmationModal(
					$request->getSession(),
					__('common.confirmDelete'), null,
					$request->getRouter()->url(
						$request, null, null, 'deleteCoverImage', null, array(
							'immersionCoverImage' => $coverImage,
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
		$this->readUserVars(array('immersionCoverImageAltText', 'immersionColorPick', 'temporaryFileId'));
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
				$this->addError('immersionCoverImage', __('editor.issues.invalidCoverImageFormat'));
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
		
		/* @var $sectionDAO ImmersionSectionDAO
		 * @var $section ImmersionSection
		 */
		$sectionDao = DAORegistry::getDAO('ImmersionSectionDAO');
		$journal = $request->getJournal();
		
		// Get or create the section object
		if ($this->getSectionId()) {
			$section = $sectionDao->getById($this->getSectionId(), $journal->getId());
		} else {
			import('plugins.themes.immersion.classes.ImmersionSection');
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
			$section->setImmersionCoverImage($newFileName, $locale);
		}
		
		// TODO input validation (HTML hex colors)
		$colorPick = $this->getData('immersionColorPick') ? $this->getData('immersionColorPick') : '';
		if (empty($colorPick)) {
			$colorPick = '#ffffff';
		}
		$section->setImmersionColor($colorPick);
		
		$section->setImmersionCoverImageAltText($this->getData('immersionCoverImageAltText'), $locale);
		
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