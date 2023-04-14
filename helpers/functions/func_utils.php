<?php

use Core\Common\Site;

if (!function_exists("ucfirst")) {
  function ucfirst(string $string): string
  {
    if (empty($string)) return "";
    $string[0] = strtoupper($string[0]);
    return $string;
  }
}

if (!function_exists("getIp")) {
  /**
   * Récupère l'IP du client
   * @return mixed 
   */
  function getIp()
  {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
  }
}
if (!function_exists("getFILES")) {
  function getFILES($varName, $def = NULL)
  {
    return !empty($_FILES[$varName]) ? $_FILES[$varName] : $def;
  }
}
if (!function_exists("getPost")) {
  function getPost($varName, $def = NULL)
  {
    return !empty($_POST[$varName]) ? $_POST[$varName] : $def;
  }
}
if (!function_exists("getGet")) {
  function getGet($varName, $def = NULL)
  {
    return !empty($_GET[$varName]) ? $_GET[$varName] : $def;
  }
}
if (!function_exists("getGetPost")) {
  function getGetPost($varName, $def = NULL)
  {
    return getGet($varName, getPost($varName, $def));
  }
}
if (!function_exists("getRequest")) {
  function getRequest($varName, $def = NULL)
  {
    return !empty($_REQUEST[$varName]) ? $_REQUEST[$varName] : $def;
  }
}

if (!function_exists("object_to_array")) {
  /**
   * Concertis un objet en tableau récursivement
   * @param object $obj 
   * @return array 
   */
  function object_to_array($obj)
  {
    if (is_object($obj)) $obj = (array) $obj;
    if (is_array($obj)) {
      $new = array();
      foreach ($obj as $key => $val) {
        $new[$key] = object_to_array($val);
      }
    } else $new = $obj;
    return $new;
  }
}

if (!function_exists("pathUrl")) {
  /**
   * Retourne l'URL complète du répertoire renseigné
   * @param string $dir 
   * @return string 
   */
  function pathUrl($dir = __DIR__)
  {

    $root = "";
    $dir = str_replace('\\', '/', realpath($dir));

    //HTTPS or HTTP
    $root .= !empty($_SERVER['HTTPS']) ? 'https' : 'http';

    //HOST
    $root .= '://' . $_SERVER['HTTP_HOST'];

    //ALIAS
    if (!empty($_SERVER['CONTEXT_PREFIX'])) {
      $root .= $_SERVER['CONTEXT_PREFIX'];
      $root .= substr($dir, strlen($_SERVER['CONTEXT_DOCUMENT_ROOT']));
    } else {
      $root .= substr($dir, strlen($_SERVER['DOCUMENT_ROOT']));
    }

    $root .= '/';

    return $root;
  }
}

if (!function_exists("relativePathUrl")) {
  /**
   * Retourne l'URL relative du répertoire renseigné par au domaine du site
   * @param string $dir 
   * @return string 
   */
  function relativePathUrl($dir = __DIR__)
  {
    $root = "";
    $dir = str_replace('\\', '/', realpath($dir));

    //ALIAS
    if (!empty($_SERVER['CONTEXT_PREFIX'])) {
      $root .= $_SERVER['CONTEXT_PREFIX'];
      $root .= substr($dir, strlen($_SERVER['CONTEXT_DOCUMENT_ROOT']));
    } else {
      $root .= substr($dir, strlen($_SERVER['DOCUMENT_ROOT']));
    }

    $root .= '/';

    return $root;
  }
}
if (!function_exists("siteURL")) {
  /**
   * Retourne le protocole http/https + le domaine du site
   * @return string 
   */
  function siteURL()
  {
    $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ||
      $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];
    return $protocol . $domainName;
  }
}

if (!function_exists("arrayFromJson")) {
  /**
   * Retourne un tableau associatif extrait du fichier JSON sont le chemin est renseigné
   * @param string $fullPath [Chemin complet ou relatif vers le fichier JSON]
   * @return array 
   */
  function arrayFromJson($fullPath)
  {
    $return = [];
    if (!empty($fullPath)) {
      $sContent = file_get_contents($fullPath);
      if (!empty($sContent)) {
        $oContent = json_decode($sContent);
        $return = object_to_array($oContent);
      }
    }
    return $return;
  }
}

