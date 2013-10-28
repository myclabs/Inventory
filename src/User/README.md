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

## ACL

You give access rights to a user by adding it a role:

```php
$user->addRole(new CellAdministrator($user, $cell));
```

Test access rights:

```php
$userService->isAllowed($user, Action::EDIT(), $resource);
```

### Extending

#### Creating a new kind of resource

To create authorizations on a new resource, you need to create a new kind of authorization:

```php
class ArticleAuthorization extends Authorization
{
    protected $article;

    public function __construct(User $user, Action $action, Article $article)
    {
        $this->user = $user;
        $this->action = $action;
        $this->article = $article;
    }
}
```

#### Creating a new role

To create a new role, extend the `Role` abstract class:

```php
class ArticleEditorRole extends Role
{
    protected $article;

    public function __construct(User $user, Article $article)
    {
        $this->user = $user;
        $this->article = $article;
    }

    public function getAuthorizations()
    {
        return [
            new ArticleAuthorization($this->user, Action::VIEW(), $this->article),
            new ArticleAuthorization($this->user, Action::EDIT(), $this->article),
        ];
    }
}
```

### Rebuilding authorizations

To manually rebuild all the authorizations:

```php
$userService->rebuildAuthorizations();
```

This is generally not needed, authorizations are automatically updated.
