{**
 * templates/frontend/pages/indexJournal.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the index page for a journal
 *
 * @uses $currentJournal Journal This journal
 * @uses $journalDescription string Journal description from HTML text editor
 * @uses $homepageImage object Image to be displayed on the homepage
 * @uses $additionalHomeContent string Arbitrary input from HTML text editor
 * @uses $announcements array List of announcements
 * @uses $numAnnouncementsHomepage int Number of announcements to display on the
 *       homepage
 * @uses $issue Issue Current issue
 * @uses $issueIdentificationString string issue identification that relies on user's settings
 * @uses $lastSectionColor string background color of the last section presented on the index page
 * @uses $immersionAnnouncementsColor string background color of the announcements section
 *}

{include file="frontend/components/header.tpl" pageTitleTranslated=$currentJournal->getLocalizedName()}

<main id="immersion_content_main">

	{call_hook name="Templates::Index::journal"}

	{if $showJournalDescription}
		<section class="journal-description{if $isJournalDescriptionDark} section_dark{/if}"{if $journalDescriptionColour} style="background-color: {$journalDescriptionColour|escape};"{/if}>
			<div class="container">
				<div class="row justify-content-between">
					<header class="col-md-6 col-lg-3">
						<h3 class="text-capitalize">
							{translate key="plugins.themes.immersion.about.journal"}
						</h3>
					</header>
					<div class="col-md-6 col-lg-8">
						{$currentJournal->getLocalizedDescription()}
					</div>
				</div>
			</div>
		</section>
	{/if}
	{* Announcements *}
	{if $announcements}
		<section class="announcements{if $isAnnouncementDark} section_dark{/if}"{if $immersionAnnouncementsColor} style="background-color: {$immersionAnnouncementsColor|escape};"{/if}>
			<div class="container">
				<header class="row issue-section__header">
					<h3 class="col-md-6 col-lg-3 announcement-section__title{if $showJournalDescription} mt-0{/if}">
						{translate key="announcement.announcements"}
					</h3>
				</header>

				<ul class="row announcement-section__toc">
					{foreach from=$announcements item=announcement}
						<li class="col-md-4">
							<p class="announcement__date">{$announcement->getDatePosted()|date_format:$dateFormatShort|escape}</p>
							<h4 class="announcement__title">
								<a href="{url router=$smarty.const.ROUTE_PAGE page="announcement" op="view" path=$announcement->getId()|escape}">
									{$announcement->getLocalizedTitle()|escape}
								</a>
							</h4>
						</li>
					{/foreach}
				</ul>
			</div>
		</section>
	{/if}

	{if $issue}
		<section class="issue">

			{* Latest issue *}
			{include file="frontend/objects/issue_toc.tpl"}

		</section>
	{/if}
</main><!-- .page -->

{* Additional Homepage Content *}
{if $additionalHomeContent}
	<aside {if $lastSectionColor}style="background-color: {$lastSectionColor|escape};"{/if}>
		<div class="container additional-home-content">
			{$additionalHomeContent|strip_unsafe_html}
		</div>
	</aside>
{/if}

{include file="frontend/components/footer.tpl"}
