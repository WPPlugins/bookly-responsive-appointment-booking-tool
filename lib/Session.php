<?php
namespace Bookly\Lib;

/**
 * Class Session
 * @package Bookly\Lib
 */
abstract class Session
{
    /**
     * Get value from session.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function get( $name, $default = null )
    {
        if ( self::has( $name ) ) {
            return $_SESSION['bookly'][ $name ];
        }

        return $default;
    }

    /**
     * Set value to session.
     *
     * @param string $name
     * @param mixed $value
     */
    public static function set( $name, $value )
    {
        $_SESSION['bookly'][ $name ] = $value;
    }

    /**
     * Check if a named value exists in session.
     *
     * @param string $name
     * @return bool
     */
    public static function has( $name )
    {
        return isset ( $_SESSION['bookly'][ $name ] );
    }

    /**
     * Destroy value in session.
     *
     * @param string $name
     */
    public static function destroy( $name )
    {
        unset ( $_SESSION['bookly'][ $name ] );
    }

    /**
     * Get named variable of a frontend booking form.
     *
     * @param string $form_id
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function getFormVar( $form_id, $name, $default = null )
    {
        if ( self::hasFormVar( $form_id, $name ) ) {
            return $_SESSION['bookly']['forms'][ $form_id ][ $name ];
        }

        return $default;
    }

    /**
     * Set named variable for a frontend booking form.
     *
     * @param string $form_id
     * @param string $name
     * @param mixed $value
     */
    public static function setFormVar( $form_id, $name, $value )
    {
        $_SESSION['bookly']['forms'][ $form_id ][ $name ] = $value;
    }

    /**
     * Check if a named variable exists for a frontend booking form.
     *
     * @param string $form_id
     * @param string $name
     * @return bool
     */
    public static function hasFormVar( $form_id, $name )
    {
        return isset ( $_SESSION['bookly']['forms'][ $form_id ][ $name ] );
    }

    /**
     * Get data of all booking forms.
     *
     * @return array
     */
    public static function getAllFormsData()
    {
        if ( isset ( $_SESSION['bookly']['forms'] ) ) {
            return $_SESSION['bookly']['forms'];
        }

        return array();
    }

    /**
     * Destroy named variable in booking form data.
     *
     * @param string $form_id
     * @param string $name
     */
    public static function destroyFormVar( $form_id, $name )
    {
        unset ( $_SESSION['bookly']['forms'][ $form_id ][ $name ] );
    }

    /**
     * Destroy all data of a booking form.
     *
     * @param string $form_id
     */
    public static function destroyFormData( $form_id )
    {
        unset ( $_SESSION['bookly']['forms'][ $form_id ] );
    }

}