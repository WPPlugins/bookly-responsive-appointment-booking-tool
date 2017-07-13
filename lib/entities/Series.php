<?php
namespace BooklyLite\Lib\Entities;

use BooklyLite\Lib;

/**
 * Class Series
 * @package BooklyLite\Lib\Entities
 */
class Series extends Lib\Base\Entity
{
    protected static $table = 'ab_series';

    protected static $schema = array(
        'id'     => array( 'format' => '%d' ),
        'repeat' => array( 'format' => '%s' ),
        'token'  => array( 'format' => '%s' ),
    );

    protected static $cache = array();
}