if (!function_exists("assetsMap")) {
  /**
   * Retourne un mapping du répertoire donné et de ses enfants jusqu'à un degré donné
   * @param string $source_dir [Répertoire à mapper]
   * @param int $directory_depth [Profondeur de récursivité]
   * @param bool $hidden [Mapper les fichiers/dossiers cachés]
   * @return array|false 
   */
  function assetsMap($source_dir, $directory_depth = 0, $hidden = FALSE)
  {
    if ($fp = @opendir($source_dir)) {
      $filedata   = array();
      $new_depth  = $directory_depth - 1;
      $source_dir = rtrim($source_dir, '/') . '/';

      while (FALSE !== ($file = readdir($fp))) {
        // Remove '.', '..', and hidden files [optional]
        if (!trim($file, '.') or ($hidden == FALSE && $file[0] == '.')) {
          continue;
        }

        if (($directory_depth < 1 or $new_depth > 0) && @is_dir($source_dir . $file)) {
          $filedata[$file] = assetsMap($source_dir . $file . '/', $new_depth, $hidden);
        } else {
          $filedata[] = $file;
        }
      }

      closedir($fp);
      return $filedata;
    }
    echo 'can not open dir';
    return FALSE;
  }
}

if (!function_exists("redirectURL")) {
  /**
   * Redirige vers une URL donnée
   * @param string $url [L'URL en question]
   * @param int $code [Code d'erreur retourné]
   * @return bool 
   */
  function redirectURL($url)
  {
    if (!empty($url)) {
      header("Location: $url", TRUE, 301);
      exit;
      return true;
    }
    return false;
  }
}
if (!function_exists("redirectRoute")) {
  /**
   * Redirige vers une route donnée
   * @param mixed $alias [Alias de la route]
   * @param array $variables [Variables de la route]
   * @param int $code [Code d'erreur retourné]
   * @return bool 
   */
  function redirectRoute($alias, $variables = [], $code = 301)
  {
    $routing = Site::getRouting();
    if (empty($routing)) return false;
    return $routing->redirect($alias, $variables, $code);
  }
}

if (!function_exists("jsonResponse")) {
  /**
   * Echo une réponse JSON et stoppe le code
   * @param array $datas [Contenu de la réponse JSON]
   * @param int $code [Code d'erreur retourné]
   * @return false 
   */
  function jsonResponse($datas, $code = 200)
  {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datas);
    exit;
    return false;
  }
}

if (!function_exists("successResponse")) {
  /**
   * Echo un réponse pré-formatée de type "succès" avec un result = 1
   * @param mixed $datas [Contenu de l'argument "data" de la réponse]
   * @param string $message [Contenu de l'argument "message" de la réponse]
   * @return false 
   */
  function successResponse(array $datas = [],string $message = "")
  {
    return jsonResponse([
      "result" => 1, "message" => $message, "data" => $datas
    ]);
  }
}

if (!function_exists("errorResponse")) {
  /**
   * Echo un réponse pré-formatée de type "échec" avec un result = 0
   * @param mixed $datas [Contenu de l'argument "data" de la réponse]
   * @param string $message [Contenu de l'argument "message" de la réponse]
   * @param int $code [Code d'erreur retourné]
   * @return false 
   */
  function errorResponse(array $datas = [], string $message = "", int $code = 500)
  {
    return jsonResponse([
      "result" => 0, "message" => $message, "data" => $datas
    ], $code);
  }
}

if (!function_exists("setArrayKey")) {
  /**
   * Retourne une table de table enfants avec les index réassignés en fonction de $key
   Exemple :
   $array = [
    0 => ["id"=>5,"nom"=>"Cinq"],
    1 => ["id"=>10,"nom"=>"Dix"],
   ]
   setArrayKey($array,"id") retourne :
   [
    5 => ["id"=>5,"nom"=>"Cinq"],
    10 => ["id"=>10,"nom"=>"Dix"],
   ]
   setArrayKey($array,"nom") retourne :
   [
    "Cinq" => ["id"=>5,"nom"=>"Cinq"],
    "Dix" => ["id"=>10,"nom"=>"Dix"],
   ]
   * @param array $array [Une table avec tables enfants]
   * @param string|int $key [Clé de la valeur récupérée dans la table enfant]
   * @return array 
   */
  function setArrayKey($array, $key)
  {
    if (empty($array) || empty($key)) return $array;
    $return = [];
    foreach ($array as $entry) {
      if (!empty($entry[$key])) {
        $return[$entry[$key]] = $entry;
      }
    }
    return $return;
  }
}

