<?php

class Dutchworld_Directcontact_SendemailController extends Mage_Core_Controller_Front_Action
{
    const XML_PATH_EMAIL_IDENTITY = 'contacts/directcontact/dc_sender_identity';
    private $templateId = 'customer_testemail_email_template';
    private $mailSubject = 'Request for contact from webshop customer';
    private $targetEmail = 'root@localhost'; // Default recipient, so all mail ends up on the server at least
    private $targetName = 'root';
    private $sender;
    private $params;
    private $postValues = '';

    public function indexAction()
    {
        //echo "U R HERE"; exit;
        // Default sender in case of mis configuration
        $this->params = Array('phoneSubmit' => Array('phoneName', 'phoneNumber', 'phoneChoice', 'phoneComment', 'phoneTodayAt', 'phoneTomorrowAt')
        , 'mailSubmit' => Array('mailName', 'mailEmail', 'mailComment', 'mailSubject')
        , 'chatSubmit' => Array('chatName', 'chatEmail', 'chatComment')
        );
        $this->dcGetParams();
        $this->sender = Array('name' => 'Direct contact module',
            'email' => 'noreply@noreply.com');
        //$this->dcGetSubject();
        $this->templateId = Mage::getStoreConfig('contacts/directcontact/dc_email_template');

        $this->targetEmail = Mage::getStoreConfig('contacts/directcontact/dc_target_email');
        $this->targetName = Mage::getStoreConfig('contacts/directcontact/dc_target_name');

        $this->sender = Mage::getStoreConfig('contacts/directcontact/dc_sender_identity');
        if (isset($this->postValues['requestEmail'])) {
            $this->sender = Array('name' => $this->postValues['requestName'],
                'email' => $this->postValues['requestEmail']);
        }
//$store = Mage::app()->getStore($this->getStore());
        //var_dump($store->getConfig(self::XML_PATH_EMAIL_IDENTITY));
//    const XML_PATH_EMAIL_IDENTITY = 'enterprise_reward/notification/email_sender';
        $this->storeId = Mage::app()->getStore()->getId();
        $translate = Mage::getSingleton('core/translate');
        $this->vars = $this->postValues;
//        $this->vars = Array();
        try {
            Mage::getModel('core/email_template')
                //     ->setTemplateSubject($this->mailSubject) Does not work, use subject in template
                ->sendTransactional($this->templateId, $this->sender, $this->targetEmail, $this->targetName, $this->vars, $this->storeId);
            $translate->setTranslateInline(true);
            Mage::getSingleton('customer/session')->addSuccess('Your contact request has been send.');
            Mage::getSingleton('core/session')->addSuccess('Your contact request has been send.');
            echo "success";
        } catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError('An unknown error has unfortunately occured');
            Mage::getSingleton('core/session')->addError('An unknown error has unfortunately occured');
            echo "error";
        }

    }

    private function dcGetParams()
    {
        $tmpFound = false;
        if (isset($_POST)) {
            foreach ($this->params as $form => $vars) {
                $values = Array();
                if (isset($_POST[$form])) {
                    $values['requestExtra'] = '';
                    $values['requestDate'] = date('l jS \of F Y H:i:s');
                    switch ($form) {
                        case 'phoneSubmit':
                            switch ($_POST['phoneChoice']) {
                                case 'asap':
                                    $values['requestExtra'] = 'Contact as soon as possible';
                                    break;
                                case 'today':
                                    $values['requestExtra'] = 'Contact today' . ($_POST['phoneTodayAt'] ? ' at ' . $_POST['phoneTodayAt'] : '');
                                    break;
                                case 'tomorrow':
                                    $values['requestExtra'] = 'Contact tomorrow' . ($_POST['phoneTomorrowAt'] ? ' at ' . $_POST['phoneTomorrowAt'] : '');
                                    break;
                                default:
                                    //$values['extra'] = 'No extra information';
                                    break;

                            }
                            $values['type'] = 'telephone';
                            break;
                        case 'mailSubmit':
                            $values['type'] = 'email';
                            break;
                        case 'chatSubmit':
                            $values['type'] = 'chat';
                            break;
                    }
                    foreach ($vars as $var) {
                        if (isset($_POST[$var])) {
                            $values[preg_replace('/^(mail|phone|chat)/', 'request', $var)] = htmlentities($_POST[$var]);
                        }
                    }
                    $tmpFound = true;
                    break;
                }
            }
        }
        if ($tmpFound) {
            $this->postValues = $values;
            return true;
        } else {
            return false;
        }

    }
}