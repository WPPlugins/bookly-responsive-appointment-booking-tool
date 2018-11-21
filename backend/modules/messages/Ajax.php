<?php
namespace Bookly\Backend\Modules\Messages;

use Bookly\Lib;

/**
 * Class Page
 * @package Bookly\Backend\Modules\Messages
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     * Get messages
     */
    public static function getMessages()
    {
        $query = Lib\Entities\Message::query( 'm' );
        $total = $query->count();

        $query->select( 'm.created, m.subject, m.seen, m.body, m.message_id' )
            ->sortBy( 'm.seen, m.message_id' )->order( 'DESC' );

        $query->limit( self::parameter( 'length' ) )->offset( self::parameter( 'start' ) );

        $data = $query->fetchArray();
        foreach ( $data as &$row ) {
            $row['created'] = Lib\Utils\DateTime::formatDateTime( $row['created'] );
        }

        wp_send_json( array(
            'draw'            => ( int ) self::parameter( 'draw' ),
            'recordsTotal'    => $total,
            'recordsFiltered' => count( $data ),
            'data'            => $data,
        ) );
    }

    /**
     * Mark all messages was read
     */
    public static function markReadAllMessages()
    {
        $messages = Lib\Entities\Message::query( 'm' )->select( 'm.message_id' )->whereNot( 'm.seen', 1 )->fetchArray();
        $message_ids = array();
        foreach ( $messages as $message ) {
            $message_ids[] = $message['message_id'];
        }

        if ( $message_ids ) {
            Lib\API::seenMessages( $message_ids );
        }
        wp_send_json_success();
    }

    /**
     * Mark some massages was read
     */
    public static function markReadMessages()
    {
        Lib\API::seenMessages( (array) self::parameter( 'message_ids' ) );
        wp_send_json_success();
    }
}