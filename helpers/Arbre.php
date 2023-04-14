<?php
namespace Helper;
set_time_limit(0);

/**
  -------------------------------
  METHODS :
  -------------------------------
  __construct()
  addArbre()
  copy()
  delete()
  get_descendants()
  get_descendant_count()
  get_next_sibling()
  get_parent()
  get_path()
  get_previous_sibling()
  get_siblings()
  get_tree()
  move()
  to_list()
  to_select()
  update()

 */
class Arbre extends \Core\Db {

  public static $tbl = 'arbre';
  public $col_id = 'id';
  public $col_uid = 'uid';
  public $col_title = 'title';
  public $cg = 'gauche';
  public $cd = 'droite';
  public $col_parent = 'parent';
  public $lookup = [];

  function __construct($properties = NULL) {
    parent::__construct();
    $this->setProperties($properties);
  }

  private function setProperties($properties = NULL) {
    if (!empty($properties) && is_array($properties)) {
      foreach ($properties as $key => $value) {
        $propName = strtolower($key);
        if (!empty($propName) && property_exists('arbre', $propName)) {
          $this->{$propName} = $value;
        }
      }
    }
  }

  public function get_FullTree() {
    $sql = "SELECT *
            FROM " . $this->tbl . "
            ORDER BY " . $this->cg . "";

    return parent::db_all($sql);
  }

  private function _init() {
    if (empty($this->lookup)) {
      $allTree = $this->get_FullTree();
      if (!empty($allTree)) {
        foreach ($allTree as $R) {
          $this->lookup[$R[$this->col_id]] = $R;
        }
      }
    }
  }

  /**
   * Updates the lookup array after inserts and deletes.
   */
  private function _reorder_lookup_array() {
    $tmp = [];
    foreach ($this->lookup as $properties) {
      ${$this->cg}[] = $properties[$this->cg];
    }
    array_multisort(${$this->cg}, SORT_ASC, $this->lookup);
    foreach ($this->lookup as $properties) {
      $tmp[$properties[$this->col_id]] = $properties;
    }
    $this->lookup = $tmp;
    unset($tmp);
  }

