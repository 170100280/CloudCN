<?php

/**
 * Created by PhpStorm.
 * User: research / Artur Marques
 * LAST EDIT DATE: 2021-04-14
 * LAST EDIT TIME: 00:00
 */

namespace util;

use util\Util;

class HttpHelper
{   /*TRABALHO CLOUD*/
    const BENFICA_URL = "/pt-pt/agora/noticias";
    const BENFICA_UNSET_URL = "/pt-pt/agora/noticias";


    const REGEXP_ABS_URL = "^(http|https|ftp|ftps){1}(://){1}";

    const USER_AGENT_STRING_MOZ47 = 'Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0';
    const USER_AGENT_STRING_GOOGLE_NEWS = 'Googlebot-News';

    const DEFAULT_BOT_NAME = "AM's Http Helper";
    const DEFAULT_REFERRER = "https://www.google.com";

    /*
     * keys used in array returns
     */
    const KEY_BIN = "BIN";
    const KEY_STATUS = "STATUS";
    const KEY_ERROR_CODE = "ERRORN";
    const KEY_ERROR_MESSAGE = "ERROR";
    const KEY_HREF = "HREF";
    const KEY_ANCHOR = "ANCHOR";
    const KEY_BYTES_WRITTEN = "BYTES_WRITTEN";
    const KEY_FILE_DL = "FILE_DL";

    /*
     * supported HTTP methods
     */
    const METHOD_HEAD = "HEAD";
    const METHOD_GET = "GET";
    const METHOD_POST = "POST";

    /*
     * useful
     */
    const INCLUDE_HEADERS = true;
    const EXCLUDE_HEADERS = false;
    const DEFAULT_USER_AGENT_STRING = "AM's Http Helper";
    const DEFAULT_CURL_TIMEOUT = 25;
    const DEFAULT_CURL_MAX_REDIRS = 10; //4 failed on 2016-07-13
    const DEFAULT_CURL_VERBOSE = 0; //2016-08-29 switched to 0/false
    const DEFAULT_CURL_COOKIES_FOLDER = "./cookies/";
    const DEFAULT_CURL_COOKIE_R_FILE = "cookie_r.txt";
    const DEFAULT_CURL_COOKIEJAR_W_FILE = "cookiejar_w.txt";
    const DEFAULT_CURL_COOKIEJAR_RW_FILE = "cookiejar_rw.txt"; //2019-01-02
    const DEFAULT_CURL_SSL_VERIFYPEER = true; //set to false if you didn't properly configure PHP
    const DEFAULT_CURL_BINARYTRANSFER = true;
    const DEFAULT_REQUEST_HEADERS = array(
        'Accept: text/html,application/xhtml+xml,application/xml',
        'Connection: keep-alive'
    );

    //mods à class HttpHelper em 2017-04-04 : 2 membros de dados + 1 constructor
    private $mUserAgentSignature; //a user agent string (deixou de ser constante, passou a ser var)
    private $mReferrer; //para representar o referrer (o endereço de origem do tráfego)
    private $mCookiesFile; //2018-07-06

    private $mUtil; //2017-04-23

