<?php
/**
 * Created by PhpStorm.
 * User: research
 * Last Edit Date: 2021-04-14
 * Time: 00:00
 */

namespace Util;


class Util
{
    const HTTP_STARTS = ["http://", "https://"];

    const REG_EXP_DELIMITER = '~';
    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.
    /*
     * 2017-03-06
     * @param string $string : a string
     * @param array $filters : an array of strings intended to represent "filters".
     * If a string in $filters starts and ends with REG_EXP_DELIMITER it'll be treated as a REGULAR EXPRESSION
     * If a string $f in $filters is not considered a REGULAR EXPRESSION, than $string will match $f if it has it somewhere, case INSENSITIVE
     */
    public static function auxCheckIfStringMatchesSomeFilter(
        $string,
        $filters
    ){
        $c0 = is_string($string);
        $c1 = $c0 && is_array($filters);

        if ($c1){
            foreach ($filters as $f){
                //check if filter is a supported regular expression
                $firstChar = substr($f, 0, 1);
                $lastChar = substr($f, strlen($f)-1);
                $firstAndLastCharsAreTheSame = $firstChar===$lastChar;
                $firstCharIsTilde = $firstChar===Util::REG_EXP_DELIMITER;
                $probablyRegularExpression = $firstAndLastCharsAreTheSame && $firstCharIsTilde;
                if ($probablyRegularExpression) {
                    //suspect regular expression
                    $stringMatchesFilterAsRegularExpression = preg_match($f, $string) === 1;
                }
                else{
                    //not a supported regular expression
                    $stringMatchesFilterAsRegularExpression = false;
                }

                $stringHasFilterHasItsSubstring = stripos($string, $f)!==false;

                $stringMatchesCurrentFilter = $stringMatchesFilterAsRegularExpression || $stringHasFilterHasItsSubstring;

                if ($stringMatchesCurrentFilter){
                    return true;
                }
            }//for each filter
        }//if there are working conditions

        return false;
    }//auxCheckIfStringMatchesSomeFilter



    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.
    /*
     * 2017-03-06
     * receives
     * @param array $strings
     * @param array $inclusiveFilters
     * returns array of strings, composed by the elements in $strings that matched 1+ filter in $inclusiveFilters
     */
    public static function auxFilterInStrings(
        $strings,
        $inclusiveFilters
    ){
        $c0 = is_array($strings) && count($strings)>0;
        $c1 = $c0 && is_array($inclusiveFilters);
        $ret = array();

        if ($c1){
            foreach ($strings as $s){
                $match = Util::auxCheckIfStringMatchesSomeFilter($s, $inclusiveFilters);
                if ($match!==false){
                    $ret[] = $s;
                }
            }//for each string

            return $ret;
        }//if there are working conditions
        else{
            return false;
        }//no working conditions
    }//auxFilterInStrings

    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.
    /*
     * 2017-03-06
     * receives
     * @param array $strings
     * @param array $excludingFilters
     * returns array of strings, composed by the elements in $strings that DID NOT MATCH 1+ filter in $excludingFilters
     */
    public static function auxFilterOutStrings(
        $strings,
        $excludingFilters
    ){
        $c0 = is_array($strings) && count($strings)>0;
        $c1 = $c0 && is_array($excludingFilters);
        $ret = array();

        if ($c1){
            foreach ($strings as $s){
                $stringMatchedAtLeastOneExcludingFilter = Util::auxCheckIfStringMatchesSomeFilter($s, $excludingFilters);
                if (!$stringMatchedAtLeastOneExcludingFilter!==false){
                    $ret[] = $s;
                }
            }//for each string

            return $ret;
        }//if there are working conditions
        else{
            return false;
        }//no working conditions
    }//auxFilterOutStrings

    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.
    public static function fb (
        string $pMessage, //2019-03-20, refactored $m to $pMessage
        bool $pbInjectTimeStamp = true
    )
    {
        $sapiName = php_sapi_name();
        //echo "sapiName = $sapiName  ";

        $CONSOLE=( $sapiName === 'cli' );

        if (!$CONSOLE){
            $pMessage=str_replace("\n", "<br>", $pMessage);
        }
        else{
            //$m=str_replace("<mark>", "** ", $m);
            //$m=str_replace("</mark>", " **", $m);
            $pMessage=str_replace("<br>", PHP_EOL, $pMessage);
            $pMessage=str_replace("<hr>", "----------------------------------------------------\n", $pMessage); //20140902
            $pMessage=str_replace("\n", PHP_EOL, $pMessage);
        }

        //http://stackoverflow.com/questions/3133209/how-to-flush-output-after-each-echo-call
        try{
            @ob_end_flush();

            //while (@ob_end_flush()); //flush all output buffers
        }
        catch (\Exception $e){
            $mError=$e->getMessage();
            echo "Exception $mError while exec'ing \"ob_end_flush();\"".PHP_EOL;
        }

        # CODE THAT NEEDS IMMEDIATE FLUSHING
        $now = date ("Y-m-d H:i:s");
        if ($pbInjectTimeStamp){ //2019-03-20 , timestamp made optional via new param
            echo "$now: $pMessage";
        }
        else{
            echo $pMessage;
        }

        try{
            ob_start();
        }
        catch (\Exception $e){
            $mError=$e->getMessage();
            echo "Exception $mError while exec'ing \"ob_start();\"".PHP_EOL;
        }

        return $pMessage;
    }//fb

    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.
    public static function auxCheckBool ($b){
        $c0 = isset($b);
        $c1 = $c0 && is_bool($b);
        return $c1;
    }//auxCheckBool

    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.
    /*
     * 2018-05-20
     * receives a string: $pString
     * receives a string OR an array of strings: $pStrStartOrArrayOfStarts
     * returns true if $pString starts with $pStrStartOrArrayOfStarts, the latter being a single string
     * returns the found start string if $pString starts with some string from $pStrStartOrArrayOfStarts
     */
    public static function stringStartsWith(
        $pString,
        $pStrStartOrArrayOfStarts,
        $pBCaseSensitive=false
    ){
        $bReceivedArrayOfPossibleStarts = is_array($pStrStartOrArrayOfStarts);

        if ($bReceivedArrayOfPossibleStarts){
            foreach($pStrStartOrArrayOfStarts as $strStart){
                $bStartsWithStart = $pBCaseSensitive ?
                    strpos($pString, $strStart)===0
                    :
                    stripos($pString, $strStart)===0;

                if (!is_string($strStart)){
                    $bProblem = true;
                }

                if ($bStartsWithStart){
                    return $strStart;
                }//if
            }//foreach

            return false;
        }//if working with an array of possible starts
        else{
            //received a single string for testing for start
            $pos = strpos($pString, $pStrStartOrArrayOfStarts);
            $iPos = stripos($pString, $pStrStartOrArrayOfStarts);
            $bStartsWithStart = $pBCaseSensitive ?
                $pos===0
                :
                $iPos===0;
            return $bStartsWithStart;
        }//if working with a single start
    }//stringStartsWith

    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.
    /*
     * 2018-05-20
     */
    public static function sanitizeStringForHtmlCodePresentation($pStr){
        $pStr = str_replace("<", "&lt;", $pStr);
        $pStr = str_replace(">", "&gt;", $pStr);
        $pStr = str_replace("&", "&amp;", $pStr);
        return $pStr;
    }//sanitizeStringForHtmlCodePresentation

    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.
    /*
     * 2019-02-26
     * untested
     */
    public static function arrayToTextString(
        $pA = [],
        $pShowKeys = true,
        $pSeparatorBetweenElements = " , ".PHP_EOL,
        $pStrArrayNotationForOpening = "[".PHP_EOL,
        $pStrArrayNotationForClosing = PHP_EOL."]"
    ){
        $ret = $pStrArrayNotationForOpening;
        $c0 = is_array($pA);
        if ($c0){
            $iHowManyElements = count($pA);
            $idx = 1;
            foreach ($pA as $k=>$v){
                //2019-03-01, experimental, when noticed outputs showing arrays as keys
                $strK = is_array($k) ?
                    Util::arrayToTextString(
                        $k,
                        $pShowKeys,
                        $pSeparatorBetweenElements,
                        //str_replace(PHP_EOL, "", $pSeparatorBetweenElements), //avoid PHP_EOL when representing the keys as strings, in case the keys are arrays, so get a more compact representation
                        $pStrArrayNotationForOpening,
                        //str_replace(PHP_EOL, "", $pStrArrayNotationForOpening),
                        $pStrArrayNotationForClosing
                        //str_replace(PHP_EOL, "", $pStrArrayNotationForClosing)
                    )
                    :
                    $k.""; //2019-03-01, keys can also be arrays!

                //$strLineForElement = $pShowKeys ? "@$k: " : ""; //if keys could NOT be arrays, it would be this simple

                $strLineForElement = $pShowKeys ? "@$strK: " : "";

                $strLineForElement.= is_array($v) ?
                    Util::arrayToTextString(
                        $v,
                        $pShowKeys,
                        //$pSeparatorBetweenElements, //would get transformed into array!
                        " , ".PHP_EOL,
                        $pStrArrayNotationForOpening,
                        $pStrArrayNotationForClosing
                    )
                    :
                    $v;

                $bLastElement = $idx===$iHowManyElements;
                if (!$bLastElement) $strLineForElement.=$pSeparatorBetweenElements;

                //code used on 2019-03-16, to spot an odd problem where $pSeparatorBetweenElements would be an arrat
                /*
                $bIsArrayStrLineForElement = is_array($strLineForElement);
                $bIsArraySeparator = is_array($pSeparatorBetweenElements);
                $bProblem = $bIsArraySeparator || $bIsArrayStrLineForElement;
                if ($bProblem){
                    echo "problem!";
                }
                */

                $ret.= $strLineForElement;

                $idx++;
            }//foreach element
        }//if param is indeed an array
        $ret.=$pStrArrayNotationForClosing;
        return $ret;
    }//arrayToTextString

    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.
    /*
     * 2018-05-20
     */
    public static function arrayToHtmltring(
        $pA = [],
        $pShowKeys = true,
        $pOpenValuesByDefaultWhenShowingKeysThisAffectsTheHtmlDetailsElement = true,
        $pSeparatorBetweenElements = "",//",<br>",
        $pStrArrayNotationForOpening = "[<br>",
        $pStrArrayNotationForClosing = "<br>]"
    )
    {
        $ret = $pStrArrayNotationForOpening;
        $c0 = is_array($pA);
        if ($c0){
            $iHowManyElements = count($pA);
            $idx = 1;
            foreach ($pA as $k=>$v){
                $strAttributeOpen = $pOpenValuesByDefaultWhenShowingKeysThisAffectsTheHtmlDetailsElement ? "open" : "";
                $strElement = $pShowKeys ? "<details $strAttributeOpen><summary>@$k</summary>" : "";

                $v = Util::sanitizeStringForHtmlCodePresentation($v);

                $bValueStartsWithHttp = Util::stringStartsWith($v, Util::HTTP_STARTS);
                $v = $bValueStartsWithHttp ? "<a href='$v'>$v</a>" : $v;
                //$ret.= is_array($v) ? $this->arrayToHtmltring($v) : $v;

                $strElement.= is_array($v) ?
                    Util::arrayToHtmltring(
                        $v,
                        $pShowKeys,
                        $pOpenValuesByDefaultWhenShowingKeysThisAffectsTheHtmlDetailsElement,
                        $pSeparatorBetweenElements,
                        $pStrArrayNotationForOpening,
                        $pStrArrayNotationForClosing
                    )
                    :
                    $v;
                $strElement.= $pShowKeys ? "</details>" : "";
                $ret.= $strElement;

                $bLastElement = $idx===$iHowManyElements;
                if (!$bLastElement) $ret.=$pSeparatorBetweenElements;
                $idx++;
            }//foreach element
        }//if param is indeed an array
        $ret.=$pStrArrayNotationForClosing;
        return $ret;
    }//arrayToHtmltring

    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.
    //2016-07-11
    //2016-07-29
    //2018-05-14 : array return
    const KEY_SYMBOL = "KEY_SYMBOL";
    const KEY_POS = "KEY_POS";
    public static function auxFirstOccurrenceOfOneOfTheFollowing (
        $s,
        $possibilities
    )
    {
        $ret=false;
        $c1=is_string ($s);
        $c2=is_array ($possibilities);
        $c3=$c2 && count($possibilities)>0;

        $lowestIndex = false;
        if ($c1 && $c2 && $c3){
            foreach ($possibilities as $p){
                $pos=stripos ($s, $p);
                if ($pos!==false){
                    if ($lowestIndex===false || $pos<$lowestIndex) $lowestIndex=$pos;

                    //bad code on PHP 8
                    /*
                    $lowestIndex=($lowestIndex===false) ?
                        $pos
                        :
                        ($pos<$lowestIndex)?$pos:$lowestIndex;
                    */
                }
            }
        }
        //return $ret;
        return [Util::KEY_POS => $lowestIndex , Util::KEY_SYMBOL => $s[$lowestIndex]];
    }//auxFirstOcurrenceOfOneOfTheFollowing

    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.
    //2016-07-11
    //2016-07-29
    //2018-05-14 : array return, renamed to auxiFirstOcurrenceOfOneOfTheFollowing, returning only the pos
    public static function auxiFirstOcurrenceOfOneOfTheFollowing (
        $s,
        $possibilities
    )
    {
        $ret=false;
        $c1=is_string ($s);
        $c2=is_array ($possibilities);
        $c3=$c2 && count($possibilities)>0;

        if ($c1 && $c2 && $c3){
            foreach ($possibilities as $p){
                $pos=stripos ($s, $p);
                if ($pos!==false){
                    $ret=($ret===false)?$pos:(($pos<$ret)?$pos:$ret);
                    return $ret;
                }
            }
        }
        return $ret;
    }//auxiFirstOcurrenceOfOneOfTheFollowing

    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.
    public static function intWithXDigits(
        $pInt,
        $pXDigits
    ){
        $strInt = "".$pInt;
        while(strlen($strInt)<$pXDigits){
            $strInt = "0".$strInt;
        }//while
        return $strInt;
    }//intWithXDigits

    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.
    /*
     * 2018-07-01
     */
    public static function stringToArrayOfInts(
        $pString, //e.g. "10 20 30"
        $pSeparator = " "
    ){
        $pString = trim($pString);
        $parts = explode($pSeparator, $pString);
        $c0 = is_array($parts) && count($parts)>0;
        if ($c0){
            $ret = [];
            foreach($parts as $part){
                $part = trim($part);
                if (!empty($part)){
                    $partIsNumber = is_numeric($part);
                    if ($partIsNumber) $ret[] = intval($part);
                }//if part not empty
            }//for every part
            return $ret;
        }//if basic check c0
        return false;
    }//stringToArrayOfInts

    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.
    /*
     * 2019-05-19
     * copied from am_imports/am_file_helpers.php
     *
     * 2020-09-09
     * added some chars
     *
     * unacceptable chars in Windows: \/:*?<>|
     */
    public static function auxIsValidChar_v1($c){
        $digit=($c>="0" && $c<="9");
        $small=($c>="a" && $c<="z");
        $capital=($c>="A" && $c<="Z");
        $space=$c===" ";
        $underscore=$c==="_";
        $dot=$c===".";
        $parentheses=$c==="("||$c===")"||$c==="["||$c==="]";
        $hyfen=$c==="-"; //TODO : found this char name in English
        $comma = $c===","; //2019-07-17
        $semiComma = $c===";"; //2020-09-09
        $sharp = "#"; //2019-08-23
        //return $digit || $small || $capital || $space || $underscore || $dot || $parentheses || $hyfen;
        return $digit || $small || $capital || $underscore || $dot || $parentheses || $hyfen || $comma || $space || $sharp;
    }//auxIsValidChar_v1

