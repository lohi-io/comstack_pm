Feature: Return information about the current user, including the permissions they have.

  Background: Logged in as Basic user

 @api
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
    Then the "type" property is a string equalling "user"
    Then the "id" property is an integer equalling "1"
    Then the "permissions" property is an array
    Then the "conversations" property is an array
    Then the "start" property is a boolean equalling "true"
    Then the "leave" property is a boolean equalling "true"
    Then the "reply" property is a boolean equalling "true"
    Then the "invite_others" property is a boolean equalling "false"
    Then the "set_title" property is a boolean equalling "false"
    Then the "mark_as_read" property is a boolean equalling "false"
    Then the "mute" property is a boolean equalling "false"
    Then the "archive" property is a boolean equalling "false"
    Then the "pinn" property is a boolean equalling "false"
    Then the "start" property is a boolean equalling "false"
    Then the "messages" property is an array
    Then the "edit_own" property is a boolean equalling "false"
    Then the "delete" property is a boolean equalling "false"

 @api
 Scenario: No authorisation response
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "GET /api/v1/cs-pm/users/current-user"
    Then I should get a 401 HTTP response

