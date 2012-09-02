<?php
/*
* TMDB API PHP class - Wrapper to themoviedb.org API version 3
*
* @pakage TMDB_API_PHP
* @author Giuliano Riboni <giuliano@riboni.com.br>
* @copyright 2012 Giuliano Riboni
* @date 2012-08-02
* @version 1.0.0
*
*/
class TMDB{
  //Variables;
  var $TMDBVersion           = '1.0.0';
  var $TMDBUrl               = 'http://api.themoviedb.org/3/';
  var $TMDB_API_Version      = 'v3';
  var $defaultLanguage       = 'en';
  var $TMDBMovieUrl          = 'http://www.themoviedb.org/movie/';
  var $IMDBMovieUrl          = 'http://www.imdb.com/title/';
  var $hideAdultContent      = true;
  var $requestCurrentAttempt = 0;
  var $requestMaxAttempt     = 10;
  var $curlTimeoutSeconds    = 10;
  var $voteAvarageMinimum    = 6.0;
  var $randomMaxMovieId      = 1000;
  var $lastErrorNumber       = false;
  var $lastErrorMessage      = false;
  var $lastErrorInfo         = false;
  var $lastResult            = false;
  var $saveDebug             = false;
  var $hasError              = false;
  var $denyLanguage          = false;
  var $debugLinks            = array();
  var $debugResults          = array();
  var $tryAgainHttpError     = array('403' => 'Forbidden', '408' => 'Request Timeout', '429' => 'Too Many Requests', '444' => 'No Response', '499' => 'Client Closed Request', '500' => 'Internal Server Error', '503' => 'Service Unavailable', '504' => 'Gateway Timeout', '598' => 'Network read timeout error', '599' => 'Network connect timeout error');
  var $tmdbHttpError         = array('TIMEOUT' => '408');
  var $debugString           = '';
  var $apikey;
  var $language;
  var $imgUrl;
  var $currentPage;
  var $totalPages;
  var $totalResults;
  var $results;

  //Constructor;
  function TMDB($apikey) {
    //Save the api key;
    $this -> setApikey( $apikey );
    //Set the default language;
    $this -> setLanguage( $this -> defaultLanguage );
    //Make the basic configuration;
    $this -> makeConfiguration();
  }

  //Debug Methods;
  function saveDebug(){
    $this -> saveDebug = true;
  }

  function makeDebug(){
    if( $this -> saveDebug === true ){
      if( is_array( $this -> debugLinks ) && sizeof( $this -> debugLinks ) > 0 && is_array( $this -> debugResults ) && sizeof( $this -> debugResults ) > 0 ){
        $this -> debugString  = '<table cellspacing="2" cellpadding="2" border="1" bgcolor="#FFFFFF">'."\n";
        $this -> debugString .= '<tr>'."\n";
        $this -> debugString .= '  <td colspan="2"><b>TMDB: Debug<b></td>'."\n";
        $this -> debugString .= '</tr>'."\n";
        $count = 0;
        foreach($this -> debugLinks as $k => $v){
          if( $count % 2 == 0 ){
            $this -> debugString .= '<tr>'."\n";
          }else{
            $this -> debugString .= '<tr bgcolor="#CCCCCC">'."\n";
          }
          $this -> debugString .= '  <td valign="top">'.$this -> debugLinks[$k].'</td>'."\n";
          $this -> debugString .= '  <td><pre>'.print_r($this -> debugResults[$k], true).'</pre></td>'."\n";
          $this -> debugString .= '</tr>'."\n";
          $count++;
        }
        $this -> debugString .= '</table>'."\n";
        return true;
      }else{
        return false;
      }
    }else{
      return false;
    }
  }

  function getDebug(){
    $this -> makeDebug();
    return $this -> debugString;
  }

  function showDebug(){
    echo $this -> getDebug();
  }

  //Error Methods;
  function hasError(){
    return $this -> hasError;
  }

  function getError(){
    $error                     = array();
    $error['ERROR']            = array();
    $error['ERROR']['CODE']    = $this -> lastErrorNumber;
    $error['ERROR']['MESSAGE'] = $this -> lastErrorMessage;
    $error['ERROR']['INFO']    = $this -> lastErrorInfo;
    $error['RESULT']           = $this -> lastResult;
    return $error;
  }

  //Basic Class Methods;
  function getLastResult(){
    return $this -> lastResult;
  }

  function setApikey($apikey){
    $this -> apikey = (string) $apikey;
  }

  function getApikey(){
    return $this -> apikey;
  }

  function getApiUrl(){
    return $this -> TMDBUrl;
  }

  function setLanguage($language){
    $this -> language = $language;
  }

  function getLanguage(){
    return $this -> language;
  }

  function sendLanguage($v){
    if( $v == false ){
      $this -> denyLanguage = true;
    }else{
      $this -> denyLanguage = false;
    }
  }

