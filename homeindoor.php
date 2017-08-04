<?php include('livedata.php');include('common.php');header('Content-type: text/html; charset=utf-8');date_default_timezone_set($TZ);?>
<body>

<?php 
 //weather34 temperture indoor celsius
 if ($weather["temp_units"]=='C' && $weather["temp_indoor"]>0){echo "<div class=\"circleindoortemp\">", $weather["temp_indoor"] ;
 echo "  <spaneindoortemp> ".$weatherunitc." </spaneindoortemp> </div> " ;} 
 
 //weather34 fahrenheit
 else if ($weather["temp_units"]=='F' && $weather["temp_indoor"]>0){echo "<div class=\"circleindoortemp\">", $weather["temp_indoor"] ;
 echo "  <spaneindoortemp> ".$weatherunitf." </spaneindoortemp>  </div> " ;}  
?>
</div>

<div class="hometemperatureindoortrend">
<?php if(array_key_exists("temp_indoor_trend",$weather));
if( $weather["temp_indoor_trend"]>0)echo '<spanindoortemprising>  
<svg id="risingtempindoor" width="11" height="11" viewBox="0 0 24 24">
    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
    <polyline points="17 6 23 6 23 12" fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="10%"/>
</svg> </spanindoortemprising> ';

else if($weather["temp_indoor_trend"]<0)echo '<spanindoortempfalling> 
<svg id="fallingtempindoor" width="11" height="11" viewBox="0 0 24 24">
    <polyline points="23 18 13.5 8.5 8.5 13.5 1 6" fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
    <polyline points="17 18 23 18 23 12" fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="10%"/>
</svg> </spanindoortempfalling> ';
?>
</div>

<!-- The rest is a comment. Just showing indoor temp. which in our  case is used for the water temp sensor 
<div class="homeindoorhum">
<?php 
echo "<spanhomeindoorhumtitle>Humidity </spanhomeindoorhumtitle><spanhomeindoorvalue>" .$weather["humidity_indoor"]."</spanhomeindoorvalue><spanehomeindoorhum> %</spanehomeindoorhum>"
?></div>

<div class="homeindoorfeels">
<?php 
//weather34 celsius
if ($weather["temp_units"]=='C')
echo "<spanfeelstitle>".$lang['Feelslike']." </spanfeelstitle>" .$weather["temp_indoor_feel"]." ".$weatherunitcsmall."";
//weather34 fahrenheit
else if ($weather["temp_units"]=='F') echo "<spanfeelstitle>Feels Like </spanfeelstitle> " .$weather["temp_indoor_feel"]." ".$weatherunitfsmall."";
?></div>

 -->
