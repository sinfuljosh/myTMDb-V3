<?php
$tmdbKey = 'YOUR_KEY_HERE';
?>
<style>
fieldset{
  background-color: rgba(255, 255, 255, 0.7);
  width:            100%;
  margin-top:       20px;
}
.alwaysOn{
  background-color: #FFFFFF;
}
legend{
  background-color: #FFFFFF;
}
</style>

<fieldset class="alwaysOn">
  <legend>Actions:</legend>
  <a href="index.php?action=latestMovie">latestMovie</a>&nbsp;&nbsp;
  <a href="index.php?action=NowPlaying">NowPlaying</a>&nbsp;&nbsp;
  <a href="index.php?action=Popular">Popular</a>&nbsp;&nbsp;
  <a href="index.php?action=Upcoming">Upcoming</a>&nbsp;&nbsp;
  <a href="index.php?action=TopRated">TopRated</a>&nbsp;&nbsp;
  <a href="index.php?action=RandomMovie">RandomMovie</a>&nbsp;&nbsp;
  <a href="index.php?action=RandomMovieWithBackdrop">RandomMovieWithBackdrop</a>&nbsp;&nbsp;
  <a href="index.php?action=GenreList">GenreList</a>&nbsp;&nbsp;
</fieldset>

<form action="index.php">
  <fieldset class="alwaysOn">
    <legend>Movies:</legend>
    <input type="text" name="query">
    <input type="submit">
  </fieldset>
</form>

<form action="index.php">
  <fieldset class="alwaysOn">
    <legend>Person (Actor name):</legend>
    <input type="text" name="queryPerson">
    <input type="submit">
  </fieldset>
</form>

<form action="index.php">
  <fieldset class="alwaysOn">
    <legend>Company:</legend>
    <input type="text" name="queryCompany">
    <input type="submit">
  </fieldset>
</form>
  
<fieldset>
  <legend>Results:</legend>
<?php
//Load the classes;
include("classes/tmdb.php");
include("classes/tmdbCast.php");
include("classes/tmdbCollection.php");
include("classes/tmdbCompany.php");
include("classes/tmdbCountry.php");
include("classes/tmdbCreditCast.php");
include("classes/tmdbCreditCrew.php");
include("classes/tmdbCrew.php");
include("classes/tmdbGenre.php");
include("classes/tmdbImage.php");
include("classes/tmdbKeyword.php");
include("classes/tmdbMovie.php");
include("classes/tmdbPerson.php");
include("classes/tmdbReleaseInfo.php");
include("classes/tmdbSpokenLanguage.php");
include("classes/tmdbTitle.php");
include("classes/tmdbTrailer.php");
include("classes/tmdbTranslation.php");

//Start whith the key;
$TMDB = new TMDB($tmdbKey);

//Set the language;
$TMDB -> setLanguage('en');

//Show adult content;
$TMDB -> showAdultContent();

//Debug;
$TMDB -> saveDebug();

$query = $_GET['query'];
if( isset( $query ) && $query != '' ){
  $TMDB -> searchMovie( $query );
  if( $TMDB -> totalResults() > 0 ){
    $movies = $TMDB -> results();
    foreach($movies as $k => $v){
      $movie = $movies[$k];
      echo '<a href="index.php?movieDetailId='.$movie -> getId().'">'.utf8_decode( $movie -> getTitle() ).'</a><br>';
      if( $movie -> hasPoster() ){
        echo '<img src="'.$TMDB -> getImageURL($movie -> getPosterPath(), 'w92').'" width="92">';
        echo '<br>';
      }
      echo '<a href="index.php?similarId='.$movie -> getId().'">Similar Movies</a>';
      echo '<br>';
      echo '<br>';
    }
  }
}