  function getImageURL($currentImage = false, $size = false){
    if( $size === false ){
      $size = 'original';
    }
    if( $currentImage === false ){
      return $this -> configuration['images']['base_url'].$size.'/';
    }else{
      if( $currentImage != '' ){
        return $this -> configuration['images']['base_url'].$size.$currentImage;
      }else{
        return $this -> getImageURL();
      }
    }
  }

  function showAdultContent(){
    $this -> hideAdultContent = false;
  }

  function hideAdultContent(){
    $this -> hideAdultContent = true;
  }

  function currentPage(){
    return $this -> currentPage;
  }

  function totalPages(){
    return $this -> totalPages;
  }

  function totalResults(){
    return $this -> totalResults;
  }

  function results(){
    return $this -> results;
  }

  function setRequestMaxAttempt($v){
    $this -> requestMaxAttempt = (int) $v;
  }

  function setVoteAvarageMinimum($v){
    $this -> voteAvarageMinimum = (float) $v;
  }

  function formatString($value){
    return utf8_decode( $value );
  }

  function parseQuery($query){
    return urlencode( $query );
  }

  function makeTmdbLink($movieId){
    return $this -> TMDBMovieUrl.$movieId;
  }

  function makeImdbLink($movieImdbId){
    return $this -> IMDBMovieUrl.$movieImdbId;
  }

  function getHttpCode(){
    if( isset( $this -> lastErrorInfo['http_code'] ) ){
      return $this -> lastErrorInfo['http_code'];
    }else{
      return false;
    }
  }

  function setHttpCode($errorCode){
    if( isset( $this -> tmdbHttpError[ $errorCode ] ) ){
      $this -> lastErrorInfo['http_code'] = $this -> tmdbHttpError[ $errorCode ];
    }
  }

  function setCurlTimeoutSeconds($v){
    $this -> curlTimeoutSeconds = $v;
  }

  function callMethod($methodName, $methodRaw = false, $parameters = false){
    $method = $methodName;
    if( $methodRaw !== false ){
      $method .= '/'.$methodRaw;
    }
    return $this -> _call($method, $parameters);
  }

  function callObjectMethod($methodObject, $objectId, $methodRaw = false, $parameters = false){
    $method = $methodObject.'/'.$objectId;
    if( $methodRaw !== false ){
      $method .= '/'.$methodRaw;
    }
    return $this -> _call($method, $parameters);
  }

  function _makeListReturn($APIReturn, $type){
    //Save the current page;
    if( isset( $APIReturn['page'] ) ){
      $this -> currentPage = $APIReturn['page'];
    }else{
      $this -> currentPage = 1;
    }
    //Save the total page;
    if( isset( $APIReturn['total_pages'] ) ){
      $this -> totalPages = $APIReturn['total_pages'];
    }else{
      $this -> totalPages = 1;
    }
    //Save the total results;
    if( isset( $APIReturn['total_results'] ) ){
      $this -> totalResults = $APIReturn['total_results'];
      //Make the results;
      $objectList = array();
      if( is_array( $APIReturn['results'] ) && sizeof( $APIReturn['results'] ) > 0 ){
        foreach($APIReturn['results'] as $k => $v){
          if( $type == 'MOVIE' ){
            $object = new TMDBMovie( $APIReturn['results'][ $k ] );
          }else if( $type == 'PERSON' ){
            $object = new TMDBPerson( $APIReturn['results'][ $k ] );
          }else if( $type == 'COMPANY' ){
            $object = new TMDBCompany( $APIReturn['results'][ $k ] );
          }
          $objectList[] = $object;
        }
      }
      $this -> results = $objectList;
      return true;
    }else{
      $this -> totalResults = 0;
      $this -> results      = false;
      return false;
    }
  }

  //TMDB API Basic Methods;
  function makeConfiguration(){
    $configurationData = $this -> getConfig();
    if( empty( $configurationData ) ){
      die("Unable to read configuration, verify that the API key is valid, or there's an connection problem!");
    }else{
      //Save the configuration array;
      $this -> configuration = $configurationData;
    }
  }

  function getConfig(){
    return $this -> callMethod('configuration');
  }

