<?php

// Mats A 2017-01 metzallo@gmail.com
// For the weathertemplate https://weather34.com/homeweatherstation/
// this program uppdates the Cumulus realtime.txt after a call to Davis Weatherlink website with a XML answer
// Documentation is available at http://pysselilivet.blogspot.com/2017/01/install-weather34-with-weatherlink.html

/*
MIT License

Copyright (c) 2017 

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/ 

// File layout http://wiki.sandaysoft.com/a/Realtime.txt 
// 2017-01-29 To get better trend values for temp and pressure the csv files in chartswudata is used as history info. 
//            Now trend calculation is using the measures 2 hours ago ->sub(new DateInterval('PT2H')) 
// 2017-03-29 Changed filesuffix for files in folder "chartswudata" to .txt
//            added cumulus[34] wich is used in Barometer view
// 2017-04-25 Due to a change in W34 it's impossible to use the history files in chartswudata  as a base for trendata.
//            Extending the file realtime.txt so fields $cumulus[60-63] is used to store the history for press and temp

ob_start();
error_reporting(0);
@ini_set('display_errors', 0);

chdir(dirname(__FILE__));

include('../settings1.php');                // The best schould have been to store the Weatherlink credentials here, but easyweathersetup.php cleans up

date_default_timezone_set($TZ);
$date_now = new DateTime('NOW');

$file_templ = "../add_on/realtime.templ";   // Template file
$file_realt = "../add_on/realtime.txt";     // Realtime/Online file 

$wlink_user = "XXXX";                    // Weather Link credentials 
$wlink_pass = "YYYY";

$cumulus = array();                         // Current observation data 
$cumulus_l = array();                       // Last observation data 
                                            // Fetch template data
$file_wrk = file_get_contents($file_templ); // echo readfile("../add_on/realtime.templ");
$cumulus = explode(" ", $file_wrk);         // var_dump ($cumulus);
                                            
$handle = fopen($file_realt, "r");          // Fetch last realtime data
$file_wrk = fread($handle, 1024);
fclose($handle);
$cumulus_l = explode(" ", $file_wrk);       
                                                                            
// Get the current "conditions" information from Davis Weatherlink
$xml = simplexml_load_file('http://www.weatherlink.com/xml.php?user='.$wlink_user.'&pass='.$wlink_pass.'');  // var_dump($xml);

// If wrong "conditions" data no file update  
if ($xml->{'temp_c'} == NULL) {
    // NOP
}
else{ 

    // Please note Field no in the Cumulus spec.  -1 => array no
    $cumulus[0] = $date_now->format('d/m/y');

    $date_wrk = $xml->{'observation_time_rfc822'};                                    
    $cumulus[1] = date_create("$date_wrk")->format('H:i:s');                          // Observation time from Weatherlink
                                                                                      // echo ("$cumulus[1] $cumulus_l[1] \n");
    if ($cumulus[1] <> $cumulus_l[1])  {                                              // Update file, not same time/observation as last
    
        $cumulus[2] = $xml->{'temp_f'};                                               // Temp F
        $cumulus[3] = $xml->{'relative_humidity'};
        $cumulus[4] = $xml->{'dewpoint_f'};
        $cumulus[5] = $xml->{'davis_current_observation'}->{'wind_ten_min_avg_mph'};  // $cumulus[5] = $xml->{'wind_mph'};       // Wind avg.
        $cumulus[6] = $xml->{'wind_mph'};                                             // wind this moment
        $cumulus[7] = $xml->{'wind_degrees'};                                        
        $cumulus[8] = $xml->{'davis_current_observation'}->{'rain_rate_in_per_hr'};   // Rain rate in/h
        $cumulus[9] = $xml->{'davis_current_observation'}->{'rain_day_in'};           // Rain in inches(in)
        $cumulus[10] = $xml->{'pressure_in'};                                         // Pressure inches
        // $cumulus[11] =                                                             // Current wind direction, calculated by W34 from $cumulus[7]
        // $cumulus[12] =                                                             // Wind in Beaufort, not used by W34 
        $cumulus[13] = "mph";                                                         // Windspeed unit 
        $cumulus[14] = "F";                                                           // Temp unit
        $cumulus[15] = "in";                                                          // Pressure unit
        $cumulus[16] = "in";                                                          // Rain unit
        $cumulus[19] = $xml->{'davis_current_observation'}->{'rain_month_in'};        // Rain acc. Month inches
        $cumulus[20] = $xml->{'davis_current_observation'}->{'rain_year_in'};         // Rain acc. Year  
        $cumulus[22] = $xml->{'davis_current_observation'}->{'temp_in_f'};            // Inside temp
        $cumulus[23] = $xml->{'davis_current_observation'}->{'relative_humidity_in'}; // Inside humidity
        $cumulus[24] = $xml->{'windchill_f'};  
        $cumulus[26] = $xml->{'davis_current_observation'}->{'temp_day_high_f'};      
        $cumulus[28] = $xml->{'davis_current_observation'}->{'temp_day_low_f'};         
        $cumulus[32] = $xml->{'davis_current_observation'}->{'wind_day_high_mph'};    
        $cumulus[34] = $xml->{'davis_current_observation'}->{'pressure_day_high_in'};  // var_dump($cumulus[34]);                                                                             // Historic CSV files are available in /chartswudata
        
        $hour = date_create("$cumulus[1]")->format('H');                                // Create an save, in realtime.txt, values for trend caculation every hour 
        $hour_l = date_create("$cumulus_l[1]")->format('H');
                                                                                        // echo "\n $hour $hour_l \n";
        if ($hour <> $hour_l) {                                                         // If new hour save data for trendvalues

            $cumulus[61] = $cumulus_l[60];                                              // Save the 1 hour old temp value 
            $cumulus[60] = $cumulus[2];                                                 // Save current temp F

            $cumulus[63] = $cumulus_l[62];                                              // Save the 1 hour old press value 
            $cumulus[62] = $cumulus[10];                                                // Save current press
        }
        else {
            
            $cumulus[60] = $cumulus_l[60];                                              // Else, overwrite template data
            $cumulus[61] = $cumulus_l[61]; 
            $cumulus[62] = $cumulus_l[62]; 
            $cumulus[63] = $cumulus_l[63]; 
        }

        $cumulus[18] = round((floatval($cumulus[10]) - $cumulus[63]),4);                // Barometer trend, current - history
        $cumulus[25] = floatval($cumulus[2]) - $cumulus[61];                            // Temp trend // echo "$cumulus[25] $cumulus[18]  \n";

        if ($cumulus[0] == $cumulus_l[0]) {                                             // Same date "d/m/y" ?Current and last

            if ($cumulus[5] > $cumulus_l[30]) {                                         // Same date and if current wind 10 min avg > daily max avg => update daily max wind avg,
              
                $cumulus[30] = floatval($cumulus[5]);                                   // echo "$cumulus[30] ";     
            }
            else { 
                                                                                        // else, update with last data, owerwrite template data 
                $cumulus[30] = $cumulus_l[30];                         
            }
        }
        else {                                                                          // New day, set daily max average to current
            
            $cumulus[30] = floatval($cumulus[5]);
        }
      
        $cumulus[46] = round((($cumulus[7] + $cumulus_l[7])/2),0);                      // Wind direction average, no decimals

    // Update the realtime.txt file 
    $file_live = implode(" ",$cumulus);
    $handle = fopen($file_realt, "w");
    fwrite($handle, $file_live);
    fclose($handle);                                                                    // echo readfile("../add_on/realtime.txt");

 } // New date END

} //  Wrong "conditions" data END

?>