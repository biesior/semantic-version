<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

// Constants you can change
// Especially REPOSITORY* can be changed per project
const SEMANTIC_REPOSITORY = 'https://github.com/biesior/semantic-version/';
const SEMANTIC_REPOSITORY_TREE = SEMANTIC_REPOSITORY . "tree/";
const SEMANTIC_REPOSITORY_ISSUES = SEMANTIC_REPOSITORY . "issues";
const SEMANTIC_VERSION_FILE = 'current-semantic-version.json';

// constants for formatting, don't change with no reason
const EOL = PHP_EOL;
const EOLx2 = EOL . EOL;


/**
 * Class SemanticVersion
 *
 * @author (c) 2020 Marcus Biesioroff <biesior@gmail.com>
 */
class SemanticVersion
{

    private $currentVersionFile = SEMANTIC_VERSION_FILE;

    /**
     * Name of this script to reference ie in help, is set in constructor
     *
     * @var string
     * see __construct()
     */
    private $selfName = '';
    private $title = 'Semantic version updater for project' . EOLx2;
    // TODO to handle with init, set update methods and keep it in current_semantic_version.json
//    private $repository = SEMANTIC_REPOSITORY;
//    private $repositoryTree = SEMANTIC_REPOSITORY_TREE;
    // TODO to handle with init, set update methods and keep it in current_semantic_version.json
    private $isVersionDisplayOnWebAllowed = false;

    private $isDebugEnabled = false;
    private $isColorsEnabled = true;
    private $isMarkdownOutput = false;


    /**
     * SemanticVersion constructor.
     */
    public function __construct()
    {

        $requiredPhp = '7.0.0';
        $currentPhp = phpversion();
        if (!version_compare($currentPhp, $requiredPhp, '>=')) {
            die("This project requires PHP ver. `{$requiredPhp}` or higher, but `{$currentPhp}` is used, exiting." . PHP_EOL);
        }
//        die($this->testColoring(true, false));


        $env = $this->checkCurrentEnv();
        if ($env == 'web' && count($_GET) == 0 && count($_POST) == 0) {
            if (!$this->isVersionDisplayOnWebAllowed) {
                die('These data are protected. Bye!');
            }

            $currentVersion = $this->fetchCurrentVersionFromFile(true);
            if (is_array($currentVersion)) {
                $currentVersion = json_encode($currentVersion, JSON_PRETTY_PRINT);
            }
            header('Content-Type: application/json');
            die($currentVersion);
        } else if ($env != 'cli') {
            die(PHP_EOL . 'This script can be only executed in the CLI, bye!' . EOLx2);
        }


        $this->selfName = 'php ' . basename(__FILE__);

        $parameters = [

            'mixed' => [
                'c' => 'clean',
                'x' => 'extract-colors',
            ],
            'long'  => [
                'debug',
                'markdown',
                'from-bash::',
                'test-formats'
            ]
        ];

        $options = $this->getOptions($parameters);

        if (array_key_exists('debug', $options)) {
            $this->isDebugEnabled = true;
        }
        if (array_key_exists('markdown', $options)) {

            $this->isMarkdownOutput = true;
        }
        if (array_key_exists('x', $options)) {
            $this->isColorsEnabled = false;
        }


        if (array_key_exists('from-bash', $options) && SemanticVersionUtility::stringLength(trim($options['from-bash'])) != 0) {
            $this->selfName = trim($options['from-bash']);
        }
        if (array_key_exists('test-formats', $options)) {
            echo $this->testFormats(true, false);
        }

        if ($this->isDebugEnabled) {
            echo EOLx2;
            echo $this->____cliActions() . EOLx2;
            echo $this->____helperMethods() . EOLx2;
            echo $this->____methodsForFetchingCurrentVersion() . EOLx2;
            echo $this->____ansiMethods() . EOLx2;
            echo $this->____additionalOfDifferentPurposes() . EOLx2;
            echo EOLx2;
        }

        if (isset($options['c']) || isset($options['clean'])) {
            system('clear;');
            echo ("\e[2;36m^--- For previous output scroll up") . $this->ansiEscape() . EOLx2;
        }

        $this->dispatcher();
    }

    /**
     * Methods for handling dispatcher requests (doing class' logic) starts here
     *
     * DO NOT move this method in the structure without reason.
     *
     * @return string Formatted filename:number + phpdoc (if any)
     * @throws ReflectionException
     * @internal This method returns the filename and line number where group of specific methods starts
     */
    private function ____cliActions(): string
    {
        return self::describeMethodItself();
    }

