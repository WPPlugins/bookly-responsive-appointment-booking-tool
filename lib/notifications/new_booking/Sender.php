<?php
namespace Bookly\Lib\Notifications\NewBooking;

use Bookly\Lib\DataHolders\Booking as DataHolders;

/**
 * Class Sender
 * @package Bookly\Lib\Notifications\NewBooking
 */
class Sender
{
    /** @var DataHolders\Order */
    protected $order;
    /** @var ItemSenders\Base[] */
    protected $item_senders;
    /** @var Codes */
    protected $codes;
    /** @var string */
    protected $initial_lang;

    /**
     * Create new sender for order.
     *
     * @param DataHolders\Order $order
     * @return static
     */
    public static function createForOrder( DataHolders\Order $order )
    {
        $sender = new static();
        $sender->order = $order;
        $sender->codes = Codes::create( $order );

        return $sender;
    }

    /**
     * Send notifications.
     */
    public function send()
    {
        foreach ( $this->order->getItems() as $item ) {
            $sender = $this->getSenderForItem( $item );
            if ( $sender ) {
                // Notify client.
                $lang = $this->wpmlSwitchToItemLang( $item );
                $sender->sendToClient( $item, $lang );
                // Notify staff and admins.
                $lang = $this->wpmlSwitchToDefaultLang();
                $sender->sendToStaffAndAdmins( $item, $lang );
            }
        }
        $this->wpmlRestoreLang();
    }

    /**
     * Get sender for given order item.
     *
     * @param DataHolders\Item $item
     * @return ItemSenders\Base|null
     */
    protected function getSenderForItem( DataHolders\Item $item )
    {
        if ( ! isset ( $this->item_senders[ $item->getType() ] ) ) {
            $sender = $item->isSimple()
                ? ItemSenders\Simple::create( $this->order, $this->codes )
                : Proxy\Shared::getSenderForItem( null, $item, $this->order, $this->codes );
            $this->item_senders[ $item->getType() ] = $sender;
        }

        return $this->item_senders[ $item->getType() ];
    }

    /**
     * Switch WPML lang.
     *
     * @param string $lang
     * @return string|null
     */
    protected function wpmlSwitchLang( $lang )
    {
        global $sitepress;

        if ( $sitepress instanceof \SitePress ) {
            if ( $lang != $sitepress->get_current_language() ) {
                if ( $this->initial_lang === null ) {
                    $this->initial_lang = $sitepress->get_current_language();
                }
                $sitepress->switch_lang( $lang );
                // WPML Multilingual CMS 3.9.2 // 2018-02
                // Does not overload the date translation
                $GLOBALS['wp_locale'] = new \WP_Locale();
            }

            return $lang;
        }

        return null;
    }

    /**
     * Switch WPML to default lang.
     *
     * @return string|null
     */
    protected function wpmlSwitchToDefaultLang()
    {
        global $sitepress;

        if ( $sitepress instanceof \SitePress ) {
            return $this->wpmlSwitchLang( $sitepress->get_default_language() );
        }

        return null;
    }

    /**
     * Switch WPML to client lang of given order item.
     *
     * @param DataHolders\Item $item
     * @return string|null
     */
    protected function wpmlSwitchToItemLang( DataHolders\Item $item )
    {
        $lang = $item->getCA()->getLocale();
        if ( $lang ) {
            return $this->wpmlSwitchLang( $lang );
        } else {
            return $this->wpmlSwitchToDefaultLang();
        }
    }

    /**
     * Restore WPML lang.
     */
    protected function wpmlRestoreLang()
    {
        if ( $this->initial_lang !== null ) {
            $this->wpmlSwitchLang( $this->initial_lang );
            $this->initial_lang = null;
        }
    }
}