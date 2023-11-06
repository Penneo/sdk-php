Feature: Users
  As an SDK user
  I need functionality to manipulate user data

Scenario: Read logged in user data
  Given user exist:
  | id | fullName | email        |
  |  1 | John Doe | john@doe.com |
   And logged in user is 1
  When I retrieve the logged in user
  Then a GET request should be sent to "/user"
   And entity property "fullName" should contain "John Doe"
   And entity property "email" should contain "john@doe.com"
