{**
 * templates/frontend/objects/article_details.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief View of an Article which displays all details about the article.
 *  Expected to be primary object on the page.
 *
 * Many journals will want to add custom data to this object, either through
 * plugins which attach to hooks on the page or by editing the template
 * themselves. In order to facilitate this, a flexible layout markup pattern has
 * been implemented. If followed, plugins and other content can provide markup
 * in a way that will render consistently with other items on the page. This
 * pattern is used in the .main_entry column and the .entry_details column. It
 * consists of the following:
 *
 * <!-- Wrapper class which provides proper spacing between components -->
 * <div class="item">
 *     <!-- Title/value combination -->
 *     <div class="label">Abstract</div>
 *     <div class="value">Value</div>
 * </div>
 *
 * All styling should be applied by class name, so that titles may use heading
 * elements (eg, <h3>) or any element required.
 *
 * <!-- Example: component with multiple title/value combinations -->
 * <div class="item">
 *     <div class="sub_item">
 *         <div class="label">DOI</div>
 *         <div class="value">12345678</div>
 *     </div>
 *     <div class="sub_item">
 *         <div class="label">Published Date</div>
 *         <div class="value">2015-01-01</div>
 *     </div>
 * </div>
 *
 * <!-- Example: component with no title -->
 * <div class="item">
 *     <div class="value">Whatever you'd like</div>
 * </div>
 *
 * Core components are produced manually below, but can also be added via
 * plugins using the hooks provided:
 *
 * Templates::Article::Main
 * Templates::Article::Details
 *
 * @uses $article Article This article
 * @uses $issue Issue The issue this article is assigned to
 * @uses $section Section The journal section this article is assigned to
 * @uses $primaryGalleys array List of article galleys that are not supplementary or dependent
 * @uses $supplementaryGalleys array List of article galleys that are supplementary
 * @uses $keywords array List of keywords assigned to this article
 * @uses $pubIdPlugins Array of pubId plugins which this article may be assigned
 * @uses $copyright string Copyright notice. Only assigned if statement should
 *   be included with published articles.
 * @uses $copyrightHolder string Name of copyright holder
 * @uses $copyrightYear string Year of copyright
 * @uses $licenseUrl string URL to license. Only assigned if license should be
 *   included with published articles.
 * @uses $ccLicenseBadge string An image and text with details about the license
 *}
