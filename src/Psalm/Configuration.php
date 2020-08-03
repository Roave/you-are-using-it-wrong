<?php

declare(strict_types=1);

namespace Roave\YouAreUsingItWrong\Psalm;

use Psalm\Config as PsalmConfig;
use Psalm\Config\ProjectFileFilter;
use Psalm\Internal\IncludeCollector;
use Psalm\Issue\ArgumentIssue;
use Psalm\Issue\ClassIssue;
use Psalm\Issue\CodeIssue;
use Psalm\Issue\FunctionIssue;
use Psalm\Issue\MethodIssue;
use Psalm\Issue\PropertyIssue;

use function stripos;

/** @internal this class is only for configuring psalm according to the defaults of this repository */
final class Configuration extends PsalmConfig
{
    /** @var string[] */
    private array $checkedNamespaces;

    private function __construct(ProjectFileFilter $files, string ...$checkedNamespaces)
    {
        parent::__construct();

        $this->project_files           = $files;
        $this->allow_phpstorm_generics = true;
        $this->use_docblock_types      = true;
        $this->checkedNamespaces       = $checkedNamespaces;

        $this->setIncludeCollector(new IncludeCollector());
    }

    public static function forStrictlyCheckedNamespacesAndProjectFiles(
        ProjectFileFilter $projectFileFilter,
        string ...$namespaces
    ): self {
        return new self($projectFileFilter, ...$namespaces);
    }

    /** {@inheritDoc} */
    public function getReportingLevelForIssue(CodeIssue $e): string
    {
        if (
            ($e instanceof ClassIssue && $this->identifierMatchesNamespace($e->fq_classlike_name))
            || ($e instanceof PropertyIssue && $this->identifierMatchesNamespace($e->property_id))
            || ($e instanceof MethodIssue && $this->identifierMatchesNamespace($e->method_id))
            || (
                ($e instanceof FunctionIssue || $e instanceof ArgumentIssue)
                && $this->identifierMatchesNamespace((string) $e->function_id)
            )
        ) {
            return parent::getReportingLevelForIssue($e);
        }

        return self::REPORT_SUPPRESS;
    }

    private function identifierMatchesNamespace(string $identifier): bool
    {
        foreach ($this->checkedNamespaces as $namespace) {
            if (stripos($identifier, $namespace) === 0) {
                return true;
            }
        }

        return false;
    }
}
