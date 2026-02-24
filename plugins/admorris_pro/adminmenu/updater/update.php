<?php

namespace Plugin\admorris_pro\updater;

/**
 * @deprecated This file is deprecated and will be removed in the future. 
 * Use Plugin\admorris_pro\update\Updater instead.
 * 
 * @var \JTL\Plugin\PluginInterface $oPlugin
 * @var \JTL\Smarty\JTLSmarty $smarty
 */

use Error;
use Exception;
use JTL\Plugin\Admin\Installation\Installer;
use JTL\Plugin\Admin\Installation\Uninstaller;
use JTL\Plugin\Admin\Updater;
use JTL\Plugin\Admin\Validation\LegacyPluginValidator;
use JTL\Plugin\Admin\Validation\PluginValidator;
use JTL\Plugin\Helper;
use JTL\Shop;
use JTL\XMLParser;
use Plugin\admorris_pro\TemplateUpdater;
use JTL\Plugin\InstallCode;
use JTL\Plugin\PluginLoader;
use Plugin\admorris_pro\ConsentManagerIntegration;
use Plugin\admorris_pro\ioHandling\cookieNoticePro\CookieNoticePro;
use Plugin\admorris_pro\lib\AdmorrisLib;
use Plugin\admorris_pro\Logger;

use function Plugin\admorris_pro\container;

global $downloadlink;
global $kPlugin;
global $template;
global $crypt_1;
global $data;
$kPlugin = $oPlugin->getID();

$data = Shop::Container()
  ->getDB()
  ->select('xplugin_admorris_pro_status', 'id', 1);
$downloadlink = $data->dl; // Downloadlink
$template = $data->t; // Hash von 0
$crypt_1 = $data->e; // Hash von 0

function prepare($version = null)
{
  global $downloadlink;
  global $data;

  if (defined('ADMORRIS_PRO_SIMULATE_UPDATE')) {
    return 1;
  }

  if ($version) {
    $downloadlink = "admorris_pro_" . $version;
  } elseif ($data->nextVersion) {
    $downloadlink = "admorris_pro_" . $data->nextVersion;
  }

  $log = container()->makeWith(Logger::class, ['prependText' => 'Updater (prepare)']);

  if ($downloadlink == 'KV') {
    return 3; //"Wir konnten leider keine passende Version zu<br>Ihrer Shop Version ' . $shopversion . ' und PHP Version ' . $phpversion . ' finden! <br>Bitte wenden Sie sich mit diesen Daten an den Support!";
  } elseif (empty($downloadlink)) {
    return 4; //"Wir konnten keine g&uuml;ltige Lizenz zu dieser Domain finden! Bitte wenden Sie sich an den Support!";
  }

  try {
    // Download files
    try {
      admDownload($downloadlink);
    } catch (\Throwable $th) {
      throw new Exception(
        'An Exception occured while downloading the update zip file: ' . $th->getMessage(),
      );
    }

    // Backup setting files
    try {
      $now = date('Y-m-d-H-i-s');
      $status = false;
      $status = copyFile(
        'templates/admorris_pro/themes/admorris/theme.css',
        'admorris_pro_backups/template_backup_' . $now . '/themes/admorris/theme.css',
      );
      $status = copyFile(
        'templates/admorris_pro/php/headerLayoutData.json',
        'admorris_pro_backups/template_backup_' . $now . '/php/headerLayoutData.json',
      );
      $status = copyFile(
        'templates/admorris_pro/themes/admorris/less/user-styles/user-styles.less',
        'admorris_pro_backups/template_backup_' .
          $now .
          '/themes/admorris/less/user-styles/user-styles.less',
      );
      copyFile(
        'templates/admorris_pro/themes/base/images/favicon.ico',
        'admorris_pro_backups/template_backup_' . $now . '/themes/base/images/favicon.ico',
        false,
      );
      copyFile(
        'templates/admorris_pro/icons.svg',
        'admorris_pro_backups/template_backup_' . $now . '/icons.svg',
        false,
      );
      copyFile(
        'templates/admorris_pro/payment-icons.svg',
        'admorris_pro_backups/template_backup_' . $now . '/payment-icons.svg',
        false,
      );
    } catch (\Throwable $th) {
      $log->error('Updater: Could not backup setting files: ' . $th->getMessage());
      return 2;
    }

    // Backup template folder
    try {
      renameFolder('templates/admorris_pro', 'admorris_pro_backups/template_backup');
    } catch (\Throwable $th) {
      $log->error('Could not backup template folder: ' . $th->getMessage());
    }

    // rename plugin folder too
    try {
      renameFolder('plugins/admorris_pro', 'admorris_pro_backups/plugin_backup');
    } catch (\Throwable $th) {
      $log->error('Could not backup plugin folder: ' . $th->getMessage());
    }

    // Extract Zip
    extractZip($downloadlink);

    \sleep(1);

    if (function_exists('opcache_reset')) {
      opcache_reset();
    }

    return 1;
  } catch (\Throwable $th) {
    $log->error($th);
    if (!str_contains($th->getMessage(), 'Could not rename folder')) {
      renameFolder('admorris_pro_backups/template_backup', 'templates/admorris_pro');
      renameFolder('admorris_pro_backups/plugin_backup', 'plugins/admorris_pro');
    }

    return 2;
  }
}

