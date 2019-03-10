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

<div class="container">
	<header class="issue__header">
		{if $requestedOp === "index"}
			<p class="issue__meta">{translate key="journal.currentIssue"}</p>
		{/if}
		{strip}
		<h{if $requestedOp === "issue"}1{else}2{/if} class="issue__title">
			{if $issue->getShowVolume() || $issue->getShowNumber()}
				{if $issue->getShowVolume()}
					<span class="issue__volume">{translate key="issue.volume"} {$issue->getVolume()|escape}{if $issue->getShowNumber()}, {/if}</span>
				{/if}
				{if $issue->getShowNumber()}
					<span class="issue__number">{translate key="issue.no"}. {$issue->getNumber()|escape}</span>
				{/if}
			{/if}
			{if $issue->getShowTitle()}
				<span class="issue__localized_name">{$issue->getLocalizedTitle()|escape}</span>
			{/if}
		</h1>
			{if $issue->getDatePublished()}
				<p class="issue__meta">{translate key="plugins.themes.immersion.issue.published"} {$issue->getDatePublished()|date_format:$dateFormatLong}</p>
			{/if}
			{/strip}
	</header>

	{if $issue->getLocalizedDescription()}
		<div class="row">
			<section class="issue-desc">
				<div class="col-md-6 issue-desc">
					<h3 class="issue-desc__title">{translate key="plugins.themes.immersion.issue.description"}</h3>
					<div class="issue-desc__content">
						{assign var=stringLenght value=280}
						{assign var=issueDescription value=$issue->getLocalizedDescription()|strip_unsafe_html}
						{if $issueDescription|strlen <= $stringLenght || $requestedPage == 'issue'}
							{$issueDescription}
						{else}
							{$issueDescription|substr:0:$stringLenght|mb_convert_encoding:'UTF-8'|replace:'?':''|trim}
							<span class="ellipsis">...</span>
							<a class="full-issue__link"
							   href="{url op="view" page="issue" path=$issue->getBestIssueId()}">{translate key="plugins.themes.immersion.issue.fullIssueLink"}</a>
						{/if}
					</div>
				</div>
			</section>
		</div>
	{/if}
</div>

{foreach from=$publishedArticlesBySections item=publishedArticlesBySection}
	{if $publishedArticlesBySection.articles}
		{assign var='immersionColorPick' value=$publishedArticlesBySection.sectionColor|escape}
		{assign var='isSectionDark' value=$publishedArticlesBySection.isSectionDark}
		<section class="issue-section{if $isSectionDark} section_dark{/if}"{if $immersionColorPick} style="background-color: {$immersionColorPick};"{/if}>
			<div class="container">
				{if $publishedArticlesBySection.title || $publishedArticlesBySection.sectionDescription}
					<header class="row issue-section__header">
						{if $publishedArticlesBySection.title}
							<h3 class="col-md-6 col-lg-3 issue-section__title">{$publishedArticlesBySection.title|escape}</h3>
						{/if}
						{if $publishedArticlesBySection.sectionDescription}
							<div class="col-md-6 col-lg-9 issue-section__desc">
								{$publishedArticlesBySection.sectionDescription|strip_unsafe_html}
							</div>
						{/if}
					</header>
				{/if}
				<div class="row">
					<div class="col-12">
						<ol class="issue-section__toc">
							{foreach from=$publishedArticlesBySection.articles item=article key=articleNumber}
								<li class="issue-section__toc-item">
									{include file="frontend/objects/article_summary.tpl" articleNumber=$articleNumber}
								</li>
							{/foreach}
						</ol>
					</div>
				</div>
			</div>
		</section>
	{/if}
{/foreach}
