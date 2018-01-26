<?php
namespace BooklyLite\Backend\Modules\Message;

use BooklyLite\Lib;

/**
 * Class Controller
 * @package BooklyLite\Backend\Modules\Message
 */
class Controller extends Lib\Base\Controller
{
    const page_slug = 'bookly-messages';

    /**
     * Default action
     */
    public function index()
    {
        $this->enqueueStyles( array(
            'backend' => array( 'bootstrap/css/bootstrap-theme.min.css', ),
        ) );

        $this->enqueueScripts( array(
            'backend' => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/datatables.min.js'          => array( 'jquery' ),
            ),
            'module'  => array( 'js/message.js' => array( 'jquery' ) ),
        ) );

        wp_localize_script( 'bookly-message.js', 'BooklyL10n', array(
            'csrf_token'  => Lib\Utils\Common::getCsrfToken(),
            'datatable' => array(
                'zeroRecords' => __( 'No records.', 'bookly' ),
                'processing'  => __( 'Processing...', 'bookly' ),
                'per_page'    => __( 'messages', 'bookly' ),
                'paginate' => array(
                    'first'    => __( 'First', 'bookly' ),
                    'previous' => __( 'Previous', 'bookly' ),
                    'next'     => __( 'Next', 'bookly' ),
                    'last'     => __( 'Last', 'bookly' ),
                )
            )
        ) );
        $this->render( 'index' );
    }

    /**
     * Get messages
     */
    public function executeGetMessages()
    {
        $query = Lib\Entities\Message::query( 'm' );
        $total = $query->count();

        $query->select( 'm.created, m.subject, m.seen, m.body, m.message_id' )
            ->sortBy( 'm.seen, m.message_id' )->order( 'DESC' );

        $query->limit( $this->getParameter( 'length' ) )->offset( $this->getParameter( 'start' ) );

        $data = $query->fetchArray();
        foreach ( $data as &$row ) {
            $row['created'] = Lib\Utils\DateTime::formatDateTime( $row['created'] );
        }

        wp_send_json( array(
            'draw'            => ( int ) $this->getParameter( 'draw' ),
            'recordsTotal'    => $total,
            'recordsFiltered' => count( $data ),
            'data'            => $data,
        ) );
    }

    /**
     * Mark all messages was read
     */
    public function executeMarkReadAllMessages()
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
    public function executeMarkReadMessages()
    {
        Lib\API::seenMessages( (array) $this->getParameter( 'message_ids' ) );
        wp_send_json_success();
    }

}