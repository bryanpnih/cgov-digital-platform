<?php

/**
 * @file
 * Local multisite development with ACSF configuration.
 *
 * This is an NCI specific file since we have ACSF, but use ACE for
 * development and QA. ACE affords us the opporunity to have multiple
 * development tiers. Normally ACSF and multisite do not work together.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 */

// If this is ACSF then we need to exit.
if (!empty($_ENV['AH_SITE_GROUP']) && !empty($_ENV['AH_SITE_ENVIRONMENT']) && function_exists('gardens_site_data_get_filepath') && file_exists(gardens_site_data_get_filepath())) {
  return;
}

// Check if we are within an Acquia ACE environment.
if (!empty($_ENV['AH_SITE_GROUP']) && !empty($_ENV['AH_SITE_ENVIRONMENT'])) {
  return;
}

// If we are requesting the default site, or no site can be found, then we
// will use the default site. The default site is the microsite demo.

if (PHP_SAPI === 'cli') {
  // We need to set the acsf_site_name here as well.
  return;
}
else {
  // We must be a local install. Local installs should be SITEDIR.devbox, with
  // microsite.devbox being default.
  $host = rtrim($_SERVER['HTTP_HOST'], '.');
  $sites[$_SERVER['HTTP_HOST']] = $host;
}

// In order to ensure that the correct DB is used without affecting ACSF, we will
// set the $_acsf_site_name var used in local.settings.php.
global $_acsf_site_name;
$_acsf_site_name = $host; // NOT if default

