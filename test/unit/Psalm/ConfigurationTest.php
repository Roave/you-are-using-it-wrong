<?php

declare(strict_types=1);

namespace RoaveTest\YouAreUsingItWrong\Psalm;

use PHPUnit\Framework\TestCase;
use Psalm\Config;
use Psalm\Config\ProjectFileFilter;
use Psalm\Issue\ArgumentIssue;
use Psalm\Issue\ClassIssue;
use Psalm\Issue\CodeIssue;
use Psalm\Issue\FunctionIssue;
use Psalm\Issue\MethodIssue;
use Psalm\Issue\PropertyIssue;
use ReflectionProperty;
use Roave\YouAreUsingItWrong\Psalm\Configuration;

/** @covers \Roave\YouAreUsingItWrong\Psalm\Configuration */
final class ConfigurationTest extends TestCase
{
    public function testConfigurationDefaults(): void
    {
        $reflectionFiles            = new ReflectionProperty(Configuration::class, 'project_files');
        $reflectionUseDocblockTypes = new ReflectionProperty(Configuration::class, 'use_docblock_types');
        $reflectionInstance         = new ReflectionProperty(Config::class, 'instance');
        $reflectionIncludeCollector = new ReflectionProperty(Config::class, 'include_collector');
        $projectFiles               = ProjectFileFilter::loadFromArray([], __DIR__, true);
        $configuration              = Configuration::forStrictlyCheckedNamespacesAndProjectFiles($projectFiles);

        self::assertSame($projectFiles, $reflectionFiles->getValue($configuration));
        self::assertTrue($reflectionUseDocblockTypes->getValue($configuration));
        self::assertSame($configuration, $reflectionInstance->getValue($configuration));
        self::assertNotNull($reflectionIncludeCollector->getValue($configuration));
    }

    /** @dataProvider expectedReportingLevels */
    public function testCheckedNamespaces(
        CodeIssue $issue,
        string $expectedReportingLevel,
        string ...$checkedNamespaces,
    ): void {
        $projectFiles = ProjectFileFilter::loadFromArray([], __DIR__, true);

        self::assertSame(
            $expectedReportingLevel,
            Configuration::forStrictlyCheckedNamespacesAndProjectFiles($projectFiles, ...$checkedNamespaces)
                         ->getReportingLevelForIssue($issue),
        );
    }