    /*
     * 2020-09-09
     */
    public static function checkHttpProxy(
        $pStrAddressToConsumeViaProxy = "https://www.google.com/",
        $pStrProxyAddress,
        $pStrProxyPort,
        $pStrProxyUser = null,
        $pStrProxyUserPass = null
    ) {
        $oCurlHandler = curl_init($pStrAddressToConsumeViaProxy);

        //curl_setopt($oCurlHandler, CURLOPT_BINARYTRANSFER, true); //deprecated from PHP 5.3!
        curl_setopt($oCurlHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($oCurlHandler, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($oCurlHandler, CURLOPT_HTTPPROXYTUNNEL, true);
        curl_setopt($oCurlHandler, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        //curl_setopt ($oCurlHandler, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);

        curl_setopt($oCurlHandler, CURLOPT_USERAGENT, self::DEFAULT_USER_AGENT_STRING);
        curl_setopt($oCurlHandler, CURLOPT_VERBOSE, true);
        curl_setopt($oCurlHandler, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($oCurlHandler, CURLOPT_COOKIESESSION, TRUE);
        curl_setopt($oCurlHandler, CURLOPT_HEADER, true);

        curl_setopt($oCurlHandler, CURLOPT_PROXY, $pStrProxyAddress);
        curl_setopt($oCurlHandler, CURLOPT_PROXYPORT, $pStrProxyPort);

        if (!empty($pStrProxyUser) && !empty($pStrProxyUserPass)) {
            //Specify the username and password.
            $strUserAndPass = $pStrProxyUser . ":" . $pStrProxyUserPass;
            curl_setopt($oCurlHandler, CURLOPT_PROXYUSERPWD, $strUserAndPass);
        } //if user+pass required

        //Execute the request.
        $binResponse = curl_exec($oCurlHandler);

        $e = curl_errno($oCurlHandler);
        $eM = curl_error($oCurlHandler);

        $iResponseCode = curl_getinfo($oCurlHandler, CURLINFO_RESPONSE_CODE); //2016-07-14
        $strMsg = $iResponseCode === 200 ? "Connection seems OK (code 200)" . PHP_EOL : "Connects seems to FAIL (code $iResponseCode)" . PHP_EOL;

        curl_close($oCurlHandler);

        /*if ($e!==0){
            echo $strMsg;
            throw new Exception($eM);
            //code will now stop running, because an Exception was thrown (and not catched)
        }//if

        //if the code reaches here, not Exception happened
        echo $strMsg;*/

        //return $binResponse;
        return $iResponseCode;
    } //checkHttpProxy

    /*
     * 2020-11-07
     * will EXPAND goo.gl addresses without using the API nor requiring JS
     * e.g.
     * http://goo.gl/fbsA
     * translates to
     * https://www1.folha.uol.com.br/tec/2010/01/683022-bons-jogadores-tem-partes-do-cerebro-maiores-diz-estudo.shtml
     *
     * without JS (as with plain CURL usage) Google will respond a page with JS for the redirection
     * location.replace( "http://www1.folha.uol.com.br/tec/2010/01/683022-bons-jogadores-tem-partes-do-cerebro-maiores-diz-estudo.shtml" )
     * BUT since JS is disabled, it also provides an equivalent mechanism via appropriate HTTP HEADER
     * e.g.
     * <meta http-equiv="Refresh" content="0; url=http://www1.folha.uol.com.br/tec/2010/01/683022-bons-jogadores-tem-partes-do-cerebro-maiores-diz-estudo.shtml">
    */

    const GOOGLE_HTTP_REFRESH_REDIRECTION_MARK = "<meta http-equiv=\"Refresh\" content=\"0; url=";
    public static function extractRedirectionUrlFromHtmlRedirectionPage(
        string $pStrHtmlSourceCode,
        string $pStrRedirectionMark = self::GOOGLE_HTTP_REFRESH_REDIRECTION_MARK
    ) {
        $iWhereDoesTheMarkStart = stripos(
            $pStrHtmlSourceCode,
            $pStrRedirectionMark
        );

        if ($iWhereDoesTheMarkStart !== false) {
            $strHtmlSourceCodeStartingAtTheTranslatedUrl =
                substr(
                    $pStrHtmlSourceCode,
                    $iWhereDoesTheMarkStart + strlen($pStrRedirectionMark)
                );

            if (!empty($strHtmlSourceCodeStartingAtTheTranslatedUrl)) {
                $iWhereDoesTheUrlClose = stripos(
                    $strHtmlSourceCodeStartingAtTheTranslatedUrl,
                    "\""
                );

                if ($iWhereDoesTheUrlClose !== false) {
                    $strTranslatedUrl =
                        substr(
                            $strHtmlSourceCodeStartingAtTheTranslatedUrl,
                            0,
                            $iWhereDoesTheUrlClose
                        );

                    return !empty($strTranslatedUrl) ? $strTranslatedUrl : false;
                } //if there is an URL closing
            } //if could extract the string after the mark

            return false;
        } //if there is a HTTP HEADER for <meta http-equiv="Refresh" content="0; url=
    } //extractRedirectionUrlFromHtmlRedirectionPage

    /*
     * 2020-11-07
     */
    public static function expandGooglUrl(
        string $pGooglUrl
    ) {
        $ch = curl_init($pGooglUrl);
        if ($ch) {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); //without this, empty return for goog.gl addresses
            curl_setopt($ch, CURLOPT_ENCODING, "");
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_VERBOSE, self::DEFAULT_CURL_VERBOSE);
            curl_setopt($ch, CURLOPT_MAXREDIRS, self::DEFAULT_CURL_MAX_REDIRS);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, self::DEFAULT_CURL_SSL_VERIFYPEER);

            $strHtmlSourceCode = curl_exec($ch);

            $iErrorCode = curl_errno($ch);
            $strErrorMsg = curl_error($ch);

            /* if ($strHtmlSourceCode){
                return self::extractRedirectionUrlFromHtmlRedirectionPage(
                    $strHtmlSourceCode,
                    self::GOOGLE_HTTP_REFRESH_REDIRECTION_MARK
                );
            }//if got a response*/
        } //if got a curl handler
        return $strHtmlSourceCode;
    } //expandGooglUrl

    public function __construct(
        $pSignature = self::USER_AGENT_STRING_MOZ47, //self::DEFAULT_BOT_NAME,
        $pReferrer = self::DEFAULT_REFERRER,
        $pCookiesFile = false
    ) {
        $this->mUserAgentSignature = $pSignature;
        $this->mReferrer = $pReferrer;
        //$this->mUtil = new \am\util\Util();


        //$this->mCookiesFile = $pCookiesFile === false ? self::DEFAULT_CURL_COOKIE_R_FILE : $pCookiesFile;
        /*
         * 2019-01-02
         * if no cookies file is set, use SEPARATE cookies files: one from where cookies should be read, the other to where cookies should be written
         */
        if ($pCookiesFile === false) {
            $this->mCookiesFile = self::DEFAULT_CURL_COOKIEJAR_RW_FILE; //same file for reading and writing
        } //if
        else {
            $this->mCookiesFile = $pCookiesFile; //2019-03-20
        }
    } //__construct

    //--------------------------------------------------------------------------------
    /*
    returns an assoc array, e.g.
    $return_array['BIN'] = contents of fetched resource
    $return_array['STATUS'] = CURL generated status of transfer
    $return_array['ERROR_CODE']  = CURL generated error status
    $return_array['ERROR_MESSAGE']   = string for the error

    2020-11-08
    rewritten
    added support for custom cookies injection
     */

    public function http(
        $pTargetUrl, //URL of resource to consume or submit
        //$ref = self::DEFAULT_REFERRER, //referrer //ter um default referrer pode quebrar o funcionamento com certos sites
        $pReferrer = false,
        $pMethod = self::METHOD_GET, //GET, POST or HEAD
        $pDataArray = false, //false or array of pairs name:value that will be considered in the request > will impact query string on GET or CURLOPT_POSTFIELDS on POST
        $pbIncludeResponseHeadersInCurlExecOutput = false, //true or INCLUDE_HEADERS for headers inclusion , false or EXCLUDE_HEADERS for headers exclusion
        $pInjectCustomHttpRequestHeaders = false, //false or array if wanting to inject custom http headers
        $paInjectTheseCookies = [] //2020-11-08 e.g. ["over18=1", "xpto=2"]
    ) {
        $pReferrer = ($pReferrer === false) ? $this->mReferrer : $pReferrer;

        //Initialize PHP/CURL handle
        $ch = curl_init();
        if ($ch) {
            curl_setopt($ch, CURLOPT_TIMEOUT, self::DEFAULT_CURL_TIMEOUT);
            curl_setopt($ch, CURLOPT_ENCODING, ""); //2016-12-08, to support encoding such like gzip deflate

            //curl_setopt($ch, CURLOPT_USERAGENT, self::DEFAULT_USER_AGENT_STRING);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->mUserAgentSignature);

            curl_setopt($ch, CURLOPT_VERBOSE, self::DEFAULT_CURL_VERBOSE);
            curl_setopt($ch, CURLOPT_MAXREDIRS, self::DEFAULT_CURL_MAX_REDIRS);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, self::DEFAULT_CURL_SSL_VERIFYPEER);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

            curl_setopt($ch, CURLOPT_REFERER, $pReferrer);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, self::DEFAULT_CURL_BINARYTRANSFER);

            /*
             * COOKIEJAR is the file to where cookies are written
             * https://curl.haxx.se/libcurl/c/CURLOPT_COOKIEJAR.html
             */
            //curl_setopt($ch, CURLOPT_COOKIEJAR, self::DEFAULT_CURL_COOKIEJAR_W_FILE);   // Cookie management.
            //2019-01-02
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->mCookiesFile); //where to write cookies

            /*
             * CURLOPT_COOKIEFILE is the file from where cookies are read
             * https://curl.haxx.se/libcurl/c/CURLOPT_COOKIEFILE.html
             */
            //curl_setopt($ch, CURLOPT_COOKIEFILE, self::DEFAULT_CURL_COOKIE_R_FILE);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->mCookiesFile); //where to read cookies from - you might want to forge an initial cookies file, as taken from browser

            //2020-11-08 : inject custom cookies
            foreach ($paInjectTheseCookies as $pStringSingleCookie) {
                curl_setopt(
                    $ch,
                    CURLOPT_COOKIE,
                    $pStringSingleCookie
                );
            } //foreach

            //Build a query string if the method is get and there is a data_array
            if (is_array($pDataArray) && $pMethod == self::METHOD_GET) {
                $query_string = http_build_query($pDataArray);
            }

            //HEAD method configuration
            if ($pMethod == self::METHOD_HEAD) {
                curl_setopt($ch, CURLOPT_HEADER, TRUE); // yes, http head
                /*
                 * CURLOPT_NOBODY
                 * TRUE to exclude the body from the output. Request method is then set to HEAD.
                 * Changing CURLOPT_NOBODY to FALSE does not change request method it to GET.
                 */
                curl_setopt($ch, CURLOPT_NOBODY, TRUE); // do NOT return body
            } else //if NOT METHOD_HEAD
            {
                //GET method configuration
                if ($pMethod == self::METHOD_GET) {
                    if (isset($query_string))
                        $pTargetUrl = $pTargetUrl . "?" . $query_string;
                    curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
                    curl_setopt($ch, CURLOPT_POST, FALSE);
                }
                //POST method configuration
                if ($pMethod == self::METHOD_POST) {
                    /* //mod 2
                    if(isset($query_string))
                        curl_setopt ($ch, CURLOPT_POSTFIELDS, $query_string);
                    */
                    curl_setopt($ch, CURLOPT_POST, TRUE);
                    curl_setopt($ch, CURLOPT_HTTPGET, FALSE);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $pDataArray);
                }