  function _call($method, $parameters = false){
    //Make the url;
    $url = $this -> getApiUrl().$method."?api_key=".$this -> getApikey();
    //Set the language;
    if(  $this -> denyLanguage === false ){
      $url .= "&language=".$this -> getLanguage();
    }
    //Show the adult content;
    if( $this -> hideAdultContent === false ){
      $url .= "&include_adult=true";
    }
    //Set the parameters;
    if( is_array( $parameters ) ){
      foreach($parameters as $k => $v){
        $url .= '&'.$k.'='.$v;
      }
    }
    //Debug;
    if( $this -> saveDebug == true ){
      $this -> debugLinks[] = $url;
    }
    //Increment the current attempt;
    $this -> requestCurrentAttempt++;
    //Run the curl;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, $this -> curlTimeoutSeconds);
    //Get the result;
    $result = curl_exec($ch);
    //Save the return data;
    $this -> lastErrorNumber  = curl_errno($ch);
    $this -> lastErrorMessage = curl_error($ch);
    $this -> lastErrorInfo    = curl_getinfo($ch);
    $this -> lastResult       = $result;
    //Validate if we got any error;
    if( $this -> lastErrorNumber != 0 ){
      //Check if we need to try again;
      if( $this -> lastErrorNumber == 28 || array_key_exists($this -> getHttpCode(), $this -> tryAgainHttpError) ){
        if( $this -> requestCurrentAttempt <= $this -> requestMaxAttempt ){
          return $this ->  _call($method, $parameters);
        }else{
          $this -> setHttpCode('TIMEOUT');
        }
      }
      //Reset the current attempt;
      $this -> requestCurrentAttempt = 0;
      $this -> hasError              = true;
    }else{
      //Reset the current attempt;
      $this -> requestCurrentAttempt = 0;
    }
    //Close the curl;
    curl_close($ch);
    //Decode the result;
    $result = json_decode($result, true);
    //Debug;
    if( $this -> saveDebug == true ){
      if( is_array( $result ) ){
        $this -> debugResults[] = $result;
      }else{
        $this -> debugResults[] = $this -> getError();
      }
    }
    return (array) $result;
  }

  //Movies Methods;
  function movieDetail($movieId, $loadAll = false, $loadCastAndCrew = false, $loadTrailers = false, $loadImages = false, $loadTranslations = false, $loadAlternativeTitles = false, $loadKeywords = false, $loadReleasesInfo = false, $loadCollection = false, $loadProductionCompanies = false){
    $movieBasicInfo = $this -> movieInfo($movieId);
    if( is_array( $movieBasicInfo ) && sizeof($movieBasicInfo  ) > 0 ){
      //Avoid the return of adult movies if we don't whant to show adult content;
      if( $this -> hideAdultContent === true && $movieBasicInfo['adult'] == true ){
        return false;
      }
      //Create the object;
      $movieObject = new TMDBMovie($movieBasicInfo, true);
      //Load some movie values;
      $loadedValue = $this -> loadGenres( $movieBasicInfo['genres'] );
      $movieObject -> setGenres( $loadedValue );
      $loadedValue = $this -> loadCollection( $movieBasicInfo['belongs_to_collection'] );
      $movieObject -> setCollection( $loadedValue );
      $loadedValue = $this -> loadProductionCompanies($movieBasicInfo['production_companies'], $loadProductionCompanies);
      $movieObject -> setProductionCompanies( $loadedValue );
      $loadedValue = $this -> loadProductionCountries( $movieBasicInfo['production_countries'] );
      $movieObject -> setProductionCountries( $loadedValue );
      $loadedValue = $this -> loadSpokenLanguages( $movieBasicInfo['spoken_languages'] );
      $movieObject -> setSpokenLanguages( $loadedValue );
      //Load the cast and crew;
      if( $loadAll === true || $loadCastAndCrew === true ){
        $castAndCrew = $this -> movieCastAndCrew($movieId);
        $movieObject -> setCast( $castAndCrew['CAST'] );
        $movieObject -> setCrew( $castAndCrew['CREW'] );
      }
      //Load the traillers;
      if( $loadAll === true || $loadTrailers === true ){
        $trailers = $this -> movieTrailers($movieId);
        $movieObject -> setTrailers($trailers['YOUTUBE'], 'YOUTUBE');
        $movieObject -> setTrailers($trailers['QUICKTIME'], 'QUICKTIME');
      }
      //Load the images;
      if( $loadAll === true || ( $loadImages === true || $loadImages === 'ALL' ) ){
        $images = $this -> movieImages($movieId, $loadImages);
        $movieObject -> setImages($images['BACKDROPS'], 'BACKDROPS');
        $movieObject -> setImages($images['POSTERS'], 'POSTERS');
      }
      //Load the translations;
      if( $loadAll === true || $loadTranslations === true ){
        $translations = $this -> movieTranslations($movieId);
        $movieObject -> setTranslations( $translations['TRANSLATIONS'] );
      }
      //Load the alternative titles;
      if( $loadAll === true || $loadAlternativeTitles === true ){
        $alternativeTitles = $this -> movieAlternativeTitles($movieId);
        $movieObject -> setAlternativeTitles( $alternativeTitles['TITLES'] );
      }
      //Load the keywords;
      if( $loadAll === true || $loadKeywords === true ){
        $keywords = $this -> movieKeywords($movieId);
        $movieObject -> setKeywords( $keywords['KEYWORDS'] );
      }
      //Load the Release Info;
      if( $loadAll === true || $loadReleasesInfo === true ){
        $releasesInfo = $this -> movieReleaseInfo($movieId);
        $movieObject -> setReleases( $releasesInfo['RELEASES_INFO'] );
      }
      //Load the collectio details;
      if( $loadAll === true || $loadCollection === true ){
        if( $movieObject -> hasCollection() ){
          $collectionRawObject = $movieObject -> getCollection();
          $collectionObject = $this -> collectionDetail($collectionRawObject -> getId(), true);
          if( is_object( $collectionObject ) ){
            $movieObject -> setCollection( $collectionObject );
          }
        }
      }
      return $movieObject;
    }else{
      return false;
    }
  }

