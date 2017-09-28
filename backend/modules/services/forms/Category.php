<?php
namespace BooklyLite\Backend\Modules\Services\Forms;

use BooklyLite\Lib;

/**
 * Class Category
 * @package BooklyLite\Backend\Modules\Services\Forms
 */
class Category extends Lib\Base\Form
{
    protected static $entity_class = 'Category';

    /**
     * Configure the form.
     */
    public function configure()
    {
        $this->setFields( array( 'name' ) );
    }

}
