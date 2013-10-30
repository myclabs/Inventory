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

You need to have your entity implement the `Resource` interface:

```php
class Article extends Resource
{
    use ResourceTrait;

    /**
     * @var ArticleAuthorization[]|Collection
     */
    protected $acl;

    public function __construct()
    {
        $this->acl = new ArrayCollection();
    }
}
```

```yaml
Article\Domain\Article:
  type: entity

  oneToMany:
    acl:
      targetEntity: Article\Domain\ACL\ArticleAuthorization
      mappedBy: resource
      cascade: [ all ]
      orphanRemoval: true
```

To create authorizations on the new resource, you need to create a new kind of authorization:

```php
class ArticleAuthorization extends Authorization
{
    protected $resource;

    public function __construct(User $user, Action $action, Article $resource)
    {
        $this->user = $user;
        $this->setAction($action);
        $this->resource = $article;

        $this->resource->addToACL($this);
    }
}
```

```yaml
Article\Domain\ACL\ArticleAuthorization:
  type: entity

  oneToOne:
    resource:
      targetEntity: Article\Domain\Article
      inversedBy: acl
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

#### Keeping the authorizations up to date

When a user, or a resource is deleted, authorizations will be deleted in cascade by Doctrine.

However, if your resources have inheritance, you need to update the authorizations when the inheritance changes
(resource removed from the tree, resource inserted in the tree, …).

A listener can update the authorizations when the resource changes:

```php
class ArticleListener
{
    public function preRemove(Article $article)
    {
        // Cascade remove
        foreach (ArticleAuthorization::loadByResource($article) as $authorization) {
            $authorization->delete();
        }
    }
}
```

YAML configuration:

```yaml
Article\Domain\Article:
  type: entity

  entityListeners:
    Article\Domain\ACL\ResourceListener\ArticleListener:
```

### Rebuilding authorizations

To manually rebuild all the authorizations:

```php
$userService->rebuildAuthorizations();
```

This is generally not needed, authorizations are automatically updated.