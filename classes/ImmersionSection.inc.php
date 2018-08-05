<?php

import('classes.journal.Section');

class ImmersionSection extends Section {
	
	/**
	 * Get the localized section cover image file name
	 * @return string
	 */
	function getLocalizedCoverImage() {
		return $this->getLocalizedData('coverImage');
	}
	
	/**
	 * Get section cover image file name
	 * @param $locale string
	 * @return string
	 */
	function getCoverImage($locale) {
		return $this->getData('coverImage', $locale);
	}
	
	/**
	 * Set section cover image file name
	 * @param $coverImage string
	 * @param $locale string
	 */
	function setCoverImage($coverImage, $locale) {
		return $this->setData('coverImage', $coverImage, $locale);
	}
	
	/**
	 * Get the localized section cover image alternate text
	 * @return string
	 */
	function getLocalizedCoverImageAltText() {
		return $this->getLocalizedData('coverImageAltText');
	}
	
	/**
	 * Get section cover image alternate text
	 * @param $locale string
	 * @return string
	 */
	function getCoverImageAltText($locale) {
		return $this->getData('coverImageAltText', $locale);
	}
	
	/**
	 * Get a full URL to the localized section cover image
	 *
	 * @return string
	 */
	function getLocalizedCoverImageUrl() {
		$coverImage = $this->getLocalizedCoverImage();
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
	function getCoverImageUrls() {
		$coverImages = $this->getCoverImage(null);
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
	function setCoverImageAltText($coverImageAltText, $locale) {
		return $this->setData('coverImageAltText', $coverImageAltText, $locale);
	}
	
	function getColor() {
		return $this->getData('colorPick');
	}
	
	function setColor($colorPick) {
		return $this->setData('colorPick', $colorPick);
	}
	
	
}