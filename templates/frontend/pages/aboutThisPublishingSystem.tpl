{**
 * templates/frontend/pages/aboutThisPublishingSystem.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the page to view details about the OJS software.
 *
 * @uses $currentJournal Journal The journal currently being viewed
 * @uses $appVersion string Current version of OJS
 * @uses $pubProcessFile string Path to image of OJS publishing process
 *}
{include file="frontend/components/header.tpl" pageTitle="about.aboutThisPublishingSystem"}

<main class="container main__content">
	<div class="row">
		<div class="offset-md-1 col-md-10 offset-lg-2 col-lg-8">
			<header class="main__header">
				<h1 class="main__title">
					<span>{translate key="about.aboutThisPublishingSystem"}</span>
				</h1>
			</header>

			<div class="content-body">
				<p>
					{if $currentJournal}
						{translate key="about.aboutOJSJournal" ojsVersion=$appVersion}
					{else}
						{translate key="about.aboutOJSSite" ojsVersion=$appVersion}
					{/if}
				</p>

				<img class="img-fluid" src="{$baseUrl}/{$pubProcessFile}" alt="{translate key="about.aboutThisPublishingSystem.altText"}">
			</div>
		</div>
	</div>
</main>

{include file="frontend/components/footer.tpl"}