if (!function_exists("getArrayValue")) {
  /**
   * Retourne L'entrée dans le tableau $array correspondant à l'index $offset
   * Retourne $default si inexistant
   * @param array $array [Table]
   * @param string|int $offset [Index de l'entrée dans le tableau]
   * @param mixed $default [Valeur par défaut si Entrée inexistante]
   * @param bool $checkEmpty [Si true, vérifie si la valeur est vide]
   * @return mixed 
   */
  function getArrayValue($array, $offset, $default = null, $checkEmpty = false)
  {
    if (empty($array) || $offset === null) return $default;
    if ($checkEmpty) return !empty($array[$offset]) ? $array[$offset] : $default;
    return isset($array[$offset]) ? $array[$offset] : $default;
  }
}

if (!function_exists("getEmptyArrayValue")) {
  /**
   * Raccourci de getArrayValue avec $checkEmpty = true
   * @param array $array [Table]
   * @param int|string $offset [Index de l'entrée dans le tableau]
   * @param mixed|null $default [Valeur par défaut si Entrée inexistante]
   * @return mixed 
   */
  function getEmptyArrayValue($array, $offset, $default = null)
  {
    return getArrayValue($array, $offset, $default, true);
  }
}

if (!function_exists("unsetArrayValue")) {
  /**
   * Unset une entrée dans une table si elle existe
   * @param array $array 
   * @param int|string $offset 
   * @return void 
   */
  function unsetArrayValue(&$array, $offset)
  {
    if (isset($array[$offset])) unset($array[$offset]);
  }
}

if (!function_exists("num2alpha")) {
  /**
   * Convertie une valeur numérique en alphanumérique (pour excel)
   * @param int|string $n 
   * @return string 
   */
  function num2alpha($n)
  {
    for ($r = ""; $n >= 0; $n = intval($n / 26) - 1)
      $r = chr($n % 26 + 0x41) . $r;
    return $r;
  }
}