    /**
     * @return array<
     *     string,
     *     array{0: CodeIssue, 1: string, 2?: non-empty-string, 3?: non-empty-string, 4?: non-empty-string}
     * >
     */
    public function expectedReportingLevels(): array
    {
        $classIssue             = $this->createMock(ClassIssue::class);
        $propertyIssue          = $this->createMock(PropertyIssue::class);
        $methodIssue            = $this->createMock(MethodIssue::class);
        $functionIssue          = $this->createMock(FunctionIssue::class);
        $argumentIssue          = $this->createMock(ArgumentIssue::class);
        $anonymousArgumentIssue = $this->createMock(ArgumentIssue::class);
        $genericIssue           = $this->createMock(CodeIssue::class);

        $classIssue->fq_classlike_name = 'Foo\\Bar\\Baz';
        $propertyIssue->property_id    = 'Foo\\Bar\\Baz$property';
        $methodIssue->method_id        = 'Foo\\Bar\\Baz::method';
        $functionIssue->function_id    = 'Foo\\Bar\\Baz\\function_name';
        $argumentIssue->function_id    = 'Foo\\Bar\\Baz\\function_name';

        return [
            'no namespaces, class issue'                           => [
                $classIssue,
                Configuration::REPORT_SUPPRESS,
            ],
            'non-matching namespaces, class issue'                 => [
                $classIssue,
                Configuration::REPORT_SUPPRESS,
                'AAA\\BBB',
                'AAA\\BBB\\',
                'Bar\\',
            ],
            'matching namespaces, class issue'                     => [
                $classIssue,
                Configuration::REPORT_ERROR,
                'AAA\\BBB',
                'AAA\\BBB\\',
                'Foo\\Bar\\',
            ],
            'case-insensitive matching namespaces, class issue'    => [
                $classIssue,
                Configuration::REPORT_ERROR,
                'AAA\\BBB',
                'AAA\\BBB\\',
                'foo\\',
            ],
            'no namespaces, property issue'                        => [
                $propertyIssue,
                Configuration::REPORT_SUPPRESS,
            ],
            'non-matching namespaces, property issue'              => [
                $propertyIssue,
                Configuration::REPORT_SUPPRESS,
                'AAA\\BBB',
                'AAA\\BBB\\',
                'Bar\\',
            ],
            'matching namespaces, property issue'                  => [
                $propertyIssue,
                Configuration::REPORT_ERROR,
                'AAA\\BBB',
                'AAA\\BBB\\',
                'Foo\\Bar\\',
            ],
            'case-insensitive matching namespaces, property issue' => [
                $propertyIssue,
                Configuration::REPORT_ERROR,
                'AAA\\BBB',
                'AAA\\BBB\\',
                'foo\\',
            ],
            'no namespaces, method issue'                          => [
                $methodIssue,
                Configuration::REPORT_SUPPRESS,
            ],
            'non-matching namespaces, method issue'                => [
                $methodIssue,
                Configuration::REPORT_SUPPRESS,
                'AAA\\BBB',
                'AAA\\BBB\\',
                'Bar\\',
            ],
            'matching namespaces, method issue'                    => [
                $methodIssue,
                Configuration::REPORT_ERROR,
                'AAA\\BBB',
                'AAA\\BBB\\',
                'Foo\\Bar\\',
            ],
            'case-insensitive matching namespaces, method issue'   => [
                $methodIssue,
                Configuration::REPORT_ERROR,
                'AAA\\BBB',
                'AAA\\BBB\\',
                'foo\\',
            ],
            'no namespaces, function issue'                        => [
                $functionIssue,
                Configuration::REPORT_SUPPRESS,
            ],
            'non-matching namespaces, function issue'              => [
                $functionIssue,
                Configuration::REPORT_SUPPRESS,
                'AAA\\BBB',
                'AAA\\BBB\\',
                'Bar\\',
            ],
            'matching namespaces, function issue'                  => [
                $functionIssue,
                Configuration::REPORT_ERROR,
                'AAA\\BBB',
                'AAA\\BBB\\',
                'Foo\\Bar\\',
            ],
            'case-insensitive matching namespaces, function issue' => [
                $functionIssue,
                Configuration::REPORT_ERROR,
                'AAA\\BBB',
                'AAA\\BBB\\',
                'foo\\',
            ],
            'no namespaces, argument issue'                        => [
                $argumentIssue,
                Configuration::REPORT_SUPPRESS,
            ],
            'non-matching namespaces, argument issue'              => [
                $argumentIssue,
                Configuration::REPORT_SUPPRESS,
                'AAA\\BBB',
                'AAA\\BBB\\',
                'Bar\\',
            ],
            'matching namespaces, argument issue'                  => [
                $argumentIssue,
                Configuration::REPORT_ERROR,
                'AAA\\BBB',
                'AAA\\BBB\\',
                'Foo\\Bar\\',
            ],
            'case-insensitive matching namespaces, argument issue' => [
                $argumentIssue,
                Configuration::REPORT_ERROR,
                'AAA\\BBB',
                'AAA\\BBB\\',
                'foo\\',
            ],
            'anonymous argument issue'                             => [
                $anonymousArgumentIssue,
                Configuration::REPORT_SUPPRESS,
                'AAA\\BBB',
                'AAA\\BBB\\',
                'foo\\',
            ],
            'generic issue type'                                   => [
                $genericIssue,
                Configuration::REPORT_SUPPRESS,
                'AAA\\BBB',
                'AAA\\BBB\\',
                'foo\\',
            ],
        ];
    }
}