  /**
   *  Adds a new node as the child of a given parent node.
   *
   *  <code>
   *  // add a new topmost node
   *  $node = $mptt->add(0, 'Main');
   *
   *  // add a child node
   *  $mptt->add($node, 'Child 1');
   *
   *  // add another child node
   *  $mptt->add($node, 'Child 2');
   *
   *  // insert a third child node
   *  // notice the "1" as the last argument, instructing the script to insert the child node
   *  // as the second child node, after "Child 1"
   *  // remember that the trees are 0-based, meaning that the first node in a tree has the index 0!
   *  $mptt->add($node, 'Child 3', 1);
   *
   *  // and finally, insert a fourth child node
   *  // notice the "0" as the last argument, instructing the script to insert the child node
   *  // as the very first child node of the parent node
   *  // remember that the trees are 0-based, meaning that the first node in a tree has the index 0!
   *  $mptt->add($node, 'Child 4', 0);
   *  </code>
   *
   *  @param  integer     $parent     The ID of the parent node.
   *
   *                                  Use "0" to add a topmost node.
   *
   *  @param  string      $title      The title of the node.
   *
   *  @param  integer     $position   (Optional) The position the node will have among the parent node's children nodes.
   *
   *                                  When parent node is given as "0", this refers to the position the node will have
   *                                  among the topmost nodes.
   *
   *                                  The values are 0-based, meaning that if you want the node to be inserted as
   *                                  the first child of the target node, you have to use "0", if you want it to
   *                                  be second, use "1", and so on.
   *
   *                                  If not given (or given as boolean FALSE), the node will be inserted as the last
   *                                  of the parent node's children nodes.
   *
   *  @return mixed                   Returns the ID of the newly inserted node or FALSE on error.
   */
  public function addArbre($parent, $title, $position = false) {
    $this->_init();
    // make sure parent ID is an integer
    $parent = (int) $parent;
    // continue only if
    if (
    // we are adding a topmost node OR
            $parent == 0 ||
            // parent node exists in the lookup array
            isset($this->lookup[$parent])
    ) {
      // get parent's descendant nodes (no deeper than the first level)
      $descendants = $this->get_descendants($parent);
      // if node is to be inserted in the default position (as the last of the parent node's children)
      // give a numerical value to the position
      if ($position === false)
        $position = count($descendants);
      // if a custom position was specified
      else {
        // make sure that position is an integer value
        $position = (int) $position;
        // if position is a bogus number
        // use the default position (as the last of the parent node's children)
        if ($position > count($descendants) || $position < 0)
          $position = count($descendants);
      }
      // if parent has no descendants OR the node is to be inserted as the parent node's first child
      if (empty($descendants) || $position == 0)
      // set the boundary - nodes having their "left"/"right" values outside this boundary will be affected by
      // the insert, and will need to be updated
      // if parent is not found (meaning that we're inserting a topmost node) set the boundary to 0
        $boundary = isset($this->lookup[$parent]) ? $this->lookup[$parent][$this->cg] : 0;
      // if parent node has descendant nodes and/or the node needs to be inserted at a specific position
      else {
        // find the child node that currently exists at the position where the new node needs to be inserted to
        $slice = array_slice($descendants, $position - 1, 1);
        $descendants = array_shift($slice);
        // set the boundary - nodes having their "left"/"right" values outside this boundary will be affected by
        // the insert, and will need to be updated
        $boundary = $descendants[$this->cd];
      }
      // iterate through all the records in the lookup array
      foreach ($this->lookup as $id => $properties) {
        // if the node's "left" value is outside the boundary
        if ($properties[$this->cg] > $boundary)
        // increment it with 2
          $this->lookup[$id][$this->cg] += 2;
        // if the node's "right" value is outside the boundary
        if ($properties[$this->cd] > $boundary)
        // increment it with 2
          $this->lookup[$id][$this->cd] += 2;
      }
      // lock table to prevent other sessions from modifying the data and thus preserving data integrity
      parent::lockTable($this->tbl);
      // update the nodes in the database having their "left"/"right" values outside the boundary
      $this->updateBorne($this->cg, 2, $boundary);
      $this->updateBorne($this->cd, 2, $boundary);

      // insert the new node into the database
      $nodesDatas = [];
      $nodesDatas[$this->col_title] = $title;
      $nodesDatas[$this->cg] = $boundary + 1;
      $nodesDatas[$this->cd] = $boundary + 2;
      $nodesDatas[$this->col_parent] = $parent;
      $nodesDatas[$this->col_uid] = $boundary + 2;
      $node = $this->insertNode($nodesDatas);
      $node_id = $node['id'];
      $node_uid = $node['uid'];
      // release table lock
      parent::unlockTables();
      // add the node to the lookup array
      $this->lookup[$node_id] = array(
          $this->col_id => $node_id,
          $this->col_uid => $node_uid,
          $this->col_title => $title,
          $this->cg => $boundary + 1,
          $this->cd => $boundary + 2,
          $this->col_parent => $parent,
      );
      // reorder the lookup array
      $this->_reorder_lookup_array();
      // return the ID of the newly inserted node
      return array('id' => $node_id, 'uid' => $node_uid);
    }

    return false;
  }