if (!function_exists("stripAccents")) {
  function stripAccents($stripAccents)
  {
    //return strtr($stripAccents, 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ', 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    $transliteration = array(
      'Ĳ' => 'I', 'Ö' => 'O', 'Œ' => 'O', 'Ü' => 'U', 'ä' => 'a', 'æ' => 'a',
      'ĳ' => 'i', 'ö' => 'o', 'œ' => 'o', 'ü' => 'u', 'ß' => 's', 'ſ' => 's',
      'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
      'Æ' => 'A', 'Ā' => 'A', 'Ą' => 'A', 'Ă' => 'A', 'Ç' => 'C', 'Ć' => 'C',
      'Č' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C', 'Ď' => 'D', 'Đ' => 'D', 'È' => 'E',
      'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E', 'Ę' => 'E', 'Ě' => 'E',
      'Ĕ' => 'E', 'Ė' => 'E', 'Ĝ' => 'G', 'Ğ' => 'G', 'Ġ' => 'G', 'Ģ' => 'G',
      'Ĥ' => 'H', 'Ħ' => 'H', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
      'Ī' => 'I', 'Ĩ' => 'I', 'Ĭ' => 'I', 'Į' => 'I', 'İ' => 'I', 'Ĵ' => 'J',
      'Ķ' => 'K', 'Ľ' => 'K', 'Ĺ' => 'K', 'Ļ' => 'K', 'Ŀ' => 'K', 'Ł' => 'L',
      'Ñ' => 'N', 'Ń' => 'N', 'Ň' => 'N', 'Ņ' => 'N', 'Ŋ' => 'N', 'Ò' => 'O',
      'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ø' => 'O', 'Ō' => 'O', 'Ő' => 'O',
      'Ŏ' => 'O', 'Ŕ' => 'R', 'Ř' => 'R', 'Ŗ' => 'R', 'Ś' => 'S', 'Ş' => 'S',
      'Ŝ' => 'S', 'Ș' => 'S', 'Š' => 'S', 'Ť' => 'T', 'Ţ' => 'T', 'Ŧ' => 'T',
      'Ț' => 'T', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ū' => 'U', 'Ů' => 'U',
      'Ű' => 'U', 'Ŭ' => 'U', 'Ũ' => 'U', 'Ų' => 'U', 'Ŵ' => 'W', 'Ŷ' => 'Y',
      'Ÿ' => 'Y', 'Ý' => 'Y', 'Ź' => 'Z', 'Ż' => 'Z', 'Ž' => 'Z', 'à' => 'a',
      'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ā' => 'a', 'ą' => 'a', 'ă' => 'a',
      'å' => 'a', 'ç' => 'c', 'ć' => 'c', 'č' => 'c', 'ĉ' => 'c', 'ċ' => 'c',
      'ď' => 'd', 'đ' => 'd', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
      'ē' => 'e', 'ę' => 'e', 'ě' => 'e', 'ĕ' => 'e', 'ė' => 'e', 'ƒ' => 'f',
      'ĝ' => 'g', 'ğ' => 'g', 'ġ' => 'g', 'ģ' => 'g', 'ĥ' => 'h', 'ħ' => 'h',
      'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i', 'ĩ' => 'i',
      'ĭ' => 'i', 'į' => 'i', 'ı' => 'i', 'ĵ' => 'j', 'ķ' => 'k', 'ĸ' => 'k',
      'ł' => 'l', 'ľ' => 'l', 'ĺ' => 'l', 'ļ' => 'l', 'ŀ' => 'l', 'ñ' => 'n',
      'ń' => 'n', 'ň' => 'n', 'ņ' => 'n', 'ŉ' => 'n', 'ŋ' => 'n', 'ò' => 'o',
      'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ø' => 'o', 'ō' => 'o', 'ő' => 'o',
      'ŏ' => 'o', 'ŕ' => 'r', 'ř' => 'r', 'ŗ' => 'r', 'ś' => 's', 'š' => 's',
      'ť' => 't', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ū' => 'u', 'ů' => 'u',
      'ű' => 'u', 'ŭ' => 'u', 'ũ' => 'u', 'ų' => 'u', 'ŵ' => 'w', 'ÿ' => 'y',
      'ý' => 'y', 'ŷ' => 'y', 'ż' => 'z', 'ź' => 'z', 'ž' => 'z', 'Α' => 'A',
      'Ά' => 'A', 'Ἀ' => 'A', 'Ἁ' => 'A', 'Ἂ' => 'A', 'Ἃ' => 'A', 'Ἄ' => 'A',
      'Ἅ' => 'A', 'Ἆ' => 'A', 'Ἇ' => 'A', 'ᾈ' => 'A', 'ᾉ' => 'A', 'ᾊ' => 'A',
      'ᾋ' => 'A', 'ᾌ' => 'A', 'ᾍ' => 'A', 'ᾎ' => 'A', 'ᾏ' => 'A', 'Ᾰ' => 'A',
      'Ᾱ' => 'A', 'Ὰ' => 'A', 'ᾼ' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D',
      'Ε' => 'E', 'Έ' => 'E', 'Ἐ' => 'E', 'Ἑ' => 'E', 'Ἒ' => 'E', 'Ἓ' => 'E',
      'Ἔ' => 'E', 'Ἕ' => 'E', 'Ὲ' => 'E', 'Ζ' => 'Z', 'Η' => 'I', 'Ή' => 'I',
      'Ἠ' => 'I', 'Ἡ' => 'I', 'Ἢ' => 'I', 'Ἣ' => 'I', 'Ἤ' => 'I', 'Ἥ' => 'I',
      'Ἦ' => 'I', 'Ἧ' => 'I', 'ᾘ' => 'I', 'ᾙ' => 'I', 'ᾚ' => 'I', 'ᾛ' => 'I',
      'ᾜ' => 'I', 'ᾝ' => 'I', 'ᾞ' => 'I', 'ᾟ' => 'I', 'Ὴ' => 'I', 'ῌ' => 'I',
      'Θ' => 'T', 'Ι' => 'I', 'Ί' => 'I', 'Ϊ' => 'I', 'Ἰ' => 'I', 'Ἱ' => 'I',
      'Ἲ' => 'I', 'Ἳ' => 'I', 'Ἴ' => 'I', 'Ἵ' => 'I', 'Ἶ' => 'I', 'Ἷ' => 'I',
      'Ῐ' => 'I', 'Ῑ' => 'I', 'Ὶ' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M',
      'Ν' => 'N', 'Ξ' => 'K', 'Ο' => 'O', 'Ό' => 'O', 'Ὀ' => 'O', 'Ὁ' => 'O',
      'Ὂ' => 'O', 'Ὃ' => 'O', 'Ὄ' => 'O', 'Ὅ' => 'O', 'Ὸ' => 'O', 'Π' => 'P',
      'Ρ' => 'R', 'Ῥ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Ύ' => 'Y',
      'Ϋ' => 'Y', 'Ὑ' => 'Y', 'Ὓ' => 'Y', 'Ὕ' => 'Y', 'Ὗ' => 'Y', 'Ῠ' => 'Y',
      'Ῡ' => 'Y', 'Ὺ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'P', 'Ω' => 'O',
      'Ώ' => 'O', 'Ὠ' => 'O', 'Ὡ' => 'O', 'Ὢ' => 'O', 'Ὣ' => 'O', 'Ὤ' => 'O',
      'Ὥ' => 'O', 'Ὦ' => 'O', 'Ὧ' => 'O', 'ᾨ' => 'O', 'ᾩ' => 'O', 'ᾪ' => 'O',
      'ᾫ' => 'O', 'ᾬ' => 'O', 'ᾭ' => 'O', 'ᾮ' => 'O', 'ᾯ' => 'O', 'Ὼ' => 'O',
      'ῼ' => 'O', 'α' => 'a', 'ά' => 'a', 'ἀ' => 'a', 'ἁ' => 'a', 'ἂ' => 'a',
      'ἃ' => 'a', 'ἄ' => 'a', 'ἅ' => 'a', 'ἆ' => 'a', 'ἇ' => 'a', 'ᾀ' => 'a',
      'ᾁ' => 'a', 'ᾂ' => 'a', 'ᾃ' => 'a', 'ᾄ' => 'a', 'ᾅ' => 'a', 'ᾆ' => 'a',
      'ᾇ' => 'a', 'ὰ' => 'a', 'ᾰ' => 'a', 'ᾱ' => 'a', 'ᾲ' => 'a', 'ᾳ' => 'a',
      'ᾴ' => 'a', 'ᾶ' => 'a', 'ᾷ' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd',
      'ε' => 'e', 'έ' => 'e', 'ἐ' => 'e', 'ἑ' => 'e', 'ἒ' => 'e', 'ἓ' => 'e',
      'ἔ' => 'e', 'ἕ' => 'e', 'ὲ' => 'e', 'ζ' => 'z', 'η' => 'i', 'ή' => 'i',
      'ἠ' => 'i', 'ἡ' => 'i', 'ἢ' => 'i', 'ἣ' => 'i', 'ἤ' => 'i', 'ἥ' => 'i',
      'ἦ' => 'i', 'ἧ' => 'i', 'ᾐ' => 'i', 'ᾑ' => 'i', 'ᾒ' => 'i', 'ᾓ' => 'i',
      'ᾔ' => 'i', 'ᾕ' => 'i', 'ᾖ' => 'i', 'ᾗ' => 'i', 'ὴ' => 'i', 'ῂ' => 'i',
      'ῃ' => 'i', 'ῄ' => 'i', 'ῆ' => 'i', 'ῇ' => 'i', 'θ' => 't', 'ι' => 'i',
      'ί' => 'i', 'ϊ' => 'i', 'ΐ' => 'i', 'ἰ' => 'i', 'ἱ' => 'i', 'ἲ' => 'i',
      'ἳ' => 'i', 'ἴ' => 'i', 'ἵ' => 'i', 'ἶ' => 'i', 'ἷ' => 'i', 'ὶ' => 'i',
      'ῐ' => 'i', 'ῑ' => 'i', 'ῒ' => 'i', 'ῖ' => 'i', 'ῗ' => 'i', 'κ' => 'k',
      'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => 'k', 'ο' => 'o', 'ό' => 'o',
      'ὀ' => 'o', 'ὁ' => 'o', 'ὂ' => 'o', 'ὃ' => 'o', 'ὄ' => 'o', 'ὅ' => 'o',
      'ὸ' => 'o', 'π' => 'p', 'ρ' => 'r', 'ῤ' => 'r', 'ῥ' => 'r', 'σ' => 's',
      'ς' => 's', 'τ' => 't', 'υ' => 'y', 'ύ' => 'y', 'ϋ' => 'y', 'ΰ' => 'y',
      'ὐ' => 'y', 'ὑ' => 'y', 'ὒ' => 'y', 'ὓ' => 'y', 'ὔ' => 'y', 'ὕ' => 'y',
      'ὖ' => 'y', 'ὗ' => 'y', 'ὺ' => 'y', 'ῠ' => 'y', 'ῡ' => 'y', 'ῢ' => 'y',
      'ῦ' => 'y', 'ῧ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'p', 'ω' => 'o',
      'ώ' => 'o', 'ὠ' => 'o', 'ὡ' => 'o', 'ὢ' => 'o', 'ὣ' => 'o', 'ὤ' => 'o',
      'ὥ' => 'o', 'ὦ' => 'o', 'ὧ' => 'o', 'ᾠ' => 'o', 'ᾡ' => 'o', 'ᾢ' => 'o',
      'ᾣ' => 'o', 'ᾤ' => 'o', 'ᾥ' => 'o', 'ᾦ' => 'o', 'ᾧ' => 'o', 'ὼ' => 'o',
      'ῲ' => 'o', 'ῳ' => 'o', 'ῴ' => 'o', 'ῶ' => 'o', 'ῷ' => 'o', 'А' => 'A',
      'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E',
      'Ж' => 'Z', 'З' => 'Z', 'И' => 'I', 'Й' => 'I', 'К' => 'K', 'Л' => 'L',
      'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S',
      'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'K', 'Ц' => 'T', 'Ч' => 'C',
      'Ш' => 'S', 'Щ' => 'S', 'Ы' => 'Y', 'Э' => 'E', 'Ю' => 'Y', 'Я' => 'Y',
      'а' => 'A', 'б' => 'B', 'в' => 'V', 'г' => 'G', 'д' => 'D', 'е' => 'E',
      'ё' => 'E', 'ж' => 'Z', 'з' => 'Z', 'и' => 'I', 'й' => 'I', 'к' => 'K',
      'л' => 'L', 'м' => 'M', 'н' => 'N', 'о' => 'O', 'п' => 'P', 'р' => 'R',
      'с' => 'S', 'т' => 'T', 'у' => 'U', 'ф' => 'F', 'х' => 'K', 'ц' => 'T',
      'ч' => 'C', 'ш' => 'S', 'щ' => 'S', 'ы' => 'Y', 'э' => 'E', 'ю' => 'Y',
      'я' => 'Y', 'ð' => 'd', 'Ð' => 'D', 'þ' => 't', 'Þ' => 'T', 'ა' => 'a',
      'ბ' => 'b', 'გ' => 'g', 'დ' => 'd', 'ე' => 'e', 'ვ' => 'v', 'ზ' => 'z',
      'თ' => 't', 'ი' => 'i', 'კ' => 'k', 'ლ' => 'l', 'მ' => 'm', 'ნ' => 'n',
      'ო' => 'o', 'პ' => 'p', 'ჟ' => 'z', 'რ' => 'r', 'ს' => 's', 'ტ' => 't',
      'უ' => 'u', 'ფ' => 'p', 'ქ' => 'k', 'ღ' => 'g', 'ყ' => 'q', 'შ' => 's',
      'ჩ' => 'c', 'ც' => 't', 'ძ' => 'd', 'წ' => 't', 'ჭ' => 'c', 'ხ' => 'k',
      'ჯ' => 'j', 'ჰ' => 'h'
    );
    $str = str_replace(
      array_keys($transliteration),
      array_values($transliteration),
      $stripAccents
    );
    return $str;
  }
}

