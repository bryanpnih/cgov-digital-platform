<?php

namespace Drupal\app_module;

use Drupal\Core\Entity\EntityInterface;

/**
 * Find and register app module paths.
 *
 * NOTE: The AliasManagerInterface that we copied this from is used by both
 * Inbound and Outbound path processors to rewrite incoming urls to route paths
 * and rewrite route paths in links to aliases. We will not make any Outbound
 * path processors, so we will not need a commensurate getAliasByPath method.
 *
 * @see \Drupal\app_module\AppPathStorageInterface
 */
interface AppPathManagerInterface {

  /**
   * Helper function to create an app path for an entity.
   *
   * This is so that we do not need to add this logic to all
   * of the entity modules that we may add app modules to.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to create the app module path entry for.
   * @param string $op
   *   Is this an 'update' or 'insert'? Unknown values default to 'insert'.
   * @param string $app_field_id
   *   The name of the app module reference field.
   */
  public function registerAppPath(EntityInterface $entity, $op, $app_field_id = 'field_application_module');

  /**
   * Given a requested path, return the path it most closely matches.
   *
   * This gets us the alias, app_module_id and settings for an app module which
   * can be used by a path processor. This will see if the requested path
   * matches or begins with any of the app module paths.
   *
   * Examples:
   *   * App paths: /foo, /foo/bar/bazz
   *   * /foo would match the path with the alias /foo
   *   * /foo/bar would match the path with the alias /foo
   *   * /foo/bar/bazz/bleh would match the path with the alias /foo/bar/bazz
   *     even though there is also a /foo.
   *
   * NOTE: This is meant to be used by an InboundPathProcessor to replace the
   * processed path right before the AliasProcessor. So the $request_path
   * should not have language or any other additional strings added by other
   * processors. This is the parallel to the AliasManagerInterface's
   * getPathByAlias.
   *
   * @param string $request_path
   *   The requested path, which should be either an alias for an entity with
   *   an app module, or it should begin with the alias of an entity with an
   *   app module.
   * @param string $langcode
   *   An optional language code to look up the path in.
   *
   * @return array|null
   *   The app path record for the alias for the app module entity that most
   *   closely matches the requested path, or null if no matching paths were
   *   found.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the request_path does not start with a slash.
   */
  public function getPathByRequest($request_path, $langcode = NULL);

  /**
   * Clear internal caches in alias manager.
   *
   * @param string $source
   *   Source path of the alias that is being inserted/updated. Can be omitted
   *   if entire cache needs to be flushed.
   */
  public function cacheClear($source = NULL);

}
