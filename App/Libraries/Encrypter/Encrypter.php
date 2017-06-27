<?php

namespace App\Libraries\Encrypter;

class Encrypter
{

    /**
     * Hashes the password string.
     * Default hash algorithm: BCRYPT
     * 
     * @param string $password Password string to be hashed
     * @param array $options Hash options
     * @return string Hashed password string
     */
    public static function hashPassword( $password, $options )
    {
        return password_hash( $password, PASSWORD_BCRYPT, $options );
    }

    /**
     * Validates whether the password and the password hash match.
     * 
     * @param type $passwordToVerify Password string
     * @param type $passwordHash Hashed version of the password string
     * @return boolean TRUE -> The password string and the password hash match
     */
    public static function verifyPassword( $passwordToVerify, $passwordHash )
    {
        return password_verify( $passwordToVerify, $passwordHash );
    }

    /**
     * Checks whether the password hash should be rahashed with the new options.
     * Default hash algorithm: BCRYPT
     * 
     * @param string $passwordHash Password string
     * @param array $options Hash options
     * @return boolean  TRUE -> The password should be rehashed
     */
    public static function checkForRehashing( $passwordHash, $options )
    {
        return password_needs_rehash( $passwordHash, PASSWORD_BCRYPT, $options );
    }
	
	/**
     * Generates rondom hash token
     * 
     * @param array $email Email string
     * @return token with length sha3 output string
     */
	public static function generateHash( $email )
	{
		 return sha3($email .strtotime(date('Y-m-d H:i:s')));
	}
}