                /*
                 * 2016-07-14
                 * don't confund CURLOPT_HEADER (include response headers in exec output) with CURLOPT_HTTPHEADER (use custom request http headers)
                 */
                curl_setopt($ch, CURLOPT_HEADER, $pbIncludeResponseHeadersInCurlExecOutput);   //include head as needed

                /*
                 * 2016-07-14
                 */
                if (is_array($pInjectCustomHttpRequestHeaders) && count($pInjectCustomHttpRequestHeaders) > 0) {
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $pInjectCustomHttpRequestHeaders);
                } //if

                curl_setopt($ch, CURLOPT_NOBODY, FALSE); // Return body
            } //else (if NOT METHOD_HEAD)

            //Create return array
            $ret = array();

            curl_setopt($ch, CURLOPT_URL, $pTargetUrl);
            $ret[self::KEY_BIN] = curl_exec($ch);
            $redirectInfo = curl_getinfo($ch, CURLINFO_REDIRECT_URL); //2016-07-14

            $ret[self::KEY_STATUS] = curl_getinfo($ch);
            $ret[self::KEY_ERROR_CODE] = curl_errno($ch);
            $ret[self::KEY_ERROR_MESSAGE] = curl_error($ch);

            //Close PHP/CURL handle
            curl_close($ch);

            //Return results
            return $ret;
        } //if got curl handler

    } //http

    //--------------------------------------------------------------------------------
    public function findHyperlinksInHtmlTextConsideringDOM(
        $pStrHtmlSourceCode,
        bool $pbTrimAnchor = false
    ) {
        $ret = array();
        $bCheck = is_string($pStrHtmlSourceCode) && strlen($pStrHtmlSourceCode) > 0;

        if ($bCheck) {
            $d = new \DOMDocument(); //DOMDocument root namespace
            $bTrueOnSuccessFalseOtherwise = @$d->loadHTML($pStrHtmlSourceCode); //true on success or false on failure. If called statically, returns a DOMDocument and issues E_STRICT warning.
            $allAElements = $d->getElementsByTagName('a'); //DOMNodeList (each element is DOMNode)

            if ($allAElements) {
                foreach ($allAElements as $a) {
                    $href = $a->getAttribute('href');
                    $anchor = $pbTrimAnchor ? trim($a->nodeValue) : $a->nodeValue;

                    if ($href) {
                        $ret[] = array(
                            self::KEY_HREF => $href,
                            self::KEY_ANCHOR => $anchor
                        );
                    } //if there is href
                } //foreach a element
            } //if there are a elements
        } //if there is html

        return $ret;
    } //findHyperlinksInHtmlTextConsideringDOM

    //--------------------------------------------------------------------------------
    /*
     * 2018-05-18 - initial version
     * 2019-01-02 - some documentation as these comments and name refactoring
     * @param $pStrHtmlSourceCode : String
     * @return : String, representing the page title, or false on failure
     */
    public function findPageTitle(
        $pStrHtmlSourceCode
    ) {
        $ret = false;

        $d = new \DOMDocument();
        @$d->loadHTML($pStrHtmlSourceCode);
        $allTitleElements = $d->getElementsByTagName('title');
        //DOMNodeList object with a single attribute (length) stating the number of nodes

        $iHowManyTitleElements = $allTitleElements->length;
        //there should be only one title!
        if ($iHowManyTitleElements > 0) {
            //$ret = $allTitleElements[0]->nodeValue;
            $ret = $allTitleElements[0]->textContent;
        } //if

        return $ret;
    } //findAttributesInAnyLinkElementConsideringDOM

    //--------------------------------------------------------------------------------
    /*
     * 2019-01-02 - some name refactoring
     */
    public function findAttributesInAnyLinkElementConsideringDOM(
        $pStrHtmlSourceCode,
        $pStrAttributeNameToBeSearchedInElement = 'src'
    ) {
        $ret = array();

        $d = new \DOMDocument();
        @$d->loadHTML($pStrHtmlSourceCode);
        //$ret = $d->getElementsByTagName(""); //array of 0 elements
        $allLinkElements = $d->getElementsByTagName('link');
        //DOMNodeList object with a single attribute (length) stating the number of nodes

        foreach ($allLinkElements as $link) {
            $attributeValue = $link->getAttribute($pStrAttributeNameToBeSearchedInElement);
            $href = $link->getAttribute('href');
            $ret[] = array(self::KEY_HREF => $href, $pStrAttributeNameToBeSearchedInElement => $attributeValue, self::KEY_ANCHOR => ""); //2016-12-03
        } //foreach
        return $ret;
    } //findAttributesInAnyLinkElementConsideringDOM

    //--------------------------------------------------------------------------------
    /*
    * 2019-01-02 - some name refactoring
    */
    public function findLinksInHtmlTextWithAttributePlusHardwiredHrefAttribute(
        $pStrHtmlSourceCode,
        $pStrAttributeNameToBeSearchedInElement = 'REL'
    ) {
        $ret = array();

        $d = new \DOMDocument();
        @$d->loadHTML($pStrHtmlSourceCode);
        $allLinkElements = $d->getElementsByTagName('link');
        //DOMNodeList object with a single attribute (length) stating the number of nodes

        foreach ($allLinkElements as $link) {
            $attributeValue = $link->getAttribute($pStrAttributeNameToBeSearchedInElement);
            $href = $link->getAttribute('href');
            $ret[] = array(
                self::KEY_HREF => $href,
                $pStrAttributeNameToBeSearchedInElement => $attributeValue,
                self::KEY_ANCHOR => ""
            ); //2016-12-03
        } //foreach
        return $ret;
    } //findLinksInHtmlTextWithAttributePlusHardwiredHrefAttribute

    //--------------------------------------------------------------------------------
    /*
     * receives HTML source code
     * returns an array of arrays ("src"=>url , "type"=>"video/mp4 for example")
    <video width="320" height="240" controls>
        <source src="movie.mp4" type="video/mp4">
        <source src="movie.ogg" type="video/ogg">
        Your browser does not support the video tag.
    </video>
    not much tested

    2019-01-02 : renamed from findSrcsInHtmlTextConsideringDOM to findSourceElementsInHtmlConsideringDOM
     */
    public function findSourceElementsInHtmlConsideringDOM(
        $pStrHtmlSourceCode
    ) {
        $ret = array();
        if ($pStrHtmlSourceCode) {
            $d = new \DOMDocument();
            @$d->loadHTML($pStrHtmlSourceCode);
            $allAElements = $d->getElementsByTagName('source');

            foreach ($allAElements as $a) {
                if ($a) {
                    $src = $a->getAttribute('src');
                    $type = $a->getAttribute('type');
                    if ($src != null && $type != null) {
                        //TODO: create some constants for SRC and TYPE keys
                        $ret[] = array('src' => $src, 'type' => $type);
                    } //if there is src and type
                } //if there is an element
            } //foreach / for all elements
        } //if there is html

        return $ret;
    } //findSrcsInHtmlTextConsideringDOM

    //--------------------------------------------------------------------------------
    /**
     * 2016-11-12
     * receives html source code
     * receives a value that some div should have for one of its attributes
     * returns all the divs' html contents for divs with at least one attribute having the searched value
     *
     * this was created while trying to harvest ft.com
     * because the article's contents was regularly being moved from a certain div class to another
     * <div class="article__body n-content-body p402_premium" data-trackable="article-body" data-legal-copy>
     * but the presence of a "article-body" was a constant!
     *
     * 2019-01-02 : renamed from strFindsDivsWithAtLeastOneAttributeValued to astrFindsDivsWithAtLeastOneAttributeValued (because it returns an array)
     * 2019-01-13 :
     * renamed fom astrFindsDivsWithAtLeastOneAttributeValued because other functions that return arrays are not named with the aStr prefix
     * changed the way the strings are being compared
     * refactor/rename of some variables
     *
     * @param $pStrHtmlSourceCode
     * @param $pStrValueToBeFoundForAtLeastOneOfTheDivsAttributes
     * @return array
     */
    public function findsDivsWithAtLeastOneAttributeValued(
        $pStrHtmlSourceCode,
        $pStrValueToBeFoundForAtLeastOneOfTheDivsAttributes
    ) {
        $retDivContents = [];
        $htmlDOM = new \DOMDocument();
        $htmlDOM->loadHTML($pStrHtmlSourceCode);
        $aAllDivs = $htmlDOM->getElementsByTagName('div');

        if (count($aAllDivs) > 0) {
            foreach ($aAllDivs as $div) {
                $bDivHasAttributes = $div->hasAttributes();
                if ($bDivHasAttributes) {
                    //$aAllAttributes = $div->getAllAttributes();
                    $aAllAttributes = $div->attributes;
                    foreach ($aAllAttributes as $attributeObject) {
                        $attributeName = $attributeObject->nodeName;
                        $attributeValue = $attributeObject->nodeValue;
                        //echo $attributeValue . '<br>';

                        $bStringsMatch =
                            strcasecmp(
                                $attributeValue,
                                $pStrValueToBeFoundForAtLeastOneOfTheDivsAttributes
                            ) === 0;
                        //if ($attributeValue==$valueToBeFoundForAtLeastOneOfTheDivsAttributes){
                        if ($bStringsMatch) {
                            //echo $attributeName;
                            //echo $div->nodeValue;
                            $retDivContents[] = $div->nodeValue;
                        } //if found an attribute with the searched value
                    } //for all attributes
                } //if div has attributes
            } //for all divs
        } //if there are divs
        return $retDivContents;
    } //findsDivsWithAtLeastOneAttributeValued

    //--------------------------------------------------------------------------------
    /*
     * accepts if the hyperlink satisfies at least one filter, no matter the position where the match happens
     */
    public function filterHyperlinksAsArrayOfPairsHrefAnchorFilterByInclusionFilters(
        $hs, //array of arrays each representing an hyperlink, having KEY_HREF and KEY_ANCHOR keys
        $inclusionFiltersArray //, //array of strings whose value at KEY_HREF should match at least one to become part of the function's final return
        //$pAvoidRepeats = true //2019-11-11
    ) {
        $resourcesMatchingInclusiveFilter = array();

        foreach ($hs as $h) {
            $href = $h[self::KEY_HREF];

            $matchesFilter = $this->mUtil->auxCheckIfStringMatchesSomeFilter(
                $href,
                $inclusionFiltersArray
            );

            if ($matchesFilter) {
                //not the way to search in these arrays of pairs anchor href
                //$bIsNew = array_search($href, $resourcesMatchingInclusiveFilter) === false;

                $resourcesMatchingInclusiveFilter[] = $h;
            } //if
        } //foreach

        return $resourcesMatchingInclusiveFilter;
    } //filterHyperlinksAsArrayOfPairsHrefAnchorFilterByInclusionFilters

    //--------------------------------------------------------------------------------
    /*
     2016-11-23
     */
    public function filterHyperlinksAsArrayOfPairsHrefAnchorFilterByExclusionFilters(
        $hs, //array of arrays each representing an hyperlink, having KEY_HREF and KEY_ANCHOR keys
        $exclusionFiltersArray //array of strings : the objects whose KEY_HREF matches 1+ of these filters will be EXCLUDED from the function's final return
    ) {
        $resourcesNotMatchingAnyExclusiveFilter = array();

        foreach ($hs as $h) {
            $href = $h[self::KEY_HREF];

            $hrefMatchesAtLeastOneExclusionFilter = $this->mUtil->auxCheckIfStringMatchesSomeFilter(
                $href,
                $exclusionFiltersArray
            );

            if (!$hrefMatchesAtLeastOneExclusionFilter) {
                $resourcesNotMatchingAnyExclusiveFilter[] = $h;
            } //if
        } //foreach

        return $resourcesNotMatchingAnyExclusiveFilter;
    } //filterHyperlinksAsArrayOfPairsHrefAnchorFilterByExclusionFilters

    //--------------------------------------------------------------------------------
    /*
     * 2016-07-08
     * 2019-03-24 trying to fight situations where the anchor organically includes the link, but gets glued to it
     * e.g. July 1998 Miami ship fire, TFD local story, TV-13 news - YouTubehttps://www.youtube.com/watch?v=tSQCCBbD-WQ :
     * notice the "glue" between YouTube and https = "YouTubehttps"
     */
    public function representHyperlinksAsArrayOfPairsHrefAnchorToHtml(
        $hs,
        int $piStart = 1,
        bool $pbTrimAnchor = false
    ) {
        //https://developer.mozilla.org/en-US/docs/Web/HTML/Element/ol
        $m = "<ol start='$piStart'>"; //2019-03-21 support for start
        foreach ($hs as $h) {
            $c0 = (array_key_exists(self::KEY_HREF, $h) && array_key_exists(self::KEY_ANCHOR, $h));
            if ($c0) {
                $href = $h[self::KEY_HREF];
                $bStartsWithHttp = stripos($href, "http") === 0;

                $anchor = $pbTrimAnchor ? trim($h[self::KEY_ANCHOR]) : $h[self::KEY_ANCHOR];

                if ($bStartsWithHttp)
                    $m .= PHP_EOL . "<li>$anchor : <mark><br>
                        <a href='$href'>$href</a></mark></li>";
                else
                    $m .= "<li>$anchor : <mark>$href</mark></li>";
            } //if
            else {
                var_dump($h);
            } //else
        } //foreach
        $m .= "</ol>";
        return $m;
    } //representHyperlinksAsArrayOfPairsHrefAnchorToHtml

    //--------------------------------------------------------------------------------
    /*
     * 2019-11-11
     */
    public function representHyperlinksAsPlainHrefsToHtml(
        $hs,
        int $piStart = 1
    ) {
        //https://developer.mozilla.org/en-US/docs/Web/HTML/Element/ol
        $m = "<ol start='$piStart'>"; //2019-03-21 support for start
        foreach ($hs as $h) {
            $c0 = is_string($h);
            if ($c0) {
                $href = $h;
                $m .= "<li><mark>$href</mark></li>";
            } //if
        } //foreach
        $m .= "</ol>";
        return $m;
    } //representHyperlinksAsPlainHrefsToHtml

    //--------------------------------------------------------------------------------
    /*
    2018-07-08
    2021-04-14
    Cloud Buckets, via streams, do NOT support the LOCK_EX mode that regular files do, so added a param to control if one should $pbUseLockExclusive, defaulting to false
     */
    const DEFAULT_LOCK_EX = false;
    public function simpleDownloader(
        $pUrl,
        $pFileName,
        $pReferrer = self::DEFAULT_REFERRER,
        $pMethod = self::METHOD_GET,
        $pbUseLockExclusive = self::DEFAULT_LOCK_EX
    ) {
        /*
        $pTargetUrl, //URL of resource to consume or submit
        $pReferrer = false,
        $pMethod = self::METHOD_GET, //GET, POST or HEAD
        $pDataArray = false, //false or array of pairs name:value that will be considered in the request > will impact query string on GET or CURLOPT_POSTFIELDS on POST
        $pIncludeHeaders = false, //true or INCLUDE_HEADERS for headers inclusion , false or EXCLUDE_HEADERS for headers exclusion
        $pInjectCustomHttpRequestHeaders = false //false or array if wanting to inject custom http headers
         */
        $quartetBinStatusError = $this->http(
            $pUrl,
            $pReferrer,
            $pMethod
        );

        $bin = $quartetBinStatusError[self::KEY_BIN];
        $iErrorCode = $quartetBinStatusError[self::KEY_ERROR_CODE];
        $strErrorString = $quartetBinStatusError[self::KEY_ERROR_MESSAGE];
        $status = $quartetBinStatusError[self::KEY_STATUS];

        $contentType = $status['content_type'];
        $bThereIsSlash = stripos($contentType, "/") !== false;
        $fileType = $bThereIsSlash ? explode("/", $contentType)[1] : "";

        //2018-12-01 : deciding when to add a file type to the file name
        $bDoesTheImplicitExtensionIncludeTheFileType = false;
        $iRightMostDotAtOriginalFileName = strripos($pFileName, ".");

        if ($iRightMostDotAtOriginalFileName !== false) {
            $strExtensionIncludingTheDotImplicitInOriginalFileName =
                substr($pFileName, $iRightMostDotAtOriginalFileName);
            $bDoesTheImplicitExtensionIncludeTheFileType =
                stripos($strExtensionIncludingTheDotImplicitInOriginalFileName, $fileType) !== false;
        }

        if (!$bDoesTheImplicitExtensionIncludeTheFileType) {
            $filename = $pFileName . "." . $fileType; //only add a file type to the name if the extension part in the natural name does not already does it
        } else {
            $filename = $pFileName;
        }

        if ($iErrorCode === 0) {
            //2021-04-13
            if ($pbUseLockExclusive) {
                $bytesWritten = file_put_contents(
                    $filename,
                    $bin,
                    LOCK_EX //error with cloud buckets
                );
            } else {
                $bytesWritten = file_put_contents(
                    $filename,
                    $bin //,
                    //LOCK_EX //error with cloud buckets
                );
            }

            //return $bytesWritten;
            //2018-07-11
            $ret = array(
                self::KEY_FILE_DL => $filename,
                self::KEY_BYTES_WRITTEN => $bytesWritten,
                self::KEY_BIN => $quartetBinStatusError[self::KEY_BIN],
                self::KEY_STATUS => $quartetBinStatusError[self::KEY_STATUS],
                self::KEY_ERROR_CODE => $quartetBinStatusError[self::KEY_ERROR_CODE],
                self::KEY_ERROR_MESSAGE => $quartetBinStatusError[self::KEY_ERROR_MESSAGE]
            );
            return $ret;
        } else {
            return false;
        }
    } //simpleDownloader

    //--------------------------------------------------------------------------------
    /*
    2017-05-14
    receives an URL
    tries to connect to the URL, using CURL
    if successful, it returns true
    if not, it returns false when the reason is CURL related, or
    it returns a string with an error description, if the error is server side related
    NOTICE that no body is required or read
     */
    public function isUrlReachable(
        $pUrl,
        $pMethod = "GET",
        $pUserAgent = self::USER_AGENT_STRING_MOZ47 //2019-01-02 : should I consider using $this->mUserAgentSignature?
    ) {
        $ch = curl_init($pUrl);
        $e = curl_errno($ch);
        $eM = curl_error($ch);

        if ($ch !== false && $e === 0 && $eM === "") {
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);  // this works
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array("User-Agent: " . $pUserAgent)
            ); // request as if Firefox
            curl_setopt($ch, CURLOPT_NOBODY, true); //if only checking the availability of the URL, headers are enough
            //curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); //should be false, be if so, curl_exec might echo stuff
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); //to support redirection
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::DEFAULT_CURL_TIMEOUT);

            //try to request by GET
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_POST, false);
            $curlGetResult = curl_exec($ch);
            $eGet = curl_errno($ch);
            $eMGet = curl_error($ch);

            //try to request by POST
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPGET, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array());
            curl_setopt($ch, CURLOPT_VERBOSE, false);
            /*
                a badly formed POST request can make curl_exec output unexpected strings
                for example, if just testing Wordpress xmlrpc.php without proper data, you might get
                <?xml version="1.0" encoding="UTF-8"?>
                    <methodResponse>
                        <fault>
                        <value>
                            <struct>
                                <member>
                                    <name>faultCode</name>
                                    <value><int>-32700</int></value>
                                </member>
                                <member>
                                    <name>faultString</name>
                                    <value><string>parse error. not well formed</string></value>
                                </member>
                            </struct>
                        </value>
                        </fault>
                    </methodResponse>
             */
            $curlPostResult = curl_exec($ch);
            $ePost = curl_errno($ch);
            $eMPost = curl_error($ch);

            curl_close($ch);

            $curlResult = $curlGetResult || $curlPostResult;
            $errorMessages = "GET: $eMGet ($eGet); POST: $eMPost ($ePost)";

            return $curlResult ? true : $errorMessages; //if the return is a string, it is the error's description
        } //if

        return false; //can't create curl object
    } //isUrlReachable

    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.
    /*
     * 2018-05-14
     * 2019-01-02 - some name changes
     */
    public function findValuesForAttributeInSourceHtml(
        $pSourceText,
        $pAttribute = "src" //do NOT value as "src=" because that is done in the very first line of this implementation
    ) {
        $pAttribute = " $pAttribute="; //e.g. " src="
        $ret = [];
        $parts = explode($pAttribute, $pSourceText);
        $iHowManyParts = count($parts);
        for ($idx = 0; $idx < $iHowManyParts; $idx += 1) {
            if ($idx > 0) { //first part is always the HTML preamble, so ignore it
                $part = $parts[$idx]; //the part

                //identify which symbol closes the url, using a closed set of possibilities and finding the first one
                $arrayKeysSymbolPosIdentifyingTheSymbolWhichClosesTheUrl = Util::auxFirstOccurrenceOfOneOfTheFollowing(
                    $part,
                    ["\"", "\'", " ", "<", ">"]
                );

                //so the part starts at 0 and ends at the pos found above
                $delimiterSymbol = $arrayKeysSymbolPosIdentifyingTheSymbolWhichClosesTheUrl[Util::KEY_SYMBOL];
                $delimiterSymbolPosClosingPos = $arrayKeysSymbolPosIdentifyingTheSymbolWhichClosesTheUrl[Util::KEY_POS];
                $strExpectedToBeTheValueOfTheCurrentAttribute = substr($part, 0, $delimiterSymbolPosClosingPos);
                $strTrimmedValueForTheCurrentAttribute = trim($strExpectedToBeTheValueOfTheCurrentAttribute); //should not be necessary

                //2018-05-15 unsure
                //2019-01-02 rethought, renamed, yet again untested
                if ($delimiterSymbol !== " ") { //for any delimiter other than the white space
                    $iDesiredStartPosition = 0 + 1; //by starting at 1 one ignores the opening delimiter itself
                    $iDesiredLength = strlen($strTrimmedValueForTheCurrentAttribute) - 1 - 1; //to the original length, subtract 2 symbols, which are the opening and closing symbols

                    $strIsolatedAttributeValueWithoutDelimiters = substr(
                        $strTrimmedValueForTheCurrentAttribute,
                        $iDesiredStartPosition,
                        $iDesiredLength
                    );
                } //if

                if (!empty($strIsolatedAttributeValueWithoutDelimiters)) {
                    $bIsNewValueNotYetInReturn = array_search($part, $ret) === false;
                    if ($bIsNewValueNotYetInReturn)
                        $ret[] = $strIsolatedAttributeValueWithoutDelimiters; //avoid repetitions
                } //if
            } //if not part 0
        } //for every part
        return $ret;
    } //findValuesForAttributeInSourceHtml

    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.
    /**
     * 2019-04-30
     * @param $pStrPlainText
     * @return array
     */
    public static function findColonSlashSlashUrlsInPlainText(
        string $pStrPlainText
    ) {
        $strSplittingMark = "://";
        $ret = [];
        $parts = explode($strSplittingMark, $pStrPlainText);
        $iHowManyParts = count($parts);
        for ($idx = 0; $idx < $iHowManyParts; $idx += 1) {
            if ($idx > 0) { //first part is always the HTML preamble, so ignore it
                $part = $parts[$idx]; //the part

                //identify which symbol closes the url, using a closed set of possibilities and finding the first one
                $arrayKeysSymbolPosIdentifyingTheSymbolWhichClosesTheUrl = Util::auxFirstOccurrenceOfOneOfTheFollowing(
                    $part,
                    ['"', "'", " ", "<", ">", "\n", "\r"]
                );

                //the symbol is relevant to find the left part of the URL, available in the previous part, delimited starting by this same symbol
                $previousPart = $parts[$idx - 1];
                $strClosingSymbol = $arrayKeysSymbolPosIdentifyingTheSymbolWhichClosesTheUrl[Util::KEY_SYMBOL];
                $iLastOcurrenceOfTheClosingSymbolInThePreviousPart =
                    strripos($previousPart, $strClosingSymbol);
                $missingPartLeftInThePreviousPart =
                    substr($previousPart, $iLastOcurrenceOfTheClosingSymbolInThePreviousPart);

                //so the part starts at 0 and ends at the pos found above
                $part = substr($part, 0, $arrayKeysSymbolPosIdentifyingTheSymbolWhichClosesTheUrl[Util::KEY_POS]);
                $part = trim($part); //should not be necessary

                $fullUrl = $missingPartLeftInThePreviousPart . $strSplittingMark . $part;

                $fullUrl = strip_tags($fullUrl); //2019-03-17
                $fullUrl = str_replace("\r", "", $fullUrl);
                $fullUrl = str_replace("\n", "", $fullUrl);

                if (!empty($part)) {
                    $bNewPart = array_search($part, $ret) === false;
                    //if ($bNewPart) $ret[] = $part; //avoid repetitions
                    if ($bNewPart) $ret[] = $fullUrl; //avoid repetitions
                } //if
            } //if not part 0
        } //for every part
        return $ret;
    } //findColonSlashSlashUrlsInPlainText

    //_.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-._.-^-.
    /**
     * 2018-05-14
     * @param $pSourceTextHtml
     * @return array
     * 2019-05-05
     * identified situations where URLs found via this tool would have a starting " which would mess with tools next in the chain of processing
     */
    public function findColonSlashSlashUrlsInSourceHtml(
        $pSourceTextHtml
    ) {
        $strSplittingMark = "://";
        $ret = [];
        $parts = explode($strSplittingMark, $pSourceTextHtml);
        $iHowManyParts = count($parts);
        for ($idx = 0; $idx < $iHowManyParts; $idx += 1) {
            if ($idx > 0) { //first part is always the HTML preamble, so ignore it
                $part = $parts[$idx]; //the part

                //identify which symbol closes the url, using a closed set of possibilities and finding the first one
                $arrayKeysSymbolPosIdentifyingTheSymbolWhichClosesTheUrl = Util::auxFirstOccurrenceOfOneOfTheFollowing(
                    $part,
                    ['"', "'", " ", "<", ">"]
                );

                //the symbol is relevant to find the left part of the URL, available in the previous part, delimited starting by this same symbol
                $previousPart = $parts[$idx - 1];
                $strClosingSymbol = $arrayKeysSymbolPosIdentifyingTheSymbolWhichClosesTheUrl[Util::KEY_SYMBOL];
                $iLastOcurrenceOfTheClosingSymbolInThePreviousPart =
                    strripos($previousPart, $strClosingSymbol);
                $missingPartLeftInThePreviousPart =
                    substr($previousPart, $iLastOcurrenceOfTheClosingSymbolInThePreviousPart);

                //so the part starts at 0 and ends at the pos found above
                $part = substr($part, 0, $arrayKeysSymbolPosIdentifyingTheSymbolWhichClosesTheUrl[Util::KEY_POS]);
                $part = trim($part); //should not be necessary

                $fullUrl = $missingPartLeftInThePreviousPart . $strSplittingMark . $part;

                $fullUrl = strip_tags($fullUrl); //2019-03-17

                //fully eliminate " and ' from the URL
                $fullUrl = str_replace("\"", "", $fullUrl); //2019-05-05
                $fullUrl = str_replace("''", "", $fullUrl); //2019-05-05

                if (!empty($part)) {
                    $bNewPart = array_search($part, $ret) === false;
                    //if ($bNewPart) $ret[] = $part; //avoid repetitions
                    if ($bNewPart) $ret[] = $fullUrl; //avoid repetitions
                } //if
            } //if not part 0
        } //for every part
        return $ret;
    } //findColonSlashSlashUrlsInSourceHtml

    //--------------------------------------------------------------------------------
    /*
     * 2018-05-20
     * receives a type of element, e.g. "li"
     * receives an attribute name, e.g. "class"
     * receives a value for the attribute with name, e.g. "ball"
     * returns the elements of the type, having the attribute, valued at the value
     */
    public function getDOMElementsMatchingTrioTypeAttributeValue(
        $pHtmlSourceCode,
        $pSomeElementType, //e.g. "li", for looking into list items
        $pWantedAttribute, //e.g. "class",
        $pWantedValue = "*" //e.g. "ball" for class="ball"
    ) {
        $retArrayOfDOMElementsMatchingCriteria = [];
        $htmlDOM = new \DOMDocument();

        /*
         * use the @
         * or, for many HTML documents, you'll get stuff like
         * PHP Warning:  DOMDocument::loadHTML(): Tag animate invalid in Entity, ...
         */
        $loadResult = @$htmlDOM->loadHTML($pHtmlSourceCode);

        if ($loadResult === true) {
            //example of how to iterate the childNode, but such iteration is usually useless
            /*
            $children = $htmlDOM->childNodes; //2 child
            foreach ($children as $c){
                var_dump ($c);
            }
            */

            $DOMNodeList_representingAnArrayOf_DOMElement_objectsOfTheParamType =
                $htmlDOM->getElementsByTagName($pSomeElementType);

            foreach ($DOMNodeList_representingAnArrayOf_DOMElement_objectsOfTheParamType
                as
                $DOMElement_representingOneElementOfTheRequestedType) {
                //var_dump ($DOMElement_representingOneElementOfTheRequestedType);
                $elementHasAttributes =
                    $DOMElement_representingOneElementOfTheRequestedType->hasAttributes();

                if ($elementHasAttributes) {
                    //there is no getAllAttributes
                    $strAttributeValue =
                        $DOMElement_representingOneElementOfTheRequestedType->getAttribute($pWantedAttribute);

                    $bAttributeExistsAndHasValue = $strAttributeValue !== "";
                    if ($bAttributeExistsAndHasValue) {
                        //2021-04-13
                        $bAcceptAnyValue = strcasecmp($pWantedValue, "*") === 0;

                        $bValueMatchesWantedValue =
                            $bAcceptAnyValue || //2021-04-13
                            strcasecmp($strAttributeValue, $pWantedValue) === 0;

                        if ($bValueMatchesWantedValue) {
                            $retArrayOfDOMElementsMatchingCriteria[] = $DOMElement_representingOneElementOfTheRequestedType;
                        } //if attribute has desired value
                    } //if attribute exists
                } //if element has attributes
            } //foreach element of the type
        } //if could load DOMDocument from source code
        return $retArrayOfDOMElementsMatchingCriteria;
    } //getDOMElementsMatchingTrioTypeAttributeValue

    //--------------------------------------------------------------------------------
    /*
     * 2019-03-04 was test_getHyperlinksAsPairsAnchorHrefContainedInDOMElements
     * 2019-03-06 renamed to "getRecursiveHyperlinksAsPairsAnchorHrefContainedInDOMElements"
     */
    public function getRecursiveHyperlinksAsPairsAnchorHrefContainedInDOMElements(
        array $paDOMElements,
        bool $pbNormalize = true
    ) {
        $ret = [];

        foreach ($paDOMElements as /*DOMElement*/ $element) {
            //a XML representation of the element, since var_dump and print_r seem to have problems with these objects
            //NOT IN USE
            if ($element->ownerDocument != null) {
                $xmlForElement = $element->ownerDocument->saveXML($element); //https://secure.php.net/manual/en/class.domelement.php
            }

            $aDOMNodeList_children = $element->childNodes;
            $bChildHasChildNodes = $element->childNodes !== null;
            $iHowManySubNodesOrChildrenContainedInTheCurrentNode = $aDOMNodeList_children->length; //count would also do the job

            /*
             * about DOMNode
             * https://secure.php.net/manual/en/class.domnode.php
             */
            $aStrListOfTagsForChildNodes = [];
            foreach ($aDOMNodeList_children as /* DOMNode */ $DOMNode_child) {
                //2019-03-24
                if ($pbNormalize) $DOMNode_child->normalize(); //remove empty text nodes and merge adjacent text nodes in this node and all its children. No value is returned.

                //recursively search for as inside every element with a tag
                $bNodeHasTag = @$DOMNode_child->tagName !== null; //to avoid records causing "PHP Notice:  Undefined property: DOMText::$tagName"
                if ($bNodeHasTag) {
                    $strTagName = $DOMNode_child->tagName;
                    $aStrListOfTagsForChildNodes[] = $strTagName; //only for the discovery of tags

                    $aPairsInChildWithTag = $this->getRecursiveHyperlinksAsPairsAnchorHrefContainedInDOMElements(
                        [$DOMNode_child] //notice the array notation
                    );

                    //add the pairs eventuallly found in child nodes to the final return
                    foreach ($aPairsInChildWithTag as $pair) $ret[] = $pair;
                } //if the child node has tag
            } //foreach element contained in the current DOM element being processed

            //var_dump ($aStrListOfTagsForChildNodes);

            //analyzing the current element itself
            $bNodeHasTag = $element->tagName !== null;
            if ($bNodeHasTag) {
                $strTagName = $element->tagName;

                $bIsAnchorElement = strcasecmp(
                    $strTagName,
                    "a"
                ) === 0;

                if ($bIsAnchorElement) {
                    $anchor = $element->nodeValue;
                    $href = $element->getAttribute('href');

                    $pair = [
                        self::KEY_ANCHOR => $anchor,
                        self::KEY_HREF => $href
                    ];

                    $ret[] = $pair;
                } //if the current element is an anchor
            } //if the child node has tag
        } //for every received DOM Element

        return $ret;
    } //getRecursiveHyperlinksAsPairsAnchorHrefContainedInDOMElements

    //--------------------------------------------------------------------------------
    /*
     * 2019-03-07
     * filter and expand URLs
     */
    public function filterAbsoluteAndOrExpandUrlsAsPairsAnchorHref(
        $paAllPairsAnchorHrefToBeFiltered,
        $pbAbsolutePairsOnly = true,
        $pbExpandKnownUrls = true
    ) {
        $ret = [];
        if ($pbAbsolutePairsOnly) {
            $paAllPairsAnchorHrefToBeFiltered = $aPairsWithAbsoluteHref =
                $this->auxFilterInAbsolutePairsOnly(
                    $paAllPairsAnchorHrefToBeFiltered
                );
        }

        if ($pbExpandKnownUrls) {
            foreach ($paAllPairsAnchorHrefToBeFiltered as $aPair) {
                $strAnchor = $aPair[self::KEY_ANCHOR];
                $strHref = $aPair[self::KEY_HREF];

                $bIsAdFly = stripos($strHref, "://adf.ly") !== false || stripos($strHref, "://ay.gy") !== false;
                $bIsGooGl = stripos($strHref, "://goo.gl") !== false;

                $strDecryptedUrl = $strHref;

                if ($bIsAdFly) {
                    $strDecryptedUrl = $this->unshortAyDotGyUrl($strHref);
                } //if adf.ly or ay.gy

                //2020-11-07
                if ($bIsGooGl) {
                    $strDecryptedUrl = self::expandGooglUrl($strHref);
                } //if goo.gl

                /*
                $bOriginalUrlIsAbsolute = $this->auxIsAbsoluteUrl($strHref); //result currently not in use - filtering is being done at the start!
                $bDecryptedUrlIsAbsoluteUrl = $this->auxIsAbsoluteUrl($strDecryptedUrl); //result currently not in use - filtering is being done at the start!
                */

                $aPair[self::KEY_HREF] = $strDecryptedUrl;

                $ret[] = $aPair;
            } //foreach URL to expand
        } //if to expand know shortened URLs
        else {
            $ret[] = $paAllPairsAnchorHrefToBeFiltered;
        } //do NOT expand known shortened URLs

        return $ret;
    } //filterAbsoluteAndOrExpandUrlsAsPairsAnchorHref

    //--------------------------------------------------------------------------------
    /*
     * 2019-03-04
     * receives an arrays of pairs anchor+href
     * returns the pairs which have an absolute href, filtering out relative urls
     */
    public function auxFilterInAbsolutePairsOnly(
        $aPairsAnchorHref
    ) {
        $aRetPairsWithHrefsThatAreAbsoluteUrls = [];

        foreach ($aPairsAnchorHref as $aPair) {
            $strAnchor = $aPair[self::KEY_ANCHOR];
            $strHref = $aPair[self::KEY_HREF];

            $bHrefIsAbsoluteUrl = $this->auxIsAbsoluteUrl($strHref);

            if ($bHrefIsAbsoluteUrl) {
                $aRetPairsWithHrefsThatAreAbsoluteUrls[] = $aPair;
            } //if an absolute href was found
        } //foreach pair
        return $aRetPairsWithHrefsThatAreAbsoluteUrls;
    } //auxFilterInAbsolutePairsOnly

    //----------------------------------------------------------------------------
    /*
     * 2019-03-06
     */
    public function auxIsAbsoluteUrl(
        $pStrUrl
    ) {
        $regExpForAbsUrl = self::REGEXP_ABS_URL;
        $regExpForAbsUrlReadyForPHP = "~$regExpForAbsUrl~";

        //ret is 1 if matches, 0 if does NOT match, FALSE if error
        $iOneOrZeroOrFalse = preg_match($regExpForAbsUrlReadyForPHP, $pStrUrl, $aMatches);

        $bHrefIsAbsoluteUrl = $iOneOrZeroOrFalse === 1;

        return $bHrefIsAbsoluteUrl;
    } //auxIsAbsoluteUrl

    //----------------------------------------------------------------------------
    /*
     * 2019-03-06
     * decrypts URLs like
     * http://ay.gy/17316377/_eaHR0cHM6Ly9vcGVubG9hZC5jby9mL0tZWU05QThmWUxRL1NhYmJ5LUNhc3NpYV9mb3JfR2VuZXNpc184LnJhcg==
     * which are, in truth, adf.ly URLs
     * using a CMD
     */
    const CMD_DECRYPT_ADFLY = "\"c:\\python3x\\python3.exe\" \"C:\\wp\\inet\\linkdecrypt\\LinkDecrypt\main.py\"";
    public function unshortAyDotGyUrl(
        $pUrl
    ) {
        $pUrl = str_replace("://ay.gy/", "://adf.ly/", $pUrl);
        $cmd = self::CMD_DECRYPT_ADFLY . " \"$pUrl\"";
        $strOutput = exec($cmd, $aEveryLineOfOuput);
        return $strOutput;
    } //unshortAyDotGyUrl

    //--------------------------------------------------------------------------------
    /*
     * 2019-03-21
     * https://github.com/N0taN3rd/userAgentLists
     */
    const USER_AGENT_STRING_FILES_KEYS = ["android", "chrome", "firefox", "ie", "opera", "safari", "tp"];

    //these paths must be relative to the root of the project; ie, relative to getcwd
    const USER_AGENT_STRING_FILES = [
        "android" => "./user_agent_strings_json/android-browser.json",
        "chrome" => "./user_agent_strings_json/chrome.json",
        "firefox" => "./user_agent_strings_json/firefox.json",
        "ie" => "./user_agent_strings_json/internet-explorer.json",
        "opera" => "./user_agent_strings_json/opera.json",
        "safari" => "./user_agent_strings_json/safari.json",
        "tp" => "./user_agent_strings_json/techpatterns_com_useragentswitcher.json" //,
        //"all" => "./user_agent_strings_json/ua_org_allagents.json"
    ];

    //for a situation of file not found, getting rid of the . did NOT solve the issue
    /*
    const USER_AGENT_STRING_FILES = [
        "android" => "user_agent_strings_json/android-browser.json",
        "chrome" => "user_agent_strings_json/chrome.json",
        "firefox" => "user_agent_strings_json/firefox.json",
        "ie" => "user_agent_strings_json/internet-explorer.json",
        "opera" => "user_agent_strings_json/opera.json",
        "safari" => "user_agent_strings_json/safari.json",
        "tp" => "user_agent_strings_json/techpatterns_com_useragentswitcher.json" //,
        //"all" => "./user_agent_strings_json/ua_org_allagents.json"
    ];
    */

    public function randomUserAgentString()
    {
        $iHowManyFiles = count(self::USER_AGENT_STRING_FILES);
        $iRandomIndex = rand(0, $iHowManyFiles - 1);
        $iRandomKey = self::USER_AGENT_STRING_FILES_KEYS[$iRandomIndex];
        //$strPathChosenPath = self::USER_AGENT_STRING_FILES[$iRandomIndex];
        $strPathChosenFile = self::USER_AGENT_STRING_FILES[$iRandomKey];

        //https://www.php.net/manual/en/language.constants.predefined.php
        //$strPathBasedOnCWD = getcwd()."/".$strPathChosenFile; //2020-06-26
        $strPathBasedOnCWD = __DIR__ . "/" . $strPathChosenFile; //2020-06-26
        //$strRealPath = realpath($strPathChosenFile); //will return false
        $strRealPath = realpath($strPathBasedOnCWD); //will work

        $strFileContents = file_get_contents($strRealPath);
        $aUserAgentStringsIndexedByNumber = json_decode($strFileContents, true);

        $iHowManyUAS = count($aUserAgentStringsIndexedByNumber);
        $iRandomIndexForUA = rand(0, $iHowManyUAS);
        $aChosenUA = $aUserAgentStringsIndexedByNumber[$iRandomIndexForUA];
        $strUA = $aChosenUA['ua'];
        return $strUA;
    } //randomUserAgentString

    /*
     * 2021-04-13
     */
    public static function auxbCheckCurlQuartet(
        $paCurlQuartet
    ) {
        $bc0 = is_array($paCurlQuartet);
        $bc1 = $bc0 && array_key_exists(HttpHelper::KEY_BIN, $paCurlQuartet);
        $bc2 = $bc0 && array_key_exists(HttpHelper::KEY_STATUS, $paCurlQuartet);
        $bc3 = $bc0 && array_key_exists(HttpHelper::KEY_ERROR_CODE, $paCurlQuartet);
        $bc4 = $bc0 && array_key_exists(HttpHelper::KEY_ERROR_MESSAGE, $paCurlQuartet);
        return $bc1 && $bc2 && $bc3 && $bc4;
    }
    public static function extractAsFromHtml(
        string $pStrHtml
    ) {
        $ret = [];
        $doc = new \DOMDocument();
        if ($doc) {
            $bTrueOrFalse = @$doc->loadHTML($pStrHtml);
            if ($bTrueOrFalse) {
                $as = $doc->getElementsByTagName('a');
                foreach ($as as $a) {
                    $href = $a->getAttribute('href');
                    $anchor = $a->nodeValue;
                    //$ret[] = ['href'=>$href, 'anchor'=>$anchor]; 
                    $bNew = !array_key_exists($href, $ret);
                    if ($bNew) $ret[$href] = $anchor;
                }
            }
        }
        return $ret;
    } //extractAsFromHtml 

    public static function getTitleAndUrl($pArrUrls)
    {
        unset($pArrUrls[HttpHelper::BENFICA_UNSET_URL]);
        $arr = [];
        foreach ($pArrUrls as $value => $val) {

            if (str_starts_with($value, HttpHelper::BENFICA_URL)) {

                //$newVal = str_replace("Futebol","",$val);
                $valFinal = str_replace(substr(strrchr($val, "."), 1), "", $val);
                //echo $valFinal."<br>";
                //echo $value."<br>";
                $arr[$valFinal] = $value;
            } else {
                echo "";
            }
        }
        return $arr;
    }
}//HttpHelper