  /**
   *  Creates a copy of a node (including its descendant nodes) as the child node of a given node.
   *
   *  <code>
   *  // insert a topmost node
   *  $node = $mptt->add(0, 'Main');
   *
   *  // add a child node
   *  $child1 = $mptt->add($node, 'Child 1');
   *
   *  // add another child node
   *  $child2 = $mptt->add($node, 'Child 2');
   *
   *  // create a copy of "Child 2" node and put it as "Child 1"'s child
   *  $mptt->copy($child2, $child1);
   *  </code>
   *
   *  @param  integer     $source     The ID of a node to copy.
   *
   *                                  <i>Remember that the node will be copied together with all its descendant nodes!</i>
   *
   *  @param  integer     $target     The ID of a node which will become the copy's parent node.
   *
   *                                  Use "0" to make the copy a topmost node.
   *
   *  @param  integer     $position   (Optional) The position the node will have among the target node's children
   *                                  nodes.
   *
   *                                  When target node is "0", this refers to the position the node will have among
   *                                  the topmost nodes.
   *
   *                                  The values are 0-based, meaning that if you want the node to be inserted as
   *                                  the first child of the target node, you have to use "0", if you want it to
   *                                  be second, use "1", and so on.
   *
   *                                  If not given (or given as boolean FALSE), the node will be inserted as the last
   *                                  of the target node's children nodes.
   *
   *  @return mixed                   Returns the ID of the newly created copy, or FALSE on error.
   */
  public function copy($source, $target, $position = false) {

    $this->_init();
    // continue only if
    if (
    // source node exists in the lookup array AND
            isset($this->lookup[$source]) &&
            // target node exists in the lookup array OR is 0 (indicating a topmost node)
            (isset($this->lookup[$target]) || $target == 0)
    ) {
      // get the source's children nodes (if any)
      $source_children = $this->get_descendants($source, false);
      // this array will hold the items we need to copy
      // by default we add the source item to it
      $sources = array($this->lookup[$source]);
      // the copy's parent will be the target node
      $sources[0][$this->col_parent] = $target;
      // iterate through source node's children
      foreach ($source_children as $child)
      // save them for later use
        $sources[] = $this->lookup[$child[$this->col_id]];
      // the value with which items outside the boundary set below, are to be updated with
      $source_rl_difference = $this->lookup[$source][$this->cd] -
              $this->lookup[$source][$this->cg] + 1;
      // set the boundary - nodes having their "left"/"right" values outside this boundary will be affected by
      // the insert, and will need to be updated
      $source_boundary = $this->lookup[$source][$this->cg];
      // get target node's children (no deeper than the first level)
      $target_children = $this->get_descendants($target);
      // if copy is to be inserted in the default position (as the last of the target node's children)
      if ($position === false)
      // give a numerical value to the position
        $position = count($target_children);
      // if a custom position was specified
      else {
        // make sure given position is an integer value
        $position = (int) $position;
        // if position is a bogus number
        if ($position > count($target_children) || $position < 0)
        // use the default position (the last of the target node's children)
          $position = count($target_children);
      }
      // we are about to do an insert and some nodes need to be updated first
      // if target has no children nodes OR the copy is to be inserted as the target node's first child node
      if (empty($target_children) || $position == 0)
      // set the boundary - nodes having their "left"/"right" values outside this boundary will be affected by
      // the insert, and will need to be updated
      // if parent is not found (meaning that we're inserting a topmost node) set the boundary to 0
        $target_boundary = isset($this->lookup[$target]) ? $this->lookup[$target][$this->cg] : 0;
      // if target has children nodes and/or the copy needs to be inserted at a specific position
      else {
        // find the target's child node that currently exists at the position where the new node needs to be inserted to
        $slice = array_slice($target_children, $position - 1, 1);
        $target_children = array_shift($slice);
        // set the boundary - nodes having their "left"/"right" values outside this boundary will be affected by
        // the insert, and will need to be updated
        $target_boundary = $target_children[$this->cd];
      }
      // iterate through the nodes in the lookup array
      foreach ($this->lookup as $id => $properties) {
        // if the "left" value of node is outside the boundary
        if ($properties[$this->cg] > $target_boundary)
        // increment it
          $this->lookup[$id][$this->cg] += $source_rl_difference;
        // if the "right" value of node is outside the boundary
        if ($properties[$this->cd] > $target_boundary)
        // increment it
          $this->lookup[$id][$this->cd] += $source_rl_difference;
      }
      // lock table to prevent other sessions from modifying the data and thus preserving data integrity
      parent::lockTable($this->tbl);

      // update the nodes in the database having their "left"/"right" values outside the boundary
      $this->updateBorne($this->cg, $source_rl_difference, $target_boundary);
      $this->updateBorne($this->cd, $source_rl_difference, $target_boundary);

      // finally, the nodes that are to be inserted need to have their "left" and "right" values updated
      $shift = $target_boundary - $source_boundary + 1;
      // iterate through the nodes that are to be inserted
      foreach ($sources as $id => &$properties) {
        // update "left" value
        $properties[$this->cg] += $shift;
        // update "right" value
        $properties[$this->cd] += $shift;
        $node = $this->insertNode($properties);
        $node_id = $node['id'];

        // because the node may have children nodes and its ID just changed
        // we need to find its children and update the reference to the parent ID
        foreach ($sources as $key => $value) {
          if ($value[$this->col_parent] == $properties[$this->col_id]) {
            $sources[$key][$this->col_parent] = $node_id;
          }
        }
        // update the node's properties with the ID
        $properties[$this->col_id] = $node_id;
        // update the array of inserted items
        $sources[$id] = $properties;
      }
      // a reference of a $properties and the last array element remain even after the foreach loop
      // we have to destroy it
      unset($properties);
      // release table lock
      parent::unlockTables();
      // at this point, we have the nodes in the database but we need to also update the lookup array
      $parents = [];
      // iterate through the inserted nodes
      foreach ($sources as $id => $properties) {
        // if the node has any parents
        if (count($parents) > 0)
        // iterate through the array of parent nodes
          while ($parents[count($parents) - 1]['right'] < $properties[$this->cd])
          // and remove those which are not parents of the current node
            array_pop($parents);
        // if there are any parents left
        if (count($parents) > 0)
        // the last node in the $parents array is the current node's parent
          $properties[$this->col_parent] = $parents[count($parents) - 1]['id'];
        // update the lookup array
        $this->lookup[$properties[$this->col_id]] = $properties;
        // add current node to the stack
        $parents[] = array(
            'id' => $properties[$this->col_id],
            'right' => $properties[$this->cd]
        );
      }
      // reorder the lookup array
      $this->_reorder_lookup_array();
      // return the ID of the copy
      return $sources[0][$this->col_id];
    }
    // if scripts gets this far, return false as something must've went wrong
    return false;
  }