    /*
     * 2020-09-09
     * unacceptable chars in Windows: \/:*?<>|
     */
    const UNACCEPTABLE_CHARS_IN_FILE_NAMES_WINDOWS = ['\\', '/', ':', '*', '?', '<', '>', '|'];
    public static function getCharsForbiddenInFileNames ()
    {
        return self::UNACCEPTABLE_CHARS_IN_FILE_NAMES_WINDOWS;
    }//getCharsForbiddenInFileNames

    /*
     * 2020-09-09
     * rewriting of auxIsValidChar, which became auxIsValidChar_v1
     * TODO:
     * instead of replacing accented chars with _ , replace them with the base char
     */
    public static function auxIsValidChar($c){
        $bRet = true;

        $iCodeSPACE = ord(" "); //32
        $iCode = ord($c);

        $bIsExtendedAsciiChar = $iCode>=0 && $iCode<=255; //will let escape odd chars
        $bIsStrictAscii = $iCode>=0 && $iCode<=127;
        $bIsExtendedAsciiCharAndVisible = $bIsExtendedAsciiChar && $iCode>=$iCodeSPACE;
        $bIsStrictAsciiAndVisible = $bIsStrictAscii && $iCode>=$iCodeSPACE;

        $aForbiddenChars = self::getCharsForbiddenInFileNames();
        $bIsForbiddenInFileNames = array_search(
            $c,
            $aForbiddenChars
        )!==false;

        $bUnacceptableInFileNames = $bIsForbiddenInFileNames || !$bIsStrictAsciiAndVisible;
        $bRet = !$bUnacceptableInFileNames;
        return $bRet;
    }//auxIsValidChar

    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.
    /*
     * 2019-05-19
     * copied from am_imports/am_file_helpers.php
     * 2019-08-23
     * edited in the context of the project "org"
     * params now have data types
     * function now has an explicit return type
     * function was returning false when called to sanitize empty strings; now it always returns a string, even if empty
     * 2020-09-09
     * added support to control of what replacement char to use
     */
    public static function auxSanitizeFileName (
        string $pStrFileName,
        int $piMaxSize = 0, //defaults to 0, so the file name will NOT get castrated, regardless of its length
        string $pReplacementChar = '_' //2020-09-09
    ) : string
    {
        //$fileName=mb_convert_encoding($fileName, "utf-8", "iso-8859-1");
        $ret="";
        $size=strlen($pStrFileName);

        for ($pos=0; $pos<$size; $pos++){
            $c=$pStrFileName[$pos];
            $bValidChar = self::auxIsValidChar($c);
            if (!$bValidChar)
                //$ret.="_";
                $ret.=$pReplacementChar;
            else
                $ret.=$c;
        }//for

        /*
         * 2016-10-08
         * had to impose a max length for filename because URLs with 250 chars from a site were causing
         * file_put_contents to fail in saving and return false
         *
         * at first I was looking for an extension in the file name, but then I remembered the use cases where a direct URL is the argument
         */
        if ($ret!==false && $piMaxSize>0){
            $filename = substr($ret, 0, $piMaxSize);
            //$ext = substr($ret, strripos($ret, "."));
            //$filename.=$ext;
            return $filename;
        }

        //return $ret===""?false:$ret;
        return $ret; //this way, it always returns a string, even if empty
    }//auxSanitizeFileName

    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.
    /*
     * 2019-02-26
     * 1st attempt at a generic util function to echo any function's activity:
     * when called, its name, its arguments, its return value
     * the built string takes into consideration if it is running on console (php_sapi_name==="cli") or not
     */
    public static function debugFunctionCall(
        $pStrFunctionName,
        $paFunctionArgs,
        $pReturnValue
    ){
        $strArgs = ""; //string representing all the function's arguments
        $strNow = date ("Y-m-d H:i:s");

        $functionToConvertArraysToString = php_sapi_name()==="cli" ? "arrayToTextString" : "arrayToHtmltring"; //notice that these are names of static methods!

        foreach($paFunctionArgs as $currentArg){
            $strCurrentArg = is_array($currentArg) ?
                call_user_func_array(["self", $functionToConvertArraysToString], $currentArg)
                :
                $currentArg."";
            $strArgs.= $strCurrentArg." ";
        }//foreach argument

        $strReturnValue = is_array($pReturnValue) ?
            //PHP Warning:  call_user_func_array() expects parameter 1 to be a valid callback, class 'Util' not found in F:\coding.jb\php\blogbot\am\util\Util.php on line 454
            //call_user_func_array(["Util", $functionToConvertArraysToString], $pReturnValue)
            //call_user_func_array(["\Util", $functionToConvertArraysToString], $pReturnValue)
            call_user_func_array(
                ["self", $functionToConvertArraysToString],
                //$pReturnValue //this would be wrong! Only detected on 2019-03-16
                [$pReturnValue]
            )
            :
            $pReturnValue."";

        $msg = sprintf(
            "%s @ %s:\n\tARGUMENTS: %s\n\tRETURN: %s\n",
            $strNow,
            $pStrFunctionName,
            $strArgs,
            $strReturnValue
        );
        //Util::fb($msg); //fb will also checks php_sapi_name

        /*
         * 2019-03-20, dropped use of Util::fb for plain echo
         */
        echo $msg;

        return $msg;
    }//debugFunctionCall

