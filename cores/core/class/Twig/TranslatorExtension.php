<?php

namespace Core\Twig;

use Core\Translator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;


/** @package App\Twig */
class TranslatorExtension extends AbstractExtension
{
  public function getFilters()
  {
    return [
      new TwigFilter('t',  [Translator::class, 't']),
      new TwigFilter('trans', [Translator::class, 't']),
    ];
  }

  public function getFunctions()
  {
    return [];
  }
}
