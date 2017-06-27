<?php 

namespace App\Libraries\Tools;

class Tools {
    
    public static function isValidEmail( $email )
    {
        if (filter_var( $email, FILTER_VALIDATE_EMAIL) ) {
            if ( strpos( $email, '+') !== false ) {
                return false;
               
            } else {
                return true;
                
            }
        } else {
            return false;
        }

    }

    /**
     * @param $basepath
     * @param $filename
     * @return string
     */
    public static function createFilePathS3( $basepath, $filename )
    {
        //$baseFilename = substr( $filename, 0, ( strrpos( $filename, ".") ) );

        //echo $filename;
        //die;
        //$fileExtension = Self::fileExtension( $filename ); 
        
        $lastSix = substr( $filename, -6);

        $lastSixArray = str_split( $lastSix, 1 );

        return $basepath . "/" . implode( "/", $lastSixArray  ) . "/" . $filename; 
    }


    public static function fileExtension( $filename )
    {
       $exts = explode(".", $filename);
       return $exts[ count($exts)-1 ];
    }
    
    public static function baseFileName( $filename )
    {
        return substr( $filename, 0, ( strrpos( $filename, ".") ) );
    }
    
    
}