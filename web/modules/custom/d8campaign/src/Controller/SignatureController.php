<?php

namespace Drupal\d8campaign\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * An example controller.
 */
class SignatureController extends ControllerBase {

  /**
   * Returns a render-able array for a test page.
   */
  public function content() {
    //$output[]['#attached']['library'][] = 'd8campaign/d8campaign-styling';
    
    if($_GET['ac']){
      $node = Node::load($_GET['ac']);
      if($node){
        $name = $node->get('title')->getValue()[0]['value'];
        $field_account_number = $node->get('field_account_number')->getValue()[0]['value'];
        $field_account_type = $node->get('field_account_type')->getValue()[0]['value'];
        $is_checked = FALSE;

        $field_campaign_status = $node->get('field_campaign_status')->getValue()[0]['value'];
        //print $field_campaign_status;exit;
        if($field_campaign_status == "accepted"){
          $is_checked = TRUE;
          $response = new RedirectResponse('/already-submitted?type=signature');
          $response->send();
        }else{
          $node->field_campaign_status = "upgrade";
          $node->save();
          $is_checked = FALSE;
        }
    
        return [
          // Your theme hook name.
          '#theme' => 'signature_account_overview',      
          // Your variables.
          '#variable1' => $name,
          '#variable2' => $field_account_number,
          '#variable3' => $field_account_type,
          '#is_accepted' => $is_checked,
          '#cache' => [
            'max-age' =>  0
          ]
        ];
      }else{
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
      }
      
    }else{
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }
    
  }


  public function content_testcampaign() {
    if($_GET['ac']){

      $node = Node::load($_GET['ac']);
      $name = $node->get('title')->getValue()[0]['value'];
      $field_account_number = $node->get('field_account_number')->getValue()[0]['value'];
      $field_account_type = $node->get('field_account_type')->getValue()[0]['value'];
      $is_checked = FALSE;

      $field_campaign_status = $node->get('field_campaign_status')->getValue()[0]['value'];
      //print $field_campaign_status;exit;
      if($field_campaign_status == "accepted"){
        $is_checked = TRUE;
        $response = new RedirectResponse('/already-submitted?type=classic');
        $response->send();
      }else{
        $node->field_campaign_status = "upgrade";
        $node->save();
        $is_checked = FALSE;
      }
  
      return [
        // Your theme hook name.
        '#theme' => 'testcampaign_account_overview',      
        // Your variables.
        '#variable1' => $name,
        '#variable2' => $field_account_number,
        '#variable3' => $field_account_type,
        '#is_accepted' => $is_checked,
        '#cache' => [
          'max-age' =>  0
        ]
      ];
    }else{
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }
    
  }

  public function content_submitted() {
    if($_GET['type']){
      $type = $_GET['type'];
    }else{
      $type = "classic";
    }
    return [
      // Your theme hook name.
      '#theme' => 'already_submitted',
      '#type' => $type,
      '#cache' => [
        'max-age' =>  0
      ]
    ];
  }

}