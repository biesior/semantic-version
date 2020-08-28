# `semantic-version` project

[![Donate](https://img.shields.io/static/v1?label=Donate&message=paypal.me/biesior&color=brightgreen "Donate the contributor via PayPal.me, amount is up to you")](https://www.paypal.me/biesior/19.99EUR)
[![State](https://img.shields.io/static/v1?label=alpha&message=0.0.10&color=blue 'Latest known version')](https://github.com/biesior/semantic-version/tree/0.0.10-alpha) <!-- __SEMANTIC_VERSION_LINE__ -->
![Updated](https://img.shields.io/static/v1?label=upated&message=2020-08-28+00:33:27&color=lightgray 'Latest known update date') <!-- __SEMANTIC_UPDATED_LINE__ -->
[![Minimum PHP version](https://img.shields.io/static/v1?label=PHP&message=7.0.0+or+higher&color=blue "Minimum PHP version")](https://www.php.net/releases/7_0_0.php)

### 0. Disclaimer

All versions lower than `0.1.0` are considered as POC and should not be used in production! 

What's more important, **any** API changes may come without any warning!.  

### 1. What it does?

PHP class for managing semantic versions in custom project.

For more detail please go to [HELP.md](HELP.md) file  
or run help for this script in the console with `php version.php -h`

### 2. Should I use it?

It's up tou you. All, this script does is rising version number with some schemas i.e. as described in [Semantic Versioning spec 2.0.0 (or later)](https://semver.org/spec/v2.0.0.html) and optionally updates some additional files like README.md. 

Most important is that's non-public, so you do not need to register to any repositories or package managers.

#### 2.1 Suggested usages are:

- For projects which shouldn't be published yet, ie. they are in very begging phase that shouldn't even be called _realise_. 
- For projects which shouldn't be published for any other reasons, like security, business, whatever.

### 3. Alternatives

There are already several solutions wich _can_ or at lest _try_ to manage your version changes.

### 4. Make `semantic-version.php` available as a system command. 

#### 4.1 PHP script
Semantic version is a PHP script, so you can use it without any additional works as:

```shell script
php semantic-version.php <options>
```

If this satisfies you, you can skip this rest of this section.

#### 4.2 System command

Instead, you can create simple executable bash script to use it globally in your console with shorter version like 

```shell script
semantic-version <options> <optional-directory>
``` 

```shell script
mkdir /path/to/your/executables
touch /path/to/your/executables/semantic-version
chmod +x /path/to/your/executables/semantic-version
```

In your favourite editor just edit `/path/to/your/executable/semantic-version` and add these lines

```shell script
#!/bin/bash
# ychy

for LASTARG in "$@"; do :; done

if [[ -d $LASTARG ]]; then
  echo switched to "$LASTARG"
  cd "$LASTARG" || return
fi

if php semantic-version.php "$@" --from-bash ; then
  exit 0
else
  echo -e "\033[0;31m
It looks there is no semantic-version.php in this directory.\033[0m

Please visit https://github.com/biesior/semantic-version to get newest release.
"
  exit 1
fi
```

So finally you can use it from any place (without required `cd` to project like:

```shell script

```

Export paths to it ie in `.bash_profile`

```shell script
export PATH=/www/githubprojects.loc/bash-scripts/executable:$PATH
```

### 5. Renaming

If for some reason listed below you want to rename `semantic-version.php` to anything else, just... do it!

In such case remember to rename and fix also the system command if you created it like mentioned in point 4.2 of this doc.


### 6. Contribute
[![Open bug issues on GitHub](https://img.shields.io/static/v1?label=issues&message=bug&color=d73a4a "Something isn't working")](https://github.com/biesior/semantic-version/labels/bug)
[![Open enhancement issues on GitHub](https://img.shields.io/static/v1?label=issues&message=enhancement&color=0e8a16 "New feature or request")](https://github.com/biesior/semantic-version/labels/enhancement)
[![Open documentation issues on GitHub](https://img.shields.io/static/v1?label=issues&message=documentation&color=0075ca "Improvements or additions to documentation")](https://github.com/biesior/semantic-version/labels/documentation)
[![Open question issues on GitHub](https://img.shields.io/static/v1?label=issues&message=question&color=d876e3 "Further information is requested")](https://github.com/biesior/semantic-version/labels/question)
[![Open help wanted issues on GitHub](https://img.shields.io/static/v1?label=issues&message=help+wanted&color=008672 "Extra attention is needed")](https://github.com/biesior/semantic-version/labels/help%20wanted)

#### 6.1 Who can contribute?

Everyone with specific knowledge and skills is more than welcome to contribute!

#### 6.2 How can I contribute?

- `6.2.1` Just by creating, commenting or resolving issues on https://github.com/biesior/semantic-version/issues
- `6.2.2` Preview the list of issues placed at the top of contributon section and decide if yo want and may to help.
- `6.2.3` You are free to create pull request with your code  

You can contribute to the project by resolving or creating issues in above categories.