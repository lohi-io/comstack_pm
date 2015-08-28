<?php

$plugin = array(
  'label' => t('Message - Comstack Private Messaging'),
  'description' => t('Validate the cs pm message type.'),
  'entity_type' => 'message',
  'bundle' => 'cs_pm',
  'class' => 'EntityValidatorMessageCSPMValidator',
);
