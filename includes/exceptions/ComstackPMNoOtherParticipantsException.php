<?php

/**
 * @file
 * Contains \ComstackPMNoOtherParticipantsException.
 */

class ComstackPMNoOtherParticipantsException extends ComstackException {
  protected $message = "There aren't any other active participants in this conversation.";
}
