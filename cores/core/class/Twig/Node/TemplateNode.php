<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\Twig\Node;

use Twig\Compiler;
use Twig\Node\Node;

final class TemplateNode extends Node
{
  public function __construct($attributes, Node $body, int $lineno, string $tag = null)
  {
    $nodes = [
      'body' => $body,
      "attributes" => $attributes
    ];

    parent::__construct($nodes, [], $lineno, $tag);
  }

  public function compile(Compiler $compiler): void
  {
    $attributes = [];
    /** @var \ArrayIterator $iterator */
    $iterator = $this->getNode('attributes')->getIterator();
    foreach (array_chunk($iterator->getArrayCopy(), 2) as $chunk) {
      $attributes[$chunk[0]->getAttribute('value')] = $chunk[1]->getAttribute('value');
    }
    $extension = new \Core\Twig\Extension();
    $compiler->addDebugInfo($this);
    $compiler->write("ob_start();\n");
    $compiler->write("echo '<script type=\"text/template\" " . $extension->getAttrsString($compiler->getEnvironment(), $attributes, true) . ">';\n");
    $compiler->subcompile($this->getNode('body'));
    $compiler->write("echo '</script>';\n");
  }
}