$similarId = $_GET['similarId'];
if( isset( $similarId ) && $similarId != '' ){
  $TMDB -> searchSimilarMovie( $similarId );
  if( $TMDB -> totalResults() > 0 ){
    $movies = $TMDB -> results();
    foreach($movies as $k => $v){
      $movie = $movies[$k];
      echo '<a href="index.php?movieDetailId='.$movie -> getId().'">'.utf8_decode( $movie -> getTitle() ).'</a><br>';
      if( $movie -> hasPoster() ){
        echo '<img src="'.$TMDB -> getImageURL($movie -> getPosterPath(), 'w92').'" width="92">';
        echo '<br>';
      }
      echo '<br>';
      echo '<a href="index.php?similarId='.$movie -> getId().'">Similar Movies</a>';
      echo '<br>';
      echo '<br>';
    }
  }
}

$movieDetailId = $_GET['movieDetailId'];
if( isset( $movieDetailId ) && $movieDetailId != '' ){
  $movie = $TMDB -> movieDetail($movieDetailId, true);
  echo utf8_decode( $movie -> getTitle() ).' ('. utf8_decode( $movie -> getOriginalTitle() ).')<br>';
  if( $movie -> hasPoster() ){
    echo '<img src="'.$TMDB -> getImageURL($movie -> getPosterPath(), 'w185').'" width="185">';
    echo '<br>';
  }
  echo '<br>';
  echo '<a href="index.php?similarId='.$movie -> getId().'">Similar Movies</a>';
  echo '<br>';
  if( $movie -> hasCollection() ){
      $collection = $movie -> getCollection();
      echo '<br>';
      echo '<a href="index.php?collectionDetailId='.$collection -> getId().'">'.utf8_decode( $collection -> getName() ).'</a><br>';
      if(  $collection -> hasPoster() ){
        echo '<img src="'.$TMDB -> getImageURL($collection -> getPosterPath(), 'w185').'" width="185">';
      }
  }
  if( $movie -> hasBackdrop() ){
    echo '<style>';
    echo 'body {';
    echo '  background-image: url("'.$TMDB -> getImageURL( $movie -> getBackdropPath() ).'");';
    echo '  background-attachment: fixed;';
    echo '  background-repeat:     no-repeat;';
    echo '  background-size:       cover;';
    echo '}';
    echo '</style>';
  }
}

$collectionDetailId = $_GET['collectionDetailId'];
if( isset( $collectionDetailId ) && $collectionDetailId != '' ){
  $returnValue = $TMDB -> collectionDetail( $collectionDetailId );
  echo utf8_decode( $returnValue -> getName() ).'<br>';
  if( $returnValue -> hasPoster() ){
    echo '<img src="'.$TMDB -> getImageURL($returnValue -> getPosterPath(), 'w92').'" width="92">';
    echo '<br>';
  }
  if( $returnValue -> hasParts() ){
    $movies = $returnValue -> getParts();
    foreach($movies as $k => $v){
      $movie = $movies[$k];
      echo '<a href="index.php?movieDetailId='.$movie -> getId().'">'.utf8_decode( $movie -> getTitle() ).'</a><br>';
      echo '<img src="'.$TMDB -> getImageURL($movie -> getPosterPath(), 'w92').'" width="92">';
      echo '<br>';
      echo '<br>';
      echo '<a href="index.php?similarId='.$movie -> getId().'">Similar Movies</a>';
      echo '<br>';
    }
  }
  echo '<style>';
  echo 'body {';
  echo '  background-image: url("'.$TMDB -> getImageURL( $returnValue -> getBackdropPath() ).'");';
  echo '  background-attachment: fixed;';
  echo '  background-repeat:     no-repeat;';
  echo '  background-size:       cover;';
  echo '}';
  echo '</style>';
}

$queryPerson = $_GET['queryPerson'];
if( isset( $queryPerson ) && $queryPerson != '' ){
  $TMDB -> searchPerson( $queryPerson );
  if( $TMDB -> totalResults() > 0 ){
    $persons = $TMDB -> results();
    foreach($persons as $k => $v){
      $person = $persons[$k];
      echo utf8_decode( $person -> getName() ).'<br>';
      echo '<img src="'.$TMDB -> getImageURL( $person -> getProfilePath() ).'" width="100" height="100">';
      echo '<br>';
      echo '<a href="index.php?personDetailsId='.$person -> getId().'">Details</a>';
      echo '<br>';
      echo '<br>';
    }
  }
}

