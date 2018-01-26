<?php
namespace BooklyLite\Lib\Utils;

/**
 * Class Price
 * @package BooklyLite\Lib\Utils
 */
abstract class Price
{
    /** @var array */
    private static $currencies = array(
        'AED' => array( 'symbol' => 'AED',  'format' => '{price|2} {symbol}' ),
        'ARS' => array( 'symbol' => '$',    'format' => '{symbol}{price|2}' ),
        'AUD' => array( 'symbol' => 'A$',   'format' => '{symbol}{price|2}' ),
        'BDT' => array( 'symbol' => '৳',    'format' => '{symbol}{price|2}' ),
        'BGN' => array( 'symbol' => 'лв.',  'format' => '{price|2} {symbol}' ),
        'BHD' => array( 'symbol' => 'BHD',  'format' => '{symbol} {price|2}' ),
        'BRL' => array( 'symbol' => 'R$',   'format' => '{symbol} {price|2}' ),
        'CAD' => array( 'symbol' => 'C$',   'format' => '{symbol}{price|2}' ),
        'CHF' => array( 'symbol' => 'CHF',  'format' => '{price|2} {symbol}' ),
        'CLP' => array( 'symbol' => '$',    'format' => '{symbol}{price|2}' ),
        'COP' => array( 'symbol' => '$',    'format' => '{symbol}{price|0}' ),
        'CRC' => array( 'symbol' => '₡',    'format' => '{symbol}{price|2}' ),
        'CZK' => array( 'symbol' => 'Kč',   'format' => '{price|2} {symbol}' ),
        'DKK' => array( 'symbol' => 'kr',   'format' => '{price|2} {symbol}' ),
        'DOP' => array( 'symbol' => 'RD$',  'format' => '{symbol}{price|2}' ),
        'EGP' => array( 'symbol' => 'EGP',  'format' => '{symbol} {price|2}' ),
        'EUR' => array( 'symbol' => '€',    'format' => '{symbol}{price|2}' ),
        'GBP' => array( 'symbol' => '£',    'format' => '{symbol}{price|2}' ),
        'GEL' => array( 'symbol' => 'lari', 'format' => '{price|2} {symbol}' ),
        'GTQ' => array( 'symbol' => 'Q',    'format' => '{symbol}{price|2}' ),
        'HKD' => array( 'symbol' => 'HK$',  'format' => '{symbol}{price|2}' ),
        'HRK' => array( 'symbol' => 'kn',   'format' => '{price|2} {symbol}' ),
        'HUF' => array( 'symbol' => 'Ft',   'format' => '{price|2} {symbol}' ),
        'IDR' => array( 'symbol' => 'Rp',   'format' => '{price|2} {symbol}' ),
        'ILS' => array( 'symbol' => '₪',    'format' => '{price|2} {symbol}' ),
        'INR' => array( 'symbol' => '₹',    'format' => '{price|2} {symbol}' ),
        'ISK' => array( 'symbol' => 'kr',   'format' => '{price|0} {symbol}' ),
        'JPY' => array( 'symbol' => '¥',    'format' => '{symbol}{price|0}' ),
        'KES' => array( 'symbol' => 'KSh',  'format' => '{symbol} {price|2}' ),
        'KRW' => array( 'symbol' => '₩',    'format' => '{price|2} {symbol}' ),
        'KZT' => array( 'symbol' => 'тг.',  'format' => '{price|2} {symbol}' ),
        'LAK' => array( 'symbol' => '₭',    'format' => '{price|0} {symbol}' ),
        'MUR' => array( 'symbol' => 'Rs',   'format' => '{symbol}{price|2}' ),
        'MXN' => array( 'symbol' => '$',    'format' => '{symbol}{price|2}' ),
        'MYR' => array( 'symbol' => 'RM',   'format' => '{price|2} {symbol}' ),
        'NAD' => array( 'symbol' => 'N$',   'format' => '{symbol}{price|2}' ),
        'NGN' => array( 'symbol' => '₦',    'format' => '{symbol}{price|2}' ),
        'NOK' => array( 'symbol' => 'Kr',   'format' => '{symbol} {price|2}' ),
        'NZD' => array( 'symbol' => '$',    'format' => '{symbol}{price|2}' ),
        'OMR' => array( 'symbol' => 'OMR',  'format' => '{price|3} {symbol}' ),
        'PEN' => array( 'symbol' => 'S/.',  'format' => '{symbol}{price|2}' ),
        'PHP' => array( 'symbol' => '₱',    'format' => '{price|2} {symbol}' ),
        'PKR' => array( 'symbol' => 'Rs.',  'format' => '{symbol} {price|0}' ),
        'PLN' => array( 'symbol' => 'zł',   'format' => '{price|2} {symbol}' ),
        'PYG' => array( 'symbol' => '₲',    'format' => '{symbol}{price|2}' ),
        'QAR' => array( 'symbol' => 'QAR',  'format' => '{price|2} {symbol}' ),
        'RMB' => array( 'symbol' => '¥',    'format' => '{price|2} {symbol}' ),
        'RON' => array( 'symbol' => 'lei',  'format' => '{price|2} {symbol}' ),
        'RSD' => array( 'symbol' => 'din.', 'format' => '{symbol}{price|0}' ),
        'RUB' => array( 'symbol' => 'руб.', 'format' => '{price|2} {symbol}' ),
        'SAR' => array( 'symbol' => 'SAR',  'format' => '{price|2} {symbol}' ),
        'SEK' => array( 'symbol' => 'kr',   'format' => '{price|2} {symbol}' ),
        'SGD' => array( 'symbol' => '$',    'format' => '{symbol}{price|2}' ),
        'THB' => array( 'symbol' => '฿',    'format' => '{price|2} {symbol}' ),
        'TRY' => array( 'symbol' => 'TL',   'format' => '{price|2} {symbol}' ),
        'TWD' => array( 'symbol' => 'NT$',  'format' => '{price|2} {symbol}' ),
        'UAH' => array( 'symbol' => '₴',    'format' => '{price|2} {symbol}' ),
        'UGX' => array( 'symbol' => 'UGX',  'format' => '{symbol} {price|0}' ),
        'USD' => array( 'symbol' => '$',    'format' => '{symbol}{price|2}' ),
        'VND' => array( 'symbol' => 'VNĐ',  'format' => '{price|0} {symbol}' ),
        'XAF' => array( 'symbol' => 'FCFA', 'format' => '{price|0} {symbol}' ),
        'XOF' => array( 'symbol' => 'CFA',  'format' => '{symbol} {price|2}' ),
        'ZAR' => array( 'symbol' => 'R',    'format' => '{symbol} {price|2}' ),
        'ZMW' => array( 'symbol' => 'K',    'format' => '{symbol}{price|2}' ),
    );