  private function updateBorne($borne = 'cd', $source_rl_difference, $target_boundary, $aom = '+', $compare = '>') {
    $sql = 'UPDATE  `' . $this->tbl . '`
              SET  `' . $borne . '` = `' . $borne . '` ' . $aom . ' ' . $source_rl_difference . '
            WHERE  `' . $borne . '` ' . $compare . ' ' . $target_boundary . ';';
    parent::db_exec($sql);
  }

  private function insertNode($properties = []) {
    $sql = 'INSERT INTO `' . $this->tbl . '`(`' . $this->col_uid . '`,`' . $this->col_title . '`,`' . $this->cg . '`, `' . $this->cd . '`, `' . $this->col_parent . '`) VALUES (:uid,:title,:cg,:cd,:parent)';
    $uid = uniqid('ai', false);
    $datas = array(':uid' => $uid,
        ':title' => $properties[$this->col_title],
        ':cg' => $properties[$this->cg],
        ':cd' => $properties[$this->cd],
        ':parent' => $properties[$this->col_parent]
    );

    return array('id' => parent::db_insert($sql, $datas, TRUE), 'uid' => $uid);
  }

  /**
   * Deletes a node, including the node's descendant nodes.
   */
  public function delete($node = 0) {
    $this->_init();
    if (isset($this->lookup[$node])) {
      // get target node's descendant nodes (if any)
      $descendants = $this->get_descendants($node, false);
      // iterate through target node's descendant nodes
      foreach ($descendants as $descendant) {
        // remove node from the lookup array
        unset($this->lookup[$descendant[$this->col_id]]);
      }
      //lock table
      parent::lockTable($this->tbl);

      //Delete nodes from the database
      $sql = "DELETE  FROM " . $this->tbl . "
                WHERE " . $this->cg . ' >= ' . $this->lookup[$node][$this->cg] . "
                AND " . $this->cd . " <= " . $this->lookup[$node][$this->cd];
      parent::db_exec($sql);

      // the value with which items outside the boundary set below, are to be updated with
      $target_rl_difference = $this->lookup[$node][$this->cd] - $this->lookup[$node][$this->cg] + 1;

      // set the boundary - nodes having their "left"/"right" values outside this boundary will be affected by
      // the insert, and will need to be updated
      $boundary = $this->lookup[$node][$this->cg];
      // remove the target node from the lookup array
      unset($this->lookup[$node]);
      // iterate through nodes in the lookup array
      foreach ($this->lookup as $id => $properties) {
        // if the "left" value of node is outside the boundary
        if ($this->lookup[$id][$this->cg] > $boundary) {
          // decrement it
          $this->lookup[$id][$this->cg] -= $target_rl_difference;
        }
        // if the "right" value of node is outside the boundary
        if ($this->lookup[$id][$this->cd] > $boundary) {
          // decrement it
          $this->lookup[$id][$this->cd] -= $target_rl_difference;
        }
      }

      // update the nodes in the database having their "left"/"right" values outside the boundary
      $this->updateBorne($this->cg, $target_rl_difference, $boundary, '-');
      $this->updateBorne($this->cd, $target_rl_difference, $boundary, '-');

      // release table lock
      parent::unLockTables();

      // return true as everything went well
      return true;
    }

    return false;
  }

