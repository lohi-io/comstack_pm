Feature: GET a message and Update the message text, as Authenticated user.

 @api
 Scenario: The user successfully found the message.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "GET /api/v1/cs-pm/messages/1"
    Then The REST API returns a 200 response
    And scope into the "data" property
    And the properties exist:
     """
     type
     id
     message_type
     conversation_id
     sender
     sent
     updated
     text
     weight
     edits
     """

 @api
 Scenario: Message wasn't found.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "GET /api/v1/cs-pm/messages/999999"
    Then The REST API returns a 404 response

 @api
 Scenario: The user successfully updated the message text.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    Given I have the payload:
     """
     {
      "text": "Sample text"
     }
     """
    When I request "PUT /api/v1/cs-pm/messages/1"
    Then The REST API returns a 200 response

 @api
 Scenario: Invalid request.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "PUT /api/v1/cs-pm/messages/1"
    Then The REST API returns a 400 response

 @api
 Scenario: Looking into the message which doesn't exist
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "PUT /api/v1/cs-pm/messages/99999"
    Then The REST API returns a 404 response
