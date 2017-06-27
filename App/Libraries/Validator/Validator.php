<?php

namespace App\Libraries\Validator;

class Validator
{
    /**
     * Validates the password length
     *
     * @param string $password Password string
     * @param int $minLength Min length of the string
     * @param int $maxLength Max length of the string
     * @return boolean TRUE -> If the password length is greater than/equal to $minLength and smaller than/equal to $maxLength.
     */
    public static function validatePasswordLength( $password, $minLength, $maxLength )
    {
        $passwordLength = strlen( trim( $password ) );
		return $passwordLength >= $minLength && $passwordLength <= $maxLength;
    }
	
	/**
     * Validates if the password contains at least one number, symbol, uppercase and lowercase letters 
     *
     * @param string $password Password string
     * @return boolean TRUE -> If the password contains at least one of the characters.
     */
	public static function asciiChecker( $password )
	{
		if (!preg_match("#[0-9]+#", $password)) {
			return false;
		}

		if (!preg_match("#[a-zA-Z]+#", $password)) {
			return false;
		}
		
		if( !preg_match("#\W+#", $password) ) {
			return false;
		}
		
		return true;
	}

    /**
     * Validates whether the password and the password confirmation are equal
     * 
     * @param string $password
     * @param string $passwordConfirmation
     * @return boolean TRUE -> The password is equal to the password confirmation
     */
    public static function validatePasswordMatch( $password, $passwordConfirmation )
    {
        return trim( $password ) === trim( $passwordConfirmation );
    }

    /**
     * Checks if there is an empty variable.
     * The following values are considered empty:
     * empty string, empty array, null
     * 
     * Values are retrived with 'func_get_args()' function
     * 
     * @return boolean TRUE -> There is an empty variable
     */
    public static function areVariablesEmpty()
    {
        foreach ( func_get_args() as $variable ) {
            if ( is_string( $variable ) ) {
                $variable = trim( $variable );
                if ( $variable === "" ) {
                    return true;
                }
            }
            if ( is_array( $variable ) ) {
                if ( count( $variable ) === 0 ) {
                    return true;
                }
            }
            if ( is_null( $variable ) ) {
                return true;
            }
        }
        return false;
    }
}
