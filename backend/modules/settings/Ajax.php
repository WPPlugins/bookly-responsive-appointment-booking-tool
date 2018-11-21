<?php
namespace Bookly\Backend\Modules\Settings;

use Bookly\Lib;

/**
 * Class Ajax
 * @package Bookly\Backend\Modules\Settings
 */
class Ajax extends Page
{
    /**
     * Ajax request for Holidays calendar
     */
    public static function settingsHoliday()
    {
        $id      = self::parameter( 'id',  false );
        $day     = self::parameter( 'day', false );
        $holiday = self::parameter( 'holiday' ) == 'true';
        $repeat  = (int) ( self::parameter( 'repeat' ) == 'true' );

        // update or delete the event
        if ( $id ) {
            if ( $holiday ) {
                Lib\Entities\Holiday::query()
                    ->update()
                    ->set( 'repeat_event', $repeat )
                    ->where( 'id', $id )
                    ->where( 'parent_id', $id , 'OR' )
                    ->execute();
            } else {
                Lib\Entities\Holiday::query()
                    ->delete()
                    ->where( 'id', $id )
                    ->where( 'parent_id', $id, 'OR' )
                    ->execute();
            }
            // add the new event
        } elseif ( $holiday && $day ) {
            $holiday = new Lib\Entities\Holiday( );
            $holiday
                ->setDate( $day )
                ->setRepeatEvent( $repeat )
                ->save();
            foreach ( Lib\Entities\Staff::query()->fetchArray() as $employee ) {
                $staff_holiday = new Lib\Entities\Holiday();
                $staff_holiday
                    ->setDate( $day)
                    ->setRepeatEvent( $repeat )
                    ->setStaffId( $employee['id'] )
                    ->setParent( $holiday )
                    ->save();
            }
        }

        // and return refreshed events
        echo json_encode( self::_getHolidays() );
        exit;
    }
}