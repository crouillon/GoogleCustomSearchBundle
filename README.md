GoogleCustomSearchBundle
=====================

[![Build Status](https://travis-ci.org/Lp-digital/GoogleCustomSearchBundle.svg?branch=master)](https://travis-ci.org/Lp-digital/GoogleCustomSearchBundle)
[![Code Climate](https://codeclimate.com/github/Lp-digital/GoogleCustomSearchBundle/badges/gpa.svg)](https://codeclimate.com/github/Lp-digital/GoogleCustomSearchBundle)
[![Test Coverage](https://codeclimate.com/github/Lp-digital/GoogleCustomSearchBundle/badges/coverage.svg)](https://codeclimate.com/github/Lp-digital/GoogleCustomSearchBundle/coverage)

**GoogleCustomSearchBundle** adds Google Custom Search feature in BackBee (v1.1.1 min).
It provides:
 * a simple search engine handled by javacript from Google,
 * and a fully customizable API search engine.

This bundle support multi-sites instances.

Installation
---------------

Edit the file `composer.json` of your BackBee project.

Add the new dependency to the bundle in the `require` section:
```
# composer.json
...
    "require": {
        ...
        "lp-digital/gcse-bundle": "~1.0"
    },
...
```

Save and close the file.

Run a composer update on your project.


Activation
--------------

Edit the file `repository/Config/bundles.yml`of your BackBee project.

Add the following line at the end of the file:
```
# bundles configuration - repository/Config/bundles.yml
...
gcse: LpDigital\Bundle\GoogleCustomSearchBundle\GoogleCustomSearch
```

Save and close the file.

Depending on your configuration, cache may need to be clear.


Configuration and use
--------------------------------

To use Google Custom Search on your project, the administrative panel of the bundle will ask you:

 * a Custom Search engine ID,
 * a Google key developer.

To get them, you need at first to [create an instance of a Custom Search Engine](https://developers.google.com/custom-search/docs/tutorial/creatingcse).

Then, from the Google [control panel](https://cse.google.com/manage/all), get your newly created  Custom Search engine ID. Copy paste it in the administrative panel.

Finally, copy/paste your Google API developer key for authentication (can be got and created on the [Developer Console](https://console.developers.google.com/)).

---

*This project is supported by [Lp digital](http://www.lp-digital.fr/en/)*

**Lead Developer** : [@crouillon](https://github.com/crouillon)

Released under the GPL3 License