    /** @var array */
    private static $formats = array(
        '{symbol}{price|2}',
        '{symbol}{price|1}',
        '{symbol}{price|0}',
        '{symbol} {price|2}',
        '{symbol} {price|1}',
        '{symbol} {price|0}',
        '{price|2}{symbol}',
        '{price|1}{symbol}',
        '{price|0}{symbol}',
        '{price|3} {symbol}',
        '{price|2} {symbol}',
        '{price|1} {symbol}',
        '{price|0} {symbol}',
    );

    /**
     * Format price.
     *
     * @param float $price
     * @return string
     */
    public static function format( $price )
    {
        $price    = (float) $price;
        $currency = get_option( 'bookly_pmt_currency' );
        $format   = get_option( 'bookly_pmt_price_format' );
        $symbol   = self::$currencies[ $currency ]['symbol'];

        if ( preg_match( '/{price\|(\d)}/', $format, $match ) ) {
            return strtr( $format, array(
                '{symbol}' => $symbol,
                "{price|{$match[1]}}" => number_format_i18n( $price, $match[1] )
            ) );
        }

        return number_format_i18n( $price, 2 );
    }

    /**
     * Get supported currencies.
     *
     * @return array
     */
    public static function getCurrencies()
    {
        return self::$currencies;
    }

    /**
     * Get supported price formats.
     *
     * @return array
     */
    public static function getFormats()
    {
        return self::$formats;
    }
}