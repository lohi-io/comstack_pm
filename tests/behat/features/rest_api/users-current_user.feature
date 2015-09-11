Feature: Return information about the current user, including the permissions they have.

  Background: Logged in as Basic user

  @api @restapi @get @expectsvalid
  Scenario: Authenticated user session.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "GET /api/v1/cs-pm/users/current-user"
    Then I should get a 200 HTTP response
    And scope into the "data" property
    And the properties exist:
    """
    type
    id
    permissions
    conversations
    start
    leave
    reply
    invite_others
    set_title
    mark_as_read
    mute
    archive
    pin
    start
    messages
    edit_own
    delete
    """
    And the "type" property is a string equalling "user"
    And the "id" property is an integer equalling "1"
    And the "permissions" property is an array
    And the "conversations" property is an array
    And the "start" property is a boolean equalling "true"
    And the "leave" property is a boolean equalling "true"
    And the "reply" property is a boolean equalling "true"
    And the "invite_others" property is a boolean equalling "false"
    And the "set_title" property is a boolean equalling "false"
    And the "mark_as_read" property is a boolean equalling "false"
    And the "mute" property is a boolean equalling "false"
    And the "archive" property is a boolean equalling "false"
    And the "pin" property is a boolean equalling "false"
    And the "start" property is a boolean equalling "false"
    And the "messages" property is an array
    And the "edit_own" property is a boolean equalling "false"
    And the "delete" property is a boolean equalling "false"

  @api @restapi @get @expectsinvalid
  Scenario: No authorisation response
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "GET /api/v1/cs-pm/users/current-user"
    Then I should get a 401 HTTP response

