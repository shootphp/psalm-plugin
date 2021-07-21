<?php
declare(strict_types=1);

namespace Shoot\PsalmPlugin\Analyzers;

use Psalm\Internal\Analyzer\ClassLikeAnalyzer;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\AfterClassLikeAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterClassLikeAnalysisEvent;
use Psalm\Storage\PropertyStorage;
use Shoot\PsalmPlugin\Issues\InvalidPresentationModelFieldCasing;
use Shoot\PsalmPlugin\Issues\InvalidPresentationModelFieldVisibility;
use Shoot\Shoot\PresentationModel;

final class PresentationModelAnalyzer implements AfterClassLikeAnalysisInterface
{
    public static function afterStatementAnalysis(AfterClassLikeAnalysisEvent $event): void {
        $codebase = $event->getCodebase();
        $classLike = $event->getClasslikeStorage();

        if (!$codebase->classExtends($classLike->name, PresentationModel::class)) {
            return;
        }

        /** @var PropertyStorage $property */
        foreach ($classLike->properties as $name => $property) {
            if ($property->location === null) {
                continue;
            }

            if (preg_match('/^[a-z_\x80-\xff][a-z0-9_\x80-\xff]*$/S', $name) !== 1) {
                IssueBuffer::accepts(new InvalidPresentationModelFieldCasing(
                    "'{$name}' must use `snake_casing`, as per Twig's coding standards",
                    $property->location
                ), $classLike->suppressed_issues);
            }

            if ($property->visibility !== ClassLikeAnalyzer::VISIBILITY_PROTECTED) {
                IssueBuffer::accepts(new InvalidPresentationModelFieldVisibility(
                    "'{$name}' must be `protected` to preserve the presentation model's immutability, while allowing access from the PresentationModel base class",
                    $property->location
                ), $classLike->suppressed_issues);
            }
        }
    }
}
