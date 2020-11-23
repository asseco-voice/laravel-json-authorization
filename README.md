<p align="center"><a href="https://see.asseco.com" target="_blank"><img src="https://github.com/asseco-voice/art/blob/main/evil_logo.png" width="500"></a></p>

# Laravel JSON authorization 

This package enables authorization via JSON objects imposed on each model which can be authorized.

Package is developed mainly for the purpose of multiple Laravel microservices
authorization having in mind to avoiding the additional trips to authorization service.

This also makes non-auth services self-contained. Authentication service should provide roles (
or any other form of authorization), while services should provide limits that are imposed on any of
the roles. Should auth service ever need to be replaced, the only responsibility is to 
re-map roles on a new auth service, and role limits will stay intact.  

## Why this approach?

This package offers a great flexibility for imposing rights on Eloquent models.
What makes the package unique is the concept switch in a way that you do not want to protect your
routes, but rather **protecting the resource** itself.

This in turn results in two great benefits which the route approach doesn't have out-of-the-box:
- calling a single endpoint doesn't mean that it operates on a single model, making it impossible
for the route approach to do the underlying protection for something which you meant to stay
protected.
- calling a relation on a protected model doesn't protect the related model, so if you're eager/lazy loading
something through Eloquent relations you have no way of protecting what is being resolved.

Resource protection here imposes limits you provided independently of where your request comes from.
We are doing that by taking advantage of Laravel scopes and Eloquent events.

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

## Terminology

- calling something **authorizable** means it is capable of being authorized
- **authorizable set** - collection of authorizable user properties 
(i.e. a collection of **roles** classifies as an **authorizable set**)  
- **authorizable set value** - single object within an **authorizable set** (i.e. a single role - `example_role_1`)
- **authorizable set type** - logical **authorizable sets** separation
(i.e. you can have a set of **roles**, set of **groups**... which would classify as an **authorizable set type**)
- **authorizable model** - model upon which the authorization can be enforced
- **right** - a single CRUD right for a single **authorizable model**, and a single **authorizable set value**
(i.e. having a **create right** for some model)
- **rule** - set of **rights** for a single **authorizable model**, and a single **authorizable set value**

## Usage

Package initialization requires few steps to set up:

1. [Pick authorizable models](#pick-authorizable-models)
1. [Migrate tables](#migrate-tables)
1. [Modify User](#modify-user)
1. [Attach rules](#attach-rules)
1. [Flush cache](#flush-cache)

### Pick authorizable models

Models you want protected MUST implement ``Voice\JsonAuthorization\App\Traits\Authorizable`` trait.

After this is done, be sure to run ``php artisan voice:sync-authorizable-models`` to sync models which
implement ``Authorizable`` trait with the DB.

Run this command each time you add or remove ``Authorizable`` trait from a model.

If model already has relation to some rules, the command will throw an exception. This is purposely done
to make you manually delete rules for the models you're about to delete, so that it doesn't happen
by accident.

### Migrate tables

Running ``php artisan migrate`` will publish 3 tables:

```
    authorization_rules ----M:1--- authorizable_models
          |
          |
          M
          -
          1
          |
          |
 authorizable_set_types
```

``authorizable_models`` - a list of full Eloquent (namespaced) models for 
[authorizable models](#pick-authorizable-models). This table is filled out automatically 
upon package usage but is not deleted automatically if you remove the trait after it is already written
in the DB. Only models within ``app`` folder are scanned. In case you have a different folder 
structure, or need to implement external models, [modify the config](#additional) ``models_path`` variable to include 
what you need.

``authorization_rules`` - a list of [authorizable set values](#terminology) and [rules](#terminology) 
imposed on them.

``authorizable_set_types`` - types represent different sets of things to authorize by. If you are
authorizing only by roles, then it makes sense to have only ``roles`` there, however there may be cases
where you'd like to merge [authorizable set values](#terminology) from different 
[authorizable set types](#terminology) in which case you will add those as well. 

With regard to the performance, everything is cached to the great extent, invalidated and re-cached
upon change. 

Seeders are available to use by including `AuthorizationSeeder` (wrapper for several seeders)
within your app ``DatabaseSeeder``. If needed, you can include single seeders from that class as well. 

### Modify User

User should implement ``AuthorizesUsers`` interface which requires you to implement a single method.

The method should return an array of [authorizable sets and their values](#terminology) for 
currently authenticated user.
 
This needs to reflect names from ``authorizable_set_types`` table as array keys, 
and [authorizable set values](#terminology) for each [authorizable set type](#terminology) set.

Example:

``authorizable_set_types``
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

You don't need to implement all of these though. This is valid as well (as long as `roles` are under 
`authorizable_set_types` table):

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

### Attach rules

If a model is [authorizable](#terminology), and no limit is present within ``authorization_rules`` table for the 
currently logged in user, we are assuming that user has no rights to operate on the model. 
You are obligated to explicitly say who has the right for what. 

Possible rights are:
- create
- read
- update
- delete

Each [authorizable set value](#terminology) will have a set of [rules](#terminology) (in JSON format) 
for a single model. 

Package is built on top of [JSON query builder](https://github.com/asseco-voice/laravel-json-query-builder)
where you can check query logic in depth, with the addition of an absolute right ``*``. 

To use the absolute right, you can do:

```
{
    "read": "*"
}
```

Giving you a read right to all rows for the given model.

In case you need some sort of admin available which has absolute rights to everything, 
[publish the configuration](#additional) and add it to the ``absolute_rights`` key, 
and you will not need to give the explicit CRUD rights for it.

#### Universal (virtual) role

If you have the need to protect resources globally or give the permission for a single resource to all users
across the system, you can do so by utilizing universal (virtual) role. By default, that role is 
``voice-all-mighty``, but can be overridden with `.env` value `UNIVERSAL_ROLE`.

A universal role **MUST NOT** exist as a standard role within your auth service. It will conflict with this and
will not work well.

This works in a way that you will i.e. give a read right for some resource to **universal role** which will 
then be inherited by all other users.

Example:

```
ID  Role               Authorization model ID
1   voice-all-mighty   1                      

Rules
{
	"read": {
		"search": {
			"id": "=1"
		}
	}
} 
```

Will give a read right to model ``1`` to all users across the system independently of their system roles.

### Flush cache

Due to the heavy workload this package has to do, everything is cached with 1 day TTL. 
Be sure to flush the cache after each manual code update (i.e. you add `Voice\JsonAuthorization\App\Traits\Authorizable` trait on a model).

You can flush the cache [the Laravel way](https://laravel.com/docs/7.x/cache#removing-items-from-the-cache),
or if you're using Redis as your cache driver you may use [one of our packages](https://github.com/asseco-voice/laravel-redis-cache-extension)
to enable a wildcard Redis flush.

## Example

Let's assume we have the following model protected:

``authorizable_models``
```
ID Name
1  App\Contact
```

Let's impose the rights  for a role called ``agent``

``authorization_rules``
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

Publish and override the configuration for the package:

    php artisan vendor:publish --provider="Voice\JsonAuthorization\JsonAuthorizationServiceProvider"