  /**
   *  Returns an unidimensional (flat) array with the descendant nodes of a given parent node.
   */
  public function get_descendants($node = 0, $direct_descendants_only = true) {
    $this->_init();
    if (isset($this->lookup[$node]) || $node === 0) {
      $descendants = [];
      $keys = array_keys($this->lookup);
      foreach ($keys as $item) {
        if ($this->lookup[$item][$this->cg] > ($node !== 0 ? $this->lookup[$node][$this->cg] : 0) &&
                $this->lookup[$item][$this->cg] < ($node !== 0 ? $this->lookup[$node][$this->cd] : PHP_INT_MAX) &&
                (!$direct_descendants_only || $this->lookup[$item][$this->col_parent] == $node)
        ) {
          $descendants[$this->lookup[$item][$this->col_id]] = $this->lookup[$item];
        }
      }
      return $descendants;
    }
    return false;
  }

  /**
   * Returns the number of descendant nodes of a given node.
   */
  public function get_descendant_count($node = 0, $direct_descendants_only = true) {
    $this->_init();
    if (isset($this->lookup[$node])) {
      if (!$direct_descendants_only) {
        return ($this->lookup[$node][$this->cd] - $this->lookup[$node][$this->cg] - 1) / 2;
      } else {
        $result = 0;
        foreach ($this->lookup as $id => $properties) {
          if ($this->lookup[$id][$this->col_parent] == $node) {
            $result++;
          }
        }
        return $result;
      }
    }
    // if script gets this far, return false as something must've went wrong
    return false;
  }

  /**
   * Returns the next sibling of a node.
   */
  public function get_next_sibling($node = 0) {
    if ($siblings = $this->get_siblings($node, true)) {
      $node_position = array_search($node, array_keys($siblings));
      $sibling = array_slice($siblings, $node_position + 1, 1);
      return !empty($sibling) ? array_pop($sibling) : 0;
    }
    return false;
  }

  /**
   *  Returns an array containing a node's direct parent node if the node has a parent node, or "0" if the node is a
   *  topmost node.
   */
  public function get_parent($node = 0) {
    $this->_init();
    if (isset($this->lookup[$node])) {
      return isset($this->lookup[$this->lookup[$node][$this->col_parent]]) ? $this->lookup[$this->lookup[$node][$this->col_parent]] : 0;
    }
    return false;
  }

