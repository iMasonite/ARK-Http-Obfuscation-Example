<?php
/**
 * This file provides a very basic example endpoint for the example HTTP Obfuscation function library for the ARK Development Kit
 *
 * @link       <https://github.com/iMasonite>
 * @license    http://opensource.org/licenses/gpl-license.php  GNU Public License
 * @author     iMasonite <https://github.com/iMasonite>
 */

// Request headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset:UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Static Value Definitions

// The default character set should match the hard coded character set in the mod's function `String Obfuscation Get Default Character Set`
define('DEFAULT_CHARACTER_SET', '!#$%&*+,-.0123456789:;=?@ABCDEFGHIJKLMNOPQRSTUVWXYZ^_`abcdefghijklmnopqrstuvwxyz|~¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶·¸¹º»¼½¾¿ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþ');

// A random character set generated using the mod's function `String Obfuscation Generate Random Character Set`
define('RANDOMISED_CHARACTER_SET', 'BÕ¨MÐZÊ?`Í¶7UápÖÅç+Âü®¿ñ¬ÏiJRCsPjA¢|eÇ.&ªdêÓ;8Üâ!·9Ûõ­l³É¤q¥ÙÝn±×¾IûÌÁ»uë¯GO#0Ä¡Àß¹4ÚÒ«ºoS$v¸óöDµfô1%32íùï~zÆòh5æ÷QYWØ¼xNkèFéLbäðÎgà²V*a¦Tå½HãtÈ§þ-mrÔ6=Ã©y_Ë,£cøX´ÑEìú:î°@ý^wKÞ');

// For checking Status: test_api.php?status
if (isset($_GET["status"])) {
  exit('{"Result": "Alive"}');
}

// Option: Reply should be in plain text: test_api.php?plain=true
if (isset($_GET["plain"])) {
  define('PLAIN_OUTPUT', filter_var($_GET["plain"], FILTER_VALIDATE_BOOLEAN));
  } else {
  define('PLAIN_OUTPUT', 0);
}

// Option: How many rolls of the obfuscation should be used: test_api.php?rolls=2 (ensure to match clamped values in the mod)
if (isset($_GET["rolls"])) {
  define('OBFUSCATION_ROLLS', ((int) $_GET["rolls"]) < 1 ? 1 : (int) $_GET["rolls"]);
  } else {
  define('OBFUSCATION_ROLLS', 1);
}

