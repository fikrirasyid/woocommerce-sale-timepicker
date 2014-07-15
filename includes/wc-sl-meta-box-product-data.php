<?php
/**
 * Product Data
 *
 * Displays the product data box, tabbed, with several panels covering price, stock etc. Modified for timepicker use
 *
 * @author 		WooThemes
 * @category 	Admin
 * @package 	WooCommerce/Admin/Meta Boxes
 * @version     2.1.0
 */
class WC_SL_Meta_Box_Product_Data extends WC_Meta_Box_Product_Data{

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
		global $post, $wpdb, $thepostid;

		wp_nonce_field( 'woocommerce_save_data', 'woocommerce_meta_nonce' );

		$thepostid = $post->ID;

		if ( $terms = wp_get_object_terms( $post->ID, 'product_type' ) )
			$product_type = sanitize_title( current( $terms )->name );
		else
			$product_type = apply_filters( 'default_product_type', 'simple' );

		$product_type_selector = apply_filters( 'product_type_selector', array(
			'simple' 	=> __( 'Simple product', 'woocommerce' ),
			'grouped' 	=> __( 'Grouped product', 'woocommerce' ),
			'external' 	=> __( 'External/Affiliate product', 'woocommerce' ),
			'variable'  => __( 'Variable product', 'woocommerce' )
		), $product_type );

		$type_box  = '<label for="product-type"><select id="product-type" name="product-type"><optgroup label="' . __( 'Product Type', 'woocommerce' ) . '">';
		foreach ( $product_type_selector as $value => $label )
			$type_box .= '<option value="' . esc_attr( $value ) . '" ' . selected( $product_type, $value, false ) .'>' . esc_html( $label ) . '</option>';
		$type_box .= '</optgroup></select></label>';

		$product_type_options = apply_filters( 'product_type_options', array(
			'virtual' => array(
				'id'            => '_virtual',
				'wrapper_class' => 'show_if_simple',
				'label'         => __( 'Virtual', 'woocommerce' ),
				'description'   => __( 'Virtual products are intangible and aren\'t shipped.', 'woocommerce' ),
				'default'       => 'no'
			),
			'downloadable' => array(
				'id'            => '_downloadable',
				'wrapper_class' => 'show_if_simple',
				'label'         => __( 'Downloadable', 'woocommerce' ),
				'description'   => __( 'Downloadable products give access to a file upon purchase.', 'woocommerce' ),
				'default'       => 'no'
			)
		) );

		foreach ( $product_type_options as $key => $option ) {
			$selected_value = get_post_meta( $post->ID, '_' . $key, true );

			if ( $selected_value == '' && isset( $option['default'] ) ) {
				$selected_value = $option['default'];
			}

			$type_box .= '<label for="' . esc_attr( $option['id'] ) . '" class="'. esc_attr( $option['wrapper_class'] ) . ' tips" data-tip="' . esc_attr( $option['description'] ) . '">' . esc_html( $option['label'] ) . ': <input type="checkbox" name="' . esc_attr( $option['id'] ) . '" id="' . esc_attr( $option['id'] ) . '" ' . checked( $selected_value, 'yes', false ) .' /></label>';
		}

		?>
		<div class="panel-wrap product_data">

			<span class="type_box"> &mdash; <?php echo $type_box; ?></span>

			<div class="wc-tabs-back"></div>

			<ul class="product_data_tabs wc-tabs" style="display:none;">
				<?php
					$product_data_tabs = apply_filters( 'woocommerce_product_data_tabs', array(
						'general' => array(
							'label'  => __( 'General', 'woocommerce' ),
							'target' => 'general_product_data',
							'class'  => array( 'hide_if_grouped' ),
						),
						'inventory' => array(
							'label'  => __( 'Inventory', 'woocommerce' ),
							'target' => 'inventory_product_data',
							'class'  => array( 'show_if_simple', 'show_if_variable', 'show_if_grouped' ),
						),
						'shipping' => array(
							'label'  => __( 'Shipping', 'woocommerce' ),
							'target' => 'shipping_product_data',
							'class'  => array( 'hide_if_virtual', 'hide_if_grouped', 'hide_if_external' ),
						),
						'linked_product' => array(
							'label'  => __( 'Linked Products', 'woocommerce' ),
							'target' => 'linked_product_data',
							'class'  => array(),
						),
						'attribute' => array(
							'label'  => __( 'Attributes', 'woocommerce' ),
							'target' => 'product_attributes',
							'class'  => array(),
						),
						'variations' => array(
							'label'  => __( 'Variations', 'woocommerce' ),
							'target' => 'variable_product_options',
							'class'  => array( 'variations_tab', 'show_if_variable' ),
						),
						'advanced' => array(
							'label'  => __( 'Advanced', 'woocommerce' ),
							'target' => 'advanced_product_data',
							'class'  => array(),
						)
					) );