    /*
     * recebe uma frase "abcabc"
     * recebe um símbolo "b"
     * retorna um array de posições em que o símbolo ocorre na frase
     * por exemplo retornaria [1, 4]
     * aplicação neste projeto é na deteção de
     * sub-domínios e domínios
     * palavrão.hierarquia ; por exemplo arturmarques.com
     * arturmarques.com/coisa.html
     * por vezes os sub-domínios são mais elaborados
     * Request: ads.xpto.site.com/coisa.doc
     * Crunched: ads.xpto.site.com/coisa.doc
     */
    public static function allOcurrencesOfSymbolInString(
        $pString,
        $pSymbol
    ){
        $ret = [];
        $c0 = is_string($pString) && is_string($pSymbol) && strlen($pSymbol)===1;
        if ($c0){
            $howManySymbolsInString = strlen($pString);
            for ($idx=0; $idx<$howManySymbolsInString; $idx++){
                $currentSymbol = $pString[$idx];
                $bMatch = $currentSymbol===$pSymbol;
                if ($bMatch){
                    $ret[] = $idx;
                }//if match
            }//for every symbol
        } //if conditions met
        return $ret;
    }//allOcurrencesOfSymbolInString

    //--------------------------------------------------------------------------------
    /*
     * 2020-03-29
     * Created as a first response to how to present aggregated search results.
     * Such results are assoc arrays with a variable number of keys.
     * They should have more than anchor and a href, at least a "source" key more.
     */
    // public static function genericRepresentationOfLinearAssocArraysToHtml(
    //     $aNumericallyIndexedArrayOfAssocArrays,
    //     int $piStart = 1,
    //     bool $pbTrimAnchor = false
    // ){
    //     $html = "<ol start='$piStart'>";
    //     /*
    //      * because this is a linear numeric iteration, one should check the element at each index to see if it really exists
    //      * there might be a key 0, 1, but no 2 for example
    //      * beware!
    //      *
    //      * However count will NOT account for the empty elements, so $idx++ should only incremented for truly existing values
    //      */
    //     foreach ($aNumericallyIndexedArrayOfAssocArrays as $aCurrentAssocArray) {
    //         if (!empty($aCurrentAssocArray)) { //check if indeed there is an element at the index
    //             $strLi = "<li><ul>";
    //             foreach ($aCurrentAssocArray as $strKey => $value) {

