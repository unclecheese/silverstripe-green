<div style="text-align:center;"><img src="docs/green.png?raw=true" style="height:256px" /></div>

# SilverStripe Green

We're consuming way too many page types. Stop the pollution, clean up the litter, and save a site tree.

Go green.

## Hippie!

*SilverStripe Green is a micro-framework that offers a design-driven approach to creating page templates that need rapid prototyping and/or rarely change.* Instead of creating new page types every time you need a new design, you can create a self-contained design that supplies its own template, CSS and a serialised content structures in the form of YAML or JSON. These are called *design modules*.

## Installation
`composer require unclecheese/silverstripe-green`

## Dependencies
* [unclecheese/silverstripe-serialised-dbfields](http://github.com/unclecheese/silverstripe-serialised-db-fields)
* SilverStripe >= 3.1

## A feast of delicious anti-pattnerns.

This module aims to solve an all-too familiar problem in SilverStripe: _the parity of page types and templates_. In doing so, it challenges many common conventions. 

Conventionally, every unique template requires:

* The creation of at least one PHP file
* Two PHP classes
* A database schema alteration
* At least one database record mutation

This amount of overhead and clutter is well worth the investment for page types that do anything beyond present content, but for page types that are simply containers for a design, this is a lot of extra effort and adds a lot of bloat. Over time, the codebase and the CMS UI become polluted with one-off page types that were created for God-knows-what back in God-knows-when for God-knows-what reason.

Yes, these are anti-patterns, but in many contexts, the conventional approach _invites_ them.

## Sound interesting?

[Read the docs](docs/en/index.md). They're eco-friendly!

## Tests
`framework sake dev/tests/GreenTest`