if (!function_exists("qi")) {
  function qi($str)
  {
    return '`' . preg_replace('/`/', '``', $str) . '`';
  }
}

if (!function_exists("uniqtoken")) {
  function uniqtoken()
  {
    return uniqid(bin2hex(random_bytes(20)));
  }
}

if (!function_exists("dieAndClose")) {
  function dieAndClose()
  {
    echo "<script>window.close();</script>";
    exit;
  }
}

if (!function_exists("deleteAll")) {
  function deleteAll($dir)
  {
    foreach (glob($dir . '/*') as $file) {
      if (is_dir($file))
        deleteAll($file);
      else
        unlink($file);
    }
    rmdir($dir);
  }
}

if (!function_exists("escape_attr_html")) {

  /**
   * Escape une valeur pour être utilisée dans un attribut HTML/XML (pas besoin de quotes)
   * Volée à Twig
   * @param mixed $value
   * @return string 
   */
  function escape_attr_html($value)
  {
    $value = (string) $value;
    return preg_replace_callback('#[^a-zA-Z0-9,\.\-_]#Su', function ($matches) {
      /**
       * This function is adapted from code coming from Zend Framework.
       *
       * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (https://www.zend.com)
       * @license   https://framework.zend.com/license/new-bsd New BSD License
       */
      $chr = $matches[0];
      $ord = \ord($chr);

      /*
       * The following replaces characters undefined in HTML with the
       * hex entity for the Unicode replacement character.
       */
      if (($ord <= 0x1f && "\t" != $chr && "\n" != $chr && "\r" != $chr) || ($ord >= 0x7f && $ord <= 0x9f)) {
        return '&#xFFFD;';
      }

      /*
       * Check if the current character to escape has a name entity we should
       * replace it with while grabbing the hex value of the character.
       */
      if (1 === \strlen($chr)) {
        /*
           * While HTML supports far more named entities, the lowest common denominator
           * has become HTML5's XML Serialisation which is restricted to the those named
           * entities that XML supports. Using HTML entities would result in this error:
           *     XML Parsing Error: undefined entity
           */
        static $entityMap = [
          34 => '&quot;', /* quotation mark */
          38 => '&amp;',  /* ampersand */
          60 => '&lt;',   /* less-than sign */
          62 => '&gt;',   /* greater-than sign */
        ];

        if (isset($entityMap[$ord])) {
          return $entityMap[$ord];
        }

        return sprintf('&#x%02X;', $ord);
      }

      /*
       * Per OWASP recommendations, we'll use hex entities for any other
       * characters where a named entity does not exist.
       */
      return sprintf('&#x%04X;', mb_ord($chr, 'UTF-8'));
    }, $value);
  }
}