  function movieInfo($movieId, $methodRaw = false, $parameters = false){
    return $this -> callObjectMethod('movie', $movieId, $methodRaw, $parameters);
  }

  function searchMovie($query, $page = false){
    $parameters = array();
    //Make the query;
    $parameters['query'] = $this -> parseQuery( $query );
    //Set the page;
    if( $page !== false ){
      $parameters['page'] = $page;
    }
    //Call the API;
    $APIReturn = $this -> callMethod('search', 'movie', $parameters);
    //Make the movie list return;
    return $this -> _makeListReturn($APIReturn, 'MOVIE');
  }

  function searchSimilarMovie($movieId, $page = false){
    $parameters = array();
    //Set the page;
    if( $page !== false ){
      $parameters['page'] = $page;
    }
    //Call the API;
    $APIReturn = $this -> movieInfo($movieId, 'similar_movies', $parameters);
    //Make the movie list return;
    return $this -> _makeListReturn($APIReturn, 'MOVIE');
  }

  function movieCastAndCrew($movieId){
    $castAndCrew = $this -> movieInfo($movieId, 'casts');
    $returnArray = array('CAST' => false, 'CREW' => false);
    if( isset( $castAndCrew['cast'] ) ){
      $returnArray['CAST'] = $this -> loadCast( $castAndCrew['cast'] );
    }
    if( isset( $castAndCrew['crew'] ) ){
      $returnArray['CREW'] = $this -> loadCrew( $castAndCrew['crew'] );
    }
    return $returnArray;
  }

  function movieTranslations($movieId){
    $translations = $this -> movieInfo($movieId, 'translations');
    $returnArray = array('TRANSLATIONS' => false);
    if( isset( $translations['translations'] ) ){
      $returnArray['TRANSLATIONS'] = $this -> loadTranslations( $translations['translations'] );
    }
    return $returnArray;
  }

  function movieAlternativeTitles($movieId){
    $alternativeTitles = $this -> movieInfo($movieId, 'alternative_titles');
    $returnArray = array('TITLES' => false);
    if( isset( $alternativeTitles['titles'] ) ){
      $returnArray['TITLES'] = $this -> loadAlternativeTitles( $alternativeTitles['titles'] );
    }
    return $returnArray;
  }

  function movieKeywords($movieId){
    $keywords = $this -> movieInfo($movieId, 'keywords');
    $returnArray = array('KEYWORDS' => false);
    if( isset( $keywords['keywords'] ) ){
      $returnArray['KEYWORDS'] = $this -> loadKeywords( $keywords['keywords'] );
    }
    return $returnArray;
  }

  function movieReleaseInfo($movieId){
    $releases = $this -> movieInfo($movieId, 'releases');
    $returnArray = array('RELEASES_INFO' => false);
    if( isset( $releases['countries'] ) ){
      $returnArray['RELEASES_INFO'] = $this -> loadReleasesInfo( $releases['countries'] );
    }
    return $returnArray;
  }

  function movieTrailers($movieId){
    $trailers = $this -> movieInfo($movieId, 'trailers');
    $returnArray = array('YOUTUBE' => false, 'QUICKTIME' => false);
    if( isset( $trailers['youtube'] ) ){
      $returnArray['YOUTUBE'] = $this -> loadTrailers($trailers['youtube'], 'YOUTUBE');
    }
    if( isset( $trailers['quicktime'] ) ){
      $returnArray['QUICKTIME'] = $this -> loadTrailers($trailers['quicktime'], 'QUICKTIME');
    }
    return $returnArray;
  }

  function movieImages($movieId, $showAllLanguages = false){
    if( $showAllLanguages = 'ALL' ){
      $this -> sendLanguage(false);
    }
    $images = $this -> movieInfo($movieId, 'images');
    if( $showAllLanguages = 'ALL' ){
      $this -> sendLanguage(true);
    }
    $returnArray = array('BACKDROPS' => false, 'POSTERS' => false);
    if( isset( $images['backdrops'] ) ){
      $returnArray['BACKDROPS'] = $this -> loadImages($images['backdrops'], 'BACKDROPS');
    }
    if( isset( $images['posters'] ) ){
      $returnArray['POSTERS'] = $this -> loadImages($images['posters'], 'POSTERS');
    }
    return $returnArray;
  }

