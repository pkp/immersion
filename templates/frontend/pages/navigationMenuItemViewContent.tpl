{**
 * frontend/pages/navigationMenuItemViewContent.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display NavigationMenuItem content 
 *}
{include file="frontend/components/header.tpl" pageTitleTranslated=$title}

<main class="container main__content">
	<div class="row">
		<div class="offset-md-1 col-md-10 offset-lg-2 col-lg-8">
			<header class="main__header">
				<h2 class="main__title">
					<span>{$title|escape}</span>
				</h2>
			</header>
			<div class="page">
				{$content}
			</div>
		</div>
	</div>
</main>

{include file="frontend/components/footer.tpl"}
