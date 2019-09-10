<?php

namespace Drupal\Tests\app_module\Kernel;

use Drupal\Core\Language\LanguageInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\Core\Path\AliasStorage
 * @group path
 */
class AliasStorageTest extends KernelTestBase {
  /**
   * {@inheritdoc}
   */
  public static $modules = ['system', 'app_module'];

  /**
   * App Path storage.
   *
   * @var \Drupal\app_module\Path\AppPathStorage
   */
  protected $storage;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->storage = $this->container->get('app_module.app_path_storage');
  }

  /**
   * @covers ::load
   */
  public function testLoad() {
    $this->storage->save(
      '/test-source-Case',
      '/test-alias-Case',
      'test_app_module'
    );

    $expected = [
      'pid' => 1,
      'owner_alias' => '/test-alias-Case',
      'owner_source' => '/test-source-Case',
      'app_module_id' => 'test_app_module',
      'app_module_data' => NULL,
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
    ];

    // Load by alias case insensative.
    $this->assertEquals([$expected], $this->storage->load(['owner_alias' => '/test-alias-Case']));
    $this->assertEquals([$expected], $this->storage->load(['owner_alias' => '/test-alias-case']));

    // Load by source case insensative.
    $this->assertEquals([$expected], $this->storage->load(['owner_source' => '/test-source-Case']));
    $this->assertEquals([$expected], $this->storage->load(['owner_source' => '/test-source-case']));

    // Load by app module id.
    $this->assertEquals([$expected], $this->storage->load(['app_module_id' => 'test_app_module']));

    $this->storage->save(
      '/test-source-Case2',
      '/test-alias-Case2',
      'test_app_module',
      ['chicken', 'cow'],
      'es'
    );

    $expected2 = [
      'pid' => 2,
      'owner_alias' => '/test-alias-Case2',
      'owner_source' => '/test-source-Case2',
      'app_module_id' => 'test_app_module',
      'app_module_data' => ['chicken', 'cow'],
      'langcode' => 'es',
    ];

    // Load both by app module id.
    $this->assertEquals([$expected2, $expected], $this->storage->load(['app_module_id' => 'test_app_module']));

    // Load only spansish by app module id.
    $this->assertEquals([$expected2], $this->storage->load([
      'app_module_id' => 'test_app_module',
      'langcode' => 'es',
    ]));

  }

}
