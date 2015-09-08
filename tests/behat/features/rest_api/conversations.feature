Feature: List a users conversations and Create a new conversation.

    #204 No content (no conversations exist for this user)

   Background: Logged in as Basic user

    Given I am on the homepage
    And I disable the mobile password plugin
    When I click element with class "action-link-text hidden-xs"
    When I fill in "edit-name" with "basic_user_1"
    When I fill in "edit-pass" with "password"
    And I press "Sign in"
    And I wait for 5000 seconds
    And I click element with class "username"
    Then I should see "Edit my profile"
    Then I should see "Sign out"

  @api
  Scenario: Start a conversation with payload.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    Given I have the payload:
    """
    {
    "recipients": "[??]",
    "text": "Blah blah"
    }
    """
    When I request "POST /api/v1/cs-pm/conversations"
    Then The REST API returns a 201 response

  @api
  Scenario: Start a new conversation without a text.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "POST /api/v1/cs-pm/conversations"
    Then The REST API returns a 400 response
    
  @api
  Scenario: Validating the GET response
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "GET /api/v1/cs-pm/conversations"
    Then The REST API returns a 200 response
    And scope into the "data" property
    And the properties exist:
    """
    type
    id
    participants
    historical_participants
    started_by
    last_updated_by
    Started
    Updated
    unread_count
    pinned
    archived
    muted
    starred
    forwarded
    deleted
    """
    Then the "type" property is a string equalling "user"
    Then the "id" property is an integer equalling "1"
    Then the "participants" property is an empty array
    Then the "historical_participants" property is an empty array
    Then the "started_by" property is an object
    Then the "last_updated_by" property is an object
    Then the "unread_count" property equals "0"
    Then the "pinned" property is a boolean equalling "false"
    Then the "archived" property is a boolean equalling "false"
    Then the "muted" property is a boolean equalling "false"
    Then the "starred" property is a boolean equalling "false"
    Then the "forwarded" property is a boolean equalling "false"
    Then the "deleted" property is a boolean equalling "false"
