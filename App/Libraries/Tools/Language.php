<?php 

namespace App\Libraries\Tools;

class Language {
    
    public static function getDefaultLanguage() {
        
        if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
        {
            return self::parseDefaultLanguage($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
        } else {
            return self::parseDefaultLanguage(NULL);
        }
    }
    
    private function parseDefaultLanguage($http_accept, $deflang = "en") 
    {
        if(isset($http_accept) && strlen($http_accept) > 1)  
        {
            # Split possible languages into array
            $x = explode(",",$http_accept);
            foreach ($x as $val) 
            {
                #check for q-value and create associative array. No q-value means 1 by rule
                if(preg_match("/(.*);q=([0-1]{0,1}.\d{0,4})/i",$val,$matches))
                    $lang[$matches[1]] = (float)$matches[2];
                    else
                        $lang[$val] = 1.0;
            }
    
            #return default language (highest q-value)
            $qval = 0.0;
            foreach ($lang as $key => $value) 
            {
                if ($value > $qval) 
                {
                    $qval = (float)$value;
                    $deflang = $key;
                }
            }
        }
        
        if( strpos($deflang, '-') > 0 )
        {
            $deflang = substr( $deflang, 0, 2 );
        }
        
        if( strpos($deflang, '_') > 0 )
        {
            $deflang = substr( $deflang, 0, 2 );
        }
        
        return strtolower($deflang);
    }
}