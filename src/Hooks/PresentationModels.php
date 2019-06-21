<?php
declare(strict_types=1);

namespace Shoot\PsalmPlugin\Hooks;

use PhpParser\Node\Stmt\ClassLike;
use Psalm\Codebase;
use Psalm\FileManipulation;
use Psalm\Internal\Analyzer\ClassLikeAnalyzer;
use Psalm\IssueBuffer;
use Psalm\Plugin\Hook\AfterClassLikeAnalysisInterface;
use Psalm\StatementsSource;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Storage\PropertyStorage;
use Shoot\PsalmPlugin\Issues\NonProtectedProperty;
use Shoot\Shoot\PresentationModel;

final class PresentationModels implements AfterClassLikeAnalysisInterface
{
    /**
     * @param ClassLike          $stmt
     * @param ClassLikeStorage   $classLikeStorage
     * @param StatementsSource   $statementsSource
     * @param Codebase           $codebase
     * @param FileManipulation[] $fileManipulations
     *
     * @return void
     */
    public static function afterStatementAnalysis(
        ClassLike $stmt,
        ClassLikeStorage $classLikeStorage,
        StatementsSource $statementsSource,
        Codebase $codebase,
        array &$fileManipulations = []
    ): void {
        if (!$codebase->classExtends($classLikeStorage->name, PresentationModel::class)) {
            return;
        }

        /** @var PropertyStorage $property */
        foreach ($classLikeStorage->properties as $propertyId => $property) {
            if ($property->visibility !== ClassLikeAnalyzer::VISIBILITY_PROTECTED) {
                $issue = new NonProtectedProperty(
                    "'{$propertyId}' should be protected, to preserve the presentation model's immutability",
                    $property->location,
                    $propertyId
                );

                if (!IssueBuffer::accepts($issue, $classLikeStorage->suppressed_issues)) {
                    break;
                };
            }
        }
    }
}
