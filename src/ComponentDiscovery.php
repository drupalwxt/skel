<?php

namespace Drupal\skel;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ExtensionDiscovery;

/**
 * Helper object to locate Skel components and sub-components.
 */
class ComponentDiscovery {

  /**
   * The extension discovery iterator.
   *
   * @var \Drupal\Core\Extension\ExtensionDiscovery
   */
  protected $discovery;

  /**
   * The Skel profile extension object.
   *
   * @var Extension
   */
  protected $profile;

  /**
   * Cache of all discovered components.
   *
   * @var Extension[]
   */
  protected $components;

  /**
   * ComponentDiscovery constructor.
   *
   * @param string $app_root
   *   The application root directory.
   */
  public function __construct($app_root) {
    $this->discovery = new ExtensionDiscovery($app_root);
  }

  /**
   * Returns an extension object for the Skel profile.
   *
   * @return \Drupal\Core\Extension\Extension
   *   The Skel profile extension object.
   *
   * @throws \RuntimeException
   *   If the Skel profile is not found in the system.
   */
  protected function getProfile() {
    if (empty($this->profile)) {
      $profiles = $this->discovery->scan('profile');

      if (empty($profiles['skel'])) {
        throw new \RuntimeException('Skel profile not found.');
      }
      $this->profile = $profiles['skel'];
    }
    return $this->profile;
  }

  /**
   * Returns the base path for all Skel components.
   *
   * @return string
   *   The base path for all Skel components.
   */
  protected function getBaseComponentPath() {
    return $this->getProfile()->getPath() . '/modules/custom';
  }

  /**
   * Returns extension objects for all Skel components.
   *
   * @return Extension[]
   *   Array of extension objects for all Skel components.
   */
  public function getAll() {
    if (is_null($this->components)) {
      $base_path = $this->getBaseComponentPath();

      $filter = function (Extension $module) use ($base_path) {
        return strpos($module->getPath(), $base_path) === 0;
      };

      $this->components = array_filter($this->discovery->scan('module'), $filter);
    }
    return $this->components;
  }

  /**
   * Returns extension objects for all main Skel components.
   *
   * @return Extension[]
   *   Array of extension objects for top-level Skel components.
   */
  public function getMainComponents() {
    $base_path = $this->getBaseComponentPath();

    $filter = function (Extension $module) use ($base_path) {
      return dirname($module->getPath()) == $base_path;
    };

    return array_filter($this->getAll(), $filter);
  }

  /**
   * Returns extension object for all Skel sub-components.
   *
   * @return Extension[]
   *   Array of extension objects for Skel sub-components.
   */
  public function getSubComponents() {
    $base_path = $this->getBaseComponentPath();

    $filter = function (Extension $module) use ($base_path) {
      return strlen(dirname($module->getPath())) > strlen($base_path);
    };

    return array_filter($this->getAll(), $filter);
  }

}
