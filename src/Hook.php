<?php

declare(strict_types=1);

namespace Roave\YouAreUsingItWrong;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use PackageVersions\Versions;
use Psalm\Internal\Analyzer\ProjectAnalyzer;
use Psalm\Internal\Provider\FileProvider;
use Psalm\Internal\Provider\Providers;
use Psalm\IssueBuffer;
use Psalm\Report\ReportOptions;
use Roave\YouAreUsingItWrong\Composer\Project;
use Roave\YouAreUsingItWrong\Psalm\Configuration;
use Roave\YouAreUsingItWrong\Psalm\ProjectFilesToBeTypeChecked;
use RuntimeException;
use function define;
use function defined;
use function file_exists;
use function getcwd;
use function microtime;

/** @internal this is a composer plugin: do not rely on it in your sources */
final class Hook implements PluginInterface, EventSubscriberInterface
{
    private const THIS_PACKAGE_NAME = 'roave/you-are-using-it-wrong';

    /** {@inheritDoc} */
    public function activate(Composer $composer, IOInterface $io) : void
    {
        // Nothing to do here, as all features are provided through event listeners
    }

    /** {@inheritDoc} */
    public static function getSubscribedEvents() : array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'runTypeChecks',
            ScriptEvents::POST_UPDATE_CMD  => 'runTypeChecks',
        ];
    }

    /**
     * @throws RuntimeException
     */
    public static function runTypeChecks(Event $composerEvent) : void
    {
        $io = $composerEvent->getIO();

        if (! file_exists(__DIR__)) {
            $io->write('<info>' . self::THIS_PACKAGE_NAME . ':</info> Package not found (probably scheduled for removal) - skipping type checks...');

            return;
        }

        $composer = $composerEvent->getComposer();
        $project  = Project::fromComposerInstallationContext($composer->getPackage(), $composer->getLocker(), getcwd());

        if (! $project->strictTypeChecksAreEnforcedByLocalInstallation()) {
            $io->write('<info>' . self::THIS_PACKAGE_NAME . ':</info> plugin not installed locally - skipping type checks...');

            return;
        }

        if ($project->alreadyHasOwnPsalmConfiguration()) {
            $io->write('<info>' . self::THIS_PACKAGE_NAME . ':</info> psalm configuration detected - assuming static analysis will run later; not running psalm now');

            return;
        }

        $io->write('<info>' . self::THIS_PACKAGE_NAME . ':</info> checking strictly type-checked packages...');

        $project
            ->packagesRequiringStrictTypeChecks()
            ->printPackagesToBeCheckedToComposerIo($io);

        self::analyseProject($project);

        $io->write('<info>' . self::THIS_PACKAGE_NAME . ':</info> ... done checking strictly type-checked packages');
    }

    private static function analyseProject(Project $project) : void
    {
        if (! defined('PSALM_VERSION')) {
            define('PSALM_VERSION', Versions::getVersion('vimeo/psalm'));
        }

        if (! defined('PHP_PARSER_VERSION')) {
            define('PHP_PARSER_VERSION', Versions::getVersion('nikic/php-parser'));
        }

        // At this stage of the installation, project dependencies are not yet autoloadable
        require_once getcwd() . '/vendor/autoload.php';

        $startTime = microtime(true);
        // @TODO in project with psalm config, skip analysis: these people know what they are doing.

        $files           = ProjectFilesToBeTypeChecked::fromAutoloadDefinitions($project->rootPackageAutoload());
        $config          = Configuration::forStrictlyCheckedNamespacesAndProjectFiles(
            $files,
            ...$project
            ->packagesRequiringStrictTypeChecks()
            ->namespacesForWhichUsagesAreToBeTypeChecked()
        );
        $projectAnalyzer = new ProjectAnalyzer($config, new Providers(new FileProvider()), new ReportOptions());

        $config->visitComposerAutoloadFiles($projectAnalyzer);
        $projectAnalyzer->check(__DIR__, false);

        // NOTE: this calls exit(1) on failed checks - currently not a problem, but it may become one
        IssueBuffer::finish($projectAnalyzer, true, $startTime);
    }
}