  function getLatestMovie($loadAll = false, $loadCastAndCrew = false, $loadTrailers = false, $loadImages = false, $loadTranslations = false, $loadAlternativeTitles = false, $loadKeywords = false, $loadReleasesInfo = false, $loadCollection = false, $loadProductionCompanies = false){
    //Call the API;
    $APIReturn = $this -> callMethod('latest', 'movie');
    if( isset($APIReturn['id']) && $APIReturn['id'] != '' ){
      $movieObject = $this -> movieDetail($APIReturn['id'], $loadAll, false, $loadTrailers, $loadImages, $loadTranslations, $loadAlternativeTitles, $loadKeywords, $loadReleasesInfo, $loadCollection, $loadProductionCompanies);
      if( is_object( $movieObject ) ){
        return $movieObject;
      }else{
        return false;
      }
    }else{
      return false;
    }
  }

  function getLatestMovieId(){
    //Call the API;
    $APIReturn = $this -> callMethod('latest', 'movie');
    if( isset($APIReturn['id']) && $APIReturn['id'] != '' ){
      return $APIReturn['id'];
    }else{
      return false;
    }
  }

  function getRandomMovie($loadAll = false, $loadCastAndCrew = false, $loadTrailers = false, $loadImages = false, $loadTranslations = false, $loadAlternativeTitles = false, $loadKeywords = false, $loadReleasesInfo = false, $loadCollection = false, $loadProductionCompanies = false){
    //Get the random max id;
    $randomId = $this -> getLatestMovieId();
    if( $randomId !== false ){
      $randomMax = $randomId;
    }else{
      $randomMax = $this -> randomMaxMovieId;
    }
    //To avoid infinit loop;
    $currentAttempt  = 1;
    while( $currentAttempt <= $this -> requestMaxAttempt ){
      $randId = mt_rand(1, $randomMax );
      $movieObject = $this -> movieDetail($randId, $loadAll, $loadCastAndCrew, $loadTrailers, $loadImages, $loadTranslations, $loadAlternativeTitles, $loadKeywords, $loadReleasesInfo, $loadCollection, $loadProductionCompanies);
      if( is_object( $movieObject ) ){
        return $movieObject;
      }
      $currentAttempt++;
    }
    return false;
  }

  function getRandomMovieWithBackdrop($loadAll = false, $loadCastAndCrew = false, $loadTrailers = false, $loadImages = false, $loadTranslations = false, $loadAlternativeTitles = false, $loadKeywords = false, $loadReleasesInfo = false, $loadCollection = false, $loadProductionCompanies = false){
    //Get the random max id;
    $randomId = $this -> getLatestMovieId();
    if( $randomId !== false ){
      $randomMax = $randomId;
    }else{
      $randomMax = $this -> randomMaxMovieId;
    }
    //To avoid infinit loop;
    $currentAttempt  = 1;
    while( $currentAttempt <= $this -> requestMaxAttempt ){
      $randId = mt_rand(1, $randomMax );
      $movieObject = $this -> movieDetail($randId, $loadAll, $loadCastAndCrew, $loadTrailers, $loadImages, $loadTranslations, $loadAlternativeTitles, $loadKeywords, $loadReleasesInfo, $loadCollection, $loadProductionCompanies);
      if( is_object( $movieObject ) && $movieObject -> getBackdropPath() != '' ){
        return $movieObject;
      }
      $currentAttempt++;
    }
    return false;
  }

  function getPopularMovieWithBackdrop($loadAll = false, $loadCastAndCrew = false, $loadTrailers = false, $loadImages = false, $loadTranslations = false, $loadAlternativeTitles = false, $loadKeywords = false, $loadReleasesInfo = false, $loadCollection = false, $loadProductionCompanies = false){
    //Get the random max id;
    $randomId = $this -> getLatestMovieId();
    if( $randomId !== false ){
      $randomMax = $randomId;
    }else{
      $randomMax = $this -> randomMaxMovieId;
    }
    //To avoid infinit loop;
    $currentAttempt  = 1;
    while( $currentAttempt <= $this -> requestMaxAttempt ){
      $randId = mt_rand(1, $randomMax );
      $movieObject = $this -> movieDetail($randId, $loadAll, $loadCastAndCrew, $loadTrailers, $loadImages, $loadTranslations, $loadAlternativeTitles, $loadKeywords, $loadReleasesInfo, $loadCollection, $loadProductionCompanies);
      if( is_object( $movieObject ) && $movieObject -> getBackdropPath() != '' && $movieObject -> getVoteAverage() >= $this -> voteAvarageMinimum ){
        return $movieObject;
      }
      $currentAttempt++;
    }
    return false;
  }

  function getNowPlayingMovies($page = false){
    $parameters = array();
    //Set the page;
    if( $page !== false ){
      $parameters['page'] = $page;
    }
    //Call the API;
    $APIReturn = $this -> callMethod('movie', 'now-playing', $parameters);
    //Make the movie list return;
    return $this -> _makeListReturn($APIReturn, 'MOVIE');
  }