    private function dispatcher()
    {
        global $argv, $argc;


        $parameters = [
            'short' => [
                'a',
                'b'
            ],
            'mixed' => [
                'h'  => 'help',
                'c'  => 'clean',
                'x'  => 'xtract-colors',
                'i:' => 'init:',
                'm:' => 'mode:',
                'n:' => 'new-version:',
                's:' => 'state:',
                'p:' => 'part:',
                'v'  => 'version',
                'V'  => 'version-verbose',
            ],
            'long'  => [
                'patch',
                'minor',
                'major',
                'repository:',
                'folder:',
                'set:',
                'kill::',
                'markdown',
                'debug',
            ]
        ];

        $options = $this->getOptions($parameters);
        if ($this->isDebugEnabled) {
            print_r(
                [
                    'argv'    => $argv,
                    'argc'    => $argc,
                    'options' => $options,
                ]
            );
        }


        $help = $this->getOptValue($options, 'h', 'help');
        $init = $this->getOptValue($options, 'i', 'init');
        $mode = $this->getOptValue($options, 'm', 'mode');
        $newVersion = $this->getOptValue($options, 'n', 'new-version');
        $state = $this->getOptValue($options, 's', 'state');
        $part = $this->getOptValue($options, 'p', 'part');
        $tree = $this->getOptValue($options, 't', 'tree');
        $version = $this->getOptValue($options, 'v', 'version');
        $versionVerbose = $this->getOptValue($options, 'V', 'version-verbose');


        $releasePatch = $this->getOptValue($options, null, 'patch');
        $releaseMinor = $this->getOptValue($options, null, 'minor');
        $releaseMajor = $this->getOptValue($options, null, 'major');
        $kill = $this->getOptValue($options, null, 'kill');


        if ($this->isDebugEnabled) {
            echo EOL . 'Debug resolved options' . EOL;
            print_r([
                'help'          => $help,
                'mode'          => $mode,
                'init'          => $init,
                'new-version'   => $newVersion,
                'state'         => $state,
                'part'          => $part,
                'release_patch' => $releasePatch,
                'release_minor' => $releaseMinor,
                'release_major' => $releaseMajor,
                'kill'          => $kill,
                'version'       => $version,
            ]);
            echo EOL . 'Debug options' . EOL;
            print_r($options);
        }

        if (!is_null($help)) {
            $this->helpAction();
        } else if (!is_null($version)) {
            $this->versionAction();
        } else if (!is_null($versionVerbose)) {
            $this->versionVerboseAction();
        } else if (!is_null($init)) {
            $this->initAction($init, $tree);
        } else if (!is_null($kill)) {
            $this->killAction($kill);
        } else if (!is_null($releasePatch)) {
            $this->updateAction('patch', null);
        } else if (!is_null($releaseMinor)) {
            $this->updateAction('minor', null);
        } else if (!is_null($releaseMajor)) {
            $this->updateAction('major', null);
        } else if (!is_null($mode)) {
            if ($mode == 'set') {
                if (is_null($newVersion) || is_null($state)) {
                    die("{$this->wrapRed('If `mode` is `set`, params `-n` or `--new-version` and `-s` or `--state` are required. Bye!')}" . EOLx2);
                }
                $version = $options['n'];
                $state = $options['s'];
                $this->set($version, $state);
            } else if ($mode = 'update') {
                if (is_null($part)) {
                    die($this->wrapRed('If `mode` is `update`, param`-p` or `--part` is required. Bye!') . EOLx2);
                }
                echo 'Make update';
            } else {
                die($this->wrapRed(sprintf("Mode %s is not supported, check the help with -h parameter. Bye!", $options['m'])) . EOLx2);
            }
        } else {
            $output = "
$this->title
{$this->wrapRed("Invalid parameters, check the help with -h parameter, like:")}

{$this->wrapCodeSample("{$this->selfName} -h")}

Bye!
            
";
            if (!$this->isColorsEnabled) $output = SemanticVersionUtility::removeAnsi($output);
            die($output);
        }

    }

    /**
     * Generates and displays the help
     *
     * @throws Exception
     */
    private function helpAction()
    {


        $now = new DateTime('now');
        $lastUpdate = $now->format('Y-m-d H:i:s');
        $help = new SemanticVersionHelp();

        $help->addHeader("{$this->wrapGreen('Welcome to semantic-version! help')}

Author (c) 2020 Marcus Biesioroff biesior@gmail.com
Newest version can be found at https://github.com/biesior/semantic-version
Last update for this help at `{$lastUpdate}`
"
            , 1);


        $help->addHeader("What it does?

This CLI stript sets or updates version according to schema and updates required files.

It is still in development phase and lot of features should be implemented or improved.
If you have any suggestion or problems visit {$this->wrapGreen(SEMANTIC_REPOSITORY_ISSUES)}
Please use it with care!

For more details please refer to these resources:
- https://semver.org/spec/v2.0.0.html
- https://en.wikipedia.org/wiki/Software_release_life_cycle"
            , 3);


        $help
            ->addHeader('Help')
            ->addParams(
                new SemanticVersionHelpParam('h', 'help', 'Displaying this help')
            );


        $help
            ->addHeader("Display options", 3)
            ->addHeader(
                [
                    'Sample usages',
                    '',
                    "- {$this->wrapCodeSample("{$this->selfName} -xc <other params>", true)} to display with clean output without colors",
                    "- {$this->wrapCodeSample("{$this->selfName} -hxc", true)} to displays monochromatic help with clean output.",
                    "- {$this->wrapCodeSample("{$this->selfName} -h --markdown > HELP.md", true)} to display this help as a markdown and ie save it to file.",
                    "- etc.",
                ],
                4
            )
            ->addHeader('<options>', 4)
            // --
            ->addParams(
                new SemanticVersionHelpParam(
                    'c',
                    'clean',
                    'If set console will be cleaned for better output'
                ),
                new SemanticVersionHelpParam(
                    'x',
                    null,
                    [
                        "Extract colors, i.e. if you want to write the output to file like",
                        $this->wrapCodeSample("{$this->selfName} -h > version-help-color.txt", true),
                        $this->wrapCodeSample("{$this->selfName} -hx > version-help-mono.txt", true),
                    ]
                ),
                new SemanticVersionHelpParam(
                    null,
                    'markdown',
                    [
                        "If set help will be generated in markdown format, i.e.",
                        $this->wrapCodeSample("{$this->selfName} -h --markdown > HELP.md", true)
                    ]
                ),
                new SemanticVersionHelpParam(
                    null,
                    'debug',
                    'If set some debug will occure, of course it\'s only for development stage')
            );


// --- REVERT THIS!!!!
        $help
            ->addHeader("Init, set, update or kill")
            ->addHeader('<options>', 4)
            ->addParams(
                new SemanticVersionHelpParam(
                    'i:',
                    'init:',
                    [
                        "Create new version by default it will be `0.0.1-alpha`",
                        "- you can change it immediately using -m `set` or `update`",
                    ]
                ),
                new SemanticVersionHelpParam(
                    null,
                    'repository:',
                    "Repository URL ie {$this->wrapGreen(SEMANTIC_REPOSITORY, true)}"
                ),
                new SemanticVersionHelpParam(
                    'm:',
                    'mode:',
                    [
                        'Mode can be `set` or `update` ',
                        '- When mode is `set` params `-n` or `--new-version` and `-s` or `--state` are required ',
                        '- When mode is `update` param `-p, --part` is required'
                    ]
                ),
                new SemanticVersionHelpParam(
                    'n:',
                    'new-version',
                    'Version which should be set like 1.2.3'
                ),
                new SemanticVersionHelpParam(
                    's:',
                    'state:',
                    'State which should be set like alpha, beta , stable'
                ),
                new SemanticVersionHelpParam(
                    'p:',
                    'part:',
                    'Part to update allowed `major`, `minor`, `patch`'
                ),
                new SemanticVersionHelpParam(
                    'v',
                    'version',
                    'Displays current version of the project'
                ),
                new SemanticVersionHelpParam(
                    null,
                    'kill::',
                    "({$this->wrapRed('destructive!')}) Deletes version file, you will need to start from beginning"
                )
            );


        $help
            ->addHeader(
                [
                    'Rise params',
                    '',
                    "You can just upgrade existing project with PATCH, MINOR or MAJOR version like"
                ]
            )
            ->addHeader('<options>', 4)
            ->addParams(
                new SemanticVersionHelpParam(
                    null,
                    'patch',
                    [
                        "Increases PATCH version i.e.: {$this->wrapGreen('0.1.0-alpha', true)} > {$this->wrapGreen('0.1.1-alpha', true)}",
                        $this->wrapCodeSample($this->selfName . ' --patch', true)
                    ]
                ),
                new SemanticVersionHelpParam(
                    null,
                    'minor',

                    [
                        "Increases MINOR version i.e.: {$this->wrapGreen('0.1.1-alpha', true)} > {$this->wrapGreen('0.2.0-alpha', true)}",
                        $this->wrapCodeSample($this->selfName . ' --minor', true)
                    ]
                ),
                new SemanticVersionHelpParam(
                    null,
                    'major',
                    [
                        "Increases MINOR version i.e.: {$this->wrapGreen('0.2.0-alpha', true)} > {$this->wrapGreen('1.0.0-alpha', true)}",
                        $this->wrapCodeSample($this->selfName . ' --major', true)
                    ]
                ),
                new SemanticVersionHelpParam(
                    null,
                    'set:',
                    [
                        "Requires one or two following params version and state" . "- first is version like {$this->wrapGreen('1.2.3', true)}",
                        "- second is version like {$this->wrapGreen('alpha', true)}}"
                    ]
                )
            );


        $help
            ->addHeader(
                [
                    'If this doc was too long to read in terminal consider visiting',
                    '',
                    "- {$this->wrapGreen(SEMANTIC_REPOSITORY . 'blob/master/HELP.md')}"
                ]
            );


//        $help
//            ->addHeader([
//                'Commands',
//                'Some functionalities are available by commands instead of parameters',
//                'That means you should use them as',
//                '',
//                "{$this->green(true)}{$this->selfName} <command>{$this->escape(true)}",
//                '',
//                'i.e. to rise the PATCH version just use that command:',
//                '', q
//                "{$this->green(true)}{$this->selfName} patch{$this->escape(true)}",
//            ])
//            ->addHeader(['Available commands'], 4)
//            ->addCommand(null, ['patch'], ['foo']);


        $help->render($this->isMarkdownOutput, $this->isColorsEnabled);

        exit(0);
    }

