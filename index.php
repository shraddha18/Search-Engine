<?php
ini_set('memory_limit',-1);
include 'SpellCorrector.php';
include 'simple_html_dom.php';

header('Content-Type: text/html; charset=utf-8'); // make sure browsers see this page as utf-8 encoded HTML
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;
//$radio=$_GET['check'];
if ($query)
{
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('/Applications/MAMP/htdocs/solr-php-client/Apache/Solr/Service.php');

  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample/');

  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }

  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)

if(isset($_GET['check'])&& $radio=='pagerank'){
     $order='pageRankFile.txt desc';
  }
  else{
     $order='score desc';
 }

$additionalParameters=array(
	//'fq' => 'a filtering query',
	'sort'=>$order
);
 try
  {

  $results=$solr->search($query, 0, $limit, $additionalParameters);

}
  catch (Exception $e)
  {
    // in production you'd probably log or email this error to an admin
    // and then show a special message to the user but for this example
    // we're going to show the full exception
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }


    //echo "<label><b>Did you mean?</b></label>" .SpellCorrector::correct($query);

  // else{
  //
  // }

  //echo "<label>Did you mean?</label>";
  //it will output *october*
}

?>
<html>
  <head>
    <title>PHP Solr Client Example</title>
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
    <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  </head>
  <body>
    <!--jQuery part for sending auto suggestions-->
    <div class="jumbotron" style="background-color: #98AEFF;">

<div class="jumbotron" style="text-align: center;">
  <h1 class="display-3" style="text-align: center;">Search Engine</h1>
  <p class="lead" style="text-align: center;">Enter your queries in the box provided</p>
  <hr class="my-4">
</div>

    <script>
   $(function() {
     var prefix = "http://localhost:8983/solr/myexample/suggest?indent=on&q=";
     var suffix = "&wt=json";
     var count=0;
     var dropdown = [];
     $("#q").autocomplete({
    source : function(request, response) {
         var finalString="",before="";
         var query = $("#q").val().toLowerCase();
         var maxDisplay = 10;
         var space =  query.lastIndexOf(' ');
         if(query.length-1>space && space!=-1){
          finalString=query.substr(space+1);
          before = query.substr(0,space);
        }
        else{
          finalString=query.substr(0);
        }
        var URL = prefix + finalString+ suffix;
        //ajax code for suggestion
        $.ajax({
 url : URL,
 success : function(data) {
//console.log(data);
 var docs = JSON.stringify(data.suggest.suggest);
 var jsonData = JSON.parse(docs);
 var answer = jsonData[finalString].suggestions;

for(var i=0,j=0;j<answer.length && i<=j;i++,j++){

  //code to eliminate special characters
  if(/^[a-zA-Z]*$/.test(answer[j].term)==true){
    dropdown[i]=before + " " + answer[j].term; //just to display the appended term
  }
  else{
    i--;
  }


  //dropdown[i]=before + " " + answer[i].term;

  //dropdown[i]=answer[i].term;
}


 response(dropdown.slice(0,maxDisplay));

 },
 dataType : 'jsonp',
 jsonp : 'json.wrf'
 });
 dropdown=[];
 },
 minLength : 1
 })
 });
</script><!--End of jQuery-->
    <form  accept-charset="utf-8" method="get">
      <label for="q" style="font-size:20px";>Search:</label>
      <input style="font-size:20px" id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
      <input type="submit"/>
    </form>

<?php

// display results
if ($results)
{
  $tbString= $_GET['q'];

  $complete="";
  $queryArray=explode(" ",$query);
  //echo sizeof($queryArray);

  if(sizeof($queryArray)==1){
    $complete=SpellCorrector::correct($query);
  }
  else{
    foreach ($queryArray as $arrayElement) {
      $temp=SpellCorrector::correct($arrayElement);
      //echo $temp;
      $complete=$complete." ".$temp;
      //echo $complete;
    }
  }


  if(strtolower(trim($tbString))!=strtolower(trim($complete))){

    //$scAns=SpellCorrector::correct($query);

    echo "<label><b>Did you mean </b></label><a href='index.php?q=$complete'>".$complete."</a><label><b>?</b></label>";
  }
else{

}




  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
?>
    <div style="font-size:20px";>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
<?php
  // iterate result documents
  foreach ($results->response->docs as $doc)
  {

$row = 1;
if (($handle = fopen("/Applications/MAMP/htdocs/mapLATimesDataFile.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, ",")) !== FALSE) {
        $num = count($data);
        $row++;
	$idx=explode('/', $doc->id);
	//echo $idx[6];
        for ($c=0; $c < $num; $c++) {
	if(strpos($data[$c], $idx[6]) !== false){
	$c++;
	$tokens=explode("\r",$data[$c]);
	//echo $idx[0];

}
        }
    }
    fclose($handle);
}

?>

<table >

<tr>
	<table>
	<tr><td style="font-size:25px";> <a href='<?php echo htmlspecialchars($tokens[0], ENT_NOQUOTES, 'utf-8'); ?>'> <?php echo htmlspecialchars($doc->title, ENT_NOQUOTES, 'utf-8'); ?> </a> </td></tr>
	<tr><td style="font-size:20px";><a href='<?php echo htmlspecialchars($tokens[0], ENT_NOQUOTES, 'utf-8'); ?>'> <?php echo htmlspecialchars($tokens[0], ENT_NOQUOTES, 'utf-8'); ?> </a> </td></tr>
	<tr><td style="font-size:20px";><?php echo htmlspecialchars($doc->id, ENT_NOQUOTES, 'utf-8'); ?></td></tr>
	<tr><td style="font-size:20px";><?php echo htmlspecialchars($doc->description, ENT_NOQUOTES, 'utf-8'); ?></td></tr>
  <tr>
        <td><b>Snippet:</b><?php echo $snippet; ?></a> </td>
    </tr>
  <br/>
	</table>
</tr>

        </table>
<?php
    $searchWord = $_GET['q'];
    $queryWords = explode(" ",$searchWord);
    $textContents =  file_get_html($doc->og_url)->plaintext;
    //$textContents =  file_get_html("https://www.google.com")->plaintext;
    //echo $textContents;
    $sentences = explode('.',$textContents);
    $snippet = "";
    foreach($queryWords as $queryword){
      foreach($sentences as $sentence) {
      if(preg_match("/\b$queryword\b/i",$sentence))
       $snippet = /*$snippet." ".*/(trim($sentence));
          break;
      }
    }

  }
?>
<?php
}
?>
  </body>
</html>