  function getPopularMovies($page = false){
    $parameters = array();
    //Set the page;
    if( $page !== false ){
      $parameters['page'] = $page;
    }
    //Call the API;
    $APIReturn = $this -> callMethod('movie', 'popular', $parameters);
    //Make the movie list return;
    return $this -> _makeListReturn($APIReturn, 'MOVIE');
  }

  function getTopRatedMovies($page = false){
    $parameters = array();
    //Set the page;
    if( $page !== false ){
      $parameters['page'] = $page;
    }
    //Call the API;
    $APIReturn = $this -> callMethod('movie', 'top-rated', $parameters);
    //Make the movie list return;
    return $this -> _makeListReturn($APIReturn, 'MOVIE');
  }

  function getUpcomingMovies($page = false){
    $parameters = array();
    //Set the page;
    if( $page !== false ){
      $parameters['page'] = $page;
    }
    //Call the API;
    $APIReturn = $this -> callMethod('movie', 'upcoming', $parameters);
    //Make the movie list return;
    return $this -> _makeListReturn($APIReturn, 'MOVIE');
  }

  //Collection Methods;
  function collectionDetail($collectionId, $loadAll = false, $loadImages = false){
    //Call the API;
    $collectionBasicInfo = $this -> collectionInfo($collectionId);
    if( is_array( $collectionBasicInfo ) && sizeof( $collectionBasicInfo ) > 0 ){
      $collectionObject = new TMDBCollection($collectionBasicInfo);
      //Load parts;
      $loadedValue = $this -> loadParts( $collectionBasicInfo['parts'] );
      $collectionObject -> setParts( $loadedValue );
      //Load the images;
      if( $loadAll === true || $loadImages === true ){
        $images = $this -> collectionImages($collectionId);
        $collectionObject -> setImages($images['BACKDROPS'], 'BACKDROPS');
        $collectionObject -> setImages($images['POSTERS'], 'POSTERS');
      }
      return $collectionObject;
    }else{
      return false;
    }
  }

  function collectionInfo($collectionId, $methodRaw = false, $parameters = false){
    return $this -> callObjectMethod('collection', $collectionId, $methodRaw, $parameters);
  }

  function collectionImages($collectionId){
    $images = $this -> collectionInfo($collectionId, 'images');
    $returnArray = array('BACKDROPS' => false, 'POSTERS' => false);
    if( isset( $images['backdrops'] ) ){
      $returnArray['BACKDROPS'] = $this -> loadImages($images['backdrops'], 'BACKDROPS');
    }
    if( isset( $images['posters'] ) ){
      $returnArray['POSTERS'] = $this -> loadImages($images['posters'], 'POSTERS');
    }
    return $returnArray;
  }

  //Person Methods;
  function searchPerson($query, $page = false){
    $parameters = array();
    //Make the query;
    $parameters['query'] = $this -> parseQuery( $query );
    //Set the page;
    if( $page !== false ){
      $parameters['page'] = $page;
    }
    //Call the API;
    $APIReturn = $this -> callMethod('search', 'person', $parameters);
    //Make the person list return;
    return $this -> _makeListReturn($APIReturn, 'PERSON');
  }

  function personDetail($personId, $loadAll = false, $loadCastAndCrew = false, $loadImages = false){
    $personBasicInfo = $this -> personInfo($personId);
    if( is_array( $personBasicInfo ) && sizeof( $personBasicInfo ) > 0 ){
      $personObject    = new TMDBPerson($personBasicInfo, true);
      //Load Credits;
      if( $loadAll === true || $loadCastAndCrew === true ){
        $castAndCrew = $this -> personCastAndCrew($personId);
        $personObject -> setCast( $castAndCrew['CAST'] );
        $personObject -> setCrew( $castAndCrew['CREW'] );
      }
      //Load the images;
      if( $loadAll === true || $loadImages === true ){
        $images = $this -> personImages($personId);
        $personObject -> setImages($images['PROFILES'], 'PROFILES');
      }
      return $personObject;
    }else{
      return false;
    }
  }

  function personInfo($personId, $methodRaw = false, $parameters = false){
    return $this -> callObjectMethod('person', $personId, $methodRaw, $parameters);
  }

  function personCastAndCrew($personId){
    $credits     = $this -> personInfo($personId, 'credits');
    $returnArray = array('CAST' => false, 'CREW' => false);
    if( isset( $credits['cast'] ) ){
      $returnArray['CAST'] = $this -> loadCredits($credits['cast'], 'CAST');
    }
    if( isset( $credits['crew'] ) ){
      $returnArray['CREW'] = $this -> loadCredits($credits['crew'], 'CREW');
    }
    return $returnArray;
  }

  function personImages($personId){
    $images = $this -> personInfo($personId, 'images');
    $returnArray = array('PROFILES' => false);
    if( isset( $images['profiles'] ) ){
      $returnArray['PROFILES'] = $this -> loadImages($images['profiles'], 'PROFILES');
    }
    return $returnArray;
  }

