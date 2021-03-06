<?php

namespace Bonnier\WP\Purchase\Settings;

use Exception;
use PLL_Language;

class SettingsPage
{
    const SETTINGS_KEY = 'bonnier_purchase_settings';
    const SETTINGS_GROUP = 'bonnier_purchase_settings_group';
    const SETTINGS_SECTION = 'bonnier_purchase_settings_section';
    const SETTINGS_PAGE = 'bonnier_purchase_settings_page';
    const NOTICE_PREFIX = 'Bonnier Purchase:';

    private $settingsFields = [
        'purchase_url' => [
            'type' => 'text',
            'name' => 'Purchase Manager URL',
        ],
        'subscription_url' => [
            'type' => 'text',
            'name' => 'Subscription Offer Highlighted URL',
        ],
        'site_id' => [
            'type' => 'text',
            'name' => 'Site Manager Site ID',
        ],
    ];

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $settingsValues;

    /**
     * Start up
     */
    public function __construct()
    {
        $this->settingsValues = get_option(self::SETTINGS_KEY);
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    function print_error($error)
    {
        $out = "<div class='error settings-error notice is-dismissible'>";
        $out .= "<strong>" . self::NOTICE_PREFIX . "</strong><p>$error</p>";
        $out .= "</div>";
        print $out;
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            'Bonnier Purchases',
            'manage_options',
            self::SETTINGS_PAGE,
            array($this, 'create_admin_page')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property

        ?>
        <div class="wrap">
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields(self::SETTINGS_GROUP);
                do_settings_sections(self::SETTINGS_PAGE);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function register_settings()
    {
        if ($this->languages_is_enabled()) {
            $this->enable_language_fields();
        }

        register_setting(
            self::SETTINGS_GROUP, // Option group
            self::SETTINGS_KEY, // Option name
            array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
            self::SETTINGS_SECTION, // ID
            'Bonnier Purchase Settings', // Title
            array($this, 'print_section_info'), // Callback
            self::SETTINGS_PAGE // Page
        );

        foreach ($this->settingsFields as $settingsKey => $settingField) {
            add_settings_field(
                $settingsKey, // ID
                $settingField['name'], // Title
                array($this, $settingsKey), // Callback
                self::SETTINGS_PAGE, // Page
                self::SETTINGS_SECTION // Section
            );
        }
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     * @return array
     */
    public function sanitize($input)
    {
        $sanitizedInput = [];

        foreach ($this->settingsFields as $fieldKey => $settingsField) {
            if (isset($input[$fieldKey])) {
                if ($settingsField['type'] === 'text' || $settingsField['type'] === 'select') {
                    $sanitizedInput[$fieldKey] = sanitize_text_field($input[$fieldKey]);
                }
            }
        }

        return $sanitizedInput;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /**
     * Catch callbacks for creating setting fields
     * @param string $function
     * @param array $arguments
     * @return bool
     */
    public function __call($function, $arguments)
    {
        if (!isset($this->settingsFields[$function])) {
            return false;
        }

        $field = $this->settingsFields[$function];
        $this->create_settings_field($field, $function);

    }

    public function get_setting_value($settingKey, $locale = null)
    {
        if(is_null($locale)) {
            $locale = $this->get_current_locale();
        }
        if(!$this->settingsValues) {
            $this->settingsValues = get_option(self::SETTINGS_KEY);
        }

        if ($locale) {
            $settingKey = $locale . '_' . $settingKey;
        }

        if (isset($this->settingsValues[$settingKey]) && !empty($this->settingsValues[$settingKey])) {
            return $this->settingsValues[$settingKey];
        }
        return false;
    }

    public function getPurchaseURL($locale = null)
    {
        if(!$locale) {
            $locale = $this->get_current_locale();
        }
        return $this->get_setting_value('purchase_url', $locale) ?: '';
    }

    public function getSiteId($locale = null)
    {
        if(!$locale) {
            $locale = $this->get_current_locale();
        }
        return $this->get_setting_value('site_id', $locale) ?: '';
    }

    public function getSubscriptionURL($locale = null)
    {
        if(!$locale) {
            $locale = $this->get_current_locale();
        }

        return $this->get_setting_value('subscription_url', $locale) ?: '';
    }

    private function enable_language_fields()
    {
        $languageEnabledFields = [];

        foreach ($this->get_languages() as $language) {
            foreach ($this->settingsFields as $fieldKey => $settingsField) {

                $localeFieldKey = $language->locale . '_' . $fieldKey;
                $languageEnabledFields[$localeFieldKey] = $settingsField;
                $languageEnabledFields[$localeFieldKey]['name'] .= ' ' . $language->locale;
                $languageEnabledFields[$localeFieldKey]['locale'] = $language->locale;

            }
        }

        $this->settingsFields = $languageEnabledFields;

    }

    public function languages_is_enabled()
    {
        return function_exists('Pll') && PLL()->model->get_languages_list();
    }

    public function get_languages()
    {
        if ($this->languages_is_enabled()) {
            return PLL()->model->get_languages_list();
        }
        return false;
    }

    /**
     * Get the current language by looking at the current HTTP_HOST
     *
     * @return null|PLL_Language
     */
    public function get_current_language()
    {
        if ($this->languages_is_enabled()) {
            return PLL()->model->get_language(pll_current_language());
        }
        return null;
    }

    public function get_current_locale() {
        $currentLang = $this->get_current_language();
        return $currentLang ? $currentLang->locale : null;
    }

    private function create_settings_field($field, $fieldKey)
    {
        $fieldName = self::SETTINGS_KEY . "[$fieldKey]";
        $fieldOutput = false;

        if ($field['type'] === 'text') {
            $fieldValue = isset($this->settingsValues[$fieldKey]) ? esc_attr($this->settingsValues[$fieldKey]) : '';
            $fieldOutput = "<input type='text' name='$fieldName' value='$fieldValue' class='regular-text' />";
        }

        if ($fieldOutput) {
            print $fieldOutput;
        }
    }

}
