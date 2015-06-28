<?php

namespace Asgrim;

use PhpParser\Node\Stmt\Property as PropertyNode;
use PhpParser\Node\Param as ParamNode;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Type;

class TypesFinder
{
    /**
     * Given a property, attempt to find the type of the property
     *
     * @param PropertyNode $node
     * @return Type[]
     */
    public static function findTypeForProperty(PropertyNode $node)
    {
        /* @var \PhpParser\Comment\Doc $comment */
        if (!$node->hasAttribute('comments')) {
            return [];
        }
        $comment = $node->getAttribute('comments')[0];
        $docBlock = new DocBlock($comment->getReformattedText());

        /* @var \phpDocumentor\Reflection\DocBlock\Tag\VarTag $varTag */
        $varTag = $docBlock->getTagsByName('var')[0];
        return self::resolveTypes($varTag->getTypes());
    }

    /**
     * Given a function and parameter, attempt to find the type of the parameter
     *
     * @param ReflectionFunctionAbstract $function
     * @param ParamNode $node
     * @return Type[]
     */
    public static function findTypeForParameter(ReflectionFunctionAbstract $function, ParamNode $node)
    {
        $docBlock = new DocBlock($function->getDocComment());

        $paramTags = $docBlock->getTagsByName('param');

        foreach ($paramTags as $paramTag) {
            /* @var $paramTag \phpDocumentor\Reflection\DocBlock\Tag\ParamTag */
            if ($paramTag->getVariableName() == '$' . $node->name) {
                return self::resolveTypes($paramTag->getTypes());
            }
        }
        return [];
    }

    /**
     * @param string[] $stringTypes
     * @return Type[]
     */
    private static function resolveTypes($stringTypes)
    {
        $resolvedTypes = [];
        $resolver = new TypeResolver();

        foreach ($stringTypes as $stringType) {
            $resolvedTypes[] = $resolver->resolve($stringType);
        }

        return $resolvedTypes;
    }
}