  //Company Methods;
  function searchCompany($query, $page = false){
    $parameters = array();
    //Make the query;
    $parameters['query'] = $this -> parseQuery( $query );
    //Set the page;
    if( $page !== false ){
      $parameters['page'] = $page;
    }
    //Call the API;
    $APIReturn = $this -> callMethod('search', 'company', $parameters);
    //Make the movie list return;
    return $this -> _makeListReturn($APIReturn, 'COMPANY');
  }

  function companyMovies($companyId, $page = false){
    $parameters = array();
    //Set the page;
    if( $page !== false ){
      $parameters['page'] = $page;
    }
    //Call the API;
    $APIReturn = $this -> companyInfo($companyId, 'movies', $parameters);
    //Make the movie list return;
    return $this -> _makeListReturn($APIReturn, 'MOVIE');
  }

  function companyDetail($companyId, $loadAll = false){
    $companyBasicInfo = $this -> companyInfo($companyId);
    if( is_array( $companyBasicInfo ) && sizeof( $companyBasicInfo ) > 0 ){
      $companyObject = new TMDBCompany($companyBasicInfo, true);
      return $companyObject;
    }else{
      return false;
    }
  }

  function companyInfo($companyId, $methodRaw = false, $parameters = false){
    return $this -> callObjectMethod('company', $companyId, $methodRaw, $parameters);
  }

  //Genre Methods;
  function genreList($parameters = false){
    //Call the API;
    $APIReturn = $this -> callMethod('genre', 'list', $parameters);
    //Save the current page;
    $this -> currentPage = 1;
    //Save the total page;
    $this -> totalPages = 1;
    //Save the total results;
    if( isset( $APIReturn['genres'] ) && sizeof( $APIReturn['genres'] ) > 0  ){
      $this -> totalResults = sizeof( $APIReturn['genres'] );
      //Make the results;
      $genreList = array();
      foreach($APIReturn['genres'] as $k => $v){
        $genre       = new TMDBGenre( $APIReturn['genres'][ $k ] );
        $genreList[] = $genre;
      }
      $this -> results = $genreList;
      return true;
    }else{
      $this -> totalResults = 0;
      $this -> results      = array();
      return false;
    }
  }

  function genreDetail($genreId, $loadAll = false){
    $genreBasicInfo = $this -> genreInfo($genreId);
    if( is_array( $genreBasicInfo ) && sizeof( $genreBasicInfo ) > 0 ){
      $genreObject = new TMDBGenre($genreBasicInfo, true);
      return $genreObject;
    }else{
      return false;
    }
  }

  function genreInfo($genreId, $methodRaw = false, $parameters = false){
    return $this -> callObjectMethod('genre', $genreId, $methodRaw, $parameters);
  }

  function genreMovies($genreId, $page = false){
    $parameters = array();
    //Set the page;
    if( $page !== false ){
      $parameters['page'] = $page;
    }
    //Call the API;
    $APIReturn = $this -> genreInfo($genreId, 'movies', $parameters);
    //Make the movie list return;
    return $this -> _makeListReturn($APIReturn, 'MOVIE');
  }

  //Load Methods;
  function loadGenres($genresBasicValues){
    $arrayObjects = array();
    if( is_array( $genresBasicValues ) && sizeof( $genresBasicValues ) > 0 ){
      foreach($genresBasicValues as $k => $v){
        $arrayObjects[] = new TMDBGenre($genresBasicValues[ $k ]);
      }
    }
    return $arrayObjects;
  }

  function loadCollection($collectionBasicValues){
    if( is_array( $collectionBasicValues ) && sizeof( $collectionBasicValues ) > 0 ){
      return new TMDBCollection($collectionBasicValues);
    }else{
      return false;
    }
  }

  function loadProductionCompanies($productionCompaniesBasicValues, $loadAll = false){
    $arrayObjects = array();
    if( is_array( $productionCompaniesBasicValues ) && sizeof( $productionCompaniesBasicValues ) > 0 ){
      foreach($productionCompaniesBasicValues as $k => $v){
        $companyRawObject = new TMDBCompany($productionCompaniesBasicValues[ $k ]);
        if( $loadAll === true && is_object( $companyRawObject ) ){
          $companyObject    = $this -> companyDetail($companyRawObject -> getId(), true);
          $arrayObjects[] = $companyObject;
        }else{
          $arrayObjects[] = $companyRawObject;
        }
      }
    }
    return $arrayObjects;
  }

  function loadProductionCountries($productionCountriesBasicValues){
    $arrayObjects = array();
    if( is_array( $productionCountriesBasicValues ) && sizeof( $productionCountriesBasicValues ) > 0 ){
      foreach($productionCountriesBasicValues as $k => $v){
        $arrayObjects[] = new TMDBCountry($productionCountriesBasicValues[ $k ]);
      }
    }
    return $arrayObjects;
  }

