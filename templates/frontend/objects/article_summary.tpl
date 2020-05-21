{**
 * templates/frontend/objects/article_summary.tpl
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief View of an Article summary which is shown within a list of articles.
 *
 * @uses $article Article The article
 * @uses $hasAccess bool Can this user access galleys for this context? The
 *       context may be an issue or an article
 * @uses $showDatePublished bool Show the date this article was published?
 * @uses $hideGalleys bool Hide the article galleys for this article?
 * @uses $primaryGenreIds array List of file genre ids for primary file types
 *}
{assign var=articlePath value=$article->getBestId()}

{if (!$section.hideAuthor && $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_DEFAULT) || $article->getHideAuthor() == $smarty.const.AUTHOR_TOC_SHOW}
	{assign var="showAuthor" value=true}
{/if}

<article class="article">
	<div class="row">
		{if $article->getLocalizedCoverImage() && $requestedOp !== "search"}
			<div class="col-md-4">
				<figure class="article__img">
					<a {if $journal}href="{url journal=$journal->getPath() page="article" op="view" path=$articlePath}"{else}href="{url page="article" op="view" path=$articlePath}"{/if} class="file">
						<img class="img-fluid" src="{$article->getLocalizedCoverImageUrl()|escape}"{if $article->getLocalizedCoverImageAltText() != ''} alt="{$article->getLocalizedCoverImageAltText()|escape}"{else} alt="{translate key="article.coverPage.altText"}"{/if}>
					</a>
				</figure>
			</div>
		{/if}
		<div class="col-md-{if $requestedOp === "search"}12{else}8{/if}{if !$article->getLocalizedCoverImage()} offset-md-4{/if}">
			{if $showAuthor}
				<p class="article__meta">{$article->getAuthorString()|escape}</p>
			{/if}

			<h4 class="article__title">
				<a {if $journal}href="{url journal=$journal->getPath() page="article" op="view" path=$articlePath}"{else}href="{url page="article" op="view" path=$articlePath}"{/if}>
					{if $article->getLocalizedFullTitle()}
						{$article->getLocalizedFullTitle()|escape}
					{/if}
				</a>
			</h4>

			{if !$hideGalleys}
				<ul class="article__btn-group">
					{foreach from=$article->getGalleys() item=galley}
						{if $primaryGenreIds}
							{assign var="file" value=$galley->getFile()}
							{if !$galley->getRemoteUrl() && !($file && in_array($file->getGenreId(), $primaryGenreIds))}
								{continue}
							{/if}
						{/if}
						<li>
							{assign var="hasArticleAccess" value=$hasAccess}
							{if $currentContext->getSetting('publishingMode') == $smarty.const.PUBLISHING_MODE_OPEN || $article->getCurrentPublication()->getData('accessStatus') == $smarty.const.ARTICLE_ACCESS_OPEN}
								{assign var="hasArticleAccess" value=1}
							{/if}
							{include file="frontend/objects/galley_link.tpl" parent=$article hasAccess=$hasArticleAccess purchaseFee=$currentJournal->getSetting('purchaseArticleFee') purchaseCurrency=$currentJournal->getSetting('currency')}
						</li>
					{/foreach}
				</ul>
			{/if}
		</div>

		{call_hook name="Templates::Issue::Issue::Article"}
	</div>
</article>
