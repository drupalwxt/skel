<?php

namespace Drupal\Tests\skel\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests Skeleton installation profile expectations.
 *
 * @group skel
 */
class SkelTest extends BrowserTestBase {

  /**
   * Installation profile.
   *
   * @var string
   */
  protected $profile = 'skel';

  /**
   * Test for the login.
   */
  public function testOpenDataLogin() {
    // Create a user to check the login.
    $user = $this->createUser();

    // Log in our user.
    $this->drupalLogin($user);

    // Verify that logged in user can access the logout link.
    $this->drupalGet('user');

    $this->assertLinkByHref('/user/logout');
  }

}
