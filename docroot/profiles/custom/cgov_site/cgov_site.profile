<?php

/**
 * @file
 * Enables configuration tasks for a cgov site install.
 *
 * So we have had a number of fights with the ordering of various
 * translation files and configs installed.
 *
 * Some of the fighting is due to the fact that the installation
 * interface requires translation. So adding additional languages gets
 * wiped out after the site profile gets installed. If we enable AFTER
 * the install, then we should be able to enable languages cleanly. Meaning
 * the right stuff just happens.
 *
 * The install tasks below will be called after everything has been installed,
 * and hopefully ensures the correct configs are installed.
 *
 * Additionally, thanks to https://www.drupal.org/project/drupal/issues/3023076,
 * local translation packs are not imported. Remote packs have a challenge from
 * a pure code standpoint as they are handled in batch processes.
 *
 * So we will mimick the behavior of the locale module's override in,
 * locale.module local_form_language_admin_add_form_alter_submit(). This created
 * the proper batches to download (remote) translation packs and import both
 * local and remote packs.
 */

use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Implements hook_install_tasks().
 */
function cgov_site_install_tasks(&$install_state) {

  // WARNING: This code assumes we are installing Spanish. If this
  // profile no longer install Spanish, all this logic should go
  // away/somewhere else.
  //
  // As we are installing Spanish, now we need to indicate that the task
  // to download and import translations MAY need to run.
  // Adding it here in case we ever make installing Spanish a
  // parameter to the profile, then we will not need to restructure
  // and re-learn what we can do with install tasks.
  //
  // We could just hardcode this to true, but it is only done
  // under certain conditions depending on locale configuration.
  // So we shall use the same logic from locale.module.
  $translation_import_enabled = \Drupal::config('locale.settings')
    ->get('translation.import_enabled');

  return [
    // Enable Spanish Language.
    'cgov_site_enable_spanish' => [],
    // Batch to download ad import translations.
    'cgov_site_download_and_import_translations' => [
      'display_name' => t('Importing translations'),
      'display' => $translation_import_enabled,
      'type' => 'batch',
      'run' => $translation_import_enabled ? INSTALL_TASK_RUN_IF_NOT_COMPLETED : INSTALL_TASK_SKIP,
    ],
    // Batch to update translation configuration.
    'cgov_site_update_translations' => [
      'display_name' => t('Updating translations'),
      'display' => $translation_import_enabled,
      'type' => 'batch',
      'run' => $translation_import_enabled ? INSTALL_TASK_RUN_IF_NOT_COMPLETED : INSTALL_TASK_SKIP,
    ],
  ];
}

/**
 * Implements hook_install_tasks_alter().
 */
function cgov_site_install_tasks_alter(&$tasks, $install_state) {
  // This is a bit of Ninjary found in the multilingual demo profile,
  // multilingual_demo. This makes sure the install tasks are performed
  // at the very end. If this is not here, the install tasks are actually
  // run before final core installation tasks.
  $es_task = $tasks['cgov_site_enable_spanish'];
  unset($tasks['cgov_site_enable_spanish']);

  $import_task = $tasks['cgov_site_download_and_import_translations'];
  unset($tasks['cgov_site_download_and_import_translations']);
  $update_task = $tasks['cgov_site_update_translations'];
  unset($tasks['cgov_site_update_translations']);

  $tasks = array_merge($tasks, [
    'cgov_site_enable_spanish' => $es_task,
    'cgov_site_download_and_import_translations' => $import_task,
    'cgov_site_update_translations' => $update_task,
  ]);
}

/**
 * Install task callback to enable spanish.
 */
function cgov_site_enable_spanish() {
  printf("BEGIN: Install spanish in profile\n");
  // Upon installation of a language, all translated configs
  // will be loaded.
  $spanish = ConfigurableLanguage::createFromLangcode('es');
  $spanish->save();
  printf("END: Install spanish in profile\n");
}

/**
 * Download and import Spanish translation packs.
 */
function cgov_site_download_and_import_translations() {
  printf("Running download and import\n");

  \Drupal::moduleHandler()->loadInclude('local', 'fetch.inc');
  $options = _cgov_site_translation_default_update_options();

  // This builds a batch to check, download and import project translations.
  $batch = locale_translation_batch_update_build([], ['es'], $options);
  return $batch;
}

/**
 * Update translations.
 */
function cgov_site_update_translations() {
  printf("Running update\n");

  \Drupal::moduleHandler()->loadInclude('locale', 'bulk.inc');
  $options = _cgov_site_translation_default_update_options();

  // This "Builds a locale batch to refresh configuration".
  // I *think* this tells locale what has been updated and when.
  $batch = locale_config_batch_update_components($options, ['es']);
  return $batch;
}

/**
 * Returns default import options for translation update.
 *
 * @return array
 *   Array of translation import options.
 */
function _cgov_site_translation_default_update_options() {
  \Drupal::moduleHandler()->loadInclude('locale', 'translation.inc');

  // Call the options function from locale.
  return _locale_translation_default_update_options();
}