  /**
   * Returns an unidimensional (flat) array with the path to the given node (including the node itself).
   */
  public function get_path($node = 0) {
    $this->_init();
    $parents = [];
    // if node exists in the lookup array
    if (isset($this->lookup[$node])) {
      foreach ($this->lookup as $id => $properties) {
        if ($properties[$this->cg] <= $this->lookup[$node][$this->cg] && $properties[$this->cd] >= $this->lookup[$node][$this->cd]) {
          $parents[$properties[$this->col_id]] = $properties;
        }
      }
      // add also the node given as argument
      $parents[$node] = $this->lookup[$node];
    }
    // return the path to the node
    return $parents;
  }

  /**
   *  Returns the previous sibling of a node.
   */
  public function get_previous_sibling($node = 0) {
    if ($siblings = $this->get_siblings($node, true)) {
      $node_position = array_search($node, array_keys($siblings));
      $sibling = $node_position > 0 ? array_slice($siblings, $node_position - 1, 1) : [];
      return !empty($sibling) ? array_pop($sibling) : 0;
    }
    return false;
  }

  /**
   * Returns an array with a node's sibling nodes
   */
  public function get_siblings($node, $include_self = false) {
    if (isset($this->lookup[$node])) {
      $properties = $this->lookup[$node];
      $siblings = $this->get_descendants($properties['parent']);
      if (!$include_self)
        unset($siblings[$node]);
      return $siblings;
    }
    return false;
  }

  /**
   *  Returns a multidimensional array with all the descendant nodes (including children nodes of children nodes of
   *  children nodes and so on) of a given node.
   *  @return array
   */
  public function get_tree($node = 0) {
    $descendants = $this->get_descendants($node);
    foreach ($descendants as $id => $properties) {
      $descendants[$id]['children'] = $this->get_tree($id);
    }
    return $descendants;
  }

