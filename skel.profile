<?php

/**
 * @file
 * Contains skel.profile.
 */

use Symfony\Component\Yaml\Parser;

/**
 * Implements hook_install_tasks().
 */
function skel_install_tasks() {
  return [
    'skel_install_extensions' => [
      'display_name' => t('Install extensions'),
      'display' => TRUE,
      'type' => 'batch',
    ],
    'skel_import_language_config' => [
      'display_name' => t('Import language configuration'),
      'display' => TRUE,
    ],
  ];
}

/**
 * Implements hook_install_tasks_alter().
 */
function skel_install_tasks_alter(array &$tasks, array $install_state) {
  // Moves the language config import task to the end of the install tasks so
  // that it is run after the final import of languages.
  $task = $tasks['skel_import_language_config'];
  unset($tasks['skel_import_language_config']);
  $tasks = array_merge($tasks, ['skel_import_language_config' => $task]);
}

/**
 * Install task callback; prepares a batch job to install Skel extensions.
 *
 * @param array $install_state
 *   The current install state.
 *
 * @return array
 *   The batch job definition.
 */
function skel_install_extensions(array &$install_state) {
  $batch = [];
  $modules = [
    'skel_ext',
  ];
  foreach ($modules as $module) {
    $batch['operations'][] = ['skel_install_module', (array) $module];
  }
  return $batch;
}

/**
 * Batch API callback. Installs a module.
 *
 * @param string|array $module
 *   The name(s) of the module(s) to install.
 */
function skel_install_module($module) {
  \Drupal::service('module_installer')->install((array) $module);
}

/**
 * Install task callback; prepares a batch job to import language config.
 *
 * @param array $install_state
 *   The current install state.
 */
function skel_import_language_config(array &$install_state) {
  $language_manager = \Drupal::languageManager();
  $yaml_parser = new Parser();

  // The language code of the default locale.
  $site_default_langcode = $language_manager->getDefaultLanguage()->getId();
  // The directory where the language config files reside.
  $language_config_directory = __DIR__ . '/config/install/language';

  // Sub-directory names (language codes).
  // The language code of the default language is excluded. If the user
  // chooses to install in French etc, the language config is imported by core
  // and the user has the chance to override it during the installation.
  $langcodes = array_diff(scandir($language_config_directory),
    ['..', '.', $site_default_langcode]);

  foreach ($langcodes as $langcode) {
    // All .yml files in the language's config subdirectory.
    $config_files = glob("$language_config_directory/$langcode/*.yml");

    foreach ($config_files as $file_name) {
      // Information from the .yml file as an array.
      $yaml = $yaml_parser->parse(file_get_contents($file_name));
      // Uses the base name of the .yml file to get the config name.
      $config_name = basename($file_name, '.yml');

      /** @var \Drupal\language\ConfigurableLanguageManager $language_manager */
      $config = $language_manager->getLanguageConfigOverride($langcode, $config_name);

      foreach ($yaml as $config_key => $config_value) {
        // Updates the configuration object.
        $config->set($config_key, $config_value);
      }

      // Saves the configuration.
      $config->save();
    }
  }
}
