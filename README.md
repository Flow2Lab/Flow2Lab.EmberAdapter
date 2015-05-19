A EmberJs adapter for TYPO3 Flow
================================

This package enables the exposing of TYPO3 Flow objects to EmberJs based on the Ember-data implementation.

Goals:
-----

Expose objects to EmberJs in a easy to use and extensible manor.

Quick Guide:
-------

Installation:
-------------

Configuration:
--------------

To expose a third-party package model you can use YAML. First you create a `EmberModels.[MyCustomName].yaml`, second you add the following 
yaml code:

```YAML

'MyPackage\Key\Domain\Model\MyModel':
  modelName: 'MyModel' 
  properties:
    description:
      type: 'string'
      options: []
    priority:
      type: 'number'
      options: []
    creationDatetime:
      type: 'date'
      options: []
    viewed:
      type: 'boolean'
      options: []

```

Contributions:
--------------

To contribute to this package please fork the package and make a pull request.

Testing:
--------


Authors:
--------

Author: Philipp Maier

Author: Sebastiaan van Parijs (<svparijs@refactory.it>)

License:
--------
Copyright 2015 Philipp Maier & Sebastiaan van Parijs

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.