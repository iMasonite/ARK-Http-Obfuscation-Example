# ARK - HTTP Obfuscation Example
### Purpose:
A basic example Mod & PHP REST Endpoint for HTTP requests in the ARK Development Kit

HTTP in the ARK development Kit has no support for SSL or secure hashing, so the only thing available (to my limited knowledge) is obfuscation. This example demonstrates how JSON string obfuscation can be achieved and utilised in the ARK Development Kit.

  - This is not to be considered *"secure"*, only more difficult to decipherer.
  - This should not be used to pass highly sensitive information across an insecure connection.
  - This is my first go at this, issues found and raised would be appreciated.

## Requires:
  - The ARK Development Kit *(ADK)*.
  - A web server running PHP 7 or higher.
  - A basic understanding of both.
 
### Features : Ark Mod.
  - Random string generator to randomise the obfuscation via script command.
  - Simple pure functions to obfuscate/deobfuscate.
  - Multi roll support for multiple levels of obfuscation.
  - An out of the box simple example mod ready to test and utilise inside the ADK.

### Default Character Set.
The `String Obfuscation Default Character Set` is a fixed string of characters used as the base for obfuscating and deobfuscating. I have chosen to omit common container, white space and escape characters from the default set although this can be added in your own usage. 
I have had experience with incorrect JSON decodes when obfuscating white space escape characters and other container characters such as "{} ()\/" etc.

![N|Solid](https://i.imgur.com/OsSGPMV.png)
```txt
!#$%&*+,-.0123456789:;=?@ABCDEFGHIJKLMNOPQRSTUVWXYZ^_`abcdefghijklmnopqrstuvwxyz|~¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶·¸¹º»¼½¾¿ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþ
```

### Usage: ADK Random Character Set Generation.
To get a random character set for use in obfuscation, the node `String Obfuscation Generate Random Character Set` can be used. This function takes the Default Character Set and randomly shuffles the characters positions.
![N|Solid](https://i.imgur.com/obL45AS.png)
| Parameter | Description |
| ------ | ------ |
| `boolean` Print To Server Game Log | Will print the generated string to the server game log wrapped in double quotes |

The Output string will go in your `GameUserSettings.ini` under the configuration section `HTTPObfuscation` with the option value `ObfuscationCharacterSet` including the double quotes. The section and option names can be changed to suit your needs in the function library.

###### Example `GameUserSettings.ini` Configuration:
```txt
[HTTPObfuscation]
ObfuscationCharacterSet="BÕ¨MÐZÊ?`Í¶7UápÖÅç+Âü®¿ñ¬ÏiJRCsPjA¢|eÇ.&ªdêÓ;8Üâ!·9Ûõ­l³É¤q¥ÙÝn±×¾IûÌÁ»uë¯GO#0Ä¡Àß¹4ÚÒ«ºoS$v¸óöDµfô1%32íùï~zÆòh5æ÷QYWØ¼xNkèFéLbäðÎgà²V*a¦Tå½HãtÈ§þ-mrÔ6=Ã©y_Ë,£cøX´ÑEìú:î°@ý^wKÞ"
```

### Usage: ADK Obfuscation
To obfuscate a string in the ADK, connect a string value to the the `String Obsfucation` node `Source String`, set the Boolean parameter `Obfuscate` to `true`, set how many rolls you wish to be preformed. The result string will be obfuscated. Conversely, setting `Obfuscate` to `false` will deobfuscate a given `Source String`.

![N|Solid](https://i.imgur.com/U1uIuKk.png)

| Parameter | Description |
| ------ | ------ |
| `string` Source String | The String to operate on. |
| `boolean` Obfuscate | `true` = Obfuscation, `false` = Deobfuscation. |
| `integer` Number Of Rolls | How many times the string will be obfuscated. MAX = 3 |
> keep in mind, the number of rolls **must match** the obfuscation when deobfuscating.

###### Example `Source String` with no obfuscation:
```json
{
    "token": "_?$gac&y^NuRNFEm2kO4p66rq*xkf5QM9DzbqL0=7q1FyiV5A1??Pov24yff-ZaL",
    "random_uuid": "2f6fc31e-f069-4aa1-9d07-f3f246541768",
    "random_int": 2147483647,
    "random_vector": [ 1.2653000354766846, 8748.3642578125, 99.258697509765625 ],
    "random_color":
    {
        "b": 0,
        "g": 250,
        "r": 255,
        "a": 255
    }
}
```
The token is just a random string of characters generated using a [Password Generator][pwsg]
Other variables are just random ones for the example.

###### Example `Source String` with obfuscation:
```json
{
    "hßÞÌB"­ "Rìèò5÷vËnMã®M+¬W¯ÞªJ¢ÙÙÜL#»Þølp³*;¦.LoÆ¿²LÓ+Ë!älmÓììußP¯JËøø3Õ5o"ô
    "Ü5BÁßWRãã!Á"­ "¯øÙø÷ñÓÌ3øÆÙ*3J55Ó3*ÁÆ²3øñø¯JÙlJÓ²Ù2"ô
    "Ü5BÁßWR!Bh"­ ¯ÓJ²J2ñÙJ²ô
    "Ü5BÁßWRPÌ÷hßÜ"­ [ ÓÇ¯ÙlñÆÆÆñlJ²ÙÙ2JÙô 2²J2ÇñÙJ¯l²2Ó¯lô **Ç¯l2Ù*²lÆ*²ÙlÙ¯l ]ô
    "Ü5BÁßWR÷ßÖßÜ"­
    {
        "."­ Æô
        "ò"­ ¯lÆô
        "Ü"­ ¯llô
        "5"­ ¯ll
    }
}
```

### Features : PHP test page.
  - An example of how to consume requests.
  - Mostly has comments documenting instructions.
  - `strtr_utf8`: utf8 support for the basic `strtr`.
  - A UUID V4 generator using `openssl_random_pseudo_bytes`

The PHP Test page has two defined values `DEFAULT_CHARACTER_SET` & `RANDOMISED_CHARACTER_SET`, these must correspond to the mod version.

| Query Parameters | Description | Example|
| ------ | ------ | ------ |
| `rolls integer` | Like with the String Obfuscation node in the mod library function the amount of rolls must match the obfuscated string being processed. Defaults to `1` if not specified. | `test_api.php?rolls=2`
| `plain boolean` | If set to `true` the response JSON object will not be obfuscated. defaults to `false` if not specified. | `test_api.php?plain=true` |
| `testsite` | Tests can be run using this option | `test_api.php?testsite` |

`plain` & `rolls` can be used together, example: `test_api.php?rolls=2&plain=true`

More comments and documentation available in `test_api.php`.

#### Note on Error:
```json
{
    "Result": "json_decode error: Syntax error"
}
```
##### This exception could be due to malformed json syntax before obfuscation and can also throw if there are too few or too many rolls on the obfuscation/deobfuscation operations. 


[pwsg]: <https://passwordsgenerator.net/>

