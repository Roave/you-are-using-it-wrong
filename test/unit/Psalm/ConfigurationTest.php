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
    public function testConfigurationDefaults() : void
    {
        $reflectionFiles            = new ReflectionProperty(Configuration::class, 'project_files');
        $reflectionPhpstormGenerics = new ReflectionProperty(Configuration::class, 'allow_phpstorm_generics');
        $reflectionUseDocblockTypes = new ReflectionProperty(Configuration::class, 'use_docblock_types');
        $reflectionTotallyTyped     = new ReflectionProperty(Configuration::class, 'totally_typed');
        $reflectionInstance         = new ReflectionProperty(Config::class, 'instance');

        $reflectionFiles->setAccessible(true);
        $reflectionPhpstormGenerics->setAccessible(true);
        $reflectionUseDocblockTypes->setAccessible(true);
        $reflectionTotallyTyped->setAccessible(true);
        $reflectionInstance->setAccessible(true);

        $projectFiles  = $this->createMock(ProjectFileFilter::class);
        $configuration = Configuration::forStrictlyCheckedNamespacesAndProjectFiles($projectFiles);

        self::assertSame($projectFiles, $reflectionFiles->getValue($configuration));
        self::assertTrue($reflectionPhpstormGenerics->getValue($configuration));
        self::assertTrue($reflectionUseDocblockTypes->getValue($configuration));
        self::assertTrue($reflectionTotallyTyped->getValue($configuration));
        self::assertSame($configuration, $reflectionInstance->getValue($configuration));
    }

    /**
     * @dataProvider expectedReportingLevels
     */
    public function testCheckedNamespaces(
        CodeIssue $issue,
        string $expectedReportingLevel,
        string ...$checkedNamespaces
    ) : void {
        $projectFiles = $this->createMock(ProjectFileFilter::class);

        self::assertSame(
            $expectedReportingLevel,
            Configuration::forStrictlyCheckedNamespacesAndProjectFiles($projectFiles, ...$checkedNamespaces)
                ->getReportingLevelForIssue($issue)
        );
    }

    /**
     * @return array<string, array<int, CodeIssue|string>>
     *
     * @psalm-return array<string, array{0: CodeIssue, 1: string}>
     */
    public function expectedReportingLevels() : array
    {
        $classIssue    = $this->createMock(ClassIssue::class);
        $propertyIssue = $this->createMock(PropertyIssue::class);
        $methodIssue   = $this->createMock(MethodIssue::class);
        $functionIssue = $this->createMock(FunctionIssue::class);
        $argumentIssue = $this->createMock(ArgumentIssue::class);

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
        ];
    }
}
