
# Welcome to semantic-version! help  
  
Author (c) 2020 Marcus Biesioroff biesior@gmail.com  
Newest version can be found at https://github.com/biesior/semantic-version  
Last update for this help at `2020-08-27 17:03:51`  
  


### What it does?  
  
This CLI stript sets or updates version according to schema and updates required files.  
  
It is still in development phase and lot of features should be implemented or improved.  
If you have any suggestion or problems visit https://github.com/biesior/semantic-version/issues  
Please use it with care!  
  
For more details please refer to these resources:  
- https://semver.org/spec/v2.0.0.html  
- https://en.wikipedia.org/wiki/Software_release_life_cycle  


### Help  

```
-h, --help         Displaying this help
```

### Display options  


#### Sample usages  
  
- `php semantic-version.php -xc <other params>` to display with clean output without colors
  
- `php semantic-version.php -hxc` to displays monochromatic help with clean output.
  
- `php semantic-version.php -h --markdown > HELP.md` to display this help as a markdown and ie save it to file.  
- etc.  


#### Parameters  

```
-c, --clean        If set console will be cleaned for better output

-x                 Extract colors, i.e. if you want to write the output to file like
                   `php semantic-version.php -h > version-help-color.txt`
                   `php semantic-version.php -hx > version-help-mono.txt`

--markdown         If set help will be generated in markdown format, i.e.
                   `php semantic-version.php -h --markdown > HELP.md`

--debug            If set some debug will occure, of course it's only for development stage
```

### Init, set, update or kill  


#### Parameters  

```
-i, --init         Create new version by default it will be `0.0.1-alpha`
                   - you can change it immediately using -m `set` or `update`

--repository       Repository URL ie `https://github.com/biesior/semantic-version/`

-m, --mode         Mode can be `set` or `update` 
                   - When mode is `set` params `-n` or `--new-version` and `-s` or `--state` are required 
                   - When mode is `update` param `-p, --part` is required

-n, --new-version  Version which should be set like 1.2.3

-s, --state        State which should be set like alpha, beta , stable

-p, --part         Part to update allowed `major`, `minor`, `patch`

-v, --version      Displays current version of the project

--kill             (destructive!) Deletes version file, you will need to start from beginning
```

### Rise params  
  
You can just upgrade existing project with PATCH, MINOR or MAJOR version like  


#### Parameters  

```
--patch            Increases PATCH version i.e.: `0.1.0-alpha` > `0.1.1-alpha`

--minor            Increases MINOR version i.e.: `0.1.1-alpha` > `0.2.0-alpha`

--major            Increases MINOR version i.e.: `0.2.0-alpha` > `1.0.0-alpha`

--set              Requires one or two following params version and state- first is version like `1.2.3`
                   - second is version like `alpha`}
```

### If this doc was too long to read in terminal consider visiting  
- https://github.com/biesior/semantic-version/blob/master/HELP.md  

