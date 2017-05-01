<?php

namespace Drupal\Tests\skel\Unit;

use Drupal\Core\Render\ElementInfoManagerInterface;
use Drupal\skel\FormHelper;
use Drupal\Tests\UnitTestCase;

/**
 * @group skel
 *
 * @coversDefaultClass \Drupal\skel\FormHelper
 */
class FormHelperTest extends UnitTestCase {

  /**
   * @covers ::applyStandardProcessing
   */
  public function testApplyStandardProcessing() {
    $element_info = $this->prophesize(ElementInfoManagerInterface::class);
    $element_info->getInfo('location')->willReturn([
      '#process' => [
        'process_location',
      ],
    ]);
    $element = ['#type' => 'location'];

    $form_helper = new FormHelper($element_info->reveal());
    $form_helper->applyStandardProcessing($element);

    $this->assertEquals(['process_location'], $element['#process']);
  }

}
