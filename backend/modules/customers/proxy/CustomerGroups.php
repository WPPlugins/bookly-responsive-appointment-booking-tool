<?php
namespace Bookly\Backend\Modules\Customers\Proxy;

use Bookly\Lib;

/**
 * Class CustomerGroups
 * @package Bookly\Backend\Modules\Customers\Proxy
 *
 * @method static array prepareCustomerListData( array $data, array $row ) Prepare 'Customer Groups' data in customers table.
 * @method static Lib\Query prepareCustomerQuery( Lib\Query $query ) Prepare 'Customer Groups' query in customers table.
 * @method static string prepareCustomerSelect( string $select ) Prepare 'Customer Groups' select in customers table.
 * @method static array prepareExportTitles( array $titles ) Prepare 'Customer Groups' titles in customers export.
 * @method static void renderExportDialogRow() Render 'Customer Group' row in export customer dialog.
 * @method static void renderCustomerTableHeader() Render 'Customer Group' in customers table.
 */
abstract class CustomerGroups extends Lib\Base\Proxy
{

}