{**
 * plugins/generic/immersion/templates/sectionForm.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to be included into the section form under journal management.
 *}

{fbvFormSection title="plugins.themes.immersion.colorPick" for="colorPick" inline=false size=$fbvStyles.size.MEDIUM}
	{fbvElement type="text" id="colorPick" value=$colorPick maxlength="7" label="plugins.themes.immersion.colorPickInstructions"}
{/fbvFormSection}