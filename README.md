Jailson for CakePHP
-----------------------
A simple but highly flexible access control plugin with a human interface.


Syntax
----------------------
Let's assume that we have two model objects loaded with one record each. 

```php
$this->User->id = '4c8b8d63-9ed4-449a-afe7-a7a6e9f4bebc';
$this->Project->id = '4c92dc36-c078-43f8-b8a8-89f2e9f4bebc';
```

In order to create a relationship we go from the owner object (User) and pass the target object along
with a description.

```php
$this->User->is('admin', $this->Project, true);
```

As you can see, the syntax is very natural and almost needs no explanation. The last parameter is a
boolean that basically defines the expected return value (actually, if omitted will not create the
relationship in question).

```php
$this->User->is('admin', $this->Project) ? 'yep' : 'nope';
// output: 'yep'
```

More sugar, please
----------------------
The Semantics, or "Why i really like this"

There is a set of semantic suffixes that you can use while describing relationships. Their whole purpose
is to make the syntax more readable and you can embrace or omit them.

* `_of` - example: `member_of`
* `_at` - example: `located_at`
* `_on` - example: `based_on`
* `_by` - example: `referred_by`
* `_for` - example: `responsible_for`
* `_in` example: `seen_in`

With these you may write role assignments like this:

```php
$this->User->is('admin_of', $this->Project, true);
```

Example within a condition:
```php
if ($this->User->is('owner_of', $this->Image)) {
  // allow something
};
```

Descriptions
----------------------
Please do not think of this as a mere "is he admin of that" type test. You can define as many roles as you
want and how you structure and name them is completely up to you. Every description is a group in itself or a
combination of many.

In the examples above i used "admin" and "responsible". These created two entries in the database and may
mean totally different things. It depends on the code what being in the responsible group means for the User.

Let me illustrate this with a more abstract way of describing, outside of a "is he admin" context.

```php
if ($this->Artist->did('paint', $this->Picture)) {
    if ($this->Artist->is('located_at', $this->Gallery)) {
        $this->Gallery->...
    }
}
```

One object (an Artist) can be two or more things and the semantic sugar was added to make them easier to
manage and test without introducing new database fields, methods and bits every time you feel like adding
something new. The descriptions is an independent layer for your business logic in a non-strict way.

### Using arrays

But of course there are other, less verbose ways of testing and describing one ore more things at a time. 

```php
$groups = array(
    'contributor',  
    'editor',
    'owner'
    );
    
$this->User->is($groups, $this->Document, true);
```

And of course there is lax AND-type test possible. In this example we only require 2 of the 3 existing roles
on the user object and check them via "isNot"

```php
public function update($data)
{
    $requiredGroups = array(
        'contributor',
        'editor'
        );
    
    if (!$this->User->isIn($requiredGroups, $this->Document)) {
        return false;
    }
    
    return $this->Document->save($data);
}
```

World without Objects
----------------------
In all the previous examples it was all about A-something-B and A-plenty-B. But what if there is no B?

Jailson is just designed to understand Model-objects, but first of all a tool to describe stuff using simple strings.
That way you can omit the object parameter and replace it with arbitrary string of your choice.

```php
$this->User->is('living_in', 'Europe');
$this->Image->was('posted_on', 'Twitter');
$this->Game->was('won_at', '2013');
$this->Member->did('join_in', '1980');
```

But don't overdo it. This looks like a schemaless model of some sort, but there is a limit to what you can store.
Single words and numeric values should be no problem. Not so sure about sentences and spaces, etc. I will write
extensive tests for this feature later. But as i said before: it works, but only to a certain degree where other
ways of storing data are becoming more appropiate anyway.

Aliases
----------------------
Since methods often say more than words ... or something like that.

* `has()`
* `was()`
* `did()`
* `isIn()`

More are planned, as i am going to refactor/branch this soon.


Querying
----------------------
As you may have noticed, there is no real lookup api and there is a reason for that: This library is only for
testing if something-is-something and stores these informations in an abstract way. A lookup or grouped results
would probably be very inefficient. However, there are model focused methods to bulk edit and list informations.

### Roles
This will return all roles and their meta details for the current user.

```php
$this->User->id = '4c8b8d63-9ed4-449a-afe7-a7a6e9f4bebc';
$roles = $this->User->roles();
```

This will return all roles for the current user, but without the meta details.

```php
$this->User->id = '4c8b8d63-9ed4-449a-afe7-a7a6e9f4bebc';
$roles = $this->User->roles($skipMeta = true);
```

Removing
----------------------

To remove a single group from an Model, use the `release` method.
```php
$this->User->id = '4c8b8d63-9ed4-449a-afe7-a7a6e9f4bebc';
$this->User->release('owner');
```

To remove a all groups from an Model, use the `free` method.
```php
$this->User->id = '4c8b8d63-9ed4-449a-afe7-a7a6e9f4bebc';
$this->User->free(); 
```



Integration
----------------------
You simply include a behaviour on any model and you can perform all of the above with it.

### Install
But before you start you need to create the necessary db table and files.
```
  cd app/plugins
  git clone git://github.com/m3nt0r/cakephp-jailson.git jailson
  cake schema create jailson -plugin jailson
```

### Models
All models that you want to control need to include the behaviour. It will add the "is()" method and others.
```php
    var $actsAs = array(
        'Jailson.Inmate'
    );
```

### Controllers
There is a huge AuthComponent extension in place which will be revised soon. Therefore i am not going to
explain it now as nothing has changed. For reference please see `legacy/README.textile`. 


Project Status
----------------------
The current API is fully tested and works as intended. I plan to branch this project and enhance the idea
behind it which may include refactors. Before this change, the current working version will be moved
into a legacy branch and master-branch is going to be replaced with the latest and greatest. I have many
new ideas for this, which is why rewrote the README with just the good parts that are probably a keeper. :-)

Contributing
----------------------
fork, edit, test, commit, submit pull request, wait :)


License
----------------------
MIT License  
Copyright (c) 2010-2013, Kjell Bublitz.  
See LICENSE for more info.