    //                 $bKeyIsHref = strcasecmp($strKey, HttpHelper::KEY_HREF) === 0;
    //                 if ($bKeyIsHref) {
    //                     $bValueStartsWithHttp = stripos($value, "http") === 0;
    //                     if ($bValueStartsWithHttp) {
    //                         $value = "<a href=\"$value\">$value</a>";
    //                     }//if
    //                 }//if

    //                 $strLi .= "<li><mark>$strKey: </mark> $value</li>" . PHP_EOL;
    //             }//foreach
    //             $strLi .= "</ul></li>";
    //             $html .= $strLi;
    //         }//if
    //     }//for
    //     return $html;
    // }//genericRepresentationOfLinearAssocArraysToHtml

    // //--------------------------------------------------------------------------------
    // /*
    //  * 2021-04-03
    //  * created in response to a need in BlogBot2
    //  *
    //  * receives:
    //  * - an array of pairs, each with 2 keys KEY_ANCHOR and KEY_HREF
    //  * - a string telling if it is to replace on the anchor OR on the href
    //  * - the string(s) to be replaced
    //  * - the string(s) that will replace them
    //  *
    //  * returns
    //  * - an array of pairs, corresponding to the originals, but after doing the replacements
    //  *
    //  * if the arguments are invalid, the replacement ops do NOT happen and the return value matches the original
    //  */
    // public static function genericStringReplacementOverAnchorOrHrefInArraysOfPairsAnchorHref(
    //     $paPairsAnchorHref = [],
    //     $pStrWorkOn = HttpHelper::KEY_HREF,
    //     $pReplaceThis = "", //could be array
    //     $pReplaceForThat = "" //could be array
    // ){
    //     $aRet = [];
    //     foreach ($paPairsAnchorHref as $aPair){
    //         $bAnchorKeyExists = array_key_exists(HttpHelper::KEY_ANCHOR, $paPairsAnchorHref);
    //         $bHrefKeyExists = array_key_exists(HttpHelper::KEY_HREF, $paPairsAnchorHref);