					foreach ( $product_data_tabs as $key => $tab ) {
						?><li class="<?php echo $key; ?>_options <?php echo $key; ?>_tab <?php echo implode( ' ' , $tab['class'] ); ?>">
							<a href="#<?php echo $tab['target']; ?>"><?php echo esc_html( $tab['label'] ); ?></a>
						</li><?php
					}

					do_action( 'woocommerce_product_write_panel_tabs' );
				?>
			</ul>
			<div id="general_product_data" class="panel woocommerce_options_panel"><?php

				echo '<div class="options_group hide_if_grouped">';

					// SKU
					if( wc_product_sku_enabled() )
						woocommerce_wp_text_input( array( 'id' => '_sku', 'label' => '<abbr title="'. __( 'Stock Keeping Unit', 'woocommerce' ) .'">' . __( 'SKU', 'woocommerce' ) . '</abbr>', 'desc_tip' => 'true', 'description' => __( 'SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased.', 'woocommerce' ) ) );
					else
						echo '<input type="hidden" name="_sku" value="' . esc_attr( get_post_meta( $thepostid, '_sku', true ) ) . '" />';

					do_action('woocommerce_product_options_sku');

				echo '</div>';

				echo '<div class="options_group show_if_external">';

					// External URL
					woocommerce_wp_text_input( array( 'id' => '_product_url', 'label' => __( 'Product URL', 'woocommerce' ), 'placeholder' => 'http://', 'description' => __( 'Enter the external URL to the product.', 'woocommerce' ) ) );

					// Button text
					woocommerce_wp_text_input( array( 'id' => '_button_text', 'label' => __( 'Button text', 'woocommerce' ), 'placeholder' => _x('Buy product', 'placeholder', 'woocommerce'), 'description' => __( 'This text will be shown on the button linking to the external product.', 'woocommerce' ) ) );

				echo '</div>';

				echo '<div class="options_group pricing show_if_simple show_if_external">';