    /**
     * This is template action to reuse, don't change it just, copy, rename and implement new action
     *
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function templateAction()
    {
        $output = '';

        $output .= 'customize me!';

        if (!$this->isColorsEnabled) $output = SemanticVersionUtility::removeAnsi($output);
        echo $output;
    }

    private function versionAction()
    {
        $output = '';

        $output .= $this->fetchCurrentVersionToString();
        $output .= EOL;

        if (!$this->isColorsEnabled) $output = SemanticVersionUtility::removeAnsi($output);
        echo $output;
    }

    private function versionVerboseAction()
    {
        $output = '';
        $projectName = $this->fetchCurrentProjectName();
        $currentVersion = $this->fetchCurrentVersionToString();
        $output .= "
Current semantic version for {$this->wrapGreen($projectName )} project is:

{$this->wrapGreen($currentVersion)}

";


        if (!$this->isColorsEnabled) $output = SemanticVersionUtility::removeAnsi($output);
        echo $output;
    }

    private function initAction($projectName, $tree = null)
    {

        if (file_exists($this->currentVersionFile)) {

            $projectName = $this->fetchCurrentProjectName();
            $currentVersion = $this->fetchCurrentVersionToString();
            $lastUpdated = $this->fetchCurrentLastUpdated();

            die (sprintf("
{$this->wrapRed('Nothing happened!')}

File {$this->wrapGreen('%s')} already exists. 

Current version of {$this->wrapGreen('%s')} project is {$this->wrapGreen('%s')} last updated at {$this->wrapGreen('%s')}

If you want to re-initialize the project's version remove this file first.

Instead maybe you want update current version with --mode `set` or `update`.

Check the help for more details with command: 

{$this->wrapCodeSample("{$this->selfName} --help")}
  
",
                $this->currentVersionFile,
                $projectName,
                $currentVersion,
                $lastUpdated)
            );
        }

        $now = new DateTime('now');
        $lastUpdate = $now->format('Y-m-d H:i:s');
        $now2 = new DateTime('now');
        $lastUpdateLink = $now2->format('Y-m-d+H:i:s');

        $vers = [
            'project_name' => $projectName, 'version' => '0.1.0', 'state' => 'alpha', 'last_update' => $lastUpdate,
        ];

        if (!is_null($tree)) {
            $vers['working_tree'] = $tree;
        }


        $this->putNormalizedCurrentFile($vers);

        if (!file_exists('README.md')) {
            $fileTemplate = "## `{$projectName}` project

[![State](https://img.shields.io/static/v1?label=alpha&message=0.1.0&color=blue)](" . SEMANTIC_REPOSITORY_TREE . "}0.1.0-alpha  'Latest known version') <!-- __SEMANTIC_VERSION_LINE__ -->
![Updated](https://img.shields.io/static/v1?label=upated&message={$lastUpdateLink}&color=lightgray  'Latest known update date') <!-- __SEMANTIC_UPDATED_LINE__ -->";

            file_put_contents('README.md', $fileTemplate);

        }
        echo "
{$this->wrapGreen('Semantic version was initialized!')}

Semantic version numbering for project {$this->wrapGreen($projectName)} was initialized with version {$this->wrapGreen('0.1.0-alpha')}!

";

    }

    private function killAction($force = false)
    {
        $output = '';

        if (file_exists($this->currentVersionFile)) {
            $projectName = $this->fetchCurrentProjectName();
            $currentVersionLong = $this->fetchCurrentVersionToString();
        } else {
            $output = "
{$this->wrapRed('Opsss...')}

File {$this->wrapGreen($this->currentVersionFile)} doesn't exist, nothing to kill.

Bye!
            
";
            if (!$this->isColorsEnabled) {
                $output = SemanticVersionUtility::removeAnsi($output);
            }
            die($output);
        }
        $renameTo = 'zzz_unused_' . time() . '_' . $this->currentVersionFile;
        if (!in_array($force, ['soft', 'hard'])) {
            $output .= "
You are trying to remove {$this->currentVersionFile} from your project and disable this functionality in it

Of course it's your choice and if you are sure repeat this command with {$this->wrapGreen('force')} value, like

{$this->wrapCodeSample("{$this->selfName} --kill=soft")}
    
    to rename `{$this->currentVersionFile}` to `{$renameTo}` or 
    
{$this->wrapCodeSample("{$this->selfName} --kill=hard")}

    to remove it totally";

            if (!$this->isColorsEnabled) {
                $output = SemanticVersionUtility::removeAnsi($output);
            }
            die($output . EOLx2);
        }

        $currentVersionShort = 'unknown';
        $currentState = 'unknown';

        if (file_exists($this->currentVersionFile)) {
            $projectName = $this->fetchCurrentProjectName();
            $currentVersionLong = $this->fetchCurrentVersionToString();
            $currentVersionShort = $this->fetchCurrentVersion();
            $currentState = $this->fetchCurrentState();
        }

        $output .= "
{$this->wrapGreen('You sucessfully killed me')} {$this->wrapRed(';(')}

Versioning for project {$this->wrapGreen($projectName)} was killed, last known version was {$this->wrapGreen($currentVersionLong)}
        
";

        if ($force == 'soft') {
            rename($this->currentVersionFile, $renameTo);
            $output .= "File {$this->wrapGreen($this->currentVersionFile)} was renamed to {$this->wrapGreen($renameTo)}";
        } else if ($force == 'hard') {
            unlink($this->currentVersionFile);
            $output .= "File {$this->wrapRed($this->currentVersionFile)} was {$this->wrapRed('deleted')}";
        }
        $output .= EOLx2;


        $output .= "The functionality is disabled now." . EOL;
        if ($force == 'soft') {
            $output .= "
or just by manually restore it by renaming the backup file to {$this->wrapGreen($this->currentVersionFile)}.
or just by manually restore it by renaming the backup file to {$this->wrapGreen($this->currentVersionFile)}.


";
        }

        $output .= "To recreate it with last known version, initialize it again and set last known version and state like:" . EOLx2;

        $output .= $this->wrapCodeSample("{$this->selfName} -i=\"{$projectName}\"") . EOL;
        $output .= $this->wrapCodeSample("{$this->selfName} -m set -n {$currentVersionShort} -s {$currentState}") . EOL;


        $output .= "
Bye {$this->wrapRed(';(')}

";


        if (!$this->isColorsEnabled) {
            $output = SemanticVersionUtility::removeAnsi($output);
        }
        echo $output;
    }

    private function set($version, $toState)
    {
        $output = '';

        $now = new DateTime('now');
        $lastUpdate = $now->format('Y-m-d+H:i:s');
        $newVersionFull = $version . ($toState == 'stable' ? '' : '-' . $toState);
        $vers = [
            'project_name' => $this->fetchCurrentProjectName(), 'version' => $version, 'state' => $toState, 'last_update' => $lastUpdate,
        ];
        $this->putNormalizedCurrentFile($vers);

        $repository = SEMANTIC_REPOSITORY_TREE . $newVersionFull;

        $output .= EOLx2;
        $this->searchAndUpdate(
            'README.md',
            "[![State](https://img.shields.io/static/v1?label=%s&message=%s&color=blue 'Latest known version')](%s)",
            '<!-- __SEMANTIC_VERSION_LINE__ -->',
            $toState, $version, $repository
        );

        $output .= EOLx2;
        $this->searchAndUpdate(
            'README.md',
            "![Updated](https://img.shields.io/static/v1?label=upated&message=%s&color=lightgray 'Latest known update date')",
            '<!-- __SEMANTIC_UPDATED_LINE__ -->',
            $lastUpdate
        );
        $output .= EOLx2;


// TODO handle exact search
//        $prevVersionFull = $this->fetchCurrentVersionToString();
//        $searchXmlVersionStr = '        <version>%s</version>';
////        $searchXmlVersionStr = 'FOOOTOREPLACE%s';
//        $this->searchAndUpdateExact(
//            'README.md',
//            sprintf($searchXmlVersionStr, $prevVersionFull),
//            sprintf($searchXmlVersionStr, $newVersionFull),
//            $lastUpdate
//        );
//        $output .= EOLx2;


        // tagging before commit has no sense
//        $newTagName = $this->getCurrentVersionToString();
//        $tagCmd = "git tag {$newTagName}";
//        system($tagCmd);


        if (!$this->isColorsEnabled) {
            $output = SemanticVersionUtility::removeAnsi($output);
        }


        $output .= "Please push your update to GitHub and don't forget to publish new release!

Bye!

";


        echo $output;
    }

    private function updateAction($part, $toState = null)
    {

        if (is_null($toState)) {
            $currentState = $this->fetchCurrentState();
            if (!is_null($currentState)) {
                $toState = $currentState;
            } else {
                die ($this->wrapRed(sprintf("State couldn't be retrieved. Please fix your %s file and retry.\n\nBye!}", $this->currentVersionFile)) . EOLx2);
            }
        }

        $output = '';
        $currentData = $this->fetchCurrentVersionFromFile();
        $oldVersion = $currentData['version'];
        $oldState = $currentData['state'];
        $oldVersionFull = $oldVersion . ($oldState == 'stable' ? '' : '-' . $oldState);
        $newVersion = $this->calculateRise($oldVersion, $part, $toState, true);
        $newVersionFull = $newVersion . ($toState == 'stable' ? '' : '-' . $toState);
        $revertCommand = "{$this->selfName} -m set -n {$oldVersion} -s {$oldState}";
        $this->set($newVersion, $toState);
        $output .= (
            sprintf(
                "
Your version was updated from {$this->wrapGreen('%s')} to {$this->wrapGreen('%s')}

To revert this change please run: {$this->wrapGreen('%s')}
",
                $oldVersionFull, $newVersionFull, $revertCommand
            )
            ) . PHP_EOL;

        if (!$this->isColorsEnabled) {
            $output = SemanticVersionUtility::removeAnsi($output);
        }
        echo $output;

    }


    /**
     * Non-public methods for repeating logic a.k.a helpers starts here
     *
     * DO NOT move this method in the structure without reason.
     *
     * @return string Formatted filename:number + phpdoc (if any)
     * @throws ReflectionException
     * @internal This method returns the filename and line number where group of specific methods starts
     */
    private function ____helperMethods(): string
    {
        return self::describeMethodItself();
    }

