<?php

/**
 * @file classes/ImmersionPublishedArticleDAO.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ImmersionPublishedArticleDAO
 * @ingroup article
 * @see PublishedArticleDAO
 *
 * @brief Operations for retrieving and modifying PublishedArticle objects for Immersion theme.
 */
	
import('classes.article.PublishedArticleDAO');

class ImmersionPublishedArticleDAO extends PublishedArticleDAO {
	
	/**
	 * Retrieve Published Articles by issue id
	 * @param $issueId int
	 * @param $useCache boolean optional
	 * @return array Array of PublishedArticle objects
	 */

	function getImmersionPublishedArticlesInSections($issueId, $useCache = false)
	{
		$locale = AppLocale::getLocale();
		
		$result = $this->retrieve(
			'SELECT DISTINCT
				ps.*,
				s.*,
				se.abstracts_not_required AS abstracts_not_required,
				se.hide_title AS section_hide_title,
				se.hide_author AS section_hide_author,
				se.editor_restricted AS section_editor_restricted,
				ss.setting_name AS section_setting_name,
				ss.setting_value AS section_setting_value,
				COALESCE(o.seq, se.seq) AS section_seq,
				ps.seq,
				' . $this->getFetchColumns() . '
			FROM	published_submissions ps
				JOIN submissions s ON (ps.submission_id = s.submission_id)
				LEFT JOIN custom_section_orders o ON (s.section_id = o.section_id AND ps.issue_id = o.issue_id)
				LEFT JOIN section_settings ss ON (s.section_id = ss.section_id)
				' . $this->getFetchJoins() . '
			WHERE	ps.issue_id = ?
				AND s.status <> ' . STATUS_DECLINED . '
				AND ss.locale = ? OR ss.locale = ""
			ORDER BY section_seq ASC, ps.seq ASC',
			array_merge(
				$this->getFetchParameters(),
				array(
					(int)$issueId,
					(string)$locale
				)
			)
		);
		
		$currSectionId = 0;
		$publishedArticles = array();
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$publishedArticle = $this->_fromRow($row);
			if ($publishedArticle->getSectionId() != $currSectionId && !isset($publishedArticles[$publishedArticle->getSectionId()])) {
				$currSectionId = $publishedArticle->getSectionId();
				$publishedArticles[$currSectionId] = array(
					'articles' => array(),
					'title' => '',
					'abstractsNotRequired' => $row['abstracts_not_required'],
					'hideAuthor' => $row['section_hide_author'],
					'editorRestricted' => $row['section_editor_restricted'],
					'settingData' => array()
				);
				
				if (!$row['section_hide_title']) {
					$publishedArticles[$currSectionId]['title'] = $publishedArticle->getSectionTitle();
				}
			}
			$publishedArticles[$currSectionId]['articles'][] = $publishedArticle;
			$publishedArticles[$currSectionId]['settingData'][$row['section_setting_name']] = $row['section_setting_value'];
			
			$result->MoveNext();
		}
		
		$result->Close();
		return $publishedArticles;
	}
}