					// Price
					woocommerce_wp_text_input( array( 'id' => '_regular_price', 'label' => __( 'Regular Price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')', 'data_type' => 'price' ) );

					// Special Price
					woocommerce_wp_text_input( array( 'id' => '_sale_price', 'data_type' => 'price', 'label' => __( 'Sale Price', 'woocommerce' ) . ' ('.get_woocommerce_currency_symbol().')', 'description' => '<a href="#" class="sale_schedule">' . __( 'Schedule', 'woocommerce' ) . '</a>' ) );

					// Special Price date range
					$sale_price_dates_from 	= ( $date = get_post_meta( $thepostid, '_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d H:i', $date ) : '';
					$sale_price_dates_to 	= ( $date = get_post_meta( $thepostid, '_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d H:i', $date ) : '';

					echo '	<p class="form-field sale_price_dates_fields">
								<label for="_sale_price_dates_from">' . __( 'Sale Price Dates', 'woocommerce' ) . '</label>
								<input type="text" class="short" name="_sale_price_dates_from" id="_sale_price_dates_from" value="' . esc_attr( $sale_price_dates_from ) . '" placeholder="' . _x( 'From&hellip;', 'placeholder', 'woocommerce' ) . ' YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])(\s(([0-1]?[0-9])|([2][0-3])):([0-5]?[0-9]))?" />
								<input type="text" class="short" name="_sale_price_dates_to" id="_sale_price_dates_to" value="' . esc_attr( $sale_price_dates_to ) . '" placeholder="' . _x( 'To&hellip;', 'placeholder', 'woocommerce' ) . '  YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])(\s(([0-1]?[0-9])|([2][0-3])):([0-5]?[0-9]))?" />
								<a href="#" class="cancel_sale_schedule">'. __( 'Cancel', 'woocommerce' ) .'</a>
							</p>';

					do_action( 'woocommerce_product_options_pricing' );

				echo '</div>';

				echo '<div class="options_group show_if_downloadable">';

					?>
					<div class="form-field downloadable_files">
						<label><?php _e( 'Downloadable Files', 'woocommerce' ); ?>:</label>
						<table class="widefat">
							<thead>
								<tr>
									<th class="sort">&nbsp;</th>
									<th><?php _e( 'Name', 'woocommerce' ); ?> <span class="tips" data-tip="<?php _e( 'This is the name of the download shown to the customer.', 'woocommerce' ); ?>">[?]</span></th>
									<th colspan="2"><?php _e( 'File URL', 'woocommerce' ); ?> <span class="tips" data-tip="<?php _e( 'This is the URL or absolute path to the file which customers will get access to.', 'woocommerce' ); ?>">[?]</span></th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<th colspan="5">
										<a href="#" class="button insert" data-row="<?php
											$file = array(
												'file' => '',
												'name' => ''
											);
											ob_start();
											include( 'views/html-product-download.php' );
											echo esc_attr( ob_get_clean() );
										?>"><?php _e( 'Add File', 'woocommerce' ); ?></a>
									</th>
								</tr>
							</tfoot>
							<tbody>
								<?php
								$downloadable_files = get_post_meta( $post->ID, '_downloadable_files', true );

								if ( $downloadable_files ) {
									foreach ( $downloadable_files as $key => $file ) {
										include( 'views/html-product-download.php' );
									}
								}
								?>
							</tbody>
						</table>
					</div>
					<?php

					// Download Limit
					woocommerce_wp_text_input( array( 'id' => '_download_limit', 'label' => __( 'Download Limit', 'woocommerce' ), 'placeholder' => __( 'Unlimited', 'woocommerce' ), 'description' => __( 'Leave blank for unlimited re-downloads.', 'woocommerce' ), 'type' => 'number', 'custom_attributes' => array(
						'step' 	=> '1',
						'min'	=> '0'
					) ) );

					// Expirey
					woocommerce_wp_text_input( array( 'id' => '_download_expiry', 'label' => __( 'Download Expiry', 'woocommerce' ), 'placeholder' => __( 'Never', 'woocommerce' ), 'description' => __( 'Enter the number of days before a download link expires, or leave blank.', 'woocommerce' ), 'type' => 'number', 'custom_attributes' => array(
						'step' 	=> '1',
						'min'	=> '0'
					) ) );

					 // Download Type
					woocommerce_wp_select( array( 'id' => '_download_type', 'label' => __( 'Download Type', 'woocommerce' ), 'description' => sprintf( __( 'Choose a download type - this controls the <a href="%s">schema</a>.', 'woocommerce' ), 'http://schema.org/' ), 'options' => array(
						''            => __( 'Standard Product', 'woocommerce' ),
						'application' => __( 'Application/Software', 'woocommerce' ),
						'music'       => __( 'Music', 'woocommerce' ),
					) ) );

					do_action( 'woocommerce_product_options_downloads' );

				echo '</div>';

				if ( get_option( 'woocommerce_calc_taxes' ) == 'yes' ) {

					echo '<div class="options_group show_if_simple show_if_external show_if_variable">';

						// Tax
						woocommerce_wp_select( array( 'id' => '_tax_status', 'label' => __( 'Tax Status', 'woocommerce' ), 'options' => array(
							'taxable' 	=> __( 'Taxable', 'woocommerce' ),
							'shipping' 	=> __( 'Shipping only', 'woocommerce' ),
							'none' 		=> _x( 'None', 'Tax status', 'woocommerce' )
						) ) );

						$tax_classes = array_filter( array_map( 'trim', explode( "\n", get_option( 'woocommerce_tax_classes' ) ) ) );
						$classes_options = array();
						$classes_options[''] = __( 'Standard', 'woocommerce' );
			    		if ( $tax_classes )
			    			foreach ( $tax_classes as $class )
			    				$classes_options[ sanitize_title( $class ) ] = esc_html( $class );

						woocommerce_wp_select( array( 'id' => '_tax_class', 'label' => __( 'Tax Class', 'woocommerce' ), 'options' => $classes_options ) );

						do_action( 'woocommerce_product_options_tax' );

					echo '</div>';

				}

				do_action( 'woocommerce_product_options_general_product_data' );
				?>
			</div>

			<div id="inventory_product_data" class="panel woocommerce_options_panel">

				<?php

				echo '<div class="options_group">';

				if (get_option('woocommerce_manage_stock')=='yes') {

					// manage stock
					woocommerce_wp_checkbox( array( 'id' => '_manage_stock', 'wrapper_class' => 'show_if_simple show_if_variable', 'label' => __('Manage stock?', 'woocommerce' ), 'description' => __( 'Enable stock management at product level (not needed if managing stock at variation level)', 'woocommerce' ) ) );

					do_action('woocommerce_product_options_stock');

					echo '<div class="stock_fields show_if_simple show_if_variable">';

					// Stock
					woocommerce_wp_text_input( array( 'id' => '_stock', 'label' => __( 'Stock Qty', 'woocommerce' ), 'desc_tip' => true, 'description' => __( 'Stock quantity. If this is a variable product this value will be used to control stock for all variations, unless you define stock at variation level.', 'woocommerce' ), 'type' => 'number', 'custom_attributes' => array(
						'step' 	=> 'any'
					)  ) );

					do_action('woocommerce_product_options_stock_fields');

					echo '</div>';

				}

				// Stock status
				woocommerce_wp_select( array( 'id' => '_stock_status', 'label' => __( 'Stock status', 'woocommerce' ), 'options' => array(
					'instock' => __( 'In stock', 'woocommerce' ),
					'outofstock' => __( 'Out of stock', 'woocommerce' )
				), 'desc_tip' => true, 'description' => __( 'Controls whether or not the product is listed as "in stock" or "out of stock" on the frontend.', 'woocommerce' ) ) );

				if (get_option('woocommerce_manage_stock')=='yes') {

					echo '<div class="show_if_simple show_if_variable">';

					// Backorders?
					woocommerce_wp_select( array( 'id' => '_backorders', 'label' => __( 'Allow Backorders?', 'woocommerce' ), 'options' => array(
						'no' => __( 'Do not allow', 'woocommerce' ),
						'notify' => __( 'Allow, but notify customer', 'woocommerce' ),
						'yes' => __( 'Allow', 'woocommerce' )
					), 'desc_tip' => true, 'description' => __( 'If managing stock, this controls whether or not backorders are allowed for this product and variations. If enabled, stock quantity can go below 0.', 'woocommerce' ) ) );

					echo '</div>';

				}
				
				do_action('woocommerce_product_options_stock_status');
				
				echo '</div>';

				echo '<div class="options_group show_if_simple show_if_variable">';

				// Individual product
				woocommerce_wp_checkbox( array( 'id' => '_sold_individually', 'wrapper_class' => 'show_if_simple show_if_variable', 'label' => __('Sold Individually', 'woocommerce'), 'description' => __('Enable this to only allow one of this item to be bought in a single order', 'woocommerce') ) );

				do_action('woocommerce_product_options_sold_individually');

				echo '</div>';
				
				do_action( 'woocommerce_product_options_inventory_product_data' );
				?>

			</div>

			<div id="shipping_product_data" class="panel woocommerce_options_panel">

				<?php

				echo '<div class="options_group">';

					// Weight
					if ( wc_product_weight_enabled() )
						woocommerce_wp_text_input( array( 'id' => '_weight', 'label' => __( 'Weight', 'woocommerce' ) . ' (' . get_option('woocommerce_weight_unit') . ')', 'placeholder' => wc_format_localized_decimal( 0 ), 'desc_tip' => 'true', 'description' => __( 'Weight in decimal form', 'woocommerce' ), 'type' => 'text', 'data_type' => 'decimal' ) );

					// Size fields
					if ( wc_product_dimensions_enabled() ) {
						?><p class="form-field dimensions_field">
							<label for="product_length"><?php echo __( 'Dimensions', 'woocommerce' ) . ' (' . get_option( 'woocommerce_dimension_unit' ) . ')'; ?></label>
							<span class="wrap">
								<input id="product_length" placeholder="<?php _e( 'Length', 'woocommerce' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="_length" value="<?php echo esc_attr( wc_format_localized_decimal( get_post_meta( $thepostid, '_length', true ) ) ); ?>" />
								<input placeholder="<?php _e( 'Width', 'woocommerce' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="_width" value="<?php echo esc_attr( wc_format_localized_decimal( get_post_meta( $thepostid, '_width', true ) ) ); ?>" />
								<input placeholder="<?php _e( 'Height', 'woocommerce' ); ?>" class="input-text wc_input_decimal last" size="6" type="text" name="_height" value="<?php echo esc_attr( wc_format_localized_decimal( get_post_meta( $thepostid, '_height', true ) ) ); ?>" />
							</span>
							<img class="help_tip" data-tip="<?php esc_attr_e( 'LxWxH in decimal form', 'woocommerce' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
						</p><?php
					}

					do_action( 'woocommerce_product_options_dimensions' );

				echo '</div>';

				echo '<div class="options_group">';

					// Shipping Class
					$classes = get_the_terms( $thepostid, 'product_shipping_class' );
					if ( $classes && ! is_wp_error( $classes ) ) $current_shipping_class = current($classes)->term_id; else $current_shipping_class = '';

					$args = array(
						'taxonomy' 			=> 'product_shipping_class',
						'hide_empty'		=> 0,
						'show_option_none' 	=> __( 'No shipping class', 'woocommerce' ),
						'name' 				=> 'product_shipping_class',
						'id'				=> 'product_shipping_class',
						'selected'			=> $current_shipping_class,
						'class'				=> 'select short'
					);
					?><p class="form-field dimensions_field"><label for="product_shipping_class"><?php _e( 'Shipping class', 'woocommerce' ); ?></label> <?php wp_dropdown_categories( $args ); ?> <img class="help_tip" data-tip="<?php esc_attr_e( 'Shipping classes are used by certain shipping methods to group similar products.', 'woocommerce' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" /></p><?php

					do_action( 'woocommerce_product_options_shipping' );

				echo '</div>';
				?>

			</div>

			<div id="product_attributes" class="panel wc-metaboxes-wrapper">

				<p class="toolbar">
					<a href="#" class="close_all"><?php _e( 'Close all', 'woocommerce' ); ?></a><a href="#" class="expand_all"><?php _e( 'Expand all', 'woocommerce' ); ?></a>
				</p>

				<div class="product_attributes wc-metaboxes">

					<?php
						// Array of defined attribute taxonomies
						$attribute_taxonomies = wc_get_attribute_taxonomies();

						// Product attributes - taxonomies and custom, ordered, with visibility and variation attributes set
						$attributes = maybe_unserialize( get_post_meta( $thepostid, '_product_attributes', true ) );

						$i = -1;

						// Taxonomies
						if ( $attribute_taxonomies ) {
					    	foreach ( $attribute_taxonomies as $tax ) {

					    		// Get name of taxonomy we're now outputting (pa_xxx)
					    		$attribute_taxonomy_name = wc_attribute_taxonomy_name( $tax->attribute_name );

					    		// Ensure it exists
					    		if ( ! taxonomy_exists( $attribute_taxonomy_name ) )
					    			continue;

					    		$i++;

					    		// Get product data values for current taxonomy - this contains ordering and visibility data
					    		if ( isset( $attributes[ sanitize_title( $attribute_taxonomy_name ) ] ) )
					    			$attribute = $attributes[ sanitize_title( $attribute_taxonomy_name ) ];

					    		$position = empty( $attribute['position'] ) ? 0 : absint( $attribute['position'] );

					    		// Get terms of this taxonomy associated with current product
					    		$post_terms = wp_get_post_terms( $thepostid, $attribute_taxonomy_name );

					    		// Any set?
					    		$has_terms = ( is_wp_error( $post_terms ) || ! $post_terms || sizeof( $post_terms ) == 0 ) ? 0 : 1;
					    		?>
					    		<div class="woocommerce_attribute wc-metabox closed taxonomy <?php echo $attribute_taxonomy_name; ?>" rel="<?php echo $position; ?>" <?php if ( ! $has_terms ) echo 'style="display:none"'; ?>>
									<h3>
										<button type="button" class="remove_row button"><?php _e( 'Remove', 'woocommerce' ); ?></button>
										<div class="handlediv" title="<?php _e( 'Click to toggle', 'woocommerce' ); ?>"></div>
										<strong class="attribute_name"><?php echo apply_filters( 'woocommerce_attribute_label', $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name, $tax->attribute_name ); ?></strong>
									</h3>
									<table cellpadding="0" cellspacing="0" class="woocommerce_attribute_data wc-metabox-content">
										<tbody>
											<tr>
												<td class="attribute_name">
													<label><?php _e( 'Name', 'woocommerce' ); ?>:</label>
													<strong><?php echo $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name; ?></strong>

													<input type="hidden" name="attribute_names[<?php echo $i; ?>]" value="<?php echo esc_attr( $attribute_taxonomy_name ); ?>" />
													<input type="hidden" name="attribute_position[<?php echo $i; ?>]" class="attribute_position" value="<?php echo esc_attr( $position ); ?>" />
													<input type="hidden" name="attribute_is_taxonomy[<?php echo $i; ?>]" value="1" />
												</td>
												<td rowspan="3">
													<label><?php _e( 'Value(s)', 'woocommerce' ); ?>:</label>
													<?php if ( $tax->attribute_type == "select" ) : ?>
														<select multiple="multiple" data-placeholder="<?php _e( 'Select terms', 'woocommerce' ); ?>" class="multiselect attribute_values" name="attribute_values[<?php echo $i; ?>][]">
															<?php
								        					$all_terms = get_terms( $attribute_taxonomy_name, 'orderby=name&hide_empty=0' );
							        						if ( $all_terms ) {
								        						foreach ( $all_terms as $term ) {
								        							$has_term = has_term( (int) $term->term_id, $attribute_taxonomy_name, $thepostid ) ? 1 : 0;
								        							echo '<option value="' . esc_attr( $term->slug ) . '" ' . selected( $has_term, 1, false ) . '>' . $term->name . '</option>';
																}
															}
															?>
														</select>

														<button class="button plus select_all_attributes"><?php _e( 'Select all', 'woocommerce' ); ?></button> <button class="button minus select_no_attributes"><?php _e( 'Select none', 'woocommerce' ); ?></button>

														<button class="button fr plus add_new_attribute" data-attribute="<?php echo $attribute_taxonomy_name; ?>"><?php _e( 'Add new', 'woocommerce' ); ?></button>

													<?php elseif ( $tax->attribute_type == "text" ) : ?>
														<input type="text" name="attribute_values[<?php echo $i; ?>]" value="<?php

															// Text attributes should list terms pipe separated
															if ( $post_terms ) {
																$values = array();
																foreach ( $post_terms as $term )
																	$values[] = $term->name;
																echo esc_attr( implode( ' ' . WC_DELIMITER . ' ', $values ) );
															}

														?>" placeholder="<?php _e( 'Pipe (|) separate terms', 'woocommerce' ); ?>" />
													<?php endif; ?>
													<?php do_action( 'woocommerce_product_option_terms', $tax, $i ); ?>
												</td>
											</tr>
											<tr>
												<td>
													<label><input type="checkbox" class="checkbox" <?php

														if ( isset( $attribute['is_visible'] ) )
															checked( $attribute['is_visible'], 1 );
														else
															checked( apply_filters( 'default_attribute_visibility', false, $tax ), true );

													?> name="attribute_visibility[<?php echo $i; ?>]" value="1" /> <?php _e( 'Visible on the product page', 'woocommerce' ); ?></label>
												</td>
											</tr>
											<tr>
												<td>
													<div class="enable_variation show_if_variable">
													<label><input type="checkbox" class="checkbox" <?php

														if ( isset( $attribute['is_variation'] ) )
															checked( $attribute['is_variation'], 1 );
														else
															checked( apply_filters( 'default_attribute_variation', false, $tax ), true );

													?> name="attribute_variation[<?php echo $i; ?>]" value="1" /> <?php _e( 'Used for variations', 'woocommerce' ); ?></label>
													</div>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
					    		<?php
					    	}
					    }

						// Custom Attributes
						if ( ! empty( $attributes ) ) foreach ( $attributes as $attribute ) {
							if ( $attribute['is_taxonomy'] )
								continue;

							$i++;

				    		$position = empty( $attribute['position'] ) ? 0 : absint( $attribute['position'] );
							?>
				    		<div class="woocommerce_attribute wc-metabox closed" rel="<?php echo $position; ?>">
								<h3>
									<button type="button" class="remove_row button"><?php _e( 'Remove', 'woocommerce' ); ?></button>
									<div class="handlediv" title="<?php _e( 'Click to toggle', 'woocommerce' ); ?>"></div>
									<strong class="attribute_name"><?php echo apply_filters( 'woocommerce_attribute_label', esc_html( $attribute['name'] ), esc_html( $attribute['name'] ) ); ?></strong>
								</h3>
								<table cellpadding="0" cellspacing="0" class="woocommerce_attribute_data wc-metabox-content">
									<tbody>
										<tr>
											<td class="attribute_name">
												<label><?php _e( 'Name', 'woocommerce' ); ?>:</label>
												<input type="text" class="attribute_name" name="attribute_names[<?php echo $i; ?>]" value="<?php echo esc_attr( $attribute['name'] ); ?>" />
												<input type="hidden" name="attribute_position[<?php echo $i; ?>]" class="attribute_position" value="<?php echo esc_attr( $position ); ?>" />
												<input type="hidden" name="attribute_is_taxonomy[<?php echo $i; ?>]" value="0" />
											</td>
											<td rowspan="3">
												<label><?php _e( 'Value(s)', 'woocommerce' ); ?>:</label>
												<textarea name="attribute_values[<?php echo $i; ?>]" cols="5" rows="5" placeholder="<?php _e( 'Enter some text, or some attributes by pipe (|) separating values.', 'woocommerce' ); ?>"><?php echo esc_textarea( $attribute['value'] ); ?></textarea>
											</td>
										</tr>
										<tr>
											<td>
												<label><input type="checkbox" class="checkbox" <?php checked( $attribute['is_visible'], 1 ); ?> name="attribute_visibility[<?php echo $i; ?>]" value="1" /> <?php _e( 'Visible on the product page', 'woocommerce' ); ?></label>
											</td>
										</tr>
										<tr>
											<td>
												<div class="enable_variation show_if_variable">
												<label><input type="checkbox" class="checkbox" <?php checked( $attribute['is_variation'], 1 ); ?> name="attribute_variation[<?php echo $i; ?>]" value="1" /> <?php _e( 'Used for variations', 'woocommerce' ); ?></label>
												</div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
							<?php
						}
					?>
				</div>

				<p class="toolbar">
					<button type="button" class="button button-primary add_attribute"><?php _e( 'Add', 'woocommerce' ); ?></button>
					<select name="attribute_taxonomy" class="attribute_taxonomy">
						<option value=""><?php _e( 'Custom product attribute', 'woocommerce' ); ?></option>
						<?php
							if ( $attribute_taxonomies ) {
						    	foreach ( $attribute_taxonomies as $tax ) {
						    		$attribute_taxonomy_name = wc_attribute_taxonomy_name( $tax->attribute_name );
						    		$label = $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name;
						    		echo '<option value="' . esc_attr( $attribute_taxonomy_name ) . '">' . esc_html( $label ) . '</option>';
						    	}
						    }
						?>
					</select>

					<button type="button" class="button save_attributes"><?php _e( 'Save attributes', 'woocommerce' ); ?></button>
				</p>
			</div>
			<div id="linked_product_data" class="panel woocommerce_options_panel">

				<div class="options_group">

				<p class="form-field"><label for="upsell_ids"><?php _e( 'Up-Sells', 'woocommerce' ); ?></label>
				<select id="upsell_ids" name="upsell_ids[]" class="ajax_chosen_select_products" multiple="multiple" data-placeholder="<?php _e( 'Search for a product&hellip;', 'woocommerce' ); ?>">
					<?php
						$upsell_ids = get_post_meta( $post->ID, '_upsell_ids', true );
						$product_ids = ! empty( $upsell_ids ) ? array_map( 'absint',  $upsell_ids ) : null;
						if ( $product_ids ) {
							foreach ( $product_ids as $product_id ) {

								$product = get_product( $product_id );

								if ( $product )
									echo '<option value="' . esc_attr( $product_id ) . '" selected="selected">' . esc_html( $product->get_formatted_name() ) . '</option>';
							}
						}
					?>
				</select> <img class="help_tip" data-tip='<?php _e( 'Up-sells are products which you recommend instead of the currently viewed product, for example, products that are more profitable or better quality or more expensive.', 'woocommerce' ) ?>' src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" /></p>

				<p class="form-field"><label for="crosssell_ids"><?php _e( 'Cross-Sells', 'woocommerce' ); ?></label>
				<select id="crosssell_ids" name="crosssell_ids[]" class="ajax_chosen_select_products" multiple="multiple" data-placeholder="<?php _e( 'Search for a product&hellip;', 'woocommerce' ); ?>">
					<?php
						$crosssell_ids = get_post_meta( $post->ID, '_crosssell_ids', true );
						$product_ids = ! empty( $crosssell_ids ) ? array_map( 'absint',  $crosssell_ids ) : null;
						if ( $product_ids ) {
							foreach ( $product_ids as $product_id ) {

								$product = get_product( $product_id );

								if ( $product )
									echo '<option value="' . esc_attr( $product_id ) . '" selected="selected">' . esc_html( $product->get_formatted_name() ) . '</option>';
							}
						}
					?>
				</select> <img class="help_tip" data-tip='<?php _e( 'Cross-sells are products which you promote in the cart, based on the current product.', 'woocommerce' ) ?>' src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" /></p>

				</div>

				<?php

				echo '<div class="options_group grouping show_if_simple show_if_external">';

					// List Grouped products
					$post_parents = array();
					$post_parents[''] = __( 'Choose a grouped product&hellip;', 'woocommerce' );

					if ( $grouped_term = get_term_by( 'slug', 'grouped', 'product_type' ) ) {

						$posts_in = array_unique( (array) get_objects_in_term( $grouped_term->term_id, 'product_type' ) );
						if ( sizeof( $posts_in ) > 0 ) {
							$args = array(
								'post_type'		=> 'product',
								'post_status' 	=> 'any',
								'numberposts' 	=> -1,
								'orderby' 		=> 'title',
								'order' 		=> 'asc',
								'post_parent' 	=> 0,
								'include' 		=> $posts_in,
							);
							$grouped_products = get_posts( $args );

							if ( $grouped_products ) {
								foreach ( $grouped_products as $product ) {

									if ( $product->ID == $post->ID )
										continue;

									$post_parents[ $product->ID ] = $product->post_title;
								}
							}
						}

					}

					woocommerce_wp_select( array( 'id' => 'parent_id', 'label' => __( 'Grouping', 'woocommerce' ), 'value' => absint( $post->post_parent ), 'options' => $post_parents, 'desc_tip' => true, 'description' => __( 'Set this option to make this product part of a grouped product.', 'woocommerce' ) ) );

					woocommerce_wp_hidden_input( array( 'id' => 'previous_parent_id', 'value' => absint( $post->post_parent ) ) );

					do_action( 'woocommerce_product_options_grouping' );

				echo '</div>';
				?>

				<?php do_action( 'woocommerce_product_options_related' ); ?>

			</div>

			<div id="advanced_product_data" class="panel woocommerce_options_panel">

				<?php

				echo '<div class="options_group hide_if_external">';

					// Purchase note
					woocommerce_wp_textarea_input(  array( 'id' => '_purchase_note', 'label' => __( 'Purchase Note', 'woocommerce' ), 'desc_tip' => 'true', 'description' => __( 'Enter an optional note to send the customer after purchase.', 'woocommerce' ) ) );

				echo '</div>';

				echo '<div class="options_group">';

					// menu_order
					woocommerce_wp_text_input(  array( 'id' => 'menu_order', 'label' => __( 'Menu order', 'woocommerce' ), 'desc_tip' => 'true', 'description' => __( 'Custom ordering position.', 'woocommerce' ), 'value' => intval( $post->menu_order ), 'type' => 'number', 'custom_attributes' => array(
						'step' 	=> '1'
					)  ) );

				echo '</div>';

				echo '<div class="options_group reviews">';

					woocommerce_wp_checkbox( array( 'id' => 'comment_status', 'label' => __( 'Enable reviews', 'woocommerce' ), 'cbvalue' => 'open', 'value' => esc_attr( $post->comment_status ) ) );

					do_action( 'woocommerce_product_options_reviews' );

				echo '</div>';
				?>

			</div>

			<?php
				self::output_variations();

				do_action( 'woocommerce_product_data_panels' );
				do_action( 'woocommerce_product_write_panels' ); // _deprecated
			?>

			<div class="clear"></div>

		</div>
		<?php
	}	

}