  /**
   * move node with descendants
   */
  public function move($source, $target, $position = false) {
    $this->_init();
    // continue only if
    if (
    // source node exists in the lookup array AND
            isset($this->lookup[$source]) &&
            // target node exists in the lookup array OR is 0 (indicating a topmost node)
            (isset($this->lookup[$target]) || $target == 0) &&
            // target node is not a child node of the source node (that would cause infinite loop)
            !in_array($target, array_keys($this->get_descendants($source, false)))
    ) {
      // if we have to move the node after/before another node
      if ($position === 'after' || $position === 'before') {
        // get the target's parent node
        $target_parent = $target == 0 ? 0 : $this->lookup[$target]['parent'];
        // get the target's parent's descendant nodes
        $descendants = $this->get_descendants($target_parent);
        // get the target's position among the descendants
        $keys = array_keys($descendants);
        $target_position = array_search($target, $keys);
        // move the source node to the desired position
        if ($position == 'after')
          return $this->move($source, $target_parent, $target_position + 1);
        else
          return $this->move($source, $target_parent, $target_position == 0 ? 0 : $target_position - 1);
      }
      // the source's parent node's ID becomes the target node's ID
      $this->lookup[$source][$this->col_parent] = $target;
      // get source node's descendant nodes (if any)
      $source_descendants = $this->get_descendants($source, false);
      // this array will hold the nodes we need to move
      // by default we add the source node to it
      $sources = array($this->lookup[$source]);
      // iterate through source node's descendants
      foreach ($source_descendants as $descendant) {
        // save them for later use
        $sources[] = $this->lookup[$descendant[$this->col_id]];
        // for now, remove them from the lookup array
        unset($this->lookup[$descendant[$this->col_id]]);
      }
      // the value with which nodes outside the boundary set below, are to be updated with
      $source_rl_difference = $this->lookup[$source][$this->cd] -
              $this->lookup[$source][$this->cg] + 1;
      // set the boundary - nodes having their "left"/"right" values outside this boundary will be affected by
      // the insert, and will need to be updated
      $source_boundary = $this->lookup[$source][$this->cg];
      // lock table to prevent other sessions from modifying the data and thus preserving data integrity
      parent::lockTable($this->tbl);
      // we'll multiply the "left" and "right" values of the nodes we're about to move with "-1", in order to
      // prevent the values being changed further in the script

      $sql = 'UPDATE `' . $this->tbl . '`
                   SET `' . $this->cg . '` = `' . $this->cg . '` * -1,
                       `' . $this->cd . '` = `' . $this->cd . '` * -1
                WHERE  `' . $this->cg . '` >= ' . $this->lookup[$source][$this->cg] . '
                  AND  `' . $this->cd . '` <= ' . $this->lookup[$source][$this->cd] . '';
      parent::db_exec($sql);

      // remove the source node from the list
      unset($this->lookup[$source]);
      // iterate through the remaining nodes in the lookup array
      foreach ($this->lookup as $id => $properties) {
        // if the "left" value of node is outside the boundary
        if ($this->lookup[$id][$this->cg] > $source_boundary)
        // decrement it
          $this->lookup[$id][$this->cg] -= $source_rl_difference;
        // if the "right" value of item is outside the boundary
        if ($this->lookup[$id][$this->cd] > $source_boundary)
        // decrement it
          $this->lookup[$id][$this->cd] -= $source_rl_difference;
      }
      // update the nodes in the database having their "left"/"right" values outside the boundary
      $this->updateBorne($this->cg, $source_rl_difference, $source_boundary, '-');
      $this->updateBorne($this->cd, $source_rl_difference, $source_boundary, '-');

      // get descendant nodes of target node (first level only)
      $target_descendants = $this->get_descendants((int) $target);
      // if node is to be inserted in the default position (as the last of target node's children nodes)
      // give a numerical value to the position
      if ($position === false)
        $position = count($target_descendants);
      // if a custom position was specified
      else {
        // make sure given position is an integer value
        $position = (int) $position;
        // if position is a bogus number
        if ($position > count($target_descendants) || $position < 0)
        // use the default position (as the last of the target node's children)
          $position = count($target_descendants);
      }
      // because of the insert, some nodes need to have their "left" and/or "right" values adjusted
      // if target node has no descendant nodes OR the node is to be inserted as the target node's first child node
      if (empty($target_descendants) || $position == 0)
      // set the boundary - nodes having their "left"/"right" values outside this boundary will be affected by
      // the insert, and will need to be updated
      // if parent is not found (meaning that we're inserting a topmost node) set the boundary to 0
        $target_boundary = isset($this->lookup[$target]) ? $this->lookup[$target][$this->cg] : 0;
      // if target has any descendant nodes and/or the node needs to be inserted at a specific position
      else {
        // find the target's child node that currently exists at the position where the new node needs to be inserted to
        $slice = array_slice($target_descendants, $position - 1, 1);
        $target_descendants = array_shift($slice);
        // set the boundary - nodes having their "left"/"right" values outside this boundary will be affected by
        // the insert, and will need to be updated
        $target_boundary = $target_descendants[$this->cd];
      }
      // iterate through the records in the lookup array
      foreach ($this->lookup as $id => $properties) {
        // if the "left" value of node is outside the boundary
        if ($properties[$this->cg] > $target_boundary)
        // increment it
          $this->lookup[$id][$this->cg] += $source_rl_difference;
        // if the "left" value of node is outside the boundary
        if ($properties[$this->cd] > $target_boundary)
        // increment it
          $this->lookup[$id][$this->cd] += $source_rl_difference;
      }
      // update the nodes in the database having their "left"/"right" values outside the boundary
      $this->updateBorne($this->cg, $source_rl_difference, $target_boundary, '+');
      $this->updateBorne($this->cd, $source_rl_difference, $target_boundary, '+');

      // finally, the nodes that are to be inserted need to have their "left" and "right" values updated
      $shift = $target_boundary - $source_boundary + 1;
      // iterate through the nodes to be inserted
      foreach ($sources as $properties) {
        // update "left" value
        $properties[$this->cg] += $shift;
        // update "right" value
        $properties[$this->cd] += $shift;
        // add the item to our lookup array
        $this->lookup[$properties[$this->col_id]] = $properties;
      }
      // also update the entries in the database
      // (notice that we're subtracting rather than adding and that finally we multiply by -1 so that the values
      // turn positive again)
      $sql = 'UPDATE `' . $this->tbl . '`
                   SET `' . $this->cg . '` = (`' . $this->cg . '` - ' . $shift . ') * -1,
                       `' . $this->cd . '` = (`' . $this->cd . '` - ' . $shift . ') * -1
                WHERE  `' . $this->cg . '` < 0';
      parent::db_exec($sql);

      //Update the parent of the source node
      $sql = 'UPDATE `' . $this->tbl . '`
                   SET `' . $this->col_parent . '` = ' . $target . '
                 WHERE `' . $this->col_id . '` = ' . $source . '';
      parent::db_exec($sql);


      // release table lock
      parent::unlockTables();
      // reorder the lookup array
      $this->_reorder_lookup_array();
      // return true as everything went well
      return true;
    }
    // if scripts gets this far, return false as something must've went wrong
    return false;
  }