    private function searchAndUpdate(string $filename, string $sprintfStr, string $lineEndsWith, ...$params)
    {

//        $replaced = '';
        if (count($params) == 1) {
            $replaced = sprintf($sprintfStr, $params[0]);
        } else if (count($params) == 2) {
            $replaced = sprintf($sprintfStr, $params[0], $params[1]);
        } else if (count($params) == 3) {
            $replaced = sprintf($sprintfStr, $params[0], $params[1], $params[2]);
        } else {
            throw new Exception(
                sprintf('Method searchAndUpdate requires max 3 additional parameters %s given', count($params)),
                1597754663
            );
        }

        $txt = file($filename);

        foreach ($txt as $lineNo => $line) {
            if (SemanticVersionUtility::endsWith(trim($line), trim($lineEndsWith))) {
                $txt[$lineNo] = $replaced . ' ' . $lineEndsWith . EOL;
                echo sprintf("Changed line {$this->wrapGreen('%s')} of {$this->wrapGreen('%s')} to:", $lineNo, $filename) . EOL;
                echo $this->wrapCyan($replaced) . EOLx2;
            }
        }
        file_put_contents($filename, implode('', $txt));


    }

    /**
     * @param string $filename
     * @param string $sprintfStr
     * @param string $lineMatches
     * @param mixed  ...$params
     *
     * @throws Exception
     * @todo remove noinspection
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function searchAndUpdateExact(string $filename, string $sprintfStr, string $lineMatches, ...$params)
    {
        echo PHP_EOL;
        echo 'repl: ' . $sprintfStr . EOL;

        print_r([
            'repl:' => $sprintfStr,
            'matc:' => $lineMatches,
            'parm:' => $params,
        ]);

//        $replaced = '';
        if (count($params) == 1) {
            $replaced = sprintf($sprintfStr, $params[0]);
        } else if (count($params) == 2) {
            $replaced = sprintf($sprintfStr, $params[0], $params[1]);
        } else if (count($params) == 3) {
            $replaced = sprintf($sprintfStr, $params[0], $params[1], $params[2]);
        } else {
            throw new Exception(
                sprintf('Method searchAndUpdateExac requires max 3 additional parameters %s given', count($params)),
                1597754663
            );
        }

        $txt = file($filename);

        foreach ($txt as $lineNo => $line) {

            if (SemanticVersionUtility::endsWith($line, $lineMatches)) {
                $txt[$lineNo] = $replaced . ' ' . $lineMatches . EOL;
                echo "Line no. {$this->wrapGreen($lineNo)} of {$this->wrapGreen($filename)} file to:" . EOL;
                echo $this->wrapCyan($replaced) . EOLx2;
            }
        }
        file_put_contents($filename, implode('', $txt));


    }

    private function calculateRise($currentVersion, $part, $state, $returnNewVersion = false)
    {
//        var_dump($currentVersion);
        $vp = explode('.', $currentVersion);


        if (count($vp) != 3 || !in_array($state, ['alpha', 'beta', 'stable'])) {
            throw new Exception('Invalid format for version, wrong  state, aborting', 1597705999);
        }
        $vp[0] = intval($vp[0]);
        $vp[1] = intval($vp[1]);
        $vp[2] = intval($vp[2]);
        switch ($part) {
            case 'patch':
                $vp[2]++;
                break;
            case 'minor':
                $vp[1]++;
                $vp[2] = 0;
                break;
            case 'major':
                $vp[0]++;
                $vp[1] = 0;
                $vp[2] = 0;
                break;
        }
        $newVersion = implode('.', $vp);


        if ($returnNewVersion) {
            return $newVersion;
        }
        $newState = ($state == 'stable') ? '' : '-' . $state;

        return [
            sprintf("Generate new {$this->wrapCodeSample('%s%s')} with ", $newVersion, $newState),
            sprintf("{$this->wrapCodeSample("{$this->selfName} update %s")}", $state),
        ];
    }


    /**
     * Methods for fetching current version data starts here
     *
     * DO NOT move this method in the structure without reason.
     *
     * @return string Formatted filename:number + phpdoc (if any)
     * @throws ReflectionException
     * @internal This method returns the filename and line number where group of specific methods starts
     */
    private function ____methodsForFetchingCurrentVersion(): string
    {
        return self::describeMethodItself();
    }