/**
 * Function to update admorris pro
 * @since 1.0.0
 * @return int
 */
function update()
{
  $log = container()->makeWith(Logger::class, ['prependText' => 'Updater (update)']);

  $db = Shop::Container()->getDB();
  /**
   * Foreign key checks cause problems in the Updater, because it removes consent items and we have set them as foreign key.
   * They are added afterwards with the restoreAdmorrisConsents() function.
   */
  $db->query('SET FOREIGN_KEY_CHECKS=0');
  try {
    /* Backup out salesbooster consent settings and save after update */
    /* necessary for livechat & enhancedecommerce */
    $salesboosterConsents = ConsentManagerIntegration::backupSalesboosterConsents();

    $cache = Shop::Container()->getCache();
    $parser = new XMLParser();
    $installer = new Installer(
      $db,
      new Uninstaller($db, $cache),
      new LegacyPluginValidator($db, $parser),
      new PluginValidator($db, $parser),
    );
    $updater = new Updater($db, $installer);
  } catch (\Throwable $th) {
    $log->error($th);
    return 2;
  }

  global $downloadlink;
  global $template;
  global $crypt_1;

  if (!empty($downloadlink) && $downloadlink != 'KV') {
    try {
      $oPlugin = Shop::get('oplugin_admorris_pro');
      $kPlugin = $oPlugin->getID();


      // Update Plugin
      $status = $updater->update($kPlugin);
      ConsentManagerIntegration::loadSalesboosterConsentsBackup($salesboosterConsents);

      if ($status !== InstallCode::OK) {
        $error = (new InstallCode($status))->getKey();
        $log->error('Could not install plugin ' . $error);
        $db->query('SET FOREIGN_KEY_CHECKS=1');
        throw new \Exception("Couldn't update plugin - " . $error);
      }

      $log->notice('plugin update success');

      // Update Template
      $activeTemplate = Shop::Container()
        ->getTemplateService()
        ->getActiveTemplate();
      $templateID = $activeTemplate->getTemplate();
      // $ordnerName = Model::loadByAttributes(['type' => 'standard'], Shop::Container()->getDB());

      if ($templateID === 'admorris_pro_child' || $templateID === 'admorris_pro') {
        $templateUpdater = new TemplateUpdater($templateID, $db, $cache);
        $status = $templateUpdater->saveConfig();

        if (!$status) {
          $db->query('SET FOREIGN_KEY_CHECKS=1');
          throw new \Exception("Couldn't update template");
        }
      }

      $log->notice('template update success');


      // Copy back template files from backup folder
      if (!defined('ADMORRIS_PRO_SIMULATE_UPDATE')) {
        try {
          copyBackFiles();
          $log->notice('copy back template files success');
        } catch (\Throwable $th) {
          $log->error('Could not copy back template files: '  . $th->getMessage());
        }
      }

      $db->query('SET FOREIGN_KEY_CHECKS=1');


      // If everything went through delete backup folder

      if (!defined('ADMORRIS_PRO_SIMULATE_UPDATE')) {
        deleteFolder(PFAD_ROOT . 'admorris_pro_backups/template_backup');
        deleteFolder(PFAD_ROOT . 'admorris_pro_backups/plugin_backup');
        // delete update zip file
        deleteFolder(PFAD_ROOT . 'admorris_pro_backups/' . $downloadlink . '.zip');
      }

      // restore consents
      // update foreign keys (tconsentId) of admorris table, set new key references after installer has reinitiated tconsent table -> check syncPluginUpdate() of Installer.php
      try {
        restoreAdmorrisConsents();
      } catch (\Throwable $th) {
        $log->error($th);
        return 2;
      }

      //"Update wurde erfolgreich durchgefuehrt!";
      $db->query('SET FOREIGN_KEY_CHECKS=1');
      $log->notice('update complete');

      return 1;
    } catch (\Throwable $th) {
      $log->error($th);
      try {
        restoreAdmorrisConsents();
        return 2;
      } catch (\Throwable $th) {
        $log->error($th);
        return 2;
      }
      $db->query('SET FOREIGN_KEY_CHECKS=1');
      if (defined('ADMORRIS_PRO_SIMULATE_UPDATE')) {
        return 2;
      }

      try {
        // If there was an error backup new template folder and restore old one
        copyBackFiles();
        renameFolder(
          'templates/admorris_pro',
          'admorris_pro_backups/template_backup/templates/admorris_pro',
        );
        renameFolder('admorris_pro_backups/template_backup', 'templates/admorris_pro');
        // restore plugin backup folder too
        renameFolder('admorris_pro_backups/plugin_backup', 'plugins/admorris_pro');
      } catch (\Throwable $th) {
        $log->error($th);
      }
      // "Beim Update ist ein Fehler aufgetreten! Bitte wenden Sie sich an den Support!";
      return 2;
    }
    $db->query('SET FOREIGN_KEY_CHECKS=1');
  }

  return 2;
}

