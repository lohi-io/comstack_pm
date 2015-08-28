<?php

/**
 * @file
 * Contains EntityValidatorMessageCSPMValidator.
 */

class EntityValidatorMessageCSPMValidator extends EntityValidateBase {
  /**
   * Overrides \EntityValidateBase::publicFieldsInfo().
   */
  public function publicFieldsInfo() {
    $public_fields = parent::publicFieldsInfo();

    FieldsInfo::setFieldInfo($public_fields['cs_pm_text'], $this)
      ->setSubProperty('value')
      ->addCallback('isNotEmpty');

    return $public_fields;
  }
}