    /**
     * @param bool $shyData
     *
     * @return array
     */
    protected function fetchCurrentVersionFromFile($shyData = false): array
    {
        if (!file_exists($this->currentVersionFile)) {
            if ($shyData) die('No data about current version or data are invalid');
            die(sprintf("
{$this->wrapRed('Opsss...')}

There is no `%s` file, please create it with {$this->wrapCodeSample("{$this->selfName} --init")} command

", $this->currentVersionFile));
        }
        $currentData = json_decode(file_get_contents($this->currentVersionFile), true);
        if (is_null($currentData)) {
            if ($shyData) die('No data about current version or data are invalid');
            die(sprintf("
{$this->wrapRed("Invalid data in `%s` file.")}

Please fix it or remove the file and initialize your version again with: {$this->wrapGreen("{$this->selfName} --init")}

", $this->currentVersionFile));
        }
        return $currentData;
    }

    protected function fetchCurrentVersionToString()
    {
        $cv = $this->fetchCurrentVersionFromFile();
        return $cv['version'] . ($cv['state'] == 'stable' ? '' : '-' . $cv['state']);
    }

    protected function fetchCurrentVersion()
    {
        $cv = $this->fetchCurrentVersionFromFile();
        return trim($cv['version']);
    }

    protected function fetchCurrentLastUpdated()
    {
        $cv = $this->fetchCurrentVersionFromFile();
        return $cv['last_update'];
    }

    protected function fetchCurrentProjectName()
    {
        $cv = $this->fetchCurrentVersionFromFile();
        return $cv['project_name'];
    }

    protected function fetchCurrentState()
    {
        $cv = $this->fetchCurrentVersionFromFile();
        return $cv['state'];
    }

    /**
     * Methods for ANSI inline coloring starts here
     *
     * DO NOT move this method in the structure without reason.
     *
     * @return string Formatted filename:number + phpdoc (if any)
     * @throws ReflectionException
     * @internal This method returns the filename and line number where group of specific methods starts
     */
    private function ____ansiMethods(): string
    {
        return self::describeMethodItself();
    }


    public function ansiEscape()
    {
        return "\e[0m";
    }

    public function wrapBlack($value, bool $orTick = false, int ...$effects)
    {
        return $this->wrapColor($value, $orTick, "30m", ...$effects);
    }

    public function wrapRed($value, bool $orTick = false, int ...$effects)
    {
        return $this->wrapColor($value, $orTick, "31m", ...$effects);
    }


    public function wrapGreen($value, bool $orTick = false, int ...$effects)
    {
        return $this->wrapColor($value, $orTick, "32m", ...$effects);
    }

    public function wrapYellow($value, bool $orTick = false, int ...$effects)
    {
        return $this->wrapColor($value, $orTick, "33m", ...$effects);
    }

    public function wrapBlue($value, bool $orTick = false, int ...$effects)
    {
        return $this->wrapColor($value, $orTick, "34m", ...$effects);
    }

    public function wrapMagenta($value, bool $orTick = false, int ...$effects)
    {
        return $this->wrapColor($value, $orTick, "35m", ...$effects);
    }

    public function wrapCyan($value, bool $orTick = false, int ...$effects)
    {
        return $this->wrapColor($value, $orTick, "36m", ...$effects);
    }

    public function wrapWhite($value, bool $orTick = false, int ...$effects)
    {
        return $this->wrapColor($value, $orTick, "37m", ...$effects);
    }

    public function wrapCodeSample($value, bool $orTick = false, int ...$effects)
    {
        return $this->wrapCyan($value, $orTick, ...$effects);
    }

    protected function wrapColor($value, $orTick = false, $color = null, int ...$effects)
    {
        if (is_null($value) && !$orTick) {
            return $color;
        }

        if ($this->isColorsEnabled && !is_null($color)) {
            if ($this->isMarkdownOutput) {
                return ($orTick)
                    ? sprintf("`%s`", $value)
                    : $value . '';
            } else {
                $effects = count($effects) == 0
                    ? ''
                    : implode(';', $effects) . ';';


                $value = "\e[" . $effects . $color . $value . "\e[0m";
            }
        }

        return $value;
    }

    /**
     * Additional methods for different taska starts here
     *
     * DO NOT move this method in the structure without reason.
     *
     * @return string Formatted filename:number + phpdoc (if any)
     * @throws ReflectionException
     * @internal This method returns the filename and line number where group of specific methods starts
     */
    private function ____additionalOfDifferentPurposes(): string
    {
        return self::describeMethodItself();
    }

    /**
     * @return string
     */
    protected function checkCurrentEnv(): string
    {
        return (php_sapi_name() == 'cli') ? 'cli' : 'web';
    }

    private function putNormalizedCurrentFile(array $vers)
    {
        file_put_contents($this->currentVersionFile, json_encode($vers, JSON_PRETTY_PRINT));
    }

    private function getOptValue(array $options, string $short = null, string $long = null)
    {
        if (!is_null($short) && array_key_exists($short, $options)) {
            return $options[$short];
        } else if (!is_null($long) && array_key_exists($long, $options)) {
            return $options[$long];
        } else {
            return null;
        }
    }

    /**
     * {@see getopt()} for short, long and mixed options, see phpdoc for params
     *
     * ```php
     * $parameters = [
     *    'short' => [
     *      'a',
     *      'b:',
     *      'c::'
     *    ],
     *    'mixed' => [
     *      'h'   => 'help',
     *      'i:'  => 'india:',
     *      'j::' => 'julia::',
     *    ],
     *    'long' => [
     *      'xrey',
     *      'yankee:',
     *      'zulu::',
     *    ]
     * ];
     *
     * $options = getOptions($parameters);
     *
     * // debug:
     *
     * print_r(getOptions($parameters, true));
     * ```
     *
     * @param array $parameters An associative array with optional keys `short`, `mixed`, `long`, see above sample array
     * @param bool  $returnParams If true array of params will be returned without executing {@link getopt()}, just for debug
     *
     *
     *
     * @return false|false[]|string[]
     */
    function getOptions(array $parameters, $returnParams = false)
    {

        $shortOptionsStr = '';
        $longOptionsArr = [];
        $allParams = [];
        if (array_key_exists('short', $parameters)) {
            $shortOptionsStr .= implode('', $parameters['short']);
            $allParams = $parameters['short'];
        }
        if (array_key_exists('mixed', $parameters)) {
            $arrayValues = array_values($parameters['mixed']);
            $arrayKeys = array_keys($parameters['mixed']);
            $shortOptionsStr .= implode('', $arrayKeys);
            $longOptionsArr = $arrayValues;
            $allParams = array_merge($allParams, $arrayKeys, $arrayValues);
        }
        if (array_key_exists('long', $parameters)) {
            $longOptionsArr = array_merge($longOptionsArr, array_values($parameters['long']));
            $allParams = array_merge($allParams, array_values($parameters['long']));
        }
        if ($returnParams) {
            return $allParams;
        }

        return getopt($shortOptionsStr, $longOptionsArr);
    }


    /**
     * Finds calling method by reflection and describes the file, line number and phpdoc if any.
     *
     * @param string $methodName Method which should be described, if null backtrace is used to find calling methodname
     *
     * @return string
     * @throws ReflectionException
     * @internal Used for debug only
     */
    private function describeMethodItself($methodName = null)
    {
        if (is_null($methodName)) {
            $methodName = debug_backtrace()[1]['function'];
        }
        $method = new ReflectionMethod(SemanticVersion::class, $methodName);
        $file = $method->getFileName();
        $line = $method->getStartLine();
        $phpdoc = $method->getDocComment();
        $phpdoc = ($phpdoc)
            ? '    ' . $phpdoc
            : 'Missing!';
        $displayName = str_replace('____', '', $methodName);


        return sprintf("`%s` starts at `%s:%d`\n\nphpdoc:\n\n%s", $displayName, $file, $line, $phpdoc);
    }

    /**
     * It's used internally only to show how colors may be colored with ANSI in the code
     *
     *
     * @param bool $isColorEnabled temporary override {@see SemanticVersion::$isColorsEnabled} property
     * @param bool $isMarkdownOutput temporary override {@see SemanticVersion::$isMarkdownOutput} property
     * @param bool $selfDebug
     *
     * @return string
     * @internal
     */
    private function testFormats(bool $isColorEnabled, bool $isMarkdownOutput, $selfDebug = false)
    {

        $out = $this->ansiEscape();

        // Store initial values
        $initialColors = $this->isColorsEnabled;
        $initialMarkdown = $this->isMarkdownOutput;

        // temporary change for test
        $this->isColorsEnabled = $isColorEnabled;
        $this->isMarkdownOutput = $isMarkdownOutput;

        if ($selfDebug) {
            print_r(
                [
                    'What'                    => "properties",
                    '$this->isColorsEnabled'  => $this->isColorsEnabled,
                    '$this->isMarkdownOutput' => $this->isMarkdownOutput,
                ]
            );
        }

        // tests

        // sample 1
        $out .= '1: ' . $this->wrapRed('This is some sample', true) . EOL;

        // sample 2
        $out .= "2: This is sample of {$this->wrapRed('inline', true)} usage with ticks " . EOL;

        // sample 3
        $out .= "3: This is sample of {$this->wrapRed('inline')} usage without ticks " . EOL;

        // sample 4
        $out .= sprintf("4: This is sample of %s usage with ticks and sprintf", $this->wrapRed('inline', true)) . EOL;

        // sample 5
        $out .= sprintf("5: Please replace {$this->wrapRed('%s')} to {$this->wrapRed('%s')}", 'foo', 'bar') . EOL;

        // sample 5
        $out .= sprintf(
                "6: Or better replace %s to %s",
                $this->wrapRed('foo', true),
                $this->wrapRed('bar', true)
            ) . EOL;

        // restore initial values
        $this->isColorsEnabled = $initialColors;
        $this->isMarkdownOutput = $initialMarkdown;
        SemanticVersionUtility::stringLength('foo');
        $out .= "
Test all wrappers:
{$this->wrapBlack('Black')}
{$this->wrapRed('Red')}
{$this->wrapGreen('Green')}
{$this->wrapYellow('Yellow')}
{$this->wrapBlue('Blue')}
{$this->wrapMagenta('Magenta')}
{$this->wrapCyan('Cyan')}
{$this->wrapWhite('White')}

Test some formatting:

Sample variable is like foo wrapped with ANSI green: \"\\e[32mfoo\\e[0m\"
" . SemanticVersionUtility::stringLength("\e[32mfoo\e[0m") . "
" . SemanticVersionUtility::stringLength("\e[32mfoo\e[0m", true) . "

SemanticVersionUtility::fillToLeft('foo', 30):
" . SemanticVersionUtility::fillToLeft('foo', 30) . "

SemanticVersionUtility::fillToRight('bar', 30):
" . SemanticVersionUtility::fillToRight('bar', 30) . "

SemanticVersionUtility::prepend('baz', 30):
" . SemanticVersionUtility::prepend('baz', 30) . "



";


        return $out;
    }


}


/**
 * Class SemanticVersionUtility
 *
 * @author (c) 2020 Marcus Biesioroff <biesior@gmail.com>
 */
class SemanticVersionUtility
{

    /**
     * Returns string's length
     *
     * @param string $variable
     * @param bool   $removeAnsi
     *
     * @return bool|false|int
     */
    public static function stringLength($variable, $removeAnsi = true)
    {
        if ($removeAnsi) {
            $variable = self::removeAnsi($variable);
        }
        return mb_strlen($variable);
    }


    /** @noinspection PhpUnused */
    public static function startsWith($haystack, $needle)
    {
        $length = self::stringLength($needle, true);
        return substr($haystack, 0, $length) === $needle;
    }

    public static function endsWith($haystack, $needle)
    {
        $length = self::stringLength($needle, true);
        if (!$length) {
            return true;
        }

        return substr($haystack, -$length) === $needle;
    }

    /**
     * Text is aligned to left with added spaces to satisfy minimum length
     *
     * @param string  $value
     * @param integer $minLen
     * @param string  $withChar
     *
     * @return string
     */
    public static function fillToLeft($value, $minLen, $withChar = ' '): string
    {
        $len = self::stringLength($value);
        if ($len < $minLen) {
            $diff = $minLen - $len;
            return ($value . str_repeat($withChar, $diff));
        } else {
            return $value;
        }
    }

    /**
     * Text is aligned to right with added spaces to satisfy minimum length
     *
     * @param string  $value
     * @param integer $minLen
     * @param string  $withChar
     *
     * @return string
     */
    public static function fillToRight($value, int $minLen, string $withChar = ' '): string
    {
        $len = self::stringLength($value);
        if ($len < $minLen) {
            $diff = $minLen - $len;
            return (str_repeat($withChar, $diff) . $value);
        } else {
            return $value;
        }
    }

    public static function prepend($value, $minLen, $withChar = ' '): string
    {
        return str_repeat($withChar, $minLen) . $value;
    }

    /** @noinspection PhpUnused */
    public static function removeColon(string $value): string
    {
        return str_replace(':', '', $value);
    }

    public static function removeAnsi($value)
    {
        return preg_replace('#\\e[[][^A-Za-z]*[A-Za-z]#', '', $value);
    }
}

/**
 * Class SemanticVersionHelp
 *
 * @author (c) 2020 Marcus Biesioroff <biesior@gmail.com>
 */
class SemanticVersionHelp
{
    protected $registeredParams = [];
    protected $registeredCommands = [];
    protected $displayedLines = [];
    protected $longestParam = 0;
    protected $leftPaneSize = 0;

    /**
     * Renders help to console output or as a Markdown depending on arguments
     *
     * @param bool $markdownOutput
     * @param bool $isColorEnabled
     * @param bool $return If true returns rendered help instead displaying it
     *
     * @return string
     */
    public function render(bool $markdownOutput = false, bool $isColorEnabled = false, $return = false)
    {


        $output = '';
        foreach ($this->displayedLines as $line) {
            if ($line['kind'] == 'param') {
                $param = $line['param'];
                if ($line['is_first']) {
                    if ($markdownOutput) {
                        $output .= '```' . EOL;
                    }
                }
                if (!$markdownOutput && $isColorEnabled) {
                    $param = "\e[0;34m{$param}\e[0m";
                }
                $output .= SemanticVersionUtility::fillToLeft($param, $this->leftPaneSize);
                $i = 0;

                foreach ($line['data'] as $hint) {
                    if ($i == 0) {
                        $output .= $hint . EOL;
                    } else {
                        $output .= SemanticVersionUtility::prepend($hint, $this->leftPaneSize) . EOL;
                    }
                    $i++;
                }

                if ($line['is_last']) {
                    if ($markdownOutput) {
                        $output .= '```' . EOL;
                    }
                } else {
                    $output .= EOL; // end-line after each param
                }

            } else if ($line['kind'] == 'header') {

                $i = 0;

                $headerType = $line['param'];
                foreach ($line['data'] as $hint) {
                    if ($i == 0) {
                        $hashes = '';
                        if ($markdownOutput) {
                            if (intval($headerType) > 0) {
                                $hashes = str_repeat('#', $headerType) . ' ';
                            }
                            $hint = $hashes . $hint . '  ';
                        } else {
                            if ($isColorEnabled) {
                                if (intval($headerType) <= 3) {
                                    $hint = "\e[1;4;32m{$hint}\e[0m";
                                } else if (intval($headerType) == 4) {
                                    $hint = "\e[0;36m{$hint}\e[0m";
                                } else {
                                    $hint = "\e[2;32m{$hint}\e[0m";
                                }
                            }
                        }
                        $output .= EOL . $hint . EOL;
                    } else {
                        if ($markdownOutput) {
                            $hint = $hint . '  ';
                        }
                        $output .= $hint . EOL;
                    }
                    $i++;
                }
                $output .= EOL;
            }
        }

        if (!$return) {
            echo $output;
        }
        return $output;


    }


    /**
     * Only parameter in given group is marked as first and last at once.
     *
     * Determining if element is first or last is used for help rendering (also for Markdown)
     *
     * @param       $name
     * @param array $hint
     *
     * @return SemanticVersionHelp
     * @throws Exception
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    public function addCommand($name, array $hint): SemanticVersionHelp
    {
        $this->registerCommand($name);
        return $this;
//        return $this->addNextParam($short, $long, $hint, true, true);
    }


    public function addParams(SemanticVersionHelpParam ...$params)
    {
        $i = 0;
        $paramsCount = count($params);
        foreach ($params as $param) {

            $this->addNextParam(
                $param->short,
                $param->long,
                $param->hint = (!is_array($param->hint)) ? explode(chr(10), $param->hint) : $param->hint,
                $i == 0,
                $i == $paramsCount - 1

            );
            $i++;
        }
        return $this;
    }

    /**
     * Each next parameter in given group is NOT marked as first neither last.
     *
     * Determining if element is first or last is used for help rendering (also for Markdown)
     *
     * @param       $short
     * @param       $long
     * @param array $hint
     * @param bool  $firstInGroup
     * @param bool  $lastInGroup
     *
     * @return SemanticVersionHelp
     * @throws Exception
     */
    public function addNextParam($short, $long, $hint, $firstInGroup = false, $lastInGroup = false): SemanticVersionHelp
    {
        $parts = [];
        if (!is_null($short)) {
            $this->registerParam($short);
            $parts[] = '-' . self::removeColon($short);
        }
        if (!is_null($long)) {
            $this->registerParam($long);
            $parts[] = '--' . self::removeColon($long);
        }

        $uniqueKey = implode(', ', $parts);
        $this->addDisplayLine('param', $uniqueKey, $hint, $firstInGroup, $lastInGroup);

        $stringLength = SemanticVersionUtility::stringLength($uniqueKey);
        if ($stringLength > $this->longestParam) {
            $this->longestParam = $stringLength;
            $this->leftPaneSize = $stringLength + 2;
        }


        return $this;
    }

    public function addHeader($header, int $level = 3): SemanticVersionHelp
    {
        if (!is_array($header)) {
            $header = explode(chr(10), $header);
        }
        $this->addDisplayLine('header', $level, $header);
        return $this;
    }


    public static function removeColon(string $value): string
    {
        return str_replace(':', '', $value);
    }

    protected function registerParam($name)
    {
        if (in_array($name, $this->registeredParams)) {
            $code = 1598060717;
            throw new Exception(sprintf('The param with name `%s` is already registered! Code: %s', $name, $code), $code);
        } else {
            $this->registeredParams[] = $name;
        }
    }

    protected function registerCommand($name)
    {
        if (in_array($name, $this->registeredCommands)) {
            $code = time();
            throw new Exception(sprintf('The command with name `%s` is already registered! Code: %s', $name, $code), $code);
        } else {
            $this->registeredParams[] = $name;
        }
    }

    protected function addDisplayLine($kind, $param, $data, $isFirst = false, $isLast = false)
    {
        $this->displayedLines[] = ['kind' => $kind, 'param' => $param, 'data' => $data, 'is_first' => $isFirst, 'is_last' => $isLast];
    }
}

/**
 * Class SemanticVersionHelpParam
 *
 * @author (c) 2020 Marcus Biesioroff <biesior@gmail.com>
 */
class SemanticVersionHelpParam
{

    public $short;
    public $long;
    public $hint;

    /**
     * SemanticVersionHelpParam constructor.
     *
     * @param $short
     * @param $long
     * @param $hint
     */
    public function __construct($short, $long, $hint)
    {
        $this->short = $short;
        $this->long = $long;
        $this->hint = $hint;
    }


}


$semanticVersionObject = new SemanticVersion();
