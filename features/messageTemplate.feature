Feature: Message templates
  As an SDK user
  I need CRUD functionality for message templates

Scenario: Read message template
  Given message templates exist:
  | id | title | subject | message      |
  |  1 | ABC   | test    | test message |
  When I retrieve message template 1
  Then a GET request should be sent to "/templates/1"
   And entity property "title" should contain "ABC"
   And entity property "subject" should contain "test"
   And entity property "message" should contain "test message"

Scenario: Store a message template
  Given I have a message template:
  | title        | subject   | message      |
  | New template | A subject | Some message |
  When I persist the message template
  Then a POST request should be sent to "/templates"
   And the request body should contain:
   """
   {
      "title": "New template",
      "subject": "A subject",
      "message": "Some message"
   }
   """
   And entity property "id" should be greater than zero

Scenario: Update a message template
  Given message templates exist:
  | id | title | subject | message      |
  |  1 | ABC   | test    | test message |
   And I retrieve message template 1
  When I set entity property "title" to "Another title"
   And I persist message template 1
  Then a PUT request should be sent to "/templates/1"
   And the request body should contain:
   """
   {
      "title": "Another title",
      "subject": "test",
      "message": "test message"
   }
   """

Scenario: Delete a message template
  Given message templates exist:
  | id | title | subject | message      |
  |  1 | ABC   | test    | test message |
   And I retrieve message template 1
  When I delete the message template 1
  Then a DELETE request should be sent to "/templates/1"
   And entity property "id" should be undefined
