<?php
declare(strict_types=1);

namespace Shoot\PsalmPlugin\Analyzers;

use PhpParser\Node\Stmt\ClassLike;
use Psalm\Codebase;
use Psalm\FileManipulation;
use Psalm\Internal\Analyzer\ClassLikeAnalyzer;
use Psalm\IssueBuffer;
use Psalm\Plugin\Hook\AfterClassLikeAnalysisInterface;
use Psalm\StatementsSource;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Storage\PropertyStorage;
use Shoot\PsalmPlugin\Issues\NonProtectedPresentationModelField;
use Shoot\Shoot\PresentationModel;

final class PresentationModelAnalyzer implements AfterClassLikeAnalysisInterface
{
    /**
     * @param ClassLike          $statement
     * @param ClassLikeStorage   $class
     * @param StatementsSource   $source
     * @param Codebase           $codebase
     * @param FileManipulation[] $fileManipulations
     *
     * @return void
     */
    public static function afterStatementAnalysis(
        ClassLike $statement,
        ClassLikeStorage $class,
        StatementsSource $source,
        Codebase $codebase,
        array &$fileManipulations = []
    ): void {
        if (!$codebase->classExtends($class->name, PresentationModel::class)) {
            return;
        }

        /** @var PropertyStorage $property */
        foreach ($class->properties as $propertyId => $property) {
            if ($property->location !== null && $property->visibility !== ClassLikeAnalyzer::VISIBILITY_PROTECTED) {
                $issue = new NonProtectedPresentationModelField(
                    "'{$propertyId}' should be `protected` to preserve the presentation model's immutability, while allowing access from the PresentationModel base class",
                    $property->location,
                    $propertyId
                );

                if (!IssueBuffer::accepts($issue, $class->suppressed_issues)) {
                    break;
                };
            }
        }
    }
}
