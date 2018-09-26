{**
 * templates/frontend/pages/searchAuthorDetails.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Index of published articles by author.
 *
 *}
{strip}
	{assign var="pageTitle" value="search.authorDetails"}
	{include file="frontend/components/header.tpl"}
{/strip}

<main>
	<section class="author-details__meta">
		<div class="container">
			<h1 class="author-details__title">
				{translate key="plugins.themes.immersion.author.details"}
			</h1>
			<h2 class="author-details__name">{$lastName|escape}, {$firstName|escape}{if $middleName} {$middleName|escape}{/if}
			</h2>
			{if $affiliation || $country}
			<p class="author-details__affiliation">
				{if $affiliation}{$affiliation|escape}{/if}{if $country && $affiliation}, {$country|escape}{elseif $country} {$country|escape}{/if}
			</p>
			{/if}
		</div>
	</section>

	<section class="author-details__articles">
		<div class="container">
			<div class="content-body">
				<div id="authorDetails">
					<ul class="author-details__list">
						{foreach from=$publishedArticles item=article}
							{assign var=issueId value=$article->getIssueId()}
							{assign var=issue value=$issues[$issueId]}
							{assign var=issueUnavailable value=$issuesUnavailable.$issueId}
							{assign var=sectionId value=$article->getSectionId()}
							{assign var=journalId value=$article->getJournalId()}
							{assign var=journal value=$journals[$journalId]}
							{assign var=section value=$sections[$sectionId]}
							{if $issue->getPublished() && $section && $journal}
								<li>
									<article class="article">
										<div class="row">
											{if $article->getLocalizedCoverImage()}
												<div class="col-md-4">
													<figure class="article__img">
														<a {if $journal}href="{url journal=$journal->getPath() page="article" op="view" path=$articlePath}"
														   {else}href="{url page="article" op="view" path=$articlePath}"{/if}
														   class="file">
															<img class="img-fluid"
															     src="{$article->getLocalizedCoverImageUrl()|escape}"{if $article->getLocalizedCoverImageAltText() != ''} alt="{$article->getLocalizedCoverImageAltText()|escape}"{else} alt="{translate key="article.coverPage.altText"}"{/if}>
														</a>
													</figure>
												</div>
											{/if}

											<div class="col-md-8{if !$article->getLocalizedCoverImage()} offset-md-4{/if}">

												<h3 class="article__title">
													<a href="{url journal=$journal->getPath() page="article" op="view" path=$article->getBestArticleId()}">
														{$article->getLocalizedFullTitle()|strip_unsafe_html}
													</a>
												</h3>

												<p class="author-details__section-title text-muted small">
													{$section->getLocalizedTitle()|escape}
												</p>

												{if (!$issueUnavailable || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN)}
													<ul class="article__btn-group">
														{foreach from=$article->getGalleys() item=galley}
															<li>
																<a href="{url journal=$journal->getPath() page="article" op="view" path=$article->getBestArticleId()|to_array:$galley->getBestGalleyId()}"
																   class="btn btn-secondary">{$galley->getGalleyLabel()|escape}</a>
															</li>
														{/foreach}
													</ul>
												{/if}
											</div>
										</div>
									</article>
								</li>
							{/if}
						{/foreach}
					</ul>
				</div>
			</div>
		</div>
	</section>
</main>
{include file="frontend/components/footer.tpl"}

