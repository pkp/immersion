{**
 * plugins/generic/orcidProfile/templates/orcidVerify.tpl
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Copyright (c) 2018-2020 University Library Heidelberg
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Page template to display from the OrcidHandler to show ORCID verification success or failure.
 *}
{include file="frontend/components/header.tpl"}

<main class="container main__content" id="immersion_content_main">
	<div class="row">
		<div class="offset-md-1 col-md-10 offset-lg-2 col-lg-8">
			<header class="main__header">
				<h1 class="main__title">
					<span>{translate key="orcid.verify.title"}</span>
				</h1>
			</header>

			<div class="content-body">
				<div class="description">
				{if $verifySuccess}
					<p>
						<span class="orcid"><a href="{$orcid|escape}" target="_blank">{$orcidIcon}{$orcid|escape}</a></span>
					</p>
					<div class="orcid-success">
					{translate key="orcid.verify.success"}
					</div>
					{if $sendSubmission}
						{if $sendSubmissionSuccess}
							<div class="orcid-success">
							{translate key="orcid.verify.sendSubmissionToOrcid.success"}
							</div>
						{else}
							<div class="orcid-failure">
							{translate key="orcid.verify.sendSubmissionToOrcid.failure"}
							</div>
						{/if}
					{elseif $submissionNotPublished}
						{translate key="orcid.verify.sendSubmissionToOrcid.notpublished"}
					{/if}
				{else}
					<div class="orcid-failure">
					{if $denied}
						{translate key="orcid.authDenied"}
					{elseif $authFailure}
						{translate key="orcid.authFailure"}
					{elseif $duplicateOrcid}
						{translate key="orcid.verify.duplicateOrcid"}
					{else}
						{translate key="orcid.verify.failure"}
					{/if}
					</div>
					{translate key="orcid.failure.contact"}
				{/if}
				</div>
			</div>
		</div>
	</div>
</main>

{include file="frontend/components/footer.tpl"}
