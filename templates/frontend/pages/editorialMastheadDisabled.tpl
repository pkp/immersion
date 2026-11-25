{**
 * templates/frontend/pages/editorialMastheadDisabled.tpl
 *
 * Copyright (c) 2026 Simon Fraser University
 * Copyright (c) 2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Display context's editorial masthead page when the enrollment-based masthead is disabled.
 *
 *}
{include file="frontend/components/header.tpl" pageTitle="common.editorialMasthead"}
<main class="container page page_masthead" id="immersion_content_main">
	<div class="row">
		<div class="offset-md-1 col-md-10 offset-lg-2 col-lg-8">
			<header class="main__header">
				<h1 class="main__title">
					{translate key="common.editorialMasthead"}
				</h1>
				{include file="frontend/components/editLink.tpl" page="management" op="settings" path="context" anchor="masthead" sectionTitleKey="common.editorialMasthead"}
			</header>
			<div class="content-body">
				{$currentContext->getLocalizedData('editorialHistory')}
			</div>
		</div>
	</div>
</main><!-- .page -->

{include file="frontend/components/footer.tpl"}
