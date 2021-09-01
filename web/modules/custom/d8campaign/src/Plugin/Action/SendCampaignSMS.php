<?php

namespace Drupal\d8campaign\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\ContentEntityInterface;
/**
 * Action description.
 *
 * @Action(
 *   id = "send_campaign_sms",
 *   label = @Translation("Send Campaign SMS"),
 *   type = ""
 * )
 */

class SendCampaignSMS extends ViewsBulkOperationsActionBase {

    use StringTranslationTrait;

    /**
     * {@inheritdoc}
     */
    public function execute(ContentEntityInterface $entity = NULL) {
        
        $nid = isset($entity->get('nid')->getValue()[0]['value']) ? $entity->get('nid')->getValue()[0]['value']: "";
        $title = isset($entity->get('title')->getValue()[0]['value']) ? $entity->get('title')->getValue()[0]['value']: "";
        $field_phone = isset($entity->get('field_phone')->getValue()[0]['value']) ? $entity->get('field_phone')->getValue()[0]['value']: "";
        $field_campaign_type = isset($entity->get('field_campaign_type')->getValue()[0]['target_id']) ? $entity->get('field_campaign_type')->getValue()[0]['target_id']: "";

        if(is_numeric($field_phone) && strlen($field_phone) == 10 && $field_campaign_type){

            $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($field_campaign_type);
            $sms_template = isset($term->get('field_sms_template')->getValue()[0]['value']) ? $term->get('field_sms_template')->getValue()[0]['value']: "";
            $landing_page_link = isset($term->get('field_landing_page_link')->getValue()[0]['value']) ? $term->get('field_landing_page_link')->getValue()[0]['value']: "";
            $status = isset($term->get('status')->getValue()[0]['value']) ? $term->get('status')->getValue()[0]['value']: "";

            if($status){
                $host = $_SERVER['SERVER_NAME'];
                $port = $_SERVER['SERVER_PORT'];
                if($port == 443){
                    $ref_url = "https://".$host;
                }else{
                    $ref_url = "http://".$host;
                }

                $landing_page_link = $ref_url."".$landing_page_link."?ac=".$nid;

                $sms_template = str_replace("#username#",$title,$sms_template);
                $sms_template = str_replace("#landingpageurl#",$landing_page_link,$sms_template);
                $sms_template = urlencode( $sms_template );

                $field_phone = "+91".$field_phone;

                $config = \Drupal::service('config.factory')->getEditable('d8campaign.api');
                //$config = $this->config('d8campaign.api');
                $sms_username =  $config->get('sms_username');
                $sms_password =  $config->get('sms_password');
                $sms_senderid =  $config->get('sms_senderid');
                $sms_cdmaheader =  $config->get('sms_cdmaheader');           

                $curlSession = curl_init();
                curl_setopt($curlSession, CURLOPT_URL, 'https://hapi.smsapi.org/SendSMS.aspx?UserName='.$sms_username.'&password='.$sms_password.'&MobileNo='.$field_phone.'&SenderID='.$sms_senderid.'&CDMAHeader='.$sms_cdmaheader.'&Message='.$sms_template.'');
                curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
                curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);

                $jsonData = curl_exec($curlSession);
                curl_close($curlSession);


                if (strpos($jsonData, 'MessageSent') !== false) {
                    $data = explode('="',$jsonData);
                    $data1 = explode('"',$data[1]);
                    $data2 = explode('"',$data[2]);
                    $guid = isset($data1[0]) ? $data1[0] : "";
                    $msg_time = isset($data2[0]) ? $data2[0] : "";

                    $node = Node::load($nid);
                    $node->field_sms_sent = 1;
                    $node->field_message_guid = $guid;
                    $node->field_message_datetime = $msg_time;
                    $node->field_campaign_otp = $randomid;
                    $node->save();
                    return $this->t('SMS Send sucessfully');
                }else{
                    return $this->t('Error Sending SMS');
                }
            }else{
                return $this->t('This Campaingn is unpublished');
            }

            

        
        }else{
            return $this->t('Invalid Mobile number');
        }
        

        
    }

    /**
     * {@inheritdoc}
     */
    public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
       
        if ($object instanceof Node && $object->get('type')->getValue()[0]['target_id'] == "campaign") {
            return TRUE;
        }
        
        
        return FALSE;
    }
}