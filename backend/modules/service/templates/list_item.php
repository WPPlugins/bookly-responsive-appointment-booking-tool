<?php
/**
 * @var array $category_collection
 * @var array $staff_collection
 * @var AB_Service $service
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<tr id="<?php echo $service->id ?>" class="<?php echo $row_class ?>">
    <td class="first service-color-cell">
        <div class="service-color-wrapper">
            <input type="hidden" class="service-color" name="color" value="<?php echo $service->color ?>" />
        </div>
    </td>
    <td class="title editable-cell">
        <?php if ( $service->title ) : ?>
            <div class="displayed-value"><?php echo esc_html($service->title) ?></div>
            <input class="value ab-value" type="text" name="title" value="<?php echo esc_attr( $service->title ) ?>" style="display: none" />
        <?php else : ?>
            <div class="displayed-value" style="display: none"></div>
            <input class="value ab-value" type="text" name="title" />
        <?php endif; ?>
    </td>
    <td>
        <select name="duration">
            <?php
                $time_interval  = get_option( 'ab_settings_time_slot_length' );
            ?>
            <!-- Build service duration choices with the range from Time Interval Option to 12. -->
                <?php for ( $j = $time_interval; $j <= 720; $j += $time_interval ) : ?>
                    <?php
                        $duration =  $j * 60;
                        $duration_output = AB_Service::durationToString( $duration );
                    ?>
                    <option value="<?php echo $duration ?>" <?php selected($service->duration, $duration) ?>>
                        <?php echo $duration_output ?>
                    </option>
                <?php endfor; ?>
        </select>
    </td>
    <td align='right' class="editable-cell price">
        <div class="displayed-value ab-rtext"><?php echo $service->price ?></div>
        <?php if ( $service->price ) : ?>
            <input class="value ab-text-focus" type="number" min="0.00" step="any" name="price" value="<?php echo esc_attr( $service->price ) ?>" style="display: none" />
        <?php else : ?>
            <input class="value ab-text-focus" type="number" min="0.00" step="any" name="price" />
        <?php endif; ?>
    </td>
    <td align='right' class="editable-cell capacity">
        <div class="displayed-value ab-rtext">1</div>
        <input class="value ab-text-focus" type="number" min="1" step="any" name="capacity" value="1" style="display: none" />
    </td>
    <td>
        <?php if ( count( $staff_collection ) ) : ?>
            <div class="btn-group">
                <?php
                    $assigned_staff_ids = $service->staff_ids ? explode(',', $service->staff_ids) : array();
                    $all_staff_selected = count( $assigned_staff_ids ) == count( $staff_collection );
                ?>
                <button class="btn btn-info"><i class="icon-user icon-white"></i> <span class=staff-count><?php echo $service->total_staff ?></span></button>
                <button class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a href="javascript:void(0)">
                            <input type="checkbox" id="service_<?php echo $service->id ?>_all_staff" class="all-staff"<?php if ( $all_staff_selected ) : ?> checked="checked" <?php endif; ?> />
                            <label class="inline" for="service_<?php echo $service->id ?>_all_staff"><?php _e('All staff','ab') ?></label>
                        </a>
                    </li>
                    <?php foreach ( $staff_collection as $i => $staff ) : ?>
                        <li>
                            <a href="javascript:void(0)" style="padding-left: 30px">
                                <?php $staff_checked = in_array( $staff->id, $assigned_staff_ids ) ?>
                                <input type="checkbox" name="staff_ids[]" class="staff" id="service_<?php echo $service->id ?>_staff_<?php echo $i ?>" value="<?php echo $staff->id ?>"<?php if ( $staff_checked ) : ?> checked="checked"<?php endif; ?>/>
                                <label class="inline" for="service_<?php echo $service->id ?>_staff_<?php echo $i ?>">
                                    <?php echo esc_html($staff->full_name) ?>
                                </label>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else : ?>
            &nbsp;
        <?php endif; ?>
    </td>
    <td>
      <?php if ( count( $category_collection ) ) : ?>
          <select name="category_id">
              <option value="0"></option>
              <?php foreach ( $category_collection as $category ) : ?>
                  <option value="<?php echo $category->id ?>" <?php selected($category->id,  $service->category_id) ?>>
                      <?php echo esc_html($category->name) ?>
                  </option>
              <?php endforeach; ?>
          </select>
      <?php else: ?>
          &nbsp;
      <?php endif; ?>
    </td>
    <td class="last">
        <input type="checkbox" class="row-checker" />
    </td>
</tr>