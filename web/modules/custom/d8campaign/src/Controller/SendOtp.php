<?php

namespace Drupal\d8campaign\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;

class SendOtp extends ControllerBase {
    public function content() {
        if($_GET['ac']){
            $node = Node::load($_GET['ac']);
            if($node){
                $field_phone = $node->get('field_phone')->getValue()[0]['value'];
                $field_campaign_status = $node->get('field_campaign_status')->getValue()[0]['value'];
                //return new JsonResponse([ 'result' => 'sms_sent', 'method' => 'GET', 'status'=> 200]);
                $config = \Drupal::service('config.factory')->getEditable('d8campaign.api');
                $sms_username =  $config->get('sms_username');
                $sms_password =  $config->get('sms_password');
                $sms_senderid =  $config->get('sms_senderid');
                $sms_cdmaheader =  $config->get('sms_cdmaheader');
                $sms_otp_template =  $config->get('sms_otp_template');

                $randomid = mt_rand(100000,999999);

                $sms_otp_template = str_replace("#var#",$randomid,$sms_otp_template);
                $sms_otp_template = urlencode( $sms_otp_template );

                $curlSession = curl_init();
                curl_setopt($curlSession, CURLOPT_URL, 'https://hapi.smsapi.org/SendSMS.aspx?UserName='.$sms_username.'&password='.$sms_password.'&MobileNo='.$field_phone.'&SenderID='.$sms_senderid.'&CDMAHeader='.$sms_cdmaheader.'&Message='.$sms_otp_template.'');
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

                    //$node = Node::load($nid);
                    //$node->field_sms_sent = 1;
                    $node->field_message_guid = $guid;
                    $node->field_message_datetime = $msg_time;
                    $node->field_campaign_otp = $randomid;

                    if($field_campaign_status == "accepted"){
                        $node->field_campaign_status = "accepted";
                    }else{
                        $node->field_campaign_status = "otp";
                    }

                    
                    $node->save();
                    return new JsonResponse([ 'result' => 'sms_sent', 'method' => 'GET', 'status'=> 200]);
                }else{
                    return new JsonResponse([ 'result' => 'error_sms_sent', 'method' => 'GET', 'status'=> 200]);
                }
                
            }else{
                return new JsonResponse([ 'result' => 'invalid_user', 'method' => 'GET', 'status'=> 200]);
            }
        }else{
            return new JsonResponse([ 'result' => 'paramerters_missing', 'method' => 'GET', 'status'=> 201]);
        }
    
    }


    public function matchotp() {
        if($_GET['nid'] && $_GET['otp']){
            $node = Node::load($_GET['nid']);
            $otp = $_GET['otp'];
            if($node){
                $field_otp = $node->get('field_campaign_otp')->getValue()[0]['value'];
                if($field_otp == $otp){
                    return new JsonResponse([ 'result' => 'otp_match', 'method' => 'GET', 'status'=> 200]);
                }else{
                    return new JsonResponse([ 'result' => 'otp_wrong', 'method' => 'GET', 'status'=> 200]);
                }
            }else{
                return new JsonResponse([ 'result' => 'invalid_user', 'method' => 'GET', 'status'=> 200]);
            }
        }else{
            return new JsonResponse([ 'result' => 'paramerters_missing', 'method' => 'GET', 'status'=> 201]);
        }
    
    }

}
