<?php

/**
 * @file plugins/themes/immersion/ImmersionPlugin.php
 *
 * Copyright (c) 2014-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ImmersionPlugin
 *
 * @ingroup plugins_themes_immersion
 *
 * @brief Immersion theme
 */

namespace APP\plugins\themes\immersion;

use APP\controllers\grid\issues\form\IssueForm;
use APP\core\Request;
use APP\facades\Repo;
use APP\section\Section;
use APP\template\TemplateManager;
use PKP\config\Config;
use PKP\core\PKPSessionGuard;
use PKP\db\DAORegistry;
use PKP\facades\Locale;
use PKP\form\validation\FormValidatorAltcha;
use PKP\plugins\Hook;
use PKP\plugins\PluginSettingsDAO;
use PKP\plugins\ThemePlugin;
use PKP\template\PKPTemplateManager;

class ImmersionPlugin extends ThemePlugin
{
    public function init()
    {
        // Adding styles (JQuery UI, Bootstrap, Tag-it)
        $this->addStyle('app-css', 'resources/dist/app.min.css');
        $this->addStyle('less', 'resources/less/import.less');

        // Styles for HTML galleys
        $this->addStyle('htmlGalley', 'templates/plugins/generic/htmlArticleGalley/css/default.less', ['contexts' => 'htmlGalley']);

        // Adding scripts (JQuery, Popper, Bootstrap, JQuery UI, Tag-it, Theme's JS)
        $this->addScript('app-js', 'resources/dist/app.min.js');

        // Add navigation menu areas for this theme
        $this->addMenuArea(['primary', 'user']);

        // Option to show section description on the journal's homepage; turned off by default
        $this->addOption('sectionDescriptionSetting', 'FieldOptions', [
            'label' => __('plugins.themes.immersion.options.sectionDescription.label'),
            'description' => __('plugins.themes.immersion.options.sectionDescription.description'),
            'type' => 'radio',
            'options' => [
                ['value' => 'disable', 'label' => __('plugins.themes.immersion.options.sectionDescription.disable')],
                ['value' => 'enable', 'label' => __('plugins.themes.immersion.options.sectionDescription.enable')]
            ]
        ]);

        $this->addOption('journalDescription', 'FieldOptions', [
            'label' => __('plugins.themes.immersion.options.journalDescription.label'),
            'description' => __('plugins.themes.immersion.options.journalDescription.description'),
            'type' => 'radio',
            'options' => [
                ['value' => 0, 'label' => __('plugins.themes.immersion.options.journalDescription.disable')],
                ['value' => 1, 'label' => __('plugins.themes.immersion.options.journalDescription.enable')]
            ]
        ]);

        $this->addOption('journalDescriptionColour', 'FieldColor', [
            'label' => __('plugins.themes.immersion.options.journalDescriptionColour.label'),
            'description' => __('plugins.themes.immersion.options.journalDescriptionColour.description'),
            'default' => '#000',
        ]);

        $this->addOption('immersionAnnouncementsColor', 'FieldColor', [
            'label' => __('plugins.themes.immersion.announcements.colorPick'),
            'default' => '#000',
        ]);

        $this->addOption('abstractsOnIssuePage', 'FieldOptions', [
            'type' => 'radio',
            'label' => __('plugins.themes.immersion.option.abstractsOnIssuePage.label'),
            'description' => __('plugins.themes.immersion.option.abstractsOnIssuePage.description'),
            'tooltip' => __('plugins.themes.immersion.option.abstractsOnIssuePage.tooltip'),
            'options' => [
                ['value' => 'noAbstracts', 'label' => __('plugins.themes.immersion.option.abstractsOnIssuePage.noAbstracts')],
                ['value' => 'fadeoutAbstracts', 'label' => __('plugins.themes.immersion.option.abstractsOnIssuePage.fadeoutAbstracts')],
                ['value' => 'fullAbstracts', 'label' => __('plugins.themes.immersion.option.abstractsOnIssuePage.fullAbstracts')],
            ],
            'default' => 'noAbstracts',
        ]);
        // Add usage stats display options
        $this->addOption('displayStats', 'FieldOptions', [
            'type' => 'radio',
            'label' => __('plugins.themes.immersion.option.displayStats.label'),
            'options' => [
                ['value' => 'none', 'label' => __('plugins.themes.immersion.option.displayStats.none')],
                ['value' => 'bar', 'label' => __('plugins.themes.immersion.option.displayStats.bar')],
                ['value' => 'line', 'label' => __('plugins.themes.immersion.option.displayStats.line')]
            ],
            'default' => 'none',
        ]);

        // Additional data to the templates
        Hook::add('TemplateManager::display', $this->initializeTemplate(...));
        Hook::add('TemplateManager::display', $this->addIssueTemplateData(...));
        Hook::add('TemplateManager::display', $this->addSiteWideData(...));
        Hook::add('TemplateManager::display', $this->homepageAnnouncements(...));
        Hook::add('TemplateManager::display', $this->homepageJournalDescription(...));
        Hook::add('issueform::display', $this->addToIssueForm(...));

        // add abstract fade-out styles
        Hook::add('TemplateManager::display', $this->addStyles(...));

        // Additional variable for the issue form
        Hook::add('Schema::get::issue', $this->addToSchema(...));
        Hook::add('issueform::initdata', $this->initDataIssueFormFields(...));
        Hook::add('issueform::readuservars', $this->readIssueFormFields(...));
        Hook::add('issueform::execute', $this->executeIssueFormFields(...));
        Hook::add('Templates::Editor::Issues::IssueData::AdditionalMetadata', $this->callbackTemplateIssueForm(...));

        // Load colorpicker on issue management page
        $this->addStyle('spectrum', '/resources/dist/spectrum-1.8.0.css', ['contexts' => 'backend-manageIssues']);
        $this->addScript('spectrum', '/resources/dist/spectrum-1.8.0.js', ['contexts' => 'backend-manageIssues']);
    }