<section class="col-md-8 article-page">
	<header class="article-page__header">

		{if $section}
			<p class="article-page__meta">{$section->getLocalizedTitle()|escape}</p>
		{else}
			<p class="article-page__meta">{translate key="article.article"}</p>
		{/if}

		<p class="article-page__meta">
			<a href="{url page="issue" op="view" path=$issue->getBestIssueId()}">{$issue->getIssueIdentification()|escape}</a>
		</p>

		<h1 class="article-page__title">
			<span>{$article->getLocalizedFullTitle()|escape}</span>
		</h1>

		{* authors list *}
		{if $article->getAuthors()}
			<div class="article-page__meta">
				<ul class="authors-string">
					{foreach from=$article->getAuthors() item=authorString key=authorStringKey}
						{strip}
							<li class="authors-string__item">
								<a class="author-string__href" href="#author-{$authorStringKey+1}">
									<span>{$authorString->getFullName()|escape}</span>
									<sup class="author-symbol author-plus">&plus;</sup>
									<sup class="author-symbol author-minus hidden">&minus;</sup>
								</a>
								{if $authorString->getOrcid()}
									<a class="orcidImage img-wrapper" href="{$authorString->getOrcid()|escape}">
										<img src="{$baseUrl}/{$orcidImageUrl}">
									</a>
								{/if}
							</li>
						{/strip}
					{/foreach}
				</ul>
			</div>
			{* Authors *}
			{assign var="authorCount" value=$article->getAuthors()|@count}
			{assign var="authorBioIndex" value=0}
			<div class="article-page__meta">
				<div class="article-details__authors">
					{foreach from=$article->getAuthors() item=author key=authorKey}
						<div class="article-details__author hidden" id="author-{$authorKey+1}">
							<div class="article-details__author-name">
								{$author->getFullName()|escape}
							</div>
							{if $author->getLocalizedAffiliation()}
								<div class="article-details__author-affiliation">{$author->getLocalizedAffiliation()|escape}</div>
							{/if}
							{if $author->getOrcid()}
								<div class="article-details__author-orcid">
									<a href="{$author->getOrcid()|escape}" target="_blank">
										{$orcidIcon}
										{$author->getOrcid()|escape}
									</a>
								</div>
							{/if}
							{if $author->getLocalizedBiography()}
								<br/>
								<a class="modal-trigger" href="#modalAuthorBio-{$authorKey+1}" data-toggle="modal" data-target="#modalAuthorBio-{$authorKey+1}">
									{translate key="plugins.themes.immersion.article.biography"}
								</a>
								{* author's biography *}
								<div class="modal fade bio-modal" id="modalAuthorBio-{$authorKey+1}" tabindex="-1" role="dialog">
									<div class="modal-dialog" role="document">
										<div class="modal-content">
											<button type="button" class="close" data-dismiss="modal" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>
											<div class="modal-body">
												<h2 class="sr-only">{translate key="submission.authorBiography"}</h2>
												{$author->getLocalizedBiography()|strip_unsafe_html}
											</div>
										</div>
									</div>
								</div>
							{/if}
						</div>
					{/foreach}
				</div>
			</div>
		{/if}
	</header>

	{* Article Galleys *}
	{if $primaryGalleys || $supplementaryGalleys}
		<div class="article-page__galleys">
			{if $primaryGalleys}
				<ul class="list-galleys primary-galleys">
					{foreach from=$primaryGalleys item=galley}
						<li>
							{include file="frontend/objects/galley_link.tpl" parent=$article galley=$galley purchaseFee=$currentJournal->getSetting('purchaseArticleFee') purchaseCurrency=$currentJournal->getSetting('currency')}
						</li>
					{/foreach}
				</ul>
			{/if}
			{if $supplementaryGalleys}
				<ul class="list-galleys supplementary-galleys">
					{foreach from=$supplementaryGalleys item=galley}
						<li>
							{include file="frontend/objects/galley_link.tpl" parent=$article galley=$galley isSupplementary="1"}
						</li>
					{/foreach}
				</ul>
			{/if}
		</div>
	{/if}

	<div class="article-page__meta">

		<dl>
		{* Pub IDs, including DOI *}
		{foreach from=$pubIdPlugins item=pubIdPlugin}
			{assign var=pubId value=$article->getStoredPubId($pubIdPlugin->getPubIdType())}
			{if $pubId}
				{assign var="doiUrl" value=$pubIdPlugin->getResolvingURL($currentJournal->getId(), $pubId)|escape}
				<dt>
					{$pubIdPlugin->getPubIdDisplayType()|escape}
				</dt>
				<dd>
					{if $pubIdPlugin->getResolvingURL($currentJournal->getId(), $pubId)|escape}
						<a id="pub-id::{$pubIdPlugin->getPubIdType()|escape}"
						   href="{$pubIdPlugin->getResolvingURL($currentJournal->getId(), $pubId)|escape}">
							{$pubIdPlugin->getResolvingURL($currentJournal->getId(), $pubId)|escape}
						</a>
					{else}
						{$pubId|escape}
					{/if}
				</dd>
			{/if}
		{/foreach}
			{if $article->getDateSubmitted()}
				<dt>
					{translate key="submissions.submitted"}
				</dt>
				<dd>
					{$article->getDateSubmitted()|escape|date_format:$dateFormatLong}
				</dd>
			{/if}
			{if $article->getDatePublished()}
				<dt>
					{translate key="submissions.published"}
				</dt>
				<dd>
					{$article->getDatePublished()|escape|date_format:$dateFormatLong}
				</dd>
			{/if}
		</dl>
	</div><!-- .article-page__meta-->

	{* Abstract *}
	{if $article->getLocalizedAbstract()}
		<h3 class="label">{translate key="article.abstract"}</h3>
		{$article->getLocalizedAbstract()|strip_unsafe_html}
	{/if}

	{* References *}
	{if $parsedCitations->getCount() || $article->getCitations()}
		<h3 class="label">
			{translate key="submission.citations"}
		</h3>
		{if $parsedCitations->getCount()}
		<ol class="references">
			{iterate from=parsedCitations item=parsedCitation}
				<li>{$parsedCitation->getCitationWithLinks()|strip_unsafe_html} {call_hook name="Templates::Article::Details::Reference" citation=$parsedCitation}</li>
			{/iterate}
		</ol>
		{elseif $article->getCitations()}
			<div class="references">
				{$article->getCitations()|nl2br}
			</div>
		{/if}
	{/if}

	{* Hook for plugins under the main block, like Recommend Articles by Author *}
	{call_hook name="Templates::Article::Main"}

</section>


<aside class="col-md-4 offset-lg-1 col-lg-3 article-sidebar">

	{* Article/Issue cover image *}
	{if $article->getLocalizedCoverImage() || $issue->getLocalizedCoverImage()}
		<h2 class="sr-only">{translate key="plugins.themes.immersion.article.figure"}</h2>
		<figure>
			{if $article->getLocalizedCoverImage()}
				<img class="img-fluid"
				     src="{$article->getLocalizedCoverImageUrl()|escape}"{if $article->getLocalizedCoverImageAltText()} alt="{$article->getLocalizedCoverImageAltText()|escape}"{/if}>
			{else}
				<a href="{url page="issue" op="view" path=$issue->getBestIssueId()}">
					<img class="img-fluid"
					     src="{$issue->getLocalizedCoverImageUrl()|escape}"{if $issue->getLocalizedCoverImageAltText()} alt="{$issue->getLocalizedCoverImageAltText()|escape}"{/if}>
				</a>
			{/if}
		</figure>
	{/if}

	{* Keywords *}
	{if !empty($keywords[$currentLocale])}
	<h2 class="article-side__title">{translate key="article.subject"}</h2>
		<ul>
			{foreach from=$keywords item=keyword}
				{foreach name=keywords from=$keyword item=keywordItem}
					<li>{$keywordItem|escape}</li>
				{/foreach}
			{/foreach}
		</ul>
	{/if}

	{* How to cite *}
	{if $citation}
		<h2>
			{translate key="submission.howToCite"}
		</h2>
		<div class="citation_format_value">
			<div id="citationOutput" role="region" aria-live="polite">
				{$citation}
			</div>
			<div class="citation_formats dropdown">
				<a class="btn btn-secondary" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
				   aria-expanded="false">
					{translate key="submission.howToCite.citationFormats"}
				</a>
				<div class="dropdown-menu" aria-labelledby="dropdownMenuButton" id="dropdown-cit">
					{foreach from=$citationStyles item="citationStyle"}
						<a
								class="dropdown-cite-link dropdown-item"
								aria-controls="citationOutput"
								href="{url page="citationstylelanguage" op="get" path=$citationStyle.id params=$citationArgs}"
								data-load-citation
								data-json-href="{url page="citationstylelanguage" op="get" path=$citationStyle.id params=$citationArgsJson}"
						>
							{$citationStyle.title|escape}
						</a>
					{/foreach}
					{if count($citationDownloads)}
						<div class="dropdown-divider"></div>
						<h3 class="download-cite">
							{translate key="submission.howToCite.downloadCitation"}
						</h3>
						{foreach from=$citationDownloads item="citationDownload"}
							<a class="dropdown-cite-link dropdown-item"
							   href="{url page="citationstylelanguage" op="download" path=$citationDownload.id params=$citationArgs}">
								{$citationDownload.title|escape}
							</a>
						{/foreach}
					{/if}
				</div>
			</div>
		</div>
	{/if}

	{* Licensing info *}
	{if $copyright || $licenseUrl}
		<div class="copyright-info">
			{if $licenseUrl}
				{if $ccLicenseBadge}
					{if $copyrightHolder}
						<p>{translate key="submission.copyrightStatement" copyrightHolder=$copyrightHolder copyrightYear=$copyrightYear}</p>
					{/if}
					{$ccLicenseBadge}
				{else}
					<a href="{$licenseUrl|escape}" class="copyright">
						{if $copyrightHolder}
							{translate key="submission.copyrightStatement" copyrightHolder=$copyrightHolder copyrightYear=$copyrightYear}
						{else}
							{translate key="submission.license"}
						{/if}
					</a>
				{/if}
			{/if}
			{* Copyright modal. Show only if license is absent *}
			{if $copyright && !$licenseUrl}
				<a class="copyright-notice__modal" data-toggle="modal" data-target="#copyrightModal">
					{translate key="about.copyrightNotice"}
				</a>
				<div class="modal fade" id="copyrightModal" tabindex="-1" role="dialog" aria-labelledby="copyrightModalTitle" aria-hidden="true">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="copyrightModalTitle">{translate key="about.copyrightNotice"}</h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								{$copyright|strip_unsafe_html}
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-primary" data-dismiss="modal">{translate key="plugins.themes.classic.close"}</button>
							</div>
						</div>
					</div>
				</div>
			{/if}
		</div>
	{/if}
	{call_hook name="Templates::Article::Details"}

</aside>


