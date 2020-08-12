# Laravel JSON authorization

This package enables authorization via JSON objects imposed
as a scope on each model which can be authorized.

Package is developed mainly for the purpose of multiple Laravel microservices
authorization having in mind to avoiding the additional trips to authorization service.

This also makes non-auth services self-contained. Auth should provide roles (or any other
form of authorization), while services should provide limits that are imposed on any of
the roles. Should auth service ever need to be replaced, the only responsibility is to 
re-map roles on a new auth service, and role limits will stay intact.  

## Why this approach?

This package offers a great flexibility for imposing rights on Eloquent models.
What makes the package unique is the concept switch in a way that you do not want to protect your
routes, but rather protecting the resource itself.

This in turn results in two great benefits which the route approach doesn't have out-of-the-box:
- calling a single endpoint doesn't mean that it operates on a single model, making it impossible
for the route approach to do the underlying protection for something which you meant to stay
protected.
- calling a relation on a protected model doesn't protect the related model, so if you're eager/lazy loading
something through Eloquent relations you have no way of protecting what is being resolved.

Resource protection here imposes limits you provided independently of where your request comes from.
We are doing that by taking advantage of Laravel scopes.

Of course, there are also some limitations:
- relation will not be protected if you manually forward a ``relation_id`` to model
I.e. ``ContactType`` has many `Contacts`. If you impose the right to only update contacts
with contact type ID 1, the following will still pass as valid:
``Contacts::create([... 'contact_type_id' = 2 ...])``
- package will try to authorize early based on the limitations provided, however on complex
limits imposed package will make a select on a DB which in some cases may prove to be a heavy action. 
This mostly affects create/update/delete rights, not read ones.

## Installation

Install the package through composer. It is automatically registered
as a Laravel service provider, so no additional actions are required to register the package.

``composer require asseco-voice/laravel-json-authorization``

## Usage

Package initialization requires few steps to set up:

1. [Pick authorizable models](#pick-authorizable-models)
1. [Migrate tables](#migrate-tables)
1. [Modify User](#modify-user)
1. [Attach permissions](#attach-permissions)

### Pick authorizable models

Models you want protected MUST implement ``AuthorizesWithJson`` trait.

### Migrate tables

Running ``php artisan migrate`` will publish 3 tables:

```
    authorizations ----M:1--- authorization_models
          |
          |
          M
          -
          1
          |
          |
authorization_manage_types
```

``authorization_models`` - a list of full Eloquent (namespaced) models for 
[authorizable models](#pick-authorizable-models). This table is filled out automatically 
upon package usage but is not deleted automatically if you remove the trait. Scanning of these models is 
done within the ``app`` folder and does not recurse within it, so in case you have a different folder 
structure, or need to implement external models, modify  the config ``models_path`` variable to include 
what you need.
``authorizations`` - a list of roles and resource limits imposed on them.
``authorization_manage_types`` - types represent different sets of things to authorize by. If you are
authorizing only by roles, then it makes sense to have only ``roles`` there, however there may be cases
where you'd like to merge roles from different sets of authorizable properties (roles, groups, IDs etc) in
which case you will add those as well. 

With regard to the performance, everything is cached to the great extent, and invalidated (TODO) and re-cached
upon rights change. 

Two seeders are available. If you want to use them, attach them directly to your ``DatabaseSeeder``.

``AuthorizationManageTypesSeeder`` - will add `roles`, `groups` and `id` as type sets. 
``AuthorizationModelSeeder`` - is not a seeder per-se, as it will read 
[authorizable models](#pick-authorizable-models) and add them to DB.

### Modify User

User should implement ``AuthorizesUsers`` interface which requires you to implement a single method.

The method should return array of authorizable sets. This needs to reflect ``authorization_manage_types`` 
types as array keys. Values should pull an array of roles/groups/IDs for the current user.

Example:

``authorization_manage_types``
```
ID Name
1  roles
2  groups
3  id
```

```
public function getAuthorizableSets(): array
{
    return [
        'roles'  => Auth::user()->roles,
        'groups' => Auth::user()->groups,
        'id'     => Auth::user()->id,
    ];
}
```

You don't need to implement all of those though. This is valid as well:

```
public function getAuthorizableSets(): array
{
    return [
        'roles'  => Auth::user()->roles
    ];
}
```

Depending on where the set is coming from, you can give it any method which will return an array of 
things to authorize by:

```
public function getAuthorizableSets(): array
{
    return [
        'roles'  => $someClass->methodCall(Auth::user()->id, 'https://my-external-service')
    ];
}
```

Once resolved, function should return for example:

```
return [
    'roles' => ['role1', 'role2'...],
    'groups' => ['group1', 'group2'...],
    ...
]
```

It is worth mentioning that final product is merge of role rules. 

Example:

```
role 1: "read" right for IDs 1, 2 and 3
role 2: "read" right for IDs 4, 5 and 6

Final "read" right for that user are IDs 1, 2, 3, 4, 5 and 6
```

### Attach permissions

If a model is authorizable, and no limit is present within ``authorizations`` table for the currently logged in
user, we are assuming that user has no rights to operate on the model. You are obligated to explicitly say who has 
the right for what. 

Possible rights are:
- create
- read
- update
- delete

Each role will have a set of rights (in JSON format) for a single model. 

Package is built on top of [JSON query builder](https://github.com/asseco-voice/laravel-json-query-builder)
where you can check query logic in depth, with the addition of an absolute right ``*``. To use it 
you can do:

```
{
    "read": "*"
}
```

Giving you a read right to all rows for the given model.

In case you need some sort of admin available which has absolute rights to everything, publish the configuration
and add it to ``absolute_rights`` key, and you will not need to give the explicit rights for it.

## Example

Let's assume we have the following model protected:

``authorization_models``
```
ID Name
1  App\Contact
```

Let's impose the rights  for a role called ``agent``

``authorizations``
```
ID  Role    Authorization model ID
1   agent   1                      

Rules
{
	"create": "*",
	"read": {
		"search": {
			"id": "=1;2;3;4;5"
		}
	},
	"update": {
		"search": {
			"id": "=!2"
		}
	}
} 
```

These rights can be roughly translated as follows:
- you can create a contact without limitations
- you can only read contacts with IDs 1, 2, 3, 4 and 5. This means that calling ``Contact::all()`` will 
return only 5 records. Also, calling ``Contact::find(6)`` will not return the record. 
- you can update any contact with ID != 2. It is important to say that read right is a top-level right 
which will in start limit the possible output to 1, 2, 3, 4 and 5 effectively saying that you can 
update IDs 1, 3, 4, and 5. Others are forbidden through an imposed read right.
- since delete option is omitted, you have no right for deleting any contact

## Additional

For dev purposes, you can disable authorization completely by adding this to your ``.env`` file:

    OVERRIDE_AUTHORIZATION=true