  function loadSpokenLanguages($spokenLanguagesBasicValues){
    $arrayObjects = array();
    if( is_array( $spokenLanguagesBasicValues ) && sizeof( $spokenLanguagesBasicValues ) > 0 ){
      foreach($spokenLanguagesBasicValues as $k => $v){
        $arrayObjects[] = new TMDBSpokenLanguage($spokenLanguagesBasicValues[ $k ]);
      }
    }
    return $arrayObjects;
  }

  function loadCast($casting){
    if( is_array( $casting ) && sizeof( $casting ) > 0 ){
      $castingObjects = array();
      foreach($casting as $k => $v){
        $castObject       = new TMDBCast( $casting[ $k ] );
        $i = intval( $castObject -> getOrder() );
        $castingObjects[$i] = $castObject;
      }
      ksort( $castingObjects );
      return $castingObjects;
    }else{
      return false;
    }
  }

  function loadCrew($crew){
    if( is_array( $crew ) && sizeof( $crew ) > 0 ){
      $crewObjects = array();
      foreach($crew as $k => $v){
        $crewObject    = new TMDBCrew( $crew[ $k ] );
        $crewObjects[] = $crewObject;
      }
      return $crewObjects;
    }else{
      return false;
    }
  }

  function loadTranslations($translations){
    if( is_array( $translations ) && sizeof( $translations ) > 0 ){
      $translationObjects = array();
      foreach($translations as $k => $v){
        $translation          = new TMDBTranslation($translations[ $k ]);
        $translationObjects[] = $translation;
      }
      return $translationObjects;
    }else{
      return false;
    }
  }

  function loadAlternativeTitles($alternativeTitles){
    if( is_array( $alternativeTitles ) && sizeof( $alternativeTitles ) > 0 ){
      $alternativeTitleObjects = array();
      foreach($alternativeTitles as $k => $v){
        $alternativeTitle          = new TMDBTitle($alternativeTitles[ $k ]);
        $alternativeTitleObjects[] = $alternativeTitle;
      }
      return $alternativeTitleObjects;
    }else{
      return false;
    }
  }

  function loadKeywords($keywords){
    if( is_array( $keywords ) && sizeof( $keywords ) > 0 ){
      $keywordsObjects = array();
      foreach($keywords as $k => $v){
        $keyword          = new TMDBKeyword($keywords[ $k ]);
        $keywordsObjects[] = $keyword;
      }
      return $keywordsObjects;
    }else{
      return false;
    }
  }

  function loadReleasesInfo($releasesInfo){
    if( is_array( $releasesInfo ) && sizeof( $releasesInfo ) > 0 ){
      $ReleasesInfoObjects = array();
      foreach($releasesInfo as $k => $v){
        $releaseInfo           = new TMDBReleaseInfo($releasesInfo[ $k ]);
        $ReleasesInfoObjects[] = $releaseInfo;
      }
      return $ReleasesInfoObjects;
    }else{
      return false;
    }
  }

  function loadTrailers($trailers, $type){
    if( is_array( $trailers ) && sizeof( $trailers ) > 0 ){
      $trailerObjects = array();
      foreach($trailers as $k => $v){
        $trailer          = new TMDBTrailer($trailers[ $k ], $type);
        $trailerObjects[] = $trailer;
      }
      return $trailerObjects;
    }else{
      return false;
    }
  }

  function loadImages($images, $type){
    if( is_array( $images ) && sizeof( $images ) > 0 ){
      $imagesObjects = array();
      foreach($images as $k => $v){
        $image           = new TMDBImage($images[ $k ], $type);
        $imagesObjects[] = $image;
      }
      return $imagesObjects;
    }else{
      return false;
    }
  }

  function loadCredits($credits, $type){
    if( is_array( $credits ) && sizeof( $credits ) > 0 ){
      $creditsObjects = array();
      foreach($credits as $k => $v){
        if( $type == 'CAST' ){
          $creditsObjects[] = new TMDBCreditCast( $credits[ $k ] );
        }else{
          $creditsObjects[] = new TMDBCreditCrew( $credits[ $k ] );
        }
      }
      return $creditsObjects;
    }else{
      return false;
    }
  }

  function loadParts($movies){
    if( is_array( $movies ) && sizeof( $movies ) > 0 ){
      $partsObjects = array();
      foreach($movies as $k => $v){
        $movie = new TMDBMovie( $movies[ $k ] );
        if( $movie -> getReleaseDate() == '' ){
          $partsObjects[ 0 ][] = $movie;
        }else{
          $partsObjects[ $movie -> getReleaseDate() ][] = $movie;
        }
      }
      krsort( $partsObjects );
      $partsArray = array();
      foreach($partsObjects as $k => $v){
        $dateMovies = $partsObjects[ $k ];
        foreach($dateMovies as $kk => $vv){
          $movieObject  = $dateMovies[ $kk ];
          $partsArray[] = $movieObject;
        }
      }
      return $partsArray;
    }else{
      return false;
    }
  }
}
?>