    //         $bValidPair = $bAnchorKeyExists && $bHrefKeyExists;
    //         //work being request over valid key
    //         $bValidWorkOn = array_search($pStrWorkOn,[HttpHelper::KEY_ANCHOR, HttpHelper::KEY_HREF])!==false;

    //         if ($bValidPair && $bValidWorkOn){
    //             $strAnchor = $aPair[HttpHelper::KEY_ANCHOR];
    //             $strHref = $aPair[HttpHelper::KEY_HREF];

    //             $bThereIsWorkToBeDone = !empty($pStrBasicOperationToPerform);

    //             if ($bThereIsWorkToBeDone){
    //                 switch ($pStrWorkOn){
    //                     case HttpHelper::KEY_ANCHOR:
    //                         $strAnchor = str_replace(
    //                             $pReplaceThis,
    //                             $pReplaceForThat,
    //                             $strAnchor
    //                         );
    //                         $aPair[HttpHelper::KEY_ANCHOR] = $strAnchor;
    //                         break;
    //                     case HttpHelper::KEY_HREF:
    //                         $strHref = str_replace(
    //                             $pReplaceThis,
    //                             $pReplaceForThat,
    //                             $strHref
    //                         );
    //                         $aPair[HttpHelper::KEY_HREF] = $strHref;
    //                         break;
    //                 }//switch
    //             }//if
    //             $aRet[] = $aPair;
    //         }//if valid request
    //     }//foreach
    //     return $aRet;
    // }//genericOpsOverAnchorOrHrefInArraysOfPairsAnchorHref

}//Util