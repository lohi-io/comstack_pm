Feature: Test the endpoint for specific conversations with the available HTTP methods - GET and DELETE.

 @api
 Scenario: Authenticated user session.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "GET /api/v1/cs-pm/conversations/1"
    Then I should get a 200 HTTP response
    And scope into the "data" property
    And the properties exist:
     """
     id
     participants
     historical_participants
     started_by
     last_updated_by
     started
     updated
     messages_count
     unread_count
     """

 @api
 Scenario: Authenticated user session with wrong conversation ID
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "GET /api/v1/cs-pm/conversations/452145"
    Then I should get a 404 HTTP response

 @api
 Scenario: Authenticated user session.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "DELETE /api/v1/cs-pm/conversations/1"
    Then I should get a 200 HTTP response

 @api
 Scenario: Authenticated user session with wrong conversation ID
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "DELETE /api/v1/cs-pm/conversations/23512"
    Then I should get a 404 HTTP response