function copyBackFiles()
{
  copyFile(
    'admorris_pro_backups/template_backup/themes/admorris/theme.css',
    'templates/admorris_pro/themes/admorris/theme.css',
  );
  copyFile(
    'admorris_pro_backups/template_backup/php/headerLayoutData.json',
    'templates/admorris_pro/php/headerLayoutData.json',
  );
  copyFile(
    'admorris_pro_backups/template_backup/themes/admorris/less/user-styles/user-styles.less',
    'templates/admorris_pro/themes/admorris/less/user-styles/user-styles.less',
  );
  copyFile(
    'admorris_pro_backups/template_backup/themes/base/images/favicon.ico',
    'templates/admorris_pro/themes/base/images/favicon.ico',
    false,
  );
  copyFile(
    'admorris_pro_backups/template_backup/icons.svg',
    'templates/admorris_pro/icons.svg',
    false,
  );
  copyFile(
    'admorris_pro_backups/template_backup/payment-icons.svg',
    'templates/admorris_pro/payment-icons.svg',
    false,
  );
}

/**
 * Function to download the plugin
 * @param string $downloadlink
 * @since 1.0.0
 * @return boolean
 */
function admDownload($downloadlink)
{
  // file handler
  $file = fopen(\PFAD_ROOT . 'admorris_pro_backups/' . $downloadlink . '.zip', 'w');

  // cURL
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://admorris.com/pro/files/' . $downloadlink . '.zip');
  // set cURL options
  curl_setopt($ch, CURLOPT_FAILONERROR, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  // set file handler option
  $response = curl_setopt($ch, CURLOPT_FILE, $file);
  // execute cURL
  if ($response === false) {
    throw new Exception('Could not set curl file handler in admDownload()');
  }
  $response = curl_exec($ch);
  if ($response === false) {
    throw new Exception('Could not execute curl in admDownload()');
  }
  // close cURL
  curl_close($ch);
  // close file
  if ($response) {
    fclose($file);
  }
  return $response;
}

/**
 * Function to copy files
 * @param string $originalFile
 * @param string $newFile
 * @since 1.0.0
 * @return boolean
 */
function copyFile($originalFile, $newFile, $throwsException = true)
{
  $path = pathinfo($newFile);
  $path = PFAD_ROOT . $path['dirname'];
  $copysignal = 0;
  if (!file_exists($path)) {
    mkdir($path, 0755, true);
  }
  $copysignal = copy(PFAD_ROOT . $originalFile, PFAD_ROOT . $newFile);
  if (!$copysignal && $throwsException) {
    throw new Exception("Couldn't copy file $originalFile to $newFile");
  }
  return true;
}

/**
 * Function to extract a zip file
 * @param string $filename
 * @since 1.0.0
 * @return boolean
 */
function extractZip($filename)
{
  $zip = new \ZipArchive();
  if ($zip->open(\PFAD_ROOT . 'admorris_pro_backups/' . $filename . '.zip') === true) {
    $zip->extractTo(PFAD_ROOT);
    $response = $zip->close();
    if ($response === false) {
      throw new Exception('Could not extract update zip file from: '
       .\PFAD_ROOT . 'admorris_pro_backups/' . $filename . '.zip');
    }
    return $response;
  } else {
    throw new Exception('Could not open zip file from: '. \PFAD_ROOT . 'admorris_pro_backups/' . $filename . '.zip');
  }
}

/**
 * Function to rename a folder and backup the folder at the new location including a timestamp.
 *
 * @param string $originalFolder
 * @param string $newFolder
 * @since 1.0.0
 * @return boolean
 */
function renameFolder($originalFolder, $newFolder)
{
  if (file_exists(PFAD_ROOT . $newFolder)) {
    $now = date('Y-m-d-H-i-s');
    $response = rename(PFAD_ROOT . $newFolder, PFAD_ROOT . $newFolder . $now);
  }
  if (file_exists(PFAD_ROOT . $originalFolder)) {
    $response = rename(PFAD_ROOT . $originalFolder, PFAD_ROOT . $newFolder);
    if ($response === false) {
      throw new Exception('Could not rename folder ' . $originalFolder . ' to ' . $newFolder);
    }
    return $response;
  }

  throw new Exception('Could not rename folder because' . $originalFolder . ' does not exist');
}

/**
 * Function to delete a folder
 * @param string $dir
 * @since 1.0.0
 * @return void
 */
function deleteFolder($dir)
{
  if (is_dir($dir)) {
    $objects = scandir($dir);
    foreach ($objects as $object) {
      if ($object != '.' && $object != '..') {
        if (is_dir($dir . '/' . $object)) {
          deleteFolder($dir . '/' . $object);
        } else {
          unlink($dir . '/' . $object);
        }
      }
    }
    rmdir($dir);
  }
}

/**
 * Copy a file, or recursively copy a folder and its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.1
 * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
 * @param       string   $source    Source path
 * @param       string   $dest      Destination path
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
function copyr($source, $dest)
{
  // Check for symlinks

  if (is_link($source)) {
    return symlink(readlink($source), $dest);
  }

  // Simple copy for a file

  if (is_file($source)) {
    return copy($source, $dest);
  }

  // Make destination directory

  if (!is_dir($dest)) {
    mkdir($dest);
  }

  // Loop through the folder

  $dir = dir($source);
  while (false !== ($entry = $dir->read())) {
    // Skip pointers

    if ($entry == '.' || $entry == '..') {
      continue;
    }

    // Deep copy directories

    copyr("$source/$entry", "$dest/$entry");
  }

  // Clean up

  $dir->close();
  return true;
}

function restoreAdmorrisConsents()
{
  // restore consents
  // update foreign keys (tconsentId) of admorris table, set new key references after installer has reinitiated tconsent table -> check syncPluginUpdate() of Installer.php
  $cookies = CookieNoticePro::getAll();
  foreach ($cookies as $cookie) {
    $payload = ConsentManagerIntegration::createConsentManagerPayload(
      $cookie,
      $cookie->localization,
    );
    $jtlConsent = ConsentManagerIntegration::registerConsentItem($payload);

    // update admorris cookie with jtl consent cookie fields
    $obj_merged = ConsentManagerIntegration::mergeAdmCookieAndJTLConsent($cookie, $jtlConsent);
    CookieNoticePro::update($obj_merged, false);
  }
}
