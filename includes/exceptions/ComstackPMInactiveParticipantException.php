<?php

/**
 * @file
 * Contains \ComstackPMInactiveParticipantException.
 */

class ComstackPMInactiveParticipantException extends ComstackException {
  protected $message = "You're not an active participant in this conversation.";
}