// A helper to test this page for errors directly: test_api.php?testsite
if (isset($_GET["testsite"])) {

  // Make Array
  $jsonData = array();

  // Add some information
  $randomUUID = UUIDv4(openssl_random_pseudo_bytes(16));
  $jsonData['RandomUUID']     = $randomUUID;
  $jsonData['ClientAddress']  = $_SERVER['REMOTE_ADDR'];
      
  // encode
  $testCase = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

  // obfuscate the test case
  $obfuscate = obfuscate($testCase);

  // deobfuscate the obfuscated test case
  $deobfuscated = deobfuscate($obfuscate);

  // decode the json string into an object
  $decoded = (array) json_decode_local($deobfuscated, JSON_THROW_ON_ERROR);

  // Make reply Arrays
  $replyData = array();

  // Set the correct data we used
  $replyData['Expected']['RandomUUID'] = $randomUUID;
  $replyData['Expected']['ClientAddress'] = $_SERVER['REMOTE_ADDR'];
 
  // set the obfuscated value so we know it was actually obfuscated in the test output
  $replyData['Obfuscated']['Rolls'] = OBFUSCATION_ROLLS;
  $replyData['Obfuscated']['EscapedData'] = preg_replace("/\s\s+/", "", $obfuscate);

  // set the values from the deobfuscated json object
  $replyData['Evaluated']['RandomUUID'] = $decoded['RandomUUID'];
  $replyData['Evaluated']['ClientAddress'] = $decoded['ClientAddress'];

  // Set status of the obfuscation by validating expected with evaluated values
  $replyData['Result'] = ($decoded['RandomUUID'] === $randomUUID && $decoded['ClientAddress'] === $_SERVER['REMOTE_ADDR']) ? "Succeeded": "Failed" ;
  
  // encode the reply array into a json string
  $replyJson = json_encode($replyData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
  
  // send it on its way
  echo $replyJson;
  exit(0);
}

// Body of the end point
try {
  
  // Get the json content
  $obfuscated = file_get_contents("php://input");
  
  // Deobfuscate the json content
  $deobfuscated = deobfuscate($obfuscated);
  
  // Decode the json content into an object
  $jsonData = (array) json_decode_local($deobfuscated);
  
  // Handle Data
  if (array_key_exists("random_uuid", $jsonData)) {
    
    // compare uuid that we sent to this page with one that we know it should be
    if ($jsonData['random_uuid'] === "2f6fc31e-f069-4aa1-9d07-f3f246541768") {
      
      // Add some information for a response
      $jsonData['ClientAddress']  = $_SERVER['REMOTE_ADDR'];
      $jsonData['Result']         = "Succeeded";
      
      // Encode the array into a json string
      $replyJson = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
      
      // if test_api.php?plain=true then just echo the plain text of the json, otherwise we'll reobfuscate it
      if (PLAIN_OUTPUT) {
        echo $replyJson;
      } else {
        
        // reobfuscate reply so it can be deobfuscated on the other end, round trip obfuscation..
        $reobfuscate = obfuscate($replyJson);

        echo $reobfuscate;
      }

    } else {
      replyWithError("UUID miss-match.");
    }

  } else {
    replyWithError("Missing Key in json object.");
  }

}
catch (Exception $e) {
  replyWithError($e->getMessage());
}


/**
 * Helper function: Obfuscate a string using the defined values RANDOMISED_CHARACTER_SET, DEFAULT_CHARACTER_SET & OBFUSCATION_ROLLS
 *
 * @param string $content — The string being obfuscated.
 * @return string Obfuscated content.
 */
function obfuscate(string $content) {
  $obfuscate = $content;
  for ($i = 0; $i < OBFUSCATION_ROLLS; $i++) {
    $obfuscate = strtr_utf8($obfuscate, RANDOMISED_CHARACTER_SET, DEFAULT_CHARACTER_SET);
  }
  return $obfuscate;
}

/**
 * Helper function: Deobfuscate a string using the defined values RANDOMISED_CHARACTER_SET, DEFAULT_CHARACTER_SET & OBFUSCATION_ROLLS
 *
 * @param string $content — The string being deobfuscated.
 * @return string Deobfuscated content.
 */
function deobfuscate(string $content) {
  $deobfuscate = $content;
  for ($i = 0; $i < OBFUSCATION_ROLLS; $i++) {
    $deobfuscate = strtr_utf8($deobfuscate, DEFAULT_CHARACTER_SET, RANDOMISED_CHARACTER_SET);
  }
  return $deobfuscate;
}


/**
 * Allow strtr to support utf8
 *
 * @param string $str — The string being translated.
 * @param string $from — The string replacing from.
 * @param string $to — The string being translated to to.
 * @return string This function returns a copy of str, translating all occurrences of each character in $from to the corresponding character in $to.
 */
function strtr_utf8(string $str, string $from, string $to) {
  $keys   = array();
  $values = array();
  preg_match_all('/./u', $from, $keys);
  preg_match_all('/./u', $to, $values);
  $mapping = array_combine($keys[0], $values[0]);
  return strtr($str, $mapping);
} 

// UUID V4 -- Used in testing and useful to keep around
function UUIDv4($data = null) {
  assert(strlen($data) == 16);

  $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
  $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

  return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// Actually have something to say on error
function json_decode_local($json, $assoc = false, $depth = 512, $options = 0) {
  $data = \json_decode($json, $assoc, $depth, $options);
  if (JSON_ERROR_NONE !== json_last_error()) {
      throw new \InvalidArgumentException( 'json_decode error: ' . json_last_error_msg() );
  }
  return $data;
}

// Helper function...
function replyWithError(string $message = "Unknown Exception") {
  $errorReply           = array();
  $errorReply['Result'] = $message;
  $replyJson            = json_encode($errorReply, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
  exit($replyJson);
}

