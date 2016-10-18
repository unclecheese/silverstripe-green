# Why Green?

This module aims to solve an all-too familiar problem in SilverStripe: _the parity of page types and templates_. In doing so, it challenges many common conventions. 

Conventionally, every unique template requires:

* The creation of at least one PHP file
* Two PHP classes
* A database schema alteration
* At least one database record mutation

This amount of overhead and clutter is well worth the investment for page types that do anything beyond present content, but for page types that are simply containers for a design, this is a lot of extra effort and adds a lot of bloat. Over time, the codebase and the CMS UI become polluted with one-off page types that were created for God-knows-what back in God-knows-when for God-knows-what reason.

## A feast of delicious anti-patterns.

Yes, these are anti-patterns, but in many contexts, I believe the conventional approach _invites_ this sort of roguish behaviour.

# Getting started
There are just two things you need to get started -- a single page type, and any number of design modules.

## Create a "green" page type
In most cases, you should only need one page type that will serve as a container for all of your different design modules. This is done with an extension at the model level, and a parent class at the controller level.

_MyGreenPageType.php_
```php
class MyGreenPageType extends Page
{
	// Defines the type of serialised data. "JSON" or "YAML" (default is YAML)
	private static $extensions = [
		"GreenExtension('YAML')" 
	];
}

class MyGreenPageType_Controller extends UncleCheese\Green\Controller
{

}
```

## Create a design module

A design module is a folder that contains a single template, a content file (YAML, or JSON), and along with any number of CSS, Javascript, and images it requires.

### Configure your design folder

First, define the directory where we will keep our design modules:

```yaml
UncleCheese\Green\Green:  
  design_folder: '$ThemeDir/green'
```
_The `$ThemeDir` variable can be used as a placeholder for `themes/my-theme`, just like the global template variable._

### Create a design module folder

Now, create a subfolder in that directory. Its name will become a unique identifier for that design. *Each design module must contain `index.ss`*, and optionally `content.yml` or `content.json` to supply its custom fields.

*Example:*
```
themes/
    my-theme/
        green/
            my-awesome-design/
                index.ss
                content.yml
                my-style.css
                some-behaviour.js
```

Any `.css` or `.js` files will be automatically loaded.

### Create a template

Now lets's create a simple template. Don't worry about the fields. Those will come from our YAML file.

_themes/my-theme/green/my-awesome-design/index.ss_
```html
<h2>Hello, welcome to $Title</h2>
<h3>Here are the $Items.count products we have on special today:</h3>
<ul>
	<% loop $Items %>
	<li>$Title ($Price.Nice)</li>
	<% end_loop %>
</ul>
```

#### Using a custom "main" template

In most cases, the `index.ss` file will serve as the `$Layout` template, with `templates/Page.ss` serving as the main template. To override this behaviour, you can either:

* If the main template is **custom to this design**, you can create `main.ss` and `layout.ss` (instead of just `index.ss`)
* Or, if the main template is **already in the theme**, you can set `main_template: SomeExistingTemplate.ss` in your `config.yml` file.

### Supply some content

There are two ways we can feed content to the template. The simplest (and perhaps the most austere) way of providing content is through a YAML or JSON file in the design module.

_themes/my-theme/green/my-awesome-design/content.yml_
```yaml
Title: "Paul's Pet Shop"
Items:
  -
    Title: Lionfish
    Price: 40.00
  -
    Title: Clownfish
    Price: 10.00
```

Alernatively, if you want to be able to edit the content in the CMS, you can omit this file.

### Auto-populating content

Green can parse your `index.ss` template for variables and auto-create content using a task:

`$ framework/sake dev/tasks/GreenTemplateParserTask module=my-design-module`

This task looks at both variables and blocks defined in your template, converts them into the appropriate structure in JSON or YAML, and assigns placeholder content to them.

This feature is also available when editing YAML in the CMS. Just click (Load from template) above the code editor, and it will populate the editor automatically.


## Insert the page type into the site tree

In the CMS, create a new page of type `MyGreenPageType` (or whatever you called it). Click on the *Content & Design* tab.

Select from one of the design modules you've created.

If the module has its own content file, it will tell you so.

![screenshot1](../screenshot1.png?raw=true)

Otherwise, you'll be prompted to create some data.

![screenshot1](../screenshot2.png?raw=true)

That's it!

![screenshot1](../screenshot3.png?raw=true)

## What about casting?

You can do that!
 
```yaml
Title: Clownfish
Price: Currency|10
DateArrived: Date|2016-02-05
```

The module that provides the ability to traverse text fields as serialised data is [silverstripe-serialised-dbfields](http://github.com/unclecheese/silverstripe-serialised-dbfields). Read the docs for more information.

# Direct module access

For the hardcore treehuggers, you can actually configure your module to be directly accessible by URL, meaning *you don't have to create an entry in the site tree*. This is very useful for prototyping.

To do that, create a `config.yml` file in the design module, and define `public_url`.

_themes/my-theme/green/my-great-design/config.yml_
```yaml
public_url: share/the/love
```

This design will now be available on `http://yourwebsite.com/share/the/love`, without any need to access the CMS.

For obvious reasons, this feature only works if the module has a content file, as CMS access is out of the question.

# Troubleshooting

Ring Uncle Cheese.


