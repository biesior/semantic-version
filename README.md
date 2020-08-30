# `semantic-version` project

[![Donate](https://img.shields.io/static/v1?label=Donate&message=paypal.me/biesior&color=brightgreen "Donate the contributor via PayPal.me, amount is up to you")](https://www.paypal.me/biesior/19.99EUR)
[![State](https://img.shields.io/static/v1?label=alpha&message=0.0.15&color=blue 'Latest known version')](https://github.com/biesior/semantic-version/tree/0.0.15-alpha) <!-- __SEMANTIC_VERSION_LINE__ -->
![Updated](https://img.shields.io/static/v1?label=upated&message=2020-08-30+03:51:48&color=lightgray 'Latest known update date') <!-- __SEMANTIC_UPDATED_LINE__ -->
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

#### 4.2 bash script as a system command

Instead, you can create simple executable bash script to use it globally in your console with shorter version like 

```shell script
semantic-version <options> <optional-directory>
``` 

Ready to use `bash` script named `semantic-version` (without extension) can be found in `bin` folder in this repository.

`/path/to/downloaded/` is the absolute path of the folder where you downloaded or cloned the `semantic-version` from this repo.

If you don't have any executable scripts and don't want to create new folder for this only, just use it in downloaded localization

```shell script
chmod +x /path/to/downloaded/semantic-version
```

Otherwise, copy `semantic-version` command to folder with your executable scripts (`mkdir` if required) and chmod it to make executable

`/path/to/your/bin/` is the absolute path of the folder where you keep your executable scripts.

```shell script
mkdir /path/to/your/bin
cp /path/to/downloaded/bin/semantic-version /path/to/your/bin
chmod +x /path/to/your/bin/semantic-version
```

Just make sure it won't conflict with other exported paths. In case of doubts refer to section 5: Renaming.

Export paths to it ie in `~/.bash_profile` depending on your OS

```shell script
export PATH=/path/to/your/bin:$PATH

#or 
export PATH=/path/to/downloaded/bin:$PATH
```
and load paths with shell's builtin command `source` or re-open terminal

```shell script
source ~/.bash_profile 
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