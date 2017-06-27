<?php 

namespace App\Libraries\CSRF;


class CsrfHelper 
{

    public function generateToken( $unique_form_name )
    {
        if (function_exists("hash_algos") and in_array("sha512",hash_algos()))
        {
            $token = hash("sha512",mt_rand(0,mt_getrandmax()));
        }
        else
        {
            $token=' ';
            for ($i=0;$i<128;++$i)
            {
                $r=mt_rand(0,35);
                if ($r<26)
                {
                    $c=chr(ord('a')+$r);
                }
                else
                {
                    $c=chr(ord('0')+$r-26);
                }
                $token.=$c;
            }
        }
        $this->store_in_session( $unique_form_name, $token );
        return $token;
    }
    
    public function validateToken( $unique_form_name, $token_value )
    {
        $token = $this->get_from_session( $unique_form_name );
        if ($token===false)
        {
            return false;
        }
        elseif ($token===$token_value)
        {
            $result = true;
        }
        else
        {
            $result = false;
        }
        $this->unset_session( $unique_form_name );
        return $result;
    }
    
    
    private function store_in_session( $key, $value )
    {
        if (isset($_SESSION))
        {
            $_SESSION[$key]=$value;
        }
    }
    
    private function unset_session( $key )
    {
        $_SESSION[$key]=' ';
        unset( $_SESSION[$key] );
    }
    
    private function get_from_session( $key )
    {
        if ( isset( $_SESSION[$key] ) )
        {
            return $_SESSION[$key];
        }
        else 
        {  
            return false; 
        }
    }
    

}