  /**
   * create html ul/li list
   * @return (string)  liste UL/LI
   */
  public function to_list($node, $list_type = 'ul', $attributes = '') {
    if (!is_array($node))
      $node = $this->get_tree($node);

    if (!empty($node)) {
      // start generating the output
      $out = '<' . $list_type . ($attributes != '' ? ' ' . $attributes : '') . '>';
      // iterate through each node
      foreach ($node as $elem) {
        // generate output and if the node has children nodes, call this method recursively
        $nodeType = !empty($elem['type']) ? $elem['type'] : 'default';
        $out .= '<li class="arbre_item arbre_item_' . $elem[$this->col_id] . ' $nodeType"  data-jstree=\'{"opened":true,"type":"' . $nodeType . '"}\' >'
                . '<a data-uid="' . $elem[$this->col_uid] . '" class="node-item">' . $elem[$this->col_title] . '</a>' . (is_array($elem['children']) ? $this->to_list($elem['children'], $list_type) : '') .
                '</li>';
      }
      // return generated output
      return $out . '</' . $list_type . '>';
    }
  }

  /**
   *
   * @param type $node
   * @param type $parent
   * @param type $attributes
   * @return type
   */
  public function to_table($node = 0, $parent = NULL, $attributes = '') {
    if (!is_array($node))
      $node = $this->get_tree($node);
    $out = "";
    if (!empty($node)) {

      foreach ($node as $elem) {
        // generate output and if the node has children nodes, call this method recursively
        $parent = !empty($elem[$this->col_parent]) ? 'treegrid-parent-' . $elem[$this->col_parent] : '';
        $out .= '<tr class="treegrid_' . $elem[$this->col_uid] . ' ' . $parent . '" data-tt-id="' . $elem[$this->col_id] . '"  >'
                . '<td data-uid="' . $elem[$this->col_uid] . '" class="node-item">' . $elem[$this->col_title] . '</td>' .
                '</tr>';
        $out .= (is_array($elem['children']) ? $this->to_table($elem['children'], $elem[$this->col_id]) : '');
      }
      // return generated output
      return $out;
    }
  }

  /**
   * Mise à jour Node Title
   */
  public function update($node = 0, $title = '') {
    $this->_init();
    // continue only if target node exists in the lookup array
    if (isset($this->lookup[$node])) {
      parent::lockTable($this->tbl);

      $sql = "UPDATE " . $this->tbl . "
                SET " . $this->col_title . " = :title
                WHERE " . $this->col_id . " = :node ";
      $datas = array(':title' => $title, ':node' => $node);
      parent::db_exec($sql, $datas);

      parent::unLockTables();

      $this->lookup[$node][$this->col_title] = $title;
      return true;
    }
    return false;
  }

//- FIN DE LA CLASS - //
}
