<?php

/**
 * @file
 * This plugin generates a search result based on a new materials node. It's
 * mainly used to override the default page for this node type (node/%node).
 */
$plugin = array(
    'title' => t('Ting new materials covers list'),
    'description' => t('Show a list of new materials covers'),
    'single' => TRUE,
    'edit form' => 'ting_new_materials_covers_content_type_edit_form',
    'render callback' => 'ting_new_materials_covers_content_type_render',
    'category' => t('Ting'),
    'required context' => array(new ctools_context_required(t('New Materials'), 'node')),
    'render last' => TRUE,
);

/**
 * Render the ting new materials results results amount block.
 */
function ting_new_materials_covers_content_type_render($subtype, $conf, $panel_args, $context) {
  // Define the return block.
  $block = new stdClass();

  // Check if context exists.
  if (isset($context[0]->data) && $context[0]->data->type == 'new_materials' ) {
    // Get the node.
    $node = $context[0]->data;
    if (ting_new_materials_no_search_query($node)) {
      return $block;
    }
    $cached_results = array();
    if (false && $cache = ting_new_materials_check_cache($node)) {
      $cached_results = $cache->data;
    } else {
      $pager_page = pager_find_page();
      $number_of_objects = ting_new_materials_number_of_objects($node);
      $num_per_page = $number_of_objects * 2;

       $query = ting_new_materials_build_search_query($node);

      // Search the data well with the query and pager page.
      $search_result = ting_new_materials_do_search($query, $pager_page, $num_per_page);
file_put_contents("/var/www/drupalvm/drupal/debug/newmat8.txt", print_r($search_result, TRUE), FILE_APPEND);
      // Check if the search return any results. 
      if ($search_result->numTotalObjects > 0) {
        $covers = ting_covers_get(array_keys($search_result->collections));

        $number_found = 0;
        foreach ($covers as $id => $cover) {
          // If there is a image
          if (isset($cover)) {
            $object = new CoverObject();
            //$entity = ding_entity_load($id, 'ting_object');
            //$object->title = $entity->getTitle();;
            //$object->creators = $entity->getCreators()[0];
            $object->id = $id;
            $object->cover = $cover;
            // Get link to link the result back to the collection.
            $uri = entity_uri('ting_collection', $search_result->collections[$id]);
            $object->uri = $uri['path'];
            $cached_results[] = $object;

            $number_found++;
            if ($number_found >= $number_of_objects) {
              break;
            }
          }
        }
        ting_new_materials_set_cached_lists($query, 'cover15', $cached_results);
      }
    }
    //file_put_contents("/var/www/drupalvm/drupal/debug/newmat4.txt", print_r($cached_results, TRUE), FILE_APPEND);
    $results = ting_new_materials_render_cover($cached_results);
    // Set default page result.
    $content = array(
        '#theme' => 'ting_new_materials',
        '#title' => 'Nyheder',
        '#availability_legend' => NULL,
        '#legend' => isset($legend) ? $legend : NULL,
    );

    // If any search result have been found add them to the array.
    if (!is_null($results)) {
      $content['#results'] = array(
          '#theme' => 'covers_search_results',
          '#results' => $results,
      );
    }
    drupal_add_js(drupal_get_path('module', 'ting_covers') . '/js/ting-covers.js');
    // Set block content.
    $block->content = $content;
  }

  return $block;
}

function ting_new_materials_no_search_query($node) {
  $well_query = field_get_items('node', $node, 'field_ting_new_materials_search');
  return (empty($well_query[0]['value']));
}

function ting_new_materials_render_cover($cached_results) {
  $results = array();
  foreach ($cached_results as $object) {
    // Add the result to an array for the "ting_search_results" theme
    // function.      
    $image = theme('ting_object_cover', array('elements' => array('#object' => $object, '#image_style' => 'ting_new_materials_cover')));
    $options = array(
        'html' => TRUE,
        'attributes' => array('title' => $object->collectionTitle),
    );
    $results[] = array(
        'snippet' => l($image, $object->uri, $options)
    );
  }
  return $results;
}

function ting_new_materials_number_of_objects($node) {
  $number_of_objects = 16;
  $items = field_get_items('node', $node, 'field-ting-new-materials-items');
  if (isset($items) && is_array($items) && isset($items[0]['value'])) {
    $number_of_objects= $items[0]['value'];
  }
  return $number_of_objects;
}

/**
 * Enable admin settings page.
 */
function ting_new_materials_covers_content_type_edit_form($form, &$form_state) {
  return $form;
}

/**
 * Check cache
 */
function ting_new_materials_check_cache($node) {
  $alma_query = field_get_items('node', $node, 'field_ddelibra_new_search');
  if (!empty($alma_query[0]['value'])) {
    $query = $alma_query[0]['value'];
  } else {
    $query = ting_new_materials_build_search_query($node);
  }
  return ting_new_materials_get_cached_lists($query, 'cover15');
  ;
}

/**
 * Gets a cached block of inpiration list in desired format.
 */
function ting_new_materials_get_cached_lists($query, $format) {
  if ($cache = cache_get(ting_new_materials_make_cache_string($query, $format))) {
    return $cache;
  } else {
    return null;
  }
}

/**
 * Sets a cached block of inpiration list in desired format.
 */
function ting_new_materials_set_cached_lists($query, $format, $block) {
  $cid = ting_new_materials_make_cache_string($query, $format);
  // Save cache for a week.
  cache_set($cid, $block, 'cache', time() + 604800);
}

/**
 * Helper function to get CID.
 */
function ting_new_materials_make_cache_string($query, $format) {
  return md5('ting_new_materials' . $query . $format);
}
