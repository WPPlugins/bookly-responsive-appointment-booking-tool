<?php
namespace Bookly\Backend\Modules\Services\Proxy;

use Bookly\Lib;

/**
 * Class Shared
 * @package Bookly\Backend\Modules\Services\Proxy
 *
 * @method static void  enqueueAssetsForServices() Enqueue assets for page Services.
 * @method static array prepareServiceColors( array $colors, int $service_id, int $service_type ) Prepare colors for service.
 * @method static array prepareUpdateService( array $data ) Prepare update service settings in add-ons.
 * @method static array prepareUpdateServiceResponse( array $response, Lib\Entities\Service $service, array $_post ) Prepare response for updated service.
 * @method static void  renderAfterServiceList( array $service_collection ) Render content after services forms.
 * @method static void  renderServiceForm( array $service ) Render content in service form.
 * @method static void  renderServiceFormHead( array $service ) Render top content in service form.
 * @method static array serviceCreated( Lib\Entities\Service $service, array $_post ) Service created.
 * @method static void  serviceDeleted( int $service_id ) Service deleted.
 * @method static array updateService( array $alert, Lib\Entities\Service $service, array $_post ) Update service settings in add-ons.
 */
abstract class Shared extends Lib\Base\Proxy
{

}