    /**
     * Initialize Template
     *
     * @param array{TemplateManager,string,string} $args TemplateManager, &template, &output
     */
    public function initializeTemplate(string $hookName, array $args): bool
    {
        [$templateMgr] = $args;
        // The login link displays the login form in a modal, therefore the reCAPTCHA must be available for all frontend routes
        $isCaptchaEnabled = Config::getVar('captcha', 'recaptcha') && Config::getVar('captcha', 'captcha_on_login');
        if ($isCaptchaEnabled) {
            $locale = substr(Locale::getLocale(), 0, 2);
            $templateMgr->addJavaScript('recaptcha', "https://www.recaptcha.net/recaptcha/api.js?hl={$locale}");
            $templateMgr->assign('recaptchaPublicKey', Config::getVar('captcha', 'recaptcha_public_key'));
        }

        $isAltchaEnabled = Config::getVar('captcha', 'altcha') && Config::getVar('captcha', 'altcha_on_login');
        if ($isAltchaEnabled) {
            FormValidatorAltcha::addAltchaJavascript($templateMgr);
            FormValidatorAltcha::insertFormChallenge($templateMgr);
        }

        return Hook::CONTINUE;
    }

    /**
     * Get the display name of this theme
     */
    public function getDisplayName(): string
    {
        return __('plugins.themes.immersion.name');
    }

    /**
     * Get the description of this plugin
     */
    public function getDescription(): string
    {
        return __('plugins.themes.immersion.description');
    }

    /**
     * Add section-specific data to the indexJournal and issue templates
     *
     * @param array{TemplateManager,string,string} $args TemplateManager, &template, &output
     */