$personDetailsId = $_GET['personDetailsId'];
if( isset( $personDetailsId ) && $personDetailsId != '' ){
  $person = $TMDB -> personDetail($personDetailsId, true);
  echo utf8_decode( $person -> getName() ).'<br>';
  echo '<img src="'.$TMDB -> getImageURL( $person -> getProfilePath() ).'" width="100" height="100">';
  echo '<br>';
  echo '<a href="index.php?personDetailsId='.$person -> getId().'">Details</a>';
  echo '<br>';
  echo '<br>';
}

$queryCompany = $_GET['queryCompany'];
if( isset( $queryCompany ) && $queryCompany != '' ){
  $TMDB -> searchCompany( $queryCompany );
  if( $TMDB -> totalResults() > 0 ){
    $companies = $TMDB -> results();
    foreach($companies as $k => $v){
      $company = $companies[$k];
      echo utf8_decode( $company -> getName() ).'<br>';
      if( $company -> hasLogo() ){
        echo '<img src="'.$TMDB -> getImageURL($company -> getLogoPath(), 'w185').'" width="185">';
        echo '<br>';
      }
      echo '<a href="index.php?companyDetailsId='.$company -> getId().'">Details</a>';
      echo '<br>';
      echo '<a href="index.php?companyMoviesId='.$company -> getId().'">Movies</a>';
      echo '<br>';
      echo '<br>';
    }
  }
}

$companyDetailsId = $_GET['companyDetailsId'];
if( isset( $companyDetailsId ) && $companyDetailsId != '' ){
  $company = $TMDB -> companyDetail( $companyDetailsId );
  echo utf8_decode( $company -> getName() ).'<br>';
  if( $company -> hasLogo() ){
    echo '<img src="'.$TMDB -> getImageURL($company -> getLogoPath(), 'w185').'" width="185">';
    echo '<br>';
  }
  echo '<a href="index.php?companyMoviesId='.$company -> getId().'">Movies</a>';
  echo '<br>';
  echo '<br>';
}

$companyMoviesId = $_GET['companyMoviesId'];
if( isset( $companyMoviesId ) && $companyMoviesId != '' ){
  $TMDB -> companyMovies( $companyMoviesId );
  if( $TMDB -> totalResults() > 0 ){
    $movies = $TMDB -> results();
    foreach($movies as $k => $v){
      $movie = $movies[$k];
      echo '<a href="index.php?movieDetailId='.$movie -> getId().'">'.utf8_decode( $movie -> getTitle() ).'</a><br>';
      echo '<img src="'.$TMDB -> getImageURL( $movie -> getBackdropPath(), 'w300').'" width="300">';
      echo '<img src="'.$TMDB -> getImageURL( $movie -> getPosterPath(), 'w92').'" width="92">';
      echo '<br>';
      echo '<br>';
      echo '<a href="index.php?similarId='.$movie -> getId().'">Similar Movies</a>';
      echo '<br>';
    }
  }
}

$genreMoviesId = $_GET['genreMoviesId'];
if( isset( $genreMoviesId ) && $genreMoviesId != '' ){
  $TMDB -> genreMovies( $genreMoviesId );
  if( $TMDB -> totalResults() > 0 ){
    $movies = $TMDB -> results();
    foreach($movies as $k => $v){
      $movie = $movies[$k];
      echo '<a href="index.php?movieDetailId='.$movie -> getId().'">'.utf8_decode( $movie -> getTitle() ).'</a><br>';
      echo '<img src="'.$TMDB -> getImageURL( $movie -> getBackdropPath(), 'w300').'" width="300">';
      echo '<img src="'.$TMDB -> getImageURL( $movie -> getPosterPath(), 'w92').'" width="92">';
      echo '<br>';
      echo '<br>';
      echo '<a href="index.php?similarId='.$movie -> getId().'">Similar Movies</a>';
      echo '<br>';
    }
  }
}

