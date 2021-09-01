<?php

namespace Drupal\d8campaign\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * An example controller.
 */
class ThanksController extends ControllerBase {

  /**
   * Returns a render-able array for a test page.
   */
  public function content() {
        $element = array(
          '#markup' => '<h1 style="text-align:center">Thank You</h1><p>Your request has been processed.</p><p>You shall receive a confirmation of upgrade SVC Bank Signature Account Shortly</p>',
        );
        return $element;
  }

}