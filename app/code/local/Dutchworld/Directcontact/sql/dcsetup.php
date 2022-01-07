<?php
$install = $this;
error_log('Running installer for DC');
$configValuesMap = Array('customer/testemail_email/template' => 'customer_testemail_email_template');
foreach($configValuesMap as $configPath => $configValue) {
    $installer->setConfigData($configPath, $configValue);
}
