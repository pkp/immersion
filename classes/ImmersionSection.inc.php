<?php

import('classes.journal.Section');

class ImmersionSection extends Section {
	
	/**
	 * Get the localized section cover image file name
	 * @return string
	 */
	function getImmersionLocalizedCoverImage() {
		return $this->getLocalizedData('immersionCoverImage');
	}
	
	/**
	 * Get section cover image file name
	 * @param $locale string
	 * @return string
	 */
	function getImmersionCoverImage($locale) {
		return $this->getData('immersionCoverImage', $locale);
	}
	
	/**
	 * Set section cover image file name
	 * @param $coverImage string
	 * @param $locale string
	 */
	function setImmersionCoverImage($coverImage, $locale) {
		$this->setData('immersionCoverImage', $coverImage, $locale);
	}
	
	/**
	 * Get the localized section cover image alternate text
	 * @return string
	 */
	function getImmersionLocalizedCoverImageAltText() {
		return $this->getLocalizedData('immersionCoverImageAltText');
	}
	
	/**
	 * Get section cover image alternate text
	 * @param $locale string
	 * @return string
	 */
	function getImmersionCoverImageAltText($locale) {
		return $this->getData('immersionCoverImageAltText', $locale);
	}
	
	/**
	 * Get a full URL to the localized section cover image
	 *
	 * @return string
	 */
	function getImmersionLocalizedCoverImageUrl() {
		$coverImage = $this->getImmersionLocalizedCoverImage();
		if (!$coverImage) {
			return '';
		}
		
		$request = Application::getRequest();
		
		import('classes.file.PublicFileManager');
		$publicFileManager = new PublicFileManager();
		
		return $request->getBaseUrl() . '/' . $publicFileManager->getJournalFilesPath($this->getJournalId()) . '/' . $coverImage;
	}
	
	/**
	 * Get the full URL to all localized cover images
	 *
	 * @return array
	 */
	function getImmersionCoverImageUrls() {
		$coverImages = $this->getImmersionCoverImage(null);
		if (empty($coverImages)) {
			return array();
		}
		
		$request = Application::getRequest();
		import('classes.file.PublicFileManager');
		$publicFileManager = new PublicFileManager();
		
		$urls = array();
		
		foreach ($coverImages as $locale => $coverImage) {
			$urls[$locale] = sprintf('%s/%s/%s', $request->getBaseUrl(), $publicFileManager->getJournalFilesPath($this->getJournalId()), $coverImage);
		}
		
		return $urls;
	}
	
	/**
	 * Set section cover image alternate text
	 * @param $coverImageAltText string
	 * @param $locale string
	 */
	function setImmersionCoverImageAltText($coverImageAltText, $locale) {
		$this->setData('immersionCoverImageAltText', $coverImageAltText, $locale);
	}
	
	/**
	 * Set section color
	 * @param $colorPick string (hex color code)
	 */
	function setImmersionColor($colorPick) {
		$this->setData('immersionColorPick', $colorPick);
	}
	
	/**
	 * Get section color
	 * @return string (hex color code)
	 */
	
	function getImmersionColor() {
		return $this->getData('immersionColorPick');
	}
	
	
	
	
}