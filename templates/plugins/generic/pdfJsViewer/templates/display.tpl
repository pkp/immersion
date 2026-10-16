{**
 * plugins/generic/pdfJsViewer/templates/display.tpl
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Template to view a PDF or HTML galley
 *}

{**
 * plugins/generic/pdfJsViewer/templates/display.tpl
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Embedded viewing of a PDF galley.
 *}
<!DOCTYPE html>
<html lang="{$currentLocale|replace:"_":"-"}" xml:lang="{$currentLocale|replace:"_":"-"}">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>
	{if $isTitleHtml}
		{translate key="article.pageTitle" title=$title|strip_tags|escape}
	{else}
		{translate key="article.pageTitle" title=$title|escape}
	{/if}
	</title>

	{load_header context="frontend" headers=$headers}
	{load_stylesheet context="frontend" stylesheets=$stylesheets}
	{load_script context="frontend" scripts=$scripts}
</head>
<body class="pkp_page_{$requestedPage|escape} pkp_op_{$requestedOp|escape}">

{* Header wrapper *}
<header class="main__header pdf-galley__header">

	<div class="pdf-return-article">
		<a href="{$parentUrl}">
			←
			<span class="visually-hidden">
					{if $issue}
						{translate key="issue.return"}
					{else}
						{translate key="article.return"}
					{/if}
			</span>
			{if $isLatestPublication}
				{if $isTitleHtml}
					{$title|strip_unsafe_html}
				{else}
					{$title|escape}
				{/if}
			{/if}
		</a>
	</div>
	{if !$isLatestPublication}
	<div class="article-page__alert" role="alert">
		{translate key="submission.outdatedVersion"
			datePublished=$galleyPublication->getData('datePublished')|date_format:$dateFormatLong
			urlRecentVersion=$parentUrl
		}
	</div>
	{/if}
	<div class="pdf-download-button">
		<a href="{$pdfUrl}" class="btn btn-primary" download>
			<span class="label">
				{translate key="common.download"}
			</span>
			<span class="visually-hidden">
				{translate key="common.downloadPdf"}
			</span>
		</a>
	</div>

</header>

<script type="text/javascript">
	// Creating iframe's src in JS instead of Smarty so that EZProxy-using sites can find our domain in $pdfUrl and do their rewrites on it.
	document.addEventListener("DOMContentLoaded", function() {ldelim}
		var urlBase = "{$pluginUrl}/pdf.js/web/viewer.html?file=";
		var pdfUrl = {$pdfUrl|json_encode:JSON_UNESCAPED_SLASHES};
		document.querySelector("#pdfCanvasContainer > iframe").setAttribute("src", urlBase + encodeURIComponent(pdfUrl));
	{rdelim});
</script>

<div id="pdfCanvasContainer" class="galley_view">
	<iframe src="" width="100%" height="100%" style="min-height: 500px;" title="{$galleyTitle|escape}" allowfullscreen webkitallowfullscreen></iframe>
</div>
{call_hook name="Templates::Common::Footer::PageFooter"}
</body>
</html>