    public function addIssueTemplateData(string $hookName, array $args): bool
    {
        [$templateMgr, $template] = $args;
        $request = $this->getRequest();

        if ($template !== 'frontend/pages/issue.tpl' && $template !== 'frontend/pages/indexJournal.tpl') {
            return Hook::CONTINUE;
        }

        $context = $request->getContext();
        $contextId = $context->getId();

        $issue = $template === 'frontend/pages/indexJournal.tpl' ? Repo::issue()->getCurrent($contextId) : $templateMgr->getTemplateVars('issue');
        if (!$issue) {
            return Hook::CONTINUE;
        }

        $publishedSubmissionsInSection = $templateMgr->getTemplateVars('publishedSubmissions');

        // we need to set this even if no section colors are set
        $templateMgr->assign('showAbstractsOnIssuePage', $this->getOption('abstractsOnIssuePage'));

        // Section color
        $immersionSectionColors = $issue->getData('immersionSectionColor');
        if (empty($immersionSectionColors)) {
            return Hook::CONTINUE;
        } // Section background colors aren't set

        $sections = Repo::section()->getByIssueId($issue->getId());
        $lastSectionColor = null;

        // Section description; check if this option and BrowseBySection plugin is enabled
        $sectionDescriptionSetting = $this->getOption('sectionDescriptionSetting');
        $pluginSettingsDAO = DAORegistry::getDAO('PluginSettingsDAO'); /** @var PluginSettingsDAO $pluginSettingsDAO */
        $browseBySectionSettings = $pluginSettingsDAO->getPluginSettings($contextId, 'browsebysectionplugin');
        $isBrowseBySectionEnabled = false;
        if (!empty($browseBySectionSettings) && array_key_exists('enabled', $browseBySectionSettings) && $browseBySectionSettings['enabled']) {
            $isBrowseBySectionEnabled = true;
        }

        $locale = Locale::getLocale();
        foreach ($publishedSubmissionsInSection as $sectionId => $publishedArticlesBySection) {
            foreach ($sections as $section) { /** @var Section $section */
                if ($section->getId() != $sectionId) {
                    continue;
                }

                // Set section and its background color
                $publishedSubmissionsInSection[$sectionId]['section'] = $section;
                $publishedSubmissionsInSection[$sectionId]['sectionColor'] = $immersionSectionColors[$sectionId];

                // Check if section background color is dark
                $isSectionDark = $immersionSectionColors[$sectionId] && $this->isColourDark($immersionSectionColors[$sectionId]);
                $publishedSubmissionsInSection[$sectionId]['isSectionDark'] = $isSectionDark;

                // Section description
                if ($sectionDescriptionSetting == 'enable' && $isBrowseBySectionEnabled && $section->getData('browseByDescription', $locale)) {
                    $publishedSubmissionsInSection[$sectionId]['sectionDescription'] = $section->getData('browseByDescription', $locale);
                }

                // Need only the color of the last section that contains articles
                if ($publishedSubmissionsInSection[$sectionId]['articles'] && $immersionSectionColors[$sectionId]) {
                    $lastSectionColor = $immersionSectionColors[$sectionId];
                }
            }
        }

        $templateMgr->assign([
            'publishedSubmissions' => $publishedSubmissionsInSection,
            'lastSectionColor' => $lastSectionColor
        ]);
        return Hook::CONTINUE;
    }

    /**
     * Background color for announcements section on the journal index page
     *
     * @param array{TemplateManager,string,string} $args TemplateManager, &template, &output
     */
    public function homepageAnnouncements(string $hookName, array $args): bool
    {
        [$templateMgr, $template] = $args;

        if ($template !== 'frontend/pages/indexJournal.tpl') {
            return Hook::CONTINUE;
        }

        /** @var Request $request */
        $request = $this->getRequest();
        $journal = $request->getJournal();

        // Announcements on index journal page
        $announcementsIntro = $journal->getLocalizedData('announcementsIntroduction');
        $immersionAnnouncementsColor = $this->getOption('immersionAnnouncementsColor');

        $isAnnouncementDark = $immersionAnnouncementsColor && $this->isColourDark($immersionAnnouncementsColor);

        $templateMgr->assign([
            'announcementsIntroduction' => $announcementsIntro,
            'isAnnouncementDark' => $isAnnouncementDark,
            'immersionAnnouncementsColor' => $immersionAnnouncementsColor
        ]);
        return Hook::CONTINUE;
    }

    /**
     * Assign additional data to Smarty templates
     *
     * @param array{TemplateManager,string,string} $args TemplateManager, &template, &output
     */
    public function addSiteWideData(string $hookName, array $args): bool
    {
        [$templateMgr] = $args;

        /** @var Request $request */
        $request = $this->getRequest();
        $journal = $request->getJournal();

        if (PKPSessionGuard::isSessionDisable()) {
            return Hook::CONTINUE;
        }

        // Check locales
        $locales = $journal ? $journal->getSupportedLocaleNames() : $request->getSite()->getSupportedLocaleNames();

        // Load login form
        $loginUrl = $request->url(null, 'login', 'signIn');
        if (Config::getVar('security', 'force_login_ssl')) {
            $loginUrl = preg_replace('/^http:/u', 'https:', $loginUrl);
        }

        if ($journal) {
            $templateMgr->assign('immersionHomepageImage', $journal->getLocalizedData('homepageImage'));
        }

        $templateMgr->assign([
            'languageToggleLocales' => $locales,
            'loginUrl' => $loginUrl
        ]);
        return Hook::CONTINUE;
    }

    /**
     * Show Journal Description on the journal landing page depending on theme settings
     *
     * @param array{TemplateManager,string,string} $args TemplateManager, &template, &output
     */
    public function homepageJournalDescription(string $hookName, array $args): bool
    {
        $templateMgr = $args[0];
        $template = $args[1];

        if ($template != 'frontend/pages/indexJournal.tpl') {
            return Hook::CONTINUE;
        }

        $journalDescriptionColour = $this->getOption('journalDescriptionColour');
        $isJournalDescriptionDark = $journalDescriptionColour && $this->isColourDark($journalDescriptionColour);

        $templateMgr->assign([
            'showJournalDescription' => $this->getOption('journalDescription'),
            'journalDescriptionColour' => $journalDescriptionColour,
            'isJournalDescriptionDark' => $isJournalDescriptionDark
        ]);
        return Hook::CONTINUE;
    }

