<?php

/*
 * This class represents a Samurai Transaction.
 * It can be used to query and capture/void/credit/reverse transactions.
 */
class Samurai_Transaction 
{
  private $knownAttributes = array( 'amount'
                                  , 'type'
                                  , 'payment_method_token'
                                  , 'currency_code'
                                  , 'descriptor'
                                  , 'custom'
                                  , 'customer_reference'
                                  , 'billing_reference'
                                  );
  
  /* -- Properties -- */

  private $isNew;
  public $attributes = array();
  private $connection;

  public $messages = array();
  public $errors = array();

  /* -- Class Methods -- */

  /*
   * Find the transaction, identified by `$referenceId`.
   * Note that the reference id of a transaction isn't the 
   * same as the transaction token. When trying to
   * fetch a transaction, always use its reference id.
   */
  public static function find($referenceId) {
    $transaction = new self(array('reference_id' => $referenceId));
    $transaction->refresh();
    return $transaction;
  }

  public function __construct($attributes = array()) {
    $this->attributes = array_merge($this->attributes, $attributes);
    $this->connection = Samurai_Connection::instance();
  }

  /* -- Methods -- */

  /*
   * Captures an authorization. Optionally specify an `$amount` to do a partial capture of the initial
   * authorization. The default is to capture the full amount of the authorization.
   */
  public function capture($amount, $options = array()) {
    $amount = isset($amount) ? $amount : $this->amount;
    $options = array_merge($options, array('amount' => $amount));
    list($error, $response) = $this->connection->post($this->pathFor('capture'), $options);
    return $this->handleResponse($error, $response);
  }

  /*
   * Void this transaction. If the transaction has not yet been captured and settled it can be voided to 
   * prevent any funds from transferring. Internally, `void` requests are 
   * mapped to the `reverse` action, so if a `void` isn't possible, 
   * a `credit` will be automatically performed.
   */
  public function void($options = array()) {
    list($error, $response) = $this->connection->post($this->pathFor('void'), $options);
    return $this->handleResponse($error, $response);
  }

  /*
   * Create a credit or refund against the original transaction.
   * Optionally accepts an `amount` to credit, the default is to credit the full
   * value of the original amount.
   * The `$amount` and `$options` parameters are optional. By the default,
   * the `$amount` is the full amount specified in the original
   * transaction. 
   * Internally, `credit` requests are mapped to the `reverse` action, so if a 
   * `credit` isn't possible, a `void` will be automatically performed.
   */
  public function credit($amount = null, $options = array()) {
    $amount = isset($amount) ? $amount : $this->amount;
    $options = array_merge($options, array('amount' => $amount));
    list($error, $response) = $this->connection->post($this->pathFor('credit'), $options);
    return $this->handleResponse($error, $response);
  }

  /*
   * Reverse this transaction.  First, tries a void.
   * If a void is unsuccessful, (because the transaction has already settled) 
   * perform a credit for the full amount.
   * The `amount` and `options` parameters are optional. By the default,
   * the `amount` is the full amount specified in the original
   * transaction.
   */
  public function reverse($amount = null, $options = array()) {
    $amount = isset($amount) ? $amount : $this->amount;
    $options = array_merge($options, array('amount' => $amount));
    list($error, $response) = $this->connection->post($this->pathFor('reverse'), $options);
    return $this->handleResponse($error, $response);
  }

  /*
   * Update this transaction's attributes with the ones fetched from 
   * Samurai's API.
   */
  public function refresh() {
    list($error, $response) = $this->connection->get($this->pathFor('show'));
    return $this->handleResponse($error, $response);
  }
  
  /* -- Helpers -- */

  public function isSuccess() {
    if (isset($this->attributes['processor_response'])
        && isset($this->attributes['processor_response']['success'])) {
        return $this->attributes['processor_response']['success'];
    } else {
      return false;
    }
  }

  public function isFailed() {
    return !$this->isSuccess();
  }

  /*
   * Parses the Samurai response for messages (info or error) and updates 
   * the current transaction's information. If an HTTP error is 
   * encountered, it will be thrown from this method.
   */
  public function handleResponse($error, $response) {
    if ($error) {
      $this->attributes['success'] = false;
      throw $error;
    } else {
      if (isset($response) && isset($response['transaction'])) {
        $this->updateAttributes($response['transaction']);
      }
    }

    if(!empty($response)) {
      $this->processResponseMessages($response);
    }
  }

  /*
   * Finds message blocks in the Samurai response, creates a `Samurai_Message`
   * object for each one and stores them in either the `messages` or the
   * `errors` internal array, depending on the message type.
   */
  private function processResponseMessages($response = array()) {
    $messages = self::extractMessagesFromResponse($response);
    $this->messages = array();
    $this->errors = array();
    
    foreach ($messages as $message) {
      $message['subclass'] = isset($message['subclass']) ? $message['subclass'] : $message['class'];
      $message['$t']       = isset($message['$t'])       ? $message['$t']       : null;
      $message['context']  = empty($message['context'])  ? 'system.general'     : $message['context'];

      $m = new Samurai_Message($message['subclass'], $message['context'], $message['key'], $message['$t']);

      if ($message['subclass'] === 'error') {
        if (isset($this->errors[$message['context']])) {
          $this->errors[$message['context']][] = $m;
        } else {
          $this->errors[$message['context']] = array($m);
        }
      } else {
        if (isset($this->messages[$message['context']])) {
          $this->messages[$message['context']][] = $m;
        } else {
          $this->messages[$message['context']] = array($m);
        }
      }
    }
  }

  /*
   * Finds all messages returned in a Samurai response, regardless of
   * what part of the response they were in.
   */
  private static function extractMessagesFromResponse($response = array()) {
    $messages = array();

    foreach ($response as $key => $value) {
      if ($key === 'messages' && is_array($value)) {
        $messages = array_merge($messages, $value);
      } elseif (is_array($value)) {
        $res = self::extractMessagesFromResponse($value);
        if(!empty($res)) {
          $messages = array_merge($messages, $res);
        }
      }
    }

    return $messages;
  }

  /*
   * Returns the API endpoint that should be used for `$method`.
   */
  private function pathFor($method) {
    $root = 'transactions';

    switch ($method) {
      case 'show':
        return $root . '/' . $this->attributes['reference_id'] . '.xml';
      default:
        return $root . '/' . $this->token . '/' . $method . '.xml';
    }
  }

  /*
   * Updates the internal `attributes` array with newly returned information.
   */
  public function updateAttributes($attributes = array()) {
    // sometimes the returned transaction would not have all of the
    // original transaction's data, so this makes sure we don't
    // overwrite data that we already have with blank values
    foreach ($attributes as $key => $value) {
      if ($value !== '' || !isset($this->attributes[$key])) {
        $this->attributes[$key] = $value;
      }
    }
  }

  /* -- Accessors -- */

  /*
   * Makes sure all properties of the internal `attributes` array can be 
   * accessed with their camelized versions from the outside. E.g.:
   *
   *   $transaction->attributes['amount'] === $transaction->amount;
   *
   */
  public function __get($prop) {
    switch($prop) {
      // First take care of property aliases
      case 'token':
        return $this->attributes['transaction_token'];
      default:
        $underscoredProp = Samurai_Helpers::underscore($prop);
        if (isset($this->attributes[$underscoredProp])) {
          return $this->attributes[$underscoredProp];
        } else {
          return null;
        }
    }
  }

  public function __set($prop, $value) {
    $underscoredProp = Samurai_Helpers::underscore($prop);
    if(in_array($underscoredProp, self::$knownAttributes)) { 
      $this->attributes[$underscoredProp] = $value;
    }
  }
}
