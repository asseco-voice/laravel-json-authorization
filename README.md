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
- calling a relation on a protected model doesn't protect the related model, so if you're lazy loading
something you have no way of protecting what is being resolved.

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

In order to use the package you need to run ``php artisan migrate``
which will publish 2 tables:

```
authorizations ----M:1--- authorization_models
```

``authorization_models`` - a list of full Eloquent (namespaced) models for models which are to be protected.
Package will automatically push to DB models which are authorizable (using ``AuthorizesWithJson`` trait), 
so no need to do that manually.
``authorizations`` - a list of roles and resource limits imposed on them.

With regard to the performance, everything is cached to the great extent, and invalidated and re-cached
upon rights change. 

If a model is authorizable, and no limit is present within ``authorizations`` table for the currently logged in
user, we are assuming that user has no rights to operate on the model. You are obligated to explicitly say who has 
the right for what. 

Possible rights are:
- create
- read
- update
- delete

Each role (or any other type of authorizable attribute, customizable through configuration) will have a 
set of rights (in JSON format) for a single model. 

By default, no model is authorizable, you need to explicitly add ``AuthorizesWithJson`` trait to it.

You are also required to implement a method from a ``AuthorizesUsers`` interface on your `User` model.
This method should return an array of roles (or other authorizable attribute). Array should contain 
the same properties which you are writing within the DB. If this is a role name, then let array return
role names for a given authenticated user, if those are role IDs, let an array return IDs. 

## In depth

Package is built on top of [JSON query builder](https://github.com/asseco-voice/laravel-json-query-builder)
where you can check query logic in depth. 

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