$action = $_GET['action'];
if( isset( $action ) && $action == 'latestMovie' ){
  $movie = $TMDB -> getLatestMovie(true);
  echo utf8_decode( $movie -> getTitle() ).' ('. utf8_decode( $movie -> getOriginalTitle() ).')<br>';
  if( $movie -> hasPoster() ){
    echo '<img src="'.$TMDB -> getImageURL($movie -> getPosterPath(), 'w185').'" width="185">';
    echo '<br>';
  }
  if( $movie -> hasCollection() ){
      $collection = $movie -> getCollections();
      echo '<a href="index.php?collectionDetailId='.$collection['id'].'">'.utf8_decode( $collection['name'] ).'</a><br>';
      echo '<img src="'.$TMDB -> getImageURL( $movie -> getBackdropPath(), 'w300').'" width="300">';
      echo '<img src="'.$TMDB -> getImageURL( $movie -> getPosterPath(), 'w92').'" width="92">';
  }
  echo '<br>';
  echo '<a href="index.php?similarId='.$movie -> getId().'">Similar Movies</a>';
  echo '<br>';
  if( $movie -> hasBackdrop() ){
    echo '<style>';
    echo 'body {';
    echo '  background-image: url("'.$TMDB -> getImageURL( $movie -> getBackdropPath() ).'");';
    echo '  background-attachment: fixed;';
    echo '  background-repeat:     no-repeat;';
    echo '  background-size:       cover;';
    echo '}';
    echo '</style>';
  }
}

if( isset( $action ) && $action == 'RandomMovie' ){
  $movie = $TMDB -> getRandomMovie(true);
  echo utf8_decode( $movie -> getTitle() ).' ('. utf8_decode( $movie -> getOriginalTitle() ).')<br>';
  if( $movie -> hasPoster() ){
    echo '<img src="'.$TMDB -> getImageURL($movie -> getPosterPath(), 'w185').'" width="185">';
    echo '<br>';
  }
  if( $movie -> hasCollection() ){
      $collection = $movie -> getCollections();
      echo '<a href="index.php?collectionDetailId='.$collection['id'].'">'.utf8_decode( $collection['name'] ).'</a><br>';
      echo '<img src="'.$TMDB -> getImageURL( $movie -> getBackdropPath(), 'w300').'" width="300">';
      echo '<img src="'.$TMDB -> getImageURL( $movie -> getPosterPath(), 'w92').'" width="92">';
  }
  echo '<br>';
  echo '<a href="index.php?similarId='.$movie -> getId().'">Similar Movies</a>';
  echo '<br>';
  if( $movie -> hasBackdrop() ){
    echo '<style>';
    echo 'body {';
    echo '  background-image: url("'.$TMDB -> getImageURL( $movie -> getBackdropPath() ).'");';
    echo '  background-attachment: fixed;';
    echo '  background-repeat:     no-repeat;';
    echo '  background-size:       cover;';
    echo '}';
    echo '</style>';
  }
}

if( isset( $action ) && $action == 'RandomMovieWithBackdrop' ){
  $movie = $TMDB -> getRandomMovieWithBackdrop(true);
  echo utf8_decode( $movie -> getTitle() ).' ('. utf8_decode( $movie -> getOriginalTitle() ).')<br>';
  if( $movie -> hasPoster() ){
    echo '<img src="'.$TMDB -> getImageURL($movie -> getPosterPath(), 'w185').'" width="185">';
    echo '<br>';
  }
  if( $movie -> hasCollection() ){
      $collection = $movie -> getCollections();
      echo '<a href="index.php?collectionDetailId='.$collection['id'].'">'.utf8_decode( $collection['name'] ).'</a><br>';
      echo '<img src="'.$TMDB -> getImageURL( $movie -> getBackdropPath(), 'w300').'" width="300">';
      echo '<img src="'.$TMDB -> getImageURL( $movie -> getPosterPath(), 'w92').'" width="92">';
  }
  echo '<br>';
  echo '<a href="index.php?similarId='.$movie -> getId().'">Similar Movies</a>';
  echo '<br>';
  if( $movie -> hasBackdrop() ){
    echo '<style>';
    echo 'body {';
    echo '  background-image: url("'.$TMDB -> getImageURL( $movie -> getBackdropPath() ).'");';
    echo '  background-attachment: fixed;';
    echo '  background-repeat:     no-repeat;';
    echo '  background-size:       cover;';
    echo '}';
    echo '</style>';
  }
}

