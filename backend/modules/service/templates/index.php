<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="ab-title"><?php _e('Services', 'ab') ?></div>
<div style="min-width: 800px;">
<div class="ab-left-bar">
    <div id="ab-categories-list">
      <div class="ab-category-item ab-active ab-main-category-item" data-id=""><?php _e('All Services','ab') ?></div>
        <div class="ab-category-item-list">
          <?php if (count($category_collection)): ?>
              <?php foreach ($category_collection as $category):?>
                <div class="ab-category-item" data-id="<?php echo $category->id ?>">
                  <span class="left displayed-value"><?php esc_html_e( $category->name ) ?></span>
                  <a href="#" class="left ab-hidden ab-edit"></a>
                  <input class="value ab-value" type="text" name="name" value="<?php esc_attr_e( $category->name ) ?>" style="display: none" />
                  <a href="#" class="left ab-hidden ab-delete"></a>
                </div>
              <?php endforeach ?>
          <?php endif ?>
      </div>
    </div>
    <input type="hidden" id="color" />
    <div id="new_category_popup" class="ab-popup-wrapper">
      <input class="btn btn-info ab-popup-trigger" data- type="submit" value="<?php _e('New Category','ab') ?>" />
      <div class="ab-popup" style="display: none">
          <div class="ab-arrow"></div>
          <div class="ab-content">
              <form method="post" id="new-category-form">
                <table class="form-horizontal">
                  <tr>
                    <td>
                      <input class="ab-clear-text" style="width: 170px" type="text" name="name" />
                      <input type="hidden" name="action" value="ab_category_form" />
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <input type="submit" class="btn btn-info ab-popup-save ab-update-button" value="<?php _e('Save category','ab') ?>" />
                      <a class="ab-popup-close" href="#"><?php _e('Cancel','ab') ?></a>
                    </td>
                  </tr>
                </table>
                <a class="ab-popup-close ab-popup-close-icon" href="#"></a>
              </form>
          </div>
      </div>
    </div>
</div>
<div class="ab-right-content" id="ab_services_wrapper">
    <h2 class="ab-category-title"><?php _e('All services','ab') ?></h2>
    <div class="no-result"<?php if (count($category_collection)) : ?> style="display: none"<?php endif; ?>><?php _e( 'No services found. Please add services.','ab' ) ?></div>
    <div class="list-wrapper">
        <div id="ab-services-list">
            <?php include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'list.php' ?>
        </div>
        <div class="list-actions">
            <a class="add-service btn btn-info" href="#"><?php _e('Add Service','ab') ?></a>
            <a class="delete btn btn-info" href="#"><?php _e('Delete','ab') ?></a>
        </div>
    </div>
</div>
</div>
