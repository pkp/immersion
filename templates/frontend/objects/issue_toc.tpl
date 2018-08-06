{**
 * templates/frontend/objects/issue_toc.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief View of an Issue which displays a full table of contents.
 *
 * @uses $issue Issue The issue
 * @uses $issueTitle string Title of the issue. May be empty
 * @uses $issueSeries string Vol/No/Year string for the issue
 * @uses $issueGalleys array Galleys for the entire issue
 * @uses $hasAccess bool Can this user access galleys for this context?
 * @uses $publishedArticles array Lists of articles published in this issue
 *   sorted by section.
 * @uses $primaryGenreIds array List of file genre ids for primary file types
 * @uses $sectionHeading string Tag to use (h2, h3, etc) for section headings
 *}
<div class="issue-toc">
	{foreach from=$immersionSections item=section}
		{if $section->getImmersionLocalizedCoverImage()}
			<div><b>Section Image</b></div>
			<img src="{$section->getImmersionLocalizedCoverImageUrl()}">
		{/if}
	{/foreach}
</div>


<div>
	{foreach from=$publishedArticlesBySections item=publishedArticlesBySection}
		<div class="section">
			{if $publishedArticlesBySection.articles}
				{if $publishedArticlesBySection.title}
					<h3 class="section_title">
						<div>
							{$publishedArticlesBySection.title|escape} <br/>

							{foreach from=$publishedArticlesBySection.settingData item=sectionValue key=sectionName}
								{if $sectionName === "policy"}
									{$sectionName|escape}: {$sectionValue|strip_unsafe_html} <br/>
								{else}
									{$sectionName|escape}: {$sectionValue|escape} <br/>
								{/if}
							{/foreach}
						</div>
					</h3>
				{/if}
				<div class="section_content">
					{foreach from=$publishedArticlesBySection.articles item=article}
						{*{include file="frontend/objects/article_summary.tpl"}*}
					{/foreach}
				</div>
			{/if}
		</div>
	{/foreach}
</div>
