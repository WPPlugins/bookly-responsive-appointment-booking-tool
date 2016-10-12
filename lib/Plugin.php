<?php
namespace BooklyLite\Lib;

/**
 * Class Plugin
 * @package BooklyLite\Lib
 */
abstract class Plugin extends Base\Plugin
{
    protected static $prefix = 'ab_';
    protected static $title;
    protected static $version;
    protected static $slug;
    protected static $directory;
    protected static $main_file;
    protected static $basename;
    protected static $text_domain;
}