<?php

namespace Core\Annotation;

class Route
{
  private string $path;
  private string $alias;
  private array $vars = [];


  public function __construct(
    string $path = null,
    ?string $alias = null,
    array $vars = []
  ) {
    $this->setPath($path);
    $this->setAlias($alias ?? self::createAlias($path));
    $this->setVars($vars);
  }

  public static function createAlias(string $path): string
  {
    $splittedPath = array_filter(explode("/", $path));
    $alias = implode("_", $splittedPath);
    $alias = str_replace("-", "_", $alias);
    return $alias;
  }


  /**
   * Get the value of vars
   *
   * @return array
   */
  public function getVars(array $routeVars): array
  {
    $vars = $this->vars;
    foreach ($routeVars as $key => $value) {
      $vars[$key] = $value;
    }
    return $vars;
  }

  /**
   * Set the value of vars
   *
   * @param array $vars
   *
   * @return self
   */
  public function setVars(array $vars): self
  {
    $this->vars = $vars;
    unset($this->vars["p"]);
    return $this;
  }

  /**
   * Get the value of alias
   *
   * @return string
   */
  public function getAlias(): string
  {
    return $this->alias;
  }

  /**
   * Set the value of alias
   *
   * @param string $alias
   *
   * @return self
   */
  public function setAlias(string $alias): self
  {
    $this->alias = $alias;

    return $this;
  }

  /**
   * Get the value of path
   *
   * @return string
   */
  public function getPath(): string
  {
    return $this->path;
  }

  /**
   * Set the value of path
   *
   * @param string $path
   *
   * @return self
   */
  public function setPath(string $path): self
  {
    $this->path = $path;

    return $this;
  }
}