    /**
     * Add section settings to IssueDAO
     *
     * @param array{object} $args [Schema object]
     */
    public function addToSchema(string $hookName, array $args): bool
    {
        [$schema] = $args;
        $prop = '{
            "type": "array",
            "multilingual": false,
            "apiSummary": true,
            "validation": [
                "nullable"
            ],
            "items": {
                "type": "string"
            }
        }';
        $schema->properties->{'immersionSectionColor'} = json_decode($prop);
        return Hook::CONTINUE;
    }


    /**
     * Initialize data when form is first loaded
     *
     * @param array{IssueForm} $args
     */
    public function initDataIssueFormFields(string $hookName, array $args): bool
    {
        [$issueForm] = $args;
        $issueForm->setData('immersionSectionColor', $issueForm->issue->getData('immersionSectionColor'));
        return Hook::CONTINUE;
    }

    /**
     * Read user input from additional fields in the issue editing form
     *
     * @param array{IssueForm,array} $args [IssueForm, array of user vars]
     */
    public function readIssueFormFields(string $hookName, array $args): bool
    {
        [$issueForm] = $args;
        $request = $this->getRequest();

        $issueForm->setData('immersionSectionColor', $request->getUserVar('immersionSectionColor'));
        return Hook::CONTINUE;
    }

    /**
     * Save additional fields in the issue editing form
     *
     * @param array{IssueForm,Issue} $args
     */
    public function executeIssueFormFields(string $hookName, array $args): bool
    {
        [$issueForm, $issue] = $args;

        // The issueform::execute hook fires twice, once at the start of the
        // method when no issue exists. Only update the object during the
        // second request
        if (!$issue) {
            return Hook::CONTINUE;
        }

        $issue->setData('immersionSectionColor', $issueForm->getData('immersionSectionColor'));
        return Hook::CONTINUE;
    }

    /**
     * Add variables to the issue editing form
     *
     * @param array{IssueForm,string} $args
     */
    public function addToIssueForm(string $hookName, array $args): bool
    {
        [$issueForm] = $args;

        // Display only if available as per IssueForm::fetch()
        if (!$issueForm->issue) {
            return Hook::CONTINUE;
        }

        $request = $this->getRequest();
        /** @var Section[] $sections */
        $sections = Repo::section()->getByIssueId($issueForm->issue->getId())->all();
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('sections', $sections);
        return Hook::CONTINUE;
    }

    /**
     * Add variables to the issue editing form
     *
     * @param array{array,TemplateManager,string} $args
     */
    public function callbackTemplateIssueForm(string $hookName, array $args): bool
    {
        [$params, $templateMgr, &$output] = $args;
        $output .= $templateMgr->fetch($this->getTemplateResource('issueForm.tpl'));
        return Hook::CONTINUE;
    }

    /**
     * Get all defined section colors indexed by 'issueId_sectionId'
     *
     * @param array{TemplateManager,string,string} $args TemplateManager, &template, &output
     */
    public function addStyles(string $hookName, array $args): bool
    {
        [$templateMgr, $template] = $args;

        $templates = ['frontend/pages/issue.tpl', 'frontend/pages/indexJournal.tpl'];
        if (!in_array($template, $templates)) {
            return Hook::CONTINUE;
        }

        // For the abstract fade-out effect we require a css class for each section color
        $cssOutput = '';
        $issue = $templateMgr->getTemplateVars('issue');
        if (!$issue?->getData('immersionSectionColor')) {
            return Hook::CONTINUE;
        }

        foreach ($issue->getData('immersionSectionColor') as $sectionIndex => $sectionColor) {
            $cssOutput .= ".article__abstract-fadeout-{$sectionIndex}::after {\n";
            $cssOutput .= "  background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, {$sectionColor} 100%);\n";
            $cssOutput .= "}\n\n";
        }

        $templateMgr->addStyleSheet(
            'fadeout',
            $cssOutput,
            [
                'contexts' => 'frontend',
                'inline' => true,
                'priority' => PKPTemplateManager::STYLE_SEQUENCE_LAST,
            ]
        );
        return Hook::CONTINUE;
    }
}

class_alias(ImmersionPlugin::class, 'ImmersionThemePlugin',);
class_alias(ImmersionPlugin::class, 'APP\plugins\themes\immersion\ImmersionThemePlugin',);
