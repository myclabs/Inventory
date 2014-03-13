# User module

## User

Create a user:

```php
$user = $userService->createUser($email, $password);
$entityManager->flush();
```

Invite a user by email:

```php
$user = $userService->inviteUser($email);
$entityManager->flush();
```

Delete a user:

```php
$user = $userService->deleteUser($user);
$entityManager->flush();
```