if (!function_exists("escape_attr_js")) {

  /**
   * Escape une valeur pour être utilisée dans le JS
   * Volée à Twig
   * @param mixed $value
   * @return string 
   */
  function escape_attr_js($value)
  {
    $string = json_encode($value);
    return preg_replace_callback('#[^a-zA-Z0-9,\._]#Su', function ($matches) {
      $char = $matches[0];

      /*
         * A few characters have short escape sequences in JSON and JavaScript.
         * Escape sequences supported only by JavaScript, not JSON, are omitted.
         * \" is also supported but omitted, because the resulting string is not HTML safe.
         */
      static $shortMap = [
        '\\' => '\\\\',
        '/' => '\\/',
        "\x08" => '\b',
        "\x0C" => '\f',
        "\x0A" => '\n',
        "\x0D" => '\r',
        "\x09" => '\t',
      ];

      if (isset($shortMap[$char])) {
        return $shortMap[$char];
      }

      $codepoint = mb_ord($char);
      if (0x10000 > $codepoint) {
        return sprintf('\u%04X', $codepoint);
      }

      // Split characters outside the BMP into surrogate pairs
      // https://tools.ietf.org/html/rfc2781.html#section-2.1
      $u = $codepoint - 0x10000;
      $high = 0xD800 | ($u >> 10);
      $low = 0xDC00 | ($u & 0x3FF);

      return sprintf('\u%04X\u%04X', $high, $low);
    }, $string);
  }
}

if (!function_exists("imgToBase64")){
  function imgToBase64($fileFullPath)
  {
    $type = pathinfo($fileFullPath, PATHINFO_EXTENSION);
    $data = file_get_contents($fileFullPath);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    return $base64;
  }
}

if (!function_exists("getConfig")) {

  /**
   * Récupère la config dans site_config.json
   * @param string|null $configPath
   * @return mixed 
   */
  function getConfig($configPath = null)
  {
    if (!defined("CONFIGS")){
      define("CONFIGS",json_decode(file_get_contents(Site::getConfigFilePath()),true));
    }
    $config = CONFIGS;
    if ($configPath){
      foreach(explode(".",$configPath) as $pathPart){
        if ($pathPart === '$ENV'){
          $pathPart = ENV;
        }
        if (is_array($config)){
          $config = $config[$pathPart] ?? null;
        }else{
          return null;
        }
      }
    }
    return $config;
  }
}