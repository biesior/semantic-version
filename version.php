<?php
declare(strict_types=1);

const EOL = PHP_EOL;
const EOLx2 = EOL . EOL;
const EOLx3 = EOL . EOL . EOL;

/**
 * Class SemanticVersion
 *
 * @author (c) 2020 Marcus Biesioroff <biesior@gmail.com>
 */
class SemanticVersion
{

    private $currentVersionFile = 'current_version.json';

    /**
     * Name of this script to reference ie in help, is set in constructor
     *
     * @var string
     * see __construct()
     */
    private $selfName = '';
    // TODO to handle with init, set update methods and keep it in current_version.json
    private $repository = 'https://github.com/biesior/version-updater/tree/';
    private $title = 'Version updater for project' . EOLx2;
    // TODO to handle with init, set update methods and keep it in current_version.json
    private $isVersionDisplayOnWebAllowed = false;

    private $isDebugEnabled = false;
    private $isColorsEnabled = true;
    private $isMarkdownOutput = false;


    /**
     * SemanticVersion constructor.
     */
    public function __construct()
    {

        $env = $this->checkCurrentEnv();
        if ($env == 'web' && count($_GET) == 0 && count($_POST) == 0) {
            if (!$this->isVersionDisplayOnWebAllowed) {
                die('These data are protected. Bye!');
            }
            $currentVersionDisplayed = 'No current version';
            $currentVersion = $this->fetchCurrentVersionFromFile(true);
            if (is_array($currentVersion)) {
                $currentVersion = json_encode($currentVersion, JSON_PRETTY_PRINT);
            }
            header('Content-Type: application/json');
            die($currentVersion);
        } elseif ($env != 'cli') {
            die(PHP_EOL . 'This script can be only executed in the CLI, bye!' . EOLx2);
        };


        $this->selfName = basename(__FILE__);
        $parameters = array(
            'c' => 'clean',
            'x' => 'extract-colors',
            'debug',
            'markdown'
        );
        $parameters = [

            'mixed' => [
                'c' => 'clean',
                'x' => 'extract-colors',
            ],
            'long'  => [
                'debug',
                'markdown'
            ]
        ];

        $options = $this->getOptions($parameters);

//        var_dump($options);
        if (array_key_exists('debug', $options)) {
            $this->isDebugEnabled = true;
        }
        if (array_key_exists('markdown', $options)) {
            $this->isColorsEnabled = false;
            $this->isMarkdownOutput = true;
        }
        if (array_key_exists('x', $options)) {
            $this->isColorsEnabled = false;
        }

        if ($this->isDebugEnabled) {
            echo EOLx2;
            echo $this->____cliActions() . EOLx2;
            echo $this->____helperMethods() . EOLx2;
            echo $this->____methodsForFetchingCurrentVersion() . EOLx2;
            echo $this->____additionalOfDifferentPurposes() . EOLx2;
            echo EOLx2;
        }

        if (isset($options['c']) || isset($options['clean'])) {
            system('clear;');
            echo ("\e[2;36m^--- For previous output scroll up {$this->escape()}") . EOLx2;
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
                'v:' => 'version:',
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

        $help = $this->getOptValue($options, 'h', 'help');
        $init = $this->getOptValue($options, 'i', 'init');
        $mode = $this->getOptValue($options, 'm', 'mode');
        $newVersion = $this->getOptValue($options, 'n', 'new-version');
        $state = $this->getOptValue($options, 's', 'state');
        $part = $this->getOptValue($options, 'p', 'part');
        $tree = $this->getOptValue($options, 't', 'tree');


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
            ]);
            echo EOL . 'Debug options' . EOL;
            print_r($options);
        }

        if (!is_null($help)) {
            $this->showHelp();
        } elseif (!is_null($init)) {
            $this->init($init, $tree);
        } elseif (!is_null($kill)) {
            $this->kill($kill);
        } elseif (!is_null($releasePatch)) {
            $this->update('patch', null);
        } elseif (!is_null($releaseMinor)) {
            $this->update('minor', null);
        } elseif (!is_null($releaseMajor)) {
            $this->update('major', null);
        } elseif (!is_null($mode)) {
            if ($mode == 'set') {
                if (is_null($newVersion) || is_null($state)) {
                    die("{$this->red()}If `mode` is `set`, params `-n` or `--new-version` and `-s` or `--state` are required. Bye!{$this->escape()}" . EOLx2);
                }
                $version = $options['n'];
                $state = $options['s'];
                $this->set($version, $state);
            } elseif ($mode = 'update') {
                if (is_null($part)) {
                    die("{$this->red()}If `mode` is `update`, param`-p` or `--part` is required. Bye!{$this->escape()}" . EOLx2);
                }
                echo 'Make update';
            } else {
                die(sprintf("{$this->red()}Mode %s is not supported, check the help with -h parameter. Bye!{$this->escape()}", $options['m']) . EOLx2);
            }
        } else {
            $out = EOL . $this->title . "{$this->red()}Invalid parameters, check the help with -h parameter, like:\n\n{$this->green()}php {$this->selfName} -h{$this->escape()} \n\nBye!{$this->escape()}" . EOLx2;
            if (!$this->isColorsEnabled) $out = SemanticVersionUtility::removeAnsi($out);
            die($out);
        }

    }

    /**
     * Generates and displays the help
     *
     * @throws Exception
     */
    private function showHelp()
    {

        $now = new DateTime('now');
        $lastUpdate = $now->format('Y-m-d H:i:s');
        $help = new SemanticVersionHelp();

        $help->addHeader([
            'Welcome to semantic-version!',
            'Author (c) 2020 Marcus Biesioroff biesior@gmail.com',
            'Newest version can be found at https://github.com/biesior/semantic-version',
            sprintf("Last update for this help at `%s`", $lastUpdate),

        ], 1);

        $help->addHeader([
            "What it does?",
            'This CLI stript sets or updates version according to schema and updates required files.',
            "\n\nIt is still in development phase and lot of features should be implemented or improved.",
            "If you have any suggestion or problems visit {$this->green()}https://github.com/biesior/semantic-version/issues{$this->escape()}",
            "Please use it with care!\n\n",
            'For more details please refer to these resources:',
            '- https://semver.org/spec/v2.0.0.html',
            '- https://en.wikipedia.org/wiki/Software_release_life_cycle',
        ], 3);

        $help
            ->addHeader(['Help'])
            ->addHeader(['Parameters'], 4)
            ->addOnlyParam('h', 'help', ['Displaying this help']);


        $help
            ->addHeader(["Display options"], 3)
            ->addHeader([
                "Sample usages",
                "- {$this->green(true)}php {$this->selfName} -xc ...other params{$this->escape(true)} to display with clean output without colors",
                "- {$this->green(true)}php {$this->selfName} -hxc{$this->escape(true)} to displays monochromatic help with clean output.",
                "- {$this->green(true)}php {$this->selfName} -h --markdown > HELP.md{$this->escape(true)} to display this help as a markdown and ie save it to file.",
                "- etc"
            ], 4)
            ->addHeader(['Parameters'], 4)
            ->addFirstParam('c', 'clean', ['If set console will be cleaned for better output'])
            ->addNextParam('x', null, [
                    "Extract colors, i.e. if you want to write the output to file like ",
                    "{$this->green(true)}php {$this->selfName} -h > version-help-color.txt{$this->escape(true)}",
                    "{$this->green(true)}php {$this->selfName} -hx > version-help-mono.txt{$this->escape(true)}"
                ]
            )
            ->addNextParam(null, 'markdown', [
                'If set help will be generated in markdown format, i.e.',
                "{$this->green(true)}php {$this->selfName} -h --markdown > HELP.md{$this->escape(true)}",
            ])
            ->addLastParam(null, 'debug', [
                'If set some debug will occure, of course it\'s only for development stage',
            ]);


        $help
            ->addHeader(["Init, set, update or kill"])
            ->addHeader(['Parameters'], 4)
            ->addFirstParam('i:', 'init:', [
                    'Create new version by default it will be `0.0.1-alpha`',
                    '- you can change it immediately using -m `set` or `update`'
                ]
            )
            ->addNextParam(null, 'repository:', ["Repository URL ie {$this->green(true)}https://github.com/biesior/version-updater/{$this->escape(true)}"])
            ->addNextParam('m:', 'mode:', [
                    'Mode can be `set` or `update` ',
                    '- When mode is `set` params `-n` or `--new-version` and `-s` or `--state` are required ',
                    '- When mode is `update` param `-p, --part` is required'
                ]
            )
            ->addNextParam('n:', 'new-version', ['Version which should be set like 1.2.3'])
            ->addNextParam('s:', 'state:', ['State which should be set like alpha, beta , stable'])
            ->addNextParam('p:', 'part:', ['Part to update allowed `major`, `minor`, `patch`'])
            ->addNextParam('v', 'version', ['Displays current version of the project'])
            ->addLastParam(null, 'kill::', ["({$this->red()}destructive!{$this->escape()}) Deletes version file, you will need to start from beginning"])//            ->endGroup($group)
        ;


        $help
            ->addHeader([
                'Rise params',
                'You can just upgrade existing project with PATCH, MINOR or MAJOR version like'
            ])
            ->addHeader(['Parameters'], 4)
            ->addFirstParam(null, 'patch', ["Increases PATCH version i.e.: {$this->green(true)}0.1.0-alpha{$this->escape(true)} > {$this->green(true)}0.1.1-alpha{$this->escape(true)}"])
            ->addNextParam(null, 'minor', ["Increases MINOR version i.e.: {$this->green(true)}0.1.1-alpha{$this->escape(true)} > {$this->green(true)}0.2.0-alpha{$this->escape(true)}"])
            ->addNextParam(null, 'major', ["Increases MINOR version i.e.: {$this->green(true)}0.2.0-alpha{$this->escape(true)} > {$this->green(true)}1.0.0-alpha{$this->escape(true)}"])
            ->addLastParam(null, 'set:', [
                "Requires one or two following params version and state",
                "- first is version like {$this->green(true)}1.2.3{$this->escape(true)}",
                "- second is version like {$this->green(true)}alpha{$this->escape(true)}"
            ]);

        $help
            ->addHeader([
                'Commands',
                'Some functionalities are available by commands instead of parameters',
                'That means you should use them as',
                '',
                "{$this->green(true)}php {$this->selfName} <command>{$this->escape(true)}",
                '',
                'i.e. to rise the PATCH version just use that command:',
                '',
                "{$this->green(true)}php {$this->selfName} patch{$this->escape(true)}",
            ])
            ->addHeader(['Available commands'], 4)
            ->addCommand(null, 'patch', ['foo'])
            ->addCommand(null, 'patch', ['foo']);
//            ->addHeader(['Commands'], 4)
//            ->addFirstParam(null, 'patch', ["Increases PATCH version i.e.: {$this->green(true)}0.1.0-alpha{$this->escape(true)} > {$this->green(true)}0.1.1-alpha{$this->escape(true)}"])
//            ->addNextParam(null, 'minor', ["Increases MINOR version i.e.: {$this->green(true)}0.1.1-alpha{$this->escape(true)} > {$this->green(true)}0.2.0-alpha{$this->escape(true)}"])
//            ->addNextParam(null, 'major', ["Increases MINOR version i.e.: {$this->green(true)}0.2.0-alpha{$this->escape(true)} > {$this->green(true)}1.0.0-alpha{$this->escape(true)}"])
//            ->addLastParam(null, 'set:', [
//                "Requires one or two following params version and state",
//                "- first is version like {$this->green(true)}1.2.3{$this->escape(true)}",
//                "- second is version like {$this->green(true)}alpha{$this->escape(true)}"
//            ]);


        $help->render($this->isMarkdownOutput, $this->isColorsEnabled);

    }

    private function init($projectName, $tree = null)
    {


        if (file_exists($this->currentVersionFile)) {

            $projectName = $this->fetchCurrentProjectName();
            $currentVersion = $this->fetchCurrentVersionToString();
            $lastUpdated = $this->fetchCurrentLastUpdated();

            die (sprintf(
                    "File {$this->green()}%s{$this->escape()} already exists. 
            \nCurrent version of {$this->green()}%s{$this->escape()} project is {$this->green()}%s{$this->escape()} last updated at {$this->green()}%s{$this->escape()} 
            \nIf you want to re-initialize the project's version remove this file first.
            \nInstead maybe you want update current version with --mode `set` or `update`.\nCheck the help for more details with command: {$this->green()}php {$this->selfName} --help{$this->escape()}
             ",
                    $this->currentVersionFile,
                    $projectName,
                    $currentVersion,
                    $lastUpdated)
                . EOLx2);
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

        file_put_contents($this->currentVersionFile, json_encode($vers, JSON_PRETTY_PRINT));
        if (!file_exists('README.md')) {
            $fileTemplate = "## `{$projectName}` project

[![State](https://img.shields.io/static/v1?label=alpha&message=0.1.0&color=blue)](https://github.com/biesior/box-drawer/tree/1.0.0-beta  'Latest known version') <!-- __SEMANTIC_VERSION_LINE__ -->
![Updated](https://img.shields.io/static/v1?label=upated&message={$lastUpdateLink}&color=lightgray  'Latest known update date') <!-- __SEMANTIC_UPDATED_LINE__ -->";

            file_put_contents('README.md', $fileTemplate);

        }
        echo "Versioning for project {$this->green()}{$projectName}{$this->escape()} was initialized with version {$this->green()}0.1.0-alpha{$this->escape()}!" . EOLx2;
    }

    private function kill($force = false)
    {
        $out = '';

        if (file_exists($this->currentVersionFile)) {
            $projectName = $this->fetchCurrentProjectName();
            $currentVersionLong = $this->fetchCurrentVersionToString();
        } else {
            $out = "File {$this->green()}{$this->currentVersionFile}{$this->escape()} doesn't exist, nothing to kill.\n\nBye!" . EOLx2;
            if (!$this->isColorsEnabled) {
                $out = SemanticVersionUtility::removeAnsi($out);
            }
            die($out);
        }
        $renameTo = 'zzz_unused_' . time() . '_' . $this->currentVersionFile;
        if (!in_array($force, ['soft', 'hard'])) {
            $out .= 'You are trying to remove ' . $this->currentVersionFile . ' from your project and disable this functionality in it' . EOLx2;
            $out .= "Of course it's your choice and if you are sure repeat this command with {$this->green()}force{$this->escape()} value, like" . EOLx2;
            $out .= "{$this->green()}php {$this->selfName} --kill=soft{$this->escape()} \n\n    to rename `{$this->currentVersionFile}` to `{$renameTo}` or \n\n{$this->green()}php {$this->selfName} --kill=hard{$this->escape()} \n\n    to remove it totally";
            if (!$this->isColorsEnabled) {
                $out = SemanticVersionUtility::removeAnsi($out);
            }
            die($out . EOLx2);
        }


        if (file_exists($this->currentVersionFile)) {
            $projectName = $this->fetchCurrentProjectName();
            $currentVersionLong = $this->fetchCurrentVersionToString();
            $currentVersionShort = $this->fetchCurrentVersion();
            $currentState = $this->fetchCurrentState();
        }

        $out .= "Versioning for project {$this->green()}{$projectName}{$this->escape()} was killed, last known version was {$this->green()}{$currentVersionLong}{$this->escape()}" . EOLx2;

        if ($force == 'soft') {
            rename($this->currentVersionFile, $renameTo);
            $out .= "File {$this->green()}{$this->currentVersionFile}{$this->escape()} was renamed to {$this->green()}{$renameTo}{$this->escape()}";
        } elseif ($force == 'hard') {
            unlink($this->currentVersionFile);
            $out .= "File {$this->green()}{$this->currentVersionFile}{$this->escape()} was {$this->red()}deleted{$this->escape()}";
        }
        $out .= EOLx2;


        $a = 'Some';
        $b = 'other';
        $c = " $b else";


        $out .= "The functionality is disabled now." . EOL;
        if ($force == 'soft') {
            $out .= "\nor just by manually restore it by renaming the backup file to {$this->green()}{$this->currentVersionFile}{$this->escape()}." . EOL;
            $out .= "\nor just by manually restore it by renaming the backup file to {$this->green()}{$this->currentVersionFile}{$this->escape()}." . EOLx3;
        }
        $out .= "To recreate it with last known version, initialize it again and set last known version and state like:" . EOLx2;

        $out .= "{$this->green()}php {$this->selfName} -i=\"{$projectName}\"{$this->escape()}" . EOL;
        $out .= "{$this->green()}php {$this->selfName} -m set -n {$currentVersionShort} -s {$currentState}{$this->escape()}" . EOL;


        $out .= EOL . "Bye {$this->red()};({$this->escape()}" . EOLx2;


        if (!$this->isColorsEnabled) {
            $out = SemanticVersionUtility::removeAnsi($out);
        }
        echo $out;
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
        file_put_contents($this->currentVersionFile, json_encode($vers));

        $repository = $this->repository . $newVersionFull;

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


        // tagging before commit has no sense
//        $newTagName = $this->getCurrentVersionToString();
//        $tagCmd = "git tag {$newTagName}";
//        system($tagCmd);


        if (!$this->isColorsEnabled) {
            $output = SemanticVersionUtility::removeAnsi($output);
        }

        $output .= "Please push your update to GitHub and don't forget to publish new release!" . EOLx2;
        $output .= 'Bye!' . EOLx2;

        echo $output;
    }

    private function update($part, $toState = null)
    {

        if (is_null($toState)) {
            $currentState = $this->fetchCurrentState();
            if (!is_null($currentState)) {
                $toState = $currentState;
            } else {
                die ("{$this->red()}State couldn't be retrieved. Please fix your " . $this->currentVersionFile . " fole and retry.\n\nBye!{$this->escape()}" . EOLx2);
            }
        }

        $output = '';
        $currentData = $this->fetchCurrentVersionFromFile();
        $oldVersion = $currentData['version'];
        $oldState = $currentData['state'];
        $oldVersionFull = $oldVersion . ($oldState == 'stable' ? '' : '-' . $oldState);
        $newVersion = $this->calculateRise($oldVersion, $part, $toState, true);
        $newVersionFull = $newVersion . ($toState == 'stable' ? '' : '-' . $toState);
        $revertCommand = "php {$this->selfName} -m set -n {$oldVersion} -s {$oldState}";
        $this->set($newVersion, $toState);
        $output .= (PHP_EOL .
                sprintf(
                    "Your version was updated from {$this->green()}%s{$this->escape()} to {$this->green()}%s{$this->escape()}

To revert this change please run: {$this->green()}%s{$this->escape()} 
",
                    $oldVersionFull, $newVersionFull, $revertCommand
                )
            ) . PHP_EOL;;

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
        } elseif (count($params) == 3) {
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
                echo sprintf("Changed line {$this->green()}%s{$this->escape()} of {$this->green()}%s{$this->escape()} to:", $lineNo, $filename) . EOL;
                echo "{$this->ansiCyan()}" . $replaced . EOLx2 . "{$this->escape()}";
            };
        }
        file_put_contents($filename, implode('', $txt));


    }

    private function calculateRise($currentVersion, $part, $state, $returnNewVersion = false)
    {
//        var_dump($currentVersion);
        $vp = explode('.', $currentVersion);


        if (count($vp) != 3 || !in_array($state, ['alpha', 'beta', 'stable'])) {
            throw new Exception('Invalid format for version, wrong  state, aborting', 1597705999);
        };
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
            sprintf("Generate new {$this->ansiCyan()}%s%s{$this->escape()} with ", $newVersion, $newState),
            sprintf("{$this->ansiCyan()}php {$this->selfName} update %s{$this->escape()}", $state)
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
            die(sprintf("there is no `%s` file, please create it with {$this->ansiCyan()}php {$this->selfName} --init{$this->escape()} command", $this->currentVersionFile) . EOLx2);
        }
        $currentData = json_decode(file_get_contents($this->currentVersionFile), true);
        if (is_null($currentData)) {
            if ($shyData) die('No data about current version or data are invalid');
            die(sprintf("{$this->red()}Invalid data in `%s` file.{$this->escape()}\n\nPlease fix it or remove the file and initialize your version again with: {$this->green()}php {$this->selfName} --init{$this->escape()}", $this->currentVersionFile) . EOLx2);
        };
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

    public function red($orTick = false)
    {
        return $this->color($orTick, "\e[1;31m");
    }

    public function green($orTick = false)
    {
        return $this->color($orTick, "\e[1;32m");
    }


    function blue($orTick = false)
    {
        return $this->color($orTick, "\e[1;34m");
    }

    function ansiCyan($orTick = false)
    {
        return $this->color($orTick, "\e[1;36m");
    }

    function white($orTick = false)
    {
        return $this->color($orTick, "\e[1;37m");
    }

    function escape($orTick = false)
    {
        return $this->color($orTick, "\e[0m");
    }

    protected function color($orTick = false, $color = null)
    {

        if ($orTick && ($this->isMarkdownOutput || !$this->isColorsEnabled)) {
            return '`';
        } elseif (!$orTick && $this->isMarkdownOutput) {
            return '';
        } elseif ($this->isColorsEnabled) {
            return $color;
        }
        return '';
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

    private function getOptValue(array $options, string $short = null, string $long = null)
    {
        if (!is_null($short) && array_key_exists($short, $options)) {
            return $options[$short];
        } elseif (!is_null($long) && array_key_exists($long, $options)) {
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
        $shortStr = '';
        $longArr = [];
        $allParams = [];
        if (array_key_exists('short', $parameters)) {
            $shortStr .= implode('', $parameters['short']);
            $allParams = $parameters['short'];
        }
        if (array_key_exists('mixed', $parameters)) {
            $arrayValues = array_values($parameters['mixed']);
            $arrayKeys = array_keys($parameters['mixed']);
            $shortStr .= implode('', $arrayKeys);
            $longArr = $arrayValues;
            $allParams = array_merge($allParams, $arrayKeys, $arrayValues);
        }
        if (array_key_exists('long', $parameters)) {
            $longArr = array_merge($longArr, array_values($parameters['long']));
            $allParams = array_merge($allParams, array_values($parameters['long']));
        }
        if ($returnParams) {
            return $allParams;
        }

        return getopt($shortStr, $longArr);
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
        $method = new \ReflectionMethod(SemanticVersion::class, $methodName);
        $file = $method->getFileName();
        $line = $method->getStartLine();
        $phpdoc = $method->getDocComment();
        $phpdoc = ($phpdoc)
            ? '    ' . $phpdoc
            : 'Missing!';
        $displayName = str_replace('____', '', $methodName);


        return sprintf("`%s` starts at `%s:%d`\n\nphpdoc:\n\n%s", $displayName, $file, $line, $phpdoc);
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

    public static function linkOrHighlight($value, $forMd = false)
    {
        if ($forMd) {
            return "{$value}";
        } else {
//            return "{$this->ansiGreen()}{$value}{$this->ansiEnd()}";
//            return "{$this->ansiGreen()}{$value}{$this->ansiEnd()}";
        }
    }

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

        $ressubstr = substr($haystack, -$length);

        $res = $ressubstr === $needle;

        return $res;
    }

    /**
     *  TODO improve phpdoc
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

    public static function prepend($value, $minLen, $withChar = ' '): string
    {
        return str_repeat($withChar, $minLen) . $value;
    }

    /**
     * Fill to right
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
     */
    public function render(bool $markdownOutput = false, bool $isColorEnabled = false, $return = false)
    {

        $out = '';
        foreach ($this->displayedLines as $line) {
            if ($line['kind'] == 'param') {
                $param = $line['param'];
                if ($line['is_first']) {
                    if ($markdownOutput) {
                        $out .= '```' . EOL;
                    }
                }
                if (!$markdownOutput && $isColorEnabled) {
                    $param = "\e[0;34m{$param}\e[0m";
                }
                $out .= SemanticVersionUtility::fillToLeft($param, $this->leftPaneSize);
                $i = 0;

                foreach ($line['data'] as $hint) {
                    if ($i == 0) {
                        $out .= $hint . EOL;
                    } else {
                        $out .= SemanticVersionUtility::prepend($hint, $this->leftPaneSize) . EOL;
                    }
                    $i++;
                }

                if ($line['is_last']) {
                    if ($markdownOutput) {
                        $out .= '```' . EOL;
                    }
                } else {
                    $out .= EOL; // end-line after each param
                }

            } elseif ($line['kind'] == 'header') {

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
                        }
                        $out .= EOL . $hint . EOL;
                    } else {
                        if ($markdownOutput) {
                            $hint = $hint . '  ';
                        }

                        $out .= $hint . EOL;

                    }
                    $i++;
                }
                $out .= EOL;
            }
        }

        if ($return) {
            return $out;
        } else {
            echo $out;
        }

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
     */
    public function addCommand($name, array $hint): SemanticVersionHelp
    {
        $this->registerCommand($name);
        return $this;
//        return $this->addNextParam($short, $long, $hint, true, true);
    }

    /**
     * Only parameter in given group is marked as first and last at once.
     *
     * Determining if element is first or last is used for help rendering (also for Markdown)
     *
     * @param       $short
     * @param       $long
     * @param array $hint
     *
     * @return SemanticVersionHelp
     */
    public function addOnlyParam($short, $long, array $hint): SemanticVersionHelp
    {
        return $this->addNextParam($short, $long, $hint, true, true);
    }

    /**
     * First parameter in given group is marked as first.
     *
     * Determining if element is first or last is used for help rendering (also for Markdown)
     *
     * @param       $short
     * @param       $long
     * @param array $hint
     *
     * @return SemanticVersionHelp
     */
    public function addFirstParam($short, $long, array $hint): SemanticVersionHelp
    {
        return $this->addNextParam($short, $long, $hint, true, false);
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
    public function addNextParam($short, $long, array $hint, $firstInGroup = false, $lastInGroup = false): SemanticVersionHelp
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

    /**
     * First parameter in given group is marked as last.
     *
     * Determining if element is first or last is used for help rendering (also for Markdown)
     *
     * @param string|null $short
     * @param string|null $long
     * @param array       $hint
     *
     * @return SemanticVersionHelp
     */
    public function addLastParam($short, $long, array $hint)
    {
        return $this->addNextParam($short, $long, $hint, false, true);
    }


    public function addHeader(array $header, int $level = 3): SemanticVersionHelp
    {
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

$versionUpdater = new SemanticVersion();
