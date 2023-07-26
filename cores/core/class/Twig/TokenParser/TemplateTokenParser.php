<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\Twig\TokenParser;

use Core\Twig\Node\TemplateNode;
use Twig\Error\SyntaxError;
use Twig\Node\BodyNode;
use Twig\Node\MacroNode;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * Defines a template for js.
 * begin with {% template {id:"template_test"} %}
 * end with {% endtemplate %}
 * put content between script tags (with type="text/template" and id="template_test")
 * @internal
 */
final class TemplateTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $attributes = $this->parser->getExpressionParser()->parseExpression();
        $stream->expect(Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideBlockEnd'], true);
        $stream->expect(Token::BLOCK_END_TYPE);
        return new TemplateNode($attributes, $body, $lineno, $this->getTag());
    }

    public function decideBlockEnd(Token $token): bool
    {
        return $token->test('endtemplate');
    }

    public function getTag(): string
    {
        return 'template';
    }
}
