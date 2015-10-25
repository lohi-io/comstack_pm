<?php

/**
 * @file
 * Contains \ComstackPMReadOnlyException.
 */

class ComstackPMReadOnlyException extends ComstackException {
  protected $message = "You're currently opted out of Private Messaging.";
}
