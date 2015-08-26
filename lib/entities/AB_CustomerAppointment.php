<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_CustomerAppointment
 */
class AB_CustomerAppointment extends AB_Entity {

    protected static $table = 'ab_customer_appointments';

    protected static $schema = array(
        'id'                => array( 'format' => '%d' ),
        'customer_id'       => array( 'format' => '%d' ),
        'appointment_id'    => array( 'format' => '%d' ),
        'number_of_persons' => array( 'format' => '%d', 'default' => 1 ),
        'custom_fields'     => array( 'format' => '%s' ),
        'coupon_code'       => array( 'format' => '%s' ),
        'coupon_discount'   => array( 'format' => '%d' ),
        'coupon_deduction'  => array( 'format' => '%d' ),
        'token'             => array( 'format' => '%s' ),
        'time_zone_offset'  => array( 'format' => '%d' ),
    );

    /** @var AB_Customer */
    public $customer = null;

    /**
     * Save entity to database.
     * Generate token before saving.
     *
     * @return int|false
     */
    public function save()
    {
        // Generate new token if it is not set.
        if ( $this->get( 'token' ) == '' ) {
            $test = new self();
            do {
                $token = md5( uniqid( time(), true ) );
            }
            while ( $test->loadBy( array( 'token' => $token ) ) === true );

            $this->set( 'token', $token );
        }

        return parent::save();
    }

    /**
     * Get array of custom fields with labels and values.
     *
     * @return array
     */
    public function getCustomFields()
    {
        $result = array();
        if ( $this->get( 'custom_fields' ) != '' ) {
            $custom_fields = array();
            foreach ( json_decode( get_option( 'ab_custom_fields' ) ) as $field ) {
                $custom_fields[ $field->id ] = $field;
            }
            $data = json_decode( $this->get( 'custom_fields' ) );
            if ( is_array( $data ) ) {
                foreach ($data as $value) {
                    if ( array_key_exists( $value->id, $custom_fields ) ) {
                        $result[] = array(
                            'id'    => $value->id,
                            'label' => $custom_fields[ $value->id ]->label,
                            'value' => isset($value->value) ? (is_array( $value->value ) ? implode( ', ', $value->value ) : $value->value) : '',
                        );
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get formatted custom fields.
     *
     * @param string $format
     * @return string
     */
    public function getFormattedCustomFields( $format )
    {
        $result = '';
        switch ( $format ) {
            case 'html':
                foreach ( $this->getCustomFields() as $custom_field ) {
                    if( '' != $custom_field[ 'value' ]) {
                        $result .= sprintf(
                            '<tr valign=top><td>%s:&nbsp;</td><td>%s</td></tr>',
                            $custom_field['label'], $custom_field['value']
                        );
                    }
                }
                if ( $result != '' ) {
                    $result = "<table cellspacing=0 cellpadding=0 border=0>$result</table>";
                }
                break;

            case 'text':
                foreach ( $this->getCustomFields() as $custom_field ) {
                    if( '' != $custom_field[ 'value' ]) {
                        $result .= sprintf(
                            "%s: %s\n",
                            $custom_field['label'], $custom_field['value']
                        );
                    }
                }
                break;
        }

        return $result;
    }

}