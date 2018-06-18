<?php

namespace Drupal\speech\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Manages autocompletion.
 *
 * @package Drupal\speech\Controller
 */
class AutocompleteController extends ControllerBase {

  /**
   * Returns response for the autocompletion.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object containing the search string.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions.
   */
  public function usersAutocomplete(Request $request) {
    $matches = [];

    // Gets the typed string from the URL, if it exists.
    if ($input = $request->query->get('q')) {
      // Generates the results based on the typed string.
      $query = \Drupal::database()->select('users_field_data', 'users');
      $query->addField('users', 'name');
      $query->condition('users.name', $query->escapeLike($input) . '%', 'LIKE');

      $results = $query->execute()->fetchAll();

      foreach ($results as $result) {
        $matches[] = [
          'value' => $result->name,
        ];
      }
    }

    return new JsonResponse($matches);
  }

}