if( isset( $action ) && $action == 'NowPlaying' ){
  $TMDB -> getNowPlayingMovies();
  if( $TMDB -> totalResults() > 0 ){
    $movies = $TMDB -> results();
    foreach($movies as $k => $v){
      $movie = $movies[$k];
      echo '<a href="index.php?movieDetailId='.$movie -> getId().'">'.utf8_decode( $movie -> getTitle() ).'</a><br>';
      echo '<img src="'.$TMDB -> getImageURL( $movie -> getBackdropPath(), 'w300').'" width="300">';
      echo '<img src="'.$TMDB -> getImageURL( $movie -> getPosterPath(), 'w92').'" width="92">';
      echo '<br>';
      echo '<a href="index.php?similarId='.$movie -> getId().'">Similar Movies</a>';
      echo '<br>';
      echo '<br>';
    }
  }
}

if( isset( $action ) && $action == 'Popular' ){
  $TMDB -> getPopularMovies();
  if( $TMDB -> totalResults() > 0 ){
    $movies = $TMDB -> results();
    foreach($movies as $k => $v){
      $movie = $movies[$k];
      echo '<a href="index.php?movieDetailId='.$movie -> getId().'">'.utf8_decode( $movie -> getTitle() ).'</a><br>';
      echo '<img src="'.$TMDB -> getImageURL( $movie -> getBackdropPath(), 'w300').'" width="300">';
      echo '<img src="'.$TMDB -> getImageURL( $movie -> getPosterPath(), 'w92').'" width="92">';
      echo '<br>';
      echo '<a href="index.php?similarId='.$movie -> getId().'">Similar Movies</a>';
      echo '<br>';
      echo '<br>';
    }
  }
}

if( isset( $action ) && $action == 'Upcoming' ){
  $TMDB -> getUpcomingMovies();
  if( $TMDB -> totalResults() > 0 ){
    $movies = $TMDB -> results();
    foreach($movies as $k => $v){
      $movie = $movies[$k];
      echo '<a href="index.php?movieDetailId='.$movie -> getId().'">'.utf8_decode( $movie -> getTitle() ).'</a><br>';
      echo '<img src="'.$TMDB -> getImageURL( $movie -> getBackdropPath(), 'w300').'" width="300">';
      echo '<img src="'.$TMDB -> getImageURL( $movie -> getPosterPath(), 'w92').'" width="92">';
      echo '<br>';
      echo '<a href="index.php?similarId='.$movie -> getId().'">Similar Movies</a>';
      echo '<br>';
      echo '<br>';
    }
  }
}

if( isset( $action ) && $action == 'TopRated' ){
  $TMDB -> getTopRatedMovies();
  if( $TMDB -> totalResults() > 0 ){
    $movies = $TMDB -> results();
    foreach($movies as $k => $v){
      $movie = $movies[$k];
      echo '<a href="index.php?movieDetailId='.$movie -> getId().'">'.utf8_decode( $movie -> getTitle() ).'</a><br>';
      echo '<img src="'.$TMDB -> getImageURL( $movie -> getBackdropPath(), 'w300').'" width="300">';
      echo '<img src="'.$TMDB -> getImageURL( $movie -> getPosterPath(), 'w92').'" width="92">';
      echo '<br>';
      echo '<a href="index.php?similarId='.$movie -> getId().'">Similar Movies</a>';
      echo '<br>';
      echo '<br>';
    }
  }
}

if( isset( $action ) && $action == 'GenreList' ){
  //Force en;
  $TMDB -> setLanguage('en');
  //Get the list;
  $TMDB -> genreList();
  if( $TMDB -> totalResults() > 0 ){
    $genres = $TMDB -> results();
    foreach($genres as $k => $v){
      $genre = $genres[$k];
      echo '<a href="index.php?genreMoviesId='.$genre -> getId().'">'.utf8_decode( $genre -> getName() ).'</a><br>';
    }
  }
}

?>
</fieldset>
<fieldset>
  <legend>Debug:</legend>
<?php
$TMDB -> showDebug();
?>
</fieldset>