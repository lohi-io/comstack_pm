<?php

/**
 * @file
 * Contains \ComstackPMReadOnlyException.
 */

class ComstackPMReadOnlyException extends ComstackException {
  protected $message = "The user you're attempting to contact isn't